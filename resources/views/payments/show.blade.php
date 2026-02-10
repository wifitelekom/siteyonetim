@extends('layouts.app')
@section('title', 'Ödeme Detay')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Ödeme Detay</h4>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-borderless">
            <tr><th width="25%">Tedarikçi</th><td>{{ $payment->vendor?->name ?? '-' }}</td></tr>
            <tr><th>Ödeme Tarihi</th><td>{{ $payment->paid_at->format('d.m.Y') }}</td></tr>
            <tr><th>Yöntem</th><td>{{ $payment->method->label() }}</td></tr>
            <tr><th>Kasa/Banka</th><td>{{ $payment->cashAccount->name }}</td></tr>
            <tr><th>Toplam Tutar</th><td class="fw-bold text-danger">{{ number_format($payment->total_amount, 2, ',', '.') }} ₺</td></tr>
            <tr><th>Açıklama</th><td>{{ $payment->description ?? '-' }}</td></tr>
        </table>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">Ödeme Kalemleri</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Gider</th><th>Hesap</th><th class="text-end">Tutar</th></tr></thead>
            <tbody>
                @foreach($payment->items as $item)
                <tr>
                    <td><a href="{{ route('expenses.show', $item->expense) }}">{{ $item->expense->description ?? 'Gider #'.$item->expense->id }}</a></td>
                    <td>{{ $item->expense->account->full_name }}</td>
                    <td class="text-end">{{ number_format($item->amount, 2, ',', '.') }} ₺</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
