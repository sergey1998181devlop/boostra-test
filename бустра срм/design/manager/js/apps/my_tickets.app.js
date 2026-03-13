$(document).ready(function() {
    function initializeResizableColumns() {
        $('th.resizable').resizable({
            handles: 'e',
            stop: function (event, ui) {
                var index = $(this).index();
                var newWidth = ui.size.width;
    
                // Применяем ширину к каждому столбцу в таблице
                $('table#tickets tr').each(function() {
                    $(this).find('th, td').eq(index).width(newWidth);
                });
            }
        });
    }

    function saveColumnWidths() {
        var columnWidths = {};
        $('#tickets th.resizable').each(function() {
            var column = $(this).data('column');
            var width = $(this).width();
            columnWidths[column] = width;
        });
        localStorage.setItem('columnWidthsInTicketsPage', JSON.stringify(columnWidths));
    }

    function loadColumnWidths() {
        var columnWidths = JSON.parse(localStorage.getItem('columnWidthsInTicketsPage'));
        if (columnWidths) {
            $('#tickets th.resizable').each(function() {
                var column = $(this).data('column');
                if (columnWidths[column]) {
                    $(this).width(columnWidths[column]);
                }
            });
        }
    }

    loadColumnWidths();

    $('#tickets th.resizable').resizable({
        handles: 'e',
        stop: function(event, ui) {
            saveColumnWidths();
        }
    });
    
    // Функция для сбора всех фильтров
    function getFilters() {
        var filters = {};
        $('#filter-row input[type="text"], #filter-row input[type="date"], #filter-row select').each(function() {
            if ($(this).val() !== '') {
                filters[$(this).attr('name')] = $(this).val();
            }
        });
        return filters;
    }
    
    function loadTickets(page = 1) {
        var _sort = $('input[name="sort"]').val();
        
        // Сбор всех фильтров, чтобы передавать их при каждом запросе
        var _searches = getFilters(); 
        $('#filter-row input[type="text"], #filter-row input[type="date"], #filter-row select').each(function(){
            if ($(this).val() != '') {
                _searches[$(this).attr('name')] = $(this).val();
            }
        });

        // Добавляем пагинацию в запрос
        $.ajax({
            method: 'GET',
            data: {
                search: _searches,
                sort: _sort,
                page: page // Передаем текущую страницу
            },
            beforeSend: function() {
                $('#tickets tbody').html('<tr><td colspan="12">Загрузка...</td></tr>');
            },
            success: function(response) {
                $('#tickets').html($(response).find('#tickets').html());
                $('#pagination-nav').html($(response).find('#pagination-nav').html());
                initializeResizableColumns();
            },
            error: function() {
                $('#tickets').html('<p>Ошибка при загрузке данных.</p>');
            }
        });
    }
    
    // Отслеживание изменений фильтров
    $(document).on('change', '#filter-row input, #filter-row select', function() {
        loadTickets();
    });

    // Отслеживание кликов на сортировку
    $(document).on('click', '.sortable', function(e) {
        e.preventDefault();
        var sortValue = $(this).data('sort');
        $('input[name="sort"]').val(sortValue);
        loadTickets();
    });

    // Отслеживание пагинации
    $(document).on('click', '#pagination-nav a.page-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        if (page) {
            loadTickets(page);
        }
    });
    
    // Отслеживание кликов на пагинацию
    $(document).on('click', '#pagination-nav a.page-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        if (page) {
            loadTickets(page); // Загружаем тикеты для выбранной страницы
        }
    });
});
