@extends('layouts.app')
@section('title', 'Site Ayarlari')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Site Ayarlari</h4>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Geri
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('management.site-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Site Adi *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $site->name) }}"
                    required
                >
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Telefon</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    class="form-control @error('phone') is-invalid @enderror"
                    value="{{ old('phone', $site->phone) }}"
                >
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Adres</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="form-control @error('address') is-invalid @enderror"
                >{{ old('address', $site->address) }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Guncelle
            </button>
        </form>
    </div>
</div>
@endsection

