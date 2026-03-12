{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}

{capture name=page_scripts}
	<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
	<script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.11" type="text/javascript"></script>
	{*<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>*}

	<script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.8.0" type="text/javascript"></script>

	{* Автокомплит для адресов - используется тот же что и в additional_data *}
	<script type="text/javascript" src="/js/autocomplete/jquery.autocomplete-min.js"></script>
	<link rel="stylesheet" href="/js/autocomplete/styles-address.css?v=1.001" />

	{* Новый JS для формы адресов *}
	<script src="design/{$settings->theme}/js/address_data.app.js?v=2.1.0" type="text/javascript"></script>
{/capture}

<style>
	.floating-label.required::after {
		content: '*';
		color: red;
		margin-left: 4px;
	}

</style>

<section id="worksheet">
	<div>
		<div class="box">

			{include file='display_stages.tpl' current=4 percent=63 total_step=4}

			<h1 class="reg_title">Адрес</h1>

			<form method="post" id="personal_data" class="js-send-feedback" data-target="etap-propiska" onsubmit="sendMetric('reachGoal', 'etap-propiska'); return true;">
				<div id="steps">

					{if $error == 'allready_exists'}
						<div class="alert alert-danger">
							Клиент с такими персональными данными уже зарегистрирован.
							<br />
							Просим связаться с нами по номеру 8 800 333 30 73.
						</div>
					{else}

						{include "ab_banner_registration.tpl"}

						<fieldset style="display: block;">

							<input type="hidden" value="address_data" name="stage" />

							<span class="title">Адрес прописки</span>
							<div class="register">
								<label class="{if $error=='empty_registration_address'}error{/if}">
									<input
											type="text"
											id="registration_address_full"
											name="registration_address_full"
											value="{$registration_full_address|escape}"
											placeholder=""
											required="required"
											autocomplete="street-address"
									/>
									<span class="floating-label required">Введите адрес прописки</span>
									{if $error=='empty_registration_address'}
										<span class="error">Укажите адрес прописки</span>
									{/if}
									<small>Начните вводить адрес, выберите из списка</small>
								</label>



								{* Скрытые поля для отправки данных *}
								<input type="hidden" name="Regindex" id="Regindex" value="{$registration_zipCode|escape}">
								<input type="hidden" name="Regregion" id="Regregion" value="{$registration_region|escape}">
								<input type="hidden" name="Regcity" id="Regcity" value="{$registration_city|escape}">
								<input type="hidden" name="Regstreet" id="Regstreet" value="{$registration_street|escape}">
								<input type="hidden" name="Reghousing" id="Reghousing" value="{$registration_house|escape}">
								<input type="hidden" name="Regbuilding" id="Regbuilding" value="{$registration_building|escape}">
								<input type="hidden" name="Regroom" id="Regroom" value="{$registration_apartment|escape}">
								<input type="hidden" name="Regregion_shorttype" id="Regregion_shorttype" value="">
								<input type="hidden" name="Regcity_shorttype" id="Regcity_shorttype" value="">
								<input type="hidden" name="Regstreet_shorttype" id="Regstreet_shorttype" value="">
								<input type="hidden" name="prop_okato" id="prop_okato" value="">
								<input type="hidden" name="prop_city_type" id="prop_city_type" value="">
								<input type="hidden" name="prop_street_type_long" id="prop_street_type_long" value="">
								<input type="hidden" name="prop_street_type_short" id="prop_street_type_short" value="">

								<label class="big left align-center">
									<div class="checkbox check_address">
										<input type="checkbox" value="1" id="equal" name="equal" {if $equal}checked="true"{/if} />
										<span></span>
									</div> Адрес регистрации совпадает с адресом проживания
								</label>

								{include file='partials/bki_consent_checkbox.tpl'}


							</div>

							<div class="living" id="living_block" {if $equal}style="display:none"{/if}>

								<span class="title">Адрес проживания</span>

								<label class="{if $error=='empty_residence_address'}error{/if}">
									<input
											type="text"
											id="residence_address_full"
											name="residence_address_full"
											value="{$residence_full_address|escape}"
											placeholder=""
											required="required"
											autocomplete="street-address"
									/>
									<span class="floating-label required">Введите адрес проживания</span>
									{if $error=='empty_residence_address'}
										<span class="error">Укажите адрес фактического проживания</span>
									{/if}
									<small>Начните вводить адрес, выберите из списка</small>
								</label>



								{* Скрытые поля для фактического адреса *}
								<input type="hidden" name="Faktindex" id="Faktindex" value="{$residence_zipCode|escape}">
								<input type="hidden" name="Faktregion" id="Faktregion" value="{$factual_region|escape}">
								<input type="hidden" name="Faktcity" id="Faktcity" value="{$residence_city|escape}">
								<input type="hidden" name="Faktstreet" id="Faktstreet" value="{$residence_street|escape}">
								<input type="hidden" name="Fakthousing" id="Fakthousing" value="{$residence_house|escape}">
								<input type="hidden" name="Faktbuilding" id="Faktbuilding" value="{$residence_building|escape}">
								<input type="hidden" name="Faktroom" id="Faktroom" value="{$residence_apartment|escape}">
								<input type="hidden" name="Faktregion_shorttype" id="Faktregion_shorttype" value="">
								<input type="hidden" name="Faktcity_shorttype" id="Faktcity_shorttype" value="">
								<input type="hidden" name="Faktstreet_shorttype" id="Faktstreet_shorttype" value="">
								<input type="hidden" name="prog_okato" id="prog_okato" value="">
								<input type="hidden" name="prog_city_type" id="prog_city_type" value="">
								<input type="hidden" name="prog_street_type_long" id="prog_street_type_long" value="">
								<input type="hidden" name="prog_street_type_short" id="prog_street_type_short" value="">

							</div>

							<div class="next">
								<button class="button big" id="doit" type="submit" name="neworder">Получить решение</button>
							</div>
						</fieldset>
					{/if}
				</div>
			</form>

			{include 'modals/inactivity_modal.tpl'}
		</div>
	</div>
</section>

<script type="text/javascript">
	var juicyLabConfig = {
		completeButton: "#doit",
		apiKey: "{$juiceScoreToken}"
	};

	{literal}
	let sessionIdExist = (document.cookie.match(/(?:^|;\s*)juicescore_session_id=([^;]*)/) || [])[1];
	if (sessionIdExist) {
		window.addEventListener('load', function () {
			$('#juicescore_session_id').val($.cookie('juicescore_session_id'));
		});
	}
	else {
		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = "https://score.juicyscore.com/static/js.js";
		var x = document.getElementsByTagName('head')[0];
		x.appendChild(s);

		window.addEventListener('load', function () {
			juicyScoreApi.getSessionId()
					.then(function (sessionId) {
						$.cookie('juicescore_session_id', sessionId, {expires: 1, path: '/'});
						$('#juicescore_session_id').val(sessionId);
					});
		});
	}
	{/literal}
</script>

<noscript>
	<img style="display:none;" src="https://score.juicyscore.com/savedata/?isJs=0"/>
</noscript>
