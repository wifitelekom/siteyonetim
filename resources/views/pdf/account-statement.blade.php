@extends('layouts.pdf')
@section('title', 'Hesap Ekstresi')
@section('content')
<h2 style="text-align: center; margin-bottom: 5px;">HESAP EKSTRESİ</h2>
<p style="text-align: center; color: #666; margin-bottom: 20px;">{{ $account->full_name }} | {{ $from }} - {{ $to }}</p>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f0f0f0;">
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Tarih</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Tür</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: left;">Açıklama</th>
            <th style="padding: 6px; border: 1px solid #ddd; text-align: right;">Tutar</th>
        </tr>
    </thead>
    <tbody>
        @php
            $rows = collect();
            foreach ($charges as $charge) {
                $rows->push([
                    'date' => $charge->due_date->format('d.m.Y'),
                    'sort_date' => $charge->due_date,
                    'type' => 'charge',
                    'description' => $charge->apartment->full_label . ' - ' . $charge->description,
                    'amount' => $charge->amount,
                ]);
            }
            foreach ($expenses as $expense) {
                $rows->push([
                    'date' => $expense->expense_date->format('d.m.Y'),
                    'sort_date' => $expense->expense_date,
                    'type' => 'expense',
                    'description' => $expense->vendor->name . ' - ' . $expense->description,
                    'amount' => $expense->amount,
                ]);
            }
            $rows = $rows->sortBy('sort_date')->values();
        @endphp
        @foreach($rows as $row)
        <tr>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $row['date'] }}</td>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $row['type'] === 'charge' ? 'Tahakkuk' : 'Gider' }}</td>
            <td style="padding: 5px; border: 1px solid #ddd;">{{ $row['description'] }}</td>
            <td style="padding: 5px; border: 1px solid #ddd; text-align: right;">{{ number_format($row['amount'], 2, ',', '.') }} ₺</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #333; color: #fff; font-weight: bold;">
            <td colspan="2" style="padding: 6px; border: 1px solid #ddd;">TOPLAM</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">Tahakkuk: {{ number_format($totalCharges, 2, ',', '.') }} ₺</td>
            <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">Gider: {{ number_format($totalExpenses, 2, ',', '.') }} ₺</td>
        </tr>
    </tfoot>
</table>
@endsection
