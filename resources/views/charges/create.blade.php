@extends('layouts.app')
@section('title', 'Yeni Tahakkuk')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Yeni Tahakkuk</h4>
    <div>
        <a href="{{ route('charges.create-bulk') }}" class="btn btn-outline-info btn-sm"><i class="bi bi-collection"></i> Toplu Tahakkuk</a>
        <a href="{{ route('charges.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('charges.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="apartment_id" class="form-label">Daire *</label>
                    <select name="apartment_id" id="apartment_id" class="form-select @error('apartment_id') is-invalid @enderror" required>
                        <option value="">Seçiniz</option>
                        @foreach($apartments as $apt)
                            <option value="{{ $apt->id }}" {{ old('apartment_id') == $apt->id ? 'selected' : '' }}>{{ $apt->full_label }}</option>
                        @endforeach
                    </select>
                    @error('apartment_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
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
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="charge_type" class="form-label">Tür *</label>
                    <select name="charge_type" id="charge_type" class="form-select" required>
                        <option value="aidat" {{ old('charge_type') == 'aidat' ? 'selected' : '' }}>Aidat</option>
                        <option value="other" {{ old('charge_type') == 'other' ? 'selected' : '' }}>Diğer</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="period" class="form-label">Dönem *</label>
                    <input type="month" class="form-control @error('period') is-invalid @enderror" id="period" name="period" value="{{ old('period', date('Y-m')) }}" required>
                    @error('period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="due_date" class="form-label">Vade Tarihi *</label>
                    <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="amount" class="form-label">Tutar (₺) *</label>
                    <input type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Açıklama</label>
                <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}">
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Kaydet</button>
        </form>
    </div>
</div>
@endsection
