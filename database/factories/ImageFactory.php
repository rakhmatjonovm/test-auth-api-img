<?php

namespace Database\Factories;

use App\Enums\ImageStatus;
use App\Models\Image;
use App\Models\ImageFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'image_file_id' => ImageFile::factory(),
            'name' => $this->faker->word() . '.jpg',
            'status' => ImageStatus::Ready,
        ];
    }

    public function processing(): static
    {
        return $this->state(fn () => ['status' => ImageStatus::Processing]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => ImageStatus::Failed]);
    }
}
