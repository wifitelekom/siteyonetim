@extends('layouts.pdf')
@section('title', 'Kasa/Banka Ekstresi')
@section('content')
<h2 style="text-align: center; margin-bottom: 5px;">KASA/BANKA EKSTRESİ</h2>
<p style="text-align: center; color: #666; margin-bottom: 20px;">{{ $account->name }} | {{ $from }} - {{ $to }}</p>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Tarih</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Açıklama</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Giriş</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Çıkış</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Bakiye</th>
        </tr>
    </thead>
    <tbody>
        <tr style="background: #e8e8e8; font-weight: bold;">
            <td colspan="4" style="padding: 6px; border: 1px solid #ddd;">Açılış Bakiyesi</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format($opening_balance, 2, ',', '.') }} ₺</td>
        </tr>
        @foreach($transactions as $mov)
        <tr>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $mov['date'] }}</td>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $mov['description'] }}</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right;">{{ $mov['direction'] === 'in' ? number_format($mov['amount'], 2, ',', '.') . ' ₺' : '' }}</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right;">{{ $mov['direction'] === 'out' ? number_format($mov['amount'], 2, ',', '.') . ' ₺' : '' }}</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right; font-weight: bold;">{{ number_format($mov['balance'], 2, ',', '.') }} ₺</td>
        </tr>
        @endforeach
        <tr style="background: #333; color: #fff; font-weight: bold;">
            <td colspan="4" style="padding: 6px; border: 1px solid #ddd;">Kapanış Bakiyesi</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format($closing_balance, 2, ',', '.') }} ₺</td>
        </tr>
    </tbody>
</table>
@endsection
