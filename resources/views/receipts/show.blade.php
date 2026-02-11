@extends('layouts.app')
@section('title', 'Tahsilat Detay')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Tahsilat Detay</h1>
        <div class="flex items-center gap-2">
            @can('receipts.print')
                <a href="{{ route('receipts.pdf', $receipt) }}" target="_blank" class="sy-btn-secondary">
                    <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                    PDF
                </a>
            @endcan
            <a href="{{ route('receipts.index') }}" class="sy-btn-ghost">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Geri
            </a>
        </div>
    </div>

    <section class="sy-card mb-4 p-6">
        <h2 class="mb-4 text-lg font-semibold text-slate-800">Makbuz Bilgileri</h2>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Makbuz No</dt>
                <dd class="mt-1 text-sm font-medium text-slate-700">{{ $receipt->receipt_no }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Daire</dt>
                <dd class="mt-1 text-sm font-medium text-slate-700">{{ $receipt->apartment->full_label }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Odeme Tarihi</dt>
                <dd class="mt-1 text-sm font-medium text-slate-700">{{ $receipt->paid_at->format('d.m.Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Odeme Yontemi</dt>
                <dd class="mt-1 text-sm font-medium text-slate-700">{{ $receipt->method->label() }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kasa/Banka</dt>
                <dd class="mt-1 text-sm font-medium text-slate-700">{{ $receipt->cashAccount->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Toplam Tutar</dt>
                <dd class="mt-1 text-sm font-semibold text-tabular text-emerald-600">{{ number_format($receipt->total_amount, 2, ',', '.') }} TL</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aciklama</dt>
                <dd class="mt-1 text-sm text-slate-600">{{ $receipt->description ?? '-' }}</dd>
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
                        <th class="px-6 py-3 text-left">Hesap</th>
                        <th class="px-6 py-3 text-left">Donem</th>
                        <th class="px-6 py-3 text-right">Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipt->items as $item)
                        <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                            <td class="sy-table-cell">{{ $item->charge->account->full_name }}</td>
                            <td class="sy-table-cell">{{ $item->charge->period }}</td>
                            <td class="sy-table-cell text-right text-tabular">{{ number_format($item->amount, 2, ',', '.') }} TL</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200 bg-slate-50/80">
                        <td colspan="2" class="sy-table-cell font-semibold text-slate-700">Toplam</td>
                        <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format($receipt->total_amount, 2, ',', '.') }} TL</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
@endsection
