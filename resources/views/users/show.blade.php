<x-layouts::app :title="__('Member: :name', ['name' => $user->name])">
    <div class="space-y-6">
        {{-- Top Navigation --}}
        <div class="flex items-center justify-between">
            <flux:button :href="route('users.index')" variant="ghost" icon="arrow-left" size="sm">
                {{ __('Back to Members') }}
            </flux:button>

            <flux:modal.trigger name="ban-user-{{ $user->id }}">
                <flux:button variant="danger" icon="no-symbol" size="sm">
                    {{ __('Ban Member') }}
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

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Member Profile Card (Left Column) --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex size-20 items-center justify-center rounded-full bg-zinc-100 font-bold text-xl text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $user->initials() }}
                        </div>
                        <flux:heading size="lg" level="2" class="mt-4">{{ $user->name }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500">{{ $user->email }}</flux:text>
                        <div class="mt-3">
                            <flux:badge size="sm" color="emerald">{{ __('Active Member') }}</flux:badge>
                        </div>
                    </div>

                    <flux:separator class="my-6" />

                    <div class="space-y-4 text-xs">
                        <div>
                            <flux:text class="font-semibold uppercase tracking-wider text-zinc-400">{{ __('Phone') }}</flux:text>
                            <p class="mt-1 font-mono text-sm text-zinc-900 dark:text-white">{{ $user->phone ?: __('Not provided') }}</p>
                        </div>

                        <div>
                            <flux:text class="font-semibold uppercase tracking-wider text-zinc-400">{{ __('Address') }}</flux:text>
                            <p class="mt-1 text-sm text-zinc-800 dark:text-zinc-200 leading-relaxed">{{ $user->address ?: __('Not provided') }}</p>
                        </div>

                        <div>
                            <flux:text class="font-semibold uppercase tracking-wider text-zinc-400">{{ __('Member Since') }}</flux:text>
                            <p class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>

                        <div>
                            <flux:text class="font-semibold uppercase tracking-wider text-zinc-400">{{ __('Active Loans') }}</flux:text>
                            <div class="mt-1 flex items-center gap-2">
                                <flux:badge size="sm" :color="$user->activeLoansCount() >= 3 ? 'red' : 'emerald'">
                                    {{ $user->activeLoansCount() }}/3 {{ __('books borrowed') }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Loan History & Requests (Right Column) --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Borrowing History --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg" level="2">{{ __('Loan Records') }}</flux:heading>
                        <flux:badge size="sm" color="zinc">{{ $rentLogs->count() }} {{ trans_choice('total loan|total loans', $rentLogs->count()) }}</flux:badge>
                    </div>

                    @if ($rentLogs->isNotEmpty())
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach ($rentLogs as $loan)
                                <div class="flex gap-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                                    <a href="{{ route('books.show', $loan->book) }}" class="shrink-0">
                                        <img
                                            src="{{ $loan->book->cover_url }}"
                                            alt="{{ $loan->book->title }}"
                                            class="h-24 w-16 rounded-lg object-cover border border-zinc-200 shadow-xs dark:border-zinc-700"
                                            onerror="this.onerror=null;this.src='{{ asset('images/placeholder-book.svg') }}';"
                                        >
                                    </a>
                                    <div class="min-w-0 flex-1 space-y-1 text-xs">
                                        <a href="{{ route('books.show', $loan->book) }}" class="block font-semibold text-sm text-zinc-900 hover:underline dark:text-white truncate" title="{{ $loan->book->title }}">
                                            {{ $loan->book->title }}
                                        </a>
                                        <div class="text-zinc-500">
                                            {{ __('Code:') }} <span class="font-mono">{{ $loan->book->book_code }}</span>
                                        </div>
                                        <div class="pt-1 text-zinc-600 dark:text-zinc-400">
                                            <span>{{ __('Rent Date:') }}</span> <strong class="font-mono text-zinc-800 dark:text-zinc-200">{{ $loan->rent_date->format('M d, Y') }}</strong>
                                        </div>
                                        <div class="text-zinc-600 dark:text-zinc-400">
                                            <span>{{ __('Return Date:') }}</span> <strong class="font-mono text-zinc-800 dark:text-zinc-200">{{ $loan->return_date->format('M d, Y') }}</strong>
                                        </div>
                                        <div class="pt-2">
                                            @if ($loan->isReturned())
                                                <flux:badge color="zinc" size="xs">
                                                    {{ __('Returned on :date', ['date' => $loan->actual_return_date?->format('M d')]) }}
                                                </flux:badge>
                                            @elseif ($loan->isOverdue())
                                                <flux:badge color="red" size="xs">{{ __('Overdue') }}</flux:badge>
                                            @else
                                                <flux:badge color="emerald" size="xs">{{ __('Currently Borrowed') }}</flux:badge>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-zinc-200 p-8 text-center dark:border-zinc-700">
                            <flux:text class="text-sm text-zinc-500">{{ __('No loan history recorded for this member.') }}</flux:text>
                        </div>
                    @endif
                </div>

                {{-- Recent Requests by User --}}
                @if ($recentRequests->isNotEmpty())
                    <div class="space-y-4 pt-4">
                        <flux:heading size="md" level="3">{{ __('Recent Requests') }}</flux:heading>
                        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                            <table class="w-full text-left text-xs text-zinc-600 dark:text-zinc-300">
                                <thead class="border-b border-zinc-200 bg-zinc-50/75 uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-4 py-3">{{ __('Book') }}</th>
                                        <th class="px-4 py-3">{{ __('Type') }}</th>
                                        <th class="px-4 py-3">{{ __('Date') }}</th>
                                        <th class="px-4 py-3">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                    @foreach ($recentRequests as $req)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $req->book->title }}</td>
                                            <td class="px-4 py-3 capitalize">{{ $req->type }}</td>
                                            <td class="px-4 py-3 font-mono">{{ $req->request_date?->format('M d, Y') }}</td>
                                            <td class="px-4 py-3">
                                                @if ($req->isPending())
                                                    <flux:badge size="xs" color="amber">{{ __('Pending') }}</flux:badge>
                                                @elseif ($req->isAccepted())
                                                    <flux:badge size="xs" color="emerald">{{ __('Accepted') }}</flux:badge>
                                                @else
                                                    <flux:badge size="xs" color="red">{{ __('Rejected') }}</flux:badge>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>

