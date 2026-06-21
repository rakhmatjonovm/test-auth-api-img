<?php

use App\Enums\ImageStatus;
use App\Jobs\ProcessUploadedImage;
use App\Models\ImageFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Queue::fake();
});

it('требует авторизации для загрузки', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->postJson('/api/images', ['file' => $file])->assertUnauthorized();
});

it('загружает валидное jpeg изображение', function () {
    Sanctum::actingAs(User::factory()->create());

    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

    $this->postJson('/api/images', ['file' => $file])
        ->assertCreated()
        ->assertJsonPath('data.status', ImageStatus::Processing->value);

    $this->assertDatabaseCount('image_files', 1);
    $this->assertDatabaseCount('images', 1);

    Queue::assertPushed(ProcessUploadedImage::class);
});

it('отклоняет файлы не jpeg/png', function () {
    Sanctum::actingAs(User::factory()->create());

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->postJson('/api/images', ['file' => $file])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    $this->assertDatabaseCount('image_files', 0);
});

it('отклоняет файлы больше 5мб', function () {
    Sanctum::actingAs(User::factory()->create());

    $file = UploadedFile::fake()->image('big.jpg')->size(5121);

    $this->postJson('/api/images', ['file' => $file])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');
});

it('переиспользует файл при повторной загрузке того же изображения', function () {
    Sanctum::actingAs(User::factory()->create());

    $file = UploadedFile::fake()->image('photo.jpg', 400, 400);

    $this->postJson('/api/images', ['file' => $file])->assertCreated();
    $this->postJson('/api/images', ['file' => $file])->assertCreated();

    $this->assertDatabaseCount('image_files', 1);
    $this->assertDatabaseCount('images', 2);

    expect(ImageFile::first()->ref_count)->toBe(2);
});
