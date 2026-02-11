@if(session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition.opacity.duration.200ms
        class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
        role="alert"
    >
        <span class="material-symbols-outlined text-[20px]">check_circle</span>
        <p class="flex-1 font-medium">{{ session('success') }}</p>
        <button type="button" class="rounded-md p-1 text-emerald-700 hover:bg-emerald-100" @click="show = false">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition.opacity.duration.200ms
        class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
        role="alert"
    >
        <span class="material-symbols-outlined text-[20px]">error</span>
        <p class="flex-1 font-medium">{{ session('error') }}</p>
        <button type="button" class="rounded-md p-1 text-red-700 hover:bg-red-100" @click="show = false">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition.opacity.duration.200ms
        class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
        role="alert"
    >
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <ul class="flex-1 list-disc space-y-1 pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="rounded-md p-1 text-red-700 hover:bg-red-100" @click="show = false">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    </div>
@endif
