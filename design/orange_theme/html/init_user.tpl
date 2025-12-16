{* Страница инициализации пользователя *}

{* Канонический адрес страницы *}
{$canonical="/init_user" scope=parent}

{$meta_title = "Проверка логина" scope=parent}
<style>
    .animate-blink {
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0%, 100% {
            -webkit-box-shadow:0px 0px 15px 5px rgb(9 151 255);
            -moz-box-shadow: 0px 0px 15px 5px rgb(9 151 255);
            box-shadow: 0px 0px 15px 5px rgb(9 151 255);
        }

        50% {
            -webkit-box-shadow:0px 0px 0px 0px rgba(56,61,156,0);
            -moz-box-shadow: 0px 0px 0px 0px rgba(56,61,156,0);
            box-shadow: 0px 0px 0px 0px rgba(56,61,156,0);
        }
    }

    .max-h {
        min-height: 0px;
    }

    .auth-button-tinkoff {
        display: block;
        margin-top: 20px;
        background-color: #FFDD2D;
        color: black;
        text-decoration: none;
        padding: 10px 15px;
        border-radius: 5px;
        text-align: center;
        font-size: 16px;
        border: 1px solid #7c858d;
        height: 44px;
    }

    .form-check__label {
        color: #818C99;
        height: auto;
        margin-top: 30px;
        margin-bottom: 30px;
    }

    .agree-label > a {
        color: #818C99;
    }

    .agree-label > a:hover {
        color: #818C99;
    }

    #bolder {
        font-weight: bold;
        margin-bottom: 15% !important;
    }

    #agree {
        width: 16px;
        height: 16px;
        border-radius: 100%;
        border: 1px solid #818C99;
        margin-top: 5px;
        margin-left: -16px;
    }

    #agree.form-check-input.is-valid:checked {
        background-color: #0A91ED;
        border: none;
    }

    /* #agree.form-check-input.is-valid:focus {
        box-shadow: none;
    }

    #agree.form-check-input.is-invalid {
        box-shadow: none;
    } */

    .agree-label {
        margin-top: 0px;
        margin-left: 5px;
        font-size: 17px;
        display: inline;
    }

    .header {
        display: block;
        text-align: left;
        font-size: medium;
    }

    #esia_part_1 {
        color: rgb(0, 102, 179);
    }

    #esia_part_2 {
        color: rgb(238, 47, 83);
    }

    .tid-4PNRE-button {
        height: 40px;
        border: 1px solid #7c858d;
    }

    .tid-4PNRE-text-m {
        font-size: 16px;
        font-weight: bold;
    }

    @media(max-width: 767px) {
        #agree {
            width: 23px;
            height: 23px;
        }

        .agree-label {
            margin-top: 0px;
            font-size: 12px;
        }
    }

    .mini-checkbox #agree_virtual_card {
        height: 16px;
        width: 16px;
        min-width: 16px;
        margin-right: 5px;
    }

    .mini-checkbox {
        display: flex;
    }

    .init_title {
        font-weight: 700;
        font-size: clamp(20px, 16px + 0.952vw, 32px);
        margin: 0 0 32px;
    }

    #init_user .wrapper input:not([type="checkbox"]) {
        margin: 0 0 15px;
        padding: 20px;
        font-size: 16px;
        border: none;
        color: #000;
        border-radius: 5px;
        /* height: 34px; */
        background-color: #F4F7FA;
        border-radius: 16px;
        width: 100%;
    }

    #phone-submit-btn {
        width: fit-content;
        background-color: #0A91ED;
        padding: clamp(14px, 12px + 0.476vw, 20px) 50px;
        border-radius: 232px;
        transition: all .3s ease;
        border: none;
        margin: 0 0 0 auto;
    }

    #phone-submit-btn:hover {
        border: none;
        background-color: #038aee;
    }

    .mt-2 {
        color: #818C99;
    }

    .mt-2 a {
        color: #818C99;
    }

    .mt-2 a:hover {
        color: #818C99;
    }

    .auth__buttons__login {
        display: flex;
        gap: 12px;
        max-width: 445px;
        height: 50px;
    }

    .auth__buttons__login a {
        text-decoration: none;
        display: flex;
        width: 100%;
        height: 50px;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 12px;
        position: relative;
        border-radius: 120px;
        font-weight: bold;
        box-sizing: border-box;
        font-size: 16px;
        transition: all 250ms ease-in-out;
        color: #141E1F;
        background-color: #F0F8FF;
    }

    .auth__buttons__login a:hover {
        color: #141E1F;
        background-color: #c6e4ff;
        transform: translateY(-2px);
    }

    .mb-3 {
        margin-top: 32px;
    }

    #init_user-form {
        display: flex;
        flex-direction: column;
    }

    .input-group-text_code {
        font-weight: 500;
        background-color: transparent;
        border: none
    }

    .code_btns_wrapper {
        display: flex;
        gap: 31px;
        flex-wrap: wrap;
        max-width: 645px;
        min-height: 40px;
    }

    .code_btns_wrapper > button[type="submit"],
    .code_btns_wrapper > button[type="button"] {
        background-color: #F0F8FF;
        border: none;
        border-radius: 232px;
        color: #0A91ED;
        box-shadow: none;
        max-width: calc((100% - 32px) / 2);
        width: 100%;
        min-width: 245px;
    }

    @media (max-width: 600px) {
        .code_btns_wrapper {
            gap: 16px;
            flex-direction: column;
        }

        .code_btns_wrapper > button[type="submit"],
        .code_btns_wrapper > button[type="button"] {
            max-width: 100%;
        }
    }

    .code_btns_wrapper > button[type="submit"]:disabled,
    .code_btns_wrapper > button[type="button"]:disabled {
        background-color: #F4F7FA;
        border: none;
        border-radius: 232px;
        color: #818C99;
        box-shadow: none;
    }
