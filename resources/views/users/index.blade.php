<x-layouts::app :title="__('Users & Members')">
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

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" level="1">{{ __('Member Management') }}</flux:heading>
                    <flux:badge size="sm" color="zinc">{{ $users->total() }} {{ trans_choice('registered member|registered members', $users->total()) }}</flux:badge>
                </div>
                <flux:text class="mt-1">
                    {{ __('Manage library patrons, view loan records, and enforce member policies.') }}
                </flux:text>
            </div>

            <div class="flex items-center gap-2">
                @if ($bannedCount > 0)
                    <flux:button :href="route('users.trashed')" variant="ghost" icon="no-symbol" size="sm">
                        {{ __('Banned Members') }} ({{ $bannedCount }})
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <form method="GET" action="{{ route('users.index') }}" class="flex items-center gap-3">
                <div class="flex-1">
                    <flux:input
                        name="search"
                        value="{{ $currentSearch }}"
                        placeholder="{{ __('Search by member name, email, or phone...') }}"
                        icon="magnifying-glass"
                        clearable
                    />
                </div>
                <flux:button type="submit" variant="filled">
                    {{ __('Search') }}
                </flux:button>
                @if ($currentSearch)
                    <flux:button :href="route('users.index')" variant="ghost">
                        {{ __('Reset') }}
                    </flux:button>
                @endif
            </form>
        </div>

        {{-- Users Table --}}
        @if ($users->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-300">
                        <thead class="border-b border-zinc-200 bg-zinc-50/75 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">{{ __('No') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Member') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Phone') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Address') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Active Loans') }}</th>
                                <th scope="col" class="px-6 py-3.5 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($users as $index => $user)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs text-zinc-400">
                                        {{ $users->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex size-9 items-center justify-center rounded-full bg-zinc-100 font-semibold text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ $user->initials() }}
                                            </div>
                                            <div>
                                                <a href="{{ route('users.show', $user) }}" class="font-medium text-zinc-900 hover:underline dark:text-white">
                                                    {{ $user->name }}
                                                </a>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $user->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs text-zinc-700 dark:text-zinc-300">
                                        {{ $user->phone ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs max-w-xs truncate text-zinc-600 dark:text-zinc-400">
                                        {{ $user->address ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <flux:badge size="sm" :color="$user->rent_logs_count > 0 ? 'emerald' : 'zinc'">
                                            {{ $user->rent_logs_count }}/3 {{ __('books') }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button :href="route('users.show', $user)" variant="ghost" size="xs" icon="information-circle">
                                                {{ __('Details') }}
                                            </flux:button>

                                            <flux:modal.trigger name="ban-user-{{ $user->id }}">
                                                <flux:button variant="ghost" size="xs" icon="no-symbol" class="text-red-600 hover:text-red-700 dark:text-red-400">
                                                    {{ __('Ban') }}
                                                </flux:button>
                                            </flux:modal.trigger>

                                            <flux:modal name="ban-user-{{ $user->id }}" class="min-w-[22rem] max-w-md space-y-6 text-left">
                                                <div>
                                                    <flux:heading size="lg">{{ __('Ban Member') }}</flux:heading>
                                                    <flux:subheading class="mt-2">
                                                        {{ __('Are you sure you want to ban member ":name"? They will be moved to the banned members list.', ['name' => $user->name]) }}
                                                    </flux:subheading>
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                                    </flux:modal.close>
                                                    <form method="POST" action="{{ route('users.destroy', $user) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <flux:button type="submit" variant="danger">{{ __('Ban Member') }}</flux:button>
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
                {{ $users->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-200 p-12 text-center dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/40">
                <flux:icon name="users" class="size-10 text-zinc-400" />
                <flux:heading size="lg" class="mt-4">{{ __('No members found') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">
                    {{ $currentSearch ? __('No members matched your search criteria.') : __('No registered members yet.') }}
                </flux:text>
            </div>
        @endif
    </div>
</x-layouts::app>

