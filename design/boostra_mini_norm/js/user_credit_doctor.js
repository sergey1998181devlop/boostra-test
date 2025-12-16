const userCreditDoctor = new Object({});
userCreditDoctor.data = new Object({});
userCreditDoctor.step = 0;
userCreditDoctor.DEFAULT_SMS_DELAY_SECONDS = 30;
userCreditDoctor.sms_timer_second = 0;

userCreditDoctor.navigateLinks = $("#survey-slider .survey-navigate li");
userCreditDoctor.sliderContents = $("#survey-slider [data-slide_content]");

userCreditDoctor.footerLinks = $("#survey-footer button");
userCreditDoctor.buttonGoToReviewOrder = $('.survey-people button');
userCreditDoctor.fieldsInput = $("#survey-wrapper input[type='radio']");

userCreditDoctor.fieldsModalOrderInput = $("#modal_survey [name='order_item']");
userCreditDoctor.sliderModalWrappers = $("#modal_survey [data-modal_slide]");

userCreditDoctor.modalPrevButton = $(".modal-survey-prev-btn");
userCreditDoctor.modalCloseButton = $(".modal-survey-close-btn");
userCreditDoctor.modalGetFreeLessonButton = $("#survey-get-free-lesson");
userCreditDoctor.goToPay = $(".survey-go-pay");
userCreditDoctor.payOtherCardButton = $(".survey-card-list button");

userCreditDoctor.survey_amount = {
    wrapper: $('.survey-ui'),
    slider: Object({}),
};

userCreditDoctor.removePreloader = function () {
    $('body').removeClass('loading');
}

userCreditDoctor.setPreloader = function () {
    $('body').addClass('loading');
}

userCreditDoctor.initCheckedProps = function (elements) {
    elements.each(function () {
        let key = $(this).attr('name'),
            value = $(this).val();

        if (userCreditDoctor.data[key] && userCreditDoctor.data[key] === value) {
            $(this).prop('checked', true);
        }
    });
}

userCreditDoctor.init = function () {
    $("[name='survey_amount']").ionRangeSlider({
        type: "single",
        min: 10000,
        max: 100000,
        step: 500,
        postfix: " ₽",
        hide_min_max: true,
        force_edges: true,
        onChange: function (data) {
            let key = data.input.attr('name'),
                value = data.from;
            userCreditDoctor.changeValue(value, key);
        },
        onStart: function (data) {
            userCreditDoctor.survey_amount.wrapper.prepend('<span>' + data.min + '</span>');
            userCreditDoctor.survey_amount.wrapper.append('<span>' + data.max + '</span>');
        },
    });

    let data = localStorage.getItem('boostra_survey_data');
    userCreditDoctor.survey_amount.slider = $("[name='survey_amount']").data("ionRangeSlider");

    if (data) {
        userCreditDoctor.data = JSON.parse(data);
        userCreditDoctor.initCheckedProps(userCreditDoctor.fieldsInput);

        if (userCreditDoctor.data['survey_amount']) {
            userCreditDoctor.survey_amount.slider.update({
                from: userCreditDoctor.data['survey_amount']
            });
        }

        userCreditDoctor.changeStep(userCreditDoctor.data['step'] || 0);
    } else {
        // если первый вход отправим событие метрики
        sendMetric('reachGoal', 'cd_go_to_slide_1');

        // дефолтное значение в сессию из слайдера чтобы не заставлять пользователя дергать слайдер первоначально
        userCreditDoctor.changeValue(userCreditDoctor.survey_amount.slider.options.min, 'survey_amount');
    }

    // если финальное окно выполним подгрузку блоков
    let url = new URL(window.location.href),
        user_action = url.searchParams.get("user_action"),
        order_id = url.searchParams.get("order_id");

    if (userCreditDoctor.data['error'] || user_action === 'getPayment') {
        userCreditDoctor.initModal();
        userCreditDoctor.setPreloader();
        $("#success-container").empty();
        userCreditDoctor.changeModalStep(4, 0);

        // если передан GET параметр проверим платеж на бекенде
        if (user_action === 'getPayment' && order_id) {
            $.ajax({
                url: '/user/credit_doctor?action=getPayment&order_id=' + order_id,
                success: function (resp) {
                    if (resp.success) {
                        $("#modal_survey").addClass('success');
                        userCreditDoctor.deleteValue('error');
                    } else {
                        $("#modal_survey").removeClass('success');
                        userCreditDoctor.changeValue(true, 'error');
                    }

                    $("#success-container").html(resp.message);
                }
            }).done(function () {
                userCreditDoctor.removePreloader();
            });
        } else if (userCreditDoctor.data['error']) {
            $("#success-container").load('/user/credit_doctor?action=getErrorBlock', function () {
                userCreditDoctor.removePreloader();
            });
        }
    }

    if (typeof userCreditDoctor.data['modal_step'] !== "undefined") {
        userCreditDoctor.initModal();

        // если страница выбора карт подгрузим их
        if (userCreditDoctor.data['modal_step'] === 3) {
            userCreditDoctor.init_sms_block();
            /*userCreditDoctor.setPreloader();
            $("#cards_list").load('/user/credit_doctor?action=getUserCards', function () {
                userCreditDoctor.removePreloader();

                // установим событие на кнопку "Оплатить с новой карты", если смс не подгружено подгрузим этот блок
                userCreditDoctor.card_other_field = $("#card_other");
                userCreditDoctor.card_other_field.on('change', function () {
                    if ($("#sms_block").html() === ""){
                        userCreditDoctor.init_sms_block();
                    }
                });
            });*/
        }
    } else {
        userCreditDoctor.removePreloader();
    }
}

