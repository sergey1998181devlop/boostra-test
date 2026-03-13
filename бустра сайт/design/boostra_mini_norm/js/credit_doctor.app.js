function CreditDoctorApp() {
    var app = this;
    app.form_wrapper = $('.credit-doctor-form');
    app.form = $('.credit-doctor-form > form');

    app.init = function () {
        app.init_local_time();
        app.init_form_on_submit();
    };

    app.init_local_time = function() {
        var local_time = Math.floor((new Date()).getTime() / 1000);
        $('#local_time').val(local_time);
    };

    app.init_form_on_submit = function () {
        app.form.submit(function(e) {
            e.preventDefault();

            app.form_wrapper.html('<div class="fancybox-loading"><div></div></div>');

            $.post('/user', app.form.serialize(), function(data) {
                app.form_wrapper.html(data);
                setTimeout(app.init_mask, 200);
            });
        })
    };

    app.init_mask = function () {
        let $code_field = $('[name="credit_doctor_sms"]');
        $code_field.inputmask({
            mask: "9999",
            oncomplete: function() {
                $.post('ajax/sms.php?action=check_credit_doctor_sms', {
                    'code': $code_field.val()
                }, function (answer) {
                    if (answer.success === 0) {
                        $('.sms-code-error').show()
                        return;
                    }

                    var $credit_doctor_content = $('.credit-doctor-content');
                    $.post('/user?action=credit_doctor_accepted', {
                        'order_id': $credit_doctor_content.data('id')
                    });

                    $credit_doctor_content.html('<p>Поздравляем вас с подпиской на сервис "Кредитный доктор"!</p>');
                })
            }
        });
    };

    (function () {
        app.init();
    })();
}

$(function () {
    new CreditDoctorApp();
});