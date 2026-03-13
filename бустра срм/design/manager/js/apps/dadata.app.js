;

function DadataAddressApp($block) {
    var app = this;
    app.$block = $block;

    app.debug = true;

    app.$input = {};
    app.kladr = {};

    var _init = function () {

        app.$input.index = app.$block.find('.js-dadata-index');
        app.$input.region = app.$block.find('.js-dadata-region');
        app.$input.region_type = app.$block.find('.js-dadata-region-type');
        app.$input.district = app.$block.find('.js-dadata-district');
        app.$input.district_type = app.$block.find('.js-dadata-district-type');
        app.$input.locality = app.$block.find('.js-dadata-locality');
        app.$input.locality_type = app.$block.find('.js-dadata-locality-type');
        app.$input.city = app.$block.find('.js-dadata-city');
        app.$input.city_real = app.$block.find('.js-dadata-city-real');
        app.$input.city_type = app.$block.find('.js-dadata-city-type');
        app.$input.street = app.$block.find('.js-dadata-street');
        app.$input.street_type = app.$block.find('.js-dadata-street-type');
        app.$input.house = app.$block.find('.js-dadata-house');
        app.$input.building = app.$block.find('.js-dadata-building');
        app.$input.room = app.$block.find('.js-dadata-room');

        app.$input.region.change();

    };

    var _init_change_region = function () {
        app.$input.region.change(function () {

            app.$input.index.val('');
            app.$input.city.val('');
            app.$input.city_real.val('');
            app.$input.city_type.val('');
            app.$input.district.val('');
            app.$input.district_type.val('');
            app.$input.locality.val('');
            app.$input.locality_type.val('');
            app.$input.street.val('');
            app.$input.street_type.val('');
            app.$input.house.val('');
            app.$input.building.val('');
            app.$input.room.val('');

            app.kladr.region = 0;
            app.kladr.city = 0;
            app.kladr.street = 0;
        });
    };

    var _init_change_city = function () {
        app.$input.city.change(function () {

            app.$input.index.val('');
            app.$input.city_real.val('');
            app.$input.city_type.val('');
            /*app.$input.street.val('');
            app.$input.street_type.val('');
            app.$input.district.val('');
            app.$input.district_type.val('');
            app.$input.locality.val('');
            app.$input.locality_type.val('');
            app.$input.house.val('');
            app.$input.building.val('');
            app.$input.room.val('');*/

            app.kladr.city = 0;
            //app.kladr.street = 0;
        });
    };

    var _init_change_street = function () {
        app.$input.street.change(function () {

            app.$input.index.val('');
            app.$input.street_type.val('');
            app.$input.house.val('');
            app.$input.building.val('');
            app.$input.room.val('');

            app.kladr.street = 0;
        });
    };

    var _init_change_house = function () {
        app.$input.house.change(function () {

            app.$input.building.val('');
            app.$input.room.val('');

        });
    };

    var _init_change_building = function () {
        app.$input.building.change(function () {

            app.$input.room.val('');

        });
    };

    /*
    var _init_autocomplete_region = function () {

        app.$input.region.select2({
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
            _init_autocomplete_city();

            app.$input.city_real.val('');

            if (app.debug) {
                console.info('region', suggestion);
            }
        });
    };
*/
    var _init_autocomplete_region = function() {
         app.$input.region.autocomplete({
             serviceUrl:'ajax/dadata.php?action=region',
             minChars:3,
             noCache: true,
             onSelect: function(suggestion){

                 app.$input.index.val(suggestion.data.postal_code);
                 app.$input.region.val(suggestion.data.region);
                 app.$input.region_type.val(suggestion.data.region_type);
                 app.$input.region.attr('data-kladr', suggestion.data.kladr_id);

                 app.kladr.region = suggestion.data.kladr_id;

                 app.$input.city.removeAttr('readonly');

                 app.$input.city.autocomplete('dispose');
                 _init_autocomplete_city();

//                 app.$input.city.focus();
                 app.$input.city_real.val('');

                 if (app.debug)
                     console.info('region', suggestion);
             },
             formatResult: function(suggestion, currentValue){
                 return suggestion.value;
             }
         });
    };


    var _init_autocomplete_district = function () {

        app.$input.district.autocomplete({
            serviceUrl: function () {
                var _url = 'ajax/dadata.php?action=city&region=' + app.kladr.region;
                if (app.debug) console.log(app.kladr.region, _url);
                return _url;
            },
            minChars: 3,
            noCache: true,
            onSelect: function (suggestion) {

                app.$input.index.val(suggestion.data.postal_code);
                app.$input.region.val(suggestion.data.region);
                app.$input.region_type.val(suggestion.data.region_type);
                app.$input.region.attr('data-kladr', suggestion.data.kladr_id);

                app.kladr.region = suggestion.data.kladr_id;

                if (suggestion.data.area) {
                    app.$input.district.val(suggestion.data.area)
                    app.$input.district_type.val(suggestion.data.area_type)
                } else {
                    app.$input.district.val('')
                    app.$input.district_type.val('')
                }

                if (!!suggestion.data.settlement) {
                    app.$input.locality.val(suggestion.data.settlement)
                    app.$input.locality_type.val(suggestion.data.settlement_type);
                } else {
                    app.$input.locality.val('');
                    app.$input.locality_type.val('');
                }


                if (!!suggestion.data.settlement) {
                    app.$input.city.val((!!suggestion.data.city ? suggestion.data.city + ' ' : '') + suggestion.data.settlement);
                    app.$input.city_type.val(suggestion.data.settlement_type);
                    app.$input.city_real.val((!!suggestion.data.city ? suggestion.data.city : ''))

                    app.$input.locality.val(suggestion.data.settlement)
                    app.$input.locality_type.val(suggestion.data.settlement_type);

                    app.kladr.city = suggestion.data.settlement_kladr_id;
                } else {
                    app.$input.city.val(suggestion.data.city)
                    app.$input.city_real.val(suggestion.data.city)
                    app.$input.city_type.val(suggestion.data.city_type);

                    app.$input.locality.val('')
                    app.$input.locality_type.val('');

                    app.kladr.city = suggestion.data.city_kladr_id;
                }

                app.$input.house.removeAttr('readonly');
                app.$input.house.autocomplete('dispose');

                app.$input.index.val(suggestion.data.postal_code);
                app.$input.street.removeAttr('readonly');
                app.$input.street.autocomplete('dispose');
                _init_autocomplete_street();

                app.$input.street.focus();

                if (app.debug)
                    console.info('city', suggestion);
            },
            formatResult: function (suggestion, currentValue) {
                return suggestion.value;
            }
        });

    };

    var _init_autocomplete_locality = function () {

        app.$input.locality.autocomplete({
            serviceUrl: function () {
                var _url = 'ajax/dadata.php?action=city&region=' + app.kladr.region
                if (app.debug) console.log(app.kladr.region, _url)
                return _url;
            },
            minChars: 3,
            noCache: true,
            onSelect: function (suggestion) {

                app.$input.index.val(suggestion.data.postal_code);
                app.$input.region.val(suggestion.data.region);
                app.$input.region_type.val(suggestion.data.region_type);
                app.$input.region.attr('data-kladr', suggestion.data.kladr_id);

                app.kladr.region = suggestion.data.kladr_id;

                if (suggestion.data.area) {
                    app.$input.district.val(suggestion.data.area)
                    app.$input.district_type.val(suggestion.data.area_type)
                } else {
                    app.$input.district.val('')
                    app.$input.district_type.val('')
                }

                if (!!suggestion.data.settlement) {
                    app.$input.locality.val(suggestion.data.settlement)
                    app.$input.locality_type.val(suggestion.data.settlement_type);
                } else {
                    app.$input.locality.val('');
                    app.$input.locality_type.val('');
                }


                if (!!suggestion.data.settlement) {
                    app.$input.city.val((!!suggestion.data.city ? suggestion.data.city + ' ' : '') + suggestion.data.settlement);
                    app.$input.city_type.val(suggestion.data.settlement_type);
                    app.$input.city_real.val((!!suggestion.data.city ? suggestion.data.city : ''))

                    app.$input.locality.val(suggestion.data.settlement)
                    app.$input.locality_type.val(suggestion.data.settlement_type);

                    app.kladr.city = suggestion.data.settlement_kladr_id;
                } else {
                    app.$input.city.val(suggestion.data.city)
                    app.$input.city_real.val(suggestion.data.city)
                    app.$input.city_type.val(suggestion.data.city_type);

                    app.$input.locality.val('')
                    app.$input.locality_type.val('');

                    app.kladr.city = suggestion.data.city_kladr_id;
                }

                app.$input.house.removeAttr('readonly');
                app.$input.house.autocomplete('dispose');

                app.$input.index.val(suggestion.data.postal_code);
                app.$input.street.removeAttr('readonly');
                app.$input.street.autocomplete('dispose');
                _init_autocomplete_street();

                app.$input.street.focus();

                if (app.debug)
                    console.info('city', suggestion);
            },
            formatResult: function (suggestion, currentValue) {
                return suggestion.value;
            }
        });

    };

    var _init_autocomplete_city = function () {

        app.$input.city.autocomplete({
            serviceUrl: function () {
                var _url = 'ajax/dadata.php?action=city&region=' + app.kladr.region
                if (app.debug) console.log(app.kladr.region, _url)
                return _url;
            },
            minChars: 3,
            noCache: true,
            onSelect: function (suggestion) {

                app.$input.index.val(suggestion.data.postal_code);
                app.$input.region.val(suggestion.data.region);
                app.$input.region_type.val(suggestion.data.region_type);
                app.$input.region.attr('data-kladr', suggestion.data.kladr_id);

                app.kladr.region = suggestion.data.kladr_id;

                if (suggestion.data.area) {
                    app.$input.district.val(suggestion.data.area)
                    app.$input.district_type.val(suggestion.data.area_type)
                } else {
                    app.$input.district.val('')
                    app.$input.district_type.val('')
                }

                if (!!suggestion.data.settlement) {
                    app.$input.locality.val(suggestion.data.settlement)
                    app.$input.locality_type.val(suggestion.data.settlement_type);
                } else {
                    app.$input.locality.val('');
                    app.$input.locality_type.val('');
                }


                if (!!suggestion.data.settlement) {
                    app.$input.city.val((!!suggestion.data.city ? suggestion.data.city + ' ' : '') + suggestion.data.settlement);
                    app.$input.city_type.val(suggestion.data.settlement_type);
                    app.$input.city_real.val((!!suggestion.data.city ? suggestion.data.city : ''))

                    app.$input.locality.val(suggestion.data.settlement)
                    app.$input.locality_type.val(suggestion.data.settlement_type);

                    app.kladr.city = suggestion.data.settlement_kladr_id;
                } else {
                    app.$input.city.val(suggestion.data.city)
                    app.$input.city_real.val(suggestion.data.city)
                    app.$input.city_type.val(suggestion.data.city_type);

                    app.$input.locality.val('')
                    app.$input.locality_type.val('');

                    app.kladr.city = suggestion.data.city_kladr_id;
                }

                app.$input.house.removeAttr('readonly');
                app.$input.house.autocomplete('dispose');

                app.$input.index.val(suggestion.data.postal_code);
                app.$input.street.removeAttr('readonly');
                app.$input.street.autocomplete('dispose');
                _init_autocomplete_street();

                app.$input.street.focus();

                if (app.debug)
                    console.info('city', suggestion);
            },
            formatResult: function (suggestion, currentValue) {
                return suggestion.value;
            }
        });

    };

    var _init_autocomplete_street = function () {

        app.$input.street.autocomplete({
            serviceUrl: function () {
                return 'ajax/dadata.php?action=street&city=' + app.kladr.city
            },
            minChars: 3,
            noCache: true,
            onSelect: function (suggestion) {

                app.$input.street.val(suggestion.data.street)
                app.$input.street_type.val(suggestion.data.street_type);

                app.kladr.street = suggestion.data.street_kladr_id;

                app.$input.index.val(suggestion.data.postal_code);
                app.$input.house.removeAttr('readonly');
                app.$input.house.autocomplete('dispose');
                _init_autocomplete_house();

                app.$input.house.focus();

                if (app.debug)
                    console.info('street', suggestion);
            },
            formatResult: function (suggestion, currentValue) {
                return suggestion.value;
            }
        });
    };

    var _init_autocomplete_house = function () {

        app.$input.house.autocomplete({
            serviceUrl: function () {
                return 'ajax/dadata.php?action=house&street=' + app.kladr.street
            },
            minChars: 1,
            noCache: true,
            onSelect: function (suggestion) {

                app.$input.index.val(suggestion.data.postal_code);
                app.$input.house.val(suggestion.data.house)
                app.$input.building.removeAttr('readonly');
                app.$input.room.removeAttr('readonly');

                if (!!suggestion.data.block) {
                    app.$input.building.val(suggestion.data.block);
                    app.$input.room.focus();
                } else {
                    app.$input.building.focus();
                }


                if (app.debug)
                    console.info('house', suggestion);
            },
            formatResult: function (suggestion, currentValue) {
                return suggestion.value;
            }
        });

    };

    ;(function () {
        _init();

        _init_autocomplete_region();
        _init_autocomplete_city();
        _init_autocomplete_street();

        _init_change_region();
        _init_change_city();
        _init_change_street();
        _init_change_house();
        _init_change_building();

    })();
};


