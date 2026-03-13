{$meta_title="Заявка №`$order->order_id`" scope=parent}
<meta charset="UTF-8">
{assign 'stopfactorsImportant' [
"Негатив по ФССП",
"Высокая доля просрочек в КИ за последние 2 года",
"Глубокая просрочка в КИ за последние 2 года",
"Глубокая просрочка по последним займам в КИ",
"Негативы последних займов в КИ",
"Высокая просрочек в КИ за последние 2 года",
"Высокая вероятность дефолта по КИ",
"Высокий риск банкротства в течении 2х месяцев",
"Банкротство в КИ",
"Подозрение на фрод",
"Черный список скористы",
"Несовпадение ФИО с данными официальных источников",
"Реквизиты паспорта не уникальны",
"Большое количество разных телефонов в заявках на текущий паспорт",
"Регион проживания не совпадает с регионом телефона и регистрации",
"Регион повышенного риска (Белгородская обл)",
"Регион военных действий",
"Регион вблизи военных действий",
"Беженцы с территорий боевых действий",
"Сомнительная серия паспорта",
"Регион повышенного риска (Дальний Восток)",
"Высокая долговая нагрузка по КИ",
"Дополнительная оценка первого займа",
"Высокая доля просрочек в КИ за последние 2 года"
]}
{capture name='page_scripts'}
    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js?v=1.02"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/order.js?v=2.21"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/order_disable_robot_calls.app.js?v=1.0"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/check_images.js?v=1.07"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/document_edit.js?v=1.2"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/promocodes.js?v=1.2"></script>
    <script src="design/{$settings->theme|escape}/js/apps/terrorist-matches.js?v=1"></script>
    <script src="design/{$settings->theme|escape}/js/apps/movements.app.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js"></script>

    <script>
        window.app = window.app || {};
        window.app.order_config = {
            order_id: '{$order->order_id}',
            approve_amount_increased: '{$order_data['approve_amount_increased']}',
            manager_role: '{$manager->role}'
        };
    </script>
    <script src="design/{$settings->theme|escape}/js/order_render.js?v=1.09"></script>

    <script>
        let index_row = 1;

        $(document).ready(function () {
            $("input[name='phone']").inputmask("+7 (999) 999-99-99");

            $('.js-datepicker').datepicker({
                autoclose: true,
                format: "yyyy.mm.dd",
                default:'',
                language: "ru",
                locale: 'ru',
            });

            $('.js-datepicker-insurance').datepicker({
                autoclose: true,
                format: "yyyy-mm-dd",
                default:'',
                language: "ru",
                locale: 'ru',
            });

            index_row = $("#fssp_table [id^='row_id_']").length;

            if ($('[name="fssp_status"]').prop('checked'))
            {
                $("#fssp_table").show();
            }

            $('#modal_divide__order').on('shown.bs.modal', function () {
                $(".ion_slider__input").ionRangeSlider({
                    postfix: " ₽",
                    skin: "big",
                    grid: true,         // default false (enable grid)
                    grid_num: 4,        // default 4 (set number of grid cells)
                    grid_snap: false,    // default false (snap grid to step)
                    /*onChange: function (data) {
                        console.log(data);
                    }*/
                });
            })

            $('.scorista_body_ajax').on('shown.bs.collapse', function () {
                let id = $(this).data('id'),
                    table_name = $(this).data('table_name');

                if (!$(this).find('div').length) {
                    $(this).find('td').load('/ajax/scorista.php?action=get_body_order_view', {literal}{table_name, id}{/literal});
                }
            })

            $(document).ready(function () {
                $('.send-complaint').click(function () {
                    var userId = {$order->user_id};
                    let subject = $('#selectSubject').val();
                    let number = $('#complaint-order-number').val()
                    let comment = $('#complaint-comment').val()
                    $.ajax({
                        url: '/client/' + userId,
                        type: 'POST',
                        data: {
                            user_id: userId,
                            subject: subject,
                            number: number,
                            comment: comment,
                            action: 'leave_complaint'
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    timer: 5000,
                                    title: 'Успешно',
                                    text: translateMessage(response.message),
                                    type: 'success',
                                }).then(() => {
                                    $('#selectSubject').val('');
                                    $('#complaint-order-number').val(null).trigger('change');
                                    $('#complaint-comment').val('');
                                    $('#leaveComplaint').modal('hide');
                                });
                            } else {
                                Swal.fire({
                                    timer: 5000,
                                    title: 'Ошибка!',
                                    text: translateMessage(response.message),
                                    type: 'error',
                                });
                            }
                        },
                        error: function () {
                            alert('Ошибка при отправке запроса.');
                        }
                    });
                });

                function translateMessage(message) {
                    const translations = {
                        'Not all fields are filled in.': 'Не все поля заполнены.',
                        'The loan does not belong to the client.': 'Заявка не принадлежит клиенту.',
                        'Complaint successfully sent to 1C.': 'Жалоба успешно отправлена в 1С.',
                        'Error sending request.': 'Ошибка при отправке запроса.'
                    };

                    return translations[message] || message;
                }
            })

            // toggle clients button
            $('#contactsCollapse').on('shown.bs.collapse', function () {
                $('.toggle-contacts').text('Свернуть');
            });
            $('#contactsCollapse').on('hidden.bs.collapse', function () {
                $('.toggle-contacts').text('Показать всех');
            });

        });

        function divideOrder() {
            const data = $('#divide_form').serialize();

            $.ajax({
                url: '/order/',
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $("#modal_divide__order .modal-content").addClass('data-loading');
                },
                success: function(resp){
                    $("#modal_divide__order").modal('hide');

                    if (resp.result) {
                        Swal.fire({
                            timer: 5000,
                            title: 'Займ успешно разделен.',
                            text: 'После закрытия окна, страница будет автоматически перезагружена!',
                            type: 'success',
                        }).then(function() {
                            window.location.reload();
                        });

                        $('[data-target="#modal_divide__order"]').closest('div').remove();
                        $("#open_edit_amount_modal").prop('disabled', true);
                    } else {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка',
                            type: 'error',
                            text: 'Обновите страницу и попробуйте ещё раз.',
                        });
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                },
            }).done(function () {
                $("#modal_divide__order .modal-content").removeClass('data-loading');
            });
        }

        $('[name="fssp_status"]').on('change', function () {
            if ($('[name="fssp_status"]').prop('checked'))
            {
                $("#fssp_table").show();
            } else {
                $("#fssp_table").hide();
            }
        });

        function addRow() {
            let tr = $("#fssp_table #row_id_1").clone();

            index_row++;

            $(tr).attr('id', 'row_id_' + index_row)
                .find('td')
                .last()
                .html('<button type="button" onclick="removeRow(' + index_row + ')" class="btn btn-danger"><i class="mdi mdi-beaker"></i> </button>');

            $("#fssp_table").append(tr);

            $(tr).find('[name$="[reason_id]"]').attr('name', 'fssp_order[' + index_row + '][reason_id]');
            $(tr).find('[name$="[basis_id]"]').attr('name', 'fssp_order[' + index_row + '][basis_id]');
            $(tr).find('[name$="[date_end]"]').attr('name', 'fssp_order[' + index_row + '][date_end]');

            $(tr).find('.js-datepicker').datepicker({
                autoclose: true,
                format: "yyyy.mm.dd",
                default:'',
                language: "ru",
                locale: 'ru',
            });
        }

        function removeRow(index) {
            $("#fssp_table #row_id_" + index).remove();
        }

        function saveFSSP() {
            let data = $("#tab_scoring_fssp input, #tab_scoring_fssp select").serialize();

            $.ajax({
                url: "ajax/orders.php?action=save_fssp",
                data: data,
                dataType: 'json',
                method : 'POST',
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function(){
                    $('.preloader').hide();
                }
            });
        }

      $(".additional-services-switch input[type='checkbox']").on('change', function () {
        let value = $(this).is(':checked') == 1 ? 0 : 1;
        $(this).val(value);

        let name = $(this).attr('name');
        let order_id = $('#order-id').val();
        let manager_id = $('.js-event-add-load').data('manager');
        let user_id = $('.js-event-add-load').data('user');

        let data = {
          order_id: order_id,
          manager_id: manager_id,
          user_id: user_id
        }
        data[name]=value

        $.ajax({
          url: 'ajax/update_additional_service.php',
          data: data,
          method: 'POST',
          success: function (resp) {
          }
        })
      });
        
        if ($('#delete-kd').prop('checked')) {
            $('#delete-kd').prop('disabled', true);
        }

        $('#unblock-asp-button-modal').click(function () {
            let order_id = $('#page_wrapper').attr('data-order');
            let manager_id = $('#page_wrapper').attr('data-manager');


            $.ajax({
                url: '/ajax/orders.php?action=unblock_asp',
                type: 'post',
                data: {
                    order_id: order_id,
                    manager_id: manager_id,
                },
                success: function (resp) {
                    $("#unblock-asp-modal").modal('hide');
                    if (resp.success) {
                        $("#unblock-asp-btn").replaceWith("<span class='font-14 float-right text-success m-r-10'>АСП разблокировано</span>")
                        return;
                    }
                    Swal.fire({
                        timer: 8000,
                        title: 'Ошибка',
                        text: resp.error,
                        type: 'error',
                    });

                },
            })
        })

        function convertBase64toBlob(content, contentType) {
            contentType = contentType || '';
            var sliceSize = 512;
            var byteCharacters = window.atob(content);
            var byteArrays = [];

            for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                var slice = byteCharacters.slice(offset, offset + sliceSize);
                var byteNumbers = new Array(slice.length);

                for (var i = 0; i < slice.length; i++) {
                    byteNumbers[i] = slice.charCodeAt(i);
                }

                var byteArray = new Uint8Array(byteNumbers);
                byteArrays.push(byteArray);
            }

            var blob = new Blob(byteArrays, {
                type: contentType
            });
            return blob;
        }

        // Делегирование кликов, т.к. блоки с документами подгружаются через Ajax
        $(document).on('click', '.download-additional-reference', function (e) {
            e.preventDefault();
            let loanID = $(this).attr('data-loan-id');

            $.ajax({
                url: "/ajax/get_references.php?loanID="+loanID+'&type=additional',
                dataType: 'json',
                method : 'GET',
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (resp) {
                    $('.preloader').hide();
                    if (resp.success) {
                        blob = convertBase64toBlob(resp.return, 'application/pdf');
                        var blobURL = URL.createObjectURL(blob);
                        window.open(blobURL);
                        return;
                    }
                    Swal.fire({
                        timer: 8000,
                        title: 'Ошибка',
                        text: resp.error ?? 'Ошибка получения данных',
                        type: 'error',
                    });

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('.preloader').hide();
                    Swal.fire({
                        timer: 8000,
                        title: 'Ошибка',
                        text: textStatus,
                        type: 'error',
                    });

                },
            });
        });

        // Делегирование для ссылок справок
        $(document).on('click', '.download-reference', function (e) {
            e.preventDefault();
            let loanID = $(this).attr('data-loan-id');
            let referenceType = $(this).attr('data-reference-type');

            $.ajax({
                url: "/ajax/get_references.php?loanID="+loanID+"&referenceType="+referenceType,
                dataType: 'json',
                method : 'GET',
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (resp) {
                    $('.preloader').hide();
                    if (resp.success) {
                        blob = convertBase64toBlob(resp.return, 'application/pdf');
                        var blobURL = URL.createObjectURL(blob);
                        window.open(blobURL);
                        return;
                    }
                    Swal.fire({
                        timer: 8000,
                        title: 'Ошибка',
                        text: resp.error ?? 'Ошибка получения данных',
                        type: 'error',
                    });

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('.preloader').hide();
                    Swal.fire({
                        timer: 8000,
                        title: 'Ошибка',
                        text: textStatus,
                        type: 'error',
                    });

                },
            });
        });

        // Делегирование для цессий и агентских договоров
        $(document).on('click', '.download-cessii', function (e) {
            e.preventDefault();
            let loanID = $(this).attr('data-loan-id');

            $.ajax({
                url: "/ajax/get_notice_of_assigment.php?loanID="+loanID,
                dataType: 'json',
                method : 'GET',
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (resp) {
                    $('.preloader').hide();
                    if (resp.success) {
                        blob = convertBase64toBlob(resp.return['File'], 'application/pdf');
                        var blobURL = URL.createObjectURL(blob);
                        window.open(blobURL);
                        return;
                    }
                    Swal.fire({
                        timer: 8000,
                        title: 'Ошибка',
                        text: resp.error ?? 'Ошибка получения данных',
                        type: 'error',
                    });

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('.preloader').hide();
                    Swal.fire({
                        timer: 8000,
                        title: 'Ошибка',
                        text: textStatus,
                        type: 'error',
                    });

                },
            });
        });

        $(".delete-kd-switch input[type='checkbox']").click(function () {
            if ($('#delete-kd').val() === "0") {
                $('#delete-kd').val(1);
                $('#delete-kd').prop('disabled', true);
                let user_id = $(".delete-kd-switch>#user-id").data('user')
                let order_id = $(".delete-kd-switch>#order-id").val()
                let manager_id = $('.js-event-add-load').data('manager')
                $.ajax({
                    url: "ajax/delete-kd.php",
                    data: {
                        user_id: user_id,
                        order_id: order_id,
                        manager_id: manager_id,
                    },
                    method: 'POST',
                    success: function (resp) {
                        if (resp == "success") {
                            Swal.fire({
                                timer: 5000,
                                title: 'ШКД успешно обновились',
                                type: 'success',
                            });
                            return;
                        }
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка',
                            text: resp,
                            type: 'error',
                        });
                    }

                });
            }
        });

        $(".toggle-prolongation-switch input[type='checkbox']").on('change', function() {
            let checkbox = $(this);
            let originalState = checkbox.is(':checked');
            let value = originalState ? 1 : 0;
            let managerID = $('#managerID').val();
            let orderID = $("#orderID").val();

            $.ajax({
                url: "/ajax/change_prolongation.php?value="+value+"&managerID="+managerID+"&orderID="+orderID,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            title: 'Успешно',
                            type: 'success',
                            timer: 5000
                        });
                    } else {
                        checkbox.prop('checked', !originalState);
                        Swal.fire({
                            title: 'Ошибка',
                            text: response.message,
                            type: 'error',
                            timer: 5000
                        });
                    }
                },
                error: function(xhr) {
                    checkbox.prop('checked', !originalState);
                    Swal.fire({
                        title: 'Ошибка',
                        text: response.message,
                        type: 'error',
                        timer: 5000
                    });
                }
            });
        });

        $(".toggle-kd-switch input[type='checkbox']").on('change', function() {
            let order_id = $('#toggle-kd').attr('data-order-id');
            let manager_id = $('#toggle-kd').attr('data-manager-id');
            let user_id = $('#toggle-kd').attr('data-user-id');

            $.ajax({
                url: "/app/orders/"+order_id+"/credit-doctor/toggle",
                method: 'POST',
                data: {
                    user_id: user_id,
                    order_id: order_id,
                    manager_id: manager_id,
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Успешно',
                        text: response.message,
                        type: 'success',
                        timer: 5000
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Ошибка',
                        text: xhr.responseJSON.message,
                        type: 'error', 
                        timer: 5000
                    });
                }
            });
        });

        $('.sms-phone').on('input', function(){
            let phone = {$order->phone_mobile}
            if (phone !== $(this).val()) {
                $('.send-sms').prop('disabled', true);
                if ($('.send-sms-modal-div .modal-radio-button').length === 0) {
                    $('.send-sms-modal-div').append(`
                <div class='modal-radio-button mt-2'>
                    <label for="">Номер клиента?</label>
                    <span>Да</span><input type="radio" name="client-phone" class="mr-2 client-phone" value='true'>
                    <span>Нет</span><input type="radio" name="client-phone" class="client-phone" value='false'>
                </div>
            `);
                }
            } else {
                $('.modal-radio-button').remove();
                $('.send-sms').prop('disabled', false);
            }
        })
        $(document).on('change', '.client-phone', function () {
            let value = $(this).val();
            if (value === 'true') {
                $('.send-sms').prop('disabled', false);
                $('.modal-whose-input').remove();
            } else {
                $('.send-sms').prop('disabled', true);
                if ($('.send-sms-modal-div .modal-input').length === 0) {
                    $('.send-sms-modal-div').append(`
                <div class='modal-whose-input mt-2'>
                    <input type="text" name="client-phone-new" class="mr-2 modal-whose-number form form-control" placeholder="Чей номер?" maxlength="64">
                </div>
            `);
                }
            }
        });

        $(document).on('input', '.modal-whose-number', function() {
            if ($(this).val().length !== 0) {
                $('.send-sms').prop('disabled', false);
            }else{
                $('.send-sms').prop('disabled', true);
            }
        })

        $(document).on('click', '.btn-modal-send-sms', function() {
            $('.send-sms').attr('data-type',$(this).data('type'))
            $('.send-sms').attr('data-policy',$(this).data('policy'))
            $('.send-sms').attr('data-order',$(this).data('order'))
            $('.send-sms').attr('data-manager',$(this).data('manager'))
        })

        document.addEventListener('DOMContentLoaded', function () {
            const links = document.querySelectorAll('.doc-link');

            links.forEach(link => {
                const original = link.getAttribute('data-original-name');
                if (!original) return;

                // Добавляет пробел между строчными буквами (или цифрами) и заглавными в названии Документа
                let spaced = original.replace(/([а-яё0-9])([А-ЯЁ])/gu, '$1 $2');

                // Добавляет пробел между парой заглавных букв, если за ними идёт строчная в названии Документа
                spaced = spaced.replace(/([А-ЯЁ])([А-ЯЁ])(?=[а-яё])/gu, '$1 $2');

                link.textContent = spaced.trim();
            });
        });
    </script>

    {if !$skip_credit_rating && !$accept_reject_orders}
        <script>
            $(document).on('click', '.js-order-status-block button, .verificator_panel button', function (e) {
                e.preventDefault();
                return false;
            });
        </script>
    {/if}
    <script>
        $('.remove-card').on('click', function (event) {
            event.preventDefault();
            let card_id = $(this).attr('data-button-card-id');
            let user_id = $(this).attr('data-user-id');
            let manager_id = $(this).attr('data-manager');
            $.ajax({
                url: 'ajax/remove_card.php',
                data: {
                    card_id: card_id,
                    user_id: user_id,
                    manager_id: manager_id
                },
                method: 'POST',
                success:  (resp) => {
                    if (resp.result && resp.result == 'success') {
                        $('[data-card-data="' + card_id + '"]').remove()
                    } else {
                        if (resp.error == 'card_blocked') {
                            alert("Удаление карты заблокировано. В настоящее время она используется для совершения для операций");
                        }

                        if (resp.error == 'first_card_blocked') {
                            alert("Удаление единственной карты невозможно");
                        }

                        if (resp.error == 'card_busy') {
                            alert("Карта используется в займе");
                        }

                        if (resp.error == 'card_not_found') {
                            alert("Карта не найдена в бд");
                        }
                    }
                }
            });
        });

        // удаление сбп акка из рекуррентов
        $('.remove-sbp-account').on('click', function (event) {
            event.preventDefault();
            let sbpToken = $(this).attr('data-sbp-token');
            let user_id = $(this).attr('data-user-id');
            let manager_id = $(this).attr('data-manager');

            $.ajax({
                url: 'ajax/remove_sbp_account.php',
                data: {
                    sbp_account_token: sbpToken,
                    user_id: user_id,
                    manager_id: manager_id
                },
                method: 'POST',
                success: (resp) => {
                    if (resp.result && resp.result === 'success') {
                        $('[data-sbp-account-data="' + sbpToken + '"]').remove()
                    } else {
                        if (resp.error == 'sbp_blocked') {
                            alert("Удаление СБП счёта заблокировано. В настоящее время он используется для совершения операций");
                        }

                        if (resp.error == 'auth_error') {
                            alert("Ошибка авторизации");
                        }

                        if (resp.error == 'sbp_token_not_found') {
                            alert("Ошибка при определении данных сбп счёта");
                        }

                        if (resp.error == 'sbp_user_id_not_found') {
                            alert("Владелец сбп счёта не определён");
                        }

                        if (resp.error == 'sbp_user_not_found') {
                            alert("Владелец сбп счёта не найден");
                        }

                        if (resp.error == 'sbp_not_found') {
                            alert("СБП счёт не найден в бд");
                        }
                    }
                }
            });
        });
    </script>

