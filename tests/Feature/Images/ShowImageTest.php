<?php

use App\Models\Image;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

it('требует авторизации для просмотра', function () {
    $image = Image::factory()->create();

    $this->getJson("/api/images/{$image->id}")->assertUnauthorized();
});

it('отдаёт файл владельцу', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $image = Image::factory()->for($user)->create();

    Storage::put($image->imageFile->path, 'fake-image-content');

    Sanctum::actingAs($user);

    $this->get("/api/images/{$image->id}")->assertOk();
});

it('запрещает доступ к чужому изображению', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $image = Image::factory()->for($owner)->create();

    Sanctum::actingAs($other);

    $this->getJson("/api/images/{$image->id}")->assertForbidden();
});

it('возвращает 202 пока изображение обрабатывается', function () {
    $user = User::factory()->create();
    $image = Image::factory()->for($user)->processing()->create();

    Sanctum::actingAs($user);

    $this->getJson("/api/images/{$image->id}")->assertStatus(202);
});
