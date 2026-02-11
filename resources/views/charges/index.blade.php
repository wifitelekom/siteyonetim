@extends('layouts.app')
@section('title', 'Tahakkuklar')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Tahakkuk Listesi</h1>
        <div class="flex items-center gap-2">
            @can('charges.create')
                <a href="{{ route('charges.create-bulk') }}" class="sy-btn-secondary">
                    <span class="material-symbols-outlined text-[18px]">library_add</span>
                    Toplu Tahakkuk
                </a>
            @endcan
            @can('charges.create')
                <a href="{{ route('charges.create') }}" class="sy-btn-primary">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Yeni Tahakkuk
                </a>
            @endcan
        </div>
    </div>

    <form method="GET" class="sy-filter-bar mb-4">
        <input type="month" name="period" value="{{ request('period') }}" class="sy-input max-w-[180px]">
        <select name="status" class="sy-input max-w-[180px]">
            <option value="">Durum: Tumu</option>
            <option value="open" @selected(request('status') === 'open')>Acik</option>
            <option value="overdue" @selected(request('status') === 'overdue')>Gecikmis</option>
            <option value="paid" @selected(request('status') === 'paid')>Odendi</option>
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Daire veya aciklama ara..." class="sy-input min-w-[220px] flex-1">
        <button type="submit" class="sy-btn-primary">
            <span class="material-symbols-outlined text-[18px]">search</span>
            Filtrele
        </button>
    </form>

    <div class="space-y-3">
        @forelse($charges as $charge)
            <article class="sy-card sy-card-hover p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                    <div class="min-w-[220px] flex-1">
                        <h2 class="text-base font-semibold text-slate-800">{{ $charge->apartment?->full_label ?? '-' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $charge->apartment && $charge->apartment->users->isNotEmpty() ? $charge->apartment->users->first()->name : 'Kullanici yok' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">{{ $charge->account?->full_name ?? '-' }}</p>
                    </div>

                    <dl class="grid grid-cols-2 gap-6 text-sm md:grid-cols-4 lg:flex-[2]">
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Donem / Vade</dt>
                            <dd class="mt-1 font-medium text-slate-700">{{ $charge->period }}</dd>
                            <dd class="text-xs text-slate-500">{{ $charge->due_date?->format('d.m.Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Tutar</dt>
                            <dd class="mt-1 font-semibold text-tabular text-slate-800">{{ number_format($charge->amount, 2, ',', '.') }} TL</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Odenen / Kalan</dt>
                            <dd class="mt-1 font-medium text-emerald-600 text-tabular">{{ number_format($charge->paid_amount, 2, ',', '.') }} TL</dd>
                            <dd class="text-xs font-semibold text-tabular {{ $charge->remaining > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                {{ number_format($charge->remaining, 2, ',', '.') }} TL
                            </dd>
                        </div>
                        <div class="flex items-center">
                            @php
                                $badgeClass = match($charge->status->value) {
                                    'paid' => 'sy-badge-paid',
                                    'overdue' => 'sy-badge-overdue',
                                    'partially_paid' => 'sy-badge-partial',
                                    default => 'sy-badge-pending',
                                };
                            @endphp
                            <span class="{{ $badgeClass }}">{{ $charge->status->label() }}</span>
                        </div>
                    </dl>

                    <div class="flex items-center gap-2 lg:ml-auto">
                        <a href="{{ route('charges.show', $charge) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-primary-50 hover:text-primary-600" title="Detay">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="sy-empty-state">
                <span class="material-symbols-outlined text-4xl text-slate-300">folder_off</span>
                <h3 class="mt-2 text-lg font-semibold text-slate-700">Kayit bulunamadi</h3>
                <p class="mt-1 text-sm text-slate-500">Filtreleri degistirip tekrar deneyin.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $charges->links() }}
    </div>
@endsection
