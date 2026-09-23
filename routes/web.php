<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BookRequestController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public catalog and home
Route::get('/', [BookController::class, 'index'])->name('home');

// Admin book trash, restore, and permanent delete (must precede books resource)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('books/trashed', [BookController::class, 'trashed'])->name('books.trashed');
    Route::put('books/{id}/restore', [BookController::class, 'restore'])->name('books.restore');
    Route::delete('books/{id}/force-delete', [BookController::class, 'forceDelete'])->name('books.force-delete');
});

Route::resource('books', BookController::class);

// Categories listing (accessible to all authenticated users; admin sees management controls)
Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Client request-based borrowing & return actions
    Route::post('books/{book}/request-loan', [BookRequestController::class, 'store'])->name('books.request-loan');
    Route::post('books/{book}/request-return', [BookRequestController::class, 'storeReturn'])->name('books.request-return');
    Route::get('my-requests', [BookRequestController::class, 'myRequests'])->name('requests.my');

    // Loan history for authenticated users
    Route::get('loans', [RentController::class, 'index'])->name('loans.index');

    // Administrator Protected Routes
    Route::middleware('admin')->group(function () {
        // Direct borrow and return for admins
        Route::post('books/{book}/borrow', [RentController::class, 'borrow'])->name('books.borrow');
        Route::post('books/{book}/return', [RentController::class, 'returnBook'])->name('books.return');

        // Categories management
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/trashed', [CategoryController::class, 'trashed'])->name('categories.trashed');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::put('categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
        Route::delete('categories/{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.force-delete');

        // User / Member management
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/trashed', [UserController::class, 'trashed'])->name('users.trashed');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::put('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');

        // Request approvals
        Route::get('requests', [BookRequestController::class, 'index'])->name('requests.index');
        Route::put('requests/{bookRequest}/accept', [BookRequestController::class, 'accept'])->name('requests.accept');
        Route::put('requests/{bookRequest}/reject', [BookRequestController::class, 'reject'])->name('requests.reject');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';
