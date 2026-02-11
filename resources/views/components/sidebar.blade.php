@php
    $linkBase = 'group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-colors';
    $linkIdle = 'text-slate-600 hover:bg-slate-100 hover:text-slate-800';
    $linkActive = 'bg-teal-50 text-teal-700';
    $iconBase = 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-[20px]';
    $iconIdle = 'text-slate-400 group-hover:text-slate-600';
    $iconActive = 'text-teal-600';
    $sectionLabel = 'px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400';
@endphp

<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity
    class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
    @click="sidebarOpen = false"
></div>

<aside
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col overflow-hidden border-r border-slate-200 bg-[#f8fafc] shadow-sm transition-transform duration-300 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    @keydown.escape.window="sidebarOpen = false"
>
    <div class="flex h-20 items-center justify-between border-b border-slate-200 px-4">
        @php
            $siteLabel = auth()->user()->hasRole('super-admin')
                ? 'Super Admin'
                : (auth()->user()->site?->name ?? 'Site Yonetimi');
        @endphp
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3" @click="sidebarOpen = false">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-700 text-white">
                <span class="material-symbols-outlined text-[20px]">apartment</span>
            </span>
            <span class="truncate text-xl font-semibold text-slate-800">{{ $siteLabel }}</span>
        </a>

        <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 lg:hidden" @click="sidebarOpen = false">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-3">
        @role('super-admin')
            <section class="space-y-0 pb-2">
                <p class="{{ $sectionLabel }}">Sistem</p>
                <a
                    href="{{ route('super.sites.index') }}"
                    @click="sidebarOpen = false"
                    class="{{ $linkBase }} {{ request()->routeIs('super.sites.*') ? $linkActive : $linkIdle }}"
                >
                    <span class="{{ $iconBase }} {{ request()->routeIs('super.sites.*') ? $iconActive : $iconIdle }}">
                        <span class="material-symbols-outlined">domain</span>
                    </span>
                    <span>Siteler</span>
                </a>
            </section>
        @endrole

        <section class="space-y-0">
            <a
                href="{{ route('dashboard') }}"
                @click="sidebarOpen = false"
                class="{{ $linkBase }} {{ request()->routeIs('dashboard') ? $linkActive : $linkIdle }}"
            >
                <span class="{{ $iconBase }} {{ request()->routeIs('dashboard') ? $iconActive : $iconIdle }}">
                    <span class="material-symbols-outlined">dashboard</span>
                </span>
                <span>Genel Bakis</span>
            </a>

            @can('charges.view')
                <a
                    href="{{ route('charges.index') }}"
                    @click="sidebarOpen = false"
                    class="{{ $linkBase }} {{ request()->routeIs('charges.*') ? $linkActive : $linkIdle }}"
                >
                    <span class="{{ $iconBase }} {{ request()->routeIs('charges.*') ? $iconActive : $iconIdle }}">
                        <span class="material-symbols-outlined">payments</span>
                    </span>
                    <span>Aidatlar</span>
                </a>
            @endcan

            @can('receipts.view')
                <a
                    href="{{ route('receipts.index') }}"
                    @click="sidebarOpen = false"
                    class="{{ $linkBase }} {{ request()->routeIs('receipts.*') ? $linkActive : $linkIdle }}"
                >
                    <span class="{{ $iconBase }} {{ request()->routeIs('receipts.*') ? $iconActive : $iconIdle }}">
                        <span class="material-symbols-outlined">receipt_long</span>
                    </span>
                    <span>Tahsilatlar</span>
                </a>
            @endcan

            @can('expenses.view')
                <a
                    href="{{ route('expenses.index') }}"
                    @click="sidebarOpen = false"
                    class="{{ $linkBase }} {{ request()->routeIs('expenses.*') ? $linkActive : $linkIdle }}"
                >
                    <span class="{{ $iconBase }} {{ request()->routeIs('expenses.*') ? $iconActive : $iconIdle }}">
                        <span class="material-symbols-outlined">account_balance_wallet</span>
                    </span>
                    <span>Giderler</span>
                </a>
            @endcan

            @can('payments.view')
                <a
                    href="{{ route('payments.index') }}"
                    @click="sidebarOpen = false"
                    class="{{ $linkBase }} {{ request()->routeIs('payments.*') ? $linkActive : $linkIdle }}"
                >
                    <span class="{{ $iconBase }} {{ request()->routeIs('payments.*') ? $iconActive : $iconIdle }}">
                        <span class="material-symbols-outlined">credit_card</span>
                    </span>
                    <span>Odemeler</span>
                </a>
            @endcan

            @can('accounts.view')
                <a
                    href="{{ route('accounts.index') }}"
                    @click="sidebarOpen = false"
                    class="{{ $linkBase }} {{ request()->routeIs('accounts.*') ? $linkActive : $linkIdle }}"
                >
                    <span class="{{ $iconBase }} {{ request()->routeIs('accounts.*') ? $iconActive : $iconIdle }}">
                        <span class="material-symbols-outlined">account_balance</span>
                    </span>
                    <span>Hesaplar</span>
                </a>
            @endcan

            @can('cash-accounts.view')
                <a
                    href="{{ route('cash-accounts.index') }}"
                    @click="sidebarOpen = false"
                    class="{{ $linkBase }} {{ request()->routeIs('cash-accounts.*') ? $linkActive : $linkIdle }}"
                >
                    <span class="{{ $iconBase }} {{ request()->routeIs('cash-accounts.*') ? $iconActive : $iconIdle }}">
                        <span class="material-symbols-outlined">savings</span>
                    </span>
                    <span>Kasa / Banka</span>
                </a>
            @endcan
        </section>

        @role('admin')
            <section>
                <p class="{{ $sectionLabel }}">Yonetim</p>

                <div class="space-y-0">
                    <a
                        href="{{ route('management.apartments.index') }}"
                        @click="sidebarOpen = false"
                        class="{{ $linkBase }} {{ request()->routeIs('management.apartments.*') ? $linkActive : $linkIdle }}"
                    >
                        <span class="{{ $iconBase }} {{ request()->routeIs('management.apartments.*') ? $iconActive : $iconIdle }}">
                            <span class="material-symbols-outlined">meeting_room</span>
                        </span>
                        <span>Daireler</span>
                    </a>

                    <a
                        href="{{ route('management.users.index') }}"
                        @click="sidebarOpen = false"
                        class="{{ $linkBase }} {{ request()->routeIs('management.users.*') ? $linkActive : $linkIdle }}"
                    >
                        <span class="{{ $iconBase }} {{ request()->routeIs('management.users.*') ? $iconActive : $iconIdle }}">
                            <span class="material-symbols-outlined">group</span>
                        </span>
                        <span>Kullanicilar</span>
                    </a>

                    <a
                        href="{{ route('management.vendors.index') }}"
                        @click="sidebarOpen = false"
                        class="{{ $linkBase }} {{ request()->routeIs('management.vendors.*') ? $linkActive : $linkIdle }}"
                    >
                        <span class="{{ $iconBase }} {{ request()->routeIs('management.vendors.*') ? $iconActive : $iconIdle }}">
                            <span class="material-symbols-outlined">local_shipping</span>
                        </span>
                        <span>Tedarikciler</span>
                    </a>

                    <a
                        href="{{ route('management.site-settings.edit') }}"
                        @click="sidebarOpen = false"
                        class="{{ $linkBase }} {{ request()->routeIs('management.site-settings.*') ? $linkActive : $linkIdle }}"
                    >
                        <span class="{{ $iconBase }} {{ request()->routeIs('management.site-settings.*') ? $iconActive : $iconIdle }}">
                            <span class="material-symbols-outlined">settings</span>
                        </span>
                        <span>Ayarlar</span>
                    </a>
                </div>
            </section>

            <section>
                <p class="{{ $sectionLabel }}">Sablonlar</p>

                <div class="space-y-0">
                    <a
                        href="{{ route('templates.aidat.index') }}"
                        @click="sidebarOpen = false"
                        class="{{ $linkBase }} {{ request()->routeIs('templates.aidat.*') ? $linkActive : $linkIdle }}"
                    >
                        <span class="{{ $iconBase }} {{ request()->routeIs('templates.aidat.*') ? $iconActive : $iconIdle }}">
                            <span class="material-symbols-outlined">event_repeat</span>
                        </span>
                        <span>Aidat Sablonlari</span>
                    </a>

                    <a
                        href="{{ route('templates.expense.index') }}"
                        @click="sidebarOpen = false"
                        class="{{ $linkBase }} {{ request()->routeIs('templates.expense.*') ? $linkActive : $linkIdle }}"
                    >
                        <span class="{{ $iconBase }} {{ request()->routeIs('templates.expense.*') ? $iconActive : $iconIdle }}">
                            <span class="material-symbols-outlined">repeat</span>
                        </span>
                        <span>Gider Sablonlari</span>
                    </a>
                </div>
            </section>
        @endrole

        @can('reports.view')
            <section>
                <p class="{{ $sectionLabel }}">Raporlar</p>

                <div class="space-y-0">
                    <a
                        href="{{ route('reports.index') }}"
                        @click="sidebarOpen = false"
                        class="{{ $linkBase }} {{ request()->routeIs('reports.*') ? $linkActive : $linkIdle }}"
                    >
                        <span class="{{ $iconBase }} {{ request()->routeIs('reports.*') ? $iconActive : $iconIdle }}">
                            <span class="material-symbols-outlined">bar_chart</span>
                        </span>
                        <span>Raporlar</span>
                    </a>
                </div>
            </section>
        @endcan
    </nav>

    <div class="border-t border-slate-200 p-4">
        <button
            type="button"
            class="group flex w-full items-center gap-3 rounded-2xl bg-slate-100 p-3 text-left transition-colors hover:bg-slate-200"
            onclick="document.getElementById('logout-form').submit();"
            title="Cikis Yap"
        >
            <img
                alt="Kullanici"
                class="h-10 w-10 rounded-full ring-2 ring-white"
                src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0f766e&color=ffffff"
            >
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ auth()->user()->roles->first()?->name ?? 'Kullanici' }}</p>
            </div>
            <span class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-slate-600">logout</span>
        </button>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</aside>
