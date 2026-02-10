@extends('layouts.app')
@section('title', 'Kullanıcı Düzenle')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Kullanıcı Düzenle</h4>
        <a href="{{ route('management.users.index') }}" class="btn btn-outline-secondary btn-sm"><i
                class="bi bi-arrow-left"></i> Geri</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('management.users.update', $user) }}">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Ad Soyad *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">E-posta *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Cep Telefonu</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
                            value="{{ old('phone', $user->phone) }}" placeholder="05XX XXX XX XX">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tc_kimlik" class="form-label">TC Kimlik No</label>
                        <input type="text" class="form-control @error('tc_kimlik') is-invalid @enderror" id="tc_kimlik"
                            name="tc_kimlik" value="{{ old('tc_kimlik', $user->tc_kimlik) }}" maxlength="11"
                            placeholder="11 haneli TC Kimlik">
                        @error('tc_kimlik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="password" class="form-label">Şifre (boş bırakılırsa değişmez)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="password_confirmation" class="form-label">Şifre Tekrar</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="role" class="form-label">Rol *</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="admin" {{ old('role', $user->roles->first()?->name) == 'admin' ? 'selected' : '' }}>Yönetici</option>
                            <option value="owner" {{ old('role', $user->roles->first()?->name) == 'owner' ? 'selected' : '' }}>Ev Sahibi</option>
                            <option value="tenant" {{ old('role', $user->roles->first()?->name) == 'tenant' ? 'selected' : '' }}>Kiracı</option>
                            <option value="vendor" {{ old('role', $user->roles->first()?->name) == 'vendor' ? 'selected' : '' }}>Tedarikçi</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Güncelle</button>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Bağlı Daireler</div>
        <div class="card-body">
            <form method="POST" action="{{ route('management.users.add-apartment', $user) }}" class="row g-2 mb-3">
                @csrf
                <div class="col-md-5">
                    <select name="apartment_id" class="form-select form-select-sm" required>
                        <option value="">Daire seç</option>
                        @foreach($availableApartments as $apartment)
                            <option value="{{ $apartment->id }}">{{ $apartment->full_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="relation_type" class="form-select form-select-sm" required>
                        <option value="owner">Ev Sahibi</option>
                        <option value="tenant">Kiracı</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm"
                        value="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Ekle</button>
                </div>
            </form>

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Daire</th>
                        <th>Tür</th>
                        <th>Başlangıç</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->apartments as $apartment)
                        <tr>
                            <td>{{ $apartment->full_label }}</td>
                            <td>
                                <span class="badge bg-{{ $apartment->pivot->relation_type === 'owner' ? 'primary' : 'info' }}">
                                    {{ $apartment->pivot->relation_type === 'owner' ? 'Ev Sahibi' : 'Kiracı' }}
                                </span>
                            </td>
                            <td>{{ $apartment->pivot->start_date }}</td>
                            <td>
                                <form method="POST"
                                    action="{{ route('management.users.remove-apartment', [$user, $apartment]) }}"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted">Bağlı daire yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection