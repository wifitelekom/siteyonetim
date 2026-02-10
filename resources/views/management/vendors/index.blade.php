@extends('layouts.app')
@section('title', 'Tedarikçiler')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Tedarikçiler</h4>
        <a href="{{ route('management.vendors.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Yeni
            Tedarikçi</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" data-datatable>
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Vergi No</th>
                        <th>Telefon</th>
                        <th>E-posta</th>
                        <th width="120">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr>
                            <td>{{ $vendor->name }}</td>
                            <td>{{ $vendor->tax_no ?? '-' }}</td>
                            <td>{{ $vendor->phone ?? '-' }}</td>
                            <td>{{ $vendor->email ?? '-' }}</td>
                            <td>
                                <a href="{{ route('management.vendors.edit', $vendor) }}"
                                    class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-outline-danger btn-sm"
                                    onclick="deleteRecord('{{ route('management.vendors.destroy', $vendor) }}')"><i
                                        class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Tedarikçi yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection