@extends('layouts.app')
@section('title', 'Tahsilat Detay')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tahsilat Detay</h4>
    <div>
        @can('receipts.print')
        <a href="{{ route('receipts.pdf', $receipt) }}" class="btn btn-outline-danger btn-sm" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>
        @endcan
        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
    </div>
</div>

<div class="card">
    <div class="card-header">Makbuz Bilgileri</div>
    <div class="card-body">
        <table class="table table-borderless">
            <tr><th width="25%">Makbuz No</th><td>{{ $receipt->receipt_no }}</td></tr>
            <tr><th>Daire</th><td>{{ $receipt->apartment->full_label }}</td></tr>
            <tr><th>Ödeme Tarihi</th><td>{{ $receipt->paid_at->format('d.m.Y') }}</td></tr>
            <tr><th>Ödeme Yöntemi</th><td>{{ $receipt->method->label() }}</td></tr>
            <tr><th>Kasa/Banka</th><td>{{ $receipt->cashAccount->name }}</td></tr>
            <tr><th>Toplam Tutar</th><td class="fw-bold text-success">{{ number_format($receipt->total_amount, 2, ',', '.') }} ₺</td></tr>
            <tr><th>Açıklama</th><td>{{ $receipt->description ?? '-' }}</td></tr>
        </table>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">Ödeme Kalemleri</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Hesap</th><th>Dönem</th><th class="text-end">Tutar</th></tr></thead>
            <tbody>
                @foreach($receipt->items as $item)
                <tr>
                    <td>{{ $item->charge->account->full_name }}</td>
                    <td>{{ $item->charge->period }}</td>
                    <td class="text-end">{{ number_format($item->amount, 2, ',', '.') }} ₺</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-dark">
                    <td colspan="2"><strong>Toplam</strong></td>
                    <td class="text-end fw-bold">{{ number_format($receipt->total_amount, 2, ',', '.') }} ₺</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