</style>

<section id="init_user">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 wrapper">
        <h2 class="init_title">Начните регистрироваться и получите первый заём</h2>
            {if $t_id_error}
                <div class="alert alert-danger my-3" role="alert">
                    {$t_id_error}
                </div>
            {/if}
            {if $esia_id_error}
                <div class="alert alert-danger my-3" role="alert">
                    {$esia_id_error}
                </div>
            {/if}
            {if $t_bank_button_registration_access || $esia_button_registration_access}
                    <h4 class="mb-3 header">Войдите через Госуслуги или Tinkoff ID</h4>
					<div class="auth__buttons__login">
						{if $t_bank_button_registration_access}
							<a onclick="sendMetric('reachGoal', 'tid_auth')" class="tid_button" href="{$t_id_auth_url}">
								T-ID
								<img src="/design/boostra_mini_norm/assets/image/tinkoff-id-small.png" alt="" />
							</a>
						{/if}
						{if $esia_button_registration_access}
							<a onclick="sendMetric('reachGoal', 'gu_auth')" class="esia_button" href="{$esia_redirect_url}">
								Госуслуги
								<img class="" height="24" src="/design/boostra_mini_norm/assets/image/esia_logo.png" alt="" />
							</a>
						{/if}
					</div>
			{/if}
            {* <h4 class="mb-3 header">Подайте заявку через сервисы - это быстрее и вероятность одобрения выше!</h4>
            <h4 class="mb-3 header">Или подтвердите номер телефона по смс, заполнив заявку вручную</h4> *}
            <form class="position-relative" id="init_user-form">
                <input name="check_user" value="1" type="hidden" />
                <input name="huid" type="hidden" value="{$settings->hui}" />
                <div class="mb-3">
                    <label for="phone" class="form-label">Номер телефона</label>
                    <input type="text" inputmode="numeric" name="phone" class="form-control" id="phone" placeholder="+7-777-777-77-77" {if isset($user_phone) && $user_phone != ''} value="{$user_phone}" {/if} />
                </div>
                <div class="my-2 text-danger" id="phone-error" style="display: none"><small>Код оператора введен не верно.</small></div>

                <div class="form-check form-check__label">
                    <input class="form-check-input" type="checkbox" id="agree" name="agree">
                    <label class="form-check-label agree-label" for="agree">
                        Нажимая "Продолжить", я соглашаюсь со
                        <a href="#" data-bs-toggle="collapse" data-bs-target="#documentsCollapse" aria-expanded="false" aria-controls="documentsCollapse">
                            следующими условиями
                        </a>
                    </label>
                </div>

                <!-- Скрытый по умолчанию список условий -->
                <div class="collapse mt-2" id="documentsCollapse">
                    <div>
                        <p>
                            С <a href="{$config->root_url}/files/docs/asp_usage_policy.pdf" target="_blank" class="">
                                условиями использования аналога собственноручной подписи (АСП)
                            </a> согласен
                        </p>
                        <p>
                            Я согласен на <a href="{$config->root_url}/files/docs/personal_data_consent.pdf" target="_blank" class="">
                                обработку персональных данных
                            </a> и
                            <a href="{$config->root_url}/files/docs/marketing_consent.pdf" target="_blank" class="">
                                получение маркетинговых коммуникаций
                            </a>
                        </p>

                        {if ! $is_pk && $is_virtual_card_checkbox}
                            <div class="mini-checkbox for-nk">
                                <input class="form-check-input" checked="checked" type="checkbox" value="1" id="agree_virtual_card" name="virtual_card">
                                <label>
                                    С <a href="{$config->root_url}../../../share_docs/general/docs_uslugi_oferta_esp_0250725.pdf" target="_blank" class="">
                                        «Офертой ООО РНКО «Платежный конструктор» на выпуск виртуальной карты Boostra»
                                    </a> согласен
                                </label>
                            </div>
                        {/if}
                    </div>
                </div>

                <div id="smart-captcha-loan-container" style="display: none;" class="smart-captcha mt-3" data-sitekey="{$config->smart_captcha_client_key}"></div>
                <button type="submit" class="validate-btn btn btn-primary" id="phone-submit-btn">Продолжить</button>
                    
            </form>
            <form class="position-relative" id="init_user-check_sms_form" style="display: none">
                <h5 id="init_user-phone_title" class="text-primary my-3"></h5>
                <input name="huid" type="hidden" value="{$settings->hui}" />
                <div class="input-group mb-3">
                    <span class="input-group-text input-group-text_code">Введите код</span>
                    <input type="text" inputmode="numeric" name="code" class="form-control" id="code" placeholder="****" />
                </div>
                <div class="code_btns_wrapper">
                    <button type="submit" class="btn btn-outline-primary">Отправить повторно {* <i class="mx-2 bi bi-repeat"></i> *}</button>
                    <button type="button" class="btn btn-outline-secondary" id="back-to-phone">{* <i class="bi bi-arrow-left"></i> *}Изменить номер телефона</button>
                </div>
            </form>
        </div>
    </div>
