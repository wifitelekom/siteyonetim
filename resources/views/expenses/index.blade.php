@extends('layouts.app')
@section('title', 'Giderler')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Giderler</h4>
        @can('expenses.create')
            <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Yeni Gider</a>
        @endcan
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
                <div class="col-md-2">
                    <label class="form-label">Başlangıç</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bitiş</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Ödenmemiş</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Kısmi</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Ödendi</option>
                    </select>
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
                        <th>Hesap</th>
                        <th>Vade</th>
                        <th class="text-end">Tutar</th>
                        <th class="text-end">Ödenen</th>
                        <th class="text-end">Kalan</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d.m.Y') }}</td>
                            <td>{{ $expense->vendor?->name ?? '-' }}</td>
                            <td>{{ $expense->account->full_name }}</td>
                            <td>{{ $expense->due_date->format('d.m.Y') }}</td>
                            <td class="text-end">{{ number_format($expense->amount, 2, ',', '.') }} ₺</td>
                            <td class="text-end text-success">{{ number_format($expense->paid_amount, 2, ',', '.') }} ₺</td>
                            <td class="text-end text-danger">{{ number_format($expense->remaining, 2, ',', '.') }} ₺</td>
                            <td><span class="badge bg-{{ $expense->status->color() }}">{{ $expense->status->label() }}</span>
                            </td>
                            <td><a href="{{ route('expenses.show', $expense) }}" class="btn btn-outline-primary btn-sm"><i
                                        class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">Gider kaydı yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($expenses->hasPages())
    <div class="mt-3">{{ $expenses->withQueryString()->links() }}</div>@endif
@endsection