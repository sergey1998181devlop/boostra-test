{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
	<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>

    <script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.7.5" type="text/javascript"></script>

	<script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.64" type="text/javascript"></script>

	<script src="design/{$settings->theme}/js/personal_data.app.js?v=1.73" type="text/javascript"></script>
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Адрес</h1>
			</hgroup>

			{include file='display_stages.tpl' current=4 percent=63 total_step=4}

			<form method="post" id="personal_data" class="js-send-feedback" data-target="etap-propiska">
				<div id="steps">

					{if $error == 'allready_exists'}
                    <div class="alert alert-danger">
                        Клиент с такими персональными данными уже зарегистрирован.
                        <br />
                        Просим связаться с нами по номеру 8 800 333 30 73.
                    </div>

                    {else}

					<fieldset style="display: block;;">

                        <input type="hidden" value="address_data" name="stage" />

						<div class="register">

                            <span class="title">Адрес прописки</span>

                            <label class="{if $error=='empty_Regregion'}error{/if} " id="regregion-label">
								<input type="text" name="Regregion" value="{$user->Regregion}" placeholder="" required="" rel="region" aria-required="true" data-selected = 'false'/>
                                <span class="floating-label">Регион</span>
                                {if $error=='empty_Regregion'}<span class="error">Укажите Регион в которой Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Regcity}readonly{/if} {if $error=='empty_Regcity'}error{/if}" id="regcity-label">
								<input type="text" name="Regcity" value="{$user->Regcity}" placeholder="" required="" rel="city" aria-required="true" data-selected = 'false'/>
                                <span class="floating-label">Населенный пункт</span>
                                {if $error=='empty_Regcity'}<span class="error">Укажите Населенный пункт в котором Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Regstreet}readonly{/if} {if $error=='empty_Regstreet'}error{/if}" id="regstreet-label">
								<input type="text" name="Regstreet" value="{$user->Regstreet}" placeholder="" rel="street" data-selected = 'false'/>
                                <span class="floating-label">Улица</span>
                                {if $error=='empty_Regstreet'}<span class="error">Укажите улицу на которой Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Reghousing}readonly{/if} {if $error=='empty_Reghousing'}error{/if}" id="reghousing-label">
								<input type="text" name="Reghousing" value="{$user->Reghousing}" placeholder="" required="" rel="house" aria-required="true" data-selected = 'false' />
                                <span class="floating-label">Номер дома</span>
                                {if $error=='empty_Reghousing'}<span class="error">Укажите номер дома в котором Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Regbuilding}readonly{/if} ">
								<input type="text" name="Regbuilding" value="{$user->Regbuilding}" placeholder="" class="adding sup" rel="building" aria-required="true" oninput="this.value = this.value.replace(/[^0-9]/g, '')"/>
							    <span class="floating-label">Строение</span>
                            </label>

							<label class="{if $user->Regroom}readonly{/if} ">
								<input type="text" name="Regroom" value="{$user->Regroom}" placeholder="" class="adding sup" rel="flat" aria-required="true"/>
							    <span class="floating-label">Номер квартиры</span>
                            </label>

                            <label class="big left">
								<div class="checkbox">
									<input type="checkbox" value="1" id="equal" name="equal" {if $equal}checked="true"{/if} />
									<span></span>
								</div> Адрес регистрации совпадает с адресом проживания
							</label>

						</div>

						<div class="living" id="living_block" {if $equal}style="display:none"{/if}>

                            <span class="title">Адрес проживания</span>

                            <label class="{if $error=='empty_Faktregion'}error{/if}" id="faktregion-label">
								<input type="text" name="Faktregion" value="{$user->Faktregion}" placeholder="" required="" rel="region" data-selected = 'false'/>
                                <span class="floating-label">Регион</span>
                                {if $error=='empty_Faktregion'}<span class="error">Укажите Регион фактического проживания</span>{/if}
							</label>

							<label class="{if $user->Faktcity}readonly{/if} {if $error=='empty_Faktcity'}error{/if}" id="faktcity-label">
								<input type="text" name="Faktcity" value="{$user->Faktcity}" placeholder="" required="" rel="city" data-selected = 'false' />
                                <span class="floating-label">Населенный пункт</span>
                                {if $error=='empty_Faktcity'}<span class="error">Укажите Населенный пункт фактического проживания</span>{/if}
							</label>

                            <label class="{if $user->Faktstreet}readonly{/if} {if $error=='empty_Faktstreet'}error{/if}" id="faktstreet-label">
								<input type="text" name="Faktstreet" value="{$user->Faktstreet}" placeholder="" rel="street" data-selected = 'false' />
                                <span class="floating-label">Улица</span>
                                {if $error=='empty_Faktstreet'}<span class="error">Укажите улицу фактического проживания</span>{/if}
							</label>

							<label class="{if $user->Fakthousing}readonly{/if} {if $error=='empty_Fakthousing'}error{/if}" id="fakthousing-label">
								<input type="text" name="Fakthousing" value="{$user->Fakthousing}" placeholder="" required="" rel="house" />
                                <span class="floating-label">Номер дома</span>
                                {if $error=='empty_Fakthousing'}<span class="error">Укажите номер дома фактического проживания</span>{/if}
							</label>

							<label class="{if $user->Faktbuilding}readonly{/if}">
								<input type="text" name="Faktbuilding" value="{$user->Faktbuilding}" placeholder="" class="adding sup" rel="building" oninput="this.value = this.value.replace(/[^0-9]/g, '')"/>
							    <span class="floating-label">Строение</span>
                            </label>

							<label class="{if $user->Faktroom}readonly{/if}">
								<input type="text" name="Faktroom" value="{$user->Faktroom}" placeholder="" class="adding sup" rel="flat" oninput="this.value = this.value.replace(/[^0-9]/g, '')"/>
							    <span class="floating-label">Номер квартиры</span>
                            </label>

							<input type="hidden" name="Regindex" id="prop_zip" value="{$user->Regindex}">

							<input type="hidden" name="Regregion_shorttype" id="regregion_shorttype" value="{$user->Regregion_shorttype}">
							<input type="hidden" name="Regcity_shorttype" id="regcity_shorttype" value="{$user->Regcity_shorttype}">
							<input type="hidden" name="Regstreet_shorttype" id="regstreet_shorttype" value="{$user->Regstreet_shorttype}">

							<input type="hidden" name="prop_okato" id="prop_okato" value="">
							<input type="hidden" name="prop_city_type" id="prop_city_type" value="">
							<input type="hidden" name="prop_street_type_long" id="prop_street_type_long" value="">
							<input type="hidden" name="prop_street_type_short" id="prop_street_type_short" value="">

                            <input type="hidden" name="Faktindex" id="prog_zip" value="{$user->Faktindex}">

							<input type="hidden" name="Faktregion_shorttype" id="faktregion_shorttype" value="{$user->Faktregion_shorttype}">
							<input type="hidden" name="Faktcity_shorttype" id="faktcity_shorttype" value="{$user->Faktcity_shorttype}">
							<input type="hidden" name="Faktstreet_shorttype" id="faktstreet_shorttype" value="{$user->Faktstreet_shorttype}">

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
		</div>
	</div>
</section>