{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.magnific-popup.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.75" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/loan.app.js?v=1.79" type="text/javascript"></script>
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup class="js-base-title">
				<h1>Заявка на {$amount} Р сроком на {$period} дней</h1>
			</hgroup>
			<hgroup class="js-sms-title" style="display:none">
				<h1>Подтвердите телефон</h1>
				<h5>кодом из СМС, который отправлен на номер <span class="js-phone-number"></span></h5>
			</hgroup>
			
            {*
            <div class="stages">
                <ul>
                    <li class="current"><span>Контактная информация</span></li>
                    <li><span>Паспортные данные</span></li>
                    <li><span>Дополнительная информация</span></li>
                    <li><span>Идентификация</span></li>
                    <li><span>Получение денег</span></li>
                </ul>
            </div>
            *}
            {include file='display_stages.tpl' current=1 percent=10 total_step=4}
            
			<form method="post" id="loan_form" action="neworder" class=""> 
				<div id="steps">
					<fieldset style="display:block">
                        
                        <div class="step1">
                            <input type="hidden" name="amount" value="{$amount|escape}" />
                            <input type="hidden" name="period" value="{$period|escape}" />

                            <input type="hidden" name="service_recurent" value="1" />
                            <input type="hidden" name="service_sms" value="0" />
                            <input type="hidden" name="service_insurance" value="1" />
                            <input type="hidden" name="service_reason" value="0" />
							{if ($user_return_credit_doctor)}
								<input type="hidden" name="service_doctor" value="0" />
							{else}
								<input type="hidden" name="service_doctor" value="1" />
							{/if}

                            <div class="clearfix">
                                <label class="{if $error=='empty_lastname'}error{/if}">
        							<input autocomplete type="text" class="js-camelcase js-cirylic" name="lastname" id="lastname" placeholder="" value="{if $is_developer}Тестовый{else}{$lastname|escape}{/if}" required="" aria-required="true">
        							<small class="err error" id="err-lastname">{if $error=='empty_lastname'}Укажите Вашу фамилию{/if}</small>
        							<span class="floating-label">Фамилия</span>
        						</label>
        						<label class="{if $error=='empty_firstname'}error{/if}">
        							<input autocomplete type="text" class="js-camelcase js-cirylic" name="firstname" id="firstname" placeholder="" value="{if $is_developer}Тест{else}{$firstname|escape}{/if}" required="" aria-required="true">
        							<small class="err error" id="err-firstname">{if $error=='empty_firstname'}Укажите Ваше имя{/if}</small>
        							<span class="floating-label">Имя</span>
        						</label>
        						<label class="{if $error=='empty_patronymic'}error{/if}">
        							<input autocomplete type="text" class="js-camelcase js-cirylic" name="patronymic" id="patronymic" placeholder="" value="{if $is_developer}Тестович{else}{$patronymic|escape}{/if}" required="" aria-required="true">
        							<small class="err error" id="err-patronymic">{if $error=='empty_patronymic'}Укажите Ваше отчество{/if}</small>
        							<span class="floating-label">Отчество</span>
        						</label>
                            </div>
                            
                            <div class="clearfix">
								{if $user_modal_phone}
									<input type="hidden" class="js-input-phone ym-record-keys" name="phone" value="{$user_modal_phone}" />
								{else}
									<label class="js-phone-block {if $error=='empty_phone'}error{/if}">
										<input class="js-input-phone ym-record-keys" type="tel" name="phone" id="phone" placeholder="" value="{$phone|escape}" required="" aria-required="true">
										<small class="err error" id="err-phone">{if $error=='empty_phone'}Укажите корректный телефон{/if}</small>
										<span class="floating-label">Мобильный телефон</span>
									</label>
								{/if}
                                
                                <label class="{if $error=='empty_birth'}error{/if}">
        							<input type="text" name="birthday" id="birthday" value="{$birth}" placeholder="" required="" value="{if $is_developer}01.01.2000{/if}"/>
                                    <span class="floating-label">Дата рождения</span>
                                    {if $error=='empty_birth_place'}<span class="error">Укажите дата рождения</span>{/if}
        						</label>
    
                            </div>
                            
                            {if 1}
                            <label class="js-accept-block big left {if $error=='empty_accept'}error{/if}" style="margin-top:1rem">
    							<div class="checkbox">
    								<input class="js-need-verify" type="checkbox" value="1" id="" name="accept" />
    								<span></span>
    							</div> Я согласен на <a href="preview/soglasie_obrabotka" target="_blank" class="js-open-soglasie js-checkbox-required" data-tpl="soglasie_obrabotka">обработку персональных данных</a>
                                <span class="error">Необходимо согласиться с обработкой персональных данных</span>
    						</label>
                            {/if}
                            
                            <label class="js-info-block big" style="display:none"></label>
    						<div class="next">

								{if $smarty.cookies.utm_source != 'leadgid' && $captcha_status}
                                	<div  id="recaptcha_register"></div>
								{/if}

                                <button class="button big js-send-code" type="button">Далее</button>
    						</div>
                        
                        </div>
                        <div class="step2" style="display:none">
                            
                            <input type="hidden" id="phone_checked" value="0" />
                            
                            <div class="clearfix">
                                
                                <label class="js-info-block medium" style="display:none"></label>
                                
                                
    
                                <label class="js-code-block {if $error=='error_code'}error{/if}" >
        							<input autofocus autocomplete="one-time-code" class="js-input-code" type="text" name="code" id="code" placeholder="" value="" required="" aria-required="true">
        							<small class="err error" id="err-code">{if $error=='error_code'}Код не верный{/if}</small>
        							<span class="floating-label">Код из СМС</span>
        						</label>
    
                                <label class="js-send-block">
                                    <div class="repeat_sms js-send-repeat"></div>
                                    <button class="button small js-send-again" type="button" style="display:none">Отправить код еще раз</button>
                                </label>
                            </div>
                            
                            {if 0}
                            <label class="js-accept-block medium left {if $error=='empty_accept'}error{/if}" style="display:">
    							<div class="checkbox">
    								<input class="js-input-accept" type="checkbox" value="1" id="accept_check" name="accept" {if $accept}checked="true"{/if} />
    								<span></span>
    							</div> Я ознакомлен и согласен <a href="javascript:void(0);" id="accept_link">со следующим</a>
                                <span class="error">Необходимо согласиться с условиями</span>
    						</label>
                            {/if}
                            
    						<div class="next js-submit-block" style="display:">
    							<button class="button big" type="submit">Далее</button>
    						</div>
                        </div>
					</fieldset>
					
				</div>
			</form>
		</div>
	</div>
</section>

<div class="hidden">
    
    {if 1}
    <div id="accept" class="white-popup mfp-hide">
        <p>
            Я не являюсь должностным лицом, супругом или родственником должностного лица, 
            указанным в ст.7.3 Федерального закона №115-ФЗ от 07.08.2001г.
        </p>
        <p>
            Я не буду действовать к выгоде другого лица при проведении сделок и иных операций
        </p>
        <p>
            У меня отсутствует бенефициарный владелец - стороннее физическое лицо, а также представитель отсутствует
        </p>
        <p>
            Настоящим я подтверждаю свое ознакомление и согласие с :
        </p>
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-bki js-need-verify" type="checkbox" value="1" id="agreed_bki" name="agreed_bki" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div> 
            </label>
<a href="https://www.boostra.ru/files/docs/polozhenie-o-poryadke-sbora-obrabotki-hraneniya-personalnyh-dannyh-i-inoj-informatsii.pdf" target="_blank">
                    Положение о порядке сбора, обработки, хранения персональных данных и иной информации
                </a>
        </div>
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
				    <input class="js-agreeed-bki js-need-verify" type="checkbox" value="1" id="agreed_bki1" name="agreed_bki" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div> 
            </label>
<a href="https://www.boostra.ru/files/docs/soglasie-na-obrabotku-personalnyh-dannyh.pdf" target="_blank">
                    Согласие_на_обработку_персональных_данных
                </a>
        </div>
        <div id="modal_error" style="display:none;color:#f11">
            <strong>Необходимо согласиться с условиями</strong>
        </div>
        

		<button title="%title%" type="button" class="mfp-close" style="color: #fff;font-size: 20px;background: green;width: 48px;padding: 10px;height: 48px;right: 10px;">ОК</button>
    </div>    
    {else}
    
    <div id="accept" class="white-popup mfp-hide">
        <p>
            Я не являюсь должностным лицом, супругом или родственником должностного лица, 
            указанным в ст. 7.3 Федерального закона №115-ФЗ от 07.08.2001г.
        </p>
        <p>
            Я не буду действовать к выгоде другого лица при проведении сделок и иных операций
        </p>
        <p>
            У меня отсутствует бенефициарный владелец - стороннее физическое лицо, а также представитель отсутствует
        </p>
        <p>
            Настоящим я подтверждаю свое ознакомление и согласие с ниже представленными документами, 
            а также подтверждаю их подписание с использованием аналога собственноручной подписи:
        </p>
        <ul style="padding-left:0px;list-style:none;">
            {foreach $docs as $doc}
            {if $doc->in_register}
            <li>
				{if $is_developer || $is_admin || $is_CB}
					<input class="js-service-doctor" type="checkbox" value="1" id="service_doctor_check" name="service_doctor" />
				{/if}
                <div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
        			<span style="opacity:1;margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
                <a href="{$config->root_url}/{$config->docs_files_dir}{$doc->filename}" target="_blank">{$doc->name|escape}</a>
            </li>
            {/if}
            {/foreach}
        </ul>

		{if !($user_return_credit_doctor)}
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
					{if $user->id == 81199 || $is_admin || $is_CB}
						<input class="js-service-doctor" type="checkbox" value="1" id="service_doctor_check" name="service_doctor" />
					{else}
						<input class="js-service-doctor" type="checkbox" value="1" id="service_doctor_check" name="service_doctor" checked="true"  />
					{/if}
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div> 
            </label>
			Я выражаю своё согласие на подписку на сервис "Кредитный доктор" в случае, если я получу отказ в займе.
        </div>
		{else}
		<input type="checkbox" value="0" id="service_doctor_check" name="service_doctor" style="display:none" />
		{/if}

		<div>
			<label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
					{if $user->id == 81199 || $is_admin || $is_CB}
						<input class="js-service-insurance" type="checkbox" value="1" id="service_insurance_check" name="service_insurance" />
					{else}
						<input class="js-service-insurance" type="checkbox" value="1" id="service_insurance_check" name="service_insurance" checked="true"  />
					{/if}
					<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
				</div>
			</label>
			Выражаю свое желание заключить договор страхования  соответственно <a href="https://boostra.ru/files/docs/pravila--195-kombinirovannogo-strahovaniya-ot-neschastnyh-sluchaev-i-boleznej.pdf" target="_blank">правилам</a>,
			страховая премия рассчитывается индивидуально, срок страхования 30 дней, страховая сумма 200% от суммы полученного займа
		</div>

        <div>
            <label class="spec_size">
                <div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
					{if $user->id == 81199 || $is_admin || $is_CB}
						<input class="js-service-recurent" type="checkbox" value="1" id="service_recurent_check" name="service_recurent"/>
					{else}
						<input class="js-service-recurent" type="checkbox" value="1" id="service_recurent_check" name="service_recurent"  checked="true"/>
					{/if}
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div> 
            </label>    
                Согласен на подключение услуги реккурентных платежей, предоставляющейся в соответствии с <a class="block_1" href="#" target="_blank">"Соглашением"</a>.
				<div class="content_block_1">
					<div>Соглашение о регулярных (рекуррентных) платежах МКК ООО «БУСТРА»&nbsp;
					<p style="text-align: right;">
						&nbsp;г. Самара, 2020 г.&nbsp;
					</p>
					 Микрокредитная компания Общество с ограниченной ответственностью «Бустра», ОГРН 1146317004030, именуемое в дальнейшем Займодавец, в лице директора Смелова С.Б., предлагает Клиентам при заключении договора займа через Сайт <a href="https://boostra.ru">https://boostra.ru</a> воспользоваться сервисом оплаты своих обязательств банковской картой по договору займа путем безакцептного (автоматического) списания денежных средств с банковской карты Клиента, указанной последним при регистрации и подаче заявки на заём (далее — Сервис «Рекуррентные платежи») и заключить настоящее Cоглашение о регулярных (рекуррентных) платежах (далее — Соглашение) на следующих нижеуказанных условиях:&nbsp;<br>
					 &nbsp;<br>
					 <b>1. Термины и определения&nbsp;</b><br>
					 &nbsp;<br>
					 <b>Клиент</b> — физическое лицо, заключающее договор займа в электронной форме;&nbsp;&nbsp;<br>
					 &nbsp;<br>
					 <b>Займодавец </b>— Микрокредитная компания Общество с ограниченной ответственностью «Бустра»;&nbsp;<br>
					 &nbsp;<br>
					 <b>Сайт Займодавца</b> — <a href="https://boostra.ru">https://boostra.ru</a>;&nbsp;&nbsp;<br>
					 &nbsp;<br>
					 <b>Договор займа</b> — договор займа, заключаемый между Клиентом и Займодавцом в электронной форме;&nbsp;<br>
					 &nbsp;<br>
					 <b>Банк</b>&nbsp; —&nbsp; кредитная&nbsp; организация,&nbsp; осуществляющая&nbsp; эмиссию&nbsp; Банковских&nbsp; карт&nbsp; на&nbsp; территории Российской Федерации в соответствии с законодательством Российской Федерации и на основании договоров с Клиентами Банка;&nbsp;<br>
					 &nbsp;<br>
					 <b>Банковская карта</b>&nbsp; —&nbsp; расчётная или&nbsp; кредитная&nbsp; карта,&nbsp; эмитентом&nbsp; которой&nbsp; является кредитная организация, являющаяся инструментом безналичных расчётов, предназначенная для совершения Клиентами Банка операций с денежными средствами, находящимися у Банка на Банковских счетах Клиентов Банка или с денежными средствами, предоставленными Банком в кредит Клиентам Банка в соответствии с законодательством Российской Федерации, а также договором банковского счёта, или в пределах установленного лимита, в соответствии с условиями кредитного договора между Банком и Клиентом Банка;&nbsp;<br>
					 &nbsp;<br>
					 <b>Заявка на заём</b> (далее — Заявка) — заявка Клиента на заключение договора займа, оформленная Клиентом на Сайте Займодавца путем использования формы Займодавца с указанием требуемых данной формой сведений и подписанное простой электронной подписью Клиента;&nbsp;<br>
					 &nbsp;<br>
					 <b>Клиент Банка</b> — физическое лицо, заключившее с Банком договор банковского счёта, и являющееся держателем Банковской карты международных платёжных систем VISA International, MasterCard, АО «Национальная система платежных карт» (НСПК) – МИР»;&nbsp;<br>
					 &nbsp;<br>
					 <b>Электронная подпись</b> — информация в электронной форме, которая присоединена к другой информации в электронной форме (подписываемой информации) или иным образом связана с такой информацией и которая используется для определения лица, подписывающего информацию; Код электронной подписи — одноразовая случайно сгенерированная парольная комбинация, отправляемая по SMS на указанный Клиентом номер мобильного телефона, ограниченная по времени использования и предназначенная для подтверждения подлинности Клиентом при осуществлении электронной подписи;&nbsp;<br>
					 &nbsp;<br>
					 <b>PAN</b> — 16-ти значный номер банковской карты;&nbsp;<br>
					 &nbsp;<br>
					 <b>Expiry</b> — срок действия банковской карты;&nbsp;<br>
					 &nbsp;<br>
					 <b>CVV2 (CVC2)</b> — код проверки подлинности банковской карты.&nbsp;<br>
					 &nbsp;<br>
					 &nbsp;<br>
					 <b>2. Описание сервиса «Рекуррентные платежи» и способ его активации.&nbsp;</b><br>
					 &nbsp;<br>
					 2.1. Сервис «Рекуррентные платежи» позволяет Клиенту производить уплату услуг обязательств (сумма основного долга и начисленные проценты) по Договору потребительского займа путём автоматического списания денежных средств с банковской карты Клиента в сумме, и по графику, указанным в Индивидуальных условиях Договора потребительского займа.&nbsp;<br>
					 &nbsp;<br>
					 2.2. Все расчеты по Банковской карте, предусмотренные настоящим Соглашением, производятся в рублях Российской Федерации.&nbsp;<br>
					 &nbsp;<br>
					 2.3. Плата за использование Сервиса «Рекуррентные платежи» (в том числе действия по его активации/отключению) не взимается.&nbsp;<br>
					 &nbsp;<br>
					 2.4. Активация (подключение) Сервиса «Рекуррентные платежи».&nbsp;<br>
					 &nbsp;<br>
					 2.4.1. При прохождении регистрации на Сайте Займодавца Клиент осуществляет привязку своей банковской карты, для возможности использования Сервиса «Рекуррентные платежи».&nbsp;<br>
					 &nbsp;<br>
					 2.4.2. После выбора суммы и срока займа, Клиент заполняет данные, необходимые для формирования Заявки на заём в целях заключения Договора потребительского займа.&nbsp;<br>
					 &nbsp;<br>
					 2.4.3. После заполнения необходимых данных и привязки банковской карты, на номер телефона, указанный Клиентом при заполнении Заявки на заём, приходит Код электронной подписи, который Клиент вводит в соответствующее окно ввода Кода электронной подписи.&nbsp;<br>
					 &nbsp;<br>
					 2.4.4. Подписывая Заявку на заём электронной подписью Клиент подтверждает достоверность предоставленных данных, свое согласие с условиями займа; согласие присоединиться к соглашению об использовании аналога собственноручной подписи и/или соглашению-оферты.&nbsp;<br>
					 &nbsp;<br>
					 2.4.5. После подачи Заявки на заём Займодавец рассматривает заявку и в случае принятия положительного решения Клиенту отправляется соответствующее SMS-сообщение и оферта на предоставление займа. В случае согласия с общими условиями займа, индивидуальными условиями займа, с соглашением о регулярных (рекуррентных) платежах, соглашением-офертой об оказании услуг Клиент подписывает оферту используя аналог собственноручной подписи (электронную подпись) - SMS Код.&nbsp;<br>
					 &nbsp;<br>
					 2.4.6. После совершения действий, указанных в п. 2.4.5. настоящего Соглашения, Сервис «Рекуррентные платежи» считается активированным (подключенным).&nbsp;<br>
					 &nbsp;<br>
					 2.5. Порядок пользования Сервисом «Рекуррентные платежи».&nbsp;<br>
					 &nbsp;<br>
					 2.5.1. Начиная со дня погашения займа по Договору потребительского займа, с Банковской карты Клиента, которая им была привязана в процессе оформления заявки на заём по Договору потребительского займа в автоматическом порядке могут быть списаны денежные средства в размере суммы общей задолженности, суммы основного долга или суммы процентов, начисленных на дату списания в соответствии с Договором потребительского займа.&nbsp;<br>
					 &nbsp;<br>
					 2.5.2. Непосредственное списание денежных средств осуществляет Банк-эмитент. Запрос на списание денежных средств в Банк-эмитент передаёт АО «Тинькофф Банк» ИНН 7710140679 (далее - «Оператор») в рамках заключённого с Займодавцем договора.&nbsp;<br>
					 &nbsp;<br>
					 2.5.3. Все расчёты с использованием Банковской карты, предусмотренные настоящим Соглашением, производятся в рублях Российской Федерации.&nbsp;<br>
					 &nbsp;<br>
					 2.5.4. Займодавец не хранит и не обрабатывает данные Банковских карт Клиентов, обеспечивая лишь направление запросов к Оператору для повторного проведения операции по Банковской карте Клиента.&nbsp;<br>
					 2.5.5. Займодавец ни при каких условиях не гарантирует возможность проведения операций по Банковской карте Клиента, оставляя разрешение данных вопросов за Оператором и Банкомэмитентом.&nbsp;<br>
					 &nbsp;<br>
					 2.5.6. Клиент гарантирует, что он является держателем Банковской карты, которую он привязал в процессе регистрации, осознанно, корректно и полностью вводил все требуемые реквизиты Банковской карты при активации (подключении) Сервиса «Рекуррентные платежи».&nbsp;<br>
					 &nbsp;<br>
					 2.5.7. При недостаточности на Банковской карте Клиента денежных средств для уплаты суммы общей задолженности, суммы основного долга или суммы начисленных процентов по Договору потребительского займа на дату списания денежных средств, Сервис «Рекуррентные платежи» автоматически посылает запрос на списание суммы общей задолженности, суммы основного долга или суммы начисленных процентов каждый последующий день до полного исполнения клиентом своих обязательств по договору займа.&nbsp;<br>
					 &nbsp;<br>
					 <b>3. Права и обязанности Сторон.&nbsp;</b><br>
					 &nbsp;<br>
					 3.1. Займодавец обязуется предоставить Клиенту возможность активации (подключения) Сервиса «Рекуррентные платежи» для совершения Клиентом платежей по Договору потребительского займа и/или оплаты услуг.&nbsp;<br>
					 &nbsp;<br>
					 3.2. Займодавец имеет право вносить изменения в настоящее Соглашение, заранее уведомив об этом Клиента в письменной форме, либо иным доступным способом, в том числе путём сообщения на электронную почту или телефон, указанные в Договоре потребительского займа.&nbsp;<br>
					 &nbsp;<br>
					 3.3. Займодавец не несёт ответственности за временную неработоспособность Сервиса «Рекуррентные платежи». В этом случае Клиент использует иные, согласованные с Займодавцем способы внесения средств для оплаты по Договору потребительского займа.&nbsp;<br>
					 &nbsp;<br>
					 3.4. В случае утраты/замены Клиентом Банковской карты, он обязан незамедлительно устно и в течение 3 (Трех) дней со дня утраты письменно известить об этом Займодавца с целью исключения реквизитов утраченной платёжной банковской карты и/или указания реквизитов новой карты. Полученное Займодавцем заявления Клиента об утрате платёжной банковской карты является основанием для приостановления операций по утраченной банковской карте.&nbsp;<br>
					 &nbsp;<br>
					 3.5. Клиент имеет право в любое время включить или отключить Сервис «Рекуррентные платежи» в «Личном кабинете» на сайте Займодавца путем установки или снятия соответствующей галочки, в случае отсутствия технической возможности осуществить вышеперечисленные действия Клиент имеет возможность направить Обществу, а также лицу, действующему от его имени и (или) в его интересах, соответствующее уведомление через нотариуса или по почте заказным письмом с уведомлением о вручении или путем вручения под расписку Обществу, а также лицу, действующему от его имени и (или) в его интересах.&nbsp;<br>
					 &nbsp;<br>
					 <b>4. Срок действия соглашения, порядок изменения и расторжения соглашения.&nbsp;</b><br>
					 &nbsp;<br>
					 4.1. Ответственность Займодавца перед Клиентом по настоящему Соглашению ограничивается суммой денежных средств, зачисленных с использованием Сервиса «Рекуррентные платежи» на счёт Займодавца для оплаты по Договору потребительского займа и/или услуги.&nbsp;<br>
					 &nbsp;<br>
					 4.2. Споры сторон, возникшие в связи с выполнением условий настоящего Соглашения, разрешаются в ходе взаимных консультаций и переговоров.&nbsp;<br>
					 &nbsp;<br>
					 <b>5. Прочие условия.&nbsp;</b><br>
					 &nbsp;<br>
					 5.1. Права и обязанности, вытекающие из настоящего Соглашения, не могут быть переданы третьим лицам без письменного согласия сторон.&nbsp;<br>
					 &nbsp;<br>
					 5.2. Совершая действия по активации (подключению) Сервиса «Рекуррентные платежи», Клиент признает действия по автоматическому списанию денежных средств с его Банковской карты в пользу оплаты суммы основного долга и суммы начисленных процентов по Договору займа, на основании заранее данного согласия (акцепта), в соответствии с п.3 ст.438 ГК РФ, путем подписания данного соглашения.&nbsp;<br>
					 &nbsp;<br>
					</div>
				</div>
				<script>
				$(document).ready(function(){
					$('.block_1').click(function(){
						$('.content_block_1').slideToggle(300);      
						return false;
					});
				});
				</script>
            
        </div>
        {*
        <div>
            <label class="spec_size">
                <div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 8px;">
        			<input class="js-service-sms" type="checkbox" value="1" id="service_sms_check" name="service_sms" checked="true" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div> Cогласен на подключение услуги смс-информирование, предоставляющейся в соответствии с <a class="block_2" href="#" target="_blank">"Положением об смс информировании"</a>
				<div class="content_block_2">
					<div>Положение о дополнительных &nbsp; &nbsp;финансовых услугах МКК ООО «Бустра»&nbsp;&nbsp;«СМС-ИНФОРМИРОВАНИЕ»&nbsp;&nbsp;
						<p style="text-align: right;">
							г. Самара, 2020 г.
						</p>
						 <b>
						<p style="text-align: center;">
							<u>Общие положения</u>
						</p>
						</b><br>
						 Настоящий документ (далее – Положение) разработан с целью получения дополнительной финансовой прибыли организации МКК ООО «Бустра».<br>
						 Настоящее Положение является документом с публичным доступом, подлежащим обязательному размещению на официальном сайте Общества.<br>
						 <b><u>
						<p style="text-align: center;">
							Основные понятия:
						</p>
						</u></b><br>
						 <b>Заявитель/Клиент</b> – лицо либо его представитель, обратившееся в Общество.<br>
						 <b>Общество</b> – Микрокредитная компания Общество с ограниченной ответственностью «Бустра».<br>
						 <b>Получатель финансовой услуги (Клиент)</b> - физическое лицо, обратившиеся в Общество с намерением получить, получающее или получившее финансовую услугу.<br>
						<b>Рабочее время</b> – промежуток рабочего времени которым определяется порядок оказываемых Обществом услуг Клиентам, с 8.00 до 18.00 по МСК времени.<br>
						&nbsp;<b><u>
						<p style="text-align: center;">
							Условия предоставления услуги «СМС-ИНФОРМИРОВАНИЕ»
						</p>
						</u></b><br>
						&nbsp;Услуга «СМС-ИНФОРМИРОВАНИЕ» (далее «Услуга») предоставляется в целях информирования клиента о статусе рассмотрения заявки на выдачу займа, платежах по договору потребительского займа, акциях и иных услугах, оказываемых МКК ООО «Бустра».<br>
						 Услуга является дополнительной, не обязательной, и не оказывает влияние на требования к заемщикам, решение о выдаче займа и условия заключения договора.<br>
						 Оказание услуги происходит после волеизъявления клиента путем совершения действий, направленных на добровольное получение услуги. Стоимость услуги составляет 199 рублей, включая НДС.<br>
						 Действие Услуги начинается с даты самостоятельного подключения услуги клиентом на сайте <a href="http://www.boostra.ru">www.boostra.ru</a> и заканчивается датой исполнения всех обязательств по договору потребительского займа и/или датой получения от клиента заявления на отказ от Услуги.<br>
						 Услуга включает в себя:<br>
						 -дату платежа;<br>
						&nbsp;-сумму платежа;<br>
						&nbsp;-напоминание об оплате платежа за день до оплаты;<br>
						- информирование клиента об акциях и финансовых продуктах Общества;<br>
						- информирование клиентов об индивидуальных предложениях для клиентов;<br>
						- информирование о возможности пропуска платежа (Услуга «Пропускаю платеж»);<br>
						- информирование о возможности подачи заявление на реструктуризацию займа; <br>
						-оплата всех исходящих смс от Общества.<br>
						 Отказ от получения услуги возможен путем направления письменного обращения по адресу электронной почты <a href="mailto:info@boostra.ru">info@boostra.ru</a>. Возврат денежных средств после отказа от Услуги возможен только в случае неоказания данной Услуги получателю, т.е. в случаях, когда от МКК ООО «Бустра» в адрес получателя не было направлено хотя бы одно СМСсообщение. После оказания Услуги возврат денежных средств не осуществляется, однако оказание Услуги прекращается.<br>
						 Заявление на отказ от Услуги должно содержать следующую информацию:<br>
						&nbsp;■ Фамилия, Имя, Отчество клиента;<br>
						&nbsp;■ Паспортные данные: серия, номер, кем выдан, дата выдачи;<br>
						&nbsp;■ Номер мобильного телефона, указанного при регистрации в личном кабинете. <br>
						Отказ от услуги возможен в личном кабинете клиента на сайте <a href="http://www.boostra.ru">www.boostra.ru</a>.<br>
						 &nbsp;<br>
						 &nbsp;<b><u>
						<p style="text-align: center;">
							 Заключительные положения
						</p>
						</u></b>&nbsp;&nbsp;<br>
						 Общество вправе изменять и дополнять настоящее Положение.<br>
						 Действующая редакция Положения в день ее утверждения размещается на сайте Общества <a href="http://www.boostra.ru">www.boostra.ru</a>.</div>
				</div>
				<script>
				$(document).ready(function(){
					$('.block_2').click(function(){
						$('.content_block_2').slideToggle(300);      
						return false;
					});
				});
				</script>
			</label>
        </div>        
        *}
        
		{*
        <div>
            <label>
                <div class="checkbox" style="border-width: 1px;width: 14px !important;height: 14px !important;margin-top: 9px;">
        			<input class="js-service-reason" type="checkbox" value="1" id="service_reason_check" name="service_reason" checked="true" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div> В случае отказа по заявке, я хочу получить описание причины отказа в соответствии с <a href="https://www.boostra.ru/files/docs/polozhenie-o-dopolnitelnyh-finansovyh-uslugah-mkk-ooo-bustra-stop-list.pdf" target="_blank">Положением</a>
            </label>
        </div>
		*}        
		<button title="%title%" type="button" class="mfp-close" style="color: #fff;font-size: 20px;background: green;width: 48px;padding: 10px;height: 48px;right: 10px;">ОК</button>
    </div>
    {/if}
	{literal}
		<script>
			$(document).ready(function () {
				if($("#loan_form .step1").is(":visible")) {
					sendMetric('reachGoal', 'registration_income_to_contact');
				}
			});
		</script>
	{/literal}
</div>
