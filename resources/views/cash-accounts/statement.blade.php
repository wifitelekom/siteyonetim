@extends('layouts.app')
@section('title', 'Ekstre - ' . $account->name)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Ekstre: {{ $account->name }}</h1>
        <a href="{{ route('cash-accounts.index') }}" class="sy-btn-ghost">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Geri
        </a>
    </div>

    <section class="sy-card mb-4 p-6">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="sy-label">Baslangic</label>
                <input type="date" name="from" value="{{ request('from', $from) }}" class="sy-input">
            </div>
            <div>
                <label class="sy-label">Bitis</label>
                <input type="date" name="to" value="{{ request('to', $to) }}" class="sy-input">
            </div>
            <div class="flex items-end">
                <button type="submit" class="sy-btn-primary w-full">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    Sorgula
                </button>
            </div>
        </form>
    </section>

    <section class="sy-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="sy-table-head">
                    <tr>
                        <th class="px-6 py-3 text-left">Tarih</th>
                        <th class="px-6 py-3 text-left">Aciklama</th>
                        <th class="px-6 py-3 text-right">Giris</th>
                        <th class="px-6 py-3 text-right">Cikis</th>
                        <th class="px-6 py-3 text-right">Bakiye</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-slate-200/70 bg-slate-50/80">
                        <td colspan="4" class="sy-table-cell font-semibold text-slate-700">Acilis Bakiyesi</td>
                        <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format($opening_balance, 2, ',', '.') }} TL</td>
                    </tr>
                    @foreach($transactions as $mov)
                        <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                            <td class="sy-table-cell">{{ $mov['date'] }}</td>
                            <td class="sy-table-cell">{{ $mov['description'] }}</td>
                            <td class="sy-table-cell text-right text-tabular text-emerald-600">
                                {{ $mov['direction'] === 'in' ? number_format($mov['amount'], 2, ',', '.') . ' TL' : '' }}
                            </td>
                            <td class="sy-table-cell text-right text-tabular text-red-600">
                                {{ $mov['direction'] === 'out' ? number_format($mov['amount'], 2, ',', '.') . ' TL' : '' }}
                            </td>
                            <td class="sy-table-cell text-right text-tabular font-semibold text-slate-800">{{ number_format($mov['balance'], 2, ',', '.') }} TL</td>
                        </tr>
                    @endforeach
                    <tr class="border-t border-slate-200/70 bg-slate-50/80">
                        <td colspan="4" class="sy-table-cell font-semibold text-slate-700">Kapanis Bakiyesi</td>
                        <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format($closing_balance, 2, ',', '.') }} TL</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection
