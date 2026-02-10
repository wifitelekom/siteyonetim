@extends('layouts.app')
@section('title', 'Toplu Tahakkuk')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Toplu Tahakkuk</h4>
    <a href="{{ route('charges.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('charges.store-bulk') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="account_id" class="form-label">Hesap *</label>
                    <select name="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                        <option value="">Seçiniz</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>{{ $account->full_name }}</option>
                        @endforeach
                    </select>
                    @error('account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="charge_type" class="form-label">Tür *</label>
                    <select name="charge_type" id="charge_type" class="form-select" required>
                        <option value="aidat">Aidat</option>
                        <option value="other">Diğer</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="period" class="form-label">Dönem *</label>
                    <input type="month" class="form-control" id="period" name="period" value="{{ old('period', date('Y-m')) }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="due_date" class="form-label">Vade Tarihi *</label>
                    <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">Tutar (₺) *</label>
                    <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Daireler *</label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="select-all">
                    <label class="form-check-label fw-bold" for="select-all">Tümünü Seç</label>
                </div>
                <div class="row">
                    @foreach($apartments as $apt)
                    <div class="col-md-3 mb-1">
                        <div class="form-check">
                            <input class="form-check-input row-checkbox" type="checkbox" name="apartment_ids[]" value="{{ $apt->id }}" id="apt{{ $apt->id }}" {{ in_array($apt->id, old('apartment_ids', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="apt{{ $apt->id }}">{{ $apt->full_label }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
                @error('apartment_ids')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Açıklama</label>
                <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}">
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Toplu Oluştur</button>
        </form>
    </div>
</div>
@endsection
