@extends('layouts.app')
@section('title', 'Hesap Ekstresi')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Hesap Ekstresi</h1>
        <div class="flex items-center gap-2">
            @if(isset($data))
                <a href="{{ route('reports.account-statement.pdf', request()->query()) }}" target="_blank" class="sy-btn-secondary">
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
                <label for="account_id" class="sy-label">Hesap *</label>
                <select name="account_id" id="account_id" class="sy-input" required>
                    <option value="">Seciniz</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected(request('account_id') == $acc->id)>{{ $acc->full_name }}</option>
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
                <h2 class="text-lg font-semibold text-slate-800">{{ $data['account']->full_name }}</h2>
                <p class="text-xs text-slate-500">{{ request('from') }} - {{ request('to') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="sy-table-head">
                        <tr>
                            <th class="px-6 py-3 text-left">Tarih</th>
                            <th class="px-6 py-3 text-left">Tur</th>
                            <th class="px-6 py-3 text-left">Aciklama</th>
                            <th class="px-6 py-3 text-right">Tutar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rows = collect();
                            foreach ($data['charges'] as $charge) {
                                $rows->push([
                                    'date' => $charge->due_date->format('d.m.Y'),
                                    'sort_date' => $charge->due_date,
                                    'type' => 'charge',
                                    'description' => $charge->apartment->full_label . ' - ' . $charge->description,
                                    'amount' => $charge->amount,
                                ]);
                            }
                            foreach ($data['expenses'] as $expense) {
                                $rows->push([
                                    'date' => $expense->expense_date->format('d.m.Y'),
                                    'sort_date' => $expense->expense_date,
                                    'type' => 'expense',
                                    'description' => $expense->vendor->name . ' - ' . $expense->description,
                                    'amount' => $expense->amount,
                                ]);
                            }
                            $rows = $rows->sortBy('sort_date')->values();
                        @endphp
                        @forelse($rows as $row)
                            <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                                <td class="sy-table-cell">{{ $row['date'] }}</td>
                                <td class="sy-table-cell">
                                    <span class="{{ $row['type'] === 'charge' ? 'sy-badge-paid' : 'sy-badge-overdue' }}">
                                        {{ $row['type'] === 'charge' ? 'Tahakkuk' : 'Gider' }}
                                    </span>
                                </td>
                                <td class="sy-table-cell">{{ $row['description'] }}</td>
                                <td class="sy-table-cell text-right text-tabular">{{ number_format($row['amount'], 2, ',', '.') }} TL</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="sy-table-cell py-8 text-center text-slate-400">Kayit yok</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($rows->count() > 0)
                        <tfoot>
                            <tr class="border-t border-slate-200 bg-slate-50/80">
                                <td colspan="2" class="sy-table-cell font-semibold text-slate-700">Toplam</td>
                                <td class="sy-table-cell text-right text-tabular text-slate-600">Tahakkuk: {{ number_format($data['totalCharges'], 2, ',', '.') }} TL</td>
                                <td class="sy-table-cell text-right text-tabular font-semibold text-slate-800">Gider: {{ number_format($data['totalExpenses'], 2, ',', '.') }} TL</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>
    @endif
@endsection
