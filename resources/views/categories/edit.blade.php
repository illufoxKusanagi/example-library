<x-layouts::app :title="__('Edit Category: :name', ['name' => $category->name])">
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Edit Category') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ __('Update category name and collection details.') }}
                </flux:text>
            </div>
            <flux:button :href="route('categories.index')" variant="ghost" icon="arrow-left" size="sm">
                {{ __('Back') }}
            </flux:button>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <flux:input
                    name="name"
                    label="{{ __('Category Name') }}"
                    value="{{ old('name', $category->name) }}"
                    required
                    autofocus
                    clearable
                />

                <div class="rounded-lg bg-zinc-50 p-3 text-xs text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                    <span class="font-medium">{{ __('Current slug:') }}</span>
                    <code class="font-mono text-zinc-700 dark:text-zinc-300">{{ $category->slug }}</code>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button :href="route('categories.index')" variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="check">
                        {{ __('Update Category') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>

