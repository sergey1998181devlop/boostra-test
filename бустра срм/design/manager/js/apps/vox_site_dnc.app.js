/* jshint esversion: 8 */
/**
 * CRUD для Vox DNC по сайтам (отключение звонков робота).
 * Запросы к /app/vox-site-dnc (сессия менеджера).
 */
class VoxSiteDncApp {
    constructor() {
        this.API_BASE = '/app/vox-site-dnc';
        this.config = window.VoxSiteDncConfig || {sites: [], organizations: []};

        this.loadList();
        this.initFilter();
        this.initAddButton();
        this.initSaveButton();
        this.initTableClick();
    }

    getSiteTitle(siteId) {
        const id = parseInt(siteId, 10);
        const site = (this.config.sites || []).find((s) => {
            return parseInt(s.site_id, 10) === id || s.site_id === siteId;
        });
        return site ? (site.title || site.domain || 'site_id: ' + siteId) : 'site_id: ' + siteId;
    }

    getOrgName(orgId) {
        const id = parseInt(orgId, 10);
        const org = (this.config.organizations || []).find((o) => parseInt(o.id, 10) === id);
        return org ? (org.short_name || org.name || 'id: ' + orgId) : 'id: ' + orgId;
    }

    maskToken(token) {
        if (!token || token.length < 8) return token ? '***' : '—';
        return token.substring(0, 4) + '…' + token.substring(token.length - 4);
    }

    getFilterSiteId() {
        const el = document.querySelector('.js-vox-site-dnc-filter-site');
        return el ? el.value : '';
    }

