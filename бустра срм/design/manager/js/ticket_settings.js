$(function() {
    // Инициализация вкладок
    $('#settingsTab a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    // Сохранение активной вкладки в localStorage
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('lastTicketSettingsTab', $(e.target).attr('href'));
    });

    // Восстановление последней активной вкладки
    var lastTab = localStorage.getItem('lastTicketSettingsTab');
    if (lastTab) {
        $('#settingsTab a[href="' + lastTab + '"]').tab('show');
    }

    // Общие функции для всех вкладок
    window.showSuccessMessage = function(message) {
        Swal.fire({
            type: 'success',
            title: 'Успешно',
            text: message
        });
    };

    window.showErrorMessage = function(message) {
        Swal.fire({
            type: 'error',
            title: 'Ошибка',
            text: message
        });
    };

    window.showConfirmation = function(title, text) {
        return Swal.fire({
            title: title,
            text: text,
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Да',
            cancelButtonText: 'Отмена'
        });
    };
});