function DadataWorkApp($block) {
    var app = this;
    app.$block = $block;

    app.debug = true;

    app.$input = {};
    app.kladr = {};

    var _init = function () {

        app.$input.company = app.$block.find('.js-dadata-company');
        app.$input.company_address = app.$block.find('.js-dadata-company-address');
        app.$input.phone = app.$block.find('.js-dadata-phone');
        app.$input.chief_name = app.$block.find('.js-dadata-chief-name');
        app.$input.chief_position = app.$block.find('.js-dadata-chief-position');
        app.$input.chief_phone = app.$block.find('.js-dadata-chief-phone');

    };


    var _init_autocomplete_company = function () {
        app.$input.company.autocomplete({
            serviceUrl: 'ajax/dadata.php?action=inn',
            minChars: 3,
            noCache: true,
            onSelect: function (suggestion) {

                if (app.debug)
                    console.info('company', suggestion);

                app.$input.company.val(suggestion.unrestricted_value)
                app.$input.company_address.val(suggestion.data.address.value)
                console.info(app.$input.company_address);
                console.info(suggestion.data.address.value)
                if (!!suggestion.data.management) {
                    if (!!suggestion.data.management.name)
                        app.$input.chief_name.val(suggestion.data.management.name).next('span').addClass('-top');
                    if (!!suggestion.data.management.post)
                        app.$input.chief_position.val(suggestion.data.management.post).next('span').addClass('-top');
                }
                if (!!suggestion.data.phones)
                    app.$input.phone.val(suggestion.data.phones[0]).next('span').addClass('-top');

            },
            formatResult: function (item, short_value) {
                var _block = '';
                var c = "(" + short_value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&") + ")";
                var item_value = item.value.replace(RegExp(c, "gi"), "<strong>$1</strong>")

                _block += '<span>' + item_value + '</span>';
                _block += '<small>' + item.data.address.value + '</small>';

                return _block;

            }
        });
    };

    ;(function () {
        _init();

        _init_autocomplete_company();

    })();
};


$(function () {
    if ($('.js-dadata-address').length > 0) {
        $('.js-dadata-address').each(function () {
            new DadataAddressApp($(this));
        });
    }
    if ($('.js-dadata-work').length > 0) {
        $('.js-dadata-work').each(function () {
            new DadataWorkApp($(this));
        });
    }
});

