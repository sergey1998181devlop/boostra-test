(function ($) {
    'use strict';

    // =============================
    // Neutralize conflicting clients.js handlers
    // clients.js binds first, so its handlers fire before ours.
    // We remove them and handle pagination/sorting/filtering ourselves.
    // Non-conflicting handlers (comments, SMS, save field) are kept.
    // =============================
    $(document).off('click', '.jsgrid-pager a');
    $(document).off('click', '.jsgrid-header-sortable a');
    $(document).off('blur', '.jsgrid-filter-row input');
    $(document).off('change', '.jsgrid-filter-row select');

    // =============================
    // Helpers
    // =============================
    function stripQuery(url) {
        return (url || '').split('?')[0];
    }

    function collectSearchParams() {
        var $form = $('#search_form');
        var sort = $form.find('[name=sort]').val();
        var searches = {};

        $form.find('input[type=text], select').each(function () {
            var $el = $(this);
            var name = $el.attr('name');
            var val = $el.val();

            if ($el.is('select[multiple]')) {
                if (Array.isArray(val) && val.length) {
                    searches[name] = Array.from(new Set(val));
                }
                return;
            }

            if (val !== null && val !== '') {
                searches[name] = val;
            }
        });

        return { sort: sort, search: searches };
    }

    function showLoader() {
        var preloaderTable = $('.preloader-table');
        if (preloaderTable.length) preloaderTable.show();
        else $('.preloader').show();
    }

    function hideLoader() {
        $('.preloader, .preloader-table').hide();
    }

    function ajaxUpdateGrid(url, data) {
        $.ajax({
            url: url,
            data: data,
            beforeSend: showLoader,
            success: function (resp) {
                var $newGrid = $(resp).find('#basicgrid');
                if ($newGrid.length) {
                    $('#basicgrid').html($newGrid.html());
                } else {
                    $('#basicgrid .jsgrid-grid-body').empty();
                    $('#basicgrid .jsgrid-pager-container').empty();
                }
            },
            complete: hideLoader
        });
    }

    function applyFilter() {
        var params = collectSearchParams();
        ajaxUpdateGrid(window.location.pathname, {
            search: params.search,
            sort: params.sort,
            page: 1
        });
    }

    // =============================
    // Pagination (preserves filters)
    // =============================
    $(document).on('click', '#basicgrid .jsgrid-pager a', function (e) {
        e.preventDefault();

        var href = $(this).attr('href') || '';
        if (!href) return;

        var baseUrl = stripQuery(href);

        var pageMatch = href.match(/[?&]page=(\d+)/);
        var page = pageMatch ? pageMatch[1] : 1;

        var params = collectSearchParams();
        ajaxUpdateGrid(baseUrl, {
            search: params.search,
            sort: params.sort,
            page: page
        });
    });

    // =============================
    // Sorting (preserves filters)
    // =============================
    $(document).on('click', '#basicgrid .jsgrid-header-sortable a', function (e) {
        e.preventDefault();

        var href = $(this).attr('href') || '';
        if (!href) return;

        var baseUrl = stripQuery(href);

        var sortMatch = href.match(/[?&]sort=([^&]+)/);
        var newSort = sortMatch ? sortMatch[1] : '';

        var params = collectSearchParams();
        ajaxUpdateGrid(baseUrl, {
            search: params.search,
            sort: newSort || params.sort,
            page: 1
        });
    });

    // =============================
    // Filters (all inputs and selects)
    // =============================
    $(function () {
        var STAGES_APPLY_DELAY = 800;
        var stagesTimer = null;

        function scheduleStagesApply() {
            clearTimeout(stagesTimer);
            stagesTimer = setTimeout(applyFilter, STAGES_APPLY_DELAY);
        }

        // Toggle multiselect option without Ctrl
        $(document).on('mousedown', '.jsgrid-filter-row select.js-stages-multi option', function (e) {
            e.preventDefault();
            var $opt = $(this);
            $opt.prop('selected', !$opt.prop('selected'));
            $opt.parent().trigger('change');
            return false;
        });

        // Stages multiselect — debounced
        $(document).on('change', '.jsgrid-filter-row select.js-stages-multi', function () {
            scheduleStagesApply();
        });

        // Regular selects — immediate
        $(document).on('change', '.jsgrid-filter-row select:not(.js-stages-multi)', function () {
            applyFilter();
        });

        // Text inputs — on blur
        $(document).on('blur', '.jsgrid-filter-row input[type=text]', function () {
            applyFilter();
        });
    });

    // =============================
    // Missings actions (set manager / close)
    // =============================
    function initSetManager() {
        $(document).on('click', '.js-set-manager', function () {
            var $this = $(this);
            var userId = $this.data('user');

            $.ajax({
                type: 'POST',
                data: { action: 'set_manager', user_id: userId },
                success: function (resp) {
                    if (resp && resp.error) {
                        Swal.fire({ text: resp.error, type: 'error' });
                        return;
                    }

                    $this.closest('.jsgrid-row').find('.js-close-missing').show();
                    $this.closest('.jsgrid-row').find('.js-missing-manager-name').text(resp.manager_name);
                }
            });
        });
    }

    function initCloseMissing() {
        $(document).on('click', '.js-close-missing', function () {
            var $this = $(this);
            var userId = $this.data('user');

            $.ajax({
                type: 'POST',
                data: { action: 'close_missing', user_id: userId },
                success: function (resp) {
                    if (resp && resp.error) {
                        Swal.fire({ text: resp.error, type: 'error' });
                        return;
                    }
                    $this.closest('.jsgrid-row').fadeOut();
                }
            });
        });
    }

    $(function () {
        initSetManager();
        initCloseMissing();
    });

    // =============================
    // Auto-updates: stages + last calls
    // =============================
    function getClientPageIds() {
        var ids = [];
        $('.stage-cell').each(function () {
            ids.push($(this).data('client-id'));
        });
        return ids;
    }

    function renderStages(client) {
        var html = '';
        html += '<span class="label label-success">Регистрация</span> ';
        html += '<span class="label ' + (Number(client.personal_data_added) ? 'label-success' : 'label-inverse') + '">Перс. инфо</span> ';
        html += '<span class="label ' + (Number(client.address_data_added) ? 'label-success' : 'label-inverse') + '">Адрес</span> ';
        html += '<span class="label ' + (Number(client.accept_data_added) ? 'label-success' : 'label-inverse') + '">Одобрение</span> ';
        html += '<span class="label ' + (Number(client.card_added) ? 'label-success' : 'label-inverse') + '">Карта</span> ';
        html += '<span class="label ' + (Number(client.files_added) ? 'label-success' : 'label-inverse') + '">Файлы</span> ';
        html += '<span class="label ' + (Number(client.additional_data_added) ? 'label-success' : 'label-inverse') + '">Доп. инфо</span> ';

        if (Number(client.stage_sms_sended)) {
            html += '<span class="label label-primary" title="СМС сообщение отправлено">СМС</span> ';
        }

        return html;
    }

    function updateClientData() {
        var ids = getClientPageIds();
        if (!ids.length) return;

        $.ajax({
            url: '/ajax/missings.php',
            method: 'post',
            dataType: 'json',
            data: { action: 'missing_stages', userIds: ids },
            success: function (clients) {
                if (!Array.isArray(clients)) return;

                for (var i = 0; i < clients.length; i++) {
                    var client = clients[i];
                    var $cell = $('td[data-client-id="' + client.id + '"].stage-cell');
                    $cell.html(renderStages(client));
                }
            }
        });
    }

    function updateClientLastCallData() {
        var ids = getClientPageIds();
        if (!ids.length) return;

        $.ajax({
            url: '/ajax/missings.php',
            method: 'post',
            dataType: 'json',
            data: { action: 'last_calls', userIds: ids },
            success: function (lastCalls) {
                if (!Array.isArray(lastCalls)) return;

                for (var i = 0; i < lastCalls.length; i++) {
                    var clientId = lastCalls[i].user_id;
                    var $callCell = $('td[data-client-id="' + clientId + '"].last-call-cell');
                    $callCell.html(lastCalls[i].created);
                }
            }
        });
    }

    $(function () {
        setInterval(updateClientData, 15000);
        setInterval(updateClientLastCallData, 10000);
    });

})(jQuery);
