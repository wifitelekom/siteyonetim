@extends('layouts.pdf')
@section('title', 'Alacak Durumu')
@section('content')
<h2 style="text-align: center; margin-bottom: 20px;">ALACAK DURUMU RAPORU</h2>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Tedarikçi</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Toplam Gider</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Ödenen</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Kalan Borç</th>
        </tr>
    </thead>
    <tbody>
        @foreach($receivables as $row)
        <tr>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $row['vendor'] }}</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right;">{{ number_format($row['total'], 2, ',', '.') }} ₺</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right;">{{ number_format($row['paid'], 2, ',', '.') }} ₺</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right; font-weight: bold;">{{ number_format($row['remaining'], 2, ',', '.') }} ₺</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #333; color: #fff; font-weight: bold;">
            <td style="padding: 6px; border: 1px solid #ddd;">GENEL TOPLAM</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format(collect($receivables)->sum('total'), 2, ',', '.') }} ₺</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format(collect($receivables)->sum('paid'), 2, ',', '.') }} ₺</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">{{ number_format(collect($receivables)->sum('remaining'), 2, ',', '.') }} ₺</td>
        </tr>
    </tfoot>
</table>
@endsection
