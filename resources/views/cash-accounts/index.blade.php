@extends('layouts.app')
@section('title', 'Kasa/Banka')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Kasa/Banka Hesapları</h4>
    <a href="{{ route('cash-accounts.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Yeni Hesap</a>
</div>
<div class="row g-3">
    @foreach($cashAccounts as $ca)
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5>{{ $ca->name }}</h5>
                <span class="badge bg-{{ $ca->type->value === 'cash' ? 'success' : 'primary' }} mb-2">{{ $ca->type->label() }}</span>
                <p class="fs-4 fw-bold mb-1">{{ number_format($ca->balance, 2, ',', '.') }} ₺</p>
                <small class="text-muted">Açılış: {{ number_format($ca->opening_balance, 2, ',', '.') }} ₺</small>
                <div class="mt-3">
                    <a href="{{ route('cash-accounts.statement', $ca) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-journal-text"></i> Ekstre</a>
                    <a href="{{ route('cash-accounts.edit', $ca) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteRecord('{{ route('cash-accounts.destroy', $ca) }}')"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
