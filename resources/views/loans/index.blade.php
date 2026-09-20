<x-layouts::app :title="$isAdmin ? __('Library Loans Management') : __('My Borrowed Books')">
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
                    <flux:heading size="xl" level="1">
                        {{ $isAdmin ? __('Library Loans Management') : __('My Borrowed Books') }}
                    </flux:heading>
                    <flux:badge size="sm" color="zinc">{{ $loans->total() }} {{ trans_choice('record|records', $loans->total()) }}</flux:badge>
                </div>
                <flux:text class="mt-1">
                    {{ $isAdmin ? __('Track and monitor book borrowings, return dates, and members across the library.') : __('View your active borrowings, due dates, and return history.') }}
                </flux:text>
            </div>

            <div class="flex items-center gap-2">
                <flux:button :href="route('books.index')" variant="ghost" icon="book-open-text">
                    {{ __('Browse Catalog') }}
                </flux:button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex items-center gap-2">
            <flux:button :href="route('loans.index')" size="sm" :variant="empty($currentStatus) ? 'filled' : 'ghost'">
                {{ __('All Records') }}
            </flux:button>
            <flux:button :href="route('loans.index', ['status' => 'active'])" size="sm" :variant="$currentStatus === 'active' ? 'filled' : 'ghost'">
                {{ __('Currently On Loan') }}
            </flux:button>
            <flux:button :href="route('loans.index', ['status' => 'returned'])" size="sm" :variant="$currentStatus === 'returned' ? 'filled' : 'ghost'">
                {{ __('Returned') }}
            </flux:button>
        </div>

        {{-- Loans Table --}}
        @if ($loans->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-300">
                        <thead class="border-b border-zinc-200 bg-zinc-50/75 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">{{ __('Book Title') }}</th>
                                @if ($isAdmin)
                                    <th scope="col" class="px-6 py-3.5">{{ __('Borrower / Member') }}</th>
                                @endif
                                <th scope="col" class="px-6 py-3.5">{{ __('Borrow Date') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Due Date') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Status') }}</th>
                                <th scope="col" class="px-6 py-3.5 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($loans as $loan)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('books.show', $loan->book) }}" class="shrink-0">
                                                <img
                                                    src="{{ $loan->book->cover_url }}"
                                                    alt="{{ $loan->book->title }}"
                                                    class="h-12 w-9 rounded object-cover shadow-xs border border-zinc-200 dark:border-zinc-700"
                                                >
                                            </a>
                                            <div>
                                                <a href="{{ route('books.show', $loan->book) }}" class="font-medium text-zinc-900 hover:underline dark:text-white">
                                                    {{ $loan->book->title }}
                                                </a>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $loan->book->book_code }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    @if ($isAdmin)
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-zinc-900 dark:text-white">{{ $loan->user->name }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $loan->user->email }}</div>
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 text-xs font-mono">
                                        {{ $loan->rent_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono">
                                        <div class="flex items-center gap-1.5">
                                            <span>{{ $loan->return_date->format('M d, Y') }}</span>
                                            @if ($loan->isOverdue())
                                                <flux:badge color="red" size="xs">{{ __('Overdue') }}</flux:badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($loan->isReturned())
                                            <flux:badge color="zinc" size="sm">
                                                {{ __('Returned on :date', ['date' => $loan->actual_return_date?->format('M d')]) }}
                                            </flux:badge>
                                        @else
                                            <flux:badge color="emerald" size="sm">
                                                {{ __('Active Loan') }}
                                            </flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if (! $loan->isReturned())
                                            <form method="POST" action="{{ route('books.return', $loan->book) }}" class="inline">
                                                @csrf
                                                <flux:button type="submit" size="xs" variant="primary">
                                                    {{ __('Return Book') }}
                                                </flux:button>
                                            </form>
                                        @else
                                            <span class="text-xs text-zinc-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-2">
                {{ $loans->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/40">
                <div class="flex size-14 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="arrow-path" class="size-7 text-zinc-500 dark:text-zinc-400" />
                </div>
                <flux:heading size="lg" class="mt-4">{{ __('No borrowing records') }}</flux:heading>
                <flux:text class="mt-1.5 max-w-sm">
                    {{ __('There are currently no book loan records in this view.') }}
                </flux:text>
                <div class="mt-6">
                    <flux:button :href="route('books.index')" variant="primary" icon="book-open-text">
                        {{ __('Browse Library Books') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
