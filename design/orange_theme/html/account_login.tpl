{* Страница входа пользователя *}

{* Канонический адрес страницы *}
{$canonical="/account/login" scope=parent}

{$meta_title = "Добро пожаловать в личный кабинет Boostra" scope=parent}

{$login_scripts = true scope=parent}

{$body_class = "gray" scope=parent}

<section id="login">
	<div>
		<div class="wrapper">
			<h3>Вход в личный кабинет заёмщика</h3>
			<form method="post" id="send" {if $error}style="display: none;"{/if}>
				{if $error}
                <span class="error">
                    {if $error == 'login_incorrect'}Неверные данные для входа
                    {elseif $error == 'user_disabled'}Ваша учетная запись заблокирована
                    {elseif $error == 'user_blocked'}Пользователь с таким номером уже зарегистрован.<br />С Вами свяжется Клиентский Центр.
                    {else}{$error|escape}{/if}
                </span>
                {/if}
                <label>
					<span>Авторизация производится с помощью<br/> мобильного телефона, который Вы указывали<br/> при получении займа</span>
					<div><input type="tel" autocomplete="tel" inputmode="tel" name="phone" placeholder="Номер телефона" required="" {if $phone}value="{$phone}"{/if}/></div>
				</label>

                {* recaptcha
                    <div style="margin:20px auto 0 auto;display:inline-block">
                        <div id="recaptcha_register"></div>
                    </div>
                *}
                
                {*<div>Пожалуйста, выберите ваш любимый мессенджер <br />и мы пришлем в него код</div>*}
                <div class="btn-login-group">
                    {*<a href="javascript:void(0);" data-messenger="whatsapp" target="_blank" class="js-login-btn btn-login-wa"></a>*}
					{*<a href="javascript:void(0);" data-messenger="viber" class="js-login-btn btn-login-vi"></a>*}
                    {*
                    <a href="javascript:void(0);"  data-messenger="telegram"class="js-login-btn btn-login-tg"></a>
                    *}
                </div>
                
				<button class="big">Войти</button>
                
                
                
                <br />
                {*<button id="gosuslugi" type="button"></button>*}
			</form>
			<form method="post" id="check" {if $error}style="display: block;"{/if}>
				<label>
					<input type="hidden" name="login" value="1" />
                    {if $error != 'user_blocked'}
                    <span style="display:block;" id="check_title"></span>
					<div>
						<input type="tel" name="key" placeholder="Код" required="" />
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
				<br/><br/>
                <div class="repeat_sms" style="margin-left:0">
    				{*<a href="#" class="new_sms">Отправить код еще раз</a>*}
                </div>
                <script>
                    var viberBotName = '{$settings->config->viberBotName}';
                    var tlgBotName = '{$settings->config->tlgBotName}';
                </script>
                <div id="loginMessangers" style="display: none;">
                    <div id="codeInSms">
                        <a href="javascript:void(0);" onclick="loginMessangers('sms');" class="button ">Отправить код через смс</a>
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
			{if isset($smarty.get.tid)}
				<a href="/ajax/auth/" id="auth-button-tinkoff" class="auth-button-tinkoff">Войти с Tinkoff ID</a>
			{/if}
		</div>
	</div>
</section>
<style>
	.auth-button-tinkoff {
		display: block;
		margin-top: 20px;
		background-color: #FFDD2D;
		color: black;
		text-decoration: none;
		padding: 10px 15px;
		border-radius: 5px;
		text-align: center;
		font-weight: bold;
	}
</style>
