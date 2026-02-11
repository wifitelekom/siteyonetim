@extends('layouts.app')
@section('title', 'Tahsilatlar')
@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Tahsilatlar</h2>
        
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white rounded-full border border-gray-200 px-3 py-2">
                <span class="text-gray-400 text-xs font-bold uppercase">Tarih:</span>
                <input type="date" name="from" value="{{ request('from') }}" class="border-none p-0 text-sm focus:ring-0 text-gray-600 bg-transparent">
                <span class="text-gray-300">-</span>
                <input type="date" name="to" value="{{ request('to') }}" class="border-none p-0 text-sm focus:ring-0 text-gray-600 bg-transparent">
            </div>

            <select name="apartment_id" class="rounded-full border-gray-200 text-sm bg-white focus:ring-[var(--primary)] py-2 px-4 outline-none max-w-[200px]" onchange="this.form.submit()">
                <option value="">Daire: Tümü</option>
                @foreach($apartments as $apartment)
                    <option value="{{ $apartment->id }}" {{ request('apartment_id') == $apartment->id ? 'selected' : '' }}>
                        {{ $apartment->full_label }}
                    </option>
                @endforeach
            </select>
            
            <button type="submit" class="w-10 h-10 rounded-full bg-[var(--primary)] text-white flex items-center justify-center hover:opacity-90 transition-opacity shadow-sm">
                <span class="material-symbols-outlined">filter_list</span>
            </button>
        </form>
    </div>

    <div class="bg-white rounded-card border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase font-bold text-gray-400">
                        <th class="px-6 py-4">Makbuz No</th>
                        <th class="px-6 py-4">Tarih</th>
                        <th class="px-6 py-4">Daire</th>
                        <th class="px-6 py-4">Yöntem / Kasa</th>
                        <th class="px-6 py-4 text-right">Tutar</th>
                        <th class="px-6 py-4 text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($receipts as $receipt)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 font-semibold text-gray-700">#{{ $receipt->receipt_no }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $receipt->paid_at?->format('d.m.Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-800">{{ $receipt->apartment?->full_label ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-700">{{ $receipt->method?->label() ?? '-' }}</span>
                                    <span class="text-xs text-gray-400">{{ $receipt->cashAccount?->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-[var(--primary)]">{{ number_format($receipt->total_amount, 2, ',', '.') }} ₺</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('receipts.show', $receipt) }}" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Görüntüle">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                    @can('receipts.print')
                                        <a href="{{ route('receipts.pdf', $receipt) }}" class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" target="_blank" title="Yazdır">
                                            <span class="material-symbols-outlined text-lg">picture_as_pdf</span>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">receipt_long</span>
                                    <p>Tahsilat kaydı bulunmuyor</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-6">
        {{ $receipts->links() }}
    </div>
@endsection