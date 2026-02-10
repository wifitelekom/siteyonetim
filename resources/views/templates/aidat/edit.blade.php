@extends('layouts.app')
@section('title', 'Aidat Şablonu Düzenle')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Aidat Şablonu Düzenle</h4>
    <a href="{{ route('templates.aidat.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Geri</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('templates.aidat.update', $template) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Şablon Adı *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $template->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="account_id" class="form-label">Hesap *</label>
                    <select name="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                        <option value="">Seçiniz</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ old('account_id', $template->account_id) == $account->id ? 'selected' : '' }}>{{ $account->full_name }}</option>
                        @endforeach
                    </select>
                    @error('account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="amount" class="form-label">Tutar (₺) *</label>
                    <input type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $template->amount) }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="due_day" class="form-label">Vade Günü (1-28) *</label>
                    <input type="number" min="1" max="28" class="form-control @error('due_day') is-invalid @enderror" id="due_day" name="due_day" value="{{ old('due_day', $template->due_day) }}" required>
                    @error('due_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="scope" class="form-label">Kapsam *</label>
                    <select name="scope" id="scope" class="form-select @error('scope') is-invalid @enderror" required>
                        <option value="all" {{ old('scope', $template->scope) == 'all' ? 'selected' : '' }}>Tüm Daireler</option>
                        <option value="selected" {{ old('scope', $template->scope) == 'selected' ? 'selected' : '' }}>Seçili Daireler</option>
                    </select>
                    @error('scope')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div id="apartmentSelection" class="mb-3" style="display: none;">
                <label class="form-label">Daireler</label>
                <div class="row">
                    @foreach($apartments as $apt)
                    <div class="col-md-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="apartment_ids[]" value="{{ $apt->id }}" id="apt{{ $apt->id }}" {{ in_array($apt->id, old('apartment_ids', $template->apartments->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <label class="form-check-label" for="apt{{ $apt->id }}">{{ $apt->full_label }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Aktif</label>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Güncelle</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('scope').addEventListener('change', function() {
    document.getElementById('apartmentSelection').style.display = this.value === 'selected' ? 'block' : 'none';
});
document.getElementById('scope').dispatchEvent(new Event('change'));
</script>
@endpush
@endsection
