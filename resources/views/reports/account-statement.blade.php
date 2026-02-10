@extends('layouts.app')
@section('title', 'Hesap Ekstresi')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Hesap Ekstresi</h4>
    <div>
        @if(isset($data))
        <a href="{{ route('reports.account-statement.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>
        @endif
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Raporlar</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="account_id" class="form-label">Hesap *</label>
                <select name="account_id" id="account_id" class="form-select" required>
                    <option value="">Seçiniz</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="from" class="form-label">Başlangıç</label>
                <input type="date" class="form-control" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label for="to" class="form-label">Bitiş</label>
                <input type="date" class="form-control" name="to" value="{{ request('to', now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Sorgula</button>
            </div>
        </form>
    </div>
</div>

@if(isset($data))
<div class="card">
    <div class="card-header"><strong>{{ $data['account']->full_name }}</strong> — {{ request('from') }} / {{ request('to') }}</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Tarih</th><th>Tür</th><th>Açıklama</th><th class="text-end">Tutar</th></tr></thead>
            <tbody>
                @php
                    $rows = collect();
                    foreach ($data['charges'] as $charge) {
                        $rows->push([
                            'date' => $charge->due_date->format('d.m.Y'),
                            'sort_date' => $charge->due_date,
                            'type' => 'charge',
                            'description' => $charge->apartment->full_label . ' - ' . $charge->description,
                            'amount' => $charge->amount,
                        ]);
                    }
                    foreach ($data['expenses'] as $expense) {
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
                @forelse($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td><span class="badge bg-{{ $row['type'] === 'charge' ? 'success' : 'danger' }}">{{ $row['type'] === 'charge' ? 'Tahakkuk' : 'Gider' }}</span></td>
                    <td>{{ $row['description'] }}</td>
                    <td class="text-end">{{ number_format($row['amount'], 2, ',', '.') }} ₺</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">Kayıt yok</td></tr>
                @endforelse
            </tbody>
            @if($rows->count() > 0)
            <tfoot>
                <tr class="table-dark">
                    <td colspan="2"><strong>Toplam</strong></td>
                    <td class="text-end"><strong>Tahakkuk:</strong> {{ number_format($data['totalCharges'], 2, ',', '.') }} ₺</td>
                    <td class="text-end fw-bold"><strong>Gider:</strong> {{ number_format($data['totalExpenses'], 2, ',', '.') }} ₺</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif
@endsection
