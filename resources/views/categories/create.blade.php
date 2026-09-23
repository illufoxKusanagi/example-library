<x-layouts::app :title="__('Add Category')">
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Add New Category') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ __('Create a genre, topic, or collection tag for organizing library books.') }}
                </flux:text>
            </div>
            <flux:button :href="route('categories.index')" variant="ghost" icon="arrow-left" size="sm">
                {{ __('Cancel') }}
            </flux:button>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-6">
                @csrf

                <flux:input
                    name="name"
                    label="{{ __('Category Name') }}"
                    value="{{ old('name') }}"
                    placeholder="{{ __('e.g., Shonen, Rom-Com, Sci-Fi, Light Novel') }}"
                    required
                    autofocus
                    clearable
                />

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button :href="route('categories.index')" variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="check">
                        {{ __('Save Category') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>

