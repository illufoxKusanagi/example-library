<x-layouts::app :title="__('Deleted Categories')">
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
                    <flux:heading size="xl" level="1">{{ __('Deleted Categories') }}</flux:heading>
                    <flux:badge size="sm" color="red">{{ $categories->total() }} {{ trans_choice('in trash', $categories->total()) }}</flux:badge>
                </div>
                <flux:text class="mt-1">
                    {{ __('Restore soft-deleted categories or review archived collections.') }}
                </flux:text>
            </div>
            <flux:button :href="route('categories.index')" variant="ghost" icon="arrow-left" size="sm">
                {{ __('Back to Categories') }}
            </flux:button>
        </div>

        @if ($categories->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-300">
                        <thead class="border-b border-zinc-200 bg-zinc-50/75 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">{{ __('No') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Category Name') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Deleted At') }}</th>
                                <th scope="col" class="px-6 py-3.5 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($categories as $index => $category)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs text-zinc-400">
                                        {{ $categories->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                        {{ $category->name }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-zinc-500 dark:text-zinc-400">
                                        {{ $category->deleted_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('categories.restore', $category->id) }}" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <flux:button type="submit" variant="primary" size="xs" icon="arrow-path">
                                                    {{ __('Restore') }}
                                                </flux:button>
                                            </form>

                                            <flux:modal.trigger name="force-delete-category-{{ $category->id }}">
                                                <flux:button variant="danger" size="xs" icon="trash">
                                                    {{ __('Permanently Delete') }}
                                                </flux:button>
                                            </flux:modal.trigger>

                                            <flux:modal name="force-delete-category-{{ $category->id }}" class="min-w-[22rem] max-w-md space-y-6 text-left">
                                                <div>
                                                    <flux:heading size="lg">{{ __('Permanently Delete Category?') }}</flux:heading>
                                                    <flux:subheading class="mt-2">
                                                        {{ __('Are you sure you want to permanently delete category ":name"? This action cannot be undone.', ['name' => $category->name]) }}
                                                    </flux:subheading>
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                                    </flux:modal.close>
                                                    <form method="POST" action="{{ route('categories.force-delete', $category->id) }}">
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
                {{ $categories->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-200 p-12 text-center dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/40">
                <flux:icon name="trash" class="size-10 text-zinc-400" />
                <flux:heading size="lg" class="mt-4">{{ __('Trash is empty') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">
                    {{ __('There are no deleted categories in the archive.') }}
                </flux:text>
            </div>
        @endif
    </div>
</x-layouts::app>

