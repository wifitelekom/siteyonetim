@extends('layouts.app')
@section('title', 'Alacak Durumu')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Alacak Durumu</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.receivable-status.pdf') }}" target="_blank" class="sy-btn-secondary">
                <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                PDF
            </a>
            <a href="{{ route('reports.index') }}" class="sy-btn-ghost">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Raporlar
            </a>
        </div>
    </div>

    <section class="sy-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="sy-table-head">
                    <tr>
                        <th class="px-6 py-3 text-left">Tedarikci</th>
                        <th class="px-6 py-3 text-right">Toplam Gider</th>
                        <th class="px-6 py-3 text-right">Odenen</th>
                        <th class="px-6 py-3 text-right">Kalan Borc</th>
                        <th class="px-6 py-3 text-left">Durum</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivables as $row)
                        <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                            <td class="sy-table-cell">{{ $row['vendor'] }}</td>
                            <td class="sy-table-cell text-right text-tabular">{{ number_format($row['total'], 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell text-right text-tabular text-emerald-600">{{ number_format($row['paid'], 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell text-right text-tabular font-semibold text-red-600">{{ number_format($row['remaining'], 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell">
                                @if($row['remaining'] > 0)
                                    <span class="sy-badge-overdue">Borclu</span>
                                @else
                                    <span class="sy-badge-paid">Odendi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="sy-table-cell py-8 text-center text-slate-400">Tedarikci borcu yok</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($receivables) > 0)
                    <tfoot>
                        <tr class="border-t border-slate-200 bg-slate-50/80">
                            <td class="sy-table-cell font-semibold text-slate-700">Genel Toplam</td>
                            <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format(collect($receivables)->sum('total'), 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format(collect($receivables)->sum('paid'), 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format(collect($receivables)->sum('remaining'), 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>
@endsection
