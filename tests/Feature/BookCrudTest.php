<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\RentLog;
use App\Models\User;
use Database\Seeders\BookSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(CategorySeeder::class);
    $this->seed(BookSeeder::class);
});

test('home displays books index', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Library Catalog');
});

test('displays books catalog with seeded entries', function () {
    $book = Book::latest('id')->first();

    $response = $this->get('/books');

    $response->assertOk()
        ->assertSee('Library Catalog')
        ->assertSee($book->title);
});

test('filters books by search query', function () {
    $response = $this->get('/books?search=Another');

    $response->assertOk()
        ->assertSee('Another')
        ->assertDontSee('Initial D');
});

test('filters books by category slug', function () {
    $response = $this->get('/books?category=racing');

    $response->assertOk()
        ->assertSee('Initial D')
        ->assertSee('MF Ghost')
        ->assertDontSee('Another');
});

test('filters books by availability status', function () {
    $response = $this->get('/books?status=available');

    $response->assertOk();
});

test('displays single book details page for guests', function () {
    $book = Book::with('categories')->first();

    $response = $this->get(route('books.show', $book));

    $response->assertOk()
        ->assertSee($book->title)
        ->assertSee($book->book_code);
});

test('regression: guest can view jujutsu kaisen book details without null pointer on user and with cover url', function () {
    $book = Book::where('slug', 'jujutsu-kaisen')->first();

    $response = $this->get('/books/jujutsu-kaisen');

    $response->assertOk()
        ->assertSee('Jujutsu Kaisen')
        ->assertSee('Gege Akutami')
        ->assertSee('storage/covers/jujutsu-kaisen.jpg')
        ->assertDontSee('Attempt to read property');
});

test('guest cannot access book creation form', function () {
    $response = $this->get('/books/create');

    $response->assertForbidden();
});

test('client member cannot access book creation form', function () {
    $client = User::factory()->create(['role' => 'client']);

    $response = $this->actingAs($client)->get('/books/create');

    $response->assertForbidden();
});

test('admin can view book creation form', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/books/create');

    $response->assertOk()
        ->assertSee('Add New Book')
        ->assertSee('Book Title');
});

test('admin can create a new book with categories and cover image', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::first();
    $cover = UploadedFile::fake()->create('test-cover.jpg', 200, 'image/jpeg');

    $response = $this->actingAs($admin)->post('/books', [
        'title' => 'Bleach',
        'author' => 'Tite Kubo',
        'book_code' => 'BK999',
        'published_year' => 2001,
        'status' => 'available',
        'synopsis' => 'Ichigo Kurosaki becomes a Substitute Soul Reaper.',
        'categories' => [$category->id],
        'cover' => $cover,
    ]);

    $book = Book::where('book_code', 'BK999')->first();

    expect($book)->not->toBeNull();
    expect($book->title)->toBe('Bleach');
    expect($book->slug)->toBe('bleach');
    expect($book->categories->pluck('id'))->toContain($category->id);

    Storage::disk('public')->assertExists('covers/'.$book->cover);

    $response->assertRedirect(route('books.show', $book));
});

test('client cannot store a new book', function () {
    $client = User::factory()->create(['role' => 'client']);

    $response = $this->actingAs($client)->post('/books', [
        'title' => 'Unauthorized Book',
        'book_code' => 'BKUNAUTH',
        'status' => 'available',
    ]);

    $response->assertForbidden();
});

test('validates required fields on book creation by admin', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/books', []);

    $response->assertSessionHasErrors(['title', 'book_code', 'status']);
});

test('validates unique book code on creation by admin', function () {
    $admin = User::factory()->admin()->create();
    $existing = Book::first();

    $response = $this->actingAs($admin)->post('/books', [
        'title' => 'Duplicate Code Test',
        'book_code' => $existing->book_code,
        'status' => 'available',
    ]);

    $response->assertSessionHasErrors(['book_code']);
});

test('admin can view the book edit form', function () {
    $admin = User::factory()->admin()->create();
    $book = Book::first();

    $response = $this->actingAs($admin)->get(route('books.edit', $book));

    $response->assertOk()
        ->assertSee('Edit Book Entry')
        ->assertSee($book->title);
});

test('client cannot view the book edit form', function () {
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::first();

    $response = $this->actingAs($client)->get(route('books.edit', $book));

    $response->assertForbidden();
});

