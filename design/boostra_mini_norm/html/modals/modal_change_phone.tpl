{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/change_phone.js?v=1.002"></script>
{/capture}

<div id='change_phone' class="white-popup-block mfp-hide">
    <p>Клиент с такими паспортными данными зарегистрирован по номеру телефона:</p>

    <form class="phone-letters" method="post" id="change_phone_form">
        {foreach str_split($existing_user->phone_mobile_obfuscated) as $key => $value}
            <input type="text" name="phone_letters[{$key}]" maxlength="1" pattern="[0-9]" inputmode="numeric"
                   value="{if $value !== '*'}{$value}{/if}"
                   {if $value !== '*'}readonly{/if}
            >
        {/foreach}
    </form>

    <p>Для входа в Личный кабинет вы можете воспользоваться этим номером или введите недостающие цифры.</p>

    <p>Если номер телефона изменился или недоступен, пожалуйста, напишите нам на почту info@boostra.ru. В письме
        приложите фото (селфи) с паспортом и укажите новый номер телефона.</p>

    <div class="phone-letters-buttons">
        <a href="/user/logout">Перейти на главную страницу</a>
        <button id="login-button">Войти в личный кабинет</button>
    </div>
</div>
