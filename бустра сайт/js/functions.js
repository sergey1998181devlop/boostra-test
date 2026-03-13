/**
 * Отправка метрики
 * @param metric_goal_id
 */
function sendCustomMetric(metric_goal_id) {
    $.ajax({
        url: 'ajax/metric.php',
        data: {
            metric_goal_id
        },
        type: 'POST',
        dataType: 'json',
        success: function (resp) {
            console.log(resp);
        }
    });
}

/**
 * Проверяет АСП, по умолчанию дефолтная проверка смс
 * @param phone
 * @param code
 * @param asp_type
 * @param _callBack
 */
function checkASPCode (phone, code, asp_type, _callBack) {
    $.ajax({
        url: 'ajax/loan.php',
        data: {
            action: 'check_code',
            code,
            phone,
            asp_type,
        },
        method: 'GET',
        success: function (resp) {
            if(resp.soap_fault) {
                console.log(resp);
                alert(resp.error);
            } else {
                _callBack(resp);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
}
