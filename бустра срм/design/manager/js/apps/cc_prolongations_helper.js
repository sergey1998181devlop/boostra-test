/**
 * Вспомогательные функции для страницы cc_prolongations
 * Вынесено из шаблона для улучшения читаемости и поддержки
 */

/**
 * Получить ID выбранной организации
 * @returns {string}
 */
function selectedOrganizationId() {
    const $mkk = $('#selected-mkk');
    const value = $mkk.length ? $mkk.val() : '';
    return value === undefined || value === null ? '' : value;
}

/**
 * Получить отчет по SMS для пользователей
 */
async function getSmsToUsers() {
    try {
        $('.preloader').show();

        let dataRange = $(".sms-date").val();

        let resp = await $.ajax({
            url: '/ajax/get_sms_report.php?period=zero&date=' + dataRange,
            type: 'GET'
        });

        if (resp.success) {
            let a = document.createElement('a');
            a.target = '_blank';
            a.href = resp.file ?? '';
            a.click();
        } else {
            Swal.fire({
                timer: 5000,
                title: 'Ошибка',
                text: resp.error,
                icon: 'error'
            });
        }
    } catch (error) {
        console.error(error);
    } finally {
        $('.preloader').hide();
    }
}

/**
 * Отправить Push уведомления пользователям
 */
async function sendPushToUsers() {
    let users = $("[name='sms_check[]']:checked");

    let yourArray = [];

    // if not selected items
    if (users.length < 1) {
        Swal.fire({
            timer: 5000,
            title: 'Ошибка отправки СМС',
            text: 'Не выбраны отправители!!!',
            type: 'error',
        });

        return;
    }
    $('.preloader').show();

    $("[name='sms_check[]']:checked").each(function () {
        yourArray.push($(this).val());
    });
    var items_data = {
        users_ids: yourArray,
        limit: false,
        manager: window.managerId || 0
    };
    $.ajax({
        url: '/ajax/array_push_send.php',
        type: 'POST',
        data: items_data,
        success: function (resp) {
            resp = JSON.parse(resp);
            if (resp.success) {
                Swal.fire({
                    timer: 18000,
                    title: 'Отправка Push',
                    html: 'отправили уведомление ' + resp.count.pushes + ' пользователей, отправили сообщение ' + resp.count.sms + ' пользователей',
                    type: 'success',
                });
            } else {
                Swal.fire({
                    timer: 5000,
                    title: 'Ошибка отправки Push',
                    text: resp.sms,
                    type: 'error',
                });
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
    $('.preloader').hide();

    return;
}

/**
 * Скачать отчет по Push/SMS
 */
function downloadPushSmsCount() {
    $('.preloader').show();
    let dataRange = $(".daterange").val();
    $.ajax({
        type: 'GET',
        url: 'ajax/download-push-sms-count.php',
        data: {dataRange: dataRange},
        success: function (resp) {
            $('.preloader').hide();
            console.log(resp);
            resp = JSON.parse(resp);
            if (!resp.success) {
                Swal.fire({
                    timer: 5000,
                    title: 'Ошибка валидации',
                    text: resp.message,
                    type: 'error',
                });
                return;
            }
            window.open(resp.message);
        }
    });
}


