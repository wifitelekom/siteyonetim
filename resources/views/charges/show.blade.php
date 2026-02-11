@extends('layouts.app')
@section('title', 'Tahakkuk Detay')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Tahakkuk Detay</h1>
        <a href="{{ route('charges.index') }}" class="sy-btn-ghost">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Geri
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <section class="sy-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">Tahakkuk Bilgileri</h2>
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

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Daire</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $charge->apartment->full_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hesap</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $charge->account->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tur</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $charge->charge_type->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Donem</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $charge->period }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Vade Tarihi</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $charge->due_date->format('d.m.Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tutar</dt>
                        <dd class="mt-1 text-sm font-semibold text-tabular text-slate-800">{{ number_format($charge->amount, 2, ',', '.') }} TL</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Odenen</dt>
                        <dd class="mt-1 text-sm font-semibold text-tabular text-emerald-600">{{ number_format($charge->paid_amount, 2, ',', '.') }} TL</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kalan</dt>
                        <dd class="mt-1 text-sm font-semibold text-tabular text-red-600">{{ number_format($charge->remaining, 2, ',', '.') }} TL</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aciklama</dt>
                        <dd class="mt-1 text-sm text-slate-600">{{ $charge->description ?? '-' }}</dd>
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
                                <th class="px-6 py-3 text-left">Makbuz No</th>
                                <th class="px-6 py-3 text-left">Yontem</th>
                                <th class="px-6 py-3 text-left">Kasa/Banka</th>
                                <th class="px-6 py-3 text-right">Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($charge->receiptItems as $item)
                                <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                                    <td class="sy-table-cell">{{ $item->receipt->paid_at->format('d.m.Y') }}</td>
                                    <td class="sy-table-cell">
                                        <a href="{{ route('receipts.show', $item->receipt) }}" class="font-medium text-primary-600 hover:text-primary-700">
                                            {{ $item->receipt->receipt_no }}
                                        </a>
                                    </td>
                                    <td class="sy-table-cell">{{ $item->receipt->method->label() }}</td>
                                    <td class="sy-table-cell">{{ $item->receipt->cashAccount->name }}</td>
                                    <td class="sy-table-cell text-right text-tabular">{{ number_format($item->amount, 2, ',', '.') }} TL</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="sy-table-cell py-8 text-center text-slate-400">Henuz odeme yok</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-4">
            @can('charges.collect')
                @if($charge->remaining > 0)
                    <section class="sy-card p-6">
                        <h2 class="mb-4 text-lg font-semibold text-slate-800">Tahsilat Al</h2>
                        <form method="POST" action="{{ route('charges.collect', $charge) }}" class="space-y-4">
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
                                <label for="cash_account_id" class="sy-label">Kasa/Banka *</label>
                                <select id="cash_account_id" name="cash_account_id" class="sy-input" required>
                                    @foreach($cashAccounts as $ca)
                                        <option value="{{ $ca->id }}">{{ $ca->name }} ({{ $ca->type->label() }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="amount" class="sy-label">Tutar (TL) *</label>
                                <input type="number" step="0.01" min="0.01" max="{{ $charge->remaining }}" id="amount" name="amount" value="{{ $charge->remaining }}" class="sy-input" required>
                                <p class="mt-1 text-xs text-slate-500">Kalan: {{ number_format($charge->remaining, 2, ',', '.') }} TL</p>
                            </div>
                            <div>
                                <label for="description" class="sy-label">Aciklama</label>
                                <input type="text" id="description" name="description" class="sy-input">
                            </div>
                            <button type="submit" class="sy-btn-primary w-full">
                                <span class="material-symbols-outlined text-[18px]">payments</span>
                                Tahsilat Al
                            </button>
                        </form>
                    </section>
                @endif
            @endcan

            @can('charges.delete')
                @if($charge->paid_amount == 0)
                    <section class="sy-card border border-red-200 bg-red-50/40 p-6 text-center">
                        <h3 class="text-sm font-semibold text-red-700">Tahakkuku Sil</h3>
                        <p class="mt-2 text-xs text-red-600">Bu islem geri alinamaz.</p>
                        <button type="button" data-delete-action="{{ route('charges.destroy', $charge) }}" class="mt-4 sy-btn-danger w-full">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                            Sil
                        </button>
                    </section>
                @endif
            @endcan
        </aside>
    </div>
@endsection
