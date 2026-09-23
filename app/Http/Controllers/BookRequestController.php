<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookRequest;
use App\Models\RentLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookRequestController extends Controller
{
    /**
     * Display a listing of all requests for administrators.
     */
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();

        $query = BookRequest::with([
            'user' => fn($query) => $query->withTrashed(),
            'book' => fn($query) => $query->withTrashed(),
        ])->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $requests = $query->paginate(15)->withQueryString();
        $pendingCount = BookRequest::pending()->count();

        return view('requests.index', [
            'requests' => $requests,
            'currentStatus' => $status,
            'currentType' => $type,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Store a client loan request for an available book.
     */
    public function store(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();

        return DB::transaction(function () use ($user, $book) {
            $lockedBook = Book::whereKey($book->id)->lockForUpdate()->firstOrFail();
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedBook->status !== 'available') {
                return back()->with('error', __('This book is currently unavailable for borrowing.'));
            }

            if ($lockedBook->hasPendingLoanRequestFor($lockedUser)) {
                return back()->with('error', __('You already have a pending borrow request for ":title".', ['title' => $lockedBook->title]));
            }

            $totalActiveAndPending = $lockedUser->activeLoansCount() + $lockedUser->pendingLoanRequestsCount();
            if ($totalActiveAndPending >= 3) {
                return back()->with('error', __('You have reached the maximum allowance of 3 books (active loans + pending requests). Please return a book first.'));
            }

            BookRequest::create([
                'user_id' => $lockedUser->id,
                'book_id' => $lockedBook->id,
                'request_date' => now(),
                'type' => 'loan',
                'status' => 'pending',
            ]);

            return back()->with('success', __('Your borrow request for ":title" has been submitted for admin approval.', ['title' => $lockedBook->title]));
        });
    }

    /**
     * Store a client return request for a currently borrowed book.
     */
    public function storeReturn(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();

        return DB::transaction(function () use ($user, $book) {
            $lockedBook = Book::whereKey($book->id)->lockForUpdate()->firstOrFail();
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if (! $lockedBook->isRentedBy($lockedUser)) {
                return back()->with('error', __('You do not have an active borrowing record for ":title".', ['title' => $lockedBook->title]));
            }

            if ($lockedBook->hasPendingReturnRequestFor($lockedUser)) {
                return back()->with('error', __('You already have a pending return request for ":title".', ['title' => $lockedBook->title]));
            }

            BookRequest::create([
                'user_id' => $lockedUser->id,
                'book_id' => $lockedBook->id,
                'request_date' => now(),
                'type' => 'return',
                'status' => 'pending',
            ]);

            return back()->with('success', __('Your return request for ":title" has been submitted for admin approval.', ['title' => $lockedBook->title]));
        });
    }

    /**
     * Accept a pending request.
     */
    public function accept(BookRequest $bookRequest): RedirectResponse
    {
        return DB::transaction(function () use ($bookRequest) {
            $lockedRequest = BookRequest::whereKey($bookRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRequest->isPending()) {
                return back()->with('error', __('This request has already been processed.'));
            }

            $book = Book::withTrashed()->lockForUpdate()->findOrFail($lockedRequest->book_id);
            $user = User::withTrashed()->lockForUpdate()->findOrFail($lockedRequest->user_id);

            if ($lockedRequest->type === 'loan') {
                if ($user->trashed()) {
                    $lockedRequest->update(['status' => 'rejected']);

                    return back()->with('error', __('Requester account is no longer active. Request rejected.'));
                }

                if ($book->trashed()) {
                    $lockedRequest->update(['status' => 'rejected']);

                    return back()->with('error', __('Book ":title" has been archived and is unavailable for borrowing. Request rejected.', ['title' => $book->title]));
                }

                if ($book->status !== 'available') {
                    return back()->with('error', __('Book ":title" is no longer available.', ['title' => $book->title]));
                }

                if ($user->activeLoansCount() >= 3) {
                    return back()->with('error', __('Member ":user" already has 3 active loans.', ['user' => $user->name]));
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
                $lockedRequest->update(['status' => 'accepted']);

                return back()->with('success', __('Loan request accepted for ":book" by :user. Due: :due.', [
                    'book' => $book->title,
                    'user' => $user->name,
                    'due' => $dueDate->format('M d, Y'),
                ]));
            }

            if ($lockedRequest->type === 'return') {
                $rentLog = $book->rentLogs()
                    ->where('user_id', $user->id)
                    ->whereNull('actual_return_date')
                    ->latest('id')
                    ->first();

                if (! $rentLog) {
                    return back()->with('error', __('No active loan found for ":book" by :user. Cannot process return.', [
                        'book' => $book->title,
                        'user' => $user->name,
                    ]));
                }

                $rentLog->update([
                    'actual_return_date' => now()->toDateString(),
                    'status' => 'returned',
                ]);

                $book->update(['status' => 'available']);
                $lockedRequest->update(['status' => 'accepted']);

                return back()->with('success', __('Return request accepted. ":book" has been returned by :user.', [
                    'book' => $book->title,
                    'user' => $user->name,
                ]));
            }

            return back()->with('error', __('Unknown request type.'));
        });
    }

    /**
     * Reject a pending request.
     */
    public function reject(BookRequest $bookRequest): RedirectResponse
    {
        return DB::transaction(function () use ($bookRequest) {
            $lockedRequest = BookRequest::whereKey($bookRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRequest->isPending()) {
                return back()->with('error', __('This request has already been processed.'));
            }

            $lockedRequest->update(['status' => 'rejected']);

            return back()->with('success', __('The :type request for ":book" by :user has been rejected.', [
                'type' => $lockedRequest->type,
                'book' => $lockedRequest->book?->title,
                'user' => $lockedRequest->user?->name,
            ]));
        });
    }

    /**
     * Display the authenticated client user's requests.
     */
    public function myRequests(Request $request): View
    {
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();

        $user = $request->user();
        $query = $user->bookRequests()
            ->with(['book' => fn($query) => $query->withTrashed()])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $requests = $query->paginate(15)->withQueryString();
        $pendingCount = $user->bookRequests()->pending()->count();

        return view('requests.my-requests', [
            'requests' => $requests,
            'currentStatus' => $status,
            'currentType' => $type,
            'pendingCount' => $pendingCount,
        ]);
    }
}
