<x-layouts::app :title="$book->title">
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

        {{-- Top Navigation / Breadcrumbs --}}
        <div class="flex items-center justify-between">
            <flux:button :href="route('books.index')" variant="ghost" icon="arrow-left" size="sm">
                {{ __('Back to Catalog') }}
            </flux:button>

            <div class="flex items-center gap-2">
                @auth
                    @if (auth()->user()->isAdmin())
                        <flux:button :href="route('books.edit', $book)" variant="filled" icon="pencil-square" size="sm">
                            {{ __('Edit Book') }}
                        </flux:button>

                        <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('{{ __('Are you sure you want to permanently delete \":title\" from the library?', ['title' => $book->title]) }}');" class="inline">
                            @csrf
                            @method('DELETE')
                            <flux:button type="submit" variant="danger" icon="trash" size="sm">
                                {{ __('Delete') }}
                            </flux:button>
                        </form>
                    @else
                        @if ($book->status === 'available')
                            <form method="POST" action="{{ route('books.borrow', $book) }}" class="inline">
                                @csrf
                                <flux:button type="submit" variant="primary" icon="book-open" size="sm">
                                    {{ __('Borrow Book (7-Day Loan)') }}
                                </flux:button>
                            </form>
                        @elseif ($book->isRentedBy(auth()->user()))
                            <form method="POST" action="{{ route('books.return', $book) }}" class="inline">
                                @csrf
                                <flux:button type="submit" variant="filled" icon="arrow-uturn-left" size="sm">
                                    {{ __('Return Book') }}
                                </flux:button>
                            </form>
                        @else
                            <flux:badge color="zinc" size="md">
                                {{ __('Currently on Loan') }}
                            </flux:badge>
                        @endif
                    @endif
                @else
                    @if ($book->status === 'available')
                        <flux:button :href="route('login')" variant="primary" size="sm">
                            {{ __('Log in to Borrow') }}
                        </flux:button>
                    @endif
                @endauth
            </div>
        </div>

        {{-- Book Details Card --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <div class="grid grid-cols-1 gap-8 p-6 md:p-8 lg:grid-cols-12">
                {{-- Cover Image Column --}}
                <div class="lg:col-span-4 flex flex-col items-center">
                    <div class="relative aspect-[3/4] w-full max-w-sm overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100 shadow-md dark:border-zinc-700 dark:bg-zinc-800">
                        <img
                            src="{{ $book->cover_url }}"
                            alt="{{ $book->title }}"
                            class="h-full w-full object-cover"
                            onerror="this.onerror=null;this.src='{{ asset('images/placeholder-book.svg') }}';"
                        >

                        <div class="absolute top-3 right-3">
                            @if ($book->status === 'available')
                                <flux:badge color="emerald" size="md">
                                    {{ __('Available') }}
                                </flux:badge>
                            @else
                                <flux:badge color="zinc" size="md">
                                    {{ __('Unavailable') }}
                                </flux:badge>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Metadata Under Cover --}}
                    <div class="mt-6 w-full max-w-sm space-y-3 rounded-xl border border-zinc-100 bg-zinc-50/70 p-4 text-xs dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span class="font-medium">{{ __('Catalog Code') }}</span>
                            <span class="font-mono font-semibold text-zinc-900 dark:text-zinc-200">{{ $book->book_code }}</span>
                        </div>

                        <flux:separator />

                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span class="font-medium">{{ __('Published Year') }}</span>
                            <span class="text-zinc-900 dark:text-zinc-200">{{ $book->published_year ?: __('N/A') }}</span>
                        </div>

                        <flux:separator />

                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span class="font-medium">{{ __('Added to Library') }}</span>
                            <span class="text-zinc-900 dark:text-zinc-200">{{ $book->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Book Information Column --}}
                <div class="lg:col-span-8 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div>
                            <flux:heading size="xl" level="1" class="text-2xl font-bold tracking-tight sm:text-3xl text-zinc-900 dark:text-white">
                                {{ $book->title }}
                            </flux:heading>

                            <p class="mt-2 text-base font-medium text-zinc-600 dark:text-zinc-300">
                                {{ __('by') }} <span class="text-zinc-900 dark:text-zinc-100">{{ $book->author ?: __('Unknown Author') }}</span>
                            </p>
                        </div>

                        {{-- Categories --}}
                        @if ($book->categories->isNotEmpty())
                            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                @foreach ($book->categories as $category)
                                    <a href="{{ route('books.index', ['category' => $category->slug]) }}">
                                        <flux:badge size="sm" color="zinc" class="hover:border-zinc-400 dark:hover:border-zinc-500 cursor-pointer transition-colors">
                                            {{ $category->name }}
                                        </flux:badge>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <flux:separator class="my-6" />

                        {{-- Synopsis --}}
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Synopsis') }}
                            </h3>
                            <div class="mt-3 prose prose-zinc dark:prose-invert max-w-none text-sm leading-relaxed text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                {{ $book->synopsis ?: __('No synopsis available for this title.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>

