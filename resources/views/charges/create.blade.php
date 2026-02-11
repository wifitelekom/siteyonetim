@extends('layouts.app')
@section('title', 'Yeni Tahakkuk')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Yeni Tahakkuk</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('charges.create-bulk') }}" class="sy-btn-secondary">
                <span class="material-symbols-outlined text-[18px]">library_add</span>
                Toplu Tahakkuk
            </a>
            <a href="{{ route('charges.index') }}" class="sy-btn-ghost">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Geri
            </a>
        </div>
    </div>

    <section class="sy-card mx-auto max-w-5xl p-6 sm:p-8">
        <form method="POST" action="{{ route('charges.store') }}">
            @csrf

            <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="apartment_id" class="sy-label">Daire <span class="text-red-500">*</span></label>
                    <select name="apartment_id" id="apartment_id" class="sy-input @error('apartment_id') border-red-300 ring-red-200 @enderror" required>
                        <option value="">Seciniz</option>
                        @foreach($apartments as $apt)
                            <option value="{{ $apt->id }}" @selected(old('apartment_id') == $apt->id)>{{ $apt->full_label }}</option>
                        @endforeach
                    </select>
                    @error('apartment_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="account_id" class="sy-label">Hesap <span class="text-red-500">*</span></label>
                    <select name="account_id" id="account_id" class="sy-input @error('account_id') border-red-300 ring-red-200 @enderror" required>
                        <option value="">Seciniz</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>{{ $account->full_name }}</option>
                        @endforeach
                    </select>
                    @error('account_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="charge_type" class="sy-label">Tur <span class="text-red-500">*</span></label>
                    <select name="charge_type" id="charge_type" class="sy-input" required>
                        <option value="aidat" @selected(old('charge_type') === 'aidat')>Aidat</option>
                        <option value="other" @selected(old('charge_type') === 'other')>Diger</option>
                    </select>
                </div>

                <div>
                    <label for="period" class="sy-label">Donem <span class="text-red-500">*</span></label>
                    <input type="month" id="period" name="period" value="{{ old('period', date('Y-m')) }}" class="sy-input @error('period') border-red-300 ring-red-200 @enderror" required>
                    @error('period')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date" class="sy-label">Vade Tarihi <span class="text-red-500">*</span></label>
                    <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}" class="sy-input @error('due_date') border-red-300 ring-red-200 @enderror" required>
                    @error('due_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="sy-label">Tutar (TL) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" id="amount" name="amount" value="{{ old('amount') }}" class="sy-input @error('amount') border-red-300 ring-red-200 @enderror" required>
                    @error('amount')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-8">
                <label for="description" class="sy-label">Aciklama</label>
                <input type="text" id="description" name="description" value="{{ old('description') }}" class="sy-input">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="sy-btn-primary">
                    <span class="material-symbols-outlined text-[18px]">check</span>
                    Kaydet
                </button>
            </div>
        </form>
    </section>
@endsection
