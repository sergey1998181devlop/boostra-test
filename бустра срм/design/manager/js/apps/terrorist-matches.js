(function (window, $) {
    'use strict';

    if (!$) return;

    var TerroristMatchesUI = {
        modalId: 'terroristMatchesModal',
        bodyId: 'terroristMatchesBody',

        ensureModal: function () {
            if ($('#' + this.modalId).length) return;

            var html =
                '<div id="' + this.modalId + '" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">' +
                '  <div class="modal-dialog modal-lg">' +
                '    <div class="modal-content">' +
                '      <div class="modal-header">' +
                '        <h4 class="modal-title">Совпадения в террористических списках</h4>' +
                '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                '      </div>' +
                '      <div class="modal-body">' +
                '        <div class="card">' +
                '          <div class="card-body">' +
                '            <div class="tab-content tabcontent-border p-3">' +
                '              <div class="tab-pane fade active show" role="tabpanel">' +
                '                <div id="' + this.bodyId + '"><div class="text-muted">Загрузка...</div></div>' +
                '                <div class="form-action clearfix mt-3">' +
                '                  <button type="button" class="btn btn-secondary btn-lg float-right waves-effect" data-dismiss="modal">Закрыть</button>' +
                '                </div>' +
                '              </div>' +
                '            </div>' +
                '          </div>' +
                '        </div>' +
                '      </div>' +
                '    </div>' +
                '  </div>' +
                '</div>';

            $('body').append(html);

            // очистка при закрытии
            $('#' + this.modalId).on('hidden.bs.modal', function () {
                $('#' + TerroristMatchesUI.bodyId).html('');
            });
        },

        escapeHtml: function (s) {
            s = (s === null || s === undefined) ? '' : String(s);
            return s
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        labelMatchedBy: function (arr) {
            var map = { inn: 'ИНН', snils: 'СНИЛС', fio_dob: 'ФИО+ДР' };
            var out = [];
            (arr || []).forEach(function (k) {
                out.push(map[k] || k);
            });
            return out.join(', ');
        },

        render: function (data) {
            var client = data.client || {};
            var matches = data.matches || [];

            var html = '';
            html += '<div class="mb-3">';
            html += '  <h5 class="mb-2">Данные клиента</h5>';
            html += '  <div><strong>ФИО:</strong> ' + this.escapeHtml(client.full_name || '') + '</div>';
            html += '  <div><strong>Дата рождения:</strong> ' + this.escapeHtml(client.date_of_birth || '') + '</div>';
            if (client.inn) html += '  <div><strong>ИНН:</strong> ' + this.escapeHtml(client.inn) + '</div>';
            if (client.snils) html += '  <div><strong>СНИЛС:</strong> ' + this.escapeHtml(client.snils) + '</div>';
            html += '</div>';

            html += '<h5 class="mb-2">Совпадения (' + matches.length + ')</h5>';

            if (!matches.length) {
                html += '<div class="text-muted">Совпадений нет</div>';
                return html;
            }

            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-striped table-bordered">';
            html += '<thead><tr>' +
                '<th style="white-space:nowrap;">Совпало по</th>' +
                '<th>Источник</th>' +
                '<th style="white-space:nowrap;">Дата перечня</th>' +
                '<th style="white-space:nowrap;">Файл</th>' +
                '<th style="white-space:nowrap;">External ID</th>' +
                '<th>ФИО в перечне</th>' +
                '<th style="white-space:nowrap;">ДР</th>' +
                '<th style="white-space:nowrap;">ИНН</th>' +
                '<th style="white-space:nowrap;">СНИЛС</th>' +
                '<th style="white-space:nowrap;">Первое/последнее</th>' +
                '</tr></thead><tbody>';

            matches.forEach(function (m) {
                var srcOut = (m.source_name && m.source_name.length) ? m.source_name : (m.source_code || '');
                html += '<tr>';
                html += '<td>' + TerroristMatchesUI.escapeHtml(TerroristMatchesUI.labelMatchedBy(m.matched_by)) + '</td>';
                html += '<td>' + TerroristMatchesUI.escapeHtml(srcOut) + '</td>';
                html += '<td>' + TerroristMatchesUI.escapeHtml(m.list_date || '') + '</td>';
                html += '<td>' + (m.import_file_id ? TerroristMatchesUI.escapeHtml(m.import_file_id) : '') + '</td>';
                html += '<td>' + TerroristMatchesUI.escapeHtml(m.external_id || '') + '</td>';
                html += '<td>' + TerroristMatchesUI.escapeHtml(m.full_name || '') + '</td>';
                html += '<td>' + TerroristMatchesUI.escapeHtml(m.date_of_birth || '') + '</td>';
                html += '<td>' + TerroristMatchesUI.escapeHtml(m.inn || '') + '</td>';
                html += '<td>' + TerroristMatchesUI.escapeHtml(m.snils || '') + '</td>';

                var seen = '';
                if (m.first_seen_date) seen += TerroristMatchesUI.escapeHtml(m.first_seen_date);
                if (m.last_seen_date && m.last_seen_date !== m.first_seen_date) seen += '<br>' + TerroristMatchesUI.escapeHtml(m.last_seen_date);
                html += '<td><small>' + (seen || '') + '</small></td>';

                html += '</tr>';
            });

            html += '</tbody></table></div>';
            html += '<small class="text-muted">Данные из результата скоринга.</small>';

            return html;
        },

        openByScoringId: function (scoringId) {
            this.ensureModal();

            var $body = $('#' + this.bodyId);
            $body.html('<div class="text-muted">Загрузка...</div>');
            $('#' + this.modalId).modal('show');

            $.ajax({
                url: '/ajax/terrorist_matches.php',
                type: 'GET',
                dataType: 'json',
                data: { scoring_id: scoringId },
                success: function (resp) {
                    if (resp && resp.success && resp.data) {
                        $body.html(TerroristMatchesUI.render(resp.data));
                    } else {
                        $body.html('<div class="text-danger">' + TerroristMatchesUI.escapeHtml(resp && resp.message ? resp.message : 'Не удалось загрузить данные') + '</div>');
                    }
                },
                error: function (xhr) {
                    var msg = 'Ошибка загрузки';
                    try {
                        var r = JSON.parse(xhr.responseText);
                        if (r && r.message) msg = r.message;
                    } catch (e) {}
                    $body.html('<div class="text-danger">' + TerroristMatchesUI.escapeHtml(msg) + '</div>');
                }
            });
        },

        bind: function () {
            // универсально: кнопка может быть и в заказе, и в клиенте
            $(document).on('click', '.js-terrorist-details', function (e) {
                e.preventDefault();
                var scoringId = $(this).data('scoring-id') || $(this).attr('data-scoring-id');
                scoringId = parseInt(scoringId || 0, 10);

                if (!scoringId) {
                    Swal.fire({
                        title: 'Ошибка',
                        text: 'Не найден scoring_id',
                        type: 'error',
                        timer: 4000
                    });
                    return false;
                }

                TerroristMatchesUI.openByScoringId(scoringId);
                return false;
            });
        }
    };

    // экспорт в window (если нужно дергать вручную)
    window.TerroristMatchesUI = TerroristMatchesUI;

    $(function () {
        TerroristMatchesUI.bind();
    });

})(window, window.jQuery);
