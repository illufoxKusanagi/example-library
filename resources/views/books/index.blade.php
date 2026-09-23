<x-layouts::app :title="__('Library Catalog')">
    <div class="space-y-6">
        {{-- Flash Notification --}}
        @if (session('success'))
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 text-sm text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300">
                <div class="flex items-center gap-2.5">
                    <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center justify-between rounded-xl border border-red-200 bg-red-50/70 p-4 text-sm text-red-800 dark:border-red-800/60 dark:bg-red-950/40 dark:text-red-300">
                <div class="flex items-center gap-2.5">
                    <flux:icon name="exclamation-circle" class="size-5 text-red-600 dark:text-red-400" />
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" level="1">{{ __('Library Catalog') }}</flux:heading>
                    <flux:badge size="sm" color="zinc">{{ $books->total() }} {{ trans_choice('entry|entries', $books->total()) }}</flux:badge>
                </div>
                <flux:text class="mt-1">
                    {{ __('Manage, discover, and organize your manga and light novel collection.') }}
                </flux:text>
            </div>

            @if (auth()->check() && auth()->user()->isAdmin())
                <div class="flex items-center gap-2">
                    <flux:button :href="route('books.trashed')" variant="ghost" icon="trash" size="sm">
                        {{ __('Trash') }}
                    </flux:button>
                    <flux:button :href="route('books.create')" variant="primary" icon="plus" size="sm">
                        {{ __('Add Book') }}
                    </flux:button>
                </div>
            @endif
        </div>

        {{-- Search & Filter Bar --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <form method="GET" action="{{ route('books.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                {{-- Search Input --}}
                <div class="lg:col-span-5">
                    <flux:input
                        name="search"
                        value="{{ $currentSearch }}"
                        placeholder="{{ __('Search by title, author, or code...') }}"
                        icon="magnifying-glass"
                        clearable
                    />
                </div>

                {{-- Category Filter --}}
                <div class="lg:col-span-3">
                    <flux:select name="category" onchange="this.form.submit()">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected($currentCategory === $category->slug)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>

                {{-- Status Filter --}}
                <div class="lg:col-span-2">
                    <flux:select name="status" onchange="this.form.submit()">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="available" @selected($currentStatus === 'available')>{{ __('Available') }}</option>
                        <option value="unavailable" @selected($currentStatus === 'unavailable')>{{ __('Unavailable') }}</option>
                    </flux:select>
                </div>

                {{-- Filter Action Buttons --}}
                <div class="flex items-center gap-2 lg:col-span-2">
                    <flux:button type="submit" variant="filled" class="w-full">
                        {{ __('Filter') }}
                    </flux:button>

                    @if ($currentSearch || $currentCategory || $currentStatus)
                        <flux:button :href="route('books.index')" variant="ghost" icon="x-mark" title="{{ __('Clear filters') }}" />
                    @endif
                </div>
            </form>
        </div>

        {{-- Books Grid --}}
        @if ($books->isNotEmpty())
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($books as $book)
                    @include('books._card', ['book' => $book])
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="pt-4">
                {{ $books->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/40">
                <div class="flex size-14 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="book-open-text" class="size-7 text-zinc-500 dark:text-zinc-400" />
                </div>

                <flux:heading size="lg" class="mt-4">{{ __('No books found') }}</flux:heading>
                <flux:text class="mt-1.5 max-w-sm">
                    @if ($currentSearch || $currentCategory || $currentStatus)
                        {{ __('No books match your current filter criteria. Try clearing some filters to see more results.') }}
                    @else
                        {{ __('Your library collection is currently empty. Get started by adding your first book entry.') }}
                    @endif
                </flux:text>

                <div class="mt-6 flex gap-3">
                    @if ($currentSearch || $currentCategory || $currentStatus)
                        <flux:button :href="route('books.index')" variant="ghost">
                            {{ __('Clear Filters') }}
                        </flux:button>
                    @endif

                    <flux:button :href="route('books.create')" variant="primary" icon="plus">
                        {{ __('Add Book') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>

