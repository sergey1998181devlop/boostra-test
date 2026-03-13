;function IndividualOrderApp()
{
    var app = this;
    
    app.$new_contactperson;
    
    app.image_deg = 0;
    
    var _init_toggle_form = function(){
        
        // редактирование формы
        $('.js-edit-form').each(function(){
            var $this = $(this);
            if (!$this.hasClass('evented'))
            {
                $this.addClass('evented');
                $this.click(function(e){
                    e.preventDefault();
                    
                    if ($(this).hasClass('open'))
                    {
                        $(this).removeClass('open');
                        
                        var $form = $(this).closest('form');
                        $form.find('.edit-block').addClass('hide');
                        $form.find('.view-block').removeClass('hide');
                    }
                    else
                    {
                        $(this).addClass('open');
                        
                        var $form = $(this).closest('form');
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
    
    var _init_submit_form = function(){

        app.current_card = $('.js-order-card').val();
        
        $(document).on('submit', '#form_need_comment_card', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            console.log($('#form_need_comment_card [name=comment]').val())
            
            if ($('#form_need_comment_manager [name=comment]').val() == '')
            {
                alert('Необходимо написать комментарий');
                $('.js-order-card option[value="'+app.current_card+'"]').attr('checked', true);
                
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
                    
                    var $content = $(resp).find('#'+_id).html();
                    
                    $form.html($content);
                    
                    $form.removeClass('loading');
                    
                    _init_toggle_form();
                }
            })
        });
    }
    
    var _init_open_image_popup = function(){

        $.fancybox.defaults.btnTpl.rotate_left = '<button class="js-fancybox-rotate-left fancybox-button " ><i class="mdi mdi-rotate-left"></i></button>';
        $.fancybox.defaults.btnTpl.rotate_right = '<button class="js-fancybox-rotate-right fancybox-button " ><i class="mdi mdi-rotate-right"></i></button>';
        

        $('.js-open-popup-image').fancybox({
            buttons: [
                "zoom",
                "rotate_right",
                "rotate_left",
                "close",
            ],
            loop: true,
            hideScrollbar: false,
            autoFocus: false,
            hash: false,
            touch: false,
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
    
            $.ajax({
                type: 'POST',
                url: '/individual_order/'+_order_id,
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
                            location.href = 'individual_orders';
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
        });
    };
    
    var _init_approve_loan = function(){
        $(document).on('click', '.js-approve-order', function(e){
            e.preventDefault();
            
            var $btn = $(this);
            var _order_id = $(this).data('order');
            
            if ($btn.hasClass('loading'))
                return false;
            
            /* проверяем фото
            var files_ready = 1;
            $('.js-file-status').each(function(){
                if ($(this).val() != 2)
                    files_ready = 0;
            });
            
            if (!files_ready)
            {
                
                Swal.fire({
                    timer: 5000,
                    type: 'error',
                    title: 'Ошибка!',
                    text: 'Необходимо принять файлы клиента!',
                    onClose: () => {
                        $('html, body').animate({
                            scrollTop: $("#images_form").offset().top-100  // класс объекта к которому приезжаем
                        }, 1000);
                    }
                });
                
                return false;
            }
            */
            
            Swal.fire({
                title: 'Одобрить выдачу кредита?',
                text: "",
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Отменить',
                confirmButtonText: 'Да, одобрить'
            }).then((result) => {
                if (result.value) {
                    
                    $.ajax({
                        type: 'POST',
                        url: '/individual_order/'+_order_id,
                        data: {
                            action: 'approve',
                            order_id: _order_id
                        },
                        beforeSend: function(){
                            $btn.addClass('loading');
                        },
                        success: function(resp){
                            app.update_page();
                            
                            $('.js-order-head').html($(resp).find('.js-order-head').html())
                        }
                    })
                    
                }
            })

        })
    };
    
    var _init_reject_loan = function(){
        
        $(document).on('click', '.js-reject-order', function(e){
            e.preventDefault();
            
            var $this = $(this);
            
            if ($this.hasClass('loading'))
                return false;
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'reject',
                    order_id: $this.data('order')
                },
                beforeSend: function(){
                    $this.addClass('loading')
                },
                success: function(resp){
                    $this.removeClass('loading');
                    
                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                        setTimeout(function(){
                            location.href = 'individual_orders';
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
            
                        app.update_page();
                        
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
        var order_id = $('#page_wrapper').data('order');
        
        $.ajax({
            url: 'individual_order/'+order_id,
            success: function(resp){
                $('#page_wrapper').html($(resp).filter('#page_wrapper').html());
                _init_toggle_form();
            }
        })
    };
    
    ;(function(){
        
        _init_toggle_form();
        _init_submit_form();
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
        
        _init_change_manager();
    })();
};
new IndividualOrderApp();