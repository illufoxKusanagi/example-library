<x-layouts::app :title="__('Add New Book')">
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Back Navigation --}}
        <div>
            <flux:button :href="route('books.index')" variant="ghost" icon="arrow-left" size="sm">
                {{ __('Back to Catalog') }}
            </flux:button>
        </div>

        {{-- Header --}}
        <div>
            <flux:heading size="xl" level="1">{{ __('Add New Book') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Enter the details and cover image to catalog a new title in the library.') }}
            </flux:text>
        </div>

        {{-- Form Card --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900 sm:p-8">
            <form method="POST" action="{{ route('books.store') }}" enctype="multipart/form-data">
                @csrf
                @include('books._form', [
                    'book' => $book,
                    'categories' => $categories,
                    'isEdit' => false,
                ])
            </form>
        </div>
    </div>
</x-layouts::app>

