@extends('layouts.app')
@section('title', 'Borc Durumu')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Borc Durumu</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.debt-status.pdf') }}" target="_blank" class="sy-btn-secondary">
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
                        <th class="px-6 py-3 text-left">Daire</th>
                        <th class="px-6 py-3 text-left">Sakin</th>
                        <th class="px-6 py-3 text-right">Toplam Borc</th>
                        <th class="px-6 py-3 text-right">Odenen</th>
                        <th class="px-6 py-3 text-right">Kalan</th>
                        <th class="px-6 py-3 text-left">Durum</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($debts as $debt)
                        <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                            <td class="sy-table-cell">{{ $debt['apartment'] }}</td>
                            <td class="sy-table-cell">{{ $debt['resident'] }}</td>
                            <td class="sy-table-cell text-right text-tabular">{{ number_format($debt['total'], 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell text-right text-tabular text-emerald-600">{{ number_format($debt['paid'], 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell text-right text-tabular font-semibold text-red-600">{{ number_format($debt['remaining'], 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell">
                                @if($debt['overdue_count'] > 0)
                                    <span class="sy-badge-overdue">{{ $debt['overdue_count'] }} gecikmis</span>
                                @else
                                    <span class="sy-badge-pending">{{ $debt['open_count'] }} acik</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="sy-table-cell py-8 text-center text-slate-400">Borc kaydi yok</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($debts) > 0)
                    <tfoot>
                        <tr class="border-t border-slate-200 bg-slate-50/80">
                            <td colspan="2" class="sy-table-cell font-semibold text-slate-700">Genel Toplam</td>
                            <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format(collect($debts)->sum('total'), 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format(collect($debts)->sum('paid'), 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format(collect($debts)->sum('remaining'), 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>
@endsection
