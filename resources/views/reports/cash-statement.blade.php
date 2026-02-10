@extends('layouts.app')
@section('title', 'Kasa/Banka Ekstresi')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Kasa/Banka Ekstresi</h4>
    <div>
        @if(isset($data))
        <a href="{{ route('reports.cash-statement.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>
        @endif
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Raporlar</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="cash_account_id" class="form-label">Kasa/Banka *</label>
                <select name="cash_account_id" id="cash_account_id" class="form-select" required>
                    <option value="">Seçiniz</option>
                    @foreach($cashAccounts as $ca)
                        <option value="{{ $ca->id }}" {{ request('cash_account_id') == $ca->id ? 'selected' : '' }}>{{ $ca->name }}</option>
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
    <div class="card-header">
        <strong>{{ $data['account']->name }}</strong> — {{ request('from') }} / {{ request('to') }}
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead>
                <tr><th>Tarih</th><th>Açıklama</th><th class="text-end">Giriş</th><th class="text-end">Çıkış</th><th class="text-end">Bakiye</th></tr>
            </thead>
            <tbody>
                <tr class="table-light">
                    <td colspan="4"><strong>Açılış Bakiyesi</strong></td>
                    <td class="text-end fw-bold">{{ number_format($data['opening_balance'], 2, ',', '.') }} ₺</td>
                </tr>
                @foreach($data['transactions'] as $mov)
                <tr>
                    <td>{{ $mov['date'] }}</td>
                    <td>{{ $mov['description'] }}</td>
                    <td class="text-end text-success">{{ $mov['direction'] === 'in' ? number_format($mov['amount'], 2, ',', '.') . ' ₺' : '' }}</td>
                    <td class="text-end text-danger">{{ $mov['direction'] === 'out' ? number_format($mov['amount'], 2, ',', '.') . ' ₺' : '' }}</td>
                    <td class="text-end fw-bold">{{ number_format($mov['balance'], 2, ',', '.') }} ₺</td>
                </tr>
                @endforeach
                <tr class="table-dark">
                    <td colspan="4"><strong>Kapanış Bakiyesi</strong></td>
                    <td class="text-end fw-bold">{{ number_format($data['closing_balance'], 2, ',', '.') }} ₺</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
