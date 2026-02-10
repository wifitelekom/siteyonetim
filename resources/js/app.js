import * as bootstrap from 'bootstrap';
import Chart from 'chart.js/auto';
import { DataTable } from 'simple-datatables';

// Make Chart.js available globally
window.Chart = Chart;
window.bootstrap = bootstrap;

// ========================================
// Delete Record (modal-based, AJAX)
// ========================================
window.deleteRecord = function (action, rowEl) {
    const form = document.getElementById('deleteForm');
    const modalEl = document.getElementById('deleteModal');

    if (!form || !modalEl) return;

    form.setAttribute('action', action);
    form.dataset.rowEl = rowEl || '';
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
};

// ========================================
// Toast Notifications
// ========================================
window.showToast = function (message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const template = document.getElementById('toastTemplate');
    if (!container || !template) return;

    const clone = template.content.cloneNode(true);
    const toastEl = clone.querySelector('.toast');

    const colorMap = {
        success: { bg: 'bg-success text-white', icon: 'bi-check-circle-fill' },
        danger: { bg: 'bg-danger text-white', icon: 'bi-exclamation-triangle-fill' },
        warning: { bg: 'bg-warning text-dark', icon: 'bi-exclamation-circle-fill' },
        info: { bg: 'bg-info text-white', icon: 'bi-info-circle-fill' },
    };

    const config = colorMap[type] || colorMap.success;
    toastEl.classList.add(...config.bg.split(' '));
    toastEl.querySelector('.toast-icon').classList.add(config.icon);
    toastEl.querySelector('.toast-message').textContent = message;

    if (type !== 'success') {
        const closeBtn = toastEl.querySelector('.btn-close-white');
        if (closeBtn && type === 'warning') closeBtn.classList.remove('btn-close-white');
    }

    container.appendChild(toastEl);
    const bsToast = new bootstrap.Toast(toastEl);
    bsToast.show();

    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
};

// ========================================
// DOM Ready
// ========================================
document.addEventListener('DOMContentLoaded', function () {

    // --- Sidebar toggle ---
    const toggleBtn = document.getElementById('menu-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('wrapper').classList.toggle('toggled');
        });
    }

    // --- Auto-dismiss alerts ---
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }, 5000);
    });

    // --- Select all checkboxes ---
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = selectAll.checked);
        });
    }

    // --- Confirm delete modals ---
    document.querySelectorAll('[data-bs-toggle="modal"][data-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = this.getAttribute('data-action');
            const form = document.getElementById('deleteForm');
            if (form) form.setAttribute('action', action);
        });
    });

    // --- AJAX Delete Form ---
    const deleteForm = document.getElementById('deleteForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const action = this.getAttribute('action');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(action, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(response => {
                    if (response.ok || response.status === 302) {
                        showToast('Kayıt başarıyla silindi.', 'success');
                        bootstrap.Modal.getInstance(document.getElementById('deleteModal'))?.hide();
                        // Try to remove the row or reload
                        setTimeout(() => location.reload(), 800);
                    } else {
                        throw new Error('Silme işlemi başarısız.');
                    }
                })
                .catch(err => {
                    showToast(err.message || 'Bir hata oluştu.', 'danger');
                });
        });
    }

    // ========================================
    // Dark Mode Toggle
    // ========================================
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    function updateThemeIcon() {
        const current = document.documentElement.getAttribute('data-theme');
        if (themeIcon) {
            themeIcon.className = current === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
        }
    }

    if (themeToggle) {
        // Init icon
        updateThemeIcon();

        themeToggle.addEventListener('click', function () {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('sy-theme', next);
            updateThemeIcon();
        });
    }

    // ========================================
    // Global Search (AJAX Autocomplete)
    // ========================================
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const q = this.value.trim();

            if (q.length < 2) {
                searchResults.classList.remove('show');
                searchResults.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`/search?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.length === 0) {
                            searchResults.innerHTML = '<div class="search-empty">Sonuç bulunamadı</div>';
                            searchResults.classList.add('show');
                            return;
                        }

                        // Group by group
                        const groups = {};
                        data.forEach(item => {
                            if (!groups[item.group]) groups[item.group] = [];
                            groups[item.group].push(item);
                        });

                        let html = '';
                        for (const [group, items] of Object.entries(groups)) {
                            html += `<div class="search-group-label">${group}</div>`;
                            items.forEach(item => {
                                html += `<a href="${item.url}" class="search-item">
                                <span class="search-item-icon ${item.color}"><i class="bi ${item.icon}"></i></span>
                                <span>${item.label}</span>
                            </a>`;
                            });
                        }

                        searchResults.innerHTML = html;
                        searchResults.classList.add('show');
                    })
                    .catch(() => {
                        searchResults.classList.remove('show');
                    });
            }, 300);
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('show');
            }
        });

        // Close on escape
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                searchResults.classList.remove('show');
                this.blur();
            }
        });
    }

    // ========================================
    // DataTables — Auto-init
    // ========================================
    document.querySelectorAll('[data-datatable]').forEach(function (table) {
        new DataTable(table, {
            searchable: true,
            sortable: true,
            perPage: 15,
            perPageSelect: [10, 15, 25, 50],
            labels: {
                placeholder: 'Ara...',
                perPage: '{select} kayıt',
                noRows: 'Kayıt bulunamadı',
                info: '{start} - {end} / {rows} kayıt',
            },
        });
    });

    // ========================================
    // Flash message → Toast
    // ========================================
    const flashSuccess = document.querySelector('.alert-success');
    if (flashSuccess) {
        const msg = flashSuccess.textContent.trim();
        if (msg) showToast(msg, 'success');
        flashSuccess.style.display = 'none';
    }

    const flashError = document.querySelector('.alert-danger');
    if (flashError) {
        const msg = flashError.textContent.trim();
        if (msg) showToast(msg, 'danger');
        flashError.style.display = 'none';
    }
});
