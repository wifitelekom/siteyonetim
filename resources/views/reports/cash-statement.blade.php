@extends('layouts.app')
@section('title', 'Kasa/Banka Ekstresi')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Kasa/Banka Ekstresi</h1>
        <div class="flex items-center gap-2">
            @if(isset($data))
                <a href="{{ route('reports.cash-statement.pdf', request()->query()) }}" target="_blank" class="sy-btn-secondary">
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
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label for="cash_account_id" class="sy-label">Kasa/Banka *</label>
                <select name="cash_account_id" id="cash_account_id" class="sy-input" required>
                    <option value="">Seciniz</option>
                    @foreach($cashAccounts as $ca)
                        <option value="{{ $ca->id }}" @selected(request('cash_account_id') == $ca->id)>{{ $ca->name }}</option>
                    @endforeach
                </select>
            </div>
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
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-800">{{ $data['account']->name }}</h2>
                <p class="text-xs text-slate-500">{{ request('from') }} - {{ request('to') }}</p>
            </div>
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
                            <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format($data['opening_balance'], 2, ',', '.') }} TL</td>
                        </tr>
                        @foreach($data['transactions'] as $mov)
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
                            <td class="sy-table-cell text-right font-semibold text-tabular text-slate-800">{{ number_format($data['closing_balance'], 2, ',', '.') }} TL</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
