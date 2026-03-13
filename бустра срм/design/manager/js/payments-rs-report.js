/**
 * JavaScript для страницы отчета по оплатам через р/с
 */
class PaymentsRsReport {
    constructor(reportUri) {
        this.reportUri = reportUri;
        this.init();
    }

    init() {
        this.initRejectModal();
        this.initFileModal();
    }

    /**
     * Инициализация модального окна для отклонения
     */
    initRejectModal() {
        $('#rejectReason').on('change', function () {
            $('#confirmRejectBtn').prop('disabled', !$(this).val());
        });

        $('#confirmRejectBtn').on('click', () => {
            const id = $('#rejectReasonModal').data('id');
            const btn = $('#rejectReasonModal').data('btn');
            const reason = $('#rejectReason').val();

            if (!reason) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Внимание',
                    text: 'Пожалуйста, выберите причину отклонения'
                });
                return;
            }

            this.performStatusUpdate(id, 'cancelled', reason, btn);
        });

        $('#rejectReasonModal').on('hidden.bs.modal', function () {
            $('#rejectReason').val('');
            $('#confirmRejectBtn').prop('disabled', true);
        });
    }

    /**
     * Инициализация модального окна для просмотра файлов
     */
    initFileModal() {
        $(document).on('click', '.open-file-modal', function (e) {
            e.preventDefault();
            const fileUrl = $(this).data('file');
            const fileName = $(this).text().trim();

            if (!fileUrl) {
                return;
            }

            $('#imageContainer').hide();
            $('#fileFrame').hide();

            const urlWithoutQuery = fileUrl.split('?')[0];
            const extension = urlWithoutQuery.split('.').pop().toLowerCase();
            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

            const decodedUrl = fileUrl.replace(/&amp;/g, '&');

            if (imageExtensions.includes(extension)) {
                $('#previewImage').attr('src', decodedUrl);
                $('#imageContainer').show();

            } else if (extension === 'pdf') {
                $('#imageContainer').html(`
                <div class="pdf-preview text-center" style="padding: 40px;">
                    <i class="fa fa-file-pdf-o fa-5x text-danger mb-3"></i>
                    <h4>PDF документ</h4>
                    <p class="text-muted mb-3">${fileName}</p>
                    <p class="text-muted">Предпросмотр PDF недоступен</p>
                    <a href="${decodedUrl}" download="${fileName}" class="btn btn-danger btn-lg">
                        <i class="fa fa-download"></i> Скачать PDF
                    </a>
                </div>
            `).show();
                $('#downloadLink').hide();
            } else {
                $('#fileFrame').attr('src', decodedUrl).show();
            }

            $('#downloadLink').attr('href', decodedUrl).attr('download', fileName || 'file');

            $('#fileModal').modal('show');
        });

        $('#fileModal').on('hidden.bs.modal', function () {
            $('#previewImage').attr('src', '');
            $('#fileFrame').attr('src', '');
            $('#downloadLink').attr('href', '');
            $('#imageContainer').html(`<img id="previewImage" src="" style="max-width: 100%; max-height: 500px;" />`);
        });
    }

    /**
     * Обновление статуса платежа
     */
    updateStatus(id, status, btn) {
        if (status === 'cancelled') {
            $('#rejectReasonModal').modal('show');
            $('#rejectReasonModal').data('id', id);
            $('#rejectReasonModal').data('btn', btn);
            return;
        }

        this.performStatusUpdate(id, status, null, btn);
    }

    /**
     * Выполнение AJAX запроса для обновления статуса
     */
    performStatusUpdate(id, status, reason = null, button = null) {
        const btn = button || $('#rejectReasonModal').data('btn');
        const $button = $(btn);

        if (!$button.length) {
            console.error('Button not found for status update');
            return;
        }

        const originalContent = $button.html();

        $button.html('<i class="fa fa-spinner fa-spin"></i>');
        $button.prop('disabled', true);

        let url = this.reportUri + '?action=updateStatus&id=' + id + '&status=' + status;
        if (reason) {
            url += '&reason=' + encodeURIComponent(reason);
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: (response) => {
                if (response.status === 'error') {
                    $button.html(originalContent);
                    $button.prop('disabled', false);

                    Swal.fire({
                        icon: 'error',
                        title: 'Ошибка',
                        text: response.message
                    });
                } else {
                    this.updateRowAfterStatusChange(id, status, $button);

                    $('#rejectReasonModal').modal('hide');

                    Swal.fire({
                        title: 'Успешно',
                        text: status === 'approved' ? 'Платеж одобрен' : 'Платеж отклонен',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: (xhr, status, error) => {
                $button.html(originalContent);
                $button.prop('disabled', false);

                Swal.fire({
                    title: 'Ошибка',
                    text: 'Произошла ошибка при выполнении запроса'
                });
            }
        });
    }

    /**
     * Обновление строки таблицы после изменения статуса
     */
    updateRowAfterStatusChange(id, status, $button) {
        const $row = $button.closest('tr');

        const $statusBadgeCell = $row.find('td:nth-child(5)');

        if (status === 'approved') {
            $statusBadgeCell.html('<span class="badge badge-success">Одобрено</span>');
        } else if (status === 'cancelled') {
            $statusBadgeCell.html('<span class="badge badge-danger">Отклонено</span>');
        }

        const $actionCell = $row.find('.status-cell .d-flex');

        if (status === 'approved') {
            $actionCell.html(`
                <button class="btn btn-danger btn-sm" onclick="updateStatus(${id}, 'cancelled', this); return false;" title="Отклонить">
                    <i class="fa fa-times"></i>
                </button>
            `);
        } else if (status === 'cancelled') {
            $actionCell.html(`
                <button class="btn btn-success btn-sm mr-2" onclick="updateStatus(${id}, 'approved', this); return false;" title="Одобрить">
                    <i class="fa fa-check"></i>
                </button>
            `);
        }
    }
}

window.updateStatus = function (id, status, btn) {
    if (window.paymentsRsReport) {
        window.paymentsRsReport.updateStatus(id, status, btn);
    } else {
        console.error('PaymentsRsReport not initialized');
    }
};

$(document).ready(function () {
    const reportUri = $('.page-wrapper').data('report-uri') || '';
    window.paymentsRsReport = new PaymentsRsReport(reportUri);
});
