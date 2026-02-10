{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h6 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle text-danger me-1"></i> Silme Onayı
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Bu kaydı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> İptal
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i> Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>