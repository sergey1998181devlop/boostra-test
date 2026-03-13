;function OrderApp()
{
    var app = this;

    app.$new_contactperson;

    app.image_deg = 0;
    app.timers = [];

    var _init_toggle_form = function(){

        var _getForm = function($element) {
            var formId = $element.data('form');
            if (formId) {
                return $('#' + formId);
            }
            return $element.closest('form');
        };

        // редактирование формы
        $('.js-edit-form').each(function(){
            var $this = $(this);
            if (!$this.hasClass('evented'))
            {
                $this.addClass('evented');
                $this.click(function(e){
                    e.preventDefault();

                    var $form = _getForm($(this));

                    if ($(this).hasClass('open'))
                    {
                        $(this).removeClass('open');
                        $form.find('.edit-block').addClass('hide');
                        $form.find('.view-block').removeClass('hide');
                    }
                    else
                    {
                        $(this).addClass('open');
                        $form.find('.view-block').addClass('hide');
                        $form.find('.edit-block').removeClass('hide');
                    }
                });
            }
        })

        // отмена редактирования
        $('.js-cancel-edit').click(function(e){
            e.preventDefault();

            var $form = $(this).closest('form');
            $form.find('.edit-block').addClass('hide');
            $form.find('.view-block').removeClass('hide');
        });
    };

    var _init_resend_approve = function(){
        $(document).on('click', '.js-resend-approve', function(){

            var $this = $(this);

            if ($this.hasClass('loading'))
                return false;

            var order_id = $this.data('order');

            $.ajax({
                type: 'POST',
                data: {
                    order_id: order_id,
                    action: 'resend_approve'
                },
                beforeSend: function(){
                    $this.addClass('loading');
                },
                success: function(resp){
                    $this.removeClass('loading');
                    Swal.fire({
                        text: resp,
                        type: 'success',
                    });

                    app.update_page();
                }
            })

        });
    };
    var _init_payment_deferment = function () {
        $('#approvePaymentDeferment').click(function (e) {
            e.preventDefault()

            let $this = $(this)
            let order_id = $this.data('order_id')

            $.ajax({
                type: 'POST',
                data: {
                    order_id: order_id,
                    action: 'payment_deferment'
                },
                beforeSend: function () {
                    $this.find('.spinner-border').removeClass('d-none')
                    $this.prop('disabled', true)

                },
                success: function (resp) {
                    $this.find('.spinner-border').addClass('d-none')
                    $this.prop('disabled', false)

                    Swal.fire({
                        text: resp.success ?? resp.error,
                        type: resp.error ? 'error' : 'success',
                    })

                    $('#paymentDeferment').modal('hide')
                    if (resp.success) {
                        app.update_page()
                    }
                }
            })

        })
    }

    var _init_set_tehotkaz = function(){
        $(document).on('click', '.js-set-tehotkaz', function(){

            var $this = $(this);

            if ($this.hasClass('loading'))
                return false;

            var order_id = $this.data('order');

            $.ajax({
                type: 'POST',
                data: {
                    order_id: order_id,
                    action: 'set_tehotkaz'
                },
                beforeSend: function(){
                    $this.addClass('loading');
                },
                success: function(resp){
                    $this.removeClass('loading');

                    Swal.fire({
                        text: resp,
                        type: 'success',
                    });

                    app.update_page();
                }
            })

        });
    };

    var _init_masks = function(){
        $('.js-mask-input').each(function(){
            var _mask = $(this).data('mask');
            $(this).inputmask(_mask)
        })
    }

    var _init_submit_form = function(){

        app.current_card = $('.js-order-card:checked').val();
        console.log('app.current_card', app.current_card);
        $(document).on('submit', '#form_need_comment_card', function(e){
            e.preventDefault();

            var $form = $(this);

            console.log($('#form_need_comment_card [name=comment]').val() == '')

            if ($('#form_need_comment_card [name=comment]').val() == '')
            {
                alert('Необходимо написать комментарий');
                $('.js-order-card[value="'+app.current_card+'"]').attr('checked', true);
                console.log('app.current_card', app.current_card);

            }
            else
            {
                $.ajax({
                    url: $form.attr('action'),
                    data: {
                        action: 'add_comment',
                        order_id: $form.find('[name=order_id]').val(),
                        user_id: $form.find('[name=user_id]').val(),
                        text: 'Причина смены карты: '+$form.find('[name=comment]').val(),
                        block: 'card'
                    },
                    type: 'POST',
                    success: function(resp){
                        if (resp.success)
                        {
                            $('#modal_need_comment_card').modal('hide');

                            app.current_card = $('.js-order-card').val();
                        }
                        else
                        {
                            Swal.fire({
                                text: resp.error,
                                type: 'error',
                            });

                        }
                    }
                })


            }
            console.log('app.current_card', app.current_card);

        });

        $(document).on('change', '.js-order-card', function(){
            var card_id = $(this).val();
            var order_id = $(this).data('order');

            if ($(this).hasClass('js-need-comment-card'))
            {
                $('#modal_need_comment_card').modal();

                return false;
            }
        });

        $(document).on('submit', '.js-order-item-form', function(e){
            e.preventDefault();

            var $form = $(this);
            var _id = $form.attr('id');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    $form.addClass('loading');
                },
                success: function(resp){
                    if ($form.find('input[name="action"]').val().endsWith('_agreement'))
                        $('#modal_agreement_saved').modal();

                    var $content = $(resp).find('#'+_id).html();

                    $form.html($content);

                    $form.removeClass('loading');

                    _init_toggle_form();

                    if ($form.find('.js-dadata-address').length > 0)
                    {
                        new DadataAddressApp($form.find('.js-dadata-address'))
                    }
                }
            })
        });

        $(document).on('click', '.js-save-no-agreement', function (e) {
            var $form = $(this).closest('form');
            var $action = $form.find('input[name="action"]');
            var currentAction = $action.val();
            if (currentAction.endsWith('_agreement')) {
                currentAction = currentAction.replace('_agreement', '');
                $action.val(currentAction);
            }
        })

        $(document).on('click', '.js-save-agreement', function (e) {
            var $form = $(this).closest('form');
            var $action = $form.find('input[name="action"]');
            var currentAction = $action.val();
            if (!currentAction.endsWith('_agreement')) {
                currentAction+= '_agreement';
                $action.val(currentAction);
            }
        });
    }

    var _init_additional_phones = function (){
        $(document).on('click', '.js-remove-additionalphone', function(e){
            e.preventDefault();

            $(this).closest('.js-additionalphone-row').remove();
        });
    }

    var _init_additional_emails = function (){
        $(document).on('click', '.js-remove-additionalemail', function(e){
            e.preventDefault();
            console.log('test')

            $(this).closest('.js-additionalemail-row').remove();
        });
    }

    var _init_open_image_popup = function(){

        $.fancybox.defaults.btnTpl.rotate_left = '<button tabindex="-1" class="js-fancybox-rotate-left fancybox-button " ><i class="mdi mdi-rotate-left"></i></button>';
        $.fancybox.defaults.btnTpl.rotate_right = '<button tabindex="-1" class="js-fancybox-rotate-right fancybox-button " ><i class="mdi mdi-rotate-right"></i></button>';

        $.fancybox.defaults.btnTpl.approve_file = '<button tabindex="-1" class="js-fancybox-approve fancybox-button btn-outline-success btn" data-status="2"><i class="fas fa-check-circle"></i></button>';
        $.fancybox.defaults.btnTpl.reject_file = '<button tabindex="-1" class="js-fancybox-reject fancybox-button btn-outline-danger btn" data-status="3"><i class="fas fa-times-circle"></i></button>';
        $.fancybox.defaults.btnTpl.label_file = '<div class="js-fancybox-label" style="width:80px;display:inline-block"></div>';

        var _image_items = [];
        $('.order-images-list .js-image-item').each(function(){
            var $this = $(this);

            var _item = {
                id: $this.data('id'),
                status: $this.data('status'),
                $label: $this.find('.label-primary').removeClass('image-label')
            };
            _image_items.push(_item);
        })

        $.extend($.fancybox.defaults, {
            buttons: [
                "label_file",
                "approve_file",
                "reject_file",
                "zoom",
                "rotate_right",
                "rotate_left",
                "close",
            ],
            afterShow: function(obj){
                var current_item = _image_items[obj.currIndex]

                $('.js-fancybox-approve').data('id', current_item.id);
                $('.js-fancybox-reject').data('id', current_item.id);
                if (current_item.status == 2)
                {
                    $('.js-fancybox-approve').removeClass('btn-outline-success').addClass('btn-success');
                    $('.js-fancybox-reject').removeClass('btn-danger').addClass('btn-outline-danger');
                }
                if (current_item.status == 3)
                {
                    $('.js-fancybox-reject').removeClass('btn-outline-danger').addClass('btn-danger')
                    $('.js-fancybox-approve').removeClass('btn-success').addClass('btn-outline-success')
                }

                $('.js-fancybox-label').html(current_item.$label)

                console.info('current_item', current_item.id);
            }
        });
        $('.js-open-popup-image').fancybox({
            loop: true,
            hideScrollbar: false,
            autoFocus: false,
            hash: false,
            touch: false,

        });

        $(document).on('click', '.js-fancybox-approve, .js-fancybox-reject', function(e){
            e.preventDefault();

            var $this = $(this);
            var $form = $('.js-check-images');

            var _id = $this.data('id');
            var _status = $this.data('status');

            $('#status_'+_id).val(_status);

            $form.submit();

            if ($this.hasClass('js-fancybox-approve'))
            {
                $('.js-fancybox-reject').removeClass('btn-danger').addClass('btn-outline-danger')
                $('.js-fancybox-approve').removeClass('btn-outline-success').addClass('btn-success')
            }
            if ($this.hasClass('js-fancybox-reject'))
            {
                $('.js-fancybox-reject').removeClass('btn-outline-danger').addClass('btn-danger')
                $('.js-fancybox-approve').removeClass('btn-success').addClass('btn-outline-success')
            }
        });

        app.image_deg = 0
        $(document).on('click', '.js-fancybox-rotate-left', function(e){
            e.preventDefault();

            var $img = $('.fancybox-content img');

            new_deg = app.image_deg == 360 ? 0 : app.image_deg - 90;
            $img.css({'transform':'rotate('+new_deg+'deg)'})

            app.image_deg = new_deg
            //$img.attr('data-deg', new_deg);
        });
        $(document).on('click', '.js-fancybox-rotate-right', function(e){
            e.preventDefault();

            var $img = $('.fancybox-content img');

            new_deg = app.image_deg == 270 ? 0 : app.image_deg + 90;
            $img.css({'transform':'rotate('+new_deg+'deg)'})

            app.image_deg = new_deg
        });
    }

    var _init_accept_loan = function(){
        $(document).on('click', '.js-accept-order', function(e){
            var $btn = $(this);
            var _order_id = $(this).data('order');

            if ($btn.hasClass('loading'))
                return false;

            const is_approve_order = parseInt($("[name='is_approve_order']").val());
            if (is_approve_order) {
                $.ajax({
                    type: 'POST',
                    url: '/order/'+_order_id,
                    data: {
                        action: 'accept',
                        order_id: _order_id
                    },
                    beforeSend: function(){
                        $btn.addClass('loading');
                    },
                    success: function(resp){
                        if (!!resp.error)
                        {
                            Swal.fire({
                                timer: 5000,
                                title: 'Ошибка!',
                                text: resp.error,
                                type: 'error',
                            });
                            setTimeout(function(){
                                location.href = 'orders';
                            }, 5000);
                        }
                        else if (!!resp.success)
                        {
                            app.update_page();

                            $('.js-order-accept-block').hide();
                            $('.js-order-status-block').fadeIn();
                            Swal.fire({
                                timer: 5000,
                                text: resp.success,
                                type: 'success',
                            });
                        }
                        else
                        {
                            console.info(resp);
                        }
                    }
                })
            } else {
                const alert_html = '<div class="alert alert-danger alert-dismissible fade show" role="alert">\n' +
                    '                    Клиент на странице КР\n' +
                    '                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">\n' +
                    '                        <span aria-hidden="true">&times;</span>\n' +
                    '                    </button>\n' +
                    '                </div>';

                $("#alert_wrapper")
                    .empty()
                    .html(alert_html);
            }
        });
    };

    var _init_approve_loan = function(){
        $(document).on('click', '.js-approve-order', function(e){
            e.preventDefault();

            var $btn = $(this);
            var _order_id = $(this).data('order');

            if ($btn.hasClass('loading'))
                return false;

            // проверяем фото
            // var files_ready = 1;
            // $('.js-file-status').each(function(){
            //     if ($(this).val() != 2)
            //         files_ready = 0;
            // });
            //
            // if (!files_ready)
            // {
            //
            //     Swal.fire({
            //         timer: 5000,
            //         type: 'error',
            //         title: 'Ошибка!',
            //         text: 'Необходимо принять файлы клиента!',
            //         onClose: () => {
            //             $('html, body').animate({
            //                 scrollTop: $("#images_form").offset().top-100  // класс объекта к которому приезжаем
            //             }, 1000);
            //         }
            //     });
            //
            //     return false;
            // }

            var swalConfig = {
                title: 'Одобрить выдачу кредита?',
                text: "",
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Отменить',
                confirmButtonText: 'Да, одобрить'
            };

            let hasHyperC = $('input[name="has_hyper_c_scoring"]').val() === '1';
            if (hasHyperC) {
                swalConfig.html = `
                    <div class="hyper-c-checkbox-wrapper">
                        <label class="hyper-c-checkbox-container">
                            <input type="checkbox" id="is_order_decision_with_hyper_c" checked>
                            <span class="hyper-c-checkmark"></span>
                            <span class="hyper-c-label">Решение принято с учетом Hyper-C</span>
                        </label>
                    </div>
                `;
            }

            Swal.fire(swalConfig).then((result) => {
                if (result.value) {
                    let is_order_decision_with_hyper_c = null;
                    if (hasHyperC) {
                        is_order_decision_with_hyper_c = $('#is_order_decision_with_hyper_c').is(':checked') ? 1 : 0;
                    }

                    $.ajax({
                        type: 'POST',
                        url: '/order/'+_order_id,
                        data: {
                            action: 'approve',
                            order_id: _order_id,
                            is_order_decision_with_hyper_c: is_order_decision_with_hyper_c
                        },
                        beforeSend: function(){
                            $btn.addClass('loading');
                        },
                        success: function(resp){
                            if (!!resp.error) {
                                Swal.fire({
                                    title: 'Ошибка!',
                                    text: resp.error,
                                    type: 'error',
                                });
                                app.update_page();
                            } else {
                                app.update_page();

                                $('.js-order-head').html($(resp).find('.js-order-head').html())
                            }
                        }
                    })

                }
            })

        })
    };

    var _init_reject_loan = function(){

        $(document).on('click', '.js-reject-order', function(e){
            e.preventDefault();
            if (($('[name="has_pay_credit_rating"]').val() && !$('[name="has_last_scorista_scoring"]').val()) || (!$('[name="skip_credit_rating"]').val() && !$('[name="accept_reject_orders"]').val())) {
                $('.warning-text').show();
            } else {
                $('#modal_reject_reason').modal();
            }
        });

        $(document).on('submit', '.js-reject-form', function(e){
            e.preventDefault();

            var $form = $(this);

            if ($form.hasClass('loading'))
                return false;

            $.ajax({
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    $form.addClass('loading')
                },
                success: function(resp){
                    $form.removeClass('loading');
                    $('#modal_reject_reason').modal('hide');

                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                        setTimeout(function(){
                            location.href = 'orders';
                        }, 5000);
                    }
                    else
                    {
                        app.update_page();

                    }
                },
            })
        });
    };

    var _init_waiting_loan = function(){

        $(document).on('click', '.js-waiting-order', function(e){
            e.preventDefault();

            $('#modal_waiting_reason').modal();
        });

        $(document).on('submit', '.js-waiting-form', function(e){
            e.preventDefault();

            var $form = $(this);

            if ($form.hasClass('loading'))
                return false;

            $.ajax({
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    $form.addClass('loading')
                },
                success: function(resp){
                    $form.removeClass('loading');
                    $('#modal_waiting_reason').modal('hide');

                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                        setTimeout(function(){
                            location.href = 'orders';
                        }, 5000);
                    }
                    else
                    {
                        app.update_page();

                    }
                },
            })
        });
    };

    var _init_send_sms = function(){

        $(document).on('click', '.js-open-sms-modal', function(e){
            e.preventDefault();

            $('#modal_send_sms').modal();
        });

        $(document).on('submit', '.js-sms-form', function(e){
            e.preventDefault();

            var $form = $(this);

            if ($form.hasClass('loading'))
                return false;

            $.ajax({
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    $form.addClass('loading')
                },
                success: function(resp){
                    $form.removeClass('loading');
                    $('#modal_send_sms').modal('hide');

                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                        setTimeout(function(){
                            location.href = 'orders';
                        }, 5000);
                    }
                    else
                    {
                        app.update_page();

                    }
                },
            })
        });
    };


    var _init_contactperson = function(){
        app.$new_contactperson = $('#new_contactperson').clone('true');
        app.$new_contactperson.removeAttr('id');
        $('#new_contactperson').remove();

        $(document).on('click', '.js-add-contactperson', function(e){
            e.preventDefault();

            var $new_block = app.$new_contactperson.clone(true);
            $('#contactperson_edit_block').append($new_block);
        });

        $(document).on('click', '.js-remove-contactperson', function(e){
            e.preventDefault();

            $(this).closest('.js-contactperson-block').remove();
        });
    };

    var _init_change_image_status = function(){

        $(document).on('click', '.js-image-reject, .js-image-accept', function(e){
            var _id = $(this).data('id');
            if ($(this).hasClass('js-image-reject'))
                var _status = 3;
            else if ($(this).hasClass('js-image-accept'))
                var _status = 2;

            $('#status_'+_id).val(_status);

            $(this).closest('form').submit();
        });

    };

    var _init_maratorium = function(){

        $(document).on('click', '#open_maratorium_modal', function(e){
            e.preventDefault();

            $('#modal_add_maratorium').modal();
        });

        $(document).on('submit', '#form_add_maratorium', function(e){
            e.preventDefault();

            var $form = $(this);

            $.ajax({
                url: $form.attr('action'),
                data: $form.serialize(),
                type: 'POST',
                success: function(resp){
                    if (resp.success)
                    {
                        $('#modal_add_maratorium').modal('hide');
                        $('.js-maratorium-block').html('<strong class="text-warning">Мораторий до '+resp.date+'</strong>')
                        Swal.fire({
                            timer: 5000,
                            title: 'Мораторий добавлен.',
                            text: 'Дата завершения: '+resp.date,
                            type: 'success',
                        });
                    }
                    else
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });

                    }
                }
            })
        })
    };

    var _init_comment_form_client = function(){
        $(document).on('submit', '#form_add_comment_client_page', function(e){
            e.preventDefault();

            var $form = $(this);

            var uid = $(this).find('[name=uid]').val();

            $.ajax({
                url: $form.attr('action'),
                data: $form.serialize(),
                type: 'POST',
                success: function(resp){
                    if (resp.success)
                    {
                        $('#modal_add_comment').modal('hide');

                        Swal.fire({
                            timer: 5000,
                            title: 'Комментарий добавлен.',
                            type: 'success'
                        }).then(function() {
                            let tableComm = $('#table__comments');

                            if (tableComm.hasClass('d-none')){
                                tableComm.removeClass('d-none');
                            }

                            tableComm.append('' +
                                '<tr><td>' + resp.created+'</td>' +
                                '<td><a href="order/0">0</a></td>' +
                                '<td>'+ resp.manager_name +'</td>' +
                                '<td>Отвалы</td>' +
                                '<td>'+ resp.text +'</td>'+
                                '</tr>');


                        });
                    }
                    else
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });

                    }
                }
            })


        })
    }

    var _init_comment_form = function(){

        $(document).on('click', '.js-open-comment-form', function(e){
            e.preventDefault();

            $('#form_add_comment [name=block]').val($(this).data('block'));
            $('#form_add_comment [name=text]').text('').val('');

            $('#modal_add_comment').modal();
        });

        $(document).on('submit', '#form_add_comment', function(e){
            e.preventDefault();

            var $form = $(this);

            $.ajax({
                url: $form.attr('action'),
                data: $form.serialize(),
                type: 'POST',
                success: function(resp){
                    if (resp.success)
                    {
                        $('#modal_add_comment').modal('hide');

                        // Перезагружаем комментарии вместо обновления всей страницы
                        window.app.comments_data = null;
                        if (typeof load_comments === 'function' && window.app.order_config && window.app.order_config.order_id) {
                            load_comments(window.app.order_config.order_id);
                        }

                        Swal.fire({
                            timer: 5000,
                            title: 'Комментарий добавлен.',
                            type: 'success',
                        });
                    }
                    else
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });

                    }
                }
            })
        })
    }

    var _init_change_manager = function(){

        app.current_manager = $('.js-order-manager').val();

        $(document).on('submit', '#form_need_comment_manager', function(e){
            e.preventDefault();

            var $form = $(this);

            console.log($('#form_need_comment_manager [name=comment]').val())

            if ($('#form_need_comment_manager [name=comment]').val() == '')
            {
                alert('Необходимо написать комментарий');
                $('.js-order-manager option[value="'+app.current_manager+'"]').attr('checked', true);

            }
            else
            {
                $.ajax({
                    url: $form.attr('action'),
                    data: {
                        action: 'add_comment',
                        order_id: $form.find('[name=order_id]').val(),
                        user_id: $form.find('[name=user_id]').val(),
                        text: 'Причина смены менеджера: '+$form.find('[name=comment]').val(),
                        block: 'manager'
                    },
                    type: 'POST',
                    success: function(resp){
                        if (resp.success)
                        {
                            $('#modal_need_comment_manager').modal('hide');
                            $.ajax({
                                type: 'POST',
                                data: $form.serialize(),
                                success: function(resp){
                                    if (!!resp.error)
                                    {
                                        Swal.fire({
                                            text: resp.error,
                                            type: 'error',
                                        });
                                    }
                                    else
                                    {
                                        Swal.fire({
                                            timer: 5000,
                                            title: 'Менеджер изменен',
                                            type: 'success',
                                        });

                                    }
                                }
                            })

                        }
                        else
                        {
                            Swal.fire({
                                text: resp.error,
                                type: 'error',
                            });

                        }
                    }
                })


            }
        });

        $(document).on('change', '.js-order-manager', function(){
            var manager_id = $(this).val();
            var order_id = $(this).data('order');

            if ($(this).hasClass('js-need-comment'))
            {
                $('#form_need_comment_manager [name=manager_id]').val(manager_id)
                $('#modal_need_comment_manager').modal();

                $('.js-order-manager option[value="'+app.current_manager+'"]').attr('checked', true);

                return false;
            }
            else
            {
                $.ajax({
                    type: 'POST',
                    data: {
                        action: 'change_manager',
                        manager_id: manager_id,
                        order_id: order_id
                    },
                    success: function(resp){
                        if (!!resp.error)
                        {
                            Swal.fire({
                                text: resp.error,
                                type: 'error',
                            });
                        }
                        else
                        {
                            Swal.fire({
                                timer: 5000,
                                title: 'Менеджер изменен',
                                type: 'success',
                            });

                        }
                    }
                })

            }
        });
    }


    app.update_page = function(){
        var order_id = $('.js-load-balance-block').data('order');
        $.ajax({
            url: 'order/'+order_id,
            success: function(resp){
                $('#page_wrapper').html($(resp).filter('#page_wrapper').html());
                _init_toggle_form();
                _init_timers();
                _init_masks();
                _init_cyrillic_mask();
                console.warn('123321')
                try {
                    if ($('#page_wrapper').find('.js-dadata-address').length > 0)
                    {
                        console.log($('.js-dadata-address'))
                        $('#page_wrapper').find('.js-dadata-address').each(function(){
                            new DadataAddressApp($(this));
                        });
                    }
                    if ($('#page_wrapper').find('.js-dadata-work').length > 0)
                    {
                        $('#page_wrapper').find('.js-dadata-work').each(function(){
                            new DadataWorkApp($(this));
                        });
                    }
                } catch (e){
                    console.log(e)
                }

                // Очистка кэшированных данных вкладок (кроме order_config)
                window.app.scorings_data = null;
                window.app.credit_history_data = null;
                window.app.documents_data = null;
                window.app.comments_data = null;
                window.app.logs_data = null;
                window.app.insures_data = null;
                window.app.overpayments_data = null;

                var _user_id = $('.js-load-balance-block').data('user');
                _load_balance(_user_id, order_id, 1);

                var $backdrop = $('.modal-backdrop');
                if ($backdrop.length > 0 && $backdrop.is(':empty')) {
                    $backdrop.remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                }
            }
        })
    };

    var _init_scroll = function(){
        $(document).on('click', '.js-scroll-to', function(e){
            e.preventDefault();

            var _target = $(this).attr('href');

            if (_target == '#scorings')
                $('#scorings').addClass('show');

            $('html, body').animate({
                scrollTop: $(_target).offset().top
            }, 1000);
        })
    }

    var _init_next_stage = function(){
        $(document).on('click', '.js-next-stage', function(e){
            e.preventDefault();
            var _stage = $(this).data('stage');
            var _order_id = $(this).data('order');

            $.ajax({
                type: 'POST',
                data: {
                    action: 'next_stage',
                    stage: _stage,
                    order_id: _order_id
                },
                success: function(resp){
                    app.update_page();
                }
            });
        })

    }

    var _init_timers = function(){
        $('.js-timer').each(function(){
            var $this = $(this);
            var _start_time = $(this).data('start') || 0;
            var _timer = setInterval(function(){
                var result = getTimeRemaining(_start_time);
                $this.html(result);
                _start_time++;
            }, 1000);
            app.timers.push(_timer);
        });
    };

    function getTimeRemaining(t) {
        var seconds = Math.floor((t) % 60);
        var minutes = Math.floor((t/ 60) % 60);
        var hours = Math.floor((t / (60 * 60)));

        str_hours = hours > 9 ? hours : '0' + hours;
        str_minutes = minutes > 9 ? minutes : '0' + minutes;
        str_seconds = seconds > 9 ? seconds : '0' + seconds;

        if (hours > 0)
            return str_minutes + ':' + str_seconds;
        else
            return str_hours + ':' + str_minutes + ':' + str_seconds;
    }

    var _init_call_variants = function(){
        $(document).on('click', '.js-open-call-variants', function(e){
            e.preventDefault();

            $('#modal_call_variants').modal();
        });

        $(document).on('submit', '#form_call_variants', function(e){
            e.preventDefault();

            var $form = $(this);

            $.ajax({
                data: $form.serialize(),
                type: 'POST',
                success: function(resp){
                    $('#modal_call_variants').modal('hide');
                    if (resp.success)
                    {
                        app.update_page();
                    }
                    else
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });

                    }
                }
            })

        });
    }

    var _init_content_load = function(){
        $(document).on('click', '.js-load-balance-run', function(e){
            e.preventDefault();

            var _user_id = $('.js-load-balance-block').data('user');
            var _order_id = $('.js-load-balance-block').data('order');
            _load_balance(_user_id, _order_id, 1);
        });

        if ($('.js-load-balance-block').length > 0)
        {
            var _user_id = $('.js-load-balance-block').data('user');
            var _order_id = $('.js-load-balance-block').data('order');
            _load_balance(_user_id, _order_id, 0);
        }
    };

    var _load_balance = function (_user_id, _order_id, _update) {

        let managerRole = $('[name="manager_role"]').val();

        $.ajax({
            type: 'POST',
            data: {
                action: 'get_balance',
                user_id: _user_id,
                order_id: _order_id,
                update: _update
            },
            beforeSend: function () {
                $('.js-load-balance-block').addClass('loading');
            },
            success: function (resp) {
                if (!!resp.error) {
                    var html = '<h3 class="text-danger">Ошибка</h3>';
                    html += '<h5 class="text-danger">' + resp.error + '</h5>';
                } else if (!!resp.balance) {
                    if (resp.balance.zaim_number == '' || resp.balance.zaim_number == 'Нет открытых договоров') {
                        var html = '<div class="box bg-success pt-2 pb-4 pr-2 pl-2 text-center">';
                        html += '<div class="text-left pb-2"><a class="js-load-balance-run btn btn-xs btn-warning" href="javascript:void(0);"><i class="fas fa-history"></i> Обновить</a> <small class="float-right">' + resp.balance.last_update + '</small></div>';
                        html += '<h5 class="text-white">Нет открытых договоров</h5>'
                        html += '</div>';

                        var pay_result = $('.js-load-balance-block').data('pay');
                        if (pay_result != ''){
                            if( pay_result.indexOf('Merchant usage limit checkup failure') !== -1){
                                html += '<div class="btn btn-block btn-warning">Результат выдачи: ' + 'На карту клиента уже был выдан займ СЕГОДНЯ. Чтобы получить займ СЕГОДНЯ клиент должен добавить новую карту в ЛК и поменять её через верификаторов.' + '</div>';
                            }else{
                                html += '<div>Результат выдачи: ' + pay_result + '</div>';
                            }
                        }
                    } else if (resp.balance.zaim_number == 'Договор продан' || resp.balance.sale_info == 'Договор продан') {
                        var html = '<div class="box bg-warning pt-2 pb-4 pr-2 pl-2 text-center">';
                        html += '<div class="text-left pb-2"><a class="js-load-balance-run btn btn-xs btn-warning" href="javascript:void(0);"><i class="fas fa-history"></i> Обновить</a> <small class="float-right">' + resp.balance.last_update + '</small></div>';
                        html += '<h5 class="text-white">Договор продан ' + resp.balance.buyer + '</h5>'
                        html += '</div>';
                    } else {
                        let phone = $('.user-phone-mobile').html()
                        var html = '<div class="box bg-danger pt-2 pb-2">';
                        html += '<div><a class="js-load-balance-run btn btn-xs btn-warning" href="javascript:void(0);"><i class="fas fa-history"></i> Обновить</a> <small class="float-right">' + resp.balance.last_update + '</small></div>';
                        html += '<ul>';

                        // Если есть ВКЛ
                        if (!!resp.rcl) {
                            html += '<li>Номер договора ВКЛ: <strong>' + resp.rcl.number + '</strong></li>';
                            html += '<li>Дата выдачи ВКЛ: ' + resp.rcl.date_start + '</li>';
                            html += '<li style="margin-bottom: 1rem">Лимит ВКЛ: ' + resp.rcl.max_amount + '</li>';
                        }

                        html += '<li><strong>' + resp.balance.zaim_number + '</strong></li>';
                        html += '<li>Дата займа: ' + resp.balance.zaim_date + '</li>';

                        if (resp.balance.loan_type == 'IL') {
                            html += '<li>Общий Долг: ' + resp.balance.details['ОбщийДолг'] + '</li>';
                            html += '<li>Текущий Долг: ' + resp.balance.details['ТекущийДолг'] + '</li>';
                            html += '<li>Основной долг: ' + resp.balance.details['ТекущийДолг_ОД'] + '</li>';
                            html += '<li>Проценты: ' + resp.balance.details['ТекущийДолг_Проценты'] + '</li>';
                            html += '<li>Просроченный Долг: ' + resp.balance.details['ПросроченныйДолг'] + '</li>';
                            html += '<li>Просроченный Долг ОД: ' + resp.balance.details['ПросроченныйДолг_ОД'] + '</li>';
                            html += '<li>Просроченный Долг Процент: ' + resp.balance.details['ПросроченныйДолг_Процент'] + '</li>';
                            html += '<li>Ближайший Платеж Сумма: ' + resp.balance.details['БлижайшийПлатеж_Сумма'] + '</li>';
                            html += '<li>Ближайший Платеж Дата: ' + resp.balance.details['БлижайшийПлатеж_Дата'] + '</li>';
                            
                        } else {
                            html += '<li>Основной долг: ' + resp.balance.ostatok_od + '</li>';
                            html += '<li>Проценты: ' + resp.balance.ostatok_percents + '</li>';
    
                            if (managerRole !== 'verificator_minus') {
                                html += '<li>ШтрафнойКД: ' + resp.balance.penalty + '</li>';
                            }
    
                            html += '<li>Дата оплаты: ' + resp.balance.payment_date + '</li>';
                            if (managerRole !== 'verificator_minus') {
                                html += '<li>Мин платеж: ' + resp.balance.prolongation_amount + '</li>';
                            }
                            // html +=
                            //     '<li>Ссылка на форму оплаты: ' +
                            //     '<div class="link-data justify-content-center mb-2">' +
                            //     '<input type="text" style="width: 80%;" readonly id="link-input" class=" mb-2" placeholder = "Ссылка оплаты " value="' + resp.link + '">' +
                            //     '<input type="tel"  id="phone-input" value="' + phone + '" placeholder = "Номер телефона">' +
                            //     '</div>' +
                            //     '<button class="btn btn-sm btn-success send-sms-link" value="' + phone + '" data-user = "' + _user_id + '" data-zaim="' + resp.balance.zaim_number + '">Отправить смс </button>' +
                            //     '</li>';
                            
                        }
                        html += '</ul>';
                        html += '</div>';

                    }
                }

                $('.js-load-balance-inner').html(html);
                $('.js-load-balance-block').removeClass('loading');
            }
        })
    };
    $(document).on('input', '#phone-input', function () {
        var numericValue = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(numericValue);
    });

    $(document).on('click', '.send-sms', function () {
        let orderId = $(this).attr('data-order') ?? $('#orderSelect').val();
        let type = $(this).attr('data-type')
        let policyId = $(this).attr('data-policy')
        let manager = $(this).data('manager')
        let phone = $('.sms-phone').val();
        let whoseNumber = $('.modal-whose-number').val()
        let clientPhone = $('input[name="client-phone"]:checked').val();
        $.ajax({
            url: 'ajax/send_sms_with_short_link_new.php',
            type: 'post',
            data: {
                'orderId': orderId,
                'type': type,
                'policyId': policyId,
                'manager': manager,
                'phone':phone,
                'whoseNumber':whoseNumber,
                'clientPhone':clientPhone
            },
            success: function (resp) {
                if (resp.error)
                {
                    Swal.fire({
                        title: 'Ошибка',
                        text: resp.error,
                        type: 'error',
                    });
                }
                else
                {
                    Swal.fire({
                        title: 'Успешно',
                        text: resp.text,
                        type: 'success',
                    });
                    $('#sms-modal').modal('hide');
                    $('.btn-modal-send-sms[data-order="' + orderId + '"][data-type="' + type + '"]').prop('disabled', true);
                }
            }
        });
    })

    var _init_hold_card = function(){
        $(document).on('click', '.js-hold-card', function(e){
            e.preventDefault();

            var $this = $(this);

            if ($this.hasClass('loading'))
                return false;

            var _user_id = $this.data('user');
            var _card_id = $this.data('card');
            var _rebill_id = $this.data('rebill');

            $.ajax({
                url: FRONT_URL+'/ajax/payment.php',
                data: {
                    action: 'hold',
                    user_id: _user_id,
                    card_id: _card_id,
                    rebill_id: _rebill_id
                },
                beforeSend: function(){
                    $this.addClass('loading');
                },
                success: function(resp){
                    console.log(resp);

                    $this.removeClass('loading');

                    if (!!resp.ErrorCode && resp.ErrorCode > 0)
                    {
                        $this.closest('.custom-control').addClass('text-danger');

                        Swal.fire({
                            title: 'Ошибка',
                            text: resp.ErrorCode+' '+resp.Message,
                            type: 'error',
                        });
                    }
                    else
                    {
                        $this.closest('.custom-control').addClass('text-success');
                        Swal.fire({
                            title: 'Успешно',
                            type: 'success',
                        });
                    }
                }
            })
        });
    }

    $(document).on('click', '.js-block-user', function(e){
        e.preventDefault();
        let userId = $(this).data('user'),
            state = $(this).data('state'),
            stateText = state ? 'за' : 'раз';
        $.ajax({
            url: 'client/' + userId,
            type: 'POST',
            data: {'action': 'block', 'state': state},
            success: function(resp) {
                if (!!resp.error) {
                    Swal.fire({
                        timer: 5000,
                        title: 'Ошибка!',
                        text: resp.error,
                        type: 'error',
                    });
                } else {
                    Swal.fire({
                        timer: 5000,
                        title: '',
                        text: `Клиент ${stateText}блокирован!`,
                        type: 'success',
                    });
                    location.reload();
                }
            }
        });
    });

    var _init_send_sms = function(){

        $(document).on('click', '.js-open-sms-modal', function(e){
            e.preventDefault();

            var _user_id = $(this).data('user');

            $('#modal_send_sms [name=user_id]').val(_user_id)
            $('#modal_send_sms').modal();
        });

        $(document).on('click', '.js-send-sms', function(e){
            e.preventDefault();

            $('.js-sms-form [name=type]').val('sms')
            $('.js-sms-form').submit();
        });
        $(document).on('click', '.js-send-viber', function(e){
            e.preventDefault();

            $('.js-sms-form [name=type]').val('viber')
            $('.js-sms-form').submit();
        });
        $(document).on('click', '.js-send-whatsapp', function(e){
            e.preventDefault();

            $('.js-sms-form [name=type]').val('whatsapp')
            $('.js-sms-form').submit();
        });

        $('.js-blacklist-user').click(function(e) {
            e.preventDefault();
            if ($(this).data('state')) {
                $('#modal_blacklist').modal('show');
            } else {
                $.ajax({
                    url: 'client/' + $(this).data('user'),
                    type: 'POST',
                    data: {'action': 'blacklist', 'remove': 1},
                    success: function(resp) {
                        if (!!resp.error) {
                            Swal.fire({
                                timer: 5000,
                                title: 'Ошибка!',
                                text: resp.error,
                                type: 'error',
                            });
                        } else {
                            Swal.fire({
                                timer: 5000,
                                title: '',
                                text: resp.success,
                                type: 'success',
                            });
                            location.reload();
                        }
                    }
                });
            }
        });

        $('.js-graylist-user, .js-toggle_user_data_field-user, .js-test-loan').off('click').click(function (e) {
            $.ajax({
                url: 'client/' + $(this).data('user'),
                type: 'POST',
                data: {
                    'action': $(this).data('action'),
                    'field': $(this).data('field'),
                },
                success: function(resp) {
                    location.reload();
                }
            })
        });

        $(document).on('submit', '.js-sms-form', function(e){
            e.preventDefault();

            var $form = $(this);

            var _user_id = $form.find('[name=user_id]').val();

            if ($form.hasClass('loading'))
                return false;


            $.ajax({
                url: 'client/'+_user_id,
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    $form.addClass('loading')
                },
                success: function(resp){
                    $form.removeClass('loading');
                    $('#modal_send_sms').modal('hide');

                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    else
                    {
                        Swal.fire({
                            timer: 5000,
                            title: '',
                            text: 'Сообщение отправлено',
                            type: 'success',
                        });
                    }
                },
            })
        });
    };

    var _init_save_select = function() {
        $(document).on('change', '.js-save-client-select', function(){
            var $select = $(this);
            if ($select.hasClass('loading'))
                return false;

            $.ajax({
                type: 'POST',
                data: {
                    action: 'save_select',
                    field: $select.attr('name'),
                    value: $select.val()
                },
                beforeSend: function(){
                    $select.addClass('loading')
                },
                success: function(resp){
                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    else
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Успешно',
                            text: 'Изменения сохранены',
                            type: 'success',
                        });
                    }

                    app.update_page();

                }
            })
        });
    }

    var _init_return_insure = function(){
        $(document).on('click', '.js-return-insure', function(e){
            e.preventDefault();

            var $button = $(this);
            var _insure_id = $(this).data('insure');

            if ($button.hasClass('loading'))
                return false;

            $.ajax({
                type: 'POST',
                data: {
                    action: 'return_insure',
                    insure_id: _insure_id
                },
                beforeSend: function(){
                    $button.addClass('loading')
                },
                success: function(resp){
                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    else
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Успешно',
                            text: 'Страховка возвращена',
                            type: 'success',
                        });
                    }

                    app.update_page();

                }
            })
        });

    };

    // tinkoff
    var _init_return_insurance = function(){

        $(document).on('click', '.js-open-return-insurance', function(e){
            e.preventDefault();
            var _insurance_id = $(this).data('insurance');
            var _number = $(this).data('number');
            var _amount = $(this).data('amount');

            $('#modal_return_insurance [name=insurance_id]').val(_insurance_id);
            $('#modal_return_insurance .js-insurance-number').html(_number);
            $('#modal_return_insurance .js-insurance-amount').html(_amount);

            $('#modal_return_insurance').modal();

        });

        $(document).on('submit', '.js-return-insurance-form', function(e){
            e.preventDefault();

            var $form = $(this);

            if ($form.hasClass('loading'))
                return false;

            $.ajax({
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    $form.addClass('loading')
                },
                success: function(resp){
                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    else
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Успешно',
                            text: 'Страховка возвращена '+resp.success,
                            type: 'success',
                        });
                        app.update_page();
                    }
                    $form.addClass('loading');

                }
            })
        });

    };

    let _init_return_dop = function(){
        $(document).on('click', '.js-open-return-dop', function(e){
            e.preventDefault();
            let service_type = $(this).data('service-type'),
                service_id = $(this).data('service-id'),
                amount = $(this).data('amount'),
                return_amount = $(this).data('return_amount'),
                service_date = $(this).data('service-date'),
                order_id = $(this).data('order-id'),
                credit_card_id = $("#select-return-dop").val(),
                left_amount = amount - return_amount,
                half_amount = round(left_amount / 2),
                seventy_five = round(left_amount * 0.75);

            console.log(566666,return_amount,service_type,amount,left_amount)

            $('#modal_return_dop [name=card_id]').val(credit_card_id);
            $('#modal_return_dop [name=service_id]').val(service_id);
            $('#modal_return_dop [name=service_date]').val(service_date);
            $('#modal_return_dop [name=order_id]').val(order_id);
            $('#modal_return_dop [name=service]').val(service_type);

            if (+return_amount === 0) {
                $('#modal_return_dop .js-credit-doctor-size [value=half]').text('Вернуть половину: ' + half_amount + ' руб').show();
                $('#modal_return_dop .js-credit-doctor-size [value=seventy_five]').text('Вернуть 75%: ' + seventy_five + ' руб').show();
            }else {
                $('#modal_return_dop .js-credit-doctor-size [value=half]').hide();
                $('#modal_return_dop .js-credit-doctor-size [value=seventy_five]').hide();
            }
            $('#modal_return_dop .js-credit-doctor-size [value=all]').text('Вернуть все: ' + left_amount + ' руб');

            const hasCards = $('#select-return-dop option[value]').length > 0;
            const $returnTypeSelect = $('#modal_return_dop [name=return_type]');
            const $cardOption = $returnTypeSelect.find('option[value="card"]');

            $cardOption.toggle(hasCards).prop('disabled', !hasCards);

            if (!hasCards && $returnTypeSelect.val() === 'card') {
                $returnTypeSelect.val('sbp');
            }

            const $sbpSelect = $('#modal_return_dop [name=sbp_account_id]');
            const sbpCount = $sbpSelect.find('option').not('[value=""]').length;
            const $sbpOption = $returnTypeSelect.find('option[value="sbp"]');

            $sbpOption.toggle(sbpCount > 0).prop('disabled', sbpCount === 0);

            if (sbpCount === 0 && $returnTypeSelect.val() === 'sbp') {
                $returnTypeSelect.val(hasCards ? 'card' : 'recompense');
            }

            toggleRefundFields($returnTypeSelect.val());

            $('#modal_return_dop').modal()
        });

        $(document).on('change', '#modal_return_dop [name=return_type]', function() {
            let returnType = $(this).val();
            toggleRefundFields(returnType);
        });

        $(document).on('submit', '.js-return-credit-doctor-form', function(e){
            e.preventDefault();
            sendReturnForm($(this));
        });
    };

    function toggleRefundFields(returnType) {
        $('#sbp-account-selection').toggle(returnType === 'sbp');
    }

    const sendReturnForm = function ($form) {
            if ($form.hasClass('loading')) {
                return false;
            }
            
            let _url
            let return_type = $form.find('[name=return_type]').val() || '';
            if (return_type === 'recompense') {
                _url = 'ajax/Recompense.php'
            } else if (return_type === 'card') {
                _url = 'ajax/RefuseFromService.php'

                let selectedCardId = $form.find('[name=card_id]').val();
                if (!selectedCardId) {
                    Swal.fire({
                        timer: 5000,
                        title: 'Ошибка!',
                        text: 'Нет выбранной карты',
                        type: 'error',
                    });
                    return false;
                }
            } else if (return_type === 'sbp') {
                _url = 'ajax/RefuseFromService.php'
            }
            
            $.ajax({
                url: _url,
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function () {
                    $form.addClass('loading');
                    $('#modal_return_dop .js-return-dop-loader').show();
                    $('#modal_return_dop .js-return-dop-content').hide();
                },
                success: function (response) {
                    
                    $form.removeClass('loading');
                    $('#modal_return_dop .js-return-dop-loader').hide();
                    $('#modal_return_dop .js-return-dop-content').show();
                    
                    if (response.status !== true) {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: response.message,
                            type: 'error',
                        });
                        return;
                    }
                    
                    Swal.fire({
                        timer: 5000,
                        title: 'Успешно',
                        text: 'Результат: ' + response.message,
                        type: 'success',
                    });

                    app.update_page();
                    // Закрываем модальное окно и перезагружаем вкладку доп. услуг. Для гладкости интерфейса
                    $('#modal_return_dop').modal('hide');
                    window.app.insures_data = null;
                    if (typeof load_insures === 'function' && window.app.order_config && window.app.order_config.order_id) {
                        load_insures(window.app.order_config.order_id);
                    }
                }
            })
    }
    
    function _init_loan_type(){
        
        $(document).on('change', '.js-loan-type-select', function(){
            var current_type = $(this).val();
            if (current_type == 'IL') {
                $('.js-periods-select .js-pdl-periods').hide();
                $('.js-periods-select .js-il-periods').show();                
                if ($('.js-periods-select option:selected').hasClass('js-pdl-periods')) {
                    $('.js-periods-select .js-il-periods').first().attr('selected', true);
                }
            } else {
                $('.js-periods-select .js-il-periods').hide();
                $('.js-periods-select .js-pdl-periods').show();
                if ($('.js-periods-select option:selected').hasClass('js-il-periods')) {
                    $('.js-periods-select .js-pdl-periods').first().attr('selected', true);                
                }
            }
        });
        
        $('.js-loan-type-select').change();
    };

    function _init_disable_check_reports_for_loan(){

        $(document).on('click', '.disable_check_reports_for_loan-confirm', function () {

            const btn = $(".btn-modal-disable_check_reports_for_loan");

            const order_id = btn.attr('data-order')
            const manager_id = btn.attr('data-manager')

            $.ajax({
                url: '/ajax/orders.php?action=disable_check_reports_for_loan',
                data: {
                    order_id: order_id,
                    manager_id: manager_id,
                },
                method: 'POST',
                success: function (result) {

                    $('#disable_check_reports_for_loan-modal').modal('hide');

                    if (!result || !result.success) {
                        Swal.fire({
                            timer: 5000,
                            title: result && result.message ? result.message : 'Ошибка',
                            type: 'error',
                        });
                    }

                    app.update_page();
                }
            });
        });
    }

    function _init_cyrillic_mask() {
        $('[data-cyrillic]').each(function() {
            const $input = $(this);
            const type = $input.data('cyrillic');
            const errorMsg = $input.data('cyrillic-error') || 'Допускается ввод только русских букв';

            let pattern;
            switch(type) {
                case 'with-numbers':
                    // Допустимые символы: русские буквы, цифры, точка, запятая, дефис, пробелы,
                    // номер, слеш, тире, кавычки, скобки, обратный слеш (любое количество)
                    pattern = "[а-яёА-ЯЁ0-9\\.\\,\\-\\s№/.–«»\\(\\)\\\\]*";
                    break;
                case 'fio':
                    // Русские буквы, дефис и пробелы (любое количество)
                    pattern = "[а-яёА-ЯЁ\\-\\s]*";
                    break;
                default:
                    // Только русские буквы и пробелы (любое количество)
                    pattern = "[а-яёА-ЯЁ\\s]*";
            }

            $input.inputmask({
                regex: pattern,
                placeholder: "",
                showMaskOnHover: false,
                showMaskOnFocus: false,
                oncomplete: function() {
                    if ($input.data('cyrillic-capitalize')) {
                        const val = $(this).val();
                        $(this).val(val[0].toUpperCase() + val.slice(1));
                    }
                },
                onKeyValidation: function(key, result) {
                    if (!result && $input.data('cyrillic-show-error') !== false) {
                        $input.addClass('error');
                        if (!$input.next('.symbols-error').length) {
                            $input.after(`<small class="symbols-error text-danger">${errorMsg}</small>`);
                        }
                    } else {
                        $input.removeClass('error');
                        $input.next('.symbols-error').remove();
                    }
                }
            });
        });

    }


    function _init_card_type() {
        let filterOptions = function() {
            const cardTypeSelect = document.querySelector('.js-order-card-type');
            const cardSelect = document.querySelector('.js-order-card');

            const selectedCardTypeOption = cardTypeSelect ? cardTypeSelect.selectedOptions[0] : null;
            const selectedCardType = selectedCardTypeOption?.value ?? '';
            const cardOptions = cardSelect ? cardSelect.options : [];

            for (const cardOption of cardOptions) {
                const showNotSelectedOption = selectedCardType === 'card' && cardOption.value === '0';
                cardOption.style.display = showNotSelectedOption || cardOption.dataset.type === selectedCardType ? '' : 'none';
            }
        };

        $(document).on('focus', '.js-order-card-type', function () {
            filterOptions();
        });

        $(document).on('focus', '.js-order-card', function () {
            filterOptions();
        });

        // При изменении типа карты выбираем первый отображаемый <option>
        $(document).on('change', '.js-order-card-type', function () {
            filterOptions();
            const cardSelect = document.querySelector('.js-order-card');
            const visibleOptions = Array.from(cardSelect.options).filter(option => option.style.display !== 'none');

            if (visibleOptions.length > 0) {
                cardSelect.value = visibleOptions[0].value;
            } else {
                cardSelect.value = '0';
            }
        });

        filterOptions();
    }

    ;(function(){

    _init_toggle_form();
    _init_submit_form();
    _init_additional_phones();
    _init_additional_emails();
    _init_open_image_popup();
    _init_change_image_status();

    _init_accept_loan();
    _init_approve_loan();
    _init_reject_loan();
    _init_waiting_loan();

    _init_send_sms();

    _init_contactperson();

    _init_maratorium();
    _init_comment_form();
    _init_comment_form_client();

    _init_change_manager();

    _init_scroll();
    _init_next_stage();
    _init_timers();
    _init_call_variants();

    _init_masks();
    _init_cyrillic_mask();

    _init_content_load();
    _init_resend_approve();
    _init_payment_deferment()
    _init_set_tehotkaz();

    _init_hold_card();

    _init_send_sms();
    _init_return_insure();
    _init_return_insurance();
    _init_return_dop();
    _init_save_select();
    
    _init_loan_type();

    _init_disable_check_reports_for_loan();

    _init_card_type();
})();

