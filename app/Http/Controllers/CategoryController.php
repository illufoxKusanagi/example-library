<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $categories = Category::query()
            ->withCount('books')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $trashedCount = $request->user()?->isAdmin()
            ? Category::onlyTrashed()->count()
            : 0;

        return view('categories.index', [
            'categories' => $categories,
            'currentSearch' => $search,
            'trashedCount' => $trashedCount,
            'isAdmin' => $request->user()?->isAdmin() ?? false,
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('categories.create', [
            'category' => new Category,
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', __('Category ":name" created successfully.', ['name' => $category->name]));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', __('Category ":name" updated successfully.', ['name' => $category->name]));
    }

    /**
     * Remove the specified category from storage (soft delete).
     */
    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', __('Category ":name" moved to trash.', ['name' => $name]));
    }

    /**
     * Display a listing of soft-deleted categories.
     */
    public function trashed(): View
    {
        $categories = Category::onlyTrashed()
            ->withCount('books')
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        return view('categories.trashed', [
            'categories' => $categories,
        ]);
    }

    /**
     * Restore the specified soft-deleted category.
     */
    public function restore(int $id): RedirectResponse
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();

        return redirect()
            ->route('categories.trashed')
            ->with('success', __('Category ":name" restored successfully.', ['name' => $category->name]));
    }

    /**
     * Permanently delete the specified soft-deleted category.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $name = $category->name;

        $category->books()->detach();
        $category->forceDelete();

        return redirect()
            ->route('categories.trashed')
            ->with('success', __('Category ":name" permanently deleted.', ['name' => $name]));
    }
}