    escapeHtml(s) {
        if (s == null) return '';
        const div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    loadList() {
        const tbody = document.querySelector('#voxSiteDncTable tbody');
        if (!tbody) return;

        tbody.innerHTML = '<tr class="js-loading-row"><td colspan="10" class="text-center text-muted">Загрузка...</td></tr>';

        const siteId = this.getFilterSiteId();
        const url = siteId ? this.API_BASE + '?site_id=' + encodeURIComponent(siteId) : this.API_BASE;

        fetch(url, {method: 'GET', credentials: 'same-origin'})
            .then((r) => {
                if (r.status === 403) {
                    throw new Error('Доступ запрещён. Выполните вход в CRM.');
                }
                return r.json();
            })
            .then((res) => {
                tbody.innerHTML = '';
                if (!res.success || !res.data || !res.data.length) {
                    tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Нет записей</td></tr>';
                    return;
                }
                res.data.forEach((row) => {
                    const tr = document.createElement('tr');
                    tr.setAttribute('data-id', row.id);
                    tr.innerHTML =
                        '<td>' + row.id + '</td>' +
                        '<td>' + this.escapeHtml(this.getSiteTitle(row.site_id)) + ' <small class="text-muted">(' + row.site_id + ')</small></td>' +
                        '<td>' + this.escapeHtml(this.getOrgName(row.organization_id)) + ' <small class="text-muted">(' + row.organization_id + ')</small></td>' +
                        '<td>' + this.escapeHtml(row.vox_domain || '—') + '</td>' +
                        '<td>' + this.escapeHtml(this.maskToken(row.vox_token)) + '</td>' +
                        '<td>' + this.escapeHtml(row.api_url || '—') + '</td>' +
                        '<td>' + (row.outgoing_calls_dnc_list_id != null ? row.outgoing_calls_dnc_list_id : '—') + '</td>' +
                        '<td>' + (row.is_active ? 'Да' : 'Нет') + '</td>' +
                        '<td>' + this.escapeHtml(row.comment || '—') + '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-sm btn-outline-primary js-vox-site-dnc-edit mr-2" data-id="' + row.id + '">Изменить</button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger js-vox-site-dnc-delete mt-2" data-id="' + row.id + '">Удалить</button>' +
                        '</td>';
                    tbody.appendChild(tr);
                });
            })
            .catch((err) => {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Ошибка: ' + this.escapeHtml(err.message) + '</td></tr>';
            });
    }

    getFormData() {
        const siteVal = document.getElementById('voxSiteDncSiteId').value.trim();
        const orgVal = document.getElementById('voxSiteDncOrganizationId').value.trim();
        const orgNum = orgVal === '' ? NaN : parseInt(orgVal, 10);
        return {
            site_id: siteVal === '' ? null : siteVal,
            organization_id: (orgVal === '' || isNaN(orgNum)) ? null : orgNum,
            vox_domain: document.getElementById('voxSiteDncDomain').value.trim() || null,
            vox_token: document.getElementById('voxSiteDncToken').value.trim() || null,
            api_url: document.getElementById('voxSiteDncApiUrl').value.trim() || null,
            outgoing_calls_dnc_list_id: document.getElementById('voxSiteDncDncListId').value.trim()
                ? parseInt(document.getElementById('voxSiteDncDncListId').value, 10)
                : null,
            is_active: document.getElementById('voxSiteDncIsActive').checked ? 1 : 0,
            comment: document.getElementById('voxSiteDncComment').value.trim() || null
        };
    }

    setFormData(data) {
        document.getElementById('voxSiteDncId').value = data.id || '';
        document.getElementById('voxSiteDncSiteId').value = data.site_id != null ? data.site_id : '';
        document.getElementById('voxSiteDncOrganizationId').value = data.organization_id != null ? data.organization_id : '';
        document.getElementById('voxSiteDncDomain').value = data.vox_domain || '';
        document.getElementById('voxSiteDncToken').value = data.vox_token || '';
        document.getElementById('voxSiteDncApiUrl').value = data.api_url || '';
        document.getElementById('voxSiteDncDncListId').value = data.outgoing_calls_dnc_list_id != null ? data.outgoing_calls_dnc_list_id : '';
        document.getElementById('voxSiteDncIsActive').checked = data.is_active !== 0;
        document.getElementById('voxSiteDncComment').value = data.comment || '';
    }

    clearForm() {
        this.setFormData({
            id: '',
            site_id: '',
            organization_id: '',
            vox_domain: '',
            vox_token: '',
            api_url: '',
            outgoing_calls_dnc_list_id: '',
            is_active: 1,
            comment: ''
        });
    }

    showMessage(msg, isError) {
        if (typeof window.Swal !== 'undefined' && window.Swal.fire) {
            window.Swal.fire({
                title: isError ? 'Ошибка' : 'Успешно',
                text: msg,
                type: isError ? 'error' : 'success'
            });
        } else {
            alert(msg);
        }
    }

    openModal(isEdit) {
        document.querySelector('.js-modal-title').textContent = isEdit ? 'Редактировать запись' : 'Добавить запись';
        if (!isEdit) this.clearForm();
        $('#voxSiteDncModal').modal('show');
    }

    saveRecord() {
        const id = document.getElementById('voxSiteDncId').value.trim();
        const data = this.getFormData();
        if (data.site_id == null || data.site_id === '' || data.organization_id == null) {
            this.showMessage('Выберите сайт и организацию', true);
            return;
        }

        const isEdit = !!id;
        const url = isEdit ? this.API_BASE + '/' + id : this.API_BASE;
        const method = isEdit ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
            .then((r) => {
                if (r.status === 403) throw new Error('Доступ запрещён');
                return r.json();
            })
            .then((res) => {
                if (res.success) {
                    this.showMessage(res.message || 'Сохранено');
                    $('#voxSiteDncModal').modal('hide');
                    this.loadList();
                } else {
                    this.showMessage(res.message || 'Ошибка сохранения', true);
                }
            })
            .catch((err) => {
                this.showMessage(err.message || 'Ошибка запроса', true);
            });
    }

    deleteRecord(id) {
        const self = this;
        if (typeof window.Swal !== 'undefined' && window.Swal.fire) {
            window.Swal.fire({
                title: 'Вы уверены?',
                text: 'Запись будет удалена.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена'
            }).then(function(result) {
                if (result.value) {
                    self.doDelete(id);
                }
            });
        } else {
            if (confirm('Удалить запись?')) this.doDelete(id);
        }
    }

    doDelete(id) {
        const self = this;
        fetch(this.API_BASE + '/' + id, {method: 'DELETE', credentials: 'same-origin'})
            .then((r) => {
                if (r.status === 403) throw new Error('Доступ запрещён');
                return r.json();
            })
            .then((res) => {
                if (res.success) {
                    this.showMessage(res.message || 'Удалено');
                    this.loadList();
                } else {
                    this.showMessage(res.message || 'Ошибка удаления', true);
                }
            })
            .catch((err) => {
                this.showMessage(err.message || 'Ошибка запроса', true);
            });
    }

    initFilter() {
        const el = document.querySelector('.js-vox-site-dnc-filter-site');
        if (el) {
            el.addEventListener('change', () => this.loadList());
        }
    }

    initAddButton() {
        const el = document.querySelector('.js-vox-site-dnc-add');
        if (el) {
            el.addEventListener('click', () => this.openModal(false));
        }
    }

    initSaveButton() {
        const el = document.querySelector('.js-vox-site-dnc-save');
        if (el) {
            el.addEventListener('click', () => this.saveRecord());
        }
    }

    initTableClick() {
        const table = document.getElementById('voxSiteDncTable');
        if (!table) return;

        table.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.js-vox-site-dnc-edit');
            const delBtn = e.target.closest('.js-vox-site-dnc-delete');
            if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                fetch(this.API_BASE + '/' + id, {method: 'GET', credentials: 'same-origin'})
                    .then((r) => r.json())
                    .then((res) => {
                        if (res.success && res.data) {
                            this.setFormData(res.data);
                            document.getElementById('voxSiteDncId').value = id;
                            this.openModal(true);
                        }
                    });
            } else if (delBtn) {
                this.deleteRecord(delBtn.getAttribute('data-id'));
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new VoxSiteDncApp();
});