{/capture}


{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css?v=1.02" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css"/>
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />

    <style>
        body.compensate-for-scrollbar {
            overflow: auto;
            overflow-x: hidden;
        }
        .jsgrid-table { margin-bottom:0}
        .form-control-static { margin-bottom:0}
        label { margin-bottom:0}

        [data-target="#modal_divide__order"] {
            min-width: 380px;
        }
        .btn-danger:disabled {
            cursor: not-allowed;
            pointer-events: none;
        }
        .onoffswitch,.onoffswitchkd {
            display:inline-block!important;
            vertical-align:top!important;
            width:60px!important;
            text-align:left;
        }
        .onoffswitch-switch,.onoffswitch-switchkd {
            right:38px!important;
            border-width:1px!important;
        }
        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch,
        .onoffswitchkd-checkbox:checked + .onoffswitchks-label .onoffswitchks-switch {
            right:0px!important;
        }
        .onoffswitch-label, .onoffswitchkd-label{
            margin-bottom:0!important;
            border-width:1px!important;
        }
        .onoffswitch-inner::after,
        .onoffswitch-inner::before,
        .onoffswitchkd-inner::after,
        .onoffswitchkd-inner::before
        {
            height:18px!important;
            line-height:18px!important;
        }
        .onoffswitch-switch,.onoffswitchkd-switch {
            width:20px!important;
            margin:1px!important;
        }
        .onoffswitch-inner::before,.onoffswitchkd-inner::before {
            content:'ВКЛ'!important;
            padding-left: 10px!important;
            font-size:10px!important;
        }
        .onoffswitch-inner::after,.onoffswitchkd-inner::after {
            content:'ВЫКЛ'!important;
            padding-right: 6px!important;
            font-size:10px!important;
        }
        .order-details .row {
            width: 100%
        }

        .jsgrid-grid-header, .jsgrid-grid-body {
            overflow-y: hidden;
        }
        ::placeholder {
            color: grey;
            opacity: 1;
        }
    </style>
{/capture}

{function name='display_comments'}
    {if isset($comments[$block]) && $manager->role != 'verificator_minus'}
        {foreach $comments[$block] as $comment}
            <div class="col-md-12 mb-2">
                <div class="bg-primary pt-1 pb-1 pl-4 pr-4 rounded" style="display:inline-block">
                    <div>
                        <strong>{$managers[$comment->manager_id]->name|escape}</strong>
                        <small><i>{$comment->created|date} {$comment->created|time}</i></small>
                    </div>
                    <div>{$comment->text|nl2br}</div>
                </div>
            </div>
        {/foreach}
    {/if}

{/function}

{function name='render_credit_card_list'}
    {if $card_list && $card_list|@count > 0}
    <select id="{$id_select}" class="form-control">
        <option disabled>--- Активные карты ---</option>
        {foreach $card_list as $card}
            {if $card->deleted}{continue}{/if}
            <option value="{$card->id}" {if $card->id == $order->card_id}selected{/if}>
                {$card->pan} {$card->expdate} ({$organizations[$card->organization_id]->short_name|escape})
            </option>
        {/foreach}
        <option disabled>--- Удаленные карты ---</option>
        {* Deleted cards *}
        {foreach $card_list as $card}
            {if ! $card->deleted}{continue}{/if}
            <option value="{$card->id}" {if $card->deleted}disabled{/if}>
                {$card->pan} {$card->expdate} ({$organizations[$card->organization_id]->short_name|escape})
            </option>
        {/foreach}
    </select>
    {/if}
{/function}

<div class="page-wrapper js-event-add-load" id="page_wrapper" data-phone_mobile="{$order->phone_mobile}"  data-event="1" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <input type="hidden" name="manager_role" value="{$manager->role}" />

        <div class="row page-titles">
            <div class="col-md-6 col-12 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-animation"></i> Заявка №{$order->order_id}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="orders">Заявки</a></li>
                    <li class="breadcrumb-item active">Заявка №{$order->order_id}</li>
                </ol>
            </div>
            <div class="col-md-3 col-6 align-self-center">
                {if $looker_link && $manager->role != 'verificator_minus'}
                    <a href="{$looker_link}" class="btn btn-info" target="_blank">
                        <i class="fas fa-user"></i><span>Смотреть ЛК</span>
                    </a>
                {/if}
            </div>
            <div class="col-md-3 col-6 align-self-center">
                {if $manager->role != 'verificator_minus'}
                    <h3 class="text-white">
                        <i>Клиент с {$order->site_id}</i><br>
                        {$organizations[$order->organization_id]->short_name|escape}
                    </h3>
                    {if $is_order_from_akvarius}
                        <h5 class="text-warning">(Ранее Аквариус)</h5>
                    {/if}
                {/if}
            </div>
        </div>

        <div id="alert_wrapper"></div>

        <ul class="mt-2 nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active js-event-add-click" data-event="5" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#tab_order" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                    <span class="hidden-xs-down">Заявка</span>
                </a>
            </li>
            {if $manager->role != 'verificator_minus'}
            <li class="nav-item">
                <a class="nav-link js-event-add-click" data-event="6"
                   data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}"
                   data-toggle="tab" href="#tab_scorings" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                    <span class="hidden-xs-down">Скоринги</span>
                </a>
            </li>
            {/if}
            <li class="nav-item">
                <a class="nav-link js-event-add-click" data-event="7" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#tab_history" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                    <span class="hidden-xs-down">Кредитная история</span>
                </a>
            </li>
            {if $manager->role != 'verificator_minus'}
            <li class="nav-item">
                <a class="nav-link js-event-add-click" data-event="9" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#tab_documents" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                    <span class="hidden-xs-down">Документы</span>
                </a>
            </li>
            {/if}
            {if $manager->role == 'verificator_minus'}
            <li class="nav-item">
                <a class="nav-link js-event-add-click" data-event="9" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#tab_documents_minus" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                    <span class="hidden-xs-down">Документы</span>
                </a>
            </li>
            {/if}
            {if $manager->role != 'verificator_minus'}
                <li class="nav-item">
                    <a class="nav-link js-event-add-click" data-event="8" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#tab_comments" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                        <span class="hidden-xs-down">Комментарии</span>
                    </a>
                </li>
            {/if}
            {if in_array('eventlogs', $manager->permissions)}
                <li class="nav-item">
                    <a class="nav-link js-event-add-click" data-event="35" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#logs" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                        <span class="hidden-xs-down">Логирование</span>
                    </a>
                </li>
            {/if}
            {if in_array('insures', $manager->permissions)}
                <li class="nav-item">
                    <a class="nav-link js-event-add-click" data-event="36" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#insures" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                        <span class="hidden-xs-down">Доп. услуги</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link js-event-add-click" data-event="38" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#overpayments" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class="ti-money"></i></span>
                        <span class="hidden-xs-down">Переплаты</span>
                    </a>
                </li>
            {/if}
            <li class="nav-item">
                <a class="nav-link js-event-add-click" data-event="37" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#duplicates" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                    <span class="hidden-xs-down">Совпадения</span>
                </a>
            </li>
        </ul>
        <div class="tab-content ">

            {include file='order/tab_order.tpl'
                manager=$manager
                order=$order
                user_data=$user_data
                region_ip_mismatch=$region_ip_mismatch
                is_order_from_akvarius=$is_order_from_akvarius
                is_short_flow=$is_short_flow
                is_short_flow_data_confirm=$is_short_flow_data_confirm
                has_autoconfirm_sms=$has_autoconfirm_sms
                is_autoconfirm=$is_autoconfirm
                order_data=$order_data
                is_samara_office=$is_samara_office
                inn_not_found=$inn_not_found
                sbp_accounts=$sbp_accounts
                has_hyper_c_scoring=$has_hyper_c_scoring
                skip_credit_rating=$skip_credit_rating
                accept_reject_orders=$accept_reject_orders
                education_name=$education_name
                blockCalls=$blockCalls
                has_approved_orders=$has_approved_orders
                files=$files
                front_url=$front_url
                is_post=$is_post
                config=$config
                images_error=$images_error
                socials_error=$socials_error
                contactpersons=$contactpersons
                contacts_error=$contacts_error
                scorista_step_files=$scorista_step_files
                scorista_step_additional_data=$scorista_step_additional_data
                user=$user
                managers=$managers
                axi_amount=$axi_amount
                card_list=$card_list
                autodebit_cards=$autodebit_cards
                tv_medical_price=$tv_medical_price
                credit_doctor_price=$credit_doctor_price
                star_oracle_price=$star_oracle_price
                comments=$comments
                commentsData=$commentsData
                comment_blocks=$comment_blocks
                passport_error=$passport_error
                eventlogs=$eventlogs
                order_divide=$order_divide
                changelog_types=$changelog_types
                order_statuses=$order_statuses
                search=$search
                changelogs=$changelogs
                maratoriums=$maratoriums
                reject_reasons=$reject_reasons
                waiting_reasons=$waiting_reasons
                organizations=$organizations
            }

            {if $manager->role != 'verificator_minus'}
                {include file='order/scorings.tpl'
                    order=$order
                    user_scorings=$user_scorings
                    need_update_scorings=$need_update_scorings
                    fssp_items=$fssp_items
                    fssp_reasons=$fssp_reasons
                    fssp_basis=$fssp_basis
                }
            {/if}

            <div id="tab_history" class="tab-pane" role="tabpanel">
                <div id="tab_history_container">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Загрузка истории...</span>
                        </div>
                        <p>Загрузка истории...</p>
                    </div>
                </div>
            </div>

            <div id="tab_documents" class="tab-pane" role="tabpanel">
                <div id="tab_documents_container">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Загрузка документов...</span>
                        </div>
                        <p>Загрузка документов...</p>
                    </div>
                </div>
            </div>

            <div id="tab_documents_minus" class="tab-pane" role="tabpanel">
                <div id="tab_documents_minus_container">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Загрузка документов...</span>
                        </div>
                        <p>Загрузка документов...</p>
                    </div>
                </div>
            </div>

            {if $manager->role != 'verificator_minus'}
                <div id="tab_comments" class="tab-pane" role="tabpanel">
                    <div id="tab_comments_container">
                        {* Загружается аяксом *}
                        <div class="text-center p-5">
                            <div class="preloader_ajax">
                                <div class="loader"></div>
                                <p>Загрузка данных...</p>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}

            <div id="logs" class="tab-pane" role="tabpanel">
                <div id="tab_logs_container">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Загрузка логов...</span>
                        </div>
                        <p>Загрузка логов...</p>
                    </div>
                </div>
            </div>

            {if in_array('insures', $manager->permissions) || in_array($manager->id, [114, 118])}
                <div id="insures" class="tab-pane" role="tabpanel">
                    <div id="tab_insures_container">
                        <div class="p-5 text-center">
                            <div class="spinner-border text-info" role="status">
                                <span class="sr-only">Загрузка...</span>
                            </div>
                            <p>Загрузка дополнительных услуг...</p>
                        </div>
                    </div>
                </div>
            {/if}

            {if in_array('insures', $manager->permissions)}
                <div id="overpayments" class="tab-pane" role="tabpanel">
                    <div id="tab_overpayments_container">
                        <div class="p-5 text-center">
                            <div class="spinner-border text-info" role="status">
                                <span class="sr-only">Загрузка...</span>
                            </div>
                            <p>Загрузка переплат...</p>
                        </div>
                    </div>
                </div>
            {/if}

            {include file='order/duplicates.tpl'
                userDuplicates=$userDuplicates
            }
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->

    {if in_array($manager->role, ['developer', 'admin', 'chief_verificator', 'opr', 'ts_operator']) || (in_array($manager->role, ['verificator', 'edit_verificator']) && $manager->id==$order->manager_id)}
        {if empty($order->reject_date) && empty($order->confirm_date)}
            {if $order->stage1}
                <div class="verificator_panel">
                    <div class="row">
                        <div class="col-md-2 text-left">
                            {if $order->stage1}
                                <div class=" display-6 pl-5 pt-2 text-warning">
                                    <i class=" wi wi-time-3 "></i>
                                    {if $order->stage1}<span class="js-timer" data-start="{$order->stage1_time}"></span>{/if}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            {/if}
        {/if}
    {/if}

    {include file='footer.tpl'}

