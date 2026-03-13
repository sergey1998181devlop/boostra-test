{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.7.5" type="text/javascript"></script>

	<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.13"/>
	<script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.64" type="text/javascript"></script>

    <script type="text/javascript" src="/js/autocomplete/jquery.autocomplete-min.js"></script>
    <link rel="stylesheet" href="/js/autocomplete/styles.css" />

    <script src="design/{$settings->theme}/js/additional_data.app.js?v=1.73" type="text/javascript"></script>

{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Завершите оформление и получите деньги</h1>
				<h5>Добавьте работу и второй контакт. Звоним только если не сможем связаться с Вами. Информацию по займу не разглашаем.</h5>
			</hgroup>

            {include file='display_stages.tpl' current=6 percent=91 total_step=6}

			<form method="post" id="additional_data" onsubmit="sendMetric('reachGoal', 'extra'); return true;">
				<div id="steps">

					<fieldset style="display: block;;">

                        <input type="hidden" value="additional_data" name="stage" />
                        <input type="hidden" value="" name="juicescore_session_id" id="juicescore_session_id" />
                        <input type="hidden" value="" name="juicescore_useragent" id="juicescore_useragent" />
                        <input type="hidden" name="finkarta_fp" id="finkarta_fp" value="" />
                        <input type="hidden" value="" name="local_time" id="local_time" />

                        <input type="hidden" value="1" name="service_insurance" id="service_insurance_hidden" />
                        <input type="hidden" value="1" name="service_reccurent" id="service_reccurent_hidden" />

                        <div class="clearfix">

                            {$other_work_scope_active = 1}
                            {if !$user->work_scope}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Работаю официально'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Работаю неофициально'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Безработный'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Пенсионер'}{$other_work_scope_active = 0}{/if}

    						<label id="work_scope_select" class="{if $error=='empty_work_scope'}error{/if}" {if $other_work_scope_active}style="display:none"{/if}>
    							<div class="select">
                                    <select name="work_scope" {if $other_work_scope_active}disabled="true"{/if}>
                                        <option value="Работаю официально" {if $user->work_scope == 'Работаю официально'}selected=""{/if}>Работаю официально</option>
                                        <option value="Работаю неофициально" {if $user->work_scope == 'Работаю неофициально'}selected=""{/if}>Работаю неофициально</option>
                                        <option value="Безработный" {if $user->work_scope == 'Безработный'}selected=""{/if}>Безработный</option>
                                        <option value="Пенсионер" {if $user->work_scope == 'Пенсионер'}selected=""{/if}>Пенсионер</option>
                                    </select>
                                </div>
                                <span class="floating-label">Занятость</span>
                                {if $error=='empty_work_scope'}<span class="error">Укажите сферу деятельности</span>{/if}
    						</label>

    						<label class=" js-pensioner-hidden {if $error=='empty_workplace'}error{/if}" {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>
    							<input type="text" name="workplace" value="{$user->workplace}" placeholder="" required=""/>
                                <span class="floating-label">Место работы</span>
                                {if $error=='empty_workplace'}<span class="error">Укажите сокращенное наименование организации</span>{/if}
                            </label>

    						<label class="js-pensioner-hidden {if $error=='empty_profession'}error{/if}" {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>
    							<input type="text" name="profession" value="{$user->profession}" placeholder="" required=""/>
                                <span class="floating-label">Ваша должность</span>
                                {if $error=='empty_profession'}<span class="error">Укажите Вашу должность</span>{/if}
                            </label>

                            <div style="clear:both"></div>

                            <label class="js-pensioner-hidden {if $error=='empty_work_phone'}error{/if}" {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>
    							<input type="text" class="" name="work_phone" value="{$user->work_phone}" placeholder="" required=""/>
                                <span class="floating-label">Телефон организации</span>
                                <small class="error">{if $error=='empty_work_phone'}Укажите рабочий телефон{/if}</small>
                            </label>

    						<label class=" js-pensioner-hidden {if $error=='empty_workdirector_name'}error{/if}" {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>
    							<input type="text" name="workdirector_name" value="{$user->workdirector_name}" placeholder="" required=""/>
                                <span class="floating-label">Руководитель</span>
                                {if $error=='empty_workdirector_name'}<span class="error">Укажите ФИО руководителя</span>{/if}
                            </label>

                            <label class="{if $error=='empty_income_base'}error{/if}">
    							<input type="text" class="js-digits" name="income_base" value="{$user->income_base}" placeholder="" required=""/>
                                <span class="floating-label">Доход в месяц</span>
                                <small class="error">{if $error=='empty_income_base'}Укажите основной доход{/if}</small>
                            </label>

                        </div>

                        <div class="register js-pensioner-hidden" {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>

                            <span class="title">Адрес Организации</span>

                            <label class=" {if $error=='empty_Regregion'}error{/if}">
								<input type="text" name="Regregion" value="{$user->Workregion}" placeholder="" required="" rel="region" aria-required="true"/>
                                <span class="floating-label">Область</span>
                                {if $error=='empty_Regregion'}<span class="error">Укажите область в которой расположена организация</span>{/if}
							</label>

							<label class="{if $user->Workcity}readonly{/if} {if $error=='empty_Regcity'}error{/if}">
								<input type="text" name="Regcity" value="{$user->Workcity}" placeholder="" required="" rel="city" aria-required="true"/>
                                <span class="floating-label">Город</span>
                                {if $error=='empty_Regcity'}<span class="error">Укажите город в котором расположена организация</span>{/if}
							</label>

							<label class="{if $user->Workstreet}readonly{/if} {if $error=='empty_Regstreet'}error{/if}">
								<input type="text" name="Regstreet" value="{$user->Workstreet}" placeholder="" rel="street"/>
                                <span class="floating-label">Улица</span>
                                {if $error=='empty_Regstreet'}<span class="error">Укажите улицу на которой расположена организация</span>{/if}
							</label>

							<label class="{if $user->Workhousing}readonly{/if} {if $error=='empty_Reghousing'}error{/if}">
								<input type="text" name="Reghousing" value="{$user->Workhousing}" placeholder="" required="" rel="house" aria-required="true"/>
                                <span class="floating-label">Дом</span>
                                {if $error=='empty_Reghousing'}<span class="error">Укажите номер дома в котором расположена организация</span>{/if}
							</label>

							<label class="{if $user->Workbuilding}readonly{/if} ">
								<input type="text" name="Regbuilding" value="{$user->Workbuilding}" placeholder="" class="adding sup" rel="building" aria-required="true"/>
							    <span class="floating-label">Строение</span>
                            </label>

							<label class="{if $user->Workroom}readonly{/if} ">
								<input type="text" name="Regroom" value="{$user->Workroom}" placeholder="" class="adding sup" rel="flat" aria-required="true"/>
							    <span class="floating-label">Офис</span>
                            </label>

							<input type="hidden" name="Regindex" id="prop_zip" value="{$user->Workindex}">

							<input type="hidden" name="Regregion_shorttype" id="regregion_shorttype" value="{$user->Regregion_shorttype}">
							<input type="hidden" name="Regcity_shorttype" id="regcity_shorttype" value="{$user->Regcity_shorttype}">
							<input type="hidden" name="Regstreet_shorttype" id="regstreet_shorttype" value="{$user->Regstreet_shorttype}">

						</div>


                        <div class="clearfix">

                            <span class="title">Дополнительный контакт</span>
                            <p style="margin-left:15px;text-align:left">Чтобы связаться с Вами, если основной телефон недоступен</p>
                            <label class="medium readonly {if $error=='empty_contact_person_name'}error{/if}">
                                <input type="text" class="js-cirylic" name="contact_person_name" value="{$user->contact_person_name}" placeholder="" required="true"/>
                                <span class="floating-label">ФИО контакного лица</span>
                                <small class="err error">{if $error=='empty_contact_person_name'}Укажите ФИО контакного лица{/if}</small>
                            </label>

                            <label class="readonly {if $error=='empty_contact_person_phone'}error{/if}">
                                <input type="text" class="" name="contact_person_phone" value="{$user->contact_person_phone}" placeholder="" required="true"/>
                                <span class="floating-label">Тел. контакного лица</span>
                                <small class="error">{if $error=='empty_contact_person_phone'}Укажите номер телефона контакного лица{/if}</small>
                            </label>

                        </div>

                           <label class="js-accept-block medium left {if $error=='empty_accept'}error{/if}" style="display:">
    							<div class="checkbox">
    								<input class="js-input-accept" type="checkbox" value="1" id="accept_check" name="accept" {if $accept}checked="true"{/if} />
    								<span></span>
    							</div> Я ознакомлен и согласен <a href="javascript:void(0);" id="accept_link">со следующим</a>
                                <span class="error">Необходимо согласиться с условиями</span>
    						</label>


						<div class="next">
                            {if $is_developer}
                            <a class="button big button-inverse" id="" href="account?step=files" >Назад</a>
                            {/if}
                            {if false}
                                <style>
                                    .service_insurance .col {
                                        display: inline-block;
                                        margin: 0 15px;
                                        width: 45%;
                                        vertical-align: top;
                                    }
                                    @media screen and (max-width: 768px){
                                        .service_insurance .col {
                                            width: 100%;
                                            padding-bottom: 20px;
                                            border-bottom: 1px solid;
                                        }
                                    }
                                    .service_insurance .buttons {
                                        height: 100px;
                                        line-height: 100px;
                                        margin-bottom: 20px;
                                    }
                                    .service_insurance .buttons .button {
                                        background: -webkit-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                        background: -o-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                        background: -ms-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                        background: -moz-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                        background: linear-gradient(94deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                        border: none;
                                    }
                                    .service_insurance .descriptions {
                                        text-align: justify;
                                    }
                                    .service_insurance .descriptions a {
                                        text-decoration: underline;
                                    }
                                </style>
                                <div class="service_insurance">
                                    <input type="hidden" id="service_insurance" name="service_insurance" value="0">
                                    <div class="col">
                                        <div class="buttons">
                                            <a href="" class="button big js-insurance">Получить заём на льготных условиях</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="buttons">
                                            <a href="" style="text-decoration: underline;">Заём на общих условиях</a>
                                        </div>
                                        <div class="descriptions">
                                            Обязуюсь исполнить обязательства по займу без возникновения просроченной задолженности.
                                            Беру на себя возможные риски, связанные с потерей моей трудоспособности в случае моей
                                            смерти, а также получения инвалидности (I) и (II) групп
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    $(document).ready(function () {
                                        $('.service_insurance .buttons a').on('click', function () {
                                            $('#service_insurance').val(+$(this).is('.js-insurance'));

                                            $(this).closest('form').submit();

                                            return false;
                                        });
                                    });
                                </script>
                            {else}
                                <button class="button big" id="doit" type="submit" name="neworder">Получить деньги</button>
                            {/if}
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
</section>


<div class="hidden">

    <div id="accept" class="white-popup mfp-hide">

        <div id="modal_error" style="display:none">
            <strong style="color:#f11">Необходимо согласиться с условиями</strong>
        </div>

        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1" id="agreed_1" name="agreed_1" checked="" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>
			<a href="http://www.boostra.ru/files/docs/obschie-usloviya.pdf" target="_blank">Общие условия договора потребительского микрозайма</a>
        </div>
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1" id="agreed_2" name="agreed_2" checked="" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>
			<a href="https://www.boostra.ru/preview/ind_usloviya?user_id={$user->id}" target="_blank">Типовая форма Договора займа</a>
        </div>
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1" id="agreed_3" name="agreed_3" checked="" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>
            <a href="user/docs?action=micro_zaim" target="_blank" class="micro-zaim-doc-js">ЗАЯВЛЕНИЕ
                о предоставлении микрозайма</a>
            <script defer>
                $('a.micro-zaim-doc-js').mousedown(function (e) {
                    e.preventDefault();
                    let loanAmount = $('#calculator .total').text();
                    if (!loanAmount) {
                        loanAmount = $('#approve_max_amount').text();
                    }
                    let is_user_credit_doctor = $('#credit_doctor_check').is(':checked') ? 1 : 0;
                    let newUrl = $(this).attr('href') + '&loan_amount=' + loanAmount + '&credit_doctor=' + is_user_credit_doctor;
                    window.open(newUrl, '_blank');
                })
            </script>
        </div>
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1" id="agreed_4" name="agreed_4" checked="" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>
			<a href="http://www.boostra.ru/files/docs/%d0%bf%d1%80%d0%b0%d0%b2%d0%b8%d0%bb%d0%b0%20%d0%bf%d1%80%d0%b5%d0%b4%d0%be%d1%81%d1%82%d0%b0%d0%b2%d0%bb%d0%b5%d0%bd%d0%b8%d1%8f%20%d0%b7%d0%b0%d0%b9%d0%bc%d0%be%d0%b2%2001.04.2022.pdf" target="_blank">ПРАВИЛА ПРЕДОСТАВЛЕНИЯ ЗАЙМОВ ООО МКК "Аквариус"</a>
        </div>
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1" id="agreed_5" name="agreed_5" checked="" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>
			<a href="http://www.boostra.ru/files/docs/politikakonfidentsialnosti.pdf" target="_blank">ПОЛИТИКА КОНФИДЕНЦИАЛЬНОСТИ ООО МКК "Аквариус"</a>
        </div>
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1" id="agreed_6" name="agreed_6" checked="" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>
			<a href="http://www.boostra.ru/files/docs/informatsiya-ob-usloviyah-predostavleniya-ispolzovaniya-i-vozvrata-potrebitelskogo-mikrozajma-mkk-ooo-bustra.pdf" target="_blank">
                ИНФОРМАЦИЯ ОБ УСЛОВИЯХ ПРЕДОСТАВЛЕНИЯ, ИСПОЛЬЗОВАНИЯ И ВОЗВРАТА ПОТРЕБИТЕЛЬСКОГО МИКРОЗАЙМА ООО МКК "Аквариус"
            </a>
        </div>
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1" id="agreed_8" name="agreed_8" checked="" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>
			<a href="http://www.boostra.ru/files/docs/ofertanaperevods2askomissiej.pdf" target="_blank">
                ПУБЛИЧНАЯ ОФЕРТА об условиях предоставления АО «Тинькофф Банк» услуг по переводам
            </a>
        </div>
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1" id="agreed_9" name="agreed_9" checked="" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>
			<a href="http://www.boostra.ru/files/docs/soglasie-klienta-na-poluchenie-informatsii-iz-byuro-kreditnyh-istorij.pdf" target="_blank">
                Согласие клиента на получение информации из бюро кредитных историй
            </a>
        </div>
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1" id="agreed_10" name="agreed_10" checked="" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>
			<a href="http://www.boostra.ru/files/docs/soglashenie-ob-ispolzovanii-analoga-sobstvennoruchnoj-podpisi-pri-distantsionnom-vzaimodejstvii-mkk-ooo-bustra.pdf" target="_blank">
                СОГЛАШЕНИЕ ОБ ИСПОЛЬЗОВАНИИ АНАЛОГА СОБСТВЕННОРУЧНОЙ ПОДПИСИ ПРИ ДИСТАНЦИОННОМ ВЗАИМОДЕЙСТВИИ ООО МКК "Аквариус"
            </a>
        </div>


        <div>
            <label class="spec_size">
                <div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
                    <input class="js-service-recurent" type="checkbox" value="1" id="service_recurent_check" name="service_recurent" checked=""/>
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>
                Согласен на подключение услуги реккурентных платежей, предоставляющейся в соответствии с <a class="block_1" href="#" target="_blank">"Соглашением"</a>

				<script>
				$(document).ready(function(){
					$('.block_1').click(function(){
						$('.content_block_1').slideToggle(300);
						return false;
					});
				});
				</script>

        </div>

		<button title="%title%" type="button" class="mfp-close" style="color: #fff;font-size: 20px;background: green;width: 48px;padding: 10px;height: 48px;right: 10px;">ОК</button>
    </div>

    <script>
        // открытие модалки с правилами
        $('#accept_link').click(function(e){
            e.preventDefault();
        	$.magnificPopup.open({
        		items: {
        			src: '#accept'
        		},
        		type: 'inline',
                showCloseBtn: false
        	});
        });

    </script>

</div>

<script>
 window.addEventListener('sessionready', function(e){
     $('#juicescore_useragent').val(navigator.userAgent)

     if (typeof FingerprintID !== 'undefined' && FingerprintID)
         $('#finkarta_fp').val(FingerprintID);
 })

</script>
