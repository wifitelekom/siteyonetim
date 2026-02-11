@extends('layouts.app')
@section('title', 'Kasa/Banka')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h4 class="text-2xl font-bold text-gray-800">Kasa/Banka Hesapları</h4>
    <a href="{{ route('cash-accounts.create') }}" class="px-4 py-2 bg-[var(--primary)] text-white rounded-full font-medium hover:opacity-90 transition-opacity flex items-center gap-2 text-sm">
        <span class="material-symbols-outlined text-lg">add</span>
        Yeni Hesap
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($cashAccounts as $ca)
    <div class="bg-white rounded-card border border-gray-100 p-6 shadow-sm hover:translate-y-[-2px] hover:shadow-md transition-all">
        <div class="flex justify-between items-start mb-4">
             <div class="w-12 h-12 rounded-xl {{ $ca->type->value === 'cash' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center">
                 <span class="material-symbols-outlined text-2xl">
                     {{ $ca->type->value === 'cash' ? 'payments' : 'account_balance' }}
                 </span>
            </div>
             <div class="flex gap-2">
                 <a href="{{ route('cash-accounts.edit', $ca) }}" class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 hover:text-[var(--primary)] flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-sm">edit</span>
                </a>
                <button class="w-8 h-8 rounded-full bg-red-50 text-red-400 hover:text-red-600 flex items-center justify-center transition-colors" onclick="deleteRecord('{{ route('cash-accounts.destroy', $ca) }}')">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
             </div>
        </div>
        
        <h5 class="text-lg font-bold text-gray-800 mb-1">{{ $ca->name }}</h5>
        <span class="text-xs font-semibold px-2 py-1 rounded bg-gray-100 text-gray-500 uppercase tracking-wide">{{ $ca->type->label() }}</span>
        
        <div class="mt-6">
            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Güncel Bakiye</p>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($ca->balance, 2, ',', '.') }} ₺</p>
        </div>
        
         <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between items-center">
             <span class="text-xs text-gray-400">Açılış: {{ number_format($ca->opening_balance, 2, ',', '.') }} ₺</span>
             <a href="{{ route('cash-accounts.statement', $ca) }}" class="text-sm font-semibold text-[var(--primary)] hover:underline flex items-center gap-1">
                 Ekstre <span class="material-symbols-outlined text-sm">arrow_forward</span>
             </a>
         </div>
    </div>
    @endforeach
</div>
@endsection
