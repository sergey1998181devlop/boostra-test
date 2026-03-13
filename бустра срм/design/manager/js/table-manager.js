class TableManager {
    constructor(options) {
        this.options = {
            tableId: null,
            filterFormId: 'filter-form',
            filterRowId: 'filter-row',
            paginationId: 'pagination-nav',
            storageKey: null,
            pageCapacity: 15,
            url: null,
            onLoad: null,
            dateRangeSelector: 'input[name="daterange"]',
            dateRangeOptions: {}, // Добавлено для передачи опций daterangepicker
            autoUpdateInterval: null, // Интервал авто-обновления в миллисекундах
            ...options
        };

        if (!this.options.tableId || !this.options.url) {
            throw new Error('TableId and URL are required options');
        }

        this.table = $(`#${this.options.tableId}`);
        this.filterForm = $(`#${this.options.filterFormId}`);
        this.filterRow = $(`#${this.options.filterRowId}`);
        this.autoUpdateTimer = null; // Таймер для авто-обновления
        this.state = {
            filters: {},
            sort: '',
            page: 1,
        };

        this.init();
    }

    init() {
        this.restoreStateFromUrl();
        this.initResizableColumns();
        this.initFilters();
        this.initEventListeners();
        this.loadColumnWidths();

        if (this.options.autoUpdateInterval) {
            this.startAutoUpdate();
        }
    }

    startAutoUpdate() {
        this.stopAutoUpdate();

        this.autoUpdateTimer = setInterval(() => {
            this.loadData();
        }, this.options.autoUpdateInterval);
    }

    stopAutoUpdate() {
        if (this.autoUpdateTimer) {
            clearInterval(this.autoUpdateTimer);
            this.autoUpdateTimer = null;
        }
    }

    initFilters() {
        const defaultOptions = {
            autoApply: true,
            locale: {
                format: 'DD.MM.YYYY'
            }
        };

        $(this.options.dateRangeSelector).daterangepicker({
            ...defaultOptions,
            ...this.options.dateRangeOptions
        });

        // Обработка отправки формы фильтров
        this.filterForm.on('submit', (e) => {
            e.preventDefault();
            this.state.page = 1;
            this.loadData();
        });

        // Обработка кнопки "Выгрузить"
        this.filterForm.on('click', '.download-btn', (e) => {
            e.preventDefault();
            this.download();
        });
    }

    restoreStateFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const filters = {};
        for (const [key, value] of urlParams.entries()) {
            if (key.startsWith('search[')) {
                const filterName = key.slice(7, -1);
                filters[filterName] = value;
            } else if (key === 'sort') {
                this.state.sort = value;
            } else if (key === 'page') {
                this.state.page = parseInt(value, 10);
            }
        }

        this.state.filters = filters;

        for (const key in filters) {
            this.filterForm.find(`[name="${key}"]`).val(filters[key]);
        }

        if (this.state.sort) {
            $('input[name="sort"]').val(this.state.sort);
            const sortField = this.state.sort.startsWith('-') ? this.state.sort.substring(1) : this.state.sort;
            const sortDirection = this.state.sort.startsWith('-') ? 'desc' : 'asc';
            const sortElement = this.table.find(`[data-sort="${sortField}"]`);
            if (sortElement.length) {
                sortElement.addClass(sortDirection);
            }
        }
    }

    getFilters() {
        const filters = {};
        this.filterForm.find('input[type="text"], input[type="date"], select[name]').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            if (value !== '') {
                filters[name] = value;
            }
        });
        
        this.filterRow.find('input[type="text"], input[type="date"], select[name]').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            if (value !== '') {
                filters[name] = value;
            }
        });
        
        return filters;
    }

    updateUrl() {
        const queryParams = new URLSearchParams();

        for (const key in this.state.filters.search) {
            queryParams.append(`search[${key}]`, this.state.filters[key]);
        }

        if (this.state.sort) {
            queryParams.append('sort', this.state.sort);
        }

        if (this.state.page > 1) {
            queryParams.append('page', this.state.page.toString());
        }

        const newUrl = `${window.location.pathname}?${queryParams.toString()}`;
        window.history.pushState({}, '', newUrl);
    }

    loadData() {
        this.state.filters = this.getFilters();
        this.state.sort = $('input[name="sort"]').val();

        // Формируем параметры запроса
        const queryParams = new URLSearchParams();
        for (const key in this.state.filters) {
            queryParams.append(`search[${key}]`, this.state.filters[key]);
        }
            
        if (this.state.sort) {
            queryParams.append('sort', this.state.sort);
        }
        
        if (this.state.page) {
            queryParams.append('page', this.state.page);
        }

        $.ajax({
            url: `${this.options.url}?${queryParams.toString()}`,
            method: 'GET',
            success: (response) => {
                const $response = $(response);
                const newTbody = $response.find(`#${this.options.tableId} tbody`);
                this.table.find('tbody').replaceWith(newTbody);
                $(`#${this.options.paginationId}`).html(
                    $response.find(`#${this.options.paginationId}`).html()
                );

                this.initResizableColumns();
                this.updateUrl();

                if (this.options.onLoad) {
                    this.options.onLoad(response);
                }
            },
            error: () => {
                this.table.find('tbody').html(
                    '<tr><td colspan="100%" class="text-center text-danger">Ошибка при загрузке данных</td></tr>'
                );
            }
        });
    }

    download() {
        this.state.filters = this.getFilters();
        this.state.sort = $('input[name="sort"]').val();
    
        const queryParams = new URLSearchParams();
        for (const key in this.state.filters) {
            queryParams.append(`search[${key}]`, this.state.filters[key]);
        }
        if (this.state.sort) {
            queryParams.append('sort', this.state.sort);
        }
        queryParams.append('action', 'download');
    
        const url = `${this.options.url}?${queryParams.toString()}`;
        window.location.href = url;
    }


    initResizableColumns() {
        if (!this.options.storageKey) return;

        this.table.find('th.resizable').resizable({
            handles: 'e',
            stop: (event, ui) => {
                const index = $(ui.element).index();
                const newWidth = ui.size.width;

                this.table.find('tr').each(function() {
                    $(this).find('th, td').eq(index).width(newWidth);
                });

                this.saveColumnWidths();
            }
        });
    }

    saveColumnWidths() {
        if (!this.options.storageKey) return;

        const columnWidths = {};
        this.table.find('th.resizable').each(function() {
            const column = $(this).data('column');
            const width = $(this).width();
            columnWidths[column] = width;
        });
        localStorage.setItem(this.options.storageKey, JSON.stringify(columnWidths));
    }

    loadColumnWidths() {
        if (!this.options.storageKey) return;

        const columnWidths = JSON.parse(localStorage.getItem(this.options.storageKey));
        if (columnWidths) {
            this.table.find('th.resizable').each(function() {
                const column = $(this).data('column');
                if (columnWidths[column]) {
                    $(this).width(columnWidths[column]);
                }
            });
        }
    }

    initEventListeners() {
        this.table.on('click', '.sortable', (e) => {
            e.preventDefault();
            const sortField = $(e.currentTarget).data('sort');
            const currentSort = this.state.sort;
            const isDesc = currentSort === sortField ? false : currentSort === `-${sortField}`;
            const newSort = isDesc ? sortField : `-${sortField}`;
        
            this.state.sort = newSort;
            $('input[name="sort"]').val(newSort);
            this.state.page = 1;
            this.loadData();
        });

        // Пагинация
        $(document).on('click', `#${this.options.paginationId} a.page-link`, (e) => {
            e.preventDefault();
            const page = $(e.currentTarget).data('page');
            if (page) {
                this.state.page = page;
                this.loadData();
            }
        });
    }
}
