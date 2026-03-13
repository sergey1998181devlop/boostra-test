{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
	<script src="design/{$settings->theme}/js/sbp.js?v=1.010"></script>
	<link rel="stylesheet" href="design/{$settings->theme}/css/cross_order_nk_sign.css?v=1.00">
	{literal}
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			// Прокрутка в начало страницы при загрузке (для всех устройств)
			setTimeout(function() {
				window.scrollTo(0, 0);
				document.body.scrollTop = 0;
				document.documentElement.scrollTop = 0;
			}, 100);
			
			// Прокрутка к секции добавления карты при её отображении
			var section = document.getElementById('card-add-section');
			if (section) {
				new MutationObserver(function() {
					if (section.style.display !== 'none') {
						setTimeout(function() {
							window.scrollTo(0, 0);
							document.body.scrollTop = 0;
							document.documentElement.scrollTop = 0;
						}, 100);
					}
				}).observe(section, {attributes: true, attributeFilter: ['style']});
			}
		});
	</script>
	{/literal}
{/capture}

{literal}
	<style>
		.alert_card-add {
			background: #D2FBD0;
			color: #0C5F07;
			padding: 20px;
			border-radius: 10px;
		}
        /* Скрываем секцию привязки карты, пока не введён код автоподписания */
        #card-add-section {
            padding-top: 15px;
			display: flex;
			flex-direction: column;
			align-items: flex-start;
        }

		.box.b2p_card {
			padding: clamp(48px, 33.6px + 4.127vw, 100px) clamp(25px, 10.1px + 4.365vw, 80px) /* clamp(48px, 7px + 12.063vw, 200px) */;
		}

		.sbp-bki-doc label{
			width: 100% !important;
		}
		.sbp-bki-doc .docs_wrapper div .spec_size .checkbox{
			margin-top: 0px !important;
		}

	</style>
{/literal}

<section id="worksheet">
	<div>
		<div class="box b2p_card">
			{if $auto_confirm_type == 'AUTOCONFIRM_FLOW'}
			<hgroup>
				{if !empty($has_success_scorista)}
					<h5 class="alert_card-add">{$user->firstname}, ваш скор балл <b>{$scorista->scorista_ball}</b>. Вам одобрено <b>{$approve_amount}</b>.</h5>
				{else}
					{include "ab_banner_registration.tpl"}
				{/if}
				{* Скрытый блок, который покажем после успешного подписания без перезагрузки *}
				<div id="score-info" style="display: none;">
					{assign var=approved_sum value=$approve_amount|default:$decisionSum|default:$order->amount}
					{assign var=scor_ball value=$scorista->scorista_ball}
					<h5 class="alert_card-add">{$user->firstname}, {if !empty($scor_ball)}ваш скор балл <b>{$scor_ball}</b>. {/if}Вам одобрено <b>{$approved_sum}</b>.</h5>
				</div>
			</hgroup>
			{/if}

			<!-- Перенести в файл, не быдлокодить сука -->
			<iframe id="add_card_frame" src="" style="display: none; width:100%; height:1200px;border:0;" scrolling="no" {if $config->is_dev}allow="local-network-access"{/if}></iframe>
            {*include file='display_stages.tpl' current=1*}

			{if $error}
				<h2 class="text-red animate-flashing">{$error}</h2>
			{/if}

			{if $show_auto_confirm_2_asp}
				{include file='auto_confirm_2_asp.tpl'}
			{/if}

			{* Модалка и экран подписания кросс-ордера: рендерим в DOM скрытыми, JS управляет показом *}
			{if $should_render_cross_order}
				{include file="auto_confirm_2_cross_order_modal.tpl"}
				{include file="cross_order_nk_sign.tpl"}
			{/if}

			{if $show_select_bank_for_sbp}
				{assign var=__sbp_wrapper_style value=''}
				{if !empty($show_auto_confirm_2_asp)}
					{assign var=__sbp_wrapper_style value='display: none;'}
				{/if}
				<div id="sbp-bank-selection-wrapper" style="{$__sbp_wrapper_style}">
					{include file='sbp_bank_selection.tpl'}
				</div>
			{/if}

            {* Секция следующих шагов; при автоподписании скрываем до SMS *}
            <div id="card-add-section" style="{if $show_auto_confirm_2_asp}display:none;{/if}">
                {if $show_card_add}
					<h1 class="add_card__title">Добавьте свою карту для получения кэшбека 💳✨</h1>
                    {if $settings->b2p_enabled || $user->use_b2p}
                        <a href="#" class="button medium js-b2p-add-card" data-organization_id="{$organization_id}" onclick="sendMetric('reachGoal', 'etap-reg-karty')">Добавить карту</a>
                    {elseif $user->add_card}
                        <a href="{$user->add_card}" class="button medium" onclick="sendMetric('reachGoal', 'etap-reg-karty')">Добавить карту</a>
                    {/if}
                    <br/>
                    <p class="security-text">Данные защищены сквозным шифрованием и передаются по безопасному соединению</p>
				{elseif $show_sbp_attach}
					<div class="add-sbp">
						<h1>Регистрация банковской карты</h1>
						<p class="sbp_par">Платите быстро и удобно через СБП</p>
						<img class="sbp_logo" src="design/boostra_mini_norm/assets/image/sbp_logo.png" alt="СБП">
						<a class="button medium add-sbp-link" href="{$add_sbp_link}" target="_blank">Привязать счет СБП</a>
						<p class="add-sbp-error hidden"></p>
					</div>
					<br/>
                {/if}
				{if !$isSafetyFlow && $isAllowedTestLeadgid}
					<div id="not_checked_info" style="display:none">
						<strong style="color:#f11">Вы должны согласиться с договором и нажать "Добавить карту"</strong>
					</div>
                    {*  опасный флоу: видно и отмечено *}
					<div class="docs_wrapper">
						<div class="conditions" style="max-width: none;">
							<h3>Я согласен со следующим</h3>
							<div style="max-width: none;">
								<label class="spec_size">
									<div class="checkbox" style="border-width:1px;width:16px!important;height:16px!important;">
										<input id="recurrent" type="checkbox"
											name="recurring_consent" value="1" checked="checked"/>
										<span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
									</div>
								</label>
								<p>Я согласен с <a href="/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf" class="" target="_blank">cоглашением о применении регулярных (рекуррентных) платежах</a></p>
							</div>
							<div id="b2pay" style="max-width: none;">
								<label class="spec_size">
									<div class="checkbox" style="border-width:1px;width:16px!important;height:16px!important;">
										<input class="js-need-verify js-agree-claim-value" type="checkbox"
											name="agree_claim_value" value="0" checked="checked"/>
										<span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
									</div>
								</label>
								<p>Я согласен с <a href="files/docs/viploan/politika-bezopasnosti-platezhei-best2pay.pdf" class="" target="_blank">договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)</a></p>
							</div>
						</div>
					</div>
                {/if}
            </div>
		</div>
	</div>
</section>