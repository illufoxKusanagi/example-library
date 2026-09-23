<x-layouts::app :title="__('Deleted Books Archive')">
    <div class="space-y-6">
        @if (session('success'))
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 text-sm text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300">
                <div class="flex items-center gap-2.5">
                    <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" level="1">{{ __('Deleted Books Archive') }}</flux:heading>
                    <flux:badge size="sm" color="red">{{ $books->total() }} {{ trans_choice('book in trash|books in trash', $books->total()) }}</flux:badge>
                </div>
                <flux:text class="mt-1">
                    {{ __('Restore soft-deleted titles back to the active library catalog.') }}
                </flux:text>
            </div>
            <flux:button :href="route('books.index')" variant="ghost" icon="arrow-left" size="sm">
                {{ __('Back to Catalog') }}
            </flux:button>
        </div>

        @if ($books->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-300">
                        <thead class="border-b border-zinc-200 bg-zinc-50/75 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">{{ __('No') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Book Title') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Book Code') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Categories') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Deleted At') }}</th>
                                <th scope="col" class="px-6 py-3.5 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($books as $index => $book)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs text-zinc-400">
                                        {{ $books->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img
                                                src="{{ $book->cover_url }}"
                                                alt="{{ $book->title }}"
                                                class="h-12 w-9 rounded object-cover border border-zinc-200 shadow-xs dark:border-zinc-700"
                                            >
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-white">{{ $book->title }}</div>
                                                <div class="text-xs text-zinc-500">{{ $book->author ?: __('Unknown') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs text-zinc-600 dark:text-zinc-300">
                                        {{ $book->book_code }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($book->categories as $cat)
                                                <flux:badge size="xs" color="zinc">{{ $cat->name }}</flux:badge>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-zinc-500 dark:text-zinc-400">
                                        {{ $book->deleted_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('books.restore', $book->id) }}" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <flux:button type="submit" variant="primary" size="xs" icon="arrow-path">
                                                    {{ __('Restore') }}
                                                </flux:button>
                                            </form>

                                            <flux:modal.trigger name="force-delete-book-{{ $book->id }}">
                                                <flux:button variant="danger" size="xs" icon="trash">
                                                    {{ __('Permanently Delete') }}
                                                </flux:button>
                                            </flux:modal.trigger>

                                            <flux:modal name="force-delete-book-{{ $book->id }}" class="min-w-[22rem] max-w-md space-y-6 text-left">
                                                <div>
                                                    <flux:heading size="lg">{{ __('Permanently Delete Book?') }}</flux:heading>
                                                    <flux:subheading class="mt-2">
                                                        {{ __('Are you sure you want to permanently delete ":title"? This action cannot be undone and will permanently remove all records and cover image associated with this book.', ['title' => $book->title]) }}
                                                    </flux:subheading>
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                                    </flux:modal.close>
                                                    <form method="POST" action="{{ route('books.force-delete', $book->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <flux:button type="submit" variant="danger">{{ __('Permanently Delete') }}</flux:button>
                                                    </form>
                                                </div>
                                            </flux:modal>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-2">
                {{ $books->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-200 p-12 text-center dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/40">
                <flux:icon name="book-open" class="size-10 text-zinc-400" />
                <flux:heading size="lg" class="mt-4">{{ __('No deleted books') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">
                    {{ __('There are no deleted books in the archive.') }}
                </flux:text>
            </div>
        @endif
    </div>
</x-layouts::app>

