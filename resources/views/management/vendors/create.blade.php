@extends('layouts.app')
@section('title', 'Yeni Tedarikçi')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Yeni Tedarikçi</h4>
    <a href="{{ route('management.vendors.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('management.vendors.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Firma Adı *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tax_no" class="form-label">Vergi No</label>
                    <input type="text" class="form-control" id="tax_no" name="tax_no" value="{{ old('tax_no') }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Telefon</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">E-posta</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                </div>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Adres</label>
                <textarea class="form-control" id="address" name="address" rows="2">{{ old('address') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Kaydet</button>
        </form>
    </div>
</div>
@endsection
