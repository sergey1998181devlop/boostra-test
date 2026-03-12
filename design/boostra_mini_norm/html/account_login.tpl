{* Страница входа пользователя *}

{* Канонический адрес страницы *}
{$canonical="/account/login" scope=parent}

{$meta_title = "Добро пожаловать в личный кабинет Boostra" scope=parent}
{$meta_title2 = "Бустра личный кабинет официальный сайт компании" scope=parent}
{$meta_description = "Личный кабинет Бустра. Для входа понадобится телефон и пароль. Для входа нажмите «Войти»." scope=parent}
{$meta_keywords = "Бустра вход в личный кабинет" scope=parent}

{$login_scripts = true scope=parent}

{$body_class = "gray" scope=parent}
<!--
{literal}
	<script src="https://unpkg.com/@vkid/sdk@1.1.0/dist-sdk/umd/index.js"></script>
	<script type="text/javascript" src="design/boostra_mini_norm/js/vk.js" defer></script>
{/literal}
-->
<style>
	.auth__buttons__login {
        display: flex;
        gap: 12px;
        max-width: 445px;
        height: 50px;
		flex-flow: row;
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
	
	.login_form_subtitle {
		margin-top: 32px;
		margin-bottom: 16px;
		font-weight: 500;
		display: block;
		text-align: left;
		font-size: medium;
		background-color: none;

	}
</style>
<section id="login">
	<div>
		<div class="wrapper">
			{if $flash_error}
				<div class="alert alert-danger">{$flash_error}</div>
			{/if}
			<h2 id="login_form_title">Вход в личный кабинет</h2>
			
			<!--<div>Используйте метод быстрой авторизации</div>
			<br>
			{if !$vk_disabled}
				<br>
				<div id="js-vkid-onetap"></div>
			{/if}-->
			{if $vk_error}
				<span class="message_error">{$vk_error}</span>
			{/if}
			{if $t_bank_button_registration_access || $esia_button_registration_access }
				<h4 class="login_form_subtitle">Войдите через Госуслуги или Tinkoff ID</h4>
				<div class="auth__buttons__login">
					{if $t_bank_button_registration_access }
						<a onclick="sendMetric('reachGoal', 'tid_auth')" class="tid_button" href="{$t_id_auth_url}">
							T-ID
							<img src="/design/boostra_mini_norm/assets/image/tinkoff-id-small.png" alt="" />
						</a>
					{/if}
					{if $esia_button_registration_access }
						<a onclick="sendMetric('reachGoal', 'gu_auth')" class="esia_button" href="{$esia_redirect_url}">
							Госуслуги
							<img class="" height="24" src="/design/boostra_mini_norm/assets/image/esia_logo.png" alt="" />
						</a>
					{/if}
				</div>
			{/if}
			<form method="post" id="send" class="loading" {if $error}style="display: none;"{/if} {if !$same_page}target="_blank"{/if}>
				{if $error}
					<span class="error">
						{if $error == 'login_incorrect'}Неверные данные для входа
						{elseif $error == 'user_disabled'}Ваша учетная запись заблокирована
						{elseif $error == 'user_blocked'}Пользователь с таким номером уже зарегистрован.<br />С Вами свяжется Клиентский Центр.
						{else}{$error|escape}{/if}
                	</span>
				{/if}
				<div id="wrapper_fields">
					<label id="login_form_phone">
						<span id="login_form_description">Или авторизуйтесь с помощью{* <br/> *} мобильного телефона, который Вы указывали{* <br/> *} при получении займа</span>
						<div><input id="phoneInput" inputmode="tel" autocomplete="on" type="tel" name="phone" placeholder="Номер телефона" required="" {if $phone}value="{$phone}"{/if}/></div>
					</label>
				</div>

				{* recaptcha}
                    <div style="margin:20px auto 0 auto;display:inline-block">
                        <div id="recaptcha_register"></div>
                    </div>
                *}

				{*<div>Пожалуйста, выберите ваш любимый мессенджер <br />и мы пришлем в него код</div>*}
				<div class="btn-login-group">
					{*<a href="javascript:void(0);" data-messenger="whatsapp" target="_blank" class="js-login-btn btn-login-wa"></a>*}
					{*<a href="javascript:void(0);" data-messenger="viber" class="js-login-btn btn-login-vi"></a>*}
					{*}
                    <a href="javascript:void(0);"  data-messenger="telegram"class="js-login-btn btn-login-tg"></a>
                    *}
				</div>

				

				<button class="big new-button ">Войти</button>

				<div id="login_form_footer"></div>
			</form>

			<br />
			{*<button id="gosuslugi" type="button"></button>*}
			<form method="post" id="check" {if $error}style="display: block;"{/if}>
				<input type="hidden" name="page_action" value="{$page_action}" />
				<label>
					<input type="hidden" name="login" value="1" />
					{if $error != 'user_blocked'}
						<span style="display:block;" id="check_title"></span>
						<div>
							<input type="text" inputmode="numeric" name="key" placeholder="Код" required="" />
							<input type="hidden" name="real_phone" {if $phone}value="{$phone}"{/if}/>
						</div>
					{/if}
					{if $error}
						<div class="message_error">
							{if $error == 'login_incorrect'}Неверный код
							{elseif $error == 'user_disabled'}Ваш аккаунт был удален.
							{elseif $error == 'user_blocked'}Пользователь с таким номером уже зарегистрован.<br />С Вами свяжется Клиентский Центр.
							{else}{$error}{/if}
						</div>
					{/if}
				</label>
				{if $error != 'user_blocked'}
					<input type="submit" name="login" class="big button" value="Отправить" {if !$is_developer && !$is_admin}style="display:none"{/if} />
					{* <br/><br/> *}
					<div class="repeat_sms" style="margin-left:0">
						{*<a href="#" class="new_sms">Отправить код еще раз</a>*}
					</div>
					<script>
						var viberBotName = '{$settings->config->viberBotName}';
						var tlgBotName = '{$settings->config->tlgBotName}';
					</script>
					<div id="loginMessangers" {if !$error}style="display: none;"{/if}>
						<div id="codeInSms">
							<a id="loginBySms" inputmode="numeric" href="javascript:void(0);" onclick="loginMessangers('sms');" class="button ">Отправить код через смс</a>
						</div>
						{*<div>Пожалуйста, выберите ваш любимый мессенджер <br />и мы пришлем в него код</div>
                        <div class="btn-login-group">
                            <a href="javascript:void(0);" onclick="loginMessangers('wa');" class="js-login-btn btn-login-wa"></a>
                            <a href="javascript:void(0);" onclick="loginMessangers('vi');" class="js-login-btn btn-login-vi"></a>
                            <a href="javascript:void(0);" onclick="loginMessangers('tg');" class="js-login-btn btn-login-tg"></a>
                        </div>*}
					</div>

				{/if}
			</form>
			<div id="smart-captcha-loan-container" style="display: none;" class="smart-captcha" data-sitekey="{$config->smart_captcha_client_key}"></div>

			<br>
			<div id="apps">
				<a href="https://redirect.appmetrica.yandex.com/serve/749596424009746204" target="_blank">
					<img src="https://boostra.ru//design/boostra_mini_norm/img/apps/175.png" style="width:90px;" />
				</a>
				&nbsp;
				<a href="https://redirect.appmetrica.yandex.com/serve/461366054585709806" target="_blank">
					<img src="https://boostra.ru//design/boostra_mini_norm/img/apps/176.png" style="width:90px;" />
				</a>
				&nbsp;
				<a href="https://redirect.appmetrica.yandex.com/serve/749647249176340067" target="_blank">
					<img src="https://boostra.ru//design/boostra_mini_norm/img/apps/177.png" style="width:90px;" />
				</a>
			</div>
		</div>
		{if isset($smarty.get.tid)}
			<a href="/ajax/auth/" id="auth-button-tinkoff" class="auth-button-tinkoff">Войти с Tinkoff ID</a>
			<input name="huid" type="hidden" value="{$authUrl}" />
		{/if}


	</div>
