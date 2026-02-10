@extends('layouts.app')
@section('title', 'Tahakkuklar')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tahakkuklar</h4>
    <div class="d-flex gap-2">
        @can('charges.create')
            <a href="{{ route('charges.create-bulk') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-collection"></i> Toplu Tahakkuk
            </a>
            <a href="{{ route('charges.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Yeni Tahakkuk
            </a>
        @endcan
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Donem</label>
                <input type="month" name="period" class="form-control form-control-sm" value="{{ request('period') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Daire</label>
                <select name="apartment_id" class="form-select form-select-sm">
                    <option value="">Tumu</option>
                    @foreach($apartments as $apartment)
                        <option value="{{ $apartment->id }}" {{ request('apartment_id') == $apartment->id ? 'selected' : '' }}>
                            {{ $apartment->full_label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tumu</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Acik</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Gecikmis</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Odendi</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Arama</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Aciklama">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filtrele
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" data-datatable>
            <thead>
                <tr>
                    <th>Daire</th>
                    <th>Hesap</th>
                    <th>Donem</th>
                    <th>Vade</th>
                    <th class="text-end">Tutar</th>
                    <th class="text-end">Odenen</th>
                    <th class="text-end">Kalan</th>
                    <th>Durum</th>
                    <th width="80">Islem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($charges as $charge)
                    <tr>
                        <td>{{ $charge->apartment?->full_label ?? '-' }}</td>
                        <td>{{ $charge->account?->full_name ?? '-' }}</td>
                        <td>{{ $charge->period }}</td>
                        <td>{{ $charge->due_date?->format('d.m.Y') }}</td>
                        <td class="text-end">{{ number_format($charge->amount, 2, ',', '.') }} TL</td>
                        <td class="text-end text-success">{{ number_format($charge->paid_amount, 2, ',', '.') }} TL</td>
                        <td class="text-end text-danger">{{ number_format($charge->remaining, 2, ',', '.') }} TL</td>
                        <td><span class="badge bg-{{ $charge->status->color() }}">{{ $charge->status->label() }}</span></td>
                        <td>
                            <a href="{{ route('charges.show', $charge) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-3">Kayit bulunmuyor</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($charges->hasPages())
    <div class="mt-3">{{ $charges->withQueryString()->links() }}</div>
@endif
@endsection
