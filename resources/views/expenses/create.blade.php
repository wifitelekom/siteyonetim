@extends('layouts.app')
@section('title', 'Gider Ekle')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Gider Ekle</h1>
        <a href="{{ route('expenses.index') }}" class="sy-btn-ghost">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Geri
        </a>
    </div>

    <section class="sy-card mx-auto max-w-5xl p-6 sm:p-8">
        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf

            <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="vendor_id" class="sy-label">Tedarikci</label>
                    <select name="vendor_id" id="vendor_id" class="sy-input">
                        <option value="">Seciniz (opsiyonel)</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="account_id" class="sy-label">Gider Hesabi <span class="text-red-500">*</span></label>
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

            <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <label for="expense_date" class="sy-label">Gider Tarihi <span class="text-red-500">*</span></label>
                    <input type="date" id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" class="sy-input @error('expense_date') border-red-300 ring-red-200 @enderror" required>
                    @error('expense_date')
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
