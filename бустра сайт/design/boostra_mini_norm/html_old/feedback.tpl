{* Страница с формой обратной связи *}

{* Канонический адрес страницы *}
{$canonical="/{$page->url}" scope=parent}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				{if !$message_sent}
				<h1>Вы уже заинтересованы?</h1>
				<h5>Небольшая форма и вы с нами!</h5>
				{else}
				<h1>Все прошло отлично!</h1>
				<h5>В ближайшее время мы с вами свяжемся!</h5>
				{/if}
			</hgroup>
			

			<form method="post" id="neworder" class="form feedback_form"> 
				<div id="steps">
					
					{if !$message_sent}

					<fieldset style="display: block;">
						
						<label>
							<input type="text" name="name" maxlength="255" id="name" placeholder="" value="{$name|escape}" required="" aria-required="true">
							{if $error=='empty_name'}
							<small class="err error" id="err-name">Введите имя</small>
							{/if}
							<span class="floating-label">Имя</span>
						</label>
						
						<label>
							<input type="tel" name="message" placeholder="" required="" value="{$message|escape}"/>
							{if $error=='empty_text'}
							<small class="err error" id="err-text">Введите телефон</small>
							{/if}
							<span class="floating-label">Телефон</span>
						</label>

						<label>
							<input type="email" ata-format="email" name="email" placeholder="" required="" value="{$email|escape}"  maxlength="255"/>
							{if $error=='empty_email'}
							<small class="err error" id="err-email">Введите email</small>
							{/if}
							<span class="floating-label">Электронная почта</span>
						</label>

						<div class="next">
							<input class="button big" type="submit" name="feedback" value="Отправить" />
							<p style="font-size: 0.8rem;">
								* Нажимая на кнопку «Отправить», я даю свое согласие на обработку персональных данных в соответствии с законом №152-ФЗ «О персональных данных» от 27.07.2006 и принимаю <a style="text-decoration: underline;" href="/boostra-politika" target="_blank">условия обработки персональных данных</a>
							</p>
						</div>

					</fieldset>
					{/if}
				</div>
			</form>
		</div>
	</div>
</section>





<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
<script src="design/{$settings->theme}/js/jquery.validate.min.js" type="text/javascript"></script>
<script src="design/{$settings->theme}/js/jquery.magnific-popup.min.js" type="text/javascript"></script>


<script type="text/javascript">
	$(document).ready(function() 
	{
		 $('input[type="tel"]').inputmask("+7 (999) 999-99-99");
	});
</script>