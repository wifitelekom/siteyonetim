@extends('layouts.app')
@section('title', 'Raporlar')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="sy-page-title">Raporlar</h1>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('reports.cash-statement') }}" class="sy-card sy-card-hover p-6">
            <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                <span class="material-symbols-outlined">account_balance</span>
            </div>
            <h2 class="text-lg font-semibold text-slate-800">Kasa/Banka Ekstresi</h2>
            <p class="mt-2 text-sm text-slate-500">Kasa ve banka hesaplari hareketleri.</p>
        </a>

        <a href="{{ route('reports.account-statement') }}" class="sy-card sy-card-hover p-6">
            <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                <span class="material-symbols-outlined">summarize</span>
            </div>
            <h2 class="text-lg font-semibold text-slate-800">Hesap Ekstresi</h2>
            <p class="mt-2 text-sm text-slate-500">Muhasebe hesaplarina gore hareketler.</p>
        </a>

        <a href="{{ route('reports.collections') }}" class="sy-card sy-card-hover p-6">
            <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <span class="material-symbols-outlined">payments</span>
            </div>
            <h2 class="text-lg font-semibold text-slate-800">Tahsilat Raporu</h2>
            <p class="mt-2 text-sm text-slate-500">Tarih araligina gore tahsilatlar.</p>
        </a>

        <a href="{{ route('reports.payments') }}" class="sy-card sy-card-hover p-6">
            <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600">
                <span class="material-symbols-outlined">credit_card</span>
            </div>
            <h2 class="text-lg font-semibold text-slate-800">Odeme Raporu</h2>
            <p class="mt-2 text-sm text-slate-500">Tedarikcilere yapilan odemeler.</p>
        </a>

        <a href="{{ route('reports.debt-status') }}" class="sy-card sy-card-hover p-6">
            <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <span class="material-symbols-outlined">error</span>
            </div>
            <h2 class="text-lg font-semibold text-slate-800">Borc Durumu</h2>
            <p class="mt-2 text-sm text-slate-500">Daire bazinda acik borclar.</p>
        </a>

        <a href="{{ route('reports.receivable-status') }}" class="sy-card sy-card-hover p-6">
            <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                <span class="material-symbols-outlined">monitoring</span>
            </div>
            <h2 class="text-lg font-semibold text-slate-800">Alacak Durumu</h2>
            <p class="mt-2 text-sm text-slate-500">Tedarikci borc durumu.</p>
        </a>

        <a href="{{ route('reports.charge-list') }}" class="sy-card sy-card-hover p-6">
            <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                <span class="material-symbols-outlined">list_alt</span>
            </div>
            <h2 class="text-lg font-semibold text-slate-800">Tahakkuk Listesi</h2>
            <p class="mt-2 text-sm text-slate-500">Donem bazinda tum tahakkuklar.</p>
        </a>
    </div>
@endsection
