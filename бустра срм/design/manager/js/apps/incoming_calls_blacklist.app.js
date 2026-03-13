$(function(){
    
    // Маска для ввода телефона
    $('input[name="phone"]').inputmask("+7 (999) 999-99-99");

    $('input[name="search"]').inputmask('79999999999', {
        clearIncomplete: true,
        showMaskOnHover: false
    });

    // Обработка выбора причины
    const reasons = {
        '1': 'Грубое поведение, более 3раз (оскорбления, угрозы)',
        '2': 'Спам/злоупотребление, более 3раз (звонки автоматической системой, молчат в трубку)',
        '3': 'Неадекватное состояние, более 3раз (алкоголь, наркотики)',
        '4': 'Бессмысленные или фейковые обращения, более 3раз (троллинг)'
    };

    $('#reason_select').on('change', function() {
        const value = $(this).val();
        const customReasonGroup = $('#custom_reason_group');
        const reasonInput = $('#reason');

        if (value === '5') {
            customReasonGroup.show();
            reasonInput.val('').prop('required', true);
        } else {
            customReasonGroup.hide();
            reasonInput.val(reasons[value] || '').prop('required', false);
        }
    });
    
    // Добавление номера
    $('#add-item').on('click', function(e){
        e.preventDefault();
        
        let form = $('#add-form');
        let phone = form.find('[name=phone]').val();
        let reasonSelect = form.find('[name=reason_select]').val();
        let customReason = form.find('[name=reason]').val();
        let managerId = form.find('input[name="manager_id"]').val();
        
        if (!phone) {
            Swal.fire({
                title: 'Ошибка!',
                text: 'Укажите номер телефона',
                type: 'error'
            });
            return false;
        }

        if (!reasonSelect) {
            Swal.fire({
                title: 'Ошибка!',
                text: 'Выберите причину',
                type: 'error'
            });
            return false;
        }

        if (reasonSelect === '5' && !customReason.trim()) {
            Swal.fire({
                title: 'Ошибка!',
                text: 'Укажите причину блокировки',
                icon: 'error'
            });
            return false;
        }

        let reason = reasonSelect === '5' ? customReason : reasons[reasonSelect];
        
        $.ajax({
            url: 'app/blacklist/add',
            type: 'POST',
            data: {
                phone: phone,
                reason: reason,
                manager_id: managerId
            },
            success: function (resp) {
                Swal.fire({
                    title: 'Успешно!',
                    text: resp.message,
                    type: 'success'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(resp) {
                let response = resp.responseJSON;
                if (response && response.error === 'PHONE_EXISTS') {
                    Swal.fire({
                        title: 'Внимание!',
                        text: 'Этот номер уже находится в черном списке',
                        type: 'warning'
                    });
                } else {
                    Swal.fire({
                        title: 'Ошибка!',
                        text: response.message || 'Неизвестная ошибка',
                        type: 'error'
                    });
                }
            }
        });
        
    });
    
    // Удаление номера
    $('.js-delete-item').on('click', function(e){
        e.preventDefault();
        
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Вы уверены?',
            text: "Номер будет удален из черного списка",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6', 
            cancelButtonColor: '#d33',
            confirmButtonText: 'Да, удалить',
            cancelButtonText: 'Отмена'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: 'app/blacklist/delete',
                    type: 'POST', 
                    data: {
                        id: id
                    },
                    success: function(resp){
                        if(resp.success){
                            location.reload();
                        } else {
                            Swal.fire({
                                title: 'Ошибка!',
                                text: resp.message,
                                type: 'error'
                            });
                        }
                    }
                });
            }
        });
    });

    // Обработка переключателя статуса
    $('.js-toggle-status').on('change', function() {
        const $switch = $(this);
        const id = $switch.data('id');
        const isChecked = this.checked;

        $.ajax({
            url: 'app/blacklist/toggle',
            type: 'POST',
            data: {
                id: id,
                status: isChecked ? 1 : 0,
            },
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({
                        title: 'Успешно!',
                        text: resp.message,
                        type: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                let response = xhr.responseJSON;
                Swal.fire({
                    title: 'Ошибка!',
                    text: (response && response.message) || 'Неизвестная ошибка',
                    type: 'error'
                });
                // Возвращаем переключатель в предыдущее состояние
                $switch.prop('checked', !isChecked);
            }
        });
    });

}); 