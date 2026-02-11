@extends('layouts.app')
@section('title', 'Siteler')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Siteler</h1>
        <a href="{{ route('super.sites.create') }}" class="sy-btn-primary">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Yeni Site
        </a>
    </div>

    <section class="sy-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="sy-table-head">
                    <tr>
                        <th class="px-6 py-3 text-left">Site</th>
                        <th class="px-6 py-3 text-left">Yonetici</th>
                        <th class="px-6 py-3 text-left">Telefon</th>
                        <th class="px-6 py-3 text-left">Durum</th>
                        <th class="px-6 py-3 text-left">Olusturma</th>
                        <th class="px-6 py-3 text-right">Islem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                        @php
                            $admin = $site->users->first();
                        @endphp
                        <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                            <td class="sy-table-cell">
                                <div class="font-semibold text-slate-800">{{ $site->name }}</div>
                                <div class="text-xs text-slate-500">{{ $site->address ?? '-' }}</div>
                            </td>
                            <td class="sy-table-cell">
                                <div class="text-sm font-medium text-slate-700">{{ $admin?->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $admin?->email ?? '' }}</div>
                            </td>
                            <td class="sy-table-cell">{{ $site->phone ?? '-' }}</td>
                            <td class="sy-table-cell">
                                <span class="{{ $site->is_active ? 'sy-badge-paid' : 'sy-badge-overdue' }}">
                                    {{ $site->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="sy-table-cell">{{ $site->created_at?->format('d.m.Y') }}</td>
                            <td class="sy-table-cell text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('super.sites.edit', $site) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-primary-50 hover:text-primary-600">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <button type="button" data-delete-action="{{ route('super.sites.destroy', $site) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="sy-table-cell py-8 text-center text-slate-400">Henuz site yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-6">
        {{ $sites->links() }}
    </div>
@endsection
