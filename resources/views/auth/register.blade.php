@extends('layouts.guest')
@section('title', 'Kayit Ol')
@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h5 class="mb-3">Yeni hesap olustur</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Ad Soyad</label>
                <input
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                >
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-posta</label>
                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                >
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Sifre</label>
                <input
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    required
                >
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Sifre Tekrar</label>
                <input
                    type="password"
                    class="form-control"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-person-plus"></i> Kayit Ol
            </button>
        </form>
    </div>
    <div class="card-footer bg-white text-center">
        <small class="text-muted">Zaten hesabiniz var mi?</small>
        <a href="{{ route('login') }}" class="small ms-1">Giris Yap</a>
    </div>
</div>
@endsection
