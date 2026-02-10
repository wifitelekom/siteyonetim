@extends('layouts.app')
@section('title', 'Ekstre - ' . $account->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Ekstre: {{ $account->name }}</h4>
    <a href="{{ route('cash-accounts.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Başlangıç</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from', $from) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bitiş</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to', $to) }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i> Sorgula</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Tarih</th><th>Açıklama</th><th class="text-end">Giriş</th><th class="text-end">Çıkış</th><th class="text-end">Bakiye</th></tr></thead>
            <tbody>
                <tr class="table-light">
                    <td colspan="4"><strong>Açılış Bakiyesi</strong></td>
                    <td class="text-end fw-bold">{{ number_format($opening_balance, 2, ',', '.') }} ₺</td>
                </tr>
                @foreach($transactions as $mov)
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
                    <td class="text-end fw-bold">{{ number_format($closing_balance, 2, ',', '.') }} ₺</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
