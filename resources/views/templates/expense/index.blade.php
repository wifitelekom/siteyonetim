@extends('layouts.app')
@section('title', 'Gider Şablonları')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Gider Şablonları</h4>
        <a href="{{ route('templates.expense.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Yeni
            Şablon</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" data-datatable>
                <thead>
                    <tr>
                        <th>Şablon Adı</th>
                        <th>Tedarikçi</th>
                        <th>Hesap</th>
                        <th>Tutar</th>
                        <th>Vade Günü</th>
                        <th>Periyot</th>
                        <th>Durum</th>
                        <th>Son Üretim</th>
                        <th width="120">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td>{{ $template->name }}</td>
                            <td>{{ $template->vendor?->name ?? '-' }}</td>
                            <td>{{ $template->account->full_name }}</td>
                            <td>{{ number_format($template->amount, 2, ',', '.') }} ₺</td>
                            <td>{{ $template->due_day }}. gün</td>
                            <td>{{ $template->period->label() }}</td>
                            <td>
                                @if($template->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Pasif</span>
                                @endif
                            </td>
                            <td>{{ $template->last_generated_at?->format('d.m.Y') ?? '-' }}</td>
                            <td>
                                <a href="{{ route('templates.expense.edit', $template) }}"
                                    class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-outline-danger btn-sm"
                                    onclick="deleteRecord('{{ route('templates.expense.destroy', $template) }}')"><i
                                        class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">Henüz şablon oluşturulmamış</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection