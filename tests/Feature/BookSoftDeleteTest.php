<?php

use App\Models\Book;
use App\Models\User;

test('admin can soft delete a book and restore it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = Book::factory()->create(['title' => 'Chainsaw Man']);

    // Soft delete
    $response = $this->actingAs($admin)->delete(route('books.destroy', $book));

    $response->assertRedirect(route('books.index'));
    expect($book->fresh()->trashed())->toBeTrue();

    // Verify excluded from public catalog
    $this->flushSession();
    $catalogResponse = $this->get(route('books.index'));
    $catalogResponse->assertDontSee('Chainsaw Man');

    // Restore book
    $restoreResponse = $this->actingAs($admin)->put(route('books.restore', $book->id));

    $restoreResponse->assertRedirect(route('books.trashed'));
    expect($book->fresh()->trashed())->toBeFalse();

    // Verify back in catalog
    $catalogResponse2 = $this->get(route('books.index'));
    $catalogResponse2->assertSee('Chainsaw Man');
});

test('admin can permanently delete a soft-deleted book', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = Book::factory()->create(['title' => 'Temporary Book']);

    $book->delete();
    expect($book->fresh()->trashed())->toBeTrue();

    $response = $this->actingAs($admin)->delete(route('books.force-delete', $book->id));

    $response->assertRedirect(route('books.trashed'));
    $this->assertDatabaseMissing('books', ['id' => $book->id]);
});
