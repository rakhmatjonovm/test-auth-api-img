<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('логинит с правильными данными', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('password123'),
    ]);

    $this->postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'password123',
    ])->assertOk()->assertJsonStructure(['user', 'token']);
});

it('отклоняет неверный пароль', function () {
    User::factory()->create([
        'email' => 'login2@example.com',
        'password' => Hash::make('password123'),
    ]);

    $this->postJson('/api/login', [
        'email' => 'login2@example.com',
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});

it('отклоняет несуществующий email', function () {
    $this->postJson('/api/login', [
        'email' => 'ghost@example.com',
        'password' => 'whatever',
    ])->assertUnprocessable();
});

it('логаут отзывает токен', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/logout')
        ->assertNoContent();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('требует авторизации для логаута', function () {
    $this->postJson('/api/logout')->assertUnauthorized();
});