$(function() {
    /**
     * Обработчик клика по кнопке "Отключить исходящие звонки"
     */
    $(document).on('click', '.js-disable-calls', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const userId = $(this).data('user');
        const days = $(this).data('days');

        console.log('Disable calls clicked:', userId, days);

        $.ajax({
            url: 'client/' + $(this).data('user'),
            type: 'POST',
            data: {'action': 'disable_outgoing_calls', 'id': userId, days: days},
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({
                        title: 'Успешно!',
                        text: resp.success,
                        type: 'success',
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Ошибка!',
                        text: resp.error,
                        type: 'error',
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Ошибка!',
                    text: 'Не удалось отключить звонки: ' + (xhr.responseJSON?.error || error),
                    type: 'error',
                });
            }
        });

        return false;
    });

    /**
     * Обработчик клика по кнопке "Включить исходящие звонки"
     */
    $(document).on('click', '.js-enable-calls', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const userId = $(this).data('user');

        $.ajax({
            url: 'client/' + $(this).data('user'),
            type: 'POST',
            data: {'action': 'disable_outgoing_calls', 'id': userId, enable: 1},
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({
                        title: 'Успешно!',
                        text: resp.success,
                        type: 'success',
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    console.log(resp);
                    Swal.fire({
                        title: 'Ошибка!',
                        text: resp.error,
                        type: 'error',
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Ошибка!',
                    text: 'Не удалось включить звонки: ' + (xhr.responseJSON?.error || error),
                    type: 'error',
                });
            }
        });

        return false;
    });
});

    const round= function (num,point=0) {
      return   Math.round((num + Number.EPSILON) * 10**point) / 10**point;
    };

    /**
     * Получить название услуги по типу
     */
    function getServiceDisplayName(serviceType) {
        const names = {
            'credit_doctor': 'Финансовый Доктор',
            'star_oracle': 'Звездный Оракул',
            'multipolis': 'Консьерж-сервис',
            'tv_medical': 'Вита-мед',
            'safe_deal': 'Безопасная Сделка',
            'overpayment': 'Переплата'
        };
        return names[serviceType] || serviceType;
    }

    /**
     * Показать результат проверки переплаты
     * @param {jQuery} $btn - кнопка проверки
     * @param {number|null} amount - сумма переплаты (если есть)
     * @param {string|null} message - сообщение для отображения (если нет amount)
     * @param {string|null} error - опциональная детальная ошибка
     * @param {string|null} type - тип уведомления ('info' или 'error', если нет amount)
     */
    function showOverpaymentAmountResult($btn, amount, message, error, type) {
        $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Проверить переплату');

        if (amount !== null && amount !== undefined) {
            const formattedAmount = amount.toLocaleString('ru-RU', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            $('#overpayment-amount').text(formattedAmount);
            $('#overpayment-result').show();

            const $returnBtn = $('#overpayment-result .js-open-return-by-requisites');
            $returnBtn.attr('data-amount', amount);
            $returnBtn.attr('data-amount-left', amount);
        } else {
            let html = '<p>' + message + '</p>';
            if (error) {
                const errorLabel = type === 'error' ? '' : 'Ошибка 1С: ';
                html += '<hr><small class="text-muted">' + errorLabel + error + '</small>';
            }

            Swal.fire({
                title: type === 'error' ? 'Ошибка!' : 'Информация',
                html: html,
                type: type,
                confirmButtonText: 'ОК'
            });
        }
    }

    /**
     * Проверка переплаты из 1С
     */
    $(document).on('click', '.js-check-overpayment', function() {
        const $btn = $(this);
        const orderId = $btn.data('order-id');

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Проверка...');

        $.ajax({
            url: '/ajax/RefundByRequisites.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'GetOverpaymentAmount',
                order_id: orderId
            },
            success: function(response) {
                if (response.status && response.amount) {
                    showOverpaymentAmountResult($btn, parseFloat(response.amount), null, null, null);
                } else {
                    const message = response.message || 'У клиента нет переплаты';
                    showOverpaymentAmountResult($btn, null, message, response.error, 'info');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Сервер 1С не отвечает, попробуйте позже';
                let errorDetail = '';

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.error) {
                        errorDetail = xhr.responseJSON.error;
                    }
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                        if (response.error) {
                            errorDetail = response.error;
                        }
                    } catch (e) {}
                }

                showOverpaymentAmountResult($btn, null, errorMessage, errorDetail, 'error');
            }
        });
    });

    /**
     * Открытие модалки возврата по реквизитам
     */
    $(document).on('click', '.js-open-return-by-requisites', function() {
        const $btn = $(this);
        const serviceType = $btn.data('service-type');
        const serviceId = $btn.data('service-id');
        const orderId = $btn.data('order-id');
        
        const clientFio = $btn.data('client-fio') || '';
        const clientBirthdate = $btn.data('client-birthdate') || '';
        const serviceName = getServiceDisplayName(serviceType);
        const amountLeft = $btn.data('amount-left') || $btn.data('amount') || 0;

        $('#form_return_by_requisites')[0].reset();
        $('#rr_alert').hide();
        $('#btn_rr_send').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Отправить');

        $('input[name="requisites_mode"][value="saved"]').prop('checked', true).parent().addClass('active');
        $('input[name="requisites_mode"][value="new"]').parent().removeClass('active');
        $('#rr_saved_requisites_block').show();
        $('#rr_new_requisites_block').hide();
        
        $('#rr_order_number').val(orderId);
        $('#rr_client_info').val(clientFio + ', ' + clientBirthdate);
        $('#rr_service_name').val(serviceName);
        
        if (serviceType === 'overpayment' && amountLeft > 0) {
            $('#rr_amount').val(amountLeft);
            $('#rr_amount').attr('max', amountLeft);
            $('#rr_max_amount').text(amountLeft.toLocaleString('ru-RU', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        } else {
            $('#rr_amount').val(amountLeft);
            $('#rr_amount').attr('max', amountLeft);
            $('#rr_max_amount').text(amountLeft);
        }

        $('#rr_service_type').val(serviceType);
        $('#rr_service_id').val(serviceId);
        $('#rr_order_id').val(orderId);
        $('#rr_recipient_fio').val(clientFio);

        $.ajax({
            url: '/ajax/RefundByRequisites.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'GetAdditionalData',
                service_type: serviceType,
                service_id: serviceId
            },
            success: function(response) {
                if (response.status) {
                    const data = response.data;

                    $('#rr_operation_id').val(data.operation_id);

                    $('#rr_saved_requisites').html('<option value="">Выберите реквизиты</option>');
                    if (data.saved_requisites && data.saved_requisites.length > 0) {
                        $.each(data.saved_requisites, function(i, req) {
                            $('#rr_saved_requisites').append(
                                $('<option></option>')
                                    .attr('value', req.id)
                                    .data('account', req.account_number)
                                    .data('bik', req.bik)
                                    .data('bank', req.bank_name)
                                    .data('recipient_fio', req.recipient_fio || '')
                                    .text(req.display_name)
                            );
                        });

                        $('#rr_saved_requisites').val($('#rr_saved_requisites option:eq(1)').val()).trigger('change');
                    } else {
                        $('input[name="requisites_mode"][value="new"]').prop('checked', true).parent().addClass('active');
                        $('input[name="requisites_mode"][value="saved"]').parent().removeClass('active');
                        $('#rr_saved_requisites_block').hide();
                        $('#rr_new_requisites_block').show();
                    }

                    $('#modal_return_by_requisites').modal('show');
                } else {
                    Swal.fire({
                        title: 'Ошибка!',
                        text: response.message,
                        type: 'error',
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Ошибка загрузки данных';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {}
                }
                
                Swal.fire({
                    title: 'Ошибка!',
                    text: errorMessage,
                    type: 'error',
                });
            }
        });
    });

    /**
     * Переключение: сохраненные / новые реквизиты
     */
    $('input[name="requisites_mode"]').on('change', function() {
        if ($(this).val() === 'saved') {
            $('#rr_saved_requisites_block').show();
            $('#rr_new_requisites_block').hide();
        } else {
            $('#rr_saved_requisites_block').hide();
            $('#rr_new_requisites_block').show();
        }
    });

    /**
     * При выборе сохраненных реквизитов - подставить в поля (для показа)
     */
    $('#rr_saved_requisites').on('change', function() {
        const selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#rr_account_number').val(selected.data('account'));
            $('#rr_bik').val(selected.data('bik'));
            $('#rr_bank_name').val(selected.data('bank'));
            $('#rr_recipient_fio').val(selected.data('recipient_fio') || '');
        }
    });

    /**
     * Кнопка "Отправить"
     */
    $('#btn_rr_send').on('click', function() {
        const requisitesMode = $('input[name="requisites_mode"]:checked').val();
        let accountNumber, bik, bankName, requisitesId;
        
        if (requisitesMode === 'saved') {
            const selected = $('#rr_saved_requisites').find('option:selected');
            if (!selected.val()) {
                showRRAlert('danger', 'Выберите сохраненные реквизиты');
                return;
            }
            requisitesId = selected.val();
            accountNumber = selected.data('account');
            bik = selected.data('bik');
            bankName = selected.data('bank');
        } else {
            accountNumber = $('#rr_account_number').val().trim();
            bik = $('#rr_bik').val().trim();
            bankName = $('#rr_bank_name').val().trim();

            if (!accountNumber || accountNumber.length !== 20 || !/^\d+$/.test(accountNumber)) {
                showRRAlert('danger', 'Номер счета должен содержать 20 цифр');
                return;
            }
            if (!bik || bik.length !== 9 || !/^\d+$/.test(bik)) {
                showRRAlert('danger', 'БИК должен содержать 9 цифр');
                return;
            }
        }
        
        const recipientFio = $('#rr_recipient_fio').val().trim();
        if (!recipientFio) {
            showRRAlert('danger', 'Укажите ФИО получателя');
            return;
        }

        const amount = parseFloat($('#rr_amount').val());
        const maxAmount = parseFloat($('#rr_max_amount').text());
        
        if (!amount || amount <= 0) {
            showRRAlert('danger', 'Укажите сумму возврата');
            return;
        }
        if (amount > maxAmount) {
            showRRAlert('danger', 'Сумма превышает остаток');
            return;
        }

        $('#btn_rr_send').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Отправка...');
        
        $.ajax({
            url: '/ajax/RefundByRequisites.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'Send',
                service_type: $('#rr_service_type').val(),
                service_id: $('#rr_service_id').val(),
                order_id: $('#rr_order_id').val(),
                amount: amount,
                recipient_fio: recipientFio,
                requisites_mode: requisitesMode,
                requisites_id: requisitesId,
                account_number: accountNumber,
                bik: bik,
                bank_name: bankName,
                save_requisites: $('#rr_save_requisites').is(':checked') ? 1 : 0,
                set_default: $('#rr_set_default').is(':checked') ? 1 : 0,
            },
            success: function(response) {
                if (response.status) {
                    showRRAlert('success', 'Успешно отправлено в 1С! Обновление страницы...');
                    $('#btn_rr_send').hide();
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showRRAlert('danger', response.message || 'Ошибка отправки');
                    $('#btn_rr_send').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Отправить');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Ошибка сети';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {}
                }
                
                showRRAlert('danger', errorMessage);
                $('#btn_rr_send').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Отправить');
            }
        });
    });

    /**
     * Показать алерт в модалке возврата по реквизитам
     */
    function showRRAlert(type, message) {
        $('#rr_alert').removeClass().addClass('alert alert-' + type).html(message).show();
    }

    $(document).on('click', '.js-return-status-refresh', function() {
        const $btn = $(this);
        const requestId = $btn.data('request-id');
        const serviceType = $btn.data('service-type');
        const serviceId = $btn.data('service-id');

        if (!requestId) {
            showRRAlert('info', 'Заявка ещё не отправлена в 1С');
            return;
        }

        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/ajax/RefundByRequisites.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'RefreshStatus',
                request_id: requestId
            },
            success: function(response) {
                if (response.status && response.data) {
                    updateServiceStatus(response.data, { serviceType, serviceId });
                    showRRAlert('success', 'Статус обновлён');
                } else {
                    showRRAlert('danger', response.message || 'Не удалось обновить статус');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Ошибка при обновлении статуса';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {}
                }
                
                showRRAlert('danger', errorMessage);
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    function updateServiceStatus(statusInfo, context) {
        context = context || {};

        const serviceType = (statusInfo && statusInfo.service_type) || context.serviceType;
        const serviceId = (statusInfo && statusInfo.service_id) || context.serviceId;

        if (!serviceType || !serviceId) {
            return;
        }

        const $container = $(`[data-return-status][data-service-type="${serviceType}"][data-service-id="${serviceId}"]`);
        if (!$container.length) {
            return;
        }

        const $badge = $container.find('[data-return-status-badge]');
        const $error = $container.find('[data-return-status-error]');
        const $updated = $container.find('[data-return-status-updated]');
        const $button = $container.find('.js-return-status-refresh');

        if (statusInfo && statusInfo.status) {
            $container
                .removeClass('d-none')
                .attr('data-request-id', statusInfo.id)
                .attr('data-status', statusInfo.status);

            $badge
                .removeClass(function(_, className) {
                    return (className || '').split(' ').filter(c => c.indexOf('badge-') === 0).join(' ');
                })
                .addClass('badge-' + (statusInfo.status_badge || 'info'))
                .removeClass('d-none')
                .text(statusInfo.status_text || statusInfo.status);

            if (statusInfo.error_text) {
                $error.removeClass('d-none').text(statusInfo.error_text);
            } else {
                $error.addClass('d-none').text('');
            }

            if (statusInfo.updated) {
                $updated.removeClass('d-none').text('Обновлено: ' + statusInfo.updated);
            } else {
                $updated.addClass('d-none').text('');
            }

            if (statusInfo.status === 'approved') {
                $button.addClass('d-none').prop('disabled', false);
            } else {
                $button
                    .removeClass('d-none')
                    .prop('disabled', false)
                    .data('request-id', statusInfo.id);
            }
        } else {
            $container.addClass('d-none').removeAttr('data-request-id data-status');
            $badge.addClass('d-none').text('');
            $error.addClass('d-none').text('');
            $updated.addClass('d-none').text('');
            $button.addClass('d-none').prop('disabled', false);
        }
    }

    /**
     * Синхронизация статуса заявки с 1С
     */
    $(document).on('click', '.sync-order-status-btn', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const orderId = $btn.data('order-id');
        console.log(orderId);
        if ($btn.hasClass('loading')) {
            return;
        }

        $btn.addClass('loading').prop('disabled', true);
        $btn.find('i').addClass('fa-spin');

        $.ajax({
            url: '/app/orders/' + orderId + '/sync-status-1c',
            type: 'PATCH',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (response.has_changes) {
                        Swal.fire({
                            title: 'Статус обновлен',
                            text: response.old_status + ' → ' + response.new_status,
                            type: 'success',
                            timer: 3000
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            text: 'Статус актуален: ' + response.current_status,
                            type: 'info',
                            timer: 3000
                        });
                    }
                } else {
                    Swal.fire({
                        title: 'Ошибка',
                        text: response.message || 'Неизвестная ошибка',
                        type: 'error',
                        timer: 5000
                    });
                }
            },
            error: function(xhr) {
                let errorMsg = 'Ошибка при обращении к серверу';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch (e) {}

                Swal.fire({
                    title: 'Ошибка',
                    text: errorMsg,
                    type: 'error',
                    timer: 5000
                });
            },
            complete: function() {
                $btn.removeClass('loading').prop('disabled', false);
                $btn.find('i').removeClass('fa-spin');
            }
        });
    });

};
new OrderApp();
