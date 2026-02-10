@extends('layouts.app')
@section('title', 'Tahakkuk Listesi')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tahakkuk Listesi</h4>
    <div>
        @if(isset($data))
        <a href="{{ route('reports.charge-list.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>
        @endif
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Raporlar</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="period" class="form-label">Dönem</label>
                <input type="month" class="form-control" name="period" value="{{ request('period', date('Y-m')) }}">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tümü</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Açık</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Ödendi</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Vadesi Geçmiş</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Sorgula</button>
            </div>
        </form>
    </div>
</div>

@if(isset($data))
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Daire</th><th>Hesap</th><th>Dönem</th><th>Vade</th><th class="text-end">Tutar</th><th class="text-end">Ödenen</th><th class="text-end">Kalan</th><th>Durum</th></tr></thead>
            <tbody>
                @forelse($data['charges'] as $charge)
                <tr>
                    <td>{{ $charge->apartment->full_label }}</td>
                    <td>{{ $charge->account->full_name }}</td>
                    <td>{{ $charge->period }}</td>
                    <td>{{ $charge->due_date->format('d.m.Y') }}</td>
                    <td class="text-end">{{ number_format($charge->amount, 2, ',', '.') }} ₺</td>
                    <td class="text-end text-success">{{ number_format($charge->paid_amount, 2, ',', '.') }} ₺</td>
                    <td class="text-end text-danger">{{ number_format($charge->remaining, 2, ',', '.') }} ₺</td>
                    <td><span class="badge bg-{{ $charge->status->color() }}">{{ $charge->status->label() }}</span></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-3">Tahakkuk yok</td></tr>
                @endforelse
            </tbody>
            @if($data['charges']->count() > 0)
            <tfoot>
                <tr class="table-dark">
                    <td colspan="4"><strong>Toplam</strong></td>
                    <td class="text-end fw-bold">{{ number_format($data['charges']->sum('amount'), 2, ',', '.') }} ₺</td>
                    <td class="text-end fw-bold">{{ number_format($data['charges']->sum('paid_amount'), 2, ',', '.') }} ₺</td>
                    <td class="text-end fw-bold">{{ number_format($data['charges']->sum('remaining'), 2, ',', '.') }} ₺</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif
@endsection
