<?php

use App\Models\Category;
use App\Models\User;

test('admin can view categories list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Category::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('categories.index'));

    $response->assertOk();
    $response->assertSee('Book Categories');
});

test('guest or client can view categories list but not create button', function () {
    $client = User::factory()->create(['role' => 'client']);
    $category = Category::factory()->create(['name' => 'Cyberpunk']);

    $response = $this->actingAs($client)->get(route('categories.index'));

    $response->assertOk();
    $response->assertSee('Cyberpunk');
    $response->assertDontSee('Add Category');
});

test('admin can create a category', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('categories.store'), [
        'name' => 'Mecha Manga',
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseHas('categories', [
        'name' => 'Mecha Manga',
        'slug' => 'mecha-manga',
    ]);
});

test('client cannot create a category', function () {
    $client = User::factory()->create(['role' => 'client']);

    $response = $this->actingAs($client)->post(route('categories.store'), [
        'name' => 'Forbidden Category',
    ]);

    $response->assertForbidden();
});

test('admin can update a category', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create(['name' => 'Old Genre']);

    $response = $this->actingAs($admin)->put(route('categories.update', $category), [
        'name' => 'New Genre',
    ]);

    $response->assertRedirect(route('categories.index'));
    expect($category->fresh()->name)->toBe('New Genre');
});

test('admin can soft delete and restore a category', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create(['name' => 'To Delete']);

    $response = $this->actingAs($admin)->delete(route('categories.destroy', $category));

    $response->assertRedirect(route('categories.index'));
    expect($category->fresh()->trashed())->toBeTrue();

    $restoreResponse = $this->actingAs($admin)->put(route('categories.restore', $category->id));

    $restoreResponse->assertRedirect(route('categories.trashed'));
    expect($category->fresh()->trashed())->toBeFalse();
});

test('admin can permanently delete a category', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create(['name' => 'Dead Category']);

    $category->delete();
    expect($category->fresh()->trashed())->toBeTrue();

    $response = $this->actingAs($admin)->delete(route('categories.force-delete', $category->id));

    $response->assertRedirect(route('categories.trashed'));
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});
