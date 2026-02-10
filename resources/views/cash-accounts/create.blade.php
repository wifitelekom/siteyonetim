@extends('layouts.app')
@section('title', 'Yeni Kasa/Banka')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Yeni Kasa/Banka Hesabı</h4>
    <a href="{{ route('cash-accounts.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('cash-accounts.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="name" class="form-label">Hesap Adı *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="type" class="form-label">Tür *</label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="cash" {{ old('type') == 'cash' ? 'selected' : '' }}>Kasa (Nakit)</option>
                        <option value="bank" {{ old('type') == 'bank' ? 'selected' : '' }}>Banka</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="opening_balance" class="form-label">Açılış Bakiyesi (₺) *</label>
                    <input type="number" step="0.01" min="0" class="form-control @error('opening_balance') is-invalid @enderror" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}" required>
                    @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Kaydet</button>
        </form>
    </div>
</div>
@endsection