userCreditDoctor.changeValue = function (value, key) {
    userCreditDoctor.data[key] = value;
    localStorage.setItem('boostra_survey_data', JSON.stringify(userCreditDoctor.data));
};

userCreditDoctor.deleteValue = function (key) {
    delete userCreditDoctor.data[key];
    localStorage.setItem('boostra_survey_data', JSON.stringify(userCreditDoctor.data));
}

userCreditDoctor.changeStep = function (index) {
    if (index < userCreditDoctor.navigateLinks.length) {
        sendMetric('reachGoal', 'cd_go_to_slide_' + (index + 1));
    }

    // проверка выбора пункта на текущем слайде
    if (index > 1 && !userCreditDoctor.sliderContents.eq(index - 1).find("input:checked").length) {
        return;
    }

    userCreditDoctor.navigateLinks.removeClass('active');
    userCreditDoctor.navigateLinks.eq(index).addClass('active');

    userCreditDoctor.sliderContents.removeClass('active');
    userCreditDoctor.sliderContents.eq(index).addClass('active');

    userCreditDoctor.footerLinks.prop('disabled', false);
    userCreditDoctor.footerLinks.eq(1).removeClass('finish').text('Далее');

    if (index === 0) {
        userCreditDoctor.footerLinks.eq(0).prop('disabled', true);
    } else if ((index + 1) === userCreditDoctor.navigateLinks.length) {
        userCreditDoctor.footerLinks.eq(1).addClass('finish').text('Завершить');
    }

    userCreditDoctor.step = index;
    userCreditDoctor.changeValue(userCreditDoctor.step, 'step');
};

userCreditDoctor.navigateLinks.on('click', function () {
    let active_index = $(this).index();
    userCreditDoctor.changeStep(active_index);
});

userCreditDoctor.closeModal = function () {
    let hasSuccess = $("#modal_survey").hasClass('success');

    sendMetric('reachGoal', 'cd_modal_close');
    userCreditDoctor.changeModalStep(0);
    userCreditDoctor.deleteValue('modal_step');
    userCreditDoctor.deleteValue('modal_index_content');
    userCreditDoctor.deleteValue('order_item');
    userCreditDoctor.deleteValue('error');
    $("#modal_survey").removeClass('after_select_price success');

    /*if (hasSuccess) {*/
        userCreditDoctor.setPreloader();
        window.location = '/user/login';
    /*}*/
}

userCreditDoctor.initModal = function () {
    userCreditDoctor.generateDataFinalForModal();

    // если есть данные о модальном окне, то инициализируем
    if (typeof userCreditDoctor.data['modal_step'] !== "undefined") {
        userCreditDoctor.changeModalStep(userCreditDoctor.data['modal_step'], userCreditDoctor.data['modal_index_content'] || 0);
        userCreditDoctor.initCheckedProps(userCreditDoctor.fieldsModalOrderInput);

        if (userCreditDoctor.data['modal_step'] > 0) {
            $("#modal_survey").addClass('after_select_price');
        }
    } else {
        //sendMetric('reachGoal', 'cd_modal_0.0');

        // инициализируем сразу самую дорогую услугу
        userCreditDoctor.hideSurveyTopBtn(false, true);
        userCreditDoctor.changeValue( 2, 'order_item');
        userCreditDoctor.initCheckedProps(userCreditDoctor.fieldsModalOrderInput);
        userCreditDoctor.changeModalStep(1, 2);
    }

    $.magnificPopup.open({
        items: {
            src: '#modal_survey'
        },
        type: 'inline',
        modal: true,
        showCloseBtn: true,
        closeOnBgClick: false,
        closeMarkup: '<button title="%title%" type="button" class="mfp-close">Закрыть &#215;</button>',
        callbacks: {
            close: function () {
                userCreditDoctor.closeModal();
            }
        }
    });
}

