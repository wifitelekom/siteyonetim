<div class="toast-container" id="toastContainer">
    {{-- Toasts will be inserted here dynamically --}}
</div>

<template id="toastTemplate">
    <div class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true"
        data-bs-delay="4000">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi toast-icon"></i>
                <span class="toast-message"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</template>