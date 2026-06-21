<?php

use App\Jobs\DeleteOrphanImageFile;
use App\Models\Image;
use App\Models\ImageFile;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

it('требует авторизации для удаления', function () {
    $image = Image::factory()->create();

    $this->deleteJson("/api/images/{$image->id}")->assertUnauthorized();
});

it('запрещает удалять чужое изображение', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $image = Image::factory()->for($owner)->create();

    Sanctum::actingAs($other);

    $this->deleteJson("/api/images/{$image->id}")->assertForbidden();
    $this->assertDatabaseHas('images', ['id' => $image->id]);
});

it('удаляет изображение и уменьшает ref_count, если файл ещё используется', function () {
    Queue::fake();

    $user = User::factory()->create();
    $imageFile = ImageFile::factory()->create(['ref_count' => 2]);

    $image = Image::factory()->for($user)->for($imageFile)->create();
    Image::factory()->for($imageFile)->create();

    Sanctum::actingAs($user);

    $this->deleteJson("/api/images/{$image->id}")->assertNoContent();

    $this->assertDatabaseMissing('images', ['id' => $image->id]);
    expect($imageFile->fresh()->ref_count)->toBe(1);

    Queue::assertNotPushed(DeleteOrphanImageFile::class);
});

it('ставит очистку файла в очередь, когда ref_count доходит до нуля', function () {
    Queue::fake();

    $user = User::factory()->create();
    $imageFile = ImageFile::factory()->create(['ref_count' => 1]);
    $image = Image::factory()->for($user)->for($imageFile)->create();

    Sanctum::actingAs($user);

    $this->deleteJson("/api/images/{$image->id}")->assertNoContent();

    expect($imageFile->fresh()->ref_count)->toBe(0);
    Queue::assertPushed(DeleteOrphanImageFile::class);
});
