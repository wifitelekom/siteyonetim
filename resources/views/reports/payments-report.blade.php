@extends('layouts.app')
@section('title', 'Ödeme Raporu')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Ödeme Raporu</h4>
    <div>
        @if(isset($data))
        <a href="{{ route('reports.payments.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>
        @endif
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Raporlar</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="from" class="form-label">Başlangıç</label>
                <input type="date" class="form-control" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label for="to" class="form-label">Bitiş</label>
                <input type="date" class="form-control" name="to" value="{{ request('to', now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Sorgula</button>
            </div>
        </form>
    </div>
</div>

@if(isset($data))
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Tarih</th><th>Tedarikçi</th><th>Yöntem</th><th>Kasa/Banka</th><th class="text-end">Tutar</th></tr></thead>
            <tbody>
                @forelse($data['payments'] as $payment)
                <tr>
                    <td>{{ $payment->paid_at->format('d.m.Y') }}</td>
                    <td>{{ $payment->vendor?->name ?? '-' }}</td>
                    <td>{{ $payment->method->label() }}</td>
                    <td>{{ $payment->cashAccount->name }}</td>
                    <td class="text-end">{{ number_format($payment->total_amount, 2, ',', '.') }} ₺</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Ödeme yok</td></tr>
                @endforelse
            </tbody>
            @if($data['payments']->count() > 0)
            <tfoot>
                <tr class="table-dark">
                    <td colspan="4"><strong>Toplam</strong></td>
                    <td class="text-end fw-bold">{{ number_format($data['payments']->sum('total_amount'), 2, ',', '.') }} ₺</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif
@endsection
