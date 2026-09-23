<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ auth()->check() ? route('dashboard') : route('books.index') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Library')" class="grid">
                    <flux:sidebar.item icon="book-open-text" :href="route('books.index')" :current="request()->routeIs('books.index') || request()->routeIs('home')" wire:navigate>
                        {{ __('Books Catalog') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="folder" :href="route('categories.index')" :current="request()->routeIs('categories.*')" wire:navigate>
                        {{ __('Categories') }}
                    </flux:sidebar.item>
                    @if (auth()->check() && auth()->user()->isAdmin())
                        <flux:sidebar.item icon="plus" :href="route('books.create')" :current="request()->routeIs('books.create')" wire:navigate>
                            {{ __('Add New Book') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                @auth
                    @if (auth()->user()->isAdmin())
                        <flux:sidebar.group :heading="__('Administration')" class="grid mt-4">
                            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                                {{ __('Dashboard') }}
                            </flux:sidebar.item>
                            @php
                                $pendingRequestsCount = \App\Models\BookRequest::pending()->count();
                            @endphp
                            <flux:sidebar.item icon="inbox-arrow-down" :href="route('requests.index')" :current="request()->routeIs('requests.index')" wire:navigate>
                                <span>{{ __('Requests') }}</span>
                                @if ($pendingRequestsCount > 0)
                                    <flux:badge size="xs" color="amber" class="ml-auto">{{ $pendingRequestsCount }}</flux:badge>
                                @endif
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="arrow-path" :href="route('loans.index')" :current="request()->routeIs('loans.*')" wire:navigate>
                                {{ __('Loans Management') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                                {{ __('Member Users') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @else
                        <flux:sidebar.group :heading="__('Member Services')" class="grid mt-4">
                            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                                {{ __('Dashboard') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="clock" :href="route('requests.my')" :current="request()->routeIs('requests.my')" wire:navigate>
                                {{ __('My Requests') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="arrow-path" :href="route('loans.index')" :current="request()->routeIs('loans.*')" wire:navigate>
                                {{ __('My Borrowed Books') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif
                @endauth
            </flux:sidebar.nav>

            <flux:spacer />

            @auth
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            @else
                <div class="hidden lg:flex flex-col gap-2 p-2">
                    <flux:button :href="route('login')" variant="ghost" class="w-full justify-start" wire:navigate>
                        {{ __('Log in') }}
                    </flux:button>
                    @if (Route::has('register'))
                        <flux:button :href="route('register')" variant="primary" class="w-full justify-start" wire:navigate>
                            {{ __('Register') }}
                        </flux:button>
                    @endif
                </div>
            @endauth
        </flux:sidebar>

        <!-- Mobile Header & User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar
                                        :name="auth()->user()->name"
                                        :initials="auth()->user()->initials()"
                                    />

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                {{ __('Settings') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer"
                                data-test="logout-button"
                            >
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @else
                <div class="flex items-center gap-2">
                    <flux:button :href="route('login')" size="sm" variant="ghost" wire:navigate>
                        {{ __('Log in') }}
                    </flux:button>
                    @if (Route::has('register'))
                        <flux:button :href="route('register')" size="sm" variant="primary" wire:navigate>
                            {{ __('Register') }}
                        </flux:button>
                    @endif
                </div>
            @endauth
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
