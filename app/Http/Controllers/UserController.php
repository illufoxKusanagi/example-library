<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of client users.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $users = User::query()
            ->where('role', 'client')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount(['rentLogs' => fn ($q) => $q->whereNull('actual_return_date')])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $bannedCount = User::onlyTrashed()->where('role', 'client')->count();

        return view('users.index', [
            'users' => $users,
            'currentSearch' => $search,
            'bannedCount' => $bannedCount,
        ]);
    }

    /**
     * Display the specified user detail with active loans and history.
     */
    public function show(User $user): View
    {
        $rentLogs = $user->rentLogs()
            ->with('book')
            ->latest('id')
            ->get();

        $recentRequests = $user->bookRequests()
            ->with('book')
            ->latest('id')
            ->take(10)
            ->get();

        return view('users.show', [
            'user' => $user,
            'rentLogs' => $rentLogs,
            'recentRequests' => $recentRequests,
        ]);
    }

    /**
     * Ban (soft-delete) the specified user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', __('You cannot ban your own account.'));
        }

        if ($user->isAdmin()) {
            return back()->with('error', __('Administrators cannot be banned.'));
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', __('Member ":name" has been banned.', ['name' => $name]));
    }

    /**
     * Display a listing of banned (soft-deleted) users.
     */
    public function trashed(): View
    {
        $users = User::onlyTrashed()
            ->where('role', 'client')
            ->latest('deleted_at')
            ->paginate(15);

        return view('users.trashed', [
            'users' => $users,
        ]);
    }

    /**
     * Restore the specified banned user.
     */
    public function restore(int $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()
            ->route('users.trashed')
            ->with('success', __('Member ":name" has been unbanned and restored.', ['name' => $user->name]));
    }

    /**
     * Permanently delete the specified banned user.
     */
    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);

        if ($user->id === $request->user()->id) {
            return back()->with('error', __('You cannot permanently delete your own account.'));
        }

        if ($user->isAdmin()) {
            return back()->with('error', __('Administrators cannot be deleted.'));
        }

        if ($user->hasActiveLoans()) {
            return back()->with('error', __('Cannot permanently delete member while they have active loans.'));
        }

        $name = $user->name;
        $user->forceDelete();

        return redirect()
            ->route('users.trashed')
            ->with('success', __('Member ":name" was permanently deleted.', ['name' => $name]));
    }
}
