@extends('layouts.app')
@section('title', 'Tahakkuk Listesi')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Tahakkuk Listesi</h1>
        <div class="flex items-center gap-2">
            @if(isset($data))
                <a href="{{ route('reports.charge-list.pdf', request()->query()) }}" target="_blank" class="sy-btn-secondary">
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
                <label for="period" class="sy-label">Donem</label>
                <input type="month" name="period" value="{{ request('period', date('Y-m')) }}" class="sy-input">
            </div>
            <div>
                <label for="status" class="sy-label">Durum</label>
                <select name="status" class="sy-input">
                    <option value="">Tumu</option>
                    <option value="open" @selected(request('status') == 'open')>Acik</option>
                    <option value="paid" @selected(request('status') == 'paid')>Odendi</option>
                    <option value="overdue" @selected(request('status') == 'overdue')>Vadesi Gecmis</option>
                </select>
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
                            <th class="px-6 py-3 text-left">Daire</th>
                            <th class="px-6 py-3 text-left">Hesap</th>
                            <th class="px-6 py-3 text-left">Donem</th>
                            <th class="px-6 py-3 text-left">Vade</th>
                            <th class="px-6 py-3 text-right">Tutar</th>
                            <th class="px-6 py-3 text-right">Odenen</th>
                            <th class="px-6 py-3 text-right">Kalan</th>
                            <th class="px-6 py-3 text-left">Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['charges'] as $charge)
                            <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                                <td class="sy-table-cell">{{ $charge->apartment->full_label }}</td>
                                <td class="sy-table-cell">{{ $charge->account->full_name }}</td>
                                <td class="sy-table-cell">{{ $charge->period }}</td>
                                <td class="sy-table-cell">{{ $charge->due_date->format('d.m.Y') }}</td>
                                <td class="sy-table-cell text-right text-tabular">{{ number_format($charge->amount, 2, ',', '.') }} TL</td>
                                <td class="sy-table-cell text-right text-tabular text-emerald-600">{{ number_format($charge->paid_amount, 2, ',', '.') }} TL</td>
                                <td class="sy-table-cell text-right text-tabular text-red-600">{{ number_format($charge->remaining, 2, ',', '.') }} TL</td>
                                <td class="sy-table-cell">
                                    @php
                                        $badgeClass = match($charge->status->value) {
                                            'paid' => 'sy-badge-paid',
                                            'overdue' => 'sy-badge-overdue',
                                            'partially_paid' => 'sy-badge-partial',
                                            default => 'sy-badge-pending',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }}">{{ $charge->status->label() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="sy-table-cell py-8 text-center text-slate-400">Tahakkuk yok</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($data['charges']->count() > 0)
                        <tfoot>
                            <tr class="border-t border-slate-200 bg-slate-50/80">
                                <td colspan="4" class="sy-table-cell font-semibold text-slate-700">Toplam</td>
                                <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format($data['charges']->sum('amount'), 2, ',', '.') }} TL</td>
                                <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format($data['charges']->sum('paid_amount'), 2, ',', '.') }} TL</td>
                                <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format($data['charges']->sum('remaining'), 2, ',', '.') }} TL</td>
                                <td class="sy-table-cell"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>
    @endif
@endsection
