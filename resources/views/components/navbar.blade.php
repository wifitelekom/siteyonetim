<header class="sticky top-0 z-20 h-16 border-b border-slate-200/80 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-full w-full max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
        <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700 lg:hidden"
            @click="sidebarOpen = true"
            aria-label="Menuyu ac"
        >
            <span class="material-symbols-outlined text-[18px]">menu</span>
        </button>

        <div class="relative hidden w-full max-w-md md:block">
            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-slate-400">search</span>
            <input
                id="globalSearch"
                type="text"
                placeholder="Daire, kisi veya islem ara..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-9 pr-3 text-[13px] text-slate-700 outline-none transition-colors placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-2 focus:ring-primary-500/20"
            >
            <div id="searchResults" class="absolute top-full z-20 mt-2 hidden w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"></div>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <button
                type="button"
                class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700"
                aria-label="Bildirimler"
            >
                <span class="material-symbols-outlined text-[18px]">notifications</span>
                <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500"></span>
            </button>

            <div x-data="{ open: false }" class="relative">
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-50"
                    @click="open = !open"
                    @keydown.escape.window="open = false"
                >
                    <img
                        alt="Kullanici"
                        class="h-6 w-6 rounded-full ring-1 ring-slate-200"
                        src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0f766e&color=ffffff"
                    >
                    <span class="hidden sm:inline">Site Yonetici</span>
                    <span class="material-symbols-outlined text-[16px] text-slate-400">expand_more</span>
                </button>

                <div
                    x-cloak
                    x-show="open"
                    x-transition
                    @click.outside="open = false"
                    class="absolute right-0 z-30 mt-2 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                >
                    <div class="border-b border-slate-100 px-3 py-2.5">
                        <p class="truncate text-xs font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                    </div>

                    <a
                        href="{{ route('profile.password') }}"
                        class="flex items-center gap-2 px-3 py-2 text-xs text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-800"
                    >
                        <span class="material-symbols-outlined text-[16px]">lock</span>
                        Sifre Degistir
                    </a>

                    <button
                        type="button"
                        onclick="document.getElementById('logout-form').submit();"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-xs text-red-600 transition-colors hover:bg-red-50"
                    >
                        <span class="material-symbols-outlined text-[16px]">logout</span>
                        Cikis Yap
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
