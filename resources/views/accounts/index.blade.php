@extends('layouts.app')
@section('title', 'Hesap Planı')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Hesap Planı</h4>
        <a href="{{ route('accounts.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Yeni Hesap</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" data-datatable>
                <thead>
                    <tr>
                        <th>Kod</th>
                        <th>Hesap Adı</th>
                        <th>Tür</th>
                        <th width="120">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td><code>{{ $account->code }}</code></td>
                            <td>{{ $account->name }}</td>
                            <td><span class="badge bg-{{ $account->type->color() }}">{{ $account->type->label() }}</span></td>
                            <td>
                                <a href="{{ route('accounts.edit', $account) }}" class="btn btn-outline-primary btn-sm"><i
                                        class="bi bi-pencil"></i></a>
                                <button class="btn btn-outline-danger btn-sm"
                                    onclick="deleteRecord('{{ route('accounts.destroy', $account) }}')"><i
                                        class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Hesap tanımlı değil</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection