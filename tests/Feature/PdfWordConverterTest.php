<?php

use App\Services\PdfWordConverter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('converts PDF lesson text and images into a cached Word document', function () {
    $image = base64_encode(UploadedFile::fake()->image('linux-logo.png', 8, 8)->getContent());
    $pdf = Pdf::loadHTML('<h1>Linux OS</h1><p>Learn the Linux terminal.</p>'
        .'<img src="data:image/png;base64,'.$image.'" width="32" height="32" alt="Linux lesson illustration">')->output();
    $directory = storage_path('app/private/document-conversions');
    $existingDirectories = File::isDirectory($directory) ? File::directories($directory) : [];

    $document = app(PdfWordConverter::class)->convert($pdf);
    $cachePath = 'private/document-conversions/'.hash('sha256', $pdf).'.docx';

    Storage::disk('local')->assertExists($cachePath);
    expect($document)->toStartWith('PK');
    expect(Storage::disk('local')->get($cachePath))->toBe($document);
    expect(app(PdfWordConverter::class)->convert($pdf))->toBe($document);

    $zip = new ZipArchive;
    expect($zip->open(Storage::disk('local')->path($cachePath)))->toBeTrue();

    try {
        $xml = $zip->getFromName('word/document.xml');
        $mediaFiles = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $filename = $zip->getNameIndex($index);

            if (str_starts_with($filename, 'word/media/')) {
                $mediaFiles[] = $filename;
            }
        }

        expect(strip_tags($xml))->toContain('Linux OS')->toContain('Learn the Linux terminal.');
        expect($mediaFiles)->not->toBeEmpty();
        expect($xml)->toContain('<w:drawing>');
    } finally {
        $zip->close();
    }

    expect(File::directories($directory))->toBe($existingDirectories);
});

it('rejects invalid PDFs without caching documents or leaving temporary conversion files', function () {
    $contents = "%PDF-1.4\nThis is not a valid PDF document.";
    $directory = storage_path('app/private/document-conversions');
    $existingDirectories = File::isDirectory($directory) ? File::directories($directory) : [];

    expect(fn () => app(PdfWordConverter::class)->convert($contents))->toThrow(RuntimeException::class);

    Storage::disk('local')->assertMissing('private/document-conversions/'.hash('sha256', $contents).'.docx');
    expect(File::directories($directory))->toBe($existingDirectories);
});
