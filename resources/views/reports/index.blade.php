@extends('layouts.app')
@section('title', 'Raporlar')
@section('content')
<h4 class="mb-4">Raporlar</h4>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-bank2 fs-1 text-primary"></i>
                <h5 class="mt-3">Kasa/Banka Ekstresi</h5>
                <p class="text-muted small">Kasa ve banka hesaplarının tarih aralığına göre hareketleri</p>
                <a href="{{ route('reports.cash-statement') }}" class="btn btn-outline-primary btn-sm">Görüntüle</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-journal-text fs-1 text-info"></i>
                <h5 class="mt-3">Hesap Ekstresi</h5>
                <p class="text-muted small">Muhasebe hesaplarına göre gelir/gider hareketleri</p>
                <a href="{{ route('reports.account-statement') }}" class="btn btn-outline-info btn-sm">Görüntüle</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-cash-stack fs-1 text-success"></i>
                <h5 class="mt-3">Tahsilat Raporu</h5>
                <p class="text-muted small">Tarih aralığına göre tahsilat detayları</p>
                <a href="{{ route('reports.collections') }}" class="btn btn-outline-success btn-sm">Görüntüle</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-credit-card fs-1 text-danger"></i>
                <h5 class="mt-3">Ödeme Raporu</h5>
                <p class="text-muted small">Tedarikçilere yapılan ödeme detayları</p>
                <a href="{{ route('reports.payments') }}" class="btn btn-outline-danger btn-sm">Görüntüle</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
                <h5 class="mt-3">Borç Durumu</h5>
                <p class="text-muted small">Daire bazında açık/vadesi geçmiş borçlar</p>
                <a href="{{ route('reports.debt-status') }}" class="btn btn-outline-warning btn-sm">Görüntüle</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-arrow-down-circle fs-1 text-secondary"></i>
                <h5 class="mt-3">Alacak Durumu</h5>
                <p class="text-muted small">Tedarikçilere olan borç durumu</p>
                <a href="{{ route('reports.receivable-status') }}" class="btn btn-outline-secondary btn-sm">Görüntüle</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-list-check fs-1 text-dark"></i>
                <h5 class="mt-3">Tahakkuk Listesi</h5>
                <p class="text-muted small">Dönem bazında tüm tahakkuklar</p>
                <a href="{{ route('reports.charge-list') }}" class="btn btn-outline-dark btn-sm">Görüntüle</a>
            </div>
        </div>
    </div>
</div>
@endsection