<x-layouts::app :title="__('My Requests')">
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
                    <flux:heading size="xl" level="1">{{ __('My Requests') }}</flux:heading>
                    <flux:badge size="sm" color="zinc">{{ $requests->total() }} {{ trans_choice('request|requests', $requests->total()) }}</flux:badge>
                </div>
                <flux:text class="mt-1">
                    {{ __('Track the status of your book borrow and return requests.') }}
                </flux:text>
            </div>

            <flux:button :href="route('books.index')" variant="primary" icon="book-open" size="sm">
                {{ __('Browse Catalog') }}
            </flux:button>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-2">
            <flux:button :href="route('requests.my')" size="sm" :variant="empty($currentStatus) ? 'filled' : 'ghost'">
                {{ __('All Requests') }}
            </flux:button>
            <flux:button :href="route('requests.my', ['status' => 'pending'])" size="sm" :variant="$currentStatus === 'pending' ? 'filled' : 'ghost'">
                {{ __('Pending') }} @if ($pendingCount > 0) ({{ $pendingCount }}) @endif
            </flux:button>
            <flux:button :href="route('requests.my', ['status' => 'accepted'])" size="sm" :variant="$currentStatus === 'accepted' ? 'filled' : 'ghost'">
                {{ __('Accepted') }}
            </flux:button>
            <flux:button :href="route('requests.my', ['status' => 'rejected'])" size="sm" :variant="$currentStatus === 'rejected' ? 'filled' : 'ghost'">
                {{ __('Rejected') }}
            </flux:button>
        </div>

        {{-- Requests Table --}}
        @if ($requests->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-300">
                        <thead class="border-b border-zinc-200 bg-zinc-50/75 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">{{ __('No') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Book Title') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Request Type') }}</th>
                                <th scope="col" class="px-6 py-3.5">{{ __('Submitted At') }}</th>
                                <th scope="col" class="px-6 py-3.5 text-right">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($requests as $index => $req)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs text-zinc-400">
                                        {{ $requests->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('books.show', $req->book) }}" class="shrink-0">
                                                <img
                                                    src="{{ $req->book->cover_url }}"
                                                    alt="{{ $req->book->title }}"
                                                    class="h-12 w-9 rounded object-cover border border-zinc-200 shadow-xs dark:border-zinc-700"
                                                >
                                            </a>
                                            <div>
                                                <a href="{{ route('books.show', $req->book) }}" class="font-medium text-zinc-900 hover:underline dark:text-white">
                                                    {{ $req->book->title }}
                                                </a>
                                                <div class="text-xs text-zinc-500">
                                                    {{ $req->book->book_code }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($req->type === 'loan')
                                            <flux:badge size="sm" color="blue">{{ __('Borrow Request') }}</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="purple">{{ __('Return Request') }}</flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-zinc-700 dark:text-zinc-300">
                                        {{ $req->request_date?->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if ($req->isPending())
                                            <flux:badge size="sm" color="amber">{{ __('Awaiting Admin Approval') }}</flux:badge>
                                        @elseif ($req->isAccepted())
                                            <flux:badge size="sm" color="emerald">{{ __('Accepted') }}</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="red">{{ __('Rejected') }}</flux:badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-2">
                {{ $requests->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-200 p-12 text-center dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/40">
                <flux:icon name="clock" class="size-10 text-zinc-400" />
                <flux:heading size="lg" class="mt-4">{{ __('No requests submitted') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500 max-w-sm">
                    {{ __('When you request to borrow or return library books, your pending and completed requests will show up here.') }}
                </flux:text>
                <div class="mt-6">
                    <flux:button :href="route('books.index')" variant="primary" icon="book-open" size="sm">
                        {{ __('Discover Books to Borrow') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>

