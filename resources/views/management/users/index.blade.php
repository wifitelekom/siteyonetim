@extends('layouts.app')
@section('title', 'Kullanıcılar')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Kullanıcılar</h4>
        <a href="{{ route('management.users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Yeni
            Kullanıcı</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" data-datatable>
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>E-posta</th>
                        <th>Telefon</th>
                        <th>Rol</th>
                        <th>Kayıt</th>
                        <th width="120">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $user->roles->first()?->name ?? '-' }}</span></td>
                            <td>{{ $user->created_at->format('d.m.Y') }}</td>
                            <td>
                                <a href="{{ route('management.users.edit', $user) }}" class="btn btn-outline-primary btn-sm"><i
                                        class="bi bi-pencil"></i></a>
                                @if($user->id !== auth()->id())
                                    <button class="btn btn-outline-danger btn-sm"
                                        onclick="deleteRecord('{{ route('management.users.destroy', $user) }}')"><i
                                            class="bi bi-trash"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Kullanıcı yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection