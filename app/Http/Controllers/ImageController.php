<?php

namespace App\Http\Controllers;

use App\Enums\ImageStatus;
use App\Http\Requests\StoreImageRequest;
use App\Http\Resources\ImageResource;
use App\Jobs\ProcessUploadedImage;
use App\Models\Image;
use App\Models\ImageFile;
use Illuminate\Support\Facades\DB;

class ImageController extends Controller
{
    public function store(StoreImageRequest $request)
    {
        $file = $request->file('file');
        $hash = hash_file('sha256', $file->getRealPath());

        $image = DB::transaction(function () use ($file, $hash, $request) {
            $imageFile = ImageFile::where('hash', $hash)->lockForUpdate()->first();

            if ($imageFile) {
                $imageFile->increment('ref_count');
                $status = ImageStatus::Ready;
            } else {
                $tempPath = $file->store('tmp');

                $imageFile = ImageFile::create([
                    'hash' => $hash,
                    'disk' => 'local',
                    'path' => $tempPath,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);

                $status = ImageStatus::Processing;
            }

            $image = Image::create([
                'user_id' => $request->user()->id,
                'image_file_id' => $imageFile->id,
                'name' => $file->getClientOriginalName(),
                'status' => $status,
            ]);

            if ($status === ImageStatus::Processing) {
                ProcessUploadedImage::dispatch($imageFile);
            }

            return $image;
        });

        return new ImageResource($image->load('imageFile'));
    }
}