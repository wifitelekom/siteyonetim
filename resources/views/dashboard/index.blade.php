@extends('layouts.app')
@section('title', 'Genel Bakış')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold" style="color: var(--sy-text);">Genel Bakış</h4>
        <div class="text-muted small">{{ now()->translatedFormat('d F Y, H:i') }}</div>
    </div>

    <div class="row g-3 dashboard-grid">
        {{-- Donut: Tahsil Edilecekler --}}
        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="donut-card">
                    <div class="donut-header">Tahsil Edilecekler</div>
                    <div class="donut-wrapper">
                        <canvas id="receivablesChart" width="160" height="160"></canvas>
                        <div class="donut-legend">
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #818cf8;"></span>
                                <span class="legend-label">Vadesi Gelmemiş</span>
                                <span class="legend-value">{{ number_format($receivables['not_due'], 2, ',', '.') }}
                                    ₺</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #f59e0b;"></span>
                                <span class="legend-label">Bugün</span>
                                <span class="legend-value">{{ number_format($receivables['due_today'], 2, ',', '.') }}
                                    ₺</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #ef4444;"></span>
                                <span class="legend-label">Geciken</span>
                                <span class="legend-value">{{ number_format($receivables['overdue'], 2, ',', '.') }}
                                    ₺</span>
                            </div>
                        </div>
                    </div>
                    <div class="donut-total">
                        <div class="total-value">{{ number_format($receivables['total'], 2, ',', '.') }} ₺</div>
                        <div class="total-label">{{ $receivables['count'] ?? 0 }} Hesap</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Donut: Ödenecekler --}}
        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="donut-card">
                    <div class="donut-header">Ödenecekler</div>
                    <div class="donut-wrapper">
                        <canvas id="payablesChart" width="160" height="160"></canvas>
                        <div class="donut-legend">
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #818cf8;"></span>
                                <span class="legend-label">Vadesi Gelmemiş</span>
                                <span class="legend-value">{{ number_format($payables['not_due'], 2, ',', '.') }} ₺</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #f59e0b;"></span>
                                <span class="legend-label">Bugün Ödenecek</span>
                                <span class="legend-value">{{ number_format($payables['due_today'], 2, ',', '.') }} ₺</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #ef4444;"></span>
                                <span class="legend-label">Geciken</span>
                                <span class="legend-value">{{ number_format($payables['overdue'], 2, ',', '.') }} ₺</span>
                            </div>
                        </div>
                    </div>
                    <div class="donut-total">
                        <div class="total-value">{{ number_format($payables['total'], 2, ',', '.') }} ₺</div>
                        <div class="total-label">{{ $payables['vendor_count'] ?? 0 }} Tedarikçi</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline Panel --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Yaklaşan İşlemler</span>
                    <span class="badge bg-primary rounded-pill">{{ $timeline->count() }}</span>
                </div>
                <div class="card-body py-2 timeline-panel" style="max-height: 380px; overflow-y: auto;">
                    @php $lastDate = ''; @endphp
                    @forelse($timeline as $item)
                        @if($item['date'] !== $lastDate)
                            <div class="timeline-date-header">
                                @php
                                    $d = \Carbon\Carbon::parse($item['date']);
                                    if ($d->isToday())
                                        echo 'BUGÜN / ' . $d->translatedFormat('d F Y');
                                    elseif ($d->isYesterday())
                                        echo 'DÜN';
                                    elseif ($d->isTomorrow())
                                        echo 'YARIN';
                                    else
                                        echo $d->diffForHumans() . ' / ' . $d->translatedFormat('d F');
                                @endphp
                            </div>
                            @php $lastDate = $item['date']; @endphp
                        @endif
                        <div class="timeline-item">
                            <span class="timeline-dot {{ $item['dot_class'] }}"></span>
                            <div class="timeline-content">
                                <div class="timeline-title">{{ $item['title'] }}</div>
                                <div class="timeline-subtitle">{{ $item['subtitle'] }}</div>
                            </div>
                            <span class="timeline-amount">{{ $item['amount'] }}</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>
                            Yaklaşan işlem yok
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Counter Cards Row --}}
    <div class="row g-3 mt-1 dashboard-grid">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-mini">
                <div class="stat-icon bg-accent"><i class="bi bi-arrow-repeat"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $aidatTemplates }}/{{ $aidatTemplatesTotal }}</div>
                    <div class="stat-label">Aidat Şablonu</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-mini">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-calendar-event"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $expenseTemplates }}/{{ $expenseTemplatesTotal }}</div>
                    <div class="stat-label">Gider Şablonu</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-mini">
                <div class="stat-icon bg-success-soft"><i class="bi bi-receipt"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $monthlyReceiptCount }}</div>
                    <div class="stat-label">Bu Ay Makbuz</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-mini">
                <div class="stat-icon bg-info-soft"><i class="bi bi-bank2"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalCash, 0, ',', '.') }} ₺</div>
                    <div class="stat-label">Toplam Bakiye</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Section: Recent Transactions + Cash Accounts --}}
    <div class="row g-3 mt-1 dashboard-grid">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Son Hareketler</span>
                    <span class="badge bg-secondary rounded-pill">{{ $recentTransactions->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Tür</th>
                                <th>Açıklama</th>
                                <th class="text-end">Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $tx)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($tx['date'])->format('d.m.Y') }}</td>
                                    <td>
                                        @if($tx['type'] === 'receipt')
                                            <span class="badge bg-success"><i class="bi bi-arrow-down-short"></i> Tahsilat</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-arrow-up-short"></i> Ödeme</span>
                                        @endif
                                    </td>
                                    <td>{{ $tx['description'] }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($tx['amount'], 2, ',', '.') }} ₺</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Hareket bulunmuyor</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">Kasa ve Bankalar</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @forelse($cashAccounts as $account)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span
                                                class="stat-icon {{ $account['type']->value === 'cash' ? 'bg-success-soft' : 'bg-accent' }}"
                                                style="width:32px;height:32px;font-size:0.85rem;">
                                                <i
                                                    class="bi {{ $account['type']->value === 'cash' ? 'bi-cash' : 'bi-bank2' }}"></i>
                                            </span>
                                            <span>{{ $account['name'] }}</span>
                                        </div>
                                    </td>
                                    <td
                                        class="text-end fw-bold {{ $account['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($account['balance'], 2, ',', '.') }} ₺
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Hesap bulunmuyor</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const chartColors = {
                    notDue: '#818cf8',
                    dueToday: '#f59e0b',
                    overdue: '#ef4444',
                };

                const chartOptions = {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return ctx.label + ': ' + new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(ctx.raw);
                                }
                            }
                        }
                    }
                };

                // Receivables Donut
                const rcvCtx = document.getElementById('receivablesChart');
                if (rcvCtx) {
                    new Chart(rcvCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Vadesi Gelmemiş', 'Bugün', 'Geciken'],
                            datasets: [{
                                data: [{{ $receivables['not_due'] }}, {{ $receivables['due_today'] }}, {{ $receivables['overdue'] }}],
                                backgroundColor: [chartColors.notDue, chartColors.dueToday, chartColors.overdue],
                                borderWidth: 0,
                                spacing: 2,
                                borderRadius: 4,
                            }]
                        },
                        options: chartOptions
                    });
                }

                // Payables Donut
                const payCtx = document.getElementById('payablesChart');
                if (payCtx) {
                    new Chart(payCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Vadesi Gelmemiş', 'Bugün Ödenecek', 'Geciken'],
                            datasets: [{
                                data: [{{ $payables['not_due'] }}, {{ $payables['due_today'] }}, {{ $payables['overdue'] }}],
                                backgroundColor: [chartColors.notDue, chartColors.dueToday, chartColors.overdue],
                                borderWidth: 0,
                                spacing: 2,
                                borderRadius: 4,
                            }]
                        },
                        options: chartOptions
                    });
                }
            });
        </script>
    @endpush
@endsection