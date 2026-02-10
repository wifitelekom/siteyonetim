@extends('layouts.app')
@section('title', 'Yeni Hesap')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Yeni Hesap</h4>
    <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="code" class="form-label">Hesap Kodu *</label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="name" class="form-label">Hesap Adı *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="type" class="form-label">Tür *</label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">Seçiniz</option>
                        <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Gelir</option>
                        <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Gider</option>
                        <option value="asset" {{ old('type') == 'asset' ? 'selected' : '' }}>Varlık</option>
                        <option value="liability" {{ old('type') == 'liability' ? 'selected' : '' }}>Borç</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Kaydet</button>
        </form>
    </div>
</div>
@endsection
