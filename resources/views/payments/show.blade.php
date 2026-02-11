@extends('layouts.app')
@section('title', 'Odeme Detay')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Odeme Detay</h1>
        <a href="{{ route('payments.index') }}" class="sy-btn-ghost">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Geri
        </a>
    </div>

    <section class="sy-card mb-4 p-6">
        <h2 class="mb-4 text-lg font-semibold text-slate-800">Odeme Bilgileri</h2>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tedarikci</dt>
                <dd class="mt-1 text-sm font-medium text-slate-700">{{ $payment->vendor?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Odeme Tarihi</dt>
                <dd class="mt-1 text-sm font-medium text-slate-700">{{ $payment->paid_at->format('d.m.Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Yontem</dt>
                <dd class="mt-1 text-sm font-medium text-slate-700">{{ $payment->method->label() }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kasa/Banka</dt>
                <dd class="mt-1 text-sm font-medium text-slate-700">{{ $payment->cashAccount->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Toplam Tutar</dt>
                <dd class="mt-1 text-sm font-semibold text-tabular text-red-600">{{ number_format($payment->total_amount, 2, ',', '.') }} TL</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aciklama</dt>
                <dd class="mt-1 text-sm text-slate-600">{{ $payment->description ?? '-' }}</dd>
            </div>
        </dl>
    </section>

    <section class="sy-card overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-800">Odeme Kalemleri</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="sy-table-head">
                    <tr>
                        <th class="px-6 py-3 text-left">Gider</th>
                        <th class="px-6 py-3 text-left">Hesap</th>
                        <th class="px-6 py-3 text-right">Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payment->items as $item)
                        <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                            <td class="sy-table-cell">
                                <a href="{{ route('expenses.show', $item->expense) }}" class="font-medium text-primary-600 hover:text-primary-700">
                                    {{ $item->expense->description ?? 'Gider #' . $item->expense->id }}
                                </a>
                            </td>
                            <td class="sy-table-cell">{{ $item->expense->account->full_name }}</td>
                            <td class="sy-table-cell text-right text-tabular">{{ number_format($item->amount, 2, ',', '.') }} TL</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
