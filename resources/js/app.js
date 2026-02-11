import Chart from 'chart.js/auto';
import { DataTable } from 'simple-datatables';

window.Chart = Chart;

const SEARCH_ICON_MAP = {
    'bi-door-open': 'door_front',
    'bi-person': 'person',
    'bi-truck': 'local_shipping',
    'bi-cash-stack': 'payments',
};

const SEARCH_COLOR_MAP = {
    'bg-accent': 'bg-primary-100 text-primary-700',
    'bg-info-soft': 'bg-cyan-100 text-cyan-700',
    'bg-warning-soft': 'bg-amber-100 text-amber-700',
    'bg-success-soft': 'bg-emerald-100 text-emerald-700',
};

window.deleteRecord = function deleteRecord(action) {
    if (!action) return;
    window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { action } }));
};

window.showToast = function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container || !message) return;

    const styleMap = {
        success: {
            shell: 'border-emerald-200 bg-emerald-50 text-emerald-800',
            iconWrap: 'bg-emerald-100 text-emerald-700',
            icon: 'check_circle',
        },
        danger: {
            shell: 'border-red-200 bg-red-50 text-red-800',
            iconWrap: 'bg-red-100 text-red-700',
            icon: 'error',
        },
        warning: {
            shell: 'border-amber-200 bg-amber-50 text-amber-800',
            iconWrap: 'bg-amber-100 text-amber-700',
            icon: 'warning',
        },
        info: {
            shell: 'border-cyan-200 bg-cyan-50 text-cyan-800',
            iconWrap: 'bg-cyan-100 text-cyan-700',
            icon: 'info',
        },
    };

    const style = styleMap[type] || styleMap.success;
    const toast = document.createElement('div');
    toast.className = `toast-enter flex w-[320px] items-start gap-3 rounded-xl border px-3 py-3 text-sm shadow-lg ${style.shell}`;
    toast.innerHTML = `
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg ${style.iconWrap}">
            <span class="material-symbols-outlined text-[18px]">${style.icon}</span>
        </span>
        <p class="flex-1 pt-1 font-medium">${message}</p>
        <button type="button" class="rounded-md p-1 text-current/70 transition-colors hover:bg-white/50 hover:text-current" aria-label="Kapat">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    `;

    const removeToast = () => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 180);
    };

    toast.querySelector('button')?.addEventListener('click', removeToast);
    container.appendChild(toast);

    if (container.childElementCount > 5) {
        container.firstElementChild?.remove();
    }

    setTimeout(removeToast, 4000);
};

document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.row-checkbox').forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
        });
    }

    document.querySelectorAll('[data-delete-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.getAttribute('data-delete-action');
            window.deleteRecord(action);
        });
    });

    const deleteForm = document.getElementById('deleteForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const action = deleteForm.getAttribute('action');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!action || !csrfToken) return;

            fetch(action, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => {
                    if (!response.ok && response.status !== 302) {
                        throw new Error('Silme islemi basarisiz.');
                    }
                    showToast('Kayit basariyla silindi.', 'success');
                    window.dispatchEvent(new CustomEvent('close-delete-modal'));
                    setTimeout(() => window.location.reload(), 600);
                })
                .catch((error) => {
                    showToast(error.message || 'Bir hata olustu.', 'danger');
                });
        });
    }

    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const query = searchInput.value.trim();

            if (query.length < 2) {
                searchResults.classList.add('hidden');
                searchResults.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`/search?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (!Array.isArray(data) || data.length === 0) {
                            searchResults.innerHTML = '<div class="p-3 text-sm text-slate-500">Sonuc bulunamadi.</div>';
                            searchResults.classList.remove('hidden');
                            return;
                        }

                        const groups = data.reduce((carry, item) => {
                            const group = item.group || 'Sonuclar';
                            if (!carry[group]) carry[group] = [];
                            carry[group].push(item);
                            return carry;
                        }, {});

                        let html = '';
                        Object.entries(groups).forEach(([group, items]) => {
                            html += `<p class="border-t border-slate-100 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400 first:border-t-0">${group}</p>`;
                            items.forEach((item) => {
                                const icon = SEARCH_ICON_MAP[item.icon] || 'search';
                                const color = SEARCH_COLOR_MAP[item.color] || 'bg-slate-100 text-slate-600';
                                html += `
                                    <a href="${item.url}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg ${color}">
                                            <span class="material-symbols-outlined text-[18px]">${icon}</span>
                                        </span>
                                        <span class="truncate">${item.label}</span>
                                    </a>
                                `;
                            });
                        });

                        searchResults.innerHTML = html;
                        searchResults.classList.remove('hidden');
                    })
                    .catch(() => {
                        searchResults.classList.add('hidden');
                    });
            }, 250);
        });

        document.addEventListener('click', (event) => {
            if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                searchResults.classList.add('hidden');
            }
        });

        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                searchResults.classList.add('hidden');
                searchInput.blur();
            }
        });
    }

    document.querySelectorAll('[data-datatable]').forEach((table) => {
        if (table.dataset.datatableInitialized) return;
        table.dataset.datatableInitialized = '1';

        new DataTable(table, {
            searchable: true,
            sortable: true,
            paging: true,
            perPage: 50,
            perPageSelect: [25, 50, 100],
            labels: {
                placeholder: 'Ara...',
                perPage: 'Sayfa basina {select} kayit',
                noRows: 'Kayit bulunamadi',
                noResults: 'Aramanizla eslesen kayit yok',
                info: '{rows} kayittan {start} - {end} arasi gosteriliyor',
            },
        });
    });
});
