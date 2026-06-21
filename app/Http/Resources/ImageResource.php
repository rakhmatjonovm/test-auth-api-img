<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'mime_type' => $this->imageFile?->mime_type,
            'size' => $this->imageFile?->size,
            'width' => $this->imageFile?->width,
            'height' => $this->imageFile?->height,
            'url' => route('images.show', $this->id),
            'created_at' => $this->created_at,
        ];
    }
}