</section>
<script>
    {if $same_page}
    window.same_tab_login = true
    {else}
    window.same_tab_login = false || !'{$login_partner_href->href}'
    window.login_partner_href = '{$login_partner_href->href}'
    window.complaint_partner_href = '{$complaint_partner_href->href}'
    window.href_append = '&utm_source2={$smarty.cookies.utm_source}'
    {/if}
	$(document).ready(function() {
		$('#auth-button-tinkoff').on('click', function(e) {
			e.preventDefault();
			window.location.href = $(this).attr('href');
		});
	});
</script>
<style>
	.auth-button-tinkoff {
		display: block;
		/*margin-top: 20px;*/
		margin: 20px auto;
		max-width: 360px;
		background-color: #FFDD2D;
		color: black;
		text-decoration: none;
		padding: 10px 15px;
		border-radius: 5px;
		text-align: center;
		font-weight: bold;
	}

	button.big.new-button {
		background-color: #0A91ED;
		color: #fff;
		border-radius: 232px;
		padding: 20px 53px;
		font-size: 16px;
		font-weight: 700;
		margin: 0 0 0 auto;
		max-width: 210px;
		transition: background-color 0.2s ease;
	}

	@media (max-width: 600px) {
		button.big.new-button {
			max-width: none;
		}
	}

	#send {
		display: flex;
		flex-direction: column;
	}

	button.big.new-button:hover {
		background-color: #0071F2;
	}

	button.big.new-button:active {
		background-color: #0069E1;
	}

	.telegram-auth {
		text-align: center;
	}

	.telegram-button {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background-color: #0077FF;
		color: white;
		border-radius: 8px;
		padding: 10px 15px;
		cursor: pointer;
		transition: background-color 0.2s ease;
		max-width: 360px;
		width: 100%;
	}

	.telegram-button:hover {
		background-color: #0071F2;
	}

	.telegram-button:active {
		background-color: #0069E1;
	}
</style>