userCreditDoctor.generateDataFinalForModal = function () {
    $('#quiz_level').text(userCreditDoctor.data['survey_amount']);
    $('#quiz_amount').text(userCreditDoctor.data['survey_amount'] * 1.96);
}

userCreditDoctor.footerLinks.on('click', function () {
    if ($(this).index() === 0) {
        userCreditDoctor.step--;
    } else {
        // проверка на заполненость чекбоксов и полей с исключением первого шага
        if (userCreditDoctor.step === 0 || (userCreditDoctor.step < (userCreditDoctor.navigateLinks.length - 1) && (userCreditDoctor.sliderContents.filter('.active').find("input:checked").length))) {
            userCreditDoctor.step++;
        } else if ($(this).hasClass('finish') && userCreditDoctor.sliderContents.filter('.active').find("input:checked").length) {
            sendMetric('reachGoal', 'cd_click_quiz_finish');

            // если последний шаг инициализируем модальное окно
            $.ajax({
                url: '/user/credit_doctor?action=addFormData',
                data: {
                    survey_amount: userCreditDoctor.data['survey_amount'],
                    count_take_money: userCreditDoctor.data['count_take_money'],
                    count_calls: userCreditDoctor.data['count_calls'],
                    has_credit: userCreditDoctor.data['has_credit'],
                },
                type: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    userCreditDoctor.setPreloader();
                },
                success: function (resp) {
                    if (resp.result) {
                        userCreditDoctor.initModal();
                    }
                }
            }).done(function () {
                userCreditDoctor.removePreloader();
            });
        }
    }
    userCreditDoctor.changeStep(userCreditDoctor.step);
});

userCreditDoctor.fieldsInput.on('change', function () {
    let value = $(this).val(),
        key = $(this).attr('name');
    userCreditDoctor.changeValue(value, key);
});

userCreditDoctor.prevModalStep = function () {
    userCreditDoctor.data['modal_step']--;
    userCreditDoctor.changeModalStep(userCreditDoctor.data['modal_step'], 0);
};

userCreditDoctor.changeModalStep = function (index, indexContent = 0) {
    if (index !== 4) {
        $("#modal_survey").removeClass('success');
        userCreditDoctor.deleteValue('error');
    } else {
        userCreditDoctor.hideSurveyTopBtn(true, false)
    }

    sendMetric('reachGoal', 'cd_modal_' + index + '.' + indexContent);

    userCreditDoctor.changeValue(index, 'modal_step');
    userCreditDoctor.changeValue(indexContent, 'modal_index_content');

    userCreditDoctor.sliderModalWrappers.removeClass('active');
    userCreditDoctor.sliderModalWrappers.eq(index).addClass('active');

    userCreditDoctor.sliderModalWrappers.eq(index).find("[data-content]").removeClass('active');
    userCreditDoctor.sliderModalWrappers.eq(index).find("[data-content]").eq(indexContent).addClass('active')

    if (index > 0) {
        $("#modal_survey").addClass('after_select_price');
    } else {
        userCreditDoctor.showSurveyTopBtn();
        $("#modal_survey").removeClass('after_select_price success');
    }
};

userCreditDoctor.hideSurveyTopBtn = function (prev = true, close = true) {
    if (prev) {
        userCreditDoctor.modalPrevButton.hide();
    }

    if (close) {
        userCreditDoctor.modalCloseButton.hide();
    }
};

userCreditDoctor.showSurveyTopBtn = function () {
    userCreditDoctor.modalPrevButton.css("display", "");
    userCreditDoctor.modalCloseButton.css("display", "");
};

userCreditDoctor.fieldsModalOrderInput.on('change click', function () {
    let value = $(this).val(),
        key = $(this).attr('name');
    userCreditDoctor.changeValue(value, key);

    // добавим параметр к кнопке при переходе к услугам
    let index = $(this).val() - 1;
    userCreditDoctor.buttonGoToReviewOrder.data('content_id', index);
    userCreditDoctor.changeModalStep(1);
});

userCreditDoctor.modalPrevButton.on('click', function () {
    userCreditDoctor.prevModalStep();
});

userCreditDoctor.modalCloseButton.on('click', function () {
    $.magnificPopup.close();
    /*if (([2, 4]).includes(userCreditDoctor.data['modal_step'])) {
        $.magnificPopup.close();
    } else {
        // если мы не слайде повторной заявке, то покажем повторное мотивирующие предложение
        userCreditDoctor.changeModalStep(2, 0);
    }*/
});