</section>

{capture name=page_scripts}

    {if $t_bank_button_registration_access}
        <script src="https://sso-forms-prod.t-static.ru/tid/widget.js"></script>
        <script type="text/javascript">
            const authParams = {
                redirectUri: '{$t_id_redirect_url}',
                responseType: 'code',
                clientId: '{$config->TBankId.clientId}',
                state: '{$t_id_state}'
            }

            const uiParams = {
                container: '#container-for-tid-button',
                size: 'm',
                color: 'primary',
                text: 'Продолжить c Т-Банк',
                target: '_self',
                recognized: true,
            }

            const tidSdk = new TidSDK(authParams);

            tidSdk.addButton(uiParams);

            $(document).ready(function() {
                $(document).find(".tid-4PNRE-text").text("Продолжить через Т-Банк");
            });
        </script>
    {/if}

    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script type="text/javascript">
        const stateInitUser = {
            inputPhone: false,
            backFromSms: false, // Флаг - была ли нажата кнопка Назад с экрана SMS
            captchaWidgetId: null, // ID виджета капчи для возможности сброса
        };
        
        function initSmartCaptcha (addAnimation = false) {
            if (window.smartCaptcha) {
                const container = document.getElementById('smart-captcha-loan-container');
                const widgetSmartCaptchaId = window.smartCaptcha.render(container, {
                    sitekey: container.dataset.sitekey,
                    hl: 'ru',
                });

                stateInitUser.captchaWidgetId = widgetSmartCaptchaId;

                if (addAnimation && typeof addAnimation === 'boolean') {
                    $(container).show().addClass('animate-blink');
                } else {
                    $(container).show().removeClass('animate-blink');
                }
            }
        }

        // Универсальная функция переноса/инициализации капчи в нужную форму
        function moveCaptchaTo(targetFormSelector, addAnimation = false) {
            const target = $(targetFormSelector);
            if (!target.length) return;

            let captchaContainer = $('#smart-captcha-loan-container');
            if (!captchaContainer.length) {
                const existingSitekey = "{$config->smart_captcha_client_key}";
                captchaContainer = $(
                    '<div id="smart-captcha-loan-container" style="display: none;" class="smart-captcha mt-3" data-sitekey="' + existingSitekey + '"></div>'
                );
                target.append(captchaContainer);
            } else if (!captchaContainer.parent().is(target)) {
                captchaContainer.appendTo(target);
            }

            if (window.smartCaptcha) {
                try {
                    if (stateInitUser.captchaWidgetId !== null) {
                        window.smartCaptcha.reset(stateInitUser.captchaWidgetId);
                    }
                } catch (e) {
                    captchaContainer.empty();
                } finally {
                    try {
                        captchaContainer.show();
                        initSmartCaptcha(addAnimation);
                    } catch (e2) {
                        console.log('Captcha re-init error:', e2);
                    }
                }
            }
        }

        function validateFormInit() {
            let errors = [];

            $("#init_user-form input").removeClass('is-invalid');
            $('#init_user .alert').remove();

            if (!$("#init_user-form input[name='phone']").inputmask("isComplete") && !$("#phone-error").is(':visible')) {
                $("#init_user-form input[name='phone']").addClass('is-invalid');
                errors.push('phone')
            }

            if (!$("#init_user-form input[name='agree']").prop('checked')) {
                $("#init_user-form input[name='agree']").addClass('is-invalid');
                errors.push('agree')
            }

            return !errors.length;
        }

        function checkSmsInitUser(code, phone) {
            $('#init_user .alert').remove();
            $("#init_user-check_sms_form input[name='code']").removeClass('is-valid').removeClass('is-invalid');

            $.ajax({
                url: 'ajax/sms.php',
                data: {
                    phone,
                    code,
                    action: 'check_init_user',
                    calc_amount: {$calc_amount},
                    calc_period: {$calc_period}.
                },
                method: 'GET',
                beforeSend: function () {
                    $("#init_user-check_sms_form").addClass('loading');
                    $("#init_user-check_sms_form button").prop('disabled', true);
                },
                success: function (resp) {
                    if (resp.success && resp.redirect_url) {
                        $('#init_user').prepend('<div class="alert alert-success" role="alert">Происходит переход на следующую страницу</div>');
                        $("#init_user-check_sms_form input[name='code']").addClass('is-valid');

                        if (resp.is_new_user == 1) {
                            sendMetric('reachGoal', 'podtverdil_telefon');
                        } else if (resp.is_new_user == 0) {
                            sendMetric('reachGoal', 'pk_podtverjdenienomera');
                        }

                        window.location = resp.redirect_url;
                    } else {
                        $("#init_user-check_sms_form input[name='code']").addClass('is-invalid');
                        $('#init_user').prepend('<div class="alert alert-danger" role="alert"> ' + (resp.error || 'Код введен не верно.') + ' </div>');
                        $("#init_user-check_sms_form").removeClass('loading');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                },
            }).done(function () {
                $("#init_user-check_sms_form button").prop('disabled', false);
            });
        }

        function sentInitSms(phone) {
            $('#init_user .alert').remove();
            let postData = $("#init_user-check_sms_form").serializeArray();
            postData.push({
                name: 'flag',
                value: 'LOGIN'
            },{
                name: 'phone',
                value: phone
            }, {
                name: 'page',
                value: 'init_user'
            });

            $.ajax({
                url: 'ajax/send_sms.php',
                data: postData,
                method: 'POST',
                beforeSend: function () {
                    $("#init_user-check_sms_form").addClass('loading');
                    $("#init_user-check_sms_form button").prop('disabled', true);
                },
                success: function (resp) {
                    if (!!resp.captcha) {
                        switch (resp.captcha) {
                            case 'init':
                                $("#init_user-check_sms_form").append($("#init_user-form #smart-captcha-loan-container").clone(true))
                                $("#init_user-form #smart-captcha-loan-container").remove();
                                initSmartCaptcha();
                                break;
                            case 're_init':
                                initSmartCaptcha();
                                break;
                            case 'empty_token':
                                $('#init_user').prepend('<div class="alert alert-danger" role="alert">Проверка не пройдена</div>');
                                break;
                        }

                        $("#init_user-phone_title").html("<span class='text-danger'>Пройдите проверку капчи</span>");
                    } else {
                        if (!resp.time_error && !resp.error) {
                            $("#init_user-phone_title").html("На Ваш номер <b>" + {if $format_phone}'{$format_phone}'{else}phone{/if} + "</b> был отправлен код <b>№" + resp.number_sms + "</b> подтверждения.");
                        }

                        $('#smart-captcha-loan-container').removeClass('animate-blink');

                        if (!!resp.time_error) {
                            $('#init_user').prepend('<div class="alert alert-danger" role="alert">' + resp.time_error + '</div>');
                        }

                        if (!!resp.error) {
                            $('#init_user').prepend('<div class="alert alert-danger" role="alert">' + resp.error + '</div>');
                        }
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                },
            }).done(function () {
                $("#init_user-check_sms_form").removeClass('loading');
                $("#init_user-check_sms_form button").prop('disabled', false);
            });
        }

        function getCookie(name) {
            const cookies = document.cookie.split('; ');

            for (const cookie of cookies) {
                const [key, value] = cookie.split('=');
                if (key === name) return value;
            }

            return null;
        }

        $(document).ready(function () {
            stateInitUser.backFromSms = false;
            
            const scriptElement = document.createElement('script');
            scriptElement.src = 'https://smartcaptcha.yandexcloud.net/captcha.js?render=onload';
            // scriptElement.onload = initSmartCaptcha;
            scriptElement.onerror = function (error) {
                console.log('Error Smart captcha script error: ', error);
            };
            document.body.appendChild(scriptElement);

            $("#init_user-check_sms_form").on('submit', function (e) {
                e.preventDefault();
                sentInitSms($("#init_user-form input[name='phone']").val());
            })

            $("#init_user-check_sms_form input[name='code']").inputmask({
                mask: "9999",
                clearIncomplete: true,
                oncomplete: function () {
                    const phone = {if $flow_after_personal_data_register && $user_phone} '{$user_phone|escape}' {else} $("#init_user-form input[name='phone']").val() {/if};
                    checkSmsInitUser($(this).val(), phone);
                },
            });

            {if !$flow_after_personal_data_register}
                $("#init_user-form input[name='phone']").inputmask({
                    mask: "+7 (999) 999-99-99",
                    clearIncomplete: true,
                    oncomplete: function () {
                        $(this).removeClass('is-valid').removeClass('is-invalid');
                        if ($(this).valid() && !$("#phone-error").is(':visible')) {
                            $(this).addClass('is-valid');
                            if (!stateInitUser.inputPhone) {
                                sendMetric('reachGoal', 'vvel_telefon');
                                stateInitUser.inputPhone = true;
                            }
                        } else {
                            $(this).addClass('is-invalid');
                        }
                    },
                });
            {/if}

            $("#init_user-form input[name='agree']").on("change", function () {
                $(this).removeClass('is-valid').removeClass('is-invalid')
                if ($(this).prop('checked')) {
                    $(this).addClass('is-valid');
                } else {
                    $(this).addClass('is-invalid');
                }
            });

            $("#back-to-phone").on('click', function() {
                sendMetric('reachGoal', 'click_back_step2');
                $("#init_user-check_sms_form").hide();
                $("#init_user-form").fadeIn();
                // Очищаем поле кода при возврате
                $("#init_user-check_sms_form input[name='code']").val('');

                moveCaptchaTo('#init_user-form', false);

                stateInitUser.backFromSms = true;
            });

            $("#init_user-form").on('submit', function (e) {
                e.preventDefault();

                if (!validateFormInit()) {
                    return;
                }

                const user_phone = $("#init_user-form input[name='phone']").val();


                $.ajax({
                    url: 'ajax/loginCodeByCall.php',
                    data: $(this).serializeArray(),
                    method: 'POST',
                    beforeSend: function () {
                        $("#init_user-form").addClass('loading');
                        $("#init_user-form button").prop('disabled', true);
                    },
                    success: function (resp) {
                        if (resp.error && resp?.error_type !== 'user_not_find') {
                            let errorMessage = resp.error;
                            switch (resp.error) {
                                case 'user_blocked':
                                    errorMessage = 'Пользователь заблокирован';
                                    break;
                            }
                            $('#init_user').prepend('<div class="alert alert-danger" role="alert">' + errorMessage + '</div>');
                        } else if (!!resp.captcha_error) {
                            initSmartCaptcha(true)
                        } else if (resp.redirect_url) {
                            window.location = resp.redirect_url;
                        } else {
                            $("#init_user-form").hide();
                            $("#init_user-check_sms_form").fadeIn();
                            $("#init_user-check_sms_form input[name='code']").val('');

                            
                            if (!stateInitUser.backFromSms) {
                                // Если кнопка "Назад" НЕ нажималась - НЕ проверяем номера, всегда отправляем SMS
                                sentInitSms(user_phone);
                            } else {
                                // Если кнопка "Назад" была нажата - проверяем соответствие номеров
                                const savedPhone = getCookie('init_user_phone');
                                const cleanSavedPhone = savedPhone ? savedPhone.replace(/\D/g, '') : '';
                                const cleanUserPhone = user_phone.replace(/\D/g, '');
                                
                                if (cleanSavedPhone !== cleanUserPhone) {
                                    sentInitSms(user_phone);
                                }

                                stateInitUser.backFromSms = false;
                            }
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                        alert(error);
                        console.log(error);
                    },
                }).done(function () {
                    $("#init_user-form").removeClass('loading');
                    $("#init_user-form button").prop('disabled', false);
                });
            });
            {if $flow_after_personal_data_register && $user_phone}
                scriptElement.onload = function() {
                    $("#init_user-form input[name='agree']").prop('checked', true);
                    $("#init_user-form").hide();
                    $("#init_user-check_sms_form").fadeIn();
                    sentInitSms('{$user_phone|escape}');
                };
            {/if}
        });
        $(document).ready(function () {
            sendMetric('reachGoal', 'sglavnoynatel')
        });
    </script>
{/capture}
