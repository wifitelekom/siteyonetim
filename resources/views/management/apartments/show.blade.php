@extends('layouts.app')
@section('title', 'Daire Detay')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">{{ $apartment->full_label }}</h1>
        <a href="{{ route('management.apartments.index') }}" class="sy-btn-ghost">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Geri
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="sy-card p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Daire Bilgileri</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Blok</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-700">{{ $apartment->block ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kat</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-700">{{ $apartment->floor }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">No</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-700">{{ $apartment->number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">m2</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-700">{{ $apartment->m2 ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Arsa Payi</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-700">{{ $apartment->arsa_payi ?? '-' }}</dd>
                </div>
            </dl>
        </section>

        <section class="sy-card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-800">Sakinler</h2>
            </div>

            <form method="POST" action="{{ route('management.apartments.add-resident', $apartment) }}" class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                @csrf
                <select name="user_id" class="sy-input" required>
                    <option value="">Kullanici sec</option>
                    @foreach($availableUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <select name="relation_type" class="sy-input" required>
                    <option value="owner">Ev Sahibi</option>
                    <option value="tenant">Kiraci</option>
                </select>
                <button type="submit" class="sy-btn-primary">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Ekle
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="sy-table-head">
                        <tr>
                            <th class="px-6 py-3 text-left">Ad</th>
                            <th class="px-6 py-3 text-left">Tur</th>
                            <th class="px-6 py-3 text-left">Baslangic</th>
                            <th class="px-6 py-3 text-right">Islem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apartment->users as $user)
                            <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                                <td class="sy-table-cell">{{ $user->name }}</td>
                                <td class="sy-table-cell">
                                    <span class="{{ $user->pivot->relation_type === 'owner' ? 'sy-badge-paid' : 'sy-badge-partial' }}">
                                        {{ $user->pivot->relation_type === 'owner' ? 'Ev Sahibi' : 'Kiraci' }}
                                    </span>
                                </td>
                                <td class="sy-table-cell">{{ $user->pivot->start_date }}</td>
                                <td class="sy-table-cell text-right">
                                    <form method="POST" action="{{ route('management.apartments.remove-resident', [$apartment, $user]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600">
                                            <span class="material-symbols-outlined text-[18px]">close</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="sy-table-cell py-6 text-center text-slate-400">Sakin yok</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
