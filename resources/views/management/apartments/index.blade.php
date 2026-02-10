@extends('layouts.app')
@section('title', 'Daireler')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Daireler</h4>
        <a href="{{ route('management.apartments.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i>
            Yeni Daire</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" data-datatable>
                <thead>
                    <tr>
                        <th>Blok</th>
                        <th>Kat</th>
                        <th>No</th>
                        <th>m2</th>
                        <th>Arsa Payı</th>
                        <th>Sakin</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($apartments as $apt)
                        <tr>
                            <td>{{ $apt->block ?? '-' }}</td>
                            <td>{{ $apt->floor }}</td>
                            <td>{{ $apt->number }}</td>
                            <td>{{ $apt->m2 ?? '-' }}</td>
                            <td>{{ $apt->arsa_payi ?? '-' }}</td>
                            <td>{{ $apt->current_owner?->name ?? $apt->current_tenant?->name ?? '-' }}</td>
                            <td><span
                                    class="badge bg-{{ $apt->is_active ? 'success' : 'secondary' }}">{{ $apt->is_active ? 'Aktif' : 'Pasif' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('management.apartments.show', $apt) }}"
                                    class="btn btn-outline-info btn-sm">Sakinler</a>
                                <a href="{{ route('management.apartments.edit', $apt) }}"
                                    class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-outline-danger btn-sm"
                                    onclick="deleteRecord('{{ route('management.apartments.destroy', $apt) }}')"><i
                                        class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Daire yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection