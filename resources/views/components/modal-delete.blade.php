<div
    x-data="{ open: false, action: '' }"
    @open-delete-modal.window="action = $event.detail.action; open = true"
    @close-delete-modal.window="open = false; action = ''"
    @keydown.escape.window="open = false"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[9998] bg-slate-900/50 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
    >
        <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl" @click.stop>
            <div class="flex items-start gap-4">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600">
                    <span class="material-symbols-outlined text-[20px]">warning</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-slate-800">Silme Onayi</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Bu kaydi silmek istediginize emin misiniz? Bu islem geri alinamaz.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button
                    type="button"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50"
                    @click="open = false"
                >
                    Iptal
                </button>
                <form id="deleteForm" method="POST" :action="action">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-red-700"
                    >
                        Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
