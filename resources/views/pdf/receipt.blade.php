@extends('layouts.pdf')
@section('title', 'Tahsilat Makbuzu')
@section('content')
<h2 style="text-align: center; margin-bottom: 20px;">TAHSİLAT MAKBUZU</h2>

<table style="width: 100%; margin-bottom: 20px;">
    <tr>
        <td style="width: 50%;"><strong>Makbuz No:</strong> {{ $receipt->receipt_no }}</td>
        <td style="width: 50%; text-align: right;"><strong>Tarih:</strong> {{ $receipt->paid_at->format('d.m.Y') }}</td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
    <tr>
        <td style="padding: 5px; border: 1px solid #ddd; width: 30%;"><strong>Daire:</strong></td>
        <td style="padding: 5px; border: 1px solid #ddd;">{{ $receipt->apartment->full_label }}</td>
    </tr>
    <tr>
        <td style="padding: 5px; border: 1px solid #ddd;"><strong>Sakin:</strong></td>
        <td style="padding: 5px; border: 1px solid #ddd;">{{ $receipt->apartment->current_owner?->name ?? $receipt->apartment->current_tenant?->name ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 5px; border: 1px solid #ddd;"><strong>Ödeme Yöntemi:</strong></td>
        <td style="padding: 5px; border: 1px solid #ddd;">{{ $receipt->method->label() }}</td>
    </tr>
    <tr>
        <td style="padding: 5px; border: 1px solid #ddd;"><strong>Kasa/Banka:</strong></td>
        <td style="padding: 5px; border: 1px solid #ddd;">{{ $receipt->cashAccount->name }}</td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Açıklama</th>
            <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Dönem</th>
            <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Tutar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($receipt->items as $item)
        <tr>
            <td style="padding: 6px; border: 1px solid #ddd;">{{ $item->charge->account->full_name }}</td>
            <td style="padding: 6px; border: 1px solid #ddd;">{{ $item->charge->period }}</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format($item->amount, 2, ',', '.') }} ₺</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #f0f0f0; font-weight: bold;">
            <td colspan="2" style="padding: 8px; border: 1px solid #ddd;">TOPLAM</td>
            <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">{{ number_format($receipt->total_amount, 2, ',', '.') }} ₺</td>
        </tr>
    </tfoot>
</table>

@if($receipt->description)
<p><strong>Açıklama:</strong> {{ $receipt->description }}</p>
@endif

<table style="width: 100%; margin-top: 50px;">
    <tr>
        <td style="width: 50%; text-align: center; border-top: 1px solid #333; padding-top: 5px;">Tahsil Eden</td>
        <td style="width: 50%; text-align: center; border-top: 1px solid #333; padding-top: 5px;">Ödeme Yapan</td>
    </tr>
</table>
@endsection
