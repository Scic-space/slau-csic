<?php

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(Tests\TestCase::class);

it('resizes and converts uploaded images to webp', function () {
    Storage::fake('public');
    $upload = UploadedFile::fake()->image('large-photo.jpg', 3200, 2400);

    $path = app(ImageOptimizer::class)->store($upload, 'optimized', 1200, 1200);

    Storage::disk('public')->assertExists($path);
    expect($path)->toEndWith('.webp');

    [$width, $height, $type] = getimagesize(Storage::disk('public')->path($path));

    expect($width)->toBe(1200)
        ->and($height)->toBe(900)
        ->and($type)->toBe(IMAGETYPE_WEBP);
});

it('rejects files whose contents are not an image', function () {
    Storage::fake('public');
    $upload = UploadedFile::fake()->createWithContent('malicious.jpg', '<?php echo "unsafe";');

    expect(fn () => app(ImageOptimizer::class)->store($upload, 'optimized'))
        ->toThrow(ValidationException::class);
});

it('rejects images with unsafe source dimensions before decoding', function () {
    Storage::fake('public');
    $upload = UploadedFile::fake()->image('oversized.png', 6500, 6500);

    expect(fn () => app(ImageOptimizer::class)->store($upload, 'optimized'))
        ->toThrow(ValidationException::class);
});
