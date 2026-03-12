/**
 * Address Form Handler
 * Использует тот же autocomplete что и в additional_data.app.js
 * Version: 2.1.0
 */

;function AddressDataApp() {
    var app = this;

    app.$form;
    app.validator;

    app.init = function() {
        app.$form = $('#personal_data');

        // Инициализация автокомплита для адресов
        app.init_address_autocomplete();

        // Обработка чекбокса "Адреса совпадают"
        app.init_equal_checkbox();

        // Валидация формы
        app.init_validator();
    };

    /**
     * Инициализация автокомплита для адресов через DaData
     */
    app.init_address_autocomplete = function() {

        // Автокомплит для адреса регистрации
        $('#registration_address_full').autocomplete({
            serviceUrl: 'ajax/dadata.php?action=full_address',
            minChars: 3,
            onSelect: function(item) {
                app.fillRegistrationAddress(item);
            },
            formatResult: function(item, short_value) {
                var c = "(" + short_value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&") + ")";
                var item_value = item.unrestricted_value.replace(RegExp(c, "gi"), "<strong>$1</strong>");
                return '<span>' + item_value + '</span>';
            }
        });

        // Автокомплит для адреса проживания
        $('#residence_address_full').autocomplete({
            serviceUrl: 'ajax/dadata.php?action=full_address',
            minChars: 3,
            onSelect: function(item) {
                app.fillResidenceAddress(item);
            },
            formatResult: function(item, short_value) {
                var c = "(" + short_value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&") + ")";
                var item_value = item.unrestricted_value.replace(RegExp(c, "gi"), "<strong>$1</strong>");
                return '<span>' + item_value + '</span>';
            }
        });
    };

    /**
     * Заполнение полей адреса регистрации
     */
    app.fillRegistrationAddress = function(item) {
        var data = item.data;

        // Заполняем видимое поле
        $('#registration_address_full').val(item.unrestricted_value);

        // Заполняем скрытые поля
        $('#Regindex').val(data.postal_code || '');
        $('#Regregion').val(data.region_with_type || data.region || '').removeData('fias_id');
        $('#Regcity').val(app.getCity(data) || '').removeData('fias_id');
        $('#Regstreet').val(data.street_with_type || data.street || '').removeData('fias_id');
        $('#Reghousing').val(data.house || '').removeData('fias_id');
        $('#Regbuilding').val(data.block || '');
        $('#Regroom').val(data.flat || '').removeData('fias_id');

        // Короткие типы
        $('#Regregion_shorttype').val(data.region_type || '');
        $('#Regcity_shorttype').val(data.city_type || data.settlement_type || '');
        $('#Regstreet_shorttype').val(data.street_type || '');

        // Дополнительные данные
        $('#prop_okato').val(data.okato || '');
        $('#prop_city_type').val(data.city_type_full || data.settlement_type_full || '');
        $('#prop_street_type_long').val(data.street_type_full || '');
        $('#prop_street_type_short').val(data.street_type || '');

        // Убираем класс ошибки если был
        $('#registration_address_full').removeClass('error').parent().removeClass('error');
    };

    /**
     * Заполнение полей адреса проживания
     */
    app.fillResidenceAddress = function(item) {
        var data = item.data;

        // Заполняем видимое поле
        $('#residence_address_full').val(item.unrestricted_value);

        // Заполняем скрытые поля
        $('#Faktindex').val(data.postal_code || '');
        $('#Faktregion').val(data.region_with_type || data.region || '').removeData('fias_id');
        $('#Faktcity').val(app.getCity(data) || '').removeData('fias_id');
        $('#Faktstreet').val(data.street_with_type || data.street || '').removeData('fias_id');
        $('#Fakthousing').val(data.house || '').removeData('fias_id');
        $('#Faktbuilding').val(data.block || '');
        $('#Faktroom').val(data.flat || '').removeData('fias_id');

        // Короткие типы
        $('#Faktregion_shorttype').val(data.region_type || '');
        $('#Faktcity_shorttype').val(data.city_type || data.settlement_type || '');
        $('#Faktstreet_shorttype').val(data.street_type || '');

        // Дополнительные данные
        $('#prog_okato').val(data.okato || '');
        $('#prog_city_type').val(data.city_type_full || data.settlement_type_full || '');
        $('#prog_street_type_long').val(data.street_type_full || '');
        $('#prog_street_type_short').val(data.street_type || '');

        // Убираем класс ошибки если был
        $('#residence_address_full').removeClass('error').parent().removeClass('error');
    };

    /**
     * Получение города из данных DaData
     */
    app.getCity = function(data) {
        return data.city || data.settlement || data.area || '';
    };

    /**
     * Обработчик чекбокса "Адреса совпадают"
     */
    app.init_equal_checkbox = function() {
        $('#equal').on('change', function() {
            if ($(this).is(':checked')) {
                $('#living_block').slideUp(300);
                app.copyRegistrationToResidence();
            } else {
                $('#living_block').slideDown(300);
            }
        });

        // Проверка при загрузке
        if ($('#equal').is(':checked')) {
            $('#living_block').hide();
        }
    };

    /**
     * Копирование адреса регистрации в адрес проживания
     */
    app.copyRegistrationToResidence = function() {
        // Копируем видимое поле
        $('#residence_address_full').val($('#registration_address_full').val());

        // Копируем скрытые поля
        $('#Faktindex').val($('#Regindex').val());
        $('#Faktregion').val($('#Regregion').val());
        $('#Faktcity').val($('#Regcity').val());
        $('#Faktstreet').val($('#Regstreet').val());
        $('#Fakthousing').val($('#Reghousing').val());
        $('#Faktbuilding').val($('#Regbuilding').val());
        $('#Faktroom').val($('#Regroom').val());

        $('#Faktregion_shorttype').val($('#Regregion_shorttype').val());
        $('#Faktcity_shorttype').val($('#Regcity_shorttype').val());
        $('#Faktstreet_shorttype').val($('#Regstreet_shorttype').val());

        $('#prog_okato').val($('#prop_okato').val());
        $('#prog_city_type').val($('#prop_city_type').val());
        $('#prog_street_type_long').val($('#prop_street_type_long').val());
        $('#prog_street_type_short').val($('#prop_street_type_short').val());
    };

    /**
     * Валидация формы
     */
    app.init_validator = function() {
        app.validator = app.$form.validate({
            errorElement: "span",
            rules: {
                "registration_address_full": {
                    required: true,
                    minlength: 10
                },
                "residence_address_full": {
                    required: function() {
                        return !$('#equal').is(':checked');
                    },
                    minlength: 10
                }
            },
            messages: {
                "registration_address_full": {
                    required: "Укажите адрес регистрации",
                    minlength: "Выберите адрес из списка подсказок"
                },
                "residence_address_full": {
                    required: "Укажите адрес проживания",
                    minlength: "Выберите адрес из списка подсказок"
                }
            },
            submitHandler: function(form) {
                // Проверяем что скрытые поля заполнены
                var isValid = true;
                var errorMessage = '';

                // Проверка адреса регистрации
                if (!$('#Regregion').val() || !$('#Regcity').val()) {
                    isValid = false;
                    errorMessage = 'Пожалуйста, выберите полный адрес регистрации из списка подсказок';
                    $('#registration_address_full').addClass('error').parent().addClass('error');
                }

                // Проверка адреса проживания (если не совпадает)
                if (!$('#equal').is(':checked')) {
                    if (!$('#Faktregion').val() || !$('#Faktcity').val()) {
                        isValid = false;
                        errorMessage = 'Пожалуйста, выберите полный адрес проживания из списка подсказок';
                        $('#residence_address_full').addClass('error').parent().addClass('error');
                    }
                }

                if (!isValid) {
                    alert(errorMessage);
                    return false;
                }

                // Если всё ок - отправляем форму
                app.$form.addClass('loading');
                form.submit();
            }
        });
    };

    // Инициализация при создании объекта
    ;(function() {
        app.init();
    })();
}

/**
 * Глобальные функции для очистки адресов
 */
window.clearRegistrationAddress = function() {
    $('#registration_address_full').val('');
    $('#Regindex, #Regregion, #Regcity, #Regstreet, #Reghousing, #Regbuilding, #Regroom').val('');
    $('#Regregion_shorttype, #Regcity_shorttype, #Regstreet_shorttype').val('');
    $('#prop_okato, #prop_city_type, #prop_street_type_long, #prop_street_type_short').val('');
};

window.clearResidenceAddress = function() {
    $('#residence_address_full').val('');
    $('#Faktindex, #Faktregion, #Faktcity, #Faktstreet, #Fakthousing, #Faktbuilding, #Faktroom').val('');
    $('#Faktregion_shorttype, #Faktcity_shorttype, #Faktstreet_shorttype').val('');
    $('#prog_okato, #prog_city_type, #prog_street_type_long, #prog_street_type_short').val('');
};

// Инициализация при загрузке страницы
$(function() {
    if ($('#personal_data').length > 0 && $('#personal_data').find('#registration_address_full').length > 0) {
        new AddressDataApp();
    }
});