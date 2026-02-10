@extends('layouts.guest')
@section('title', 'Yeni Sifre')
@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h5 class="mb-3">Yeni sifre belirleyin</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="mb-3">
                <label for="email" class="form-label">E-posta</label>
                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                >
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Yeni Sifre</label>
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
                <label for="password_confirmation" class="form-label">Yeni Sifre Tekrar</label>
                <input
                    type="password"
                    class="form-control"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-shield-lock"></i> Sifreyi Guncelle
            </button>
        </form>
    </div>
    <div class="card-footer bg-white text-center">
        <a href="{{ route('login') }}" class="small">Giris ekranina don</a>
    </div>
</div>
@endsection
