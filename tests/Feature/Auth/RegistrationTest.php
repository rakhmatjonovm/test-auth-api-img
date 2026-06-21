<?php

use App\Models\User;

it('регистрирует пользователя и возвращает токен', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

it('не регистрирует при несовпадении подтверждения пароля', function () {
    $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'bademail@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
    ])->assertUnprocessable()->assertJsonValidationErrors('password');
});

it('не регистрирует с уже занятым email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});
