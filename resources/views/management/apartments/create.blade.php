@extends('layouts.app')
@section('title', 'Yeni Daire')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Yeni Daire</h4>
    <a href="{{ route('management.apartments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('management.apartments.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="block" class="form-label">Blok</label>
                    <input type="text" class="form-control @error('block') is-invalid @enderror" id="block" name="block" value="{{ old('block') }}">
                    @error('block')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="floor" class="form-label">Kat *</label>
                    <input type="number" class="form-control @error('floor') is-invalid @enderror" id="floor" name="floor" value="{{ old('floor') }}" required>
                    @error('floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="number" class="form-label">Daire No *</label>
                    <input type="text" class="form-control @error('number') is-invalid @enderror" id="number" name="number" value="{{ old('number') }}" required>
                    @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="m2" class="form-label">m2</label>
                    <input type="number" class="form-control" id="m2" name="m2" value="{{ old('m2') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="arsa_payi" class="form-label">Arsa Payı</label>
                    <input type="number" class="form-control" id="arsa_payi" name="arsa_payi" value="{{ old('arsa_payi') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Durum</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Kaydet</button>
        </form>
    </div>
</div>
@endsection
