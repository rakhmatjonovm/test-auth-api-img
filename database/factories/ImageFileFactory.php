<?php

namespace Database\Factories;

use App\Models\ImageFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImageFile>
 */
class ImageFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hash' => hash('sha256', $this->faker->unique()->uuid()),
            'disk' => 'local',
            'path' => 'images/' . $this->faker->uuid() . '.webp',
            'mime_type' => 'image/webp',
            'size' => $this->faker->numberBetween(20_000, 500_000),
            'width' => $this->faker->numberBetween(400, 2000),
            'height' => $this->faker->numberBetween(400, 2000),
            'ref_count' => 1,
        ];
    }
}
