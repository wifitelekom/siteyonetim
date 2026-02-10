@extends('layouts.app')
@section('title', 'Daire Düzenle')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Daire Düzenle</h4>
    <a href="{{ route('management.apartments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('management.apartments.update', $apartment) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="block" class="form-label">Blok</label>
                    <input type="text" class="form-control" id="block" name="block" value="{{ old('block', $apartment->block) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="floor" class="form-label">Kat *</label>
                    <input type="number" class="form-control" id="floor" name="floor" value="{{ old('floor', $apartment->floor) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="number" class="form-label">Daire No *</label>
                    <input type="text" class="form-control" id="number" name="number" value="{{ old('number', $apartment->number) }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="m2" class="form-label">m2</label>
                    <input type="number" class="form-control" id="m2" name="m2" value="{{ old('m2', $apartment->m2) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="arsa_payi" class="form-label">Arsa Payı</label>
                    <input type="number" class="form-control" id="arsa_payi" name="arsa_payi" value="{{ old('arsa_payi', $apartment->arsa_payi) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Durum</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $apartment->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Güncelle</button>
        </form>
    </div>
</div>
<div class="card mt-3">
    <div class="card-header">Sakinler (Ev Sahibi / Kiracı)</div>
    <div class="card-body">
        <form method="POST" action="{{ route('management.apartments.add-resident', $apartment) }}" class="row g-2 mb-3">
            @csrf
            <div class="col-md-5">
                <select name="user_id" class="form-select form-select-sm" required>
                    <option value="">Kullanıcı seç</option>
                    @foreach($availableUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="relation_type" class="form-select form-select-sm" required>
                    <option value="owner">Ev Sahibi</option>
                    <option value="tenant">Kiracı</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">Ekle</button>
            </div>
        </form>

        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Ad</th>
                    <th>Tür</th>
                    <th>Başlangıç</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($apartment->users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>
                            <span class="badge bg-{{ $user->pivot->relation_type === 'owner' ? 'primary' : 'info' }}">
                                {{ $user->pivot->relation_type === 'owner' ? 'Ev Sahibi' : 'Kiracı' }}
                            </span>
                        </td>
                        <td>{{ $user->pivot->start_date }}</td>
                        <td>
                            <form method="POST" action="{{ route('management.apartments.remove-resident', [$apartment, $user]) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-muted">Sakin yok</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