</div>

<div id="modal_add_maratorium" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить мораторий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_maratorium" action="order/{$order->order_id}">

                    <div class="alert" style="display:none"></div>

                    <input type="hidden" name="action" value="add_maratorium" />
                    <input type="hidden" name="user_id" value="{$order->user_id}" />

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Выберите мораторий:</label>
                        <select class="form-control" name="maratorium_id">
                            {foreach $maratoriums as $maratorium}
                                {$maratorium_period = $maratorium->period/86400}
                                <option value="{$maratorium->id}">{$maratorium->name} ({$maratorium_period} {$maratorium_period|plural:'день':'дня':'дней'})</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_add_comment" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_comment" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                    <input type="hidden" name="block" value="" />
                    <input type="hidden" name="action" value="add_comment" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Комментарий:</label>
                        <textarea class="form-control" name="text"></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loan_operations" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loan_operations_title">Операции по договору</h5>
                <button type="button" class="btn-close btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-white"></i>
                </button>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<div id="modal_reject_reason" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Отказать в выдаче кредита?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="reject_mko" aria-labelledby="home-tab">
                                <form class="js-reject-form">
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="action" value="reject" />
                                    <input type="hidden" name="status" value="3" />
                                    <div class="form-group">
                                        <label for="admin_name" class="control-label">Выберите причину отказа:</label>
                                        <select name="reason_id" class="form-control">
                                            {foreach $reject_reasons as $reject_reason}
                                                <option value="{$reject_reason->id}">{if $manager->role=='verificator_minus'}{$reject_reason->client_name|escape}{else}{$reject_reason->admin_name|escape}{/if}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect" data-dismiss="modal">Отменить</button>
                                        <button type="submit" class="btn btn-success btn-lg float-right waves-effect waves-light">Да, отказать</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_waiting_reason" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Перевести заявку в ожидание?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                <form class="js-waiting-form">
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="action" value="waiting" />
                                    <input type="hidden" name="status" value="3" />
                                    <div class="form-group">
                                        <label for="admin_name" class="control-label">Выберите причину ожидания:</label>
                                        <select name="reason_id" class="form-control">
                                            {foreach $waiting_reasons as $waiting_reason}
                                                <option value="{$waiting_reason->id}">{$waiting_reason->admin_name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect" data-dismiss="modal">Отменить</button>
                                        <button type="submit" class="btn btn-success btn-lg float-right waves-effect waves-light">Да, перевести</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_send_sms" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Отправить смс-сообщение? <span class="text-themecolor">{$order->site_id}</span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                <form class="js-sms-form">
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                                    <input type="hidden" name="action" value="send_sms" />
                                    <div class="form-group">
                                        <label for="name" class="control-label">Выберите шаблон сообщения:</label>
                                        <select name="template_id" class="form-control">
                                            {foreach $sms_templates as $sms_template}
                                                <option value="{$sms_template->id}" title="{$sms_template->template|escape}">{$sms_template->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect" data-dismiss="modal">Отменить</button>
                                        <button type="submit" class="btn btn-success btn-lg float-right waves-effect waves-light">Да, отправить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_return_insurance" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Вернуть страховку?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                <form class="js-return-insurance-form">
                                    <input type="hidden" name="insurance_id" value="" />
                                    <input type="hidden" name="action" value="return_insurance" />
                                    <p>Номер полиса: <span class="js-insurance-number"></span></p>
                                    <p>Сумма, руб: <span class="js-insurance-amount"></span></p>
                                    <div class="form-group">
                                        <label for="name" class="control-label">Выберите карту для возврата:</label>
                                        <select name="card_id" class="form-control">
                                            {foreach $card_list as $card}
                                                <option
                                                        value="{$card->CardId}"
                                                        {if !empty($card->Status) && $card->Status != 'A'}disabled="true"{/if}
                                                        {if $card->CardId == $order->card_id}selected="true"{/if}>
                                                    {$card->Pan} {$card->ExpDate}
                                                </option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="name" class="control-label">Дата заявления:</label>
                                        <input class="form-control js-datepicker-insurance" name="application_date" value="" />
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect" data-dismiss="modal">Отменить</button>
                                        <button type="submit" class="btn btn-success btn-lg float-right waves-effect waves-light">Да, вернуть</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_return_dop" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Вернуть доп. услугу?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="js-return-dop-loader text-center py-4" style="display: none;">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-2">Выполняется возврат...</p>
                </div>
                <div class="card js-return-dop-content">
                    <div class="card-body">
                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                <form class="js-return-credit-doctor-form">
                                    <input type="hidden" name="service_id" value="" />
                                    <input type="hidden" name="action" value="refund_extra_service" />
                                    <input type="hidden" name="card_id" value="" />
                                    <input type="hidden" name="service_date" value="" />
                                    <input type="hidden" name="order_id" value="" />
                                    <input type="hidden" name="service" value="" />

                                    <div class="form-group">
                                        <label for="name" class="control-label">Сумма:</label>
                                        <select name="return_size" class="form-control js-credit-doctor-size">
                                            <option value="all"></option>
                                            <option value="seventy_five"></option>
                                            <option value="half"></option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="name" class="control-label">Тип возврата:</label>
                                        <select name="return_type" class="form-control js-credit-doctor-type">
                                            <option value="recompense">Взаимозачет</option>
                                            <option value="card">На карту</option>
                                            <option value="sbp">На СБП</option>
                                        </select>
                                    </div>

                                    <div class="form-group" id="sbp-account-selection" style="display: none;">
                                        <label for="name" class="control-label">Выберите СБП счет для возврата:</label>
                                        <select name="sbp_account_id" class="form-control">
                                            {if isset($sbp_accounts)}
                                                {foreach $sbp_accounts as $sbp_account}
                                                    <option value="{$sbp_account->id}">
                                                        {$sbp_account->title} - {$sbp_account->qrcId}
                                                    </option>
                                                {/foreach}
                                            {/if}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect" data-dismiss="modal">Отменить</button>
                                        <button type="submit" class="btn btn-success btn-lg float-right waves-effect waves-light">Да, вернуть</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="modal_need_comment_card" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Укажите причину смены карты</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_need_comment_card" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                    <input type="hidden" name="card_id" value="" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="comment" class="control-label">Комментарий:</label>
                        <textarea class="form-control" id="comment" name="comment" required=""></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_need_comment_manager" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Укажите причину смены менеджера</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_need_comment_manager" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                    <input type="hidden" name="manager_id" value="" />
                    <input type="hidden" name="action" value="change_manager" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="comment" class="control-label">Комментарий:</label>
                        <textarea class="form-control" id="comment" name="comment" required=""></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_call_variants" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Выберите варианты дозвона</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_call_variants" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                    <input type="hidden" name="manager_id" value="" />
                    <input type="hidden" name="action" value="set_call_variants" />

                    <div class="alert" style="display:none"></div>

                    <div class="row">
                        <div class="col-md-6">

                            <div class="form-group">
                                <h4>Mango</h4>
                                <div class="custom-control custom-radio text-success">
                                    <input type="radio" id="mango_success" name="call_variants[mango]" value="1" class="custom-control-input" {if isset($order->call_variants['mango']) && $order->call_variants['mango']==1}checked{/if}>
                                    <label class="custom-control-label" for="mango_success">Успех</label>
                                </div>
                                <div class="custom-control custom-radio text-danger">
                                    <input type="radio" id="mango_alert" name="call_variants[mango]" value="2" class="custom-control-input" {if isset($order->call_variants['mango']) && $order->call_variants['mango']==2}checked{/if}>
                                    <label class="custom-control-label" for="mango_alert">Неуспешно</label>
                                </div>
                                <div class="custom-control custom-radio  text-warning">
                                    <input type="radio" id="mango_none" name="call_variants[mango]" value="0" class="custom-control-input" {if !isset($order->call_variants['mango'])}checked{/if}>
                                    <label class="custom-control-label" for="mango_none">Не задействовано</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <h4>Sipnet</h4>
                                <div class="custom-control custom-radio text-success">
                                    <input type="radio" id="sipnet_success" name="call_variants[sipnet]" value="1" class="custom-control-input" {if isset($order->call_variants['sipnet']) && $order->call_variants['sipnet']==1}checked{/if}>
                                    <label class="custom-control-label" for="sipnet_success">Успех</label>
                                </div>
                                <div class="custom-control custom-radio text-danger">
                                    <input type="radio" id="sipnet_alert" name="call_variants[sipnet]" value="2" class="custom-control-input" {if isset($order->call_variants['sipnet']) && $order->call_variants['sipnet']==2}checked{/if}>
                                    <label class="custom-control-label" for="sipnet_alert">Неуспешно</label>
                                </div>
                                <div class="custom-control custom-radio  text-warning">
                                    <input type="radio" id="sipnet_none" name="call_variants[sipnet]" value="0" class="custom-control-input" {if !isset($order->call_variants['sipnet'])}checked{/if}>
                                    <label class="custom-control-label" for="sipnet_none">Не задействовано</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <h4>SMS</h4>
                                <div class="custom-control custom-radio text-success">
                                    <input type="radio" id="sms_success" name="call_variants[sms]" value="1" class="custom-control-input" {if isset($order->call_variants['sms']) && $order->call_variants['sms']==1}checked{/if}>
                                    <label class="custom-control-label" for="sms_success">Успех</label>
                                </div>
                                <div class="custom-control custom-radio text-danger">
                                    <input type="radio" id="sms_alert" name="call_variants[sms]" value="2" class="custom-control-input" {if isset($order->call_variants['sms']) && $order->call_variants['sms']==2}checked{/if}>
                                    <label class="custom-control-label" for="sms_alert">Неуспешно</label>
                                </div>
                                <div class="custom-control custom-radio  text-warning">
                                    <input type="radio" id="sms_none" name="call_variants[sms]" value="0" class="custom-control-input" {if !isset($order->call_variants['sms'])}checked{/if}>
                                    <label class="custom-control-label" for="sms_none">Не задействовано</label>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <h4>WA</h4>
                                <div class="custom-control custom-radio text-success">
                                    <input type="radio" id="wa_success" name="call_variants[wa]" value="1" class="custom-control-input" {if isset($order->call_variants['wa']) && $order->call_variants['wa']==1}checked{/if}>
                                    <label class="custom-control-label" for="wa_success">Успех</label>
                                </div>
                                <div class="custom-control custom-radio text-danger">
                                    <input type="radio" id="wa_alert" name="call_variants[wa]" value="2" class="custom-control-input" {if isset($order->call_variants['wa']) && $order->call_variants['wa']==2}checked{/if}>
                                    <label class="custom-control-label" for="wa_alert">Неуспешно</label>
                                </div>
                                <div class="custom-control custom-radio  text-warning">
                                    <input type="radio" id="wa_none" name="call_variants[wa]" value="0" class="custom-control-input" {if !isset($order->call_variants['wa'])}checked{/if}>
                                    <label class="custom-control-label" for="wa_none">Не задействовано</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <h4>Telegram</h4>
                                <div class="custom-control custom-radio text-success">
                                    <input type="radio" id="telegram_success" name="call_variants[telegram]" value="1" class="custom-control-input" {if isset($order->call_variants['telegram']) && $order->call_variants['telegram']==1}checked{/if}>
                                    <label class="custom-control-label" for="telegram_success">Успех</label>
                                </div>
                                <div class="custom-control custom-radio text-danger">
                                    <input type="radio" id="telegram_alert" name="call_variants[telegram]" value="2" class="custom-control-input" {if isset($order->call_variants['telegram']) && $order->call_variants['telegram']==2}checked{/if}>
                                    <label class="custom-control-label" for="telegram_alert">Неуспешно</label>
                                </div>
                                <div class="custom-control custom-radio  text-warning">
                                    <input type="radio" id="telegram_none" name="call_variants[telegram]" value="0" class="custom-control-input" {if !isset($order->call_variants['telegram'])}checked{/if}>
                                    <label class="custom-control-label" for="telegram_none">Не задействовано</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <h4>Viber</h4>
                                <div class="custom-control custom-radio text-success">
                                    <input type="radio" id="viber_success" name="call_variants[viber]" value="1" class="custom-control-input" {if isset($order->call_variants['viber']) && $order->call_variants['viber']==1}checked{/if}>
                                    <label class="custom-control-label" for="viber_success">Успех</label>
                                </div>
                                <div class="custom-control custom-radio text-danger">
                                    <input type="radio" id="viber_alert" name="call_variants[viber]" value="2" class="custom-control-input" {if isset($order->call_variants['viber']) && $order->call_variants['viber']==2}checked{/if}>
                                    <label class="custom-control-label" for="viber_alert">Неуспешно</label>
                                </div>
                                <div class="custom-control custom-radio  text-warning">
                                    <input type="radio" id="viber_none" name="call_variants[viber]" value="0" class="custom-control-input" {if !isset($order->call_variants['viber'])}checked{/if}>
                                    <label class="custom-control-label" for="viber_none">Не задействовано</label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{if in_array($manager->role, ['developer', 'chief_verificator']) && !$has_approved_orders}
    <div id="modal_agreement_saved" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Готово</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="card">
                        <div class="card-body">

                            <div class="tab-content tabcontent-border p-3" id="myTabContent">
                                <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                    <div>Клиенту отправлено доп. соглашение в ЛК</div>
                                    <div class="form-action clearfix">
                                        <div class="row">
                                            <div class="col-12 text-right">
                                                <button type="button" class="btn btn-success float-right waves-effect" data-dismiss="modal">Ок</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}


{include file='html_blocks/modals/order_modals.tpl'}
{include file='html_blocks/return_by_requisites_modal.tpl'}
{include file='html_blocks/issue_promocode_modal.tpl' clientId=$order->user_id haveCloseCredits=$order->have_close_credits}
{include file='html_blocks/payment_deferment_modal.tpl'}
