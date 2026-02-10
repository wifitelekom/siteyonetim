@extends('layouts.app')
@section('title', 'Gider Ekle')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gider Ekle</h4>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="vendor_id" class="form-label">Tedarikçi</label>
                    <select name="vendor_id" id="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror">
                        <option value="">Seçiniz (opsiyonel)</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                    @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="account_id" class="form-label">Gider Hesabı *</label>
                    <select name="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                        <option value="">Seçiniz</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->full_name }}</option>
                        @endforeach
                    </select>
                    @error('account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="expense_date" class="form-label">Gider Tarihi *</label>
                    <input type="date" class="form-control @error('expense_date') is-invalid @enderror" id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                    @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description') }}">
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Kaydet</button>
        </form>
    </div>
</div>
@endsection
