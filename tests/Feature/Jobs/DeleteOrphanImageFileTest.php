<?php

use App\Jobs\DeleteOrphanImageFile;
use App\Models\ImageFile;
use Illuminate\Support\Facades\Storage;

it('удаляет файл с диска, если ref_count всё ещё 0', function () {
    Storage::fake('local');

    $imageFile = ImageFile::factory()->create([
        'ref_count' => 0,
        'path' => 'images/aa/bb/test.webp',
    ]);

    Storage::put($imageFile->path, 'fake-content');

    (new DeleteOrphanImageFile($imageFile))->handle();

    Storage::assertMissing($imageFile->path);
    $this->assertDatabaseMissing('image_files', ['id' => $imageFile->id]);
});

it('не удаляет файл, если он снова стал использоваться за время задержки', function () {
    Storage::fake('local');

    $imageFile = ImageFile::factory()->create([
        'ref_count' => 0,
        'path' => 'images/aa/bb/test2.webp',
    ]);

    Storage::put($imageFile->path, 'fake-content');

    $imageFile->update(['ref_count' => 1]);

    (new DeleteOrphanImageFile($imageFile))->handle();

    Storage::assertExists($imageFile->path);
    $this->assertDatabaseHas('image_files', ['id' => $imageFile->id]);
});
