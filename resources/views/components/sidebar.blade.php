<div id="sidebar-wrapper">
    <div class="sidebar-heading">
        <i class="bi bi-building"></i>
        <span>{{ auth()->user()->site?->name ?? 'Site Yönetimi' }}</span>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-grid-1x2"></i> Genel Bakış
        </a>

        @can('charges.view')
            <a class="nav-link {{ request()->routeIs('charges.*') ? 'active' : '' }}" href="{{ route('charges.index') }}">
                <i class="bi bi-cash-stack"></i> Aidatlar
            </a>
        @endcan

        @can('receipts.view')
            <a class="nav-link {{ request()->routeIs('receipts.*') ? 'active' : '' }}" href="{{ route('receipts.index') }}">
                <i class="bi bi-receipt"></i> Tahsilatlar
            </a>
        @endcan

        @can('expenses.view')
            <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                <i class="bi bi-cart-dash"></i> Giderler
            </a>
        @endcan

        @can('payments.view')
            <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                <i class="bi bi-credit-card"></i> Ödemeler
            </a>
        @endcan

        @can('accounts.view')
            <a class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="{{ route('accounts.index') }}">
                <i class="bi bi-journal-text"></i> Hesaplar
            </a>
        @endcan

        @can('cash-accounts.view')
            <a class="nav-link {{ request()->routeIs('cash-accounts.*') ? 'active' : '' }}"
                href="{{ route('cash-accounts.index') }}">
                <i class="bi bi-bank2"></i> Kasa / Banka
            </a>
        @endcan

        @role('admin')
        <div class="sidebar-section-label">Yönetim</div>
        <a class="nav-link {{ request()->routeIs('management.apartments.*') ? 'active' : '' }}"
            href="{{ route('management.apartments.index') }}">
            <i class="bi bi-door-open"></i> Daireler
        </a>
        <a class="nav-link {{ request()->routeIs('management.users.*') ? 'active' : '' }}"
            href="{{ route('management.users.index') }}">
            <i class="bi bi-people"></i> Kullanıcılar
        </a>
        <a class="nav-link {{ request()->routeIs('management.vendors.*') ? 'active' : '' }}"
            href="{{ route('management.vendors.index') }}">
            <i class="bi bi-truck"></i> Tedarikçiler
        </a>
        <a class="nav-link {{ request()->routeIs('management.site-settings.*') ? 'active' : '' }}"
            href="{{ route('management.site-settings.edit') }}">
            <i class="bi bi-gear"></i> Site Ayarları
        </a>

        <div class="sidebar-section-label">Şablonlar</div>
        <a class="nav-link {{ request()->routeIs('templates.aidat.*') ? 'active' : '' }}"
            href="{{ route('templates.aidat.index') }}">
            <i class="bi bi-calendar-check"></i> Aidat Şablonları
        </a>
        <a class="nav-link {{ request()->routeIs('templates.expense.*') ? 'active' : '' }}"
            href="{{ route('templates.expense.index') }}">
            <i class="bi bi-arrow-repeat"></i> Gider Şablonları
        </a>
        @endrole

        @can('reports.view')
            <div class="sidebar-section-label">Raporlar</div>
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="bi bi-bar-chart-line"></i> Raporlar
            </a>
        @endcan
    </nav>
</div>