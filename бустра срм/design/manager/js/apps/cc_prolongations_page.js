/**
 * Основной JavaScript файл для страницы cc_prolongations
 * Содержит всю логику инициализации и обработчиков событий
 * 
 * Требует наличия window.CCProlongationsConfig с данными:
 * - managerId: ID менеджера
 * - tvMedicalPrice: Цена телемедицины
 * - managers: Объект менеджеров
 */

(function() {
    'use strict';

    // Проверка наличия конфигурации
    if (!window.CCProlongationsConfig) {
        console.error('CCProlongationsConfig не найден!');
        return;
    }

    const config = window.CCProlongationsConfig;
    let date = null;
    let buffManagers = [];
    let managers = null;
    let boolean = false;

    // Инициализация переменных для JavaScript
    window.managerId = config.managerId;

    // Инициализация date pickers
    $('.sms-date').daterangepicker({
        autoApply: true,
        locale: {
            format: 'YYYY.MM.DD'
        },
        default: ''
    });

    // Обработчики событий
    $(document).on('click', '.downloadPushSmsCount', function () {
        downloadPushSmsCount();
    });

    $(function(){
        $('.js-filter-manager').select2({
            placeholder: "Менеджеры"
        });
    });

    $(function(){
        $('.daterange').daterangepicker({
            autoApply: true,
            locale: {
                format: 'YYYY.MM.DD'
            },
            default:''
        });

        $(document).on('click', '.runFilter', function(e){
            $(this).closest('form').submit();
        });

        // нулёвки за 3/5 дней до
        $(document).on('submit', '#form_zero_days_payment', function (e) {
            e.preventDefault();
            var $form = $(this);
            if (!$form.find('input[name="organization_id"]').length) {
                $('<input>', {
                    type: 'hidden',
                    name: 'organization_id',
                    value: $('#selected-mkk').val()
                }).appendTo($form);
            }

            $.ajax({
                url: $form.attr('action'),
                data: $form.serialize(),
                type: 'POST',
                beforeSend: function () {
                    $form.addClass('loading');
                },
                success: function (resp) {
                    if (resp.success) {
                        console.log(resp);
                        Swal.fire({
                            timer: 18000,
                            title: 'Отправка в VOX',
                            html: resp.text,
                            type: 'success',
                        });
                    } else {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    $form.removeClass('loading');
                }
            });
        });

        $(document).on('click', '#distribute_me', function(e){
            e.preventDefault();
            var $form = $(this);

            if ($form.hasClass('loading'))
                return false;

            console.log(location.hash);
            var _hash = location.hash.replace('#', '?');
            $.ajax({
                data: {
                    action: 'distribute_me'
                },
                type: 'POST',
                beforeSend: function(){
                    $form.addClass('loading');
                },
                success: function(resp){
                    if (resp.success) {
                        $('#modal_distribute').modal('hide');
                        Swal.fire({
                            timer: 5000,
                            title: 'Договора распределены.',
                            type: 'success',
                        });
                        location.reload();
                    } else {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    $form.removeClass('loading');
                }
            });
        });

        $(document).on('click', '.update-user-balance', function () {
           let task_id = $(this).data('task'),
               user_id = $(this).data('user'),
               $_td = $(this).closest('td');

            $.ajax({
                url: '/order/0000',
                type: 'POST',
                data: {
                    action: 'get_balance',
                    user_id: user_id,
                    update: 1
                },
                beforeSend: function(){
                    $_td.addClass('data-loading');
                },
                success: function(resp){
                    if (!resp.error) {
                        let $_ostatok = $('#ostatok_' + task_id).closest('td.zaim-ostatok-odd'),
                            $_prolongation = $('#prolongation_' + task_id).closest('td.zaim-prolongation'),
                            amount_full = parseInt(resp.balance.ostatok_od) + parseInt(resp.balance.ostatok_percents) + parseInt(resp.balance.ostatok_peni),
                            amount_prolongation = parseInt(resp.balance.prolongation_amount) + config.tvMedicalPrice;

                        if (!$_prolongation.is('a') && amount_prolongation > 0) {
                            let html = '<a class="" href="#" data-toggle="collapse" data-target="#prolongation_' + task_id + '"></a>' +
                                '<div id="prolongation_' + task_id + '" class="collapse">' +
                                '<div class="prolongation_percent">Проценты: <strong></strong></div> ' +
                                '<div class="prolongation_insurer">Страховка: <strong></strong></div> ' +
                                '<div class="prolongation_prolongation">Пролонгация: <strong></strong></div> ' +
                                '<div class="prolongation_sms">СМС-информ: <strong></strong></div> ' +
                                '<div>Телемедицина (Лайт) <strong>' + config.tvMedicalPrice + '</strong></div> ' +
                                '</div>';

                            $_prolongation.html(html);
                        }

                        $_ostatok.find('a').text(amount_full);
                        $_ostatok.find('div > div').eq(0).find('strong').text(resp.balance.ostatok_od);
                        $_ostatok.find('div > div').eq(1).find('strong').text(resp.balance.ostatok_percents);
                        $_ostatok.find('div > div').eq(2).find('strong').text(resp.balance.ostatok_peni);

                        $_prolongation.find('a').text(amount_prolongation);
                        $_prolongation.find('div .prolongation_percent').find('strong').text(resp.balance.prolongation_summ_percents);
                        $_prolongation.find('div .prolongation_insurer').find('strong').text(resp.balance.prolongation_summ_insurance);
                        $_prolongation.find('div .prolongation_prolongation').find('strong').text(resp.balance.prolongation_summ_cost);
                        $_prolongation.find('div .prolongation_sms').find('strong').text(resp.balance.prolongation_summ_sms);

                        Swal.fire({
                            timer: 5000,
                            title: 'Действие выполнено',
                            text: 'Баланс пользователя успешно обновлен',
                            type: 'success',
                        });
                    } else {
                        Swal.fire({
                            timer: 5000,
                            title: 'Произошла ошибка',
                            text: 'Ошибка: ' + resp.error,
                            type: 'error',
                        });
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                },
            }).done(function () {
                $_td.removeClass('data-loading');
            });
        });
    });

    $(".js-schedule-open").click(function (){
        $('.schedule-calendar-div').toggleClass('showDiv');
    });

    $('.employs-button').click(function (){
        var content = $('.employs-div');
        if (content.find('.employs-list').length > 0) {
            $('.employs-div').empty();
        } else {
            $('.employs-div').empty();
            $.ajax({
                type: 'GET',
                url:'ajax/get_company_managers.php',
                data: {
                    organization_id: selectedOrganizationId()
                },
                success: function(resp){
                    $('.employs-list').empty();
                    $(".employs-div-buttons").remove();
                    $('.employs-div').append(`<ul class="employs-list"> </ul>`);
                    resp.managers.forEach(function (m) {
                        $('.employs-list').append(`
                          <li>
                            <div class="main-input-div" >
                            <div class="input-div">
                                <input class="" name="managers[]" id="distribute_` + m.id + `" value="` + m.id + `" type="checkbox" />
                             </div>
                                <label for='distribute_` + m.id + `' class=''>` + m.name + ` </label>
                            </div>
                        </li>
                    `);
                    });
                    $('.employs-div').append(
                        `<div class = "employs-div-buttons">
                        <button class="replace-operator-button">Заменить оператора</button>
                        <button class="apply-button">Применить</button>
                    </div>`
                    );
                }
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            height: 350,
            plugins: [ 'dayGrid', 'interaction' ],
            dateClick: function(info) {
                $('input[name="managers[]"]').prop('checked', false);
                var selectedDate = document.querySelector('.selected-date');
                if (selectedDate) {
                    selectedDate.classList.remove('selected-date');
                }
                info.dayEl.classList.add('selected-date');
                date = info.dateStr;
                $.ajax({
                    type: 'Get',
                    url:'ajax/get_company_managers.php',
                    data: {
                        date: date,
                        organization_id: selectedOrganizationId()
                    },
                    success: function(resp){
                        buffManagers = [];
                        resp.managers.forEach(function (m) {
                            buffManagers.push(m.id);
                            $("#distribute_" + m.id).prop('checked', true);
                        });
                    }
                });
            },
        });
        calendar.render();
    });

    $(document).on('click', '.apply-button', function() {
        var checkedValues = $('input[name="managers[]"]:checked').map(function() {
            return $(this).val();
        }).get();
        managers = checkedValues;
        let currentDate = new Date();
        let year = currentDate.getFullYear();
        let month = String(currentDate.getMonth() + 1).padStart(2, '0');
        let day = String(currentDate.getDate()).padStart(2, '0');
        let formattedDate = year + '-' + month + '-' + day;
        let deleted = buffManagers.filter(value => !managers.includes(value));
        let added = managers.filter(value => !buffManagers.includes(value));

        if(date == formattedDate && checkedValues.length && boolean){
            $(".modal-wrapper").css('display', 'flex');
            let txt = "";
            deleted.forEach(function (el) {
                let value = $("label[for='distribute_" + el + "']").text();
                txt += value + " ";
            });
            txt += 'будут распределены на ';
            added.forEach(function (el) {
                let value = $("label[for='distribute_" + el + "']").text();
                txt += value + " ";
            });
            $('.employs-names').html(txt);
            $('.third-p').html("");
            $('body').css('overflow', 'hidden');
        } else if(date == formattedDate && !(deleted.length)){
            $(".modal-wrapper").css('display', 'flex');
            $('.third-p').html("будут распределены");
        } else if(date == formattedDate && checkedValues.length){
            $(".modal-wrapper").css('display', 'flex');
            let txt = "";
            deleted.forEach(function (el) {
                let value = $("label[for='distribute_" + el + "']").text();
                txt += value + ", ";
            });
            $('.employs-names').html(txt);
            $('body').css('overflow', 'hidden');
        } else if(date && checkedValues.length){
            $.ajax({
                type: 'POST',
                url:'ajax/insert-schedule.php',
                data: { date: date, managers: checkedValues, organization_id: selectedOrganizationId()},
                success: function(resp){
                    Swal.fire({
                        timer: 5000,
                        title: 'Сотрудники назначены.',
                        type: 'success',
                    });
                }
            });
        }
    });

    $('.employs-names-button-no').click(function () {
        $(".modal-wrapper").css('display', 'none');
        $('body').css('overflow', 'auto');
    });

    // Обработка выбора МКК
    $(document).on('click', '.mkk-btn', function() {
        const mkkId = $(this).data('mkk');
        const mkkName = $(this).data('mkk-name');
        $('#selected-mkk').val(mkkId);
        $('#selected-mkk-name').val(mkkName);
        const targetUrl = new URL(window.location.href);
        targetUrl.searchParams.set('organization_id', mkkId);
        window.location.href = targetUrl.toString();
    });

    $(document).on('submit', 'form', function () {
        const selectedMkk = $('#selected-mkk').val();
        if (selectedMkk && !$(this).find('input[name="organization_id"]').length) {
            $('<input>', {
                type: 'hidden',
                name: 'organization_id',
                value: selectedMkk
            }).appendTo(this);
        }
    });

    $('.employs-names-button-yes').click(function () {
        const oldManagers = [];
        buffManagers.forEach(item => {
            oldManagers.push(item);
        });
        $(this).prop('disabled', true);
        $('.employs-names-button-no').prop('disabled', true);
        let deleted = oldManagers.filter(value => !managers.includes(value));
        let added = managers.filter(value => !oldManagers.includes(value));
        var _hash = location.hash.replace('#', '?');
        let txt = 'action=distribute&period=zero';
        const selectedMkk = $('#selected-mkk').val();
        const selectedMkkName = $('#selected-mkk-name').val();
        txt += '&organization_id=' + selectedMkk;
        if (date) {
            txt += '&task_date=' + date;
        }
        
        managers.forEach(function (el) {
            txt += '&managers[]='+ el;
        });
        deleted.forEach(item=> {
            txt += '&deleted[]='+ item;
        });
        added.forEach(item=> {
            txt += '&added[]='+ item;
        });
        txt += '&boolean='+boolean;
        let newData = false;
        let diffAdded = managers.filter(value => added.includes(value));
        if (diffAdded.length > 0){
            newData = true;
            let diffAddedMan = managers.filter(value => !added.includes(value));
            diffAddedMan.forEach(item=> {
                txt += '&diffAddedMan[]='+ item;
            });
        }
        txt += '&newData='+ newData;

        if(!$(this).hasClass('loading')){
            $(".loader-icon").css("display","block");
            $(".modal-wrapper").css('opacity','0.5');
            $.ajax({
                type: 'POST',
                url:'ajax/insert-schedule.php',
                data: { date: date, managers: managers, organization_id: $('#selected-mkk').val()},
            })
                .then(function () {
                    $.ajax({
                        url: '/ccprolongations' + _hash,
                        data: txt,
                        type: 'POST',
                        beforeSend: function () {
                            $(this).addClass('loading');
                        },
                        success: (resp) => {
                            $(this).prop('disabled', false);
                            $('.employs-names-button-no').prop('disabled', false);
                            $(".modal-wrapper").css('display', 'none');
                            $('body').css('overflow', 'auto');
                            $(".loader-icon").css("display", "none");
                            if (resp.success) {
                                $('#modal_distribute').modal('hide');
                                // Отправка в Vox после распределения
                                sendToVox(managers, selectedMkk, selectedMkkName);
                            } else {
                                Swal.fire({
                                    text: resp.error,
                                    type: 'error',
                                });
                            }
                            $(this).removeClass('loading');
                        }
                    });
                });
        }
    });

    // Функция отправки в Vox
    function sendToVox(managers, organizationId, organizationName) {
        $('.preloader').show();
        $.ajax({
            type: 'POST',
            url: 'ajax/send_to_vox_mkk.php',
            data: {
                managers: managers,
                organization_id: organizationId,
                date: date
            },
            success: function(resp) {
                $('.preloader').hide();
                let message = '';
                console.log(resp);
                if (resp.success) {
                    message = 'Всё прошло удачно. Отправилось ' + resp.count + ' номеров. ' + organizationName;
                    $('#vox-result-message').html('<div class="alert alert-success">' + message + '</div>');
                } else {
                    message = 'Номера тел не отправились в Vox. ' + organizationName + '<br>' + (resp.error || 'Произошла ошибка');
                    $('#vox-result-message').html('<div class="alert alert-danger">' + message + '</div>');
                }
                $('#voxResultModal').modal('show');
            },
            error: function(xhr, status, error) {
                $('.preloader').hide();
                let message = 'Номера тел не отправились в Vox. ' + organizationName + '<br>Ошибка: ' + error;
                $('#vox-result-message').html('<div class="alert alert-danger">' + message + '</div>');
                $('#voxResultModal').modal('show');
            }
        });
    }

    $(document).on('click', '.replace-operator-button', function() {
        boolean = true;
    });

    $(document).on('change', 'input[name="managers[]"]', function () {
        if(boolean) {
            var checkedValues = $('input[name="managers[]"]:checked').map(function() {
                return $(this).val();
            }).get();
            let deleted = buffManagers.filter(value => !checkedValues.includes(value));
            let added = checkedValues.filter(value => !buffManagers.includes(value));
            $(this).parent().css("background-color", "#fff");
            $('#distribute_' + deleted).parent().css("background-color", "red");
            $('#distribute_' + added).css('accent-color', 'green');
        }
    });

    $('.employs-edit').click(function () {
        let content = $('.employs-div');
        if (content.find('.vox-inputs').length > 0) {
            $('.employs-div').empty();
        } else {
            $('.employs-div').empty();
            $.ajax({
                type: 'GET',
                url: 'ajax/get_company_managers.php',
                data: {
                    bool: true,
                    organization_id: selectedOrganizationId()
                },
                success: function (resp) {
                    $('.employs-div').append(`<div class="vox-div">
            <select multiple  class="form-control  js-vox-manager" id="js-vox-manager" name="manager_id" placeholder = "Менеджеры">
            </select>
            </div>`);
                    resp.managers.forEach(function (m) {
                        if (m.company) {
                            $('.js-vox-manager').append(`
                    <option value="` + m.id + `">` + m.name + `  pds:` + m.company + `  dnc:` + m.dnc_number + `</option>
                `);
                        } else {
                            $('.js-vox-manager').append(`
                    <option value="` + m.id + `">` + m.name + `</option>
                `);
                        }
                    });
                    $('.vox-div').append(`<div class = "vox-inputs">
                <div>
                    <label for="">ID PDS</label>
                    <input class="pds" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                </div>
                <div>
                    <label for="">ID DNC</label>
                  <input class = "dnc" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                </div>
        </div>
        <button class = "add-manager-to-vox">Применить </button>`);
                    $('.js-vox-manager').select2({
                        width: '100%',
                        placeholder: "Менеджеры",
                        allowClear: true,
                        maximumSelectionLength: 1,
                    });
                }
            });
        }
    });

    $(document).on('click', '.add-manager-to-vox', function () {
        let manager_id = $('.js-vox-manager').val().join('');
        console.log(manager_id);
        let pds = $('.pds').val();
        let dnc = $('.dnc').val();
        if (manager_id && pds && dnc) {
            $.ajax({
                type: 'POST',
                url: 'ajax/add_vox_manager.php',
                data: {
                    manager_id: manager_id,
                    pds: pds,
                    dnc: dnc,
                    organization_id: selectedOrganizationId()
                },
                success: function (resp) {
                    $('.modal-wrapper-vox>.div-modal').append(
                        `<p style="width: 50%;">
                           Настройки сотрудника ` + resp.name + ` c ID PPS `+ pds +` и ID DNC `+dnc+` успешно изменены
                        </p>
                        <div class="employs-vox-buttons">
                        <button class="employs-vox-names-button-yes btn btn-success">Да</button>
                </div>`
                    );
                    $('.js-vox-manager').val(null).trigger('change');
                    $('.pds').val("");
                    $('.dnc').val("");
                    $('.modal-wrapper-vox').css('display','flex');
                }
            });
        }
    });

    $(document).on('click','.employs-vox-names-button-yes', function () {
        $('.modal-wrapper-vox').css('display','none');
        location.reload();
    });

    $(document).on('click','.downloadCallList',function () {
        $('.preloader').show();
        let dataRange = $(".daterange").val();
        let manager = $('#js-filter-manager').val();
        $.ajax({
            type: 'GET',
            url:'ajax/download-call-list.php',
            data: { dataRange: dataRange, manager: manager, organization_id: selectedOrganizationId()},
            success:function (resp){
                $('.preloader').hide();
                console.log(resp);
                resp = JSON.parse(resp);
                if(!resp.success){
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
    });

    function markSelectAllCheckbox() {
        let numberOfChecked = $('.user_checkbox input:checkbox:checked').length;
        let totalCheckboxes = $('.user_checkbox input:checkbox:not(:disabled)').length;
        if  (numberOfChecked == 0) {
            $('.check_all_checkbox').removeClass('all_checkboxes');
            $('.check_all_checkbox').removeClass('some_checkboxes');
        } else if (totalCheckboxes != numberOfChecked) {
            $('.check_all_checkbox').removeClass('all_checkboxes');
            $('.check_all_checkbox').addClass('some_checkboxes');
        } else {
            $('.check_all_checkbox').removeClass('some_checkboxes');
            $('.check_all_checkbox').addClass('all_checkboxes');
        }
    }

    $(document).on('click','.user_checkbox input',function () {
        markSelectAllCheckbox();
    });

    $(document).on('click','.check_all_checkbox input',function () {
        if ($('.check_all_checkbox').hasClass('all_checkboxes')) {
            $('.user_checkbox input:checkbox:checked').each(function() {
                $( this ).prop("checked", false);
            });
        } else {
            $('.user_checkbox input:checkbox:not(:disabled)').each(function() {
                $( this ).prop("checked",true);
            });
        }
        markSelectAllCheckbox();
    });

    $(document).on('click','.js-send-megafon',function(){
        let value = $(".js-sms-form input[name=user_id]").val();
        let template = $('select[name="template_id"]').val();
        $.ajax({
            type: 'GET',
            data: {
                userId: value,
                limit: false,
                manager: config.managerId,
                template: template
            },
            url:'ajax/send_sms_megafon.php',
            success: (resp) => {
                $('.modal_send_sms').modal('hide');
                if (resp.success) {
                    Swal.fire({
                        timer: 18000,
                        title: 'Отправка СМС',
                        html: 'Сообщение отправлено',
                        type: 'success',
                    });
                } else if (resp.error == 'limit_sms') {
                    Swal.fire({
                        timer: 5000,
                        title: 'Ошибка отправки СМС',
                        text: 'Превышен лимит отправки сообщений',
                        type: 'error',
                    });
                } else {
                    Swal.fire({
                        timer: 5000,
                        title: 'Ошибка отправки СМС',
                        text: 'Сообщение не отправлено',
                        type: 'error',
                    });
                }
            }
        });
    });
    
    // Обработчик открытия модального окна перезвона - автоматическое заполнение ID кампании
    $(document).on('click','.recall-robo-modal',function () {
        const selectedMkk = $('#selected-mkk').val();
        
        // Mapping ID организаций к ID кампаний для перезвона
        const callbackCampaigns = {
            '13': '68293',  // РЗС
            '15': '68136'   // Лорд
        };
        
        // Автоматически заполняем ID кампании для перезвона
        if (selectedMkk && callbackCampaigns[selectedMkk]) {
            $('.robo-number').val(callbackCampaigns[selectedMkk]);
        } else {
            $('.robo-number').val('');
        }
    });
    
    $(document).on('click','.recall-robo',function () {
        let pdsId = $('.robo-number').val();
        let attemptsNumber = $('.attempt-numbers').val();
        let interval = $('.interval').val();
        const selectedMkk = $('#selected-mkk').val();
        const selectedMkkName = $('#selected-mkk-name').val() || 'МКК';
        
        // Получаем диапазон дат из поля фильтра
        let dateRange = $('.daterange').val() || '';
        
        if (!selectedMkk) {
            Swal.fire({
                timer: 5000,
                title: 'Ошибка',
                text: 'Не выбрана МКК',
                type: 'error',
            });
            return;
        }

        // Закрываем модалку ввода
        $('#recallRobo').modal('hide');
        
        $.ajax({
            type: 'POST',
            data: {
                pdsId: pdsId,
                attemptsNumber: attemptsNumber,
                interval: interval,
                organization_id: selectedMkk,
                date_range: dateRange
            },
            url:'ajax/send-missed-calls.php',
            success: (resp) => {
                let message = '';
                if(!resp.success){
                    message = 'Номера тел не отправились в Vox. ' + (resp.organization_name || selectedMkkName) + '. ' + (resp.error || 'Неизвестная ошибка');
                    $('#vox-result-message').html('<p class="text-danger">' + message + '</p>');
                } else {
                    const count = resp.count || 0;
                    message = 'Всё прошло удачно. Отправилось ' + count + ' номеров. ' + (resp.organization_name || selectedMkkName);
                    $('#vox-result-message').html('<p class="text-success">' + message + '</p>');
                }
                
                // Очищаем поля
                $('.robo-number').val('');
                $('.attempt-numbers').val('');
                $('.interval').val('');
                
                // Показываем модалку с результатом
                $('#voxResultModal').modal('show');
            },
            error: function() {
                let message = 'Номера тел не отправились в Vox. ' + selectedMkkName + '. Ошибка при отправке запроса';
                $('#vox-result-message').html('<p class="text-danger">' + message + '</p>');
                $('#voxResultModal').modal('show');
            }
        });
    });
})();

