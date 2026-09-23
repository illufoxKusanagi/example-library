<?php

use App\Models\Book;
use App\Models\BookRequest;
use App\Models\RentLog;
use App\Models\User;

test('client can submit a loan request for an available book', function () {
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::factory()->create(['status' => 'available']);

    $response = $this->actingAs($client)
        ->post(route('books.request-loan', $book));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('book_requests', [
        'user_id' => $client->id,
        'book_id' => $book->id,
        'type' => 'loan',
        'status' => 'pending',
    ]);
});

test('client cannot submit duplicate loan request for same book', function () {
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::factory()->create(['status' => 'available']);

    BookRequest::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'type' => 'loan',
        'status' => 'pending',
        'request_date' => now(),
    ]);

    $response = $this->actingAs($client)
        ->post(route('books.request-loan', $book));

    $response->assertSessionHas('error');
    expect(BookRequest::where('user_id', $client->id)->count())->toBe(1);
});

test('client cannot request loan when 3 books quota is reached', function () {
    $client = User::factory()->create(['role' => 'client']);

    // Create 3 active loans
    for ($i = 0; $i < 3; $i++) {
        $b = Book::factory()->create(['status' => 'unavailable']);
        RentLog::create([
            'user_id' => $client->id,
            'book_id' => $b->id,
            'rent_date' => now()->toDateString(),
            'return_date' => now()->addDays(7)->toDateString(),
            'status' => 'rented',
        ]);
    }

    $newBook = Book::factory()->create(['status' => 'available']);

    $response = $this->actingAs($client)
        ->post(route('books.request-loan', $newBook));

    $response->assertSessionHas('error');
    $this->assertDatabaseMissing('book_requests', [
        'user_id' => $client->id,
        'book_id' => $newBook->id,
    ]);
});

test('client can request return for a borrowed book', function () {
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::factory()->create(['status' => 'unavailable']);

    RentLog::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'rent_date' => now()->toDateString(),
        'return_date' => now()->addDays(7)->toDateString(),
        'status' => 'rented',
    ]);

    $response = $this->actingAs($client)
        ->post(route('books.request-return', $book));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('book_requests', [
        'user_id' => $client->id,
        'book_id' => $book->id,
        'type' => 'return',
        'status' => 'pending',
    ]);
});

test('admin can accept loan request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::factory()->create(['status' => 'available']);

    $req = BookRequest::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'type' => 'loan',
        'status' => 'pending',
        'request_date' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->put(route('requests.accept', $req));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($req->fresh()->status)->toBe('accepted');
    expect($book->fresh()->status)->toBe('unavailable');
    $this->assertDatabaseHas('rent_logs', [
        'user_id' => $client->id,
        'book_id' => $book->id,
        'status' => 'rented',
    ]);
});

test('admin can accept return request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::factory()->create(['status' => 'unavailable']);

    RentLog::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'rent_date' => now()->subDays(3)->toDateString(),
        'return_date' => now()->addDays(4)->toDateString(),
        'status' => 'rented',
    ]);

    $req = BookRequest::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'type' => 'return',
        'status' => 'pending',
        'request_date' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->put(route('requests.accept', $req));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($req->fresh()->status)->toBe('accepted');
    expect($book->fresh()->status)->toBe('available');
    expect(RentLog::where('book_id', $book->id)->first()->actual_return_date)->not->toBeNull();
});

test('admin can reject a request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::factory()->create(['status' => 'available']);

    $req = BookRequest::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'type' => 'loan',
        'status' => 'pending',
        'request_date' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->put(route('requests.reject', $req));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($req->fresh()->status)->toBe('rejected');
    expect($book->fresh()->status)->toBe('available');
});

test('client can view my requests page', function () {
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::factory()->create(['title' => 'Dandadan']);

    BookRequest::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'type' => 'loan',
        'status' => 'pending',
        'request_date' => now(),
    ]);

    $response = $this->actingAs($client)
        ->get(route('requests.my'));

    $response->assertOk();
    $response->assertSee('My Requests');
    $response->assertSee('Dandadan');
    $response->assertSee('Borrow Request');
});

test('admin cannot accept loan request if user is soft-deleted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::factory()->create(['status' => 'available']);

    $req = BookRequest::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'type' => 'loan',
        'status' => 'pending',
        'request_date' => now(),
    ]);

    $client->delete();

    $response = $this->actingAs($admin)
        ->put(route('requests.accept', $req));

    $response->assertSessionHas('error');
    expect($req->fresh()->status)->toBe('rejected');
    expect($book->fresh()->status)->toBe('available');
});

test('admin cannot accept loan request if book is soft-deleted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::factory()->create(['status' => 'available']);

    $req = BookRequest::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'type' => 'loan',
        'status' => 'pending',
        'request_date' => now(),
    ]);

    $book->delete();

    $response = $this->actingAs($admin)
        ->put(route('requests.accept', $req));

    $response->assertSessionHas('error');
    expect($req->fresh()->status)->toBe('rejected');
});

test('admin cannot accept return request if no active rent log exists', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::factory()->create(['status' => 'unavailable']);

    $req = BookRequest::create([
        'user_id' => $client->id,
        'book_id' => $book->id,
        'type' => 'return',
        'status' => 'pending',
        'request_date' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->put(route('requests.accept', $req));

    $response->assertSessionHas('error');
    expect($req->fresh()->status)->toBe('pending');
    expect($book->fresh()->status)->toBe('unavailable');
});
