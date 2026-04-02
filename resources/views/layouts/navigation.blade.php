<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @if (Auth::user()->role === 'admin_up3')
                        <a href="{{ route('daily-stock') }}">
                            <img src="{{ asset('images/logo-pln.png') }}" class="block h-9 w-auto" alt="Logo PLN">
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}">
                            <img src="{{ asset('images/logo-pln.png') }}" class="block h-9 w-auto" alt="Logo PLN">
                        </a>
                    @endif
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                    @if (Auth::user()->role === 'admin_up3')
                        <x-nav-link :href="route('daily-stock')" :active="request()->routeIs('daily-stock')">
                            {{ __('Input Stok Harian') }}
                        </x-nav-link>
                        <x-nav-link :href="route('manage.warehouse-products')" :active="request()->routeIs('manage.warehouse-products')">
                            {{ __('Kelola Stok') }}
                        </x-nav-link>
                        <x-nav-link :href="route('manage.orders')" :active="request()->routeIs('manage.orders')">
                            {{ __('Pemesanan') }}
                        </x-nav-link>
                    @elseif (Auth::user()->role === 'rent')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('manage.orders')" :active="request()->routeIs('manage.orders')">
                            {{ __('Pemesanan') }}
                        </x-nav-link>
                    @elseif (Auth::user()->role === 'manager')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->role === 'admin_uid')
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false" class="inline-flex items-center gap-1 px-1 pt-1 pb-1 h-full text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none border-b-2 {{ request()->routeIs('manage.*') ? 'text-gray-900 border-teal-400' : 'text-gray-500 hover:text-gray-700 border-transparent hover:border-gray-300' }}">
                                Kelola
                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute left-0 z-50 mt-0 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black/5 py-1" style="display: none;">
                                <a href="{{ route('manage.products') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('manage.products') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">Produk</a>
                                <a href="{{ route('manage.warehouses') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('manage.warehouses') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">Gudang</a>
                                <a href="{{ route('manage.warehouse-products') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('manage.warehouse-products') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">Stok Gudang</a>
                                <a href="{{ route('manage.orders') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('manage.orders') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">Pemesanan</a>
                                <a href="{{ route('manage.users') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('manage.users') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">User</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Role Badge -->
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mr-3
                    {{ Auth::user()->role === 'admin_uid' ? 'bg-cyan-100 text-cyan-700' : '' }}
                    {{ Auth::user()->role === 'admin_up3' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ Auth::user()->role === 'rent' ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ Auth::user()->role === 'manager' ? 'bg-teal-100 text-teal-700' : '' }}
                ">
                    {{ strtoupper(str_replace('_', ' ', Auth::user()->role)) }}
                </span>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if (Auth::user()->role === 'admin_up3')
                <x-responsive-nav-link :href="route('daily-stock')" :active="request()->routeIs('daily-stock')">
                    {{ __('Input Stok Harian') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manage.warehouse-products')" :active="request()->routeIs('manage.warehouse-products')">
                    {{ __('Kelola Stok') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manage.orders')" :active="request()->routeIs('manage.orders')">
                    {{ __('Pemesanan') }}
                </x-responsive-nav-link>
            @elseif (Auth::user()->role === 'rent')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manage.orders')" :active="request()->routeIs('manage.orders')">
                    {{ __('Pemesanan') }}
                </x-responsive-nav-link>
            @elseif (Auth::user()->role === 'manager')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->role === 'admin_uid')
                <div class="border-t border-gray-200 mt-1 pt-1">
                    <p class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase">Kelola</p>
                    <x-responsive-nav-link :href="route('manage.products')" :active="request()->routeIs('manage.products')">Produk</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('manage.warehouses')" :active="request()->routeIs('manage.warehouses')">Gudang</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('manage.warehouse-products')" :active="request()->routeIs('manage.warehouse-products')">Stok Gudang</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('manage.orders')" :active="request()->routeIs('manage.orders')">Pemesanan</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('manage.users')" :active="request()->routeIs('manage.users')">User</x-responsive-nav-link>
                </div>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
