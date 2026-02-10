@extends('layouts.guest')
@section('title', 'Giris Yap')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="mb-3">Hesabınıza giriş yapın</h5>

            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="identity" class="form-label">E-posta, Telefon veya TC Kimlik</label>
                    <input type="text" class="form-control @error('identity') is-invalid @enderror" id="identity"
                        name="identity" value="{{ old('identity') }}" required autofocus
                        placeholder="ornek@mail.com / 05XX... / 1234567890X">
                    @error('identity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Şifre</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        name="password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Beni hatırla</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="small text-decoration-none">Şifremi unuttum</a>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                </button>
            </form>
        </div>
        <div class="card-footer bg-white text-center">
            <small class="text-muted">Hesabınız yok mu?</small>
            <a href="{{ route('register') }}" class="small ms-1">Kayıt Ol</a>
        </div>
    </div>
@endsection