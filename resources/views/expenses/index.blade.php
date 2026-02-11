@extends('layouts.app')
@section('title', 'Giderler')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Gider Listesi</h1>
        @can('expenses.create')
            <a href="{{ route('expenses.create') }}" class="sy-btn-primary">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Yeni Gider
            </a>
        @endcan
    </div>

    <form method="GET" class="sy-filter-bar mb-4">
        <select name="vendor_id" class="sy-input max-w-[240px]">
            <option value="">Tedarikci: Tumu</option>
            @foreach($vendors as $vendor)
                <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
            @endforeach
        </select>

        <input type="date" name="from" value="{{ request('from') }}" class="sy-input max-w-[180px]">
        <input type="date" name="to" value="{{ request('to') }}" class="sy-input max-w-[180px]">

        <select name="status" class="sy-input max-w-[180px]">
            <option value="">Durum: Tumu</option>
            <option value="unpaid" @selected(request('status') == 'unpaid')>Odenmemis</option>
            <option value="partial" @selected(request('status') == 'partial')>Kismi</option>
            <option value="paid" @selected(request('status') == 'paid')>Odendi</option>
        </select>

        <button type="submit" class="sy-btn-primary">
            <span class="material-symbols-outlined text-[18px]">filter_list</span>
            Filtrele
        </button>
    </form>

    <div class="space-y-3">
        @forelse($expenses as $expense)
            <article class="sy-card sy-card-hover p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                    <div class="min-w-[220px] flex-1">
                        <h2 class="text-base font-semibold text-slate-800">{{ $expense->vendor?->name ?? '-' }}</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ $expense->account->full_name }}</p>
                    </div>

                    <dl class="grid grid-cols-2 gap-6 text-sm md:grid-cols-4 lg:flex-[2]">
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Tarih / Vade</dt>
                            <dd class="mt-1 font-medium text-slate-700">{{ $expense->expense_date->format('d.m.Y') }}</dd>
                            <dd class="text-xs text-slate-500">{{ $expense->due_date->format('d.m.Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Tutar</dt>
                            <dd class="mt-1 font-semibold text-tabular text-slate-800">{{ number_format($expense->amount, 2, ',', '.') }} TL</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Odenen / Kalan</dt>
                            <dd class="mt-1 font-medium text-emerald-600 text-tabular">{{ number_format($expense->paid_amount, 2, ',', '.') }} TL</dd>
                            <dd class="text-xs font-semibold text-tabular {{ $expense->remaining > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                {{ number_format($expense->remaining, 2, ',', '.') }} TL
                            </dd>
                        </div>
                        <div class="flex items-center">
                            @php
                                $badgeClass = match($expense->status->value) {
                                    'paid' => 'sy-badge-paid',
                                    'partial' => 'sy-badge-partial',
                                    default => 'sy-badge-overdue',
                                };
                            @endphp
                            <span class="{{ $badgeClass }}">{{ $expense->status->label() }}</span>
                        </div>
                    </dl>

                    <div class="flex items-center gap-2 lg:ml-auto">
                        <a href="{{ route('expenses.show', $expense) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-primary-50 hover:text-primary-600" title="Detay">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="sy-empty-state">
                <span class="material-symbols-outlined text-4xl text-slate-300">remove_shopping_cart</span>
                <h3 class="mt-2 text-lg font-semibold text-slate-700">Kayit bulunamadi</h3>
                <p class="mt-1 text-sm text-slate-500">Filtreleri degistirip tekrar deneyin.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $expenses->links() }}
    </div>
@endsection
