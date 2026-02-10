@extends('layouts.app')
@section('title', 'Şifre Değiştir')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <h4 class="fw-bold mb-4" style="color: var(--sy-text);">
                <i class="bi bi-key me-2"></i>Şifre Değiştir
            </h4>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mevcut Şifre</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Yeni Şifre</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Yeni Şifre (Tekrar)</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Şifreyi Güncelle
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection