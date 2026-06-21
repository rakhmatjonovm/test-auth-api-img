<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImageFile extends Model
{
    /** @use HasFactory<\Database\Factories\ImageFileFactory> */
    use HasFactory;

    protected $fillable = [
        'hash',
        'disk',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
        'ref_count',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'ref_count' => 'integer',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
