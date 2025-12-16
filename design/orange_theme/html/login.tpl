{* Страница входа пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user/login" scope=parent}

{$meta_title = "Вход в кабинет заёмщика" scope=parent}

{$login_scripts = true scope=parent}

{$body_class = "gray" scope=parent}

<section id="login">
	<div>
		<div class="wrapper">
			<h3>Вход в кабинет заёмщика</h3>
			<form method="post" id="send" {if $error}style="display: none;"{/if}>
				{if $error}
                <span class="error">
                    {if $error == 'login_incorrect'}Неверные данные для входа
                    {elseif $error == 'user_disabled'}Ваша учетная запись заблокирована
                    {elseif $error == 'user_blocked'}С Вами свяжется контактный центр
                    {else}{$error|escape}{/if}
                </span>
                {/if}
                <label>
					<span>Авторизация производится с помощью<br/> мобильного телефона, который Вы указывали<br/> при получении займа</span>
					<div><input type="tel" name="phone" placeholder="Номер телефона" required="" {if $phone}value="{$phone}"{/if}/></div>
				</label>
				
				<button class="big">Отправить</button>
			</form>
			<form method="post" id="check" {if $error}style="display: block;"{/if}>
				<label>
					<span>Введите код из СМС</span>
					<div>
						<input type="tel" name="key" placeholder="Код" required="" />
						<input type="hidden" name="real_phone" {if $phone}value="{$phone}"{/if}/>
					</div>
					{if $error}
					<div class="message_error">
						{if $error == 'login_incorrect'}Неверный код
						{elseif $error == 'user_disabled'}Ваш аккаунт не активирован.
						{elseif $error == 'user_blocked'}По Вашей учетной записи множество совпадений.<br />С Вами свяжется контакный центр
						{else}{$error}{/if}
					</div>
					{/if}
				</label>
				<input type="submit" name="login" class="big button" value="Отправить"/>
				<br/><br/>
                <div class="repeat_sms">
    				{*}<a href="#" class="new_sms">Отправить код еще раз</a>{*}
                </div>
			</form>
			{if isset($smarty.get.tid)}
				<a href="/ajax/auth/" id="auth-button-tinkoff" class="auth-button-tinkoff">Войти с Tinkoff ID</a>
				<input name="huid" type="hidden" value="{$authUrl}" />
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