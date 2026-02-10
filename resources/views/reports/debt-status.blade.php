@extends('layouts.app')
@section('title', 'Borç Durumu')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Borç Durumu (Daire Bazında)</h4>
    <div>
        <a href="{{ route('reports.debt-status.pdf') }}" class="btn btn-outline-danger btn-sm" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Raporlar</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Daire</th><th>Sakin</th><th class="text-end">Toplam Borç</th><th class="text-end">Ödenen</th><th class="text-end">Kalan</th><th>Durum</th></tr></thead>
            <tbody>
                @forelse($debts as $debt)
                <tr>
                    <td>{{ $debt['apartment'] }}</td>
                    <td>{{ $debt['resident'] }}</td>
                    <td class="text-end">{{ number_format($debt['total'], 2, ',', '.') }} ₺</td>
                    <td class="text-end text-success">{{ number_format($debt['paid'], 2, ',', '.') }} ₺</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($debt['remaining'], 2, ',', '.') }} ₺</td>
                    <td>
                        @if($debt['overdue_count'] > 0)
                            <span class="badge bg-danger">{{ $debt['overdue_count'] }} vadesi geçmiş</span>
                        @else
                            <span class="badge bg-warning">{{ $debt['open_count'] }} açık</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Borç kaydı yok</td></tr>
                @endforelse
            </tbody>
            @if(count($debts) > 0)
            <tfoot>
                <tr class="table-dark">
                    <td colspan="2"><strong>Genel Toplam</strong></td>
                    <td class="text-end fw-bold">{{ number_format(collect($debts)->sum('total'), 2, ',', '.') }} ₺</td>
                    <td class="text-end fw-bold">{{ number_format(collect($debts)->sum('paid'), 2, ',', '.') }} ₺</td>
                    <td class="text-end fw-bold">{{ number_format(collect($debts)->sum('remaining'), 2, ',', '.') }} ₺</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
