<x-layouts::app :title="__('Edit: :title', ['title' => $book->title])">
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Back Navigation --}}
        <div>
            <flux:button :href="route('books.show', $book)" variant="ghost" icon="arrow-left" size="sm">
                {{ __('Back to Book Details') }}
            </flux:button>
        </div>

        {{-- Header --}}
        <div>
            <flux:heading size="xl" level="1">{{ __('Edit Book Entry') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Update the catalog information, categories, or cover art for :title.', ['title' => $book->title]) }}
            </flux:text>
        </div>

        {{-- Form Card --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 sm:p-8">
            <form method="POST" action="{{ route('books.update', $book) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('books._form', [
                    'book' => $book,
                    'categories' => $categories,
                    'isEdit' => true,
                ])
            </form>
        </div>
    </div>
</x-layouts::app>

