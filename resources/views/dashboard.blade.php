<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-8">
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

        {{-- Welcome Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2.5">
                    <flux:heading size="xl" level="1">
                        {{ __('Welcome back, :name', ['name' => $user->name]) }}
                    </flux:heading>
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold uppercase tracking-wider {{ $isAdmin ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' }}">
                        {{ $isAdmin ? __('Administrator') : __('Member') }}
                    </span>
                </div>
                <flux:text class="mt-1">
                    {{ $isAdmin ? __('Library management overview and activity monitor.') : __('Manage your borrowings and discover your next manga or light novel.') }}
                </flux:text>
            </div>

            <div class="flex items-center gap-2">
                @if ($isAdmin)
                    <flux:button :href="route('books.create')" variant="primary" icon="plus" size="sm">
                        {{ __('Add Book') }}
                    </flux:button>
                    <flux:button :href="route('loans.index')" variant="filled" icon="arrow-path" size="sm">
                        {{ __('Manage Loans') }}
                    </flux:button>
                @else
                    <flux:button :href="route('books.index')" variant="primary" icon="book-open" size="sm">
                        {{ __('Browse Catalog') }}
                    </flux:button>
                    <flux:button :href="route('loans.index')" variant="filled" icon="clock" size="sm">
                        {{ __('My Loan History') }}
                    </flux:button>
                @endif
            </div>
        </div>

        @if ($isAdmin)
            {{-- Admin Stats Grid --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Total Books --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <flux:text class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Catalog Titles') }}
                        </flux:text>
                        <div class="rounded-lg bg-zinc-100 p-2 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            <flux:icon name="book-open" class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $totalBooks }}</span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('books') }}</span>
                    </div>
                    <div class="mt-2 flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            {{ $availableBooks }} {{ __('available') }}
                        </span>
                        <span>•</span>
                        <span>{{ $rentedBooks }} {{ __('on loan') }}</span>
                    </div>
                </div>

                {{-- Active Loans --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <flux:text class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Active Borrowings') }}
                        </flux:text>
                        <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                            <flux:icon name="arrow-path" class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $activeLoans }}</span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('out with members') }}</span>
                    </div>
                    <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <a href="{{ route('loans.index', ['status' => 'active']) }}" class="text-indigo-600 hover:underline dark:text-indigo-400 font-medium">
                            {{ __('View active loans →') }}
                        </a>
                    </div>
                </div>

                {{-- Overdue Loans --}}
                <div class="rounded-xl border {{ $overdueLoans > 0 ? 'border-red-200 bg-red-50/30 dark:border-red-900/60 dark:bg-red-950/20' : 'border-zinc-200 bg-white dark:border-zinc-700/80 dark:bg-zinc-900' }} p-5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <flux:text class="text-xs font-semibold uppercase tracking-wider {{ $overdueLoans > 0 ? 'text-red-700 dark:text-red-400 font-semibold' : 'text-zinc-500 dark:text-zinc-400' }}">
                            {{ __('Overdue Loans') }}
                        </flux:text>
                        <div class="rounded-lg {{ $overdueLoans > 0 ? 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }} p-2">
                            <flux:icon name="exclamation-triangle" class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-bold tracking-tight {{ $overdueLoans > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">{{ $overdueLoans }}</span>
                        <span class="text-xs {{ $overdueLoans > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-500 dark:text-zinc-400' }}">{{ __('past return date') }}</span>
                    </div>
                    <div class="mt-2 text-xs">
                        @if ($overdueLoans > 0)
                            <span class="font-medium text-red-600 dark:text-red-400">{{ __('Action required') }}</span>
                        @else
                            <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ __('All loans on schedule') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Members & Categories --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <flux:text class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Registered Members') }}
                        </flux:text>
                        <div class="rounded-lg bg-zinc-100 p-2 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            <flux:icon name="users" class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $totalMembers }}</span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('clients') }}</span>
                    </div>
                    <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <span>{{ $totalCategories }} {{ __('genres & categories') }}</span>
                    </div>
                </div>
            </div>

            {{-- 2-Column Section: Active Borrowings & Recent Additions --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                {{-- Active Loans Table --}}
                <div class="lg:col-span-8 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="lg" level="2">{{ __('Current Active Loans') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Books currently borrowed by members.') }}
                            </flux:text>
                        </div>
                        <flux:button :href="route('loans.index')" variant="ghost" size="xs">
                            {{ __('View all records →') }}
                        </flux:button>
                    </div>

                    @if ($recentLoans->isNotEmpty())
                        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                            <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @foreach ($recentLoans as $loan)
                                    <div class="flex items-center justify-between p-4 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('books.show', $loan->book) }}" class="shrink-0">
                                                <img
                                                    src="{{ $loan->book->cover_url }}"
                                                    alt="{{ $loan->book->title }}"
                                                    class="h-14 w-10 rounded-md object-cover border border-zinc-200 shadow-xs dark:border-zinc-700"
                                                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder-book.svg') }}';"
                                                >
                                            </a>
                                            <div>
                                                <a href="{{ route('books.show', $loan->book) }}" class="font-medium text-zinc-900 hover:underline dark:text-white">
                                                    {{ $loan->book->title }}
                                                </a>
                                                <div class="mt-0.5 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                                    <span>{{ __('Borrower:') }} <strong class="text-zinc-700 dark:text-zinc-300">{{ $loan->user->name }}</strong></span>
                                                    <span>•</span>
                                                    <span>{{ __('Due:') }} <strong class="font-mono text-zinc-700 dark:text-zinc-300">{{ $loan->return_date->format('M d, Y') }}</strong></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            @if ($loan->isOverdue())
                                                <flux:badge color="red" size="sm">{{ __('Overdue') }}</flux:badge>
                                            @else
                                                <flux:badge color="emerald" size="sm">{{ __('Active') }}</flux:badge>
                                            @endif

                                            <form method="POST" action="{{ route('books.return', $loan->book) }}" class="inline">
                                                @csrf
                                                <flux:button type="submit" size="xs" variant="filled">
                                                    {{ __('Return') }}
                                                </flux:button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-200 p-8 text-center dark:border-zinc-700/80 bg-zinc-50/50 dark:bg-zinc-900/40">
                            <flux:icon name="check-circle" class="size-8 text-zinc-400" />
                            <flux:text class="mt-2 text-sm font-medium">{{ __('No books currently on loan.') }}</flux:text>
                            <flux:text class="text-xs text-zinc-500">{{ __('All library titles are available on shelves.') }}</flux:text>
                        </div>
                    @endif
                </div>

                {{-- Recently Added Books Column --}}
                <div class="lg:col-span-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="lg" level="2">{{ __('Latest Additions') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Recently registered titles.') }}
                            </flux:text>
                        </div>
                        <flux:button :href="route('books.index')" variant="ghost" size="xs">
                            {{ __('Catalog →') }}
                        </flux:button>
                    </div>

                    <div class="space-y-3">
                        @foreach ($recentBooks as $book)
                            <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                                <a href="{{ route('books.show', $book) }}" class="shrink-0">
                                    <img
                                        src="{{ $book->cover_url }}"
                                        alt="{{ $book->title }}"
                                        class="h-16 w-11 rounded-md object-cover border border-zinc-200 shadow-xs dark:border-zinc-700"
                                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-book.svg') }}';"
                                    >
                                </a>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('books.show', $book) }}" class="block truncate font-medium text-sm text-zinc-900 hover:underline dark:text-white" title="{{ $book->title }}">
                                        {{ $book->title }}
                                    </a>
                                    <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $book->author ?: __('Unknown') }}
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-1.5">
                                        <flux:badge size="xs" :color="$book->status === 'available' ? 'emerald' : 'zinc'">
                                            {{ $book->status === 'available' ? __('Available') : __('On Loan') }}
                                        </flux:badge>
                                        <span class="font-mono text-[10px] text-zinc-400">{{ $book->book_code }}</span>
                                    </div>
                                </div>
                                <div>
                                    <flux:button :href="route('books.edit', $book)" variant="ghost" size="xs" icon="pencil-square" title="{{ __('Edit') }}" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        @else
            {{-- Client / Member View --}}

            {{-- Borrowing Limit & Quota Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-gradient-to-br from-white to-zinc-50/50 p-6 shadow-xs dark:border-zinc-700/80 dark:from-zinc-900 dark:to-zinc-800/40">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <flux:heading size="lg" level="2">{{ __('Your Loan Allowance') }}</flux:heading>
                            <flux:badge :color="$activeLoansCount >= 3 ? 'red' : 'emerald'" size="sm">
                                {{ $activeLoansCount }}/3 {{ __('Active Loans') }}
                            </flux:badge>
                        </div>
                        <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            @if ($activeLoansCount >= 3)
                                <strong class="text-red-600 dark:text-red-400">{{ __('Maximum limit reached.') }}</strong> {{ __('Please return a book before borrowing another.') }}
                            @else
                                {{ __('You can borrow up to :count more book(s) for 7 days each.', ['count' => 3 - $activeLoansCount]) }}
                            @endif
                        </flux:text>
                    </div>

                    <div class="flex items-center gap-3">
                        <flux:button :href="route('books.index')" variant="primary" icon="book-open">
                            {{ __('Discover Books') }}
                        </flux:button>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mt-5 space-y-1.5">
                    <div class="flex justify-between text-xs font-medium text-zinc-500 dark:text-zinc-400">
                        <span>{{ __('Quota used:') }} {{ round(($activeLoansCount / 3) * 100) }}%</span>
                        <span>{{ 3 - $activeLoansCount }} {{ __('slots available') }}</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div
                            class="h-full rounded-full transition-all duration-300 {{ $activeLoansCount >= 3 ? 'bg-red-500' : ($activeLoansCount > 0 ? 'bg-emerald-500' : 'bg-transparent') }}"
                            style="width: {{ ($activeLoansCount / 3) * 100 }}%"
                        ></div>
                    </div>
                </div>
            </div>

            {{-- Current Active Borrowings --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg" level="2">{{ __('My Active Loans') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Books currently in your possession.') }}
                        </flux:text>
                    </div>
                    <flux:button :href="route('loans.index')" variant="ghost" size="xs">
                        {{ __('Full loan history →') }}
                    </flux:button>
                </div>

                @if ($myActiveLoans->isNotEmpty())
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($myActiveLoans as $loan)
                            <div class="flex flex-col justify-between overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                                <div class="flex gap-4">
                                    <a href="{{ route('books.show', $loan->book) }}" class="shrink-0">
                                        <img
                                            src="{{ $loan->book->cover_url }}"
                                            alt="{{ $loan->book->title }}"
                                            class="h-24 w-16 rounded-lg object-cover border border-zinc-200 shadow-xs dark:border-zinc-700"
                                            onerror="this.onerror=null;this.src='{{ asset('images/placeholder-book.svg') }}';"
                                        >
                                    </a>
                                    <div class="min-w-0 flex-1 space-y-1">
                                        <a href="{{ route('books.show', $loan->book) }}" class="block font-semibold text-sm text-zinc-900 hover:underline dark:text-white truncate">
                                            {{ $loan->book->title }}
                                        </a>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $loan->book->author }}
                                        </div>
                                        <div class="pt-2 text-xs space-y-1">
                                            <div class="text-zinc-500 dark:text-zinc-400">
                                                {{ __('Borrowed:') }} <span class="font-mono text-zinc-800 dark:text-zinc-200">{{ $loan->rent_date->format('M d') }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Due:') }}</span>
                                                <span class="font-mono font-medium text-zinc-900 dark:text-zinc-100">{{ $loan->return_date->format('M d, Y') }}</span>
                                                @if ($loan->isOverdue())
                                                    <flux:badge color="red" size="xs">{{ __('Overdue') }}</flux:badge>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 border-t border-zinc-100 pt-3 dark:border-zinc-800 flex justify-end">
                                    <form method="POST" action="{{ route('books.return', $loan->book) }}">
                                        @csrf
                                        <flux:button type="submit" size="sm" variant="filled" icon="arrow-uturn-left">
                                            {{ __('Return Book') }}
                                        </flux:button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-200 p-10 text-center dark:border-zinc-700/80 bg-zinc-50/50 dark:bg-zinc-900/30">
                        <div class="rounded-full bg-zinc-100 p-3 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <flux:icon name="book-open" class="size-6" />
                        </div>
                        <flux:heading size="md" class="mt-3">{{ __('No books currently on loan') }}</flux:heading>
                        <flux:text class="mt-1 text-xs text-zinc-500 max-w-sm">
                            {{ __('You do not have any borrowed titles right now. Browse our library catalog to borrow up to 3 books.') }}
                        </flux:text>
                        <div class="mt-5">
                            <flux:button :href="route('books.index')" variant="primary" icon="plus" size="sm">
                                {{ __('Browse & Borrow Books') }}
                            </flux:button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Recommended / New Arrivals --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg" level="2">{{ __('Available to Borrow') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Popular titles ready on the shelves right now.') }}
                        </flux:text>
                    </div>
                    <flux:button :href="route('books.index')" variant="ghost" size="xs">
                        {{ __('View all in catalog →') }}
                    </flux:button>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($recommendedBooks as $book)
                        @include('books._card', ['book' => $book])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
