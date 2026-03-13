$(function() {
    // Функция фильтрации списка
    function filterList(listId, searchText) {
        var searchLower = searchText.toLowerCase();
        $("#" + listId + " option").each(function() {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(searchLower) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Поиск в списках
    ['dopy', 'collection', 'auto_assign_ticket'].forEach(function(type) {
        $('#' + type + '-available-search').on('keyup', function() {
            filterList(type + '-available-managers', $(this).val());
        });

        $('#' + type + '-selected-search').on('keyup', function() {
            filterList(type + '-selected-managers', $(this).val());
        });

        // Перемещение вправо
        $('#' + type + '-btn-move-right').on('click', function() {
            var $selected = $('#' + type + '-available-managers option:selected');
            $selected.each(function() {
                $(this).prop('selected', false);
                $('#' + type + '-selected-managers').append($(this));
            });
        });

        // Перемещение влево
        $('#' + type + '-btn-move-left').on('click', function() {
            var $selected = $('#' + type + '-selected-managers option:selected');
            $selected.each(function() {
                $(this).prop('selected', false);
                $('#' + type + '-available-managers').append($(this));
            });
        });
    });

    // Обработчик кнопок сохранения
    $('.save-managers').on('click', function() {
        var type = $(this).data('type');
        var managerIds = [];

        $('#' + type + '-selected-managers option').each(function() {
            managerIds.push(parseInt($(this).val()));
        });

        $.ajax({
            url: '/tickets/settings',
            method: 'POST',
            data: {
                action: 'save_authorized_managers',
                type: type,
                manager_ids: managerIds
            },
            success: function(response) {
                if (response.success) {
                    window.showSuccessMessage('Настройки доступа успешно сохранены');
                } else {
                    window.showErrorMessage(response.message || 'Произошла ошибка при сохранении настроек');
                }
            },
            error: function() {
                window.showErrorMessage('Произошла ошибка при сохранении настроек');
            }
        });
    });
});
