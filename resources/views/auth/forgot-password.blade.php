@extends('layouts.guest')
@section('title', 'Sifre Sifirlama')
@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h5 class="mb-3">Sifre sifirlama baglantisi</h5>
        <p class="text-muted small">
            E-posta adresinizi girin. Sifre sifirlama baglantisi gonderilecektir.
        </p>

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

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">E-posta</label>
                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-envelope"></i> Sifirlama Linki Gonder
            </button>
        </form>
    </div>
    <div class="card-footer bg-white text-center">
        <a href="{{ route('login') }}" class="small">Giris ekranina don</a>
    </div>
</div>
@endsection
