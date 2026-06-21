<?php

namespace App\Jobs;

use App\Enums\ImageStatus;
use App\Models\Image;
use App\Models\ImageFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class ProcessUploadedImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ImageFile $imageFile) {}

    public function handle(): void
    {
        $manager = ImageManager::usingDriver(Driver::class);

        $image = $manager->decodePath(Storage::path($this->imageFile->path));
        $image->scaleDown(width: 2000, height: 2000);

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: 82);

        $hash = $this->imageFile->hash;
        $finalPath = sprintf(
            'images/%s/%s/%s.webp',
            substr($hash, 0, 2),
            substr($hash, 2, 2),
            $hash,
        );

        Storage::put($finalPath, (string) $encoded);
        Storage::delete($this->imageFile->path);

        $this->imageFile->update([
            'path' => $finalPath,
            'mime_type' => 'image/webp',
            'size' => strlen((string) $encoded),
            'width' => $image->width(),
            'height' => $image->height(),
        ]);

        Image::where('image_file_id', $this->imageFile->id)
            ->update(['status' => ImageStatus::Ready]);
    }
}