<?php

use App\Models\Book;
use App\Models\RentLog;
use App\Models\User;

test('admin can view user list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(3)->create(['role' => 'client']);

    $response = $this->actingAs($admin)->get(route('users.index'));

    $response->assertOk();
    $response->assertSee('Member Management');
});

test('client cannot access user management', function () {
    $client = User::factory()->create(['role' => 'client']);

    $response = $this->actingAs($client)->get(route('users.index'));

    $response->assertForbidden();
});

test('admin can view user detail with loans', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create([
        'role' => 'client',
        'name' => 'John Reader',
        'phone' => '+65 9111 2222',
        'address' => '123 Library Lane',
    ]);
    $book = Book::factory()->create(['title' => 'Frieren Beyond Journeys End']);

    RentLog::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'rent_date' => now()->toDateString(),
        'return_date' => now()->addDays(7)->toDateString(),
        'status' => 'rented',
    ]);

    $response = $this->actingAs($admin)->get(route('users.show', $client));

    $response->assertOk();
    $response->assertSee('John Reader');
    $response->assertSee('+65 9111 2222');
    $response->assertSee('123 Library Lane');
    $response->assertSee('Frieren Beyond Journeys End');
});

test('admin can ban and restore a client user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['role' => 'client', 'name' => 'Bad Member']);

    $response = $this->actingAs($admin)->delete(route('users.destroy', $client));

    $response->assertRedirect(route('users.index'));
    expect($client->fresh()->trashed())->toBeTrue();

    $restoreResponse = $this->actingAs($admin)->put(route('users.restore', $client->id));

    $restoreResponse->assertRedirect(route('users.trashed'));
    expect($client->fresh()->trashed())->toBeFalse();
});

test('admin cannot ban themselves or another admin', function () {
    $admin1 = User::factory()->create(['role' => 'admin']);
    $admin2 = User::factory()->create(['role' => 'admin']);

    $responseSelf = $this->actingAs($admin1)->delete(route('users.destroy', $admin1));
    $responseSelf->assertSessionHas('error');
    expect($admin1->fresh()->trashed())->toBeFalse();

    $responseOtherAdmin = $this->actingAs($admin1)->delete(route('users.destroy', $admin2));
    $responseOtherAdmin->assertSessionHas('error');
    expect($admin2->fresh()->trashed())->toBeFalse();
});

test('admin can permanently delete a banned user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['role' => 'client', 'name' => 'To Expel']);

    $client->delete();
    expect($client->fresh()->trashed())->toBeTrue();

    $response = $this->actingAs($admin)->delete(route('users.force-delete', $client->id));

    $response->assertRedirect(route('users.trashed'));
    $this->assertDatabaseMissing('users', ['id' => $client->id]);
});

test('admin cannot permanently delete user who has active loans', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['role' => 'client', 'name' => 'Has Active Loans']);
    $book = Book::factory()->create();

    RentLog::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'rent_date' => now()->toDateString(),
        'return_date' => now()->addDays(7)->toDateString(),
        'status' => 'rented',
    ]);

    $client->delete();
    expect($client->fresh()->trashed())->toBeTrue();

    $response = $this->actingAs($admin)->delete(route('users.force-delete', $client->id));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('users', ['id' => $client->id]);
});
