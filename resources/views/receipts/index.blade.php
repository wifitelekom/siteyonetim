@extends('layouts.app')
@section('title', 'Tahsilatlar')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Tahsilatlar</h4>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
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
                <div class="col-md-3">
                    <label class="form-label">Baslangic</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bitis</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
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
                        <th>Makbuz No</th>
                        <th>Tarih</th>
                        <th>Daire</th>
                        <th>Yontem</th>
                        <th>Kasa/Banka</th>
                        <th class="text-end">Tutar</th>
                        <th width="110">Islem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                        <tr>
                            <td>{{ $receipt->receipt_no }}</td>
                            <td>{{ $receipt->paid_at?->format('d.m.Y') }}</td>
                            <td>{{ $receipt->apartment?->full_label ?? '-' }}</td>
                            <td>{{ $receipt->method?->label() ?? '-' }}</td>
                            <td>{{ $receipt->cashAccount?->name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($receipt->total_amount, 2, ',', '.') }} TL</td>
                            <td>
                                <a href="{{ route('receipts.show', $receipt) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('receipts.print')
                                    <a href="{{ route('receipts.pdf', $receipt) }}" class="btn btn-outline-danger btn-sm"
                                        target="_blank">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Tahsilat kaydi bulunmuyor</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


@endsection