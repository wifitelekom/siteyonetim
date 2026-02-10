@extends('layouts.app')
@section('title', 'Tahakkuk Detay')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tahakkuk Detay</h4>
    <a href="{{ route('charges.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Tahakkuk Bilgileri</span>
                <span class="badge bg-{{ $charge->status->color() }}">{{ $charge->status->label() }}</span>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th width="35%">Daire</th><td>{{ $charge->apartment->full_label }}</td></tr>
                    <tr><th>Hesap</th><td>{{ $charge->account->full_name }}</td></tr>
                    <tr><th>Tür</th><td>{{ $charge->charge_type->label() }}</td></tr>
                    <tr><th>Dönem</th><td>{{ $charge->period }}</td></tr>
                    <tr><th>Vade Tarihi</th><td>{{ $charge->due_date->format('d.m.Y') }}</td></tr>
                    <tr><th>Tutar</th><td class="fw-bold">{{ number_format($charge->amount, 2, ',', '.') }} ₺</td></tr>
                    <tr><th>Ödenen</th><td class="text-success">{{ number_format($charge->paid_amount, 2, ',', '.') }} ₺</td></tr>
                    <tr><th>Kalan</th><td class="text-danger fw-bold">{{ number_format($charge->remaining, 2, ',', '.') }} ₺</td></tr>
                    <tr><th>Açıklama</th><td>{{ $charge->description ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Ödeme Geçmişi</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Tarih</th><th>Makbuz No</th><th>Yöntem</th><th>Kasa/Banka</th><th class="text-end">Tutar</th></tr>
                    </thead>
                    <tbody>
                        @forelse($charge->receiptItems as $item)
                        <tr>
                            <td>{{ $item->receipt->paid_at->format('d.m.Y') }}</td>
                            <td><a href="{{ route('receipts.show', $item->receipt) }}">{{ $item->receipt->receipt_no }}</a></td>
                            <td>{{ $item->receipt->method->label() }}</td>
                            <td>{{ $item->receipt->cashAccount->name }}</td>
                            <td class="text-end">{{ number_format($item->amount, 2, ',', '.') }} ₺</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Henüz ödeme yok</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        @can('charges.collect')
        @if($charge->remaining > 0)
        <div class="card mb-3">
            <div class="card-header">Tahsilat Al</div>
            <div class="card-body">
                <form method="POST" action="{{ route('charges.collect', $charge) }}">
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
                        <label for="cash_account_id" class="form-label">Kasa/Banka *</label>
                        <select name="cash_account_id" id="cash_account_id" class="form-select" required>
                            @foreach($cashAccounts as $ca)
                                <option value="{{ $ca->id }}">{{ $ca->name }} ({{ $ca->type->label() }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Tutar (₺) *</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $charge->remaining }}" class="form-control" id="amount" name="amount" value="{{ $charge->remaining }}" required>
                        <div class="form-text">Kalan: {{ number_format($charge->remaining, 2, ',', '.') }} ₺</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <input type="text" class="form-control" id="description" name="description">
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-cash-stack"></i> Tahsilat Al</button>
                </form>
            </div>
        </div>
        @endif
        @endcan

        @can('charges.delete')
        @if($charge->paid_amount == 0)
        <div class="card border-danger">
            <div class="card-body text-center">
                <form method="POST" action="{{ route('charges.destroy', $charge) }}" onsubmit="return confirm('Bu tahakkuku silmek istediğinizden emin misiniz?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Tahakkuku Sil</button>
                </form>
            </div>
        </div>
        @endif
        @endcan
    </div>
</div>
@endsection
