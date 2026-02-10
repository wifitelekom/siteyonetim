@extends('layouts.pdf')
@section('title', 'Ödeme Raporu')
@section('content')
<h2 style="text-align: center; margin-bottom: 5px;">ÖDEME RAPORU</h2>
<p style="text-align: center; color: #666; margin-bottom: 20px;">{{ $from }} - {{ $to }}</p>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Tarih</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Tedarikçi</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Yöntem</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Kasa/Banka</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Tutar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
        <tr>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $payment->paid_at->format('d.m.Y') }}</td>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $payment->vendor?->name ?? '-' }}</td>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $payment->method->label() }}</td>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $payment->cashAccount->name }}</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right;">{{ number_format($payment->total_amount, 2, ',', '.') }} ₺</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #333; color: #fff; font-weight: bold;">
            <td colspan="4" style="padding: 6px; border: 1px solid #ddd;">TOPLAM</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format($payments->sum('total_amount'), 2, ',', '.') }} ₺</td>
        </tr>
    </tfoot>
</table>
@endsection
