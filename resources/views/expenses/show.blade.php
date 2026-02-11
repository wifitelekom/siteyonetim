@extends('layouts.app')
@section('title', 'Gider Detay')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Gider Detay</h1>
        <a href="{{ route('expenses.index') }}" class="sy-btn-ghost">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Geri
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <section class="sy-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">Gider Bilgileri</h2>
                    @php
                        $badgeClass = match($expense->status->value) {
                            'paid' => 'sy-badge-paid',
                            'partial' => 'sy-badge-partial',
                            default => 'sy-badge-overdue',
                        };
                    @endphp
                    <span class="{{ $badgeClass }}">{{ $expense->status->label() }}</span>
                </div>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tedarikci</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $expense->vendor?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hesap</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $expense->account->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Gider Tarihi</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $expense->expense_date->format('d.m.Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Vade Tarihi</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $expense->due_date->format('d.m.Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tutar</dt>
                        <dd class="mt-1 text-sm font-semibold text-tabular text-slate-800">{{ number_format($expense->amount, 2, ',', '.') }} TL</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Odenen</dt>
                        <dd class="mt-1 text-sm font-semibold text-tabular text-emerald-600">{{ number_format($expense->paid_amount, 2, ',', '.') }} TL</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kalan</dt>
                        <dd class="mt-1 text-sm font-semibold text-tabular text-red-600">{{ number_format($expense->remaining, 2, ',', '.') }} TL</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aciklama</dt>
                        <dd class="mt-1 text-sm text-slate-600">{{ $expense->description ?? '-' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="sy-card overflow-hidden">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Odeme Gecmisi</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="sy-table-head">
                            <tr>
                                <th class="px-6 py-3 text-left">Tarih</th>
                                <th class="px-6 py-3 text-left">Yontem</th>
                                <th class="px-6 py-3 text-left">Kasa/Banka</th>
                                <th class="px-6 py-3 text-right">Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expense->paymentItems as $item)
                                <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                                    <td class="sy-table-cell">{{ $item->payment->paid_at->format('d.m.Y') }}</td>
                                    <td class="sy-table-cell">{{ $item->payment->method->label() }}</td>
                                    <td class="sy-table-cell">{{ $item->payment->cashAccount->name }}</td>
                                    <td class="sy-table-cell text-right text-tabular">{{ number_format($item->amount, 2, ',', '.') }} TL</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="sy-table-cell py-8 text-center text-slate-400">Henuz odeme yok</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-4">
            @can('expenses.pay')
                @if($expense->remaining > 0)
                    <section class="sy-card p-6">
                        <h2 class="mb-4 text-lg font-semibold text-slate-800">Odeme Yap</h2>
                        <form method="POST" action="{{ route('expenses.pay', $expense) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="paid_at" class="sy-label">Odeme Tarihi *</label>
                                <input type="date" id="paid_at" name="paid_at" value="{{ old('paid_at', date('Y-m-d')) }}" class="sy-input @error('paid_at') border-red-300 ring-red-200 @enderror" required>
                                @error('paid_at')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="method" class="sy-label">Odeme Yontemi *</label>
                                <select id="method" name="method" class="sy-input" required>
                                    <option value="cash">Nakit</option>
                                    <option value="bank">Banka</option>
                                </select>
                            </div>
                            <div>
                                <label for="cash_account_id" class="sy-label">Kasa/Banka Hesabi *</label>
                                <select id="cash_account_id" name="cash_account_id" class="sy-input" required>
                                    @foreach($cashAccounts as $ca)
                                        <option value="{{ $ca->id }}">{{ $ca->name }} ({{ $ca->type->label() }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="amount" class="sy-label">Tutar (TL) *</label>
                                <input type="number" step="0.01" min="0.01" max="{{ $expense->remaining }}" id="amount" name="amount" value="{{ $expense->remaining }}" class="sy-input" required>
                                <p class="mt-1 text-xs text-slate-500">Kalan: {{ number_format($expense->remaining, 2, ',', '.') }} TL</p>
                            </div>
                            <div>
                                <label for="description" class="sy-label">Aciklama</label>
                                <input type="text" id="description" name="description" class="sy-input">
                            </div>
                            <button type="submit" class="sy-btn-danger w-full">
                                <span class="material-symbols-outlined text-[18px]">credit_card</span>
                                Odeme Yap
                            </button>
                        </form>
                    </section>
                @endif
            @endcan

            @can('expenses.delete')
                @if($expense->paid_amount == 0)
                    <section class="sy-card border border-red-200 bg-red-50/40 p-6 text-center">
                        <h3 class="text-sm font-semibold text-red-700">Gideri Sil</h3>
                        <p class="mt-2 text-xs text-red-600">Bu islem geri alinamaz.</p>
                        <button type="button" data-delete-action="{{ route('expenses.destroy', $expense) }}" class="mt-4 sy-btn-danger w-full">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                            Sil
                        </button>
                    </section>
                @endif
            @endcan
        </aside>
    </div>
@endsection
