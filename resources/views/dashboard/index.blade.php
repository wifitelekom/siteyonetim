@extends('layouts.app')
@section('title', 'Genel Bakis')

@section('content')
    @php
        $paidAmount = $receivables['total'] - ($receivables['not_due'] + $receivables['due_today'] + $receivables['overdue']);
        $pendingAmount = $receivables['not_due'] + $receivables['due_today'] + $receivables['overdue'];
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Genel Bakis</h1>
        <span class="text-sm font-medium text-slate-500">{{ now()->translatedFormat('d F Y, H:i') }}</span>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="sy-card p-6">
            <div class="mb-4 flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                    <span class="material-symbols-outlined">payments</span>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Toplam Aidat</p>
            </div>
            <p class="text-3xl font-bold text-slate-800 text-tabular">{{ number_format($receivables['total'], 2, ',', '.') }} TL</p>
        </div>

        <div class="sy-card p-6">
            <div class="mb-4 flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <span class="material-symbols-outlined">check_circle</span>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Odenen</p>
            </div>
            <p class="text-3xl font-bold text-slate-800 text-tabular">{{ number_format($paidAmount, 2, ',', '.') }} TL</p>
        </div>

        <div class="sy-card p-6">
            <div class="mb-4 flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <span class="material-symbols-outlined">schedule</span>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Bekleyen</p>
            </div>
            <p class="text-3xl font-bold text-slate-800 text-tabular">{{ number_format($pendingAmount, 2, ',', '.') }} TL</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section class="sy-card p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Tahsil Edilecekler</h2>
            <div class="mx-auto mb-4 h-44 w-44">
                <canvas id="receivablesChart"></canvas>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-400"></span>
                        <span>Vadesi Gelmemis</span>
                    </div>
                    <span class="font-semibold text-tabular text-slate-700">{{ number_format($receivables['not_due'], 2, ',', '.') }} TL</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <span>Bugun</span>
                    </div>
                    <span class="font-semibold text-tabular text-slate-700">{{ number_format($receivables['due_today'], 2, ',', '.') }} TL</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        <span>Geciken</span>
                    </div>
                    <span class="font-semibold text-tabular text-slate-700">{{ number_format($receivables['overdue'], 2, ',', '.') }} TL</span>
                </div>
            </div>
            <div class="mt-4 border-t border-slate-200 pt-4">
                <p class="text-xs uppercase tracking-wider text-slate-400">Toplam</p>
                <p class="mt-1 text-lg font-semibold text-primary-700 text-tabular">{{ number_format($receivables['total'], 2, ',', '.') }} TL</p>
            </div>
        </section>

        <section class="sy-card p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Odenecekler</h2>
            <div class="mx-auto mb-4 h-44 w-44">
                <canvas id="payablesChart"></canvas>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-400"></span>
                        <span>Vadesi Gelmemis</span>
                    </div>
                    <span class="font-semibold text-tabular text-slate-700">{{ number_format($payables['not_due'], 2, ',', '.') }} TL</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <span>Bugun</span>
                    </div>
                    <span class="font-semibold text-tabular text-slate-700">{{ number_format($payables['due_today'], 2, ',', '.') }} TL</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        <span>Geciken</span>
                    </div>
                    <span class="font-semibold text-tabular text-slate-700">{{ number_format($payables['overdue'], 2, ',', '.') }} TL</span>
                </div>
            </div>
            <div class="mt-4 border-t border-slate-200 pt-4">
                <p class="text-xs uppercase tracking-wider text-slate-400">Toplam</p>
                <p class="mt-1 text-lg font-semibold text-red-600 text-tabular">{{ number_format($payables['total'], 2, ',', '.') }} TL</p>
            </div>
        </section>

        <section class="sy-card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-800">Yaklasan Islemler</h2>
                <span class="rounded-md bg-primary-50 px-2 py-1 text-xs font-semibold text-primary-700">{{ $timeline->count() }}</span>
            </div>
            <div class="max-h-[380px] space-y-3 overflow-y-auto pr-1">
                @php $lastDate = ''; @endphp
                @forelse($timeline as $item)
                    @if($item['date'] !== $lastDate)
                        <p class="pt-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 first:pt-0">
                            @php
                                $d = \Carbon\Carbon::parse($item['date']);
                                echo $d->translatedFormat('d F Y');
                            @endphp
                        </p>
                        @php $lastDate = $item['date']; @endphp
                    @endif

                    <div class="rounded-lg border border-slate-200 p-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-sm font-semibold text-slate-700">{{ $item['title'] }}</p>
                            <span class="text-sm font-semibold text-tabular text-slate-800">{{ $item['amount'] }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $item['subtitle'] }}</p>
                    </div>
                @empty
                    <div class="sy-empty-state py-8">
                        <span class="material-symbols-outlined text-4xl text-slate-300">event_available</span>
                        <p class="mt-2 text-sm text-slate-500">Yaklasan islem yok.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section class="sy-card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-800">Son Hareketler</h2>
                <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500">{{ $recentTransactions->count() }} kayit</span>
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
                        @forelse($recentTransactions as $tx)
                            <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                                <td class="sy-table-cell">{{ \Illuminate\Support\Carbon::parse($tx['date'])->format('d.m.Y') }}</td>
                                <td class="sy-table-cell">
                                    @if($tx['type'] === 'receipt')
                                        <span class="sy-badge-paid">Tahsilat</span>
                                    @else
                                        <span class="sy-badge-overdue">Odeme</span>
                                    @endif
                                </td>
                                <td class="sy-table-cell">{{ $tx['description'] }}</td>
                                <td class="sy-table-cell text-right font-semibold text-slate-800 text-tabular">{{ number_format($tx['amount'], 2, ',', '.') }} TL</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="sy-table-cell py-8 text-center text-slate-400">Hareket bulunmuyor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="sy-card p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Kasa ve Bankalar</h2>
            <div class="space-y-3">
                @forelse($cashAccounts as $account)
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg {{ $account['type']->value === 'cash' ? 'bg-emerald-50 text-emerald-600' : 'bg-cyan-50 text-cyan-600' }}">
                                <span class="material-symbols-outlined text-[18px]">{{ $account['type']->value === 'cash' ? 'payments' : 'account_balance' }}</span>
                            </span>
                            <span class="text-sm font-medium text-slate-700">{{ $account['name'] }}</span>
                        </div>
                        <span class="text-sm font-semibold text-tabular {{ $account['balance'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($account['balance'], 2, ',', '.') }} TL
                        </span>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500">
                        Hesap bulunmuyor.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const chartColors = ['#818cf8', '#f59e0b', '#ef4444'];
                const options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { display: false },
                    },
                };

                const receivablesCanvas = document.getElementById('receivablesChart');
                if (receivablesCanvas) {
                    new Chart(receivablesCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: ['Vadesi Gelmemis', 'Bugun', 'Geciken'],
                            datasets: [{
                                data: [{{ $receivables['not_due'] }}, {{ $receivables['due_today'] }}, {{ $receivables['overdue'] }}],
                                backgroundColor: chartColors,
                                borderWidth: 0,
                            }],
                        },
                        options,
                    });
                }

                const payablesCanvas = document.getElementById('payablesChart');
                if (payablesCanvas) {
                    new Chart(payablesCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: ['Vadesi Gelmemis', 'Bugun', 'Geciken'],
                            datasets: [{
                                data: [{{ $payables['not_due'] }}, {{ $payables['due_today'] }}, {{ $payables['overdue'] }}],
                                backgroundColor: chartColors,
                                borderWidth: 0,
                            }],
                        },
                        options,
                    });
                }
            });
        </script>
    @endpush
@endsection
