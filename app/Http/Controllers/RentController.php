<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\RentLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentController extends Controller
{
    /**
     * Display a listing of loans (member's own loans, or all loans for admin).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = RentLog::with(['user', 'book'])->latest('id');

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            if ($request->string('status')->toString() === 'active') {
                $query->active();
            } elseif ($request->string('status')->toString() === 'returned') {
                $query->whereNotNull('actual_return_date');
            }
        }

        $loans = $query->paginate(15)->withQueryString();

        return view('loans.index', [
            'loans' => $loans,
            'currentStatus' => $request->input('status', ''),
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    /**
     * Borrow an available book.
     */
    public function borrow(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();

        if ($book->status !== 'available') {
            return back()->with('error', __('This book is currently unavailable for borrowing.'));
        }

        if ($user->activeLoansCount() >= 3) {
            return back()->with('error', __('You have reached the maximum active loan limit of 3 books. Please return a book first.'));
        }

        $dueDate = now()->addDays(7);

        RentLog::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rent_date' => now()->toDateString(),
            'return_date' => $dueDate->toDateString(),
            'status' => 'rented',
        ]);

        $book->update(['status' => 'unavailable']);

        return back()->with('success', __('You have borrowed ":title". Due date: :date.', [
            'title' => $book->title,
            'date' => $dueDate->format('M d, Y'),
        ]));
    }

    /**
     * Return a borrowed book.
     */
    public function returnBook(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();

        $rentLogQuery = $book->rentLogs()->active();

        if (! $user->isAdmin()) {
            $rentLogQuery->where('user_id', $user->id);
        }

        $rentLog = $rentLogQuery->latest('id')->first();

        if (! $rentLog) {
            return back()->with('error', __('No active borrowing record found for this book.'));
        }

        $rentLog->update([
            'actual_return_date' => now()->toDateString(),
            'status' => 'returned',
        ]);

        $book->update(['status' => 'available']);

        return back()->with('success', __('":title" has been successfully returned to the library.', [
            'title' => $book->title,
        ]));
    }
}
