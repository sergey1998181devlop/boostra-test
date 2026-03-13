;
function DadataRegionApp($block) {
    var app = this;
    app.$block = $block;

    app.debug = true;

    app.$input = {};
    app.kladr = {};

    var _init = function () {
        app.$input.index = app.$block.find('.js-dadata-index');
        app.$input.region = app.$block.find('.js-dadata-region');
        app.$input.region.change();
    };

    var _init_autocomplete_region = function () {
        app.$input.region.select2({
            // Configuration options for the select2 plugin
            width: 'resolve',
            placeholder: 'Выберите регион',
            minimumInputLength: 3,
            ajax: {
                url: 'ajax/dadata.php?action=region',
                dataType: 'json',
                data: function (params) {
                    return {
                        query: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data.suggestions, function (item) {
                            return {
                                text: item.value,
                                id: item.value,
                                data: item.data
                            };
                        })
                    };
                }
            }
        });

        app.$input.region.on('select2:select', function (e) {
            var suggestion = e.params.data;

            app.$input.index.val(suggestion.data.postal_code);
            app.$input.region_type.val(suggestion.data.region_type);
            app.$input.region.attr('data-kladr', suggestion.data.kladr_id);

            app.kladr.region = suggestion.data.kladr_id;

            app.$input.city.removeAttr('readonly');

            if (app.$input.city.hasClass("select2-hidden-accessible")) {
                app.$input.city.select2('destroy');
            }

            app.$input.city_real.val('');

            if (app.debug) {
                console.info('region', suggestion);
            }
        });
    };

    ;(function () {
        _init();
        _init_autocomplete_region();
    })();
}

$(function () {
    if ($('.js-dadata-address').length > 0) {
        $('.js-dadata-address').each(function () {
            new DadataRegionApp($(this));
        });
    }
});
