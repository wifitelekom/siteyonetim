@extends('layouts.app')
@section('title', 'Gider Detay')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gider Detay</h4>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>Gider Bilgileri</span>
                <span class="badge bg-{{ $expense->status->color() }}">{{ $expense->status->label() }}</span>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th width="35%">Tedarikçi</th><td>{{ $expense->vendor?->name ?? '-' }}</td></tr>
                    <tr><th>Hesap</th><td>{{ $expense->account->full_name }}</td></tr>
                    <tr><th>Gider Tarihi</th><td>{{ $expense->expense_date->format('d.m.Y') }}</td></tr>
                    <tr><th>Vade Tarihi</th><td>{{ $expense->due_date->format('d.m.Y') }}</td></tr>
                    <tr><th>Tutar</th><td class="fw-bold">{{ number_format($expense->amount, 2, ',', '.') }} ₺</td></tr>
                    <tr><th>Ödenen</th><td class="text-success">{{ number_format($expense->paid_amount, 2, ',', '.') }} ₺</td></tr>
                    <tr><th>Kalan</th><td class="text-danger fw-bold">{{ number_format($expense->remaining, 2, ',', '.') }} ₺</td></tr>
                    <tr><th>Açıklama</th><td>{{ $expense->description ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Ödeme Geçmişi</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Tarih</th><th>Yöntem</th><th>Kasa/Banka</th><th class="text-end">Tutar</th></tr>
                    </thead>
                    <tbody>
                        @forelse($expense->paymentItems as $item)
                        <tr>
                            <td>{{ $item->payment->paid_at->format('d.m.Y') }}</td>
                            <td>{{ $item->payment->method->label() }}</td>
                            <td>{{ $item->payment->cashAccount->name }}</td>
                            <td class="text-end">{{ number_format($item->amount, 2, ',', '.') }} ₺</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Henüz ödeme yok</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        @can('expenses.pay')
        @if($expense->remaining > 0)
        <div class="card">
            <div class="card-header">Ödeme Yap</div>
            <div class="card-body">
                <form method="POST" action="{{ route('expenses.pay', $expense) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="paid_at" class="form-label">Ödeme Tarihi *</label>
                        <input type="date" class="form-control @error('paid_at') is-invalid @enderror" id="paid_at" name="paid_at" value="{{ old('paid_at', date('Y-m-d')) }}" required>
                        @error('paid_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="method" class="form-label">Ödeme Yöntemi *</label>
                        <select name="method" id="method" class="form-select" required>
                            <option value="cash">Nakit</option>
                            <option value="bank">Banka</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cash_account_id" class="form-label">Kasa/Banka Hesabı *</label>
                        <select name="cash_account_id" id="cash_account_id" class="form-select" required>
                            @foreach($cashAccounts as $ca)
                                <option value="{{ $ca->id }}">{{ $ca->name }} ({{ $ca->type->label() }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Tutar (₺) *</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $expense->remaining }}" class="form-control" id="amount" name="amount" value="{{ $expense->remaining }}" required>
                        <div class="form-text">Kalan: {{ number_format($expense->remaining, 2, ',', '.') }} ₺</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <input type="text" class="form-control" id="description" name="description">
                    </div>
                    <button type="submit" class="btn btn-danger w-100"><i class="bi bi-credit-card"></i> Ödeme Yap</button>
                </form>
            </div>
        </div>
        @endif
        @endcan

        @can('expenses.delete')
        @if($expense->paid_amount == 0)
        <div class="card border-danger">
            <div class="card-body text-center">
                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Bu gideri silmek istediğinizden emin misiniz?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Gideri Sil</button>
                </form>
            </div>
        </div>
        @endif
        @endcan
    </div>
</div>
@endsection
