@extends('layouts.app')
@section('title', 'Hesap Plani')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Hesap Plani</h1>
        <a href="{{ route('accounts.create') }}" class="sy-btn-primary">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Yeni Hesap
        </a>
    </div>

    <section class="sy-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full" data-datatable>
                <thead class="sy-table-head">
                    <tr>
                        <th class="px-6 py-3 text-left">Kod</th>
                        <th class="px-6 py-3 text-left">Hesap Adi</th>
                        <th class="px-6 py-3 text-left">Tur</th>
                        <th class="px-6 py-3 text-right">Islem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                            <td class="sy-table-cell"><code class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-600">{{ $account->code }}</code></td>
                            <td class="sy-table-cell">{{ $account->name }}</td>
                            <td class="sy-table-cell">
                                @php
                                    $typeBadge = match($account->type->value) {
                                        'income' => 'sy-badge-paid',
                                        'expense' => 'sy-badge-overdue',
                                        'asset' => 'sy-badge-pending',
                                        'liability' => 'sy-badge-partial',
                                        default => 'sy-badge-pending',
                                    };
                                @endphp
                                <span class="{{ $typeBadge }}">{{ $account->type->label() }}</span>
                            </td>
                            <td class="sy-table-cell text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('accounts.edit', $account) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-primary-50 hover:text-primary-600">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <button type="button" data-delete-action="{{ route('accounts.destroy', $account) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="sy-table-cell py-8 text-center text-slate-400">Hesap tanimli degil</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
