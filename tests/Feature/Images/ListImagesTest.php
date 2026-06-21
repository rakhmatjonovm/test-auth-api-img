<?php

use App\Models\Image;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('требует авторизации для списка', function () {
    $this->getJson('/api/images')->assertUnauthorized();
});

it('возвращает только свои изображения', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Image::factory()->count(3)->for($user)->create();
    Image::factory()->count(2)->for($otherUser)->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/images')->assertOk()->assertJsonCount(3, 'data');
});
