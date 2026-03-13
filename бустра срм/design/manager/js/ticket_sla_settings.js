/**
 * JavaScript для SLA настроек тикетов
 */

$(document).ready(function() {
    // Отправка формы времени SLA
    $('#slaSettingsForm').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'Ошибка при сохранении настроек SLA');
            }
        });
    });

    // Сохранение SLA настроек менеджеров
    $('#saveSlaManagers').click(function() {
        var slaData = [];
        
        $('.sla-level-select').each(function() {
            var managerId = $(this).data('manager-id');
            var type = $(this).data('type');
            var slaLevel = $(this).val();
            
            if (slaLevel !== '') {
                slaData.push({
                    manager_id: managerId,
                    type: type,
                    sla_level: slaLevel
                });
            }
        });
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'save_sla_managers',
                sla_data: slaData
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'Ошибка при сохранении SLA настроек менеджеров');
            }
        });
    });
});

/**
 * Сброс формы SLA настроек
 */
function resetSLAForm() {
    if (confirm('Вы уверены, что хотите сбросить все настройки SLA?')) {
        location.reload();
    }
}

/**
 * Показать уведомление
 * @param {string} type - тип уведомления ('success' или 'error')
 * @param {string} message - сообщение
 */
function showNotification(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alert = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                  message +
                  '<button type="button" class="close" data-dismiss="alert">' +
                  '<span>&times;</span></button></div>');
    
    $('.sla-settings').prepend(alert);
    
    setTimeout(function() {
        alert.alert('close');
    }, 5000);
}
