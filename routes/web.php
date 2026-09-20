<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index'])->name('home');

Route::resource('books', BookController::class);

Route::middleware('auth')->group(function () {
    Route::post('books/{book}/borrow', [RentController::class, 'borrow'])->name('books.borrow');
    Route::post('books/{book}/return', [RentController::class, 'returnBook'])->name('books.return');
    Route::get('loans', [RentController::class, 'index'])->name('loans.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';
