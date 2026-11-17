<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class PdfWordConverter
{
    public function convert(string $contents): string
    {
        $cachePath = 'private/document-conversions/'.hash('sha256', $contents).'.docx';
        $disk = Storage::disk('local');

        if ($disk->exists($cachePath)) {
            return $disk->get($cachePath);
        }

        $executable = (new ExecutableFinder)->find('libreoffice');

        if ($executable === null) {
            throw new RuntimeException('PDF to Word conversion is unavailable.');
        }

        $directory = storage_path('app/private/document-conversions/'.Str::uuid());
        File::makeDirectory($directory, 0700, true);

        try {
            File::put($directory.'/lesson.pdf', $contents);

            $process = new Process([
                $executable,
                '-env:UserInstallation=file://'.$directory.'/profile',
                '--headless',
                '--nologo',
                '--nodefault',
                '--norestore',
                '--infilter=writer_pdf_import',
                '--convert-to',
                'docx:Office Open XML Text',
                '--outdir',
                $directory,
                $directory.'/lesson.pdf',
            ]);
            $process->setTimeout(45);
            $process->mustRun();

            if (! File::isFile($directory.'/lesson.docx')) {
                throw new RuntimeException('The uploaded PDF could not be converted to Word.');
            }

            $converted = File::get($directory.'/lesson.docx');
            $disk->put($cachePath, $converted);

            return $converted;
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
