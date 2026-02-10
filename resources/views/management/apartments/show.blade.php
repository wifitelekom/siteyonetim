@extends('layouts.app')
@section('title', 'Daire Detay')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $apartment->full_label }}</h4>
    <a href="{{ route('management.apartments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Daire Bilgileri</div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Blok</th><td>{{ $apartment->block ?? '-' }}</td></tr>
                    <tr><th>Kat</th><td>{{ $apartment->floor }}</td></tr>
                    <tr><th>No</th><td>{{ $apartment->number }}</td></tr>
                    <tr><th>m2</th><td>{{ $apartment->m2 ?? '-' }}</td></tr>
                    <tr><th>Arsa Payı</th><td>{{ $apartment->arsa_payi ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Sakinler</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('management.apartments.add-resident', $apartment) }}" class="row g-2 mb-3">
                    @csrf
                    <div class="col-5">
                        <select name="user_id" class="form-select form-select-sm" required>
                            <option value="">Kullanıcı seç</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <select name="relation_type" class="form-select form-select-sm" required>
                            <option value="owner">Ev Sahibi</option>
                            <option value="tenant">Kiracı</option>
                        </select>
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Ekle</button>
                    </div>
                </form>

                <table class="table table-sm">
                    <thead><tr><th>Ad</th><th>Tür</th><th>Başlangıç</th><th></th></tr></thead>
                    <tbody>
                        @forelse($apartment->users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td><span class="badge bg-{{ $user->pivot->relation_type === 'owner' ? 'primary' : 'info' }}">{{ $user->pivot->relation_type === 'owner' ? 'Ev Sahibi' : 'Kiracı' }}</span></td>
                            <td>{{ $user->pivot->start_date }}</td>
                            <td>
                                <form method="POST" action="{{ route('management.apartments.remove-resident', [$apartment, $user]) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted">Sakin yok</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
