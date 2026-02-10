@extends('layouts.app')
@section('title', 'Ödemeler')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Ödemeler</h4>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tedarikçi</label>
                    <select name="vendor_id" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Başlangıç</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bitiş</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i>
                        Filtrele</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" data-datatable>
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Tedarikçi</th>
                        <th>Yöntem</th>
                        <th>Kasa/Banka</th>
                        <th class="text-end">Tutar</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_at->format('d.m.Y') }}</td>
                            <td>{{ $payment->vendor?->name ?? '-' }}</td>
                            <td>{{ $payment->method->label() }}</td>
                            <td>{{ $payment->cashAccount->name }}</td>
                            <td class="text-end">{{ number_format($payment->total_amount, 2, ',', '.') }} ₺</td>
                            <td><a href="{{ route('payments.show', $payment) }}" class="btn btn-outline-primary btn-sm"><i
                                        class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Ödeme kaydı yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="mt-3">{{ $payments->withQueryString()->links() }}</div>@endif
@endsection