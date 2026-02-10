@extends('layouts.app')
@section('title', 'Yeni Kullanıcı')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Yeni Kullanıcı</h4>
        <a href="{{ route('management.users.index') }}" class="btn btn-outline-secondary btn-sm"><i
                class="bi bi-arrow-left"></i> Geri</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('management.users.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Ad Soyad *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                            value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">E-posta *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Cep Telefonu</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
                            value="{{ old('phone') }}" placeholder="05XX XXX XX XX">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tc_kimlik" class="form-label">TC Kimlik No</label>
                        <input type="text" class="form-control @error('tc_kimlik') is-invalid @enderror" id="tc_kimlik"
                            name="tc_kimlik" value="{{ old('tc_kimlik') }}" maxlength="11"
                            placeholder="11 haneli TC Kimlik">
                        @error('tc_kimlik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="password" class="form-label">Şifre *</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="password_confirmation" class="form-label">Şifre Tekrar *</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                            required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="role" class="form-label">Rol *</label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">Seçiniz</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Yönetici</option>
                            <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Ev Sahibi</option>
                            <option value="tenant" {{ old('role') == 'tenant' ? 'selected' : '' }}>Kiracı</option>
                            <option value="vendor" {{ old('role') == 'vendor' ? 'selected' : '' }}>Tedarikçi</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Kaydet</button>
            </form>
        </div>
    </div>
@endsection