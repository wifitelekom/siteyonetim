@extends('layouts.pdf')
@section('title', 'Tahakkuk Listesi')
@section('content')
<h2 style="text-align: center; margin-bottom: 5px;">TAHAKKUK LİSTESİ</h2>
<p style="text-align: center; color: #666; margin-bottom: 20px;">Dönem: {{ $period }}@if(request('status')) | Durum: {{ request('status') }}@endif</p>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Daire</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Hesap</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Vade</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Tutar</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Ödenen</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Kalan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($charges as $charge)
        <tr>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $charge->apartment->full_label }}</td>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $charge->account->full_name }}</td>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $charge->due_date->format('d.m.Y') }}</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right;">{{ number_format($charge->amount, 2, ',', '.') }} ₺</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right;">{{ number_format($charge->paid_amount, 2, ',', '.') }} ₺</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right; font-weight: bold;">{{ number_format($charge->remaining, 2, ',', '.') }} ₺</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #333; color: #fff; font-weight: bold;">
            <td colspan="3" style="padding: 6px; border: 1px solid #ddd;">TOPLAM</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format($charges->sum('amount'), 2, ',', '.') }} ₺</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format($charges->sum('paid_amount'), 2, ',', '.') }} ₺</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format($charges->sum('remaining'), 2, ',', '.') }} ₺</td>
        </tr>
    </tfoot>
</table>
@endsection
