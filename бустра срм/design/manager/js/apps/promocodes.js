$(document).ready(function() {
    const $form = $('#generatePromoCodeForm');
    const $modal = $('#generatePromoCodeModal');
    const $submitButton = $form.find('button[type="submit"]');
    const $spinner = $submitButton.find('.spinner-border');
    
    const errorMap = {
        'empty_date_start': 'dateStart',
        'empty_date_end': 'dateEnd',
        'empty_title': 'promoTitle',
        'invalid_date_format': 'dateStart',
        'end_date_before_start_date': 'dateEnd',
    };

    const systemErrors = ['user_not_found', 'promocode_creation_failed'];
    
    const systemErrorMessages = {
        'user_not_found': 'Пользователь не найден',
        'promocode_creation_failed': 'Ошибка при создании промокода'
    };

    function clearFormErrors() {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').hide();
    }

    function showFormErrors(errors) {
        const formErrors = [];
        const sysErrors = [];
        
        errors.forEach(error => {
            if (systemErrors.includes(error)) {
                sysErrors.push(systemErrorMessages[error] || error);
            } else if (errorMap[error]) {
                formErrors.push(error);
            }
        });

        clearFormErrors();
        formErrors.forEach(error => {
            const fieldId = errorMap[error];
            const $field = $(`#${fieldId}`);
            $field.addClass('is-invalid');
            $field.siblings('.invalid-feedback').show();
        });

        if (sysErrors.length > 0) {
            Swal.fire({
                title: 'Ошибка',
                text: sysErrors.join('\n'),
                icon: 'error',
                timer: 5000
            });
        }
    }

    $form.on('submit', function(event) {
        event.preventDefault();
        
        clearFormErrors();
        $submitButton.prop('disabled', true);
        $spinner.removeClass('d-none');

        const formData = $(this).serialize() + '&action=create';

        $.ajax({
            type: 'POST',
            url: '/promocode',
            data: formData,
            dataType: 'json'
        })
        .done(function(response) {
            if (response.errors) {
                showFormErrors(response.errors);
            } else if (response.promocode) {
                $modal.modal('hide');
                $form[0].reset();
                Swal.fire({
                    title: 'Промокод создан',
                    text: 'Промокод: ' + response.promocode.promocode,
                    icon: 'success'
                }).then(function() {
                    window.location.reload();
                });
            }
        })
        .fail(function(jqXHR) {
            Swal.fire({
                title: 'Ошибка сервера',
                text: 'Попробуйте позже',
                icon: 'error',
                timer: 5000
            });
        })
        .always(function() {
            $submitButton.prop('disabled', false);
            $spinner.addClass('d-none');
        });
    });

    $modal.on('hidden.bs.modal', function() {
        $form[0].reset();
        clearFormErrors();
    });

    $form.find('input').on('input', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').hide();
    });
});