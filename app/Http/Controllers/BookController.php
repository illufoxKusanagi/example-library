<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     * Display a listing of books with optional search and filters.
     */
    public function index(Request $request): View
    {
        //
        $books = Book::with('categories')
            ->search($request->string('search')->toString())
            ->inCategory($request->string('category')->toString())
            ->withStatus($request->string('status')->toString())
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('books.index', [
            'books' => $books,
            'categories' => $categories,
            'currentSearch' => $request->input('search', ''),
            'currentCategory' => $request->input('category', ''),
            'currentStatus' => $request->input('status', ''),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Show the form for creating a new book.
     */
    public function create(): View
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403, __('Unauthorized. Only administrators can add books.'));

        $categories = Category::orderBy('name')->get();

        return view('books.create', [
            'categories' => $categories,
            'book' => new Book([
                'status' => 'available',
            ]),
        ]);
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403, __('Unauthorized. Only administrators can add books.'));

        $validated = $request->validated();
        $categories = $validated['categories'] ?? [];
        unset($validated['categories']);

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('covers', 'public');
            if (is_string($path)) {
                $validated['cover'] = basename($path);
            }
        }

        $book = Book::create($validated);

        if (! empty($categories)) {
            $book->categories()->sync($categories);
        }

        return redirect()
            ->route('books.show', $book)
            ->with('success', __('Book ":title" created successfully.', ['title' => $book->title]));
    }

    /**
     * Display the specified resource.
     * Display the specified book.
     */
    public function show(Book $book): View
    {
        //
        $book->load('categories');

        return view('books.show', [
            'book' => $book,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * Show the form for editing the specified book.
     */
    public function edit(Book $book): View
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403, __('Unauthorized. Only administrators can edit books.'));

        $book->load('categories');
        $categories = Category::orderBy('name')->get();

        return view('books.edit', [
            'book' => $book,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified book in storage.
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403, __('Unauthorized. Only administrators can update books.'));

        $validated = $request->validated();
        $categories = $validated['categories'] ?? [];
        unset($validated['categories']);

        if ($request->hasFile('cover')) {
            if ($book->cover && Storage::disk('public')->exists('covers/'.$book->cover)) {
                Storage::disk('public')->delete('covers/'.$book->cover);
            }

            $path = $request->file('cover')->store('covers', 'public');
            if (is_string($path)) {
                $validated['cover'] = basename($path);
            }
        }

        $book->update($validated);
        $book->categories()->sync($categories);

        return redirect()
            ->route('books.show', $book)
            ->with('success', __('Book ":title" updated successfully.', ['title' => $book->title]));
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Book $book): RedirectResponse
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403, __('Unauthorized. Only administrators can delete books.'));

        $title = $book->title;

        if ($book->cover && Storage::disk('public')->exists('covers/'.$book->cover)) {
            Storage::disk('public')->delete('covers/'.$book->cover);
        }

        $book->categories()->detach();
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', __('Book ":title" deleted successfully.', ['title' => $title]));
    }
}
