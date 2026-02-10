@extends('layouts.pdf')
@section('title', 'Borç Durumu')
@section('content')
<h2 style="text-align: center; margin-bottom: 20px;">BORÇ DURUMU RAPORU</h2>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Daire</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Sakin</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Toplam Borç</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Ödenen</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Kalan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($debts as $debt)
        <tr>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $debt['apartment'] }}</td>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $debt['resident'] }}</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right;">{{ number_format($debt['total'], 2, ',', '.') }} ₺</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right;">{{ number_format($debt['paid'], 2, ',', '.') }} ₺</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right; font-weight: bold;">{{ number_format($debt['remaining'], 2, ',', '.') }} ₺</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #333; color: #fff; font-weight: bold;">
            <td colspan="2" style="padding: 6px; border: 1px solid #ddd;">GENEL TOPLAM</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format(collect($debts)->sum('total'), 2, ',', '.') }} ₺</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format(collect($debts)->sum('paid'), 2, ',', '.') }} ₺</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format(collect($debts)->sum('remaining'), 2, ',', '.') }} ₺</td>
        </tr>
    </tfoot>
</table>
@endsection
