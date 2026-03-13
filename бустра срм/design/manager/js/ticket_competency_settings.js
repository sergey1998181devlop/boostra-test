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

    // Функция перемещения опций между списками
    function moveOptions(fromSelectId, toSelectId) {
        var $selected = $('#' + fromSelectId + ' option:selected');
        $selected.each(function() {
            $(this).prop('selected', false);
            $('#' + toSelectId).append($(this));
        });
    }

    // Маппинг типов для правильного формирования ID
    var typeMapping = {
        'additional_services': 'dopy',
        'collection': 'collection'
    };

    // Обработка поиска и перемещения для каждого уровня компетенции
    ['additional_services', 'collection'].forEach(function(type) {
        var prefix = typeMapping[type];
        
        ['soft', 'middle', 'hard'].forEach(function(level) {
            // Поиск в списках
            $('#' + prefix + '-' + level + '-available-search').on('keyup', function() {
                filterList(prefix + '-' + level + '-available', $(this).val());
            });

            $('#' + prefix + '-' + level + '-selected-search').on('keyup', function() {
                filterList(prefix + '-' + level + '-selected', $(this).val());
            });

            // Кнопки перемещения
            $('#' + prefix + '-' + level + '-btn-move-down').on('click', function() {
                // При добавлении проверяем, нет ли менеджера в других уровнях
                var $selected = $('#' + prefix + '-' + level + '-available option:selected');
                var selectedIds = $selected.map(function() { return $(this).val(); }).get();

                // Проверяем другие уровни
                var otherLevels = ['soft', 'middle', 'hard'].filter(l => l !== level);
                var hasConflicts = false;
                var conflictManagers = [];

                otherLevels.forEach(function(otherLevel) {
                    $('#' + prefix + '-' + otherLevel + '-selected option').each(function() {
                        if (selectedIds.includes($(this).val())) {
                            hasConflicts = true;
                            conflictManagers.push($(this).text());
                        }
                    });
                });

                if (hasConflicts) {
                    Swal.fire({
                        type: 'warning',
                        title: 'Конфликт уровней',
                        html: 'Следующие менеджеры уже назначены на другой уровень:<br>' +
                              conflictManagers.join('<br>') +
                              '<br><br>Сначала удалите их с текущего уровня.'
                    });
                    return;
                }

                moveOptions(prefix + '-' + level + '-available', prefix + '-' + level + '-selected');
            });

            $('#' + prefix + '-' + level + '-btn-move-up').on('click', function() {
                moveOptions(prefix + '-' + level + '-selected', prefix + '-' + level + '-available');
            });
        });
    });

    // Сохранение компетенций
    $('.save-competencies').on('click', function() {
        var type = $(this).data('type');
        var competencies = {};
        var prefix = typeMapping[type];

        ['soft', 'middle', 'hard'].forEach(function(level) {
            competencies[level] = [];
            $('#' + prefix + '-' + level + '-selected option').each(function() {
                competencies[level].push($(this).val());
            });
        });

        $.ajax({
            url: '/tickets/settings',
            method: 'POST',
            data: {
                action: 'save_competencies',
                type: type,
                competencies: competencies
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        type: 'success',
                        title: 'Успешно',
                        text: 'Настройки компетенций успешно сохранены'
                    });
                } else {
                    Swal.fire({
                        type: 'error',
                        title: 'Ошибка',
                        text: response.message || 'Произошла ошибка при сохранении настроек'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    type: 'error',
                    title: 'Ошибка',
                    text: 'Произошла ошибка при сохранении настроек'
                });
            }
        });
    });

    // Подсказки
    $('[data-toggle="tooltip"]').tooltip();
});