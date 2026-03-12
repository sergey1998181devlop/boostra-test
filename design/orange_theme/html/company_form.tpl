{* Страница инициализации пользователя *}

{* Канонический адрес страницы *}
{$canonical="/company_form" scope=parent}

{$meta_title = "Форма для юридических лиц" scope=parent}

{function name=render_field}
    <label for="{$key}" class="form-label">{$value}</label>

    {if $key === 'tax'}
        <select class="form-control {if $errors[$key]}is-invalid{/if}" name="{$key_field}[{$key}]" required id="{$key}">
            <option value="" disabled selected>Выберите вариант</option>
            {foreach $taxes as $tax}
                <option {if $smarty.post[$key_field][$key] === $tax}selected{/if} value="{$tax}">{$tax}</option>
            {/foreach}
        </select>
    {elseif $key === 'co_credit_target_id'}
        <select class="form-control {if $errors[$key]}is-invalid{/if}" name="{$key_field}[{$key}]" required id="{$key}">
            <option value="" disabled selected>Выберите вариант</option>
            {foreach $credit_targets as $credit_target}
                <option {if $smarty.post[$key_field][$key] === $credit_target->id}selected{/if} value="{$credit_target->id}">{$credit_target->name}</option>
            {/foreach}
        </select>
    {else}
        <input type="{if $key === 'company_form_email'}email{else}text{/if}"
               name="{$key_field}[{$key}]"
               required
               id="{$key}"
               value="{if $smarty.post[$key_field][$key]}{$smarty.post[$key_field][$key]}{/if}"
               class="form-control {if $errors[$key]}is-invalid{/if}"
               placeholder="" />
    {/if}

    {if $key === 'company_form_email'}
        <span class="form-text text-warning">Указывайте актуальную почту, мы направим Вам запрос с необходимыми документами.</span>
    {/if}

    {if $errors[$key]}
        <div class="d-block my-2">
            <span class="form-text text-danger">{$errors[$key]}</span>
        </div>
    {/if}
{/function}

<section id="init_user">
    <div class="row justify-content-center">
        <div class="col-12">
            {if $success}
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <div>
                        Форма успешно была отправлена. Сейчас вы будите перенаправлены в личный кабинет.
                    </div>
                </div>
            {/if}
            {if $errors}
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">При отправке формы обнаружены следующие ошибки:</h4>
                    {foreach $errors as $error}
                        <p class="mb-0">
                            <small>{$error}</small>
                        </p>
                    {/foreach}
                </div>
            {/if}
            {if $warning}
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">{$warning->title}</h4>
                        <p><b>{$warning->description}</b></p>
                </div>
            {/if}
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading">Уважаемый клиент!</h4>
                <p>Мечты должны сбываться, а планы – воплощаться в реальность! Не откладывай важные покупки и решения на потом. Благодаря денежному кредиту, ты сможешь осуществить свои цели уже сегодня!</p>
                <hr>
                <p class="mb-0">Легкие условия, гибкие сроки и низкие ставки помогут тебе справиться с любыми финансовыми задачами.</p>
            </div>
            {if $show_company_form}
                <form method="post">
                    {foreach $fields as $key_field => $field}
                        {if $field@iteration > 1}
                            <hr />
                        {/if}
                        <fieldset class="">
                            <legend>{$field['name']}</legend>
                            {foreach $field['values'] as $key => $value}
                                <div class="mb-3">
                                    {render_field key_field=$key_field key=$key value=$value}
                                </div>
                            {/foreach}
                        </fieldset>
                    {/foreach}
                    <div id="smart-captcha-loan-container" class="smart-captcha mb-3" data-sitekey="{$config->smart_captcha_client_key}"></div>
                    <button type="submit" class="btn btn-primary">Отправить</button>
                </form>
            {/if}
        </div>
    </div>
</section>
{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    {if $success}
        <script type="text/javascript">
            $(document).ready(function () {
                setTimeout(function () {
                    window.location.href = "/user"
                }, 3000)
            });
        </script>
    {/if}
    <script type="text/javascript">
        $("[name$='[phone]']").inputmask({
            mask: "+7 (999) 999-99-99",
            clearIncomplete: true,
            oncomplete: function () {
                $(this).removeClass('is-valid').removeClass('is-invalid');
                if ($(this).valid() && !$("#phone-error").is(':visible')) {
                    $(this).addClass('is-valid');
                } else {
                    $(this).addClass('is-invalid');
                }
            },
        });
        $("[name='passport[passport_serial]']").inputmask({
            mask: "9999",
            clearIncomplete: true,
            oncomplete: function () {
                $(this).removeClass('is-valid').removeClass('is-invalid');
                if ($(this).valid()) {
                    $(this).addClass('is-valid');
                } else {
                    $(this).addClass('is-invalid');
                }
            },
        });
        $("[name='passport[passport_number]']").inputmask({
            mask: "999-999",
            clearIncomplete: true,
            oncomplete: function () {
                $(this).removeClass('is-valid').removeClass('is-invalid');
                if ($(this).valid()) {
                    $(this).addClass('is-valid');
                } else {
                    $(this).addClass('is-invalid');
                }
            },
        });
        $(document).ready(function () {
            $("[name='personal[birth]'], [name='passport[passport_date]']").attr('type', 'date');
            const scriptElement = document.createElement('script');
            scriptElement.src = 'https://smartcaptcha.yandexcloud.net/captcha.js?render=onload';
            scriptElement.onload = function () {
                const container = document.getElementById('smart-captcha-loan-container');
                const widgetSmartCaptchaId = window.smartCaptcha.render(container, {
                    sitekey: container.dataset.sitekey,
                    hl: 'ru',
                });
            };
            scriptElement.onerror = function (error) {
                console.log('Error Smart captcha script error: ', error);
            };
            document.body.appendChild(scriptElement);
        })
    </script>
{/capture}

