<?php

namespace App\Jobs;

use App\Models\ImageFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteOrphanImageFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ImageFile $imageFile) {}

    public function handle(): void
    {
        $imageFile = $this->imageFile->fresh();

        if (! $imageFile || $imageFile->ref_count > 0) {
            return;
        }

        Storage::delete($imageFile->path);
        $imageFile->delete();
    }
}