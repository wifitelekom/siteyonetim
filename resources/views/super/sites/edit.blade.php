@extends('layouts.app')
@section('title', 'Site Duzenle')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Site Duzenle</h1>
        <a href="{{ route('super.sites.index') }}" class="sy-btn-ghost">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Geri
        </a>
    </div>

    <form method="POST" action="{{ route('super.sites.update', $site) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <section class="sy-card p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Site Bilgileri</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="sy-label" for="name">Site Adi *</label>
                    <input class="sy-input" type="text" id="name" name="name" value="{{ old('name', $site->name) }}" required>
                </div>
                <div>
                    <label class="sy-label" for="phone">Telefon</label>
                    <input class="sy-input" type="text" id="phone" name="phone" value="{{ old('phone', $site->phone) }}">
                </div>
                <div>
                    <label class="sy-label" for="tax_no">Vergi No</label>
                    <input class="sy-input" type="text" id="tax_no" name="tax_no" value="{{ old('tax_no', $site->tax_no) }}">
                </div>
                <div class="md:col-span-2">
                    <label class="sy-label" for="address">Adres</label>
                    <textarea class="sy-input min-h-[90px]" id="address" name="address">{{ old('address', $site->address) }}</textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="h-4 w-4" {{ old('is_active', $site->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="text-sm text-slate-600">Aktif</label>
                </div>
            </div>
        </section>

        <section class="sy-card p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Site Yoneticisi</h2>

            <div class="mb-4">
                <label class="sy-label" for="admin_user_id">Yonetici Ata</label>
                <select id="admin_user_id" name="admin_user_id" class="sy-input">
                    <option value="">Degistirme</option>
                    @foreach($availableAdmins as $admin)
                        <option value="{{ $admin->id }}" @selected(old('admin_user_id', $currentAdminId) == $admin->id)>
                            {{ $admin->name }} - {{ $admin->email }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-slate-500">Sadece bosa alinmis veya bu siteye ait adminler listelenir.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="sy-label" for="admin_name">Yeni Yonetici Ad Soyad</label>
                    <input class="sy-input" type="text" id="admin_name" name="admin_name" value="{{ old('admin_name') }}">
                </div>
                <div>
                    <label class="sy-label" for="admin_email">Yeni Yonetici E-posta</label>
                    <input class="sy-input" type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}">
                </div>
                <div>
                    <label class="sy-label" for="admin_password">Yeni Yonetici Sifre</label>
                    <input class="sy-input" type="password" id="admin_password" name="admin_password">
                </div>
                <div>
                    <label class="sy-label" for="admin_password_confirmation">Sifre Tekrar</label>
                    <input class="sy-input" type="password" id="admin_password_confirmation" name="admin_password_confirmation">
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="sy-btn-primary">
                <span class="material-symbols-outlined text-[18px]">check</span>
                Kaydet
            </button>
        </div>
    </form>
@endsection
