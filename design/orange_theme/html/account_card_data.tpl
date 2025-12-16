{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Регистрация банковской карты</h1>
				<h5>Добавьте дебетовую (обычную) действующую карту, на которую мы сможем зачислить вам деньги.</h5>
			</hgroup>
			
            {*include file='display_stages.tpl' current=1*}
            
            <div>
				<br/>
				{if $isTestUser}
				<div id="not_checked_info" style="display:none">
					<strong style="color:#f11">Вы должны согласиться с договором и нажать "Добавить карту"</strong>
				</div>
				<div class="docs_wrapper">
					<div class="conditions" style="max-width: none;">
						<h3>Я согласен со следующим</h3>
						<div style="max-width: none;">
							<label class="spec_size">
								<div class="checkbox" style="border-width:1px;width:10px!important;height:10px!important;">
									<input id="recurrent" type="checkbox"
										name="recurring_consent" value="1" checked="checked"/>
									<span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
								</div>
							</label>
							<p>Я согласен с <a href="/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf" class="" target="_blank">cоглашением о применении регулярных (рекуррентных) платежах</a></p>
						</div>
						<div id="b2pay" style="max-width: none;">
							<label class="spec_size">
								<div class="checkbox" style="border-width:1px;width:10px!important;height:10px!important;">
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
                {if $settings->b2p_enabled || $user->use_b2p}
                    <a href="#" class="button medium js-b2p-add-card" onclick="sendMetric('reachGoal', 'etap-reg-karty')">Добавить карту</a>
                {else}
                    {if $user->add_card}
                        <a href="{$user->add_card}" class="button medium" onclick="sendMetric('reachGoal', 'etap-reg-karty')">Добавить карту</a>
                    {/if}
                {/if}
				<br/>
                <p>Данные защищены сквозным шифрованием и передаются по безопасному соединению</p>
                
            </div>
		</div>
	</div>
</section>