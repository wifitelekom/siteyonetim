@extends('layouts.app')
@section('title', 'Gider Sablonlari')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="sy-page-title">Gider Sablonlari</h1>
        <a href="{{ route('templates.expense.create') }}" class="sy-btn-primary">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Yeni Sablon
        </a>
    </div>

    <section class="sy-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full" data-datatable>
                <thead class="sy-table-head">
                    <tr>
                        <th class="px-6 py-3 text-left">Sablon Adi</th>
                        <th class="px-6 py-3 text-left">Tedarikci</th>
                        <th class="px-6 py-3 text-left">Hesap</th>
                        <th class="px-6 py-3 text-left">Tutar</th>
                        <th class="px-6 py-3 text-left">Vade Gunu</th>
                        <th class="px-6 py-3 text-left">Periyot</th>
                        <th class="px-6 py-3 text-left">Durum</th>
                        <th class="px-6 py-3 text-left">Son Uretim</th>
                        <th class="px-6 py-3 text-right">Islem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        <tr class="border-t border-slate-200/70 hover:bg-slate-50/60">
                            <td class="sy-table-cell">{{ $template->name }}</td>
                            <td class="sy-table-cell">{{ $template->vendor?->name ?? '-' }}</td>
                            <td class="sy-table-cell">{{ $template->account->full_name }}</td>
                            <td class="sy-table-cell text-tabular">{{ number_format($template->amount, 2, ',', '.') }} TL</td>
                            <td class="sy-table-cell">{{ $template->due_day }}. gun</td>
                            <td class="sy-table-cell">{{ $template->period->label() }}</td>
                            <td class="sy-table-cell">
                                <span class="{{ $template->is_active ? 'sy-badge-paid' : 'sy-badge-overdue' }}">
                                    {{ $template->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="sy-table-cell">{{ $template->last_generated_at?->format('d.m.Y') ?? '-' }}</td>
                            <td class="sy-table-cell text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('templates.expense.edit', $template) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-primary-50 hover:text-primary-600">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <button type="button" data-delete-action="{{ route('templates.expense.destroy', $template) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="sy-table-cell py-8 text-center text-slate-400">Henuz sablon olusturulmamis</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
