<x-layouts::app :title="__('Categories')">
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

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" level="1">{{ __('Book Categories') }}</flux:heading>
                    <flux:badge size="sm" color="zinc">{{ $categories->total() }} {{ trans_choice('category|categories', $categories->total()) }}</flux:badge>
                </div>
                <flux:text class="mt-1">
                    {{ __('Explore book genres, manga demographics, and literary categories.') }}
                </flux:text>
            </div>

            @if ($isAdmin)
                <div class="flex items-center gap-2">
                    @if ($trashedCount > 0)
                        <flux:button :href="route('categories.trashed')" variant="ghost" icon="trash" size="sm">
                            {{ __('Trash') }} ({{ $trashedCount }})
                        </flux:button>
                    @endif
                    <flux:button :href="route('categories.create')" variant="primary" icon="plus" size="sm">
                        {{ __('Add Category') }}
                    </flux:button>
                </div>
            @endif
        </div>

        {{-- Search Bar --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <form method="GET" action="{{ route('categories.index') }}" class="flex items-center gap-3">
                <div class="flex-1">
                    <flux:input
                        name="search"
                        value="{{ $currentSearch }}"
                        placeholder="{{ __('Search categories...') }}"
                        icon="magnifying-glass"
                        clearable
                    />
                </div>
                <flux:button type="submit" variant="filled">
                    {{ __('Search') }}
                </flux:button>
                @if ($currentSearch)
                    <flux:button :href="route('categories.index')" variant="ghost">
                        {{ __('Reset') }}
                    </flux:button>
                @endif
            </form>
        </div>

        {{-- Categories Table --}}
        @if ($categories->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-300">
                        <thead class="border-b border-zinc-200 bg-zinc-50/75 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">{{ __('No') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Category Name') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Slug') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Total Books') }}</th>
                                @if ($isAdmin)
                                    <th scope="col" class="px-6 py-3.5 text-right">{{ __('Actions') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($categories as $index => $category)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs text-zinc-400">
                                        {{ $categories->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('books.index', ['category' => $category->slug]) }}" class="font-medium text-zinc-900 hover:underline dark:text-white">
                                            {{ $category->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $category->slug }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:badge size="sm" color="zinc">
                                            {{ $category->books_count }} {{ trans_choice('book|books', $category->books_count) }}
                                        </flux:badge>
                                    </td>
                                    @if ($isAdmin)
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <flux:button :href="route('categories.edit', $category)" variant="ghost" size="xs" icon="pencil-square" title="{{ __('Edit') }}" />

                                                <flux:modal.trigger name="delete-category-{{ $category->id }}">
                                                    <flux:button variant="ghost" size="xs" icon="trash" class="text-red-600 hover:text-red-700 dark:text-red-400" title="{{ __('Delete') }}" />
                                                </flux:modal.trigger>

                                                <flux:modal name="delete-category-{{ $category->id }}" class="min-w-[22rem] max-w-md space-y-6 text-left">
                                                    <div>
                                                        <flux:heading size="lg">{{ __('Delete Category') }}</flux:heading>
                                                        <flux:subheading class="mt-2">
                                                            {{ __('Are you sure you want to delete category ":name"? It will be moved to trash.', ['name' => $category->name]) }}
                                                        </flux:subheading>
                                                    </div>
                                                    <div class="flex justify-end gap-2">
                                                        <flux:modal.close>
                                                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                                        </flux:modal.close>
                                                        <form method="POST" action="{{ route('categories.destroy', $category) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                                                        </form>
                                                    </div>
                                                </flux:modal>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-2">
                {{ $categories->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-200 p-12 text-center dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/40">
                <flux:icon name="folder-open" class="size-10 text-zinc-400" />
                <flux:heading size="lg" class="mt-4">{{ __('No categories found') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">
                    {{ $currentSearch ? __('No categories matched your search term.') : __('No categories have been registered yet.') }}
                </flux:text>
                @if ($isAdmin && ! $currentSearch)
                    <div class="mt-6">
                        <flux:button :href="route('categories.create')" variant="primary" icon="plus" size="sm">
                            {{ __('Add First Category') }}
                        </flux:button>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts::app>

