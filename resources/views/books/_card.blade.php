@props(['book'])

<div class="group flex flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-700/80 dark:bg-zinc-900">
    {{-- Book Cover Container --}}
    <a href="{{ route('books.show', $book) }}" class="relative block aspect-[3/4] w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800">
        <img
            src="{{ $book->cover_url }}"
            alt="{{ $book->title }}"
            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            loading="lazy"
            onerror="this.onerror=null;this.src='{{ asset('images/placeholder-book.svg') }}';"
        >

        {{-- Status Badge --}}
        <div class="absolute top-2.5 right-2.5">
            @if ($book->status === 'available')
                <flux:badge color="emerald" size="sm" inset="top right">
                    {{ __('Available') }}
                </flux:badge>
            @else
                <flux:badge color="zinc" size="sm" inset="top right">
                    {{ __('On Loan') }}
                </flux:badge>
            @endif
        </div>

        {{-- Book Code Badge --}}
        <div class="absolute top-2.5 left-2.5">
            <span class="inline-flex items-center rounded-md bg-black/60 px-2 py-0.5 text-xs font-medium tracking-wider text-white backdrop-blur-xs">
                {{ $book->book_code }}
            </span>
        </div>
    </a>

    {{-- Content --}}
    <div class="flex flex-1 flex-col p-4">
        <div class="flex-1">
            <a href="{{ route('books.show', $book) }}" class="block">
                <h3 class="line-clamp-1 font-semibold text-zinc-900 transition-colors group-hover:text-zinc-600 dark:text-zinc-100 dark:group-hover:text-zinc-300" title="{{ $book->title }}">
                    {{ $book->title }}
                </h3>
            </a>

            <p class="mt-1 line-clamp-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ $book->author ?: __('Unknown Author') }}
                @if ($book->published_year)
                    <span class="mx-1 text-zinc-300 dark:text-zinc-600">•</span>
                    <span>{{ $book->published_year }}</span>
                @endif
            </p>

            {{-- Categories Badges --}}
            @if ($book->categories->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach ($book->categories->take(3) as $category)
                        <span class="inline-flex items-center rounded-md bg-zinc-100 px-1.5 py-0.5 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $category->name }}
                        </span>
                    @endforeach
                    @if ($book->categories->count() > 3)
                        <span class="inline-flex items-center rounded-md bg-zinc-50 px-1.5 py-0.5 text-[11px] font-medium text-zinc-400 dark:bg-zinc-800/60 dark:text-zinc-500">
                            +{{ $book->categories->count() - 3 }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Action Bar --}}
        <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">
            <flux:button :href="route('books.show', $book)" size="xs" variant="ghost" class="text-xs">
                {{ __('Details') }}
            </flux:button>

            <div class="flex items-center gap-1">
                @auth
                    @if (auth()->user()->isAdmin())
                        <flux:button :href="route('books.edit', $book)" size="xs" variant="subtle" icon="pencil-square" title="{{ __('Edit') }}" />

                        <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete \":title\"?', ['title' => $book->title]) }}');" class="inline">
                            @csrf
                            @method('DELETE')
                            <flux:button type="submit" size="xs" variant="danger" icon="trash" title="{{ __('Delete') }}" />
                        </form>
                    @else
                        @if ($book->status === 'available')
                            <form method="POST" action="{{ route('books.borrow', $book) }}" class="inline">
                                @csrf
                                <flux:button type="submit" size="xs" variant="primary">
                                    {{ __('Borrow') }}
                                </flux:button>
                            </form>
                        @elseif ($book->isRentedBy(auth()->user()))
                            <form method="POST" action="{{ route('books.return', $book) }}" class="inline">
                                @csrf
                                <flux:button type="submit" size="xs" variant="filled">
                                    {{ __('Return') }}
                                </flux:button>
                            </form>
                        @endif
                    @endif
                @else
                    @if ($book->status === 'available')
                        <flux:button :href="route('login')" size="xs" variant="primary">
                            {{ __('Borrow') }}
                        </flux:button>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</div>