$(".repeat_order button").on('click', function () {
    userCreditDoctor.changeModalStep(2, 1);
});

$(document).on('click', '[data-go_to]', function () {
   const modal_slide_id = $(this).data('go_to');
   const content_id = $(this).data('content_id') || 0;

   userCreditDoctor.changeModalStep(modal_slide_id, content_id);
});

// подгружаем блок с доступными картами для оплаты
$(document).on('click', '.survey-get-pay', function () {
    let order_item_index = $(this).data('order_item_index');
    if (typeof order_item_index !== "undefined") {
        // если есть атрибут предложения установим его
        userCreditDoctor.fieldsModalOrderInput.eq(order_item_index).trigger('change').prop('checked', true);
    }
    userCreditDoctor.showSurveyTopBtn();

    //отобразим кнопку "Оплатить услугу" и очистим содержимое блока смс ввода

    // Т.к. оплата без Тинька сразу переходим к блоку оплаты с СМС
    userCreditDoctor.changeModalStep(3, 0);
    userCreditDoctor.init_sms_block();

    // отключили подгрузку т.к. нет необходимости подгружать карты
    /*userCreditDoctor.goToPay.show();
    $("#sms_block").empty();

    // подгрузим карты пользователя
    userCreditDoctor.setPreloader();
    $("#cards_list").load('/user/credit_doctor?action=getUserCards', function () {
        userCreditDoctor.removePreloader();
        userCreditDoctor.changeModalStep(3, 0);

        // установим событие на кнопку "Оплатить с новой карты", если смс не подгружено подгрузим этот блок
        userCreditDoctor.card_other_field = $("#card_other");
        userCreditDoctor.card_other_field.on('change', function () {
            if ($("#sms_block").html() === ""){
                userCreditDoctor.init_sms_block();
            }
        });
    });*/
});

// функция таймера отправки смс
userCreditDoctor.init_sms_timer = function (seconds) {
    userCreditDoctor.sms_timer_second = seconds;
    userCreditDoctor.sms_repeat_button.hide();
    userCreditDoctor.SMSTimerField.show();

    userCreditDoctor.sms_timer = setInterval(function () {
        if (userCreditDoctor.sms_timer_second === 0) {
            userCreditDoctor.delete_sms_timer();
            userCreditDoctor.sms_repeat_button.show();
        } else {
            userCreditDoctor.SMSTimerField.text(userCreditDoctor.sms_timer_second);
        }
        userCreditDoctor.sms_timer_second--;
    }, 1000);
};

// выключение таймера и снятие блокировок
userCreditDoctor.delete_sms_timer = function () {
    clearInterval(userCreditDoctor.sms_timer);
    userCreditDoctor.sms_repeat_button.show();
    userCreditDoctor.SMSTimerField.hide();
};

