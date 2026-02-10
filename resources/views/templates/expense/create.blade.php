@extends('layouts.app')
@section('title', 'Yeni Gider Şablonu')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Yeni Gider Şablonu</h4>
    <a href="{{ route('templates.expense.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('templates.expense.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Şablon Adı *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="vendor_id" class="form-label">Tedarikçi</label>
                    <select name="vendor_id" id="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror">
                        <option value="">Seçiniz (Opsiyonel)</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                    @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
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
                <div class="col-md-6 mb-3">
                    <label for="period" class="form-label">Periyot *</label>
                    <select name="period" id="period" class="form-select @error('period') is-invalid @enderror" required>
                        <option value="monthly" {{ old('period') == 'monthly' ? 'selected' : '' }}>Aylık</option>
                        <option value="quarterly" {{ old('period') == 'quarterly' ? 'selected' : '' }}>3 Aylık</option>
                        <option value="yearly" {{ old('period') == 'yearly' ? 'selected' : '' }}>Yıllık</option>
                    </select>
                    @error('period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">Tutar (₺) *</label>
                    <input type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="due_day" class="form-label">Vade Günü (1-28) *</label>
                    <input type="number" min="1" max="28" class="form-control @error('due_day') is-invalid @enderror" id="due_day" name="due_day" value="{{ old('due_day', 15) }}" required>
                    @error('due_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Aktif</label>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Kaydet</button>
        </form>
    </div>
</div>
@endsection
