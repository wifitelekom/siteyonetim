@extends('layouts.app')
@section('title', 'Kullanıcılar')
@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Kullanıcı Yönetimi</h2>
        <a href="{{ route('management.users.create') }}" class="px-4 py-2 bg-[var(--primary)] text-white rounded-full font-medium hover:opacity-90 transition-opacity flex items-center gap-2 text-sm shadow-sm">
            <span class="material-symbols-outlined text-lg">person_add</span>
            Yeni Kullanıcı
        </a>
    </div>

    <div class="bg-white rounded-card border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase font-bold text-gray-400">
                        <th class="px-6 py-4">Kullanıcı Adı</th>
                        <th class="px-6 py-4">İletişim</th>
                        <th class="px-6 py-4">Rol</th>
                        <th class="px-6 py-4">Kayıt Tarihi</th>
                        <th class="px-6 py-4 text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                             <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[var(--bg-main)] flex items-center justify-center text-[var(--primary)] font-bold">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $user->phone ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-600 border border-blue-100">
                                    {{ $user->roles->first()?->name ?? 'Rol Yok' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $user->created_at->format('d.m.Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('management.users.edit', $user) }}" class="w-8 h-8 rounded-full bg-gray-50 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors" title="Düzenle">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <button class="w-8 h-8 rounded-full bg-gray-50 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors" onclick="deleteRecord('{{ route('management.users.destroy', $user) }}')" title="Sil">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">group_off</span>
                                    <p>Kullanıcı bulunmuyor</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection