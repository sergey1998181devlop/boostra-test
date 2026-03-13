function ProlongationsApp() {
    var app = this;

    var _init = function() {
        _init_pagination();
        _init_sortable();
        _init_filter();
        _init_filters();
    };

    var _init_filters = function() {
        $(document).on('click', '.js-filter-status a', function(e) {
            e.preventDefault();

            var _link = $(this).attr('href');

            app.load(_link, true, (resp) => {
                $('.js-count-order-page').html($(resp).find('.js-order-row').length || 0);
            });
        });

    }

    var _init_pagination = function() {
        $(document).on('click', '.jsgrid-pager a', function(e) {
            e.preventDefault();

            var _url = $(this).attr('href');

            app.load(_url, true, (resp) => {
                $('.js-count-order-page').html($(resp).find('.js-order-row').length || 0);
            });
        });
    };

    var _init_sortable = function() {
        $(document).on('click', '.jsgrid-header-sortable a', function(e) {
            e.preventDefault();

            var _url = $(this).attr('href');

            app.load(_url, true);
        });
    };

    var _init_filter = function() {
        $(document).on('keyup', '.jsgrid-filter-row input', app.filter);
        $(document).on('change', '.jsgrid-filter-row select', app.filter);
    };

    app.filter = function() {
        var $form = $('#search_form');
        var _sort = $form.find('[name=sort]').val()
        var _searches = {};
        $form.find('input[type=text]').each(function() {
            if ($(this).val() != '') {
                _searches[$(this).attr('name')] = $(this).val();
            }
        });
        $form.find('select').each(function() {
            if ($(this).val() != '') {
                _searches[$(this).attr('name')] = $(this).val();
            }
        });
        let filter_period = $('#filter_status').val() || '';
        var filter_date_range = $('#date_range').val() || '';

        $.ajax({
            data: {
                search: _searches,
                sort: _sort,
                period: filter_period,
                date_range: filter_date_range
            },
            beforeSend: function() {
                $('.jsgrid-load-shader').show();
                $('.jsgrid-load-panel').show();
            },
            success: function(resp) {

                $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                $('#basicgrid .jsgrid-pager-container').html($(resp).find('#basicgrid .jsgrid-pager-container').html() || '');
                $('#basicgrid .jsgrid-header-row').html($(resp).find('#basicgrid .jsgrid-header-row').html());

                $('.js-filter-status').html($(resp).find('.js-filter-status').html());

            }
        }).done(function() {
            $('.jsgrid-load-shader').hide();
            $('.jsgrid-load-panel').hide();
        })

    };

    app.load = function(_url, loading, callback = false) {

        var _split = _url.split('?');
        $.ajax({
            url: _url,
            beforeSend: function() {
                if (loading) {
                    $('.jsgrid-load-shader').show();
                    $('.jsgrid-load-panel').show();
                }
            },
            success: function(resp) {

                $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                $('#basicgrid .jsgrid-pager-container').html($(resp).find('#basicgrid .jsgrid-pager-container').html() || '');
                $('#basicgrid .jsgrid-header-row').html($(resp).find('#basicgrid .jsgrid-header-row').html());

                $('.js-filter-status').html($(resp).find('.js-filter-status').html());

                if (!!_split[1]) {
                    window.history.replaceState(null, null, location.pathname + '?' + _split[1]);
                }

                if (loading) {
                    $('html, body').animate({
                        scrollTop: $("#basicgrid").offset().top - 80
                    }, 1000);

                    $('.jsgrid-load-shader').hide();
                    $('.jsgrid-load-panel').hide();
                }

                if (callback) {
                    callback(resp);
                }
            }
        })
    };

    ;
    (function () {
        _init();
    })();
};

new ProlongationsApp();