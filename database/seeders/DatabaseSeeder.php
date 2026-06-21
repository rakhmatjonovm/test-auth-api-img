<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Image;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Image::factory()->count(5)->for($user)->create();

        Image::factory()->processing()->for($user)->create();
        Image::factory()->failed()->for($user)->create();

        $anotherUser = User::factory()->create([
            'name' => 'Another Test User',
            'email' => 'anothertest@example.com',
            'password' => bcrypt('password'),
        ]);

        Image::factory()->count(3)->for($anotherUser)->create();
    }
}
