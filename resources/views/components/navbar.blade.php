<nav class="navbar navbar-expand-lg border-bottom shadow-sm">
    <div class="container-fluid">
        <button class="btn btn-sm btn-outline-secondary d-md-none" id="menu-toggle">
            <i class="bi bi-list"></i>
        </button>

        <span class="navbar-brand ms-2 d-none d-md-block fw-semibold" style="color: var(--sy-text);">
            {{ auth()->user()->site?->name ?? 'Site Yönetimi' }}
        </span>

        {{-- Global Search --}}
        <div class="navbar-search d-none d-md-block mx-auto">
            <i class="bi bi-search search-icon"></i>
            <input type="text" class="form-control" id="globalSearch"
                placeholder="Daire, kullanıcı veya tahsilat ara..." autocomplete="off">
            <div class="search-results" id="searchResults"></div>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            {{-- Theme Toggle --}}
            <button class="theme-toggle" id="themeToggle" title="Tema Değiştir">
                <i class="bi bi-sun-fill" id="themeIcon"></i>
            </button>

            {{-- User Dropdown --}}
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" role="button"
                    data-bs-toggle="dropdown" style="color: var(--sy-text);">
                    <i class="bi bi-person-circle"></i>
                    <span class="d-none d-lg-inline">{{ auth()->user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span
                            class="dropdown-item-text text-muted small">{{ auth()->user()->roles->first()?->name ?? '' }}</span>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    @role('admin')
                    <li>
                        <a class="dropdown-item" href="{{ route('management.site-settings.edit') }}">
                            <i class="bi bi-gear"></i> Site Ayarları
                        </a>
                    </li>
                    @endrole
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.password') }}">
                            <i class="bi bi-key"></i> Şifre Değiştir
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right"></i> Çıkış Yap
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>