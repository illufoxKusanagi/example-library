<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\RentLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isAdmin()) {
            $totalBooks = Book::count();
            $availableBooks = Book::where('status', 'available')->count();
            $rentedBooks = Book::where('status', 'unavailable')->count();
            $activeLoans = RentLog::active()->count();
            $overdueLoans = RentLog::active()->where('return_date', '<', now()->toDateString())->count();
            $totalMembers = User::where('role', 'client')->count();
            $totalCategories = Category::count();

            $recentLoans = RentLog::with(['user', 'book'])
                ->active()
                ->latest('id')
                ->take(5)
                ->get();

            $recentBooks = Book::with('categories')
                ->latest('id')
                ->take(4)
                ->get();

            return view('dashboard', [
                'user' => $user,
                'isAdmin' => true,
                'totalBooks' => $totalBooks,
                'availableBooks' => $availableBooks,
                'rentedBooks' => $rentedBooks,
                'activeLoans' => $activeLoans,
                'overdueLoans' => $overdueLoans,
                'totalMembers' => $totalMembers,
                'totalCategories' => $totalCategories,
                'recentLoans' => $recentLoans,
                'recentBooks' => $recentBooks,
            ]);
        }

        // Client / Member dashboard
        $myActiveLoans = $user->rentLogs()
            ->with('book')
            ->active()
            ->latest('id')
            ->get();

        $activeLoansCount = $myActiveLoans->count();
        $totalBorrowedEver = $user->rentLogs()->count();
        $overdueLoansCount = $myActiveLoans->filter->isOverdue()->count();

        $recommendedBooks = Book::with('categories')
            ->where('status', 'available')
            ->latest('id')
            ->take(4)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'isAdmin' => false,
            'myActiveLoans' => $myActiveLoans,
            'activeLoansCount' => $activeLoansCount,
            'totalBorrowedEver' => $totalBorrowedEver,
            'overdueLoansCount' => $overdueLoansCount,
            'recommendedBooks' => $recommendedBooks,
        ]);
    }
}
