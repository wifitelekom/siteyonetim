@extends('layouts.app')
@section('title', 'Alacak Durumu')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Alacak Durumu (Tedarikçi Bazında)</h4>
    <div>
        <a href="{{ route('reports.receivable-status.pdf') }}" class="btn btn-outline-danger btn-sm" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Raporlar</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Tedarikçi</th><th class="text-end">Toplam Gider</th><th class="text-end">Ödenen</th><th class="text-end">Kalan Borç</th><th>Durum</th></tr></thead>
            <tbody>
                @forelse($receivables as $row)
                <tr>
                    <td>{{ $row['vendor'] }}</td>
                    <td class="text-end">{{ number_format($row['total'], 2, ',', '.') }} ₺</td>
                    <td class="text-end text-success">{{ number_format($row['paid'], 2, ',', '.') }} ₺</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row['remaining'], 2, ',', '.') }} ₺</td>
                    <td>
                        @if($row['remaining'] > 0)
                            <span class="badge bg-danger">Borçlu</span>
                        @else
                            <span class="badge bg-success">Ödendi</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Tedarikçi borcu yok</td></tr>
                @endforelse
            </tbody>
            @if(count($receivables) > 0)
            <tfoot>
                <tr class="table-dark">
                    <td><strong>Genel Toplam</strong></td>
                    <td class="text-end fw-bold">{{ number_format(collect($receivables)->sum('total'), 2, ',', '.') }} ₺</td>
                    <td class="text-end fw-bold">{{ number_format(collect($receivables)->sum('paid'), 2, ',', '.') }} ₺</td>
                    <td class="text-end fw-bold">{{ number_format(collect($receivables)->sum('remaining'), 2, ',', '.') }} ₺</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