userCreditDoctor.sendSms = function () {
    $.ajax({
        url: 'ajax/sms.php',
        data: {
            action: 'user_credit_doctor_send',
        },
        dataType: 'json',
        beforeSend: function () {
            $(userCreditDoctor.sms_code_field).closest('.sms_code_wrapper').removeClass('has-error has-success');
            userCreditDoctor.init_sms_timer(userCreditDoctor.DEFAULT_SMS_DELAY_SECONDS);
            userCreditDoctor.setPreloader();
        },
        success: function (resp) {
            if (resp['error']) {
                if (resp['time_left']) {
                    userCreditDoctor.delete_sms_timer();
                    userCreditDoctor.init_sms_timer(resp['time_left']);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    }).done(function () {
        userCreditDoctor.removePreloader();
    });
};

// проверка СМС
userCreditDoctor.validate_sms_code = function () {
    let sms_code = userCreditDoctor.sms_code_field .val(),
        $sms_code_wrapper = $(userCreditDoctor.sms_code_field).closest('.sms_code_wrapper');

    $sms_code_wrapper.removeClass('has-error has-success');

    $.ajax({
        url: 'ajax/sms.php?action=user_credit_doctor_check',
        data: {
            code: sms_code,
        },
        type: 'POST',
        success: function (resp) {
            if (resp.success) {
                $sms_code_wrapper.addClass('has-success');
                userCreditDoctor.access_sms_button.prop('disabled', false);
            } else {
                $sms_code_wrapper.addClass('has-error');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
};

// добавляет оплату в БД и получает ссылку на оплату
userCreditDoctor.getPaymentLink = function () {
    $.ajax({
        url: '/user/credit_doctor?action=addPayment',
        data: {
            order_type_id: userCreditDoctor.data['order_item'],
        },
        type: 'POST',
        dataType: 'json',
        beforeSend: function () {
            userCreditDoctor.changeModalStep(4, 0);
        },
        success: function (resp) {
            if (!resp['redirect_url']) {
                $("#success-container").load('/user/credit_doctor?action=getErrorBlock');
                userCreditDoctor.changeValue(true, 'error');
                userCreditDoctor.removePreloader();
            } else {
                sendMetric('reachGoal', 'cd_go_to_payment');
                $.magnificPopup.close();
                window.location = resp['redirect_url'];
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            userCreditDoctor.changeValue(true, 'error');
            alert(error);
            console.log(error);
        },
    });
}

// подгрузка блока с вводом СМС кода
userCreditDoctor.init_sms_block = function () {
    userCreditDoctor.goToPay.hide();
    userCreditDoctor.setPreloader();
    $("#sms_block").load('/user/credit_doctor?action=getSmsBlock', function () {
        userCreditDoctor.removePreloader();
        userCreditDoctor.sms_code_field = $("input[name='sms_code']");
        userCreditDoctor.sms_code_field.inputmask({
            mask: "9999",
            oncomplete: function () {
                userCreditDoctor.validate_sms_code();
            }
        });
        userCreditDoctor.SMSTimerField = $("#sms-timer");

        userCreditDoctor.sms_repeat_button = $("#sms-repeat");
        userCreditDoctor.sms_repeat_button.on('click', function (e) {
            e.preventDefault();
            userCreditDoctor.sendSms();
        })

        userCreditDoctor.access_sms_button = $("#access_sms");
        userCreditDoctor.access_sms_button.on('click', function () {
            sendMetric('reachGoal', 'cd_btn_get_payment');
            userCreditDoctor.setPreloader();

            let promise = $.ajax({
                url: '/user/credit_doctor?action=validateSmsForm',
                data: $(".sms-content input").serialize(),
                type: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $(".sms-content .error").remove();
                    $(".sms-content label").removeClass('has-error');
                },
                success: function (resp) {
                    if (resp['errors']) {
                        $(".sms-content").append("<p class='error'>" + resp['errors_message'] + "</p>");
                        $.each(resp['errors'], function(i, v) {
                            $(".sms-content label[for='" + v + "']").addClass('has-error');
                        });

                        userCreditDoctor.removePreloader();
                    } else {
                        userCreditDoctor.getPaymentLink();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                }
            });
        });

        userCreditDoctor.sendSms();
    });
};

// подгружаем блок для ввода СМС кода (подписание АСП)
userCreditDoctor.goToPay.on('click', function () {
    userCreditDoctor.init_sms_block();
});

$(document).on('click', '.form-email button', function () {
    let button = this;
    $.ajax({
        url: '/user/credit_doctor?action=sendFinishEmail',
        data: {
            email: $(button).closest('.form-email').find('input[type="email"]').val(),
        },
        type: 'POST',
        dataType: 'json',
        beforeSend: function () {
            userCreditDoctor.setPreloader();
            $(button).closest('[data-content]').removeClass('has-error').find('.error').remove();
        },
        success: function (resp) {
            if (resp['error']) {
                $(button).closest('[data-content]').addClass('has-error');
                $(button).closest('[data-content]').find('input[type="email"]').after("<p class='error'>" + resp['error'] + "</p>");
            }

            if (resp['success']) {
                $(button).closest('[data-content]').empty().html("<p>" + resp['message'] + "</p>");
            }
        }
    }).done(function () {
        userCreditDoctor.removePreloader();
    });
});

userCreditDoctor.modalGetFreeLessonButton.on('click', function () {
    let button = this;
    $.ajax({
        url: '/user/credit_doctor?action=getFreeLesson',
        data: {
            email: $('[name="survey_email"]').val(),
        },
        type: 'POST',
        dataType: 'json',
        beforeSend: function () {
            userCreditDoctor.setPreloader();
            $(button).closest('[data-content]').removeClass('has-error').find('.error').remove();
        },
        success: function (resp) {
            if (resp['error']) {
                $(button).closest('[data-content]').addClass('has-error');
                $(button).closest('[data-content]').find('[name="survey_email"]').after("<p class='error'>" + resp['error'] + "</p>");
            }

            if (resp['success']) {
                $("#modal_survey").addClass('success');
                $(button).closest('[data-content]').empty().html("<p>" + resp['message'] + "</p>");
            }
        }
    }).done(function () {
        userCreditDoctor.removePreloader();
    });
});

$(document).ready(function () {
    userCreditDoctor.init();
})
