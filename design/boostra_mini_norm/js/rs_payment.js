$(document).ready(function() {
    let isSubmitting = false;
    const $modal = $('#rs_payment_modal');
    let originalFormHtml = null;

    function resetModal() {
        if (originalFormHtml) {
            $modal.find('.rs-payment-form-container').html(originalFormHtml);
        }
        
        const $form = $('.rs-payment-form');
        if ($form.length) {
            $form[0].reset();
            $form.find('button[type="submit"]').prop('disabled', false).show();
            $('#rs_loader').hide();
            $('#rs_file_error').hide();
            $('#rs_order_id').val('');
        }
        
        isSubmitting = false;
    }

    function initFormHandlers() {
        $('#rs_payment_contract').off('change').on('change', function () {
            const selected = $(this).find('option:selected');
            const orderId = selected.data('order-id');
            $('#rs_order_id').val(orderId);
        });

        $('#rs_payment_file').off('change').on('change', function() {
            const maxFileSize = 100 * 1024 * 1024;
            if (this.files.length > 0) {
                const fileSize = this.files[0].size;
                if (fileSize > maxFileSize) {
                    $('#rs_file_error').text('Размер файла превышает 100 МБ. Пожалуйста, выберите файл меньшего размера.').show();
                } else {
                    $('#rs_file_error').hide();
                }
            }
        });
    }

    initFormHandlers();

    $(document).on('submit', '.rs-payment-form', function(e) {
        e.preventDefault();

        if (isSubmitting) {
            return false;
        }

        isSubmitting = true;
        const $currentForm = $(this);
        const $submitBtn = $currentForm.find('button[type="submit"]');

        $submitBtn.prop('disabled', true).hide();
        $('#rs_loader').show();
        $('#rs_file_error').hide();

        const maxFileSize = 100 * 1024 * 1024;
        const fileInput = document.getElementById('rs_payment_file');

        if (fileInput.files.length === 0) {
            $('#rs_file_error').text('Пожалуйста, выберите файл').show();
            $submitBtn.prop('disabled', false).show();
            $('#rs_loader').hide();
            isSubmitting = false;
            return false;
        }

        const fileName = fileInput.files[0].name;
        const fileExtension = fileName.split('.').pop().toLowerCase();
        const allowedExtensions = ['png', 'jpg', 'jpeg', 'pdf', 'doc', 'docx'];

        if (!allowedExtensions.includes(fileExtension)) {
            $('#rs_file_error').text('Недопустимый формат файла. Разрешены только PNG, JPG, PDF, DOC, DOCX.').show();
            $submitBtn.prop('disabled', false).show();
            $('#rs_loader').hide();
            isSubmitting = false;
            return false;
        }

        const fileSize = fileInput.files[0].size;
        if (fileSize > maxFileSize) {
            $('#rs_file_error').text('Размер файла превышает 100 МБ. Пожалуйста, выберите файл меньшего размера.').show();
            $submitBtn.prop('disabled', false).show();
            $('#rs_loader').hide();
            isSubmitting = false;
            return false;
        }

        const formData = new FormData(this);

        $.ajax({
            url: '/ajax/UploadPaymentRsHandler.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;

                    if (data.success) {
                        $('.rs-payment-form-container').html(
                            '<div class="text-center" style="padding: 40px 20px;">' +
                            '<div style="font-size: 48px; color: #00BB00; margin-bottom: 20px;">✓</div>' +
                            '<h3 style="color: #2E2E2E; margin-bottom: 15px;">Файл успешно загружен!</h3>' +
                            '<p style="color: #666; font-size: 14px;">Ваш чек принят в обработку. Платёж будет зачислен в течение до 3 рабочих дней.</p>' +
                            '</div>'
                        );
                        
                        setTimeout(function() {
                            $.magnificPopup.close();
                        }, 2000);
                    } else if (data.error) {
                        $('#rs_file_error').text(data.error).show();
                        $submitBtn.prop('disabled', false).show();
                        $('#rs_loader').hide();
                        isSubmitting = false;
                    }
                } catch (e) {
                    $('#rs_file_error').text('Ошибка при обработке ответа сервера').show();
                    $submitBtn.prop('disabled', false).show();
                    $('#rs_loader').hide();
                    isSubmitting = false;
                }
            },
            error: function(xhr, status, error) {
                let message = 'Ошибка при отправке файла: ' + error;

                try {
                    const responseJson = JSON.parse(xhr.responseText);
                    if (responseJson.error) {
                        message = responseJson.error;
                    }
                } catch (e) {}

                $('#rs_file_error').text(message).show();
                $submitBtn.prop('disabled', false).show();
                $('#rs_loader').hide();
                isSubmitting = false;
            }
        });
    });

    $('#openRsModal').on('click', function () {
        if (!originalFormHtml) {
            originalFormHtml = $modal.find('.rs-payment-form-container').html();
        }
        
        resetModal();
        initFormHandlers();
        
        $.magnificPopup.open({
            items: {
                src: '#rs_payment_modal'
            },
            type: 'inline',
            callbacks: {
                close: function() {
                    resetModal();
                }
            }
        });

        $('#rs_payment_contract').trigger('change');

        $('.any-faq').on('click', function () {
            sendMetric('reachGoal', 'ost_voprosy');
        });
    });
});

