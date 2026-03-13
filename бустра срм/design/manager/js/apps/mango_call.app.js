$(document).on('click', '.js-mango-call', function(e){
            e.preventDefault();
        
            var _phone = $(this).data('phone');
            
            var _order_id = $(this).data('order') || 0;
            var _user_id = $(this).data('user') || 0;
            
            Swal.fire({
                title: 'Выполнить звонок?',
                text: "Вы хотите позвонить на номер: "+_phone,
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Отменить',
                confirmButtonText: 'Да, позвонить'
            }).then((result) => {
                if (result.value) {
                    
                    $.ajax({
                        url: 'ajax/mango_call.php',
                        data: {
                            phone: _phone,
                            order_id: _order_id,
                            user_id: _user_id
                        },
                        beforeSend: function(){
                            
                        },
                        success: function(resp){
                            if (!!resp.error)
                            {
                                if (resp.error == 'empty_mango')
                                {
                                    Swal.fire(
                                        'Ошибка!',
                                        'Необходимо указать Ваш внутренний номер сотрудника Mango-office.',
                                        'error'
                                    )
                                }
                                
                                if (resp.error == 'unknown_manager')
                                {
                                    Swal.fire(
                                        'Ошибка!',
                                        'Не хватает прав на выполнение операции.',
                                        'error'
                                    )
                                }
                            }
                            else if (resp.success)
                            {
                                /*
                                Swal.fire(
                                    '',
                                    'Выполняется звонок.',
                                    'success'
                                )
                                */
                            }
                            else
                            {
                                console.error(resp);
                                Swal.fire(
                                    'Ошибка!',
                                    '',
                                    'error'
                                )
                            }
                        }
                    })
                    
                }
            })
            
            
        });