test('admin can update an existing book', function () {
    $admin = User::factory()->admin()->create();
    $book = Book::first();
    $category = Category::where('name', 'Action')->first();

    $response = $this->actingAs($admin)->put(route('books.update', $book), [
        'title' => 'Updated Title For Testing',
        'book_code' => $book->book_code,
        'author' => 'Updated Author',
        'published_year' => 2024,
        'status' => 'unavailable',
        'synopsis' => 'Updated synopsis text.',
        'categories' => [$category->id],
    ]);

    $book->refresh();

    expect($book->title)->toBe('Updated Title For Testing');
    expect($book->status)->toBe('unavailable');
    expect($book->author)->toBe('Updated Author');
    expect($book->categories->pluck('id'))->toContain($category->id);

    $response->assertRedirect(route('books.show', $book));
});

test('client cannot update an existing book', function () {
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::first();

    $response = $this->actingAs($client)->put(route('books.update', $book), [
        'title' => 'Malicious Update',
        'book_code' => $book->book_code,
        'status' => 'available',
    ]);

    $response->assertForbidden();
});

test('admin can delete a book and detach relationships', function () {
    $admin = User::factory()->admin()->create();
    $book = Book::create([
        'title' => 'Book To Delete',
        'book_code' => 'BKDEL01',
        'status' => 'available',
    ]);

    $category = Category::first();
    $book->categories()->attach($category->id);

    $response = $this->actingAs($admin)->delete(route('books.destroy', $book));

    expect(Book::where('book_code', 'BKDEL01')->exists())->toBeFalse();

    $response->assertRedirect(route('books.index'));
});

test('client cannot delete a book', function () {
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::first();

    $response = $this->actingAs($client)->delete(route('books.destroy', $book));

    $response->assertForbidden();
});

test('authenticated client can borrow an available book', function () {
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::where('status', 'available')->first();

    $response = $this->actingAs($client)->post(route('books.borrow', $book));

    $response->assertSessionHas('success');
    $book->refresh();

    expect($book->status)->toBe('unavailable');
    expect($book->isRentedBy($client))->toBeTrue();

    $rentLog = RentLog::where('book_id', $book->id)->where('user_id', $client->id)->first();
    expect($rentLog)->not->toBeNull();
    expect($rentLog->status)->toBe('rented');
    expect($rentLog->actual_return_date)->toBeNull();
});

test('client cannot borrow an unavailable book', function () {
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::where('status', 'available')->first();
    $book->update(['status' => 'unavailable']);

    $response = $this->actingAs($client)->post(route('books.borrow', $book));

    $response->assertSessionHas('error');
});

test('client cannot borrow more than 3 books', function () {
    $client = User::factory()->create(['role' => 'client']);
    $books = Book::where('status', 'available')->take(3)->get();

    foreach ($books as $b) {
        RentLog::create([
            'user_id' => $client->id,
            'book_id' => $b->id,
            'rent_date' => now()->toDateString(),
            'return_date' => now()->addDays(7)->toDateString(),
            'status' => 'rented',
        ]);
        $b->update(['status' => 'unavailable']);
    }

    expect($client->activeLoansCount())->toBe(3);

    $fourthBook = Book::where('status', 'available')->first();
    $response = $this->actingAs($client)->post(route('books.borrow', $fourthBook));

    $response->assertSessionHas('error');
    $fourthBook->refresh();
    expect($fourthBook->status)->toBe('available');
});

test('client can return a borrowed book', function () {
    $client = User::factory()->create(['role' => 'client']);
    $book = Book::where('status', 'available')->first();

    $this->actingAs($client)->post(route('books.borrow', $book));
    $book->refresh();
    expect($book->status)->toBe('unavailable');

    $response = $this->actingAs($client)->post(route('books.return', $book));

    $response->assertSessionHas('success');
    $book->refresh();

    expect($book->status)->toBe('available');
    expect($book->isRentedBy($client))->toBeFalse();

    $rentLog = RentLog::where('book_id', $book->id)->where('user_id', $client->id)->first();
    expect($rentLog->isReturned())->toBeTrue();
});

test('user can view loans history', function () {
    $client = User::factory()->create(['role' => 'client']);

    $response = $this->actingAs($client)->get(route('loans.index'));

    $response->assertOk()
        ->assertSee('My Borrowed Books');
});

test('admin can view all member loans in management view', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('loans.index'));

    $response->assertOk()
        ->assertSee('Library Loans Management');
});
