@extends('layouts.app')
@section('title', 'Tahsilat Raporu')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Tahsilat Raporu</h1>
        <div class="flex items-center gap-2">
            @if(isset($data))
                <a href="{{ route('reports.collections.pdf', request()->query()) }}" target="_blank" class="sy-btn-secondary">
                    <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                    PDF
                </a>
            @endif
            <a href="{{ route('reports.index') }}" class="sy-btn-ghost">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Raporlar
            </a>
        </div>
    </div>

    <section class="sy-card mb-4 p-6">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="from" class="sy-label">Baslangic</label>
                <input type="date" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}" class="sy-input">
            </div>
            <div>
                <label for="to" class="sy-label">Bitis</label>
                <input type="date" name="to" value="{{ request('to', now()->format('Y-m-d')) }}" class="sy-input">
            </div>
            <div class="flex items-end">
                <button type="submit" class="sy-btn-primary w-full">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    Sorgula
                </button>
            </div>
        </form>
    </section>

    @if(isset($data))
        <section class="sy-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="sy-table-head">
                        <tr>
                            <th class="px-6 py-3 text-left">Makbuz No</th>
                            <th class="px-6 py-3 text-left">Tarih</th>
                            <th class="px-6 py-3 text-left">Daire</th>
                            <th class="px-6 py-3 text-left">Yontem</th>
                            <th class="px-6 py-3 text-left">Kasa/Banka</th>
                            <th class="px-6 py-3 text-right">Tutar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['receipts'] as $receipt)
                            <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                                <td class="sy-table-cell">{{ $receipt->receipt_no }}</td>
                                <td class="sy-table-cell">{{ $receipt->paid_at->format('d.m.Y') }}</td>
                                <td class="sy-table-cell">{{ $receipt->apartment->full_label }}</td>
                                <td class="sy-table-cell">{{ $receipt->method->label() }}</td>
                                <td class="sy-table-cell">{{ $receipt->cashAccount->name }}</td>
                                <td class="sy-table-cell text-right text-tabular">{{ number_format($receipt->total_amount, 2, ',', '.') }} TL</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="sy-table-cell py-8 text-center text-slate-400">Tahsilat yok</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($data['receipts']->count() > 0)
                        <tfoot>
                            <tr class="border-t border-slate-200 bg-slate-50/80">
                                <td colspan="5" class="sy-table-cell font-semibold text-slate-700">Toplam</td>
                                <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format($data['receipts']->sum('total_amount'), 2, ',', '.') }} TL</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>
    @endif
@endsection
