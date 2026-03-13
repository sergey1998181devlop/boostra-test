{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user" scope=parent}

{$body_class = "gray" scope=parent}
 
{$add_order_css_js = true scope=parent}

{capture name=page_scripts}
<script src="design/{$settings->theme|escape}/js/user.js?v=1.42" type="text/javascript"></script>
{/capture}

{function name=loan_form}
    {if $quantity_loans_block}
        <div style="color:red;font-size:1.5rem;">
        Вы можете подать новую заявку не ранее чем {$quantity_loans_block|date} {$quantity_loans_block|time}
        </div>
        {*
        <p>
            <a href="partners" target="_blank" class="part-item__link button">Обратитесь к нашим партнерам</a>
        </p>
        *}
    {else}
    
        {if $user_discount}
		<input type="hidden" name="has_user_discount" value="1" />
        <div class="discount_subtitle" style=";margin: 30px 0;color:#21ca50;">
            {if $user_discount->percent > 0}
            Для вас есть акционное предложение: {$user_discount->percent*1}% по займу вместо 1%! <br />
            {else}
            Для вас доступен беспроцентный заём на {$user_discount->max_period} {$user_discount->max_period|plural:'день':'дней':'дня'}  <br />
            {if !$user_discount->end_date}
            <a href="{$config->root_url}/files/docs/zaim_0.pdf" style="font-size:1rem" target="_blank">* Условия акции «ПЕРВЫЙ ЗАЁМ 0%»</a>
            {/if}
            {/if}
            {if $user_discount->end_date}
            Срок действия акции: до {$user_discount->end_date|date} <br />
            (необходимо оформить заявку и получить деньги в течение этого периода)
            {/if}
        </div>
        {/if}
			{if $user->maratorium_valid}
        		<p class="warning-credit-text">Вы можете подать новую заявку не ранее чем {$user->maratorium_date|date} {$user->maratorium_date|time}</p>
        	{/if}
		{include file="user_get_zaim_form.tpl"}
    {/if}
{/function}


    {*if !$consultation_send}
        {include file='consultation_form_v2.tpl'}
    {/if*}
    
    {*if !$consultation_send}
        {include file='consultation_form.tpl'}
    {/if*}
    
<section id="private">
	<input type="hidden" name="is_new_client" value="{$is_new_client}" />
	<div>
		<div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">
			
            <div class="nav">
				<ul>
					<li><a href="/user?user_id={$user->id}" {if $action=='user'}class="current"{/if}>Текущий заём</a></li>
{if $is_developer}
					<li><a href="/user?user_id={$user->id}&action=history" {if $action == 'history'}class="current"{/if}>История займов</a></li>
{/if}	
                    <li><a href="/user/loanhistory">Мои заявки</a></li>					
                    <li><a href="/user/upload">Мои файлы</a></li>					
					<li><a href="/user/docs">Документы</a></li>
					<li><a href="user/logout">Выйти</a></li>
				</ul>
			</div>
			
            <div class="content">
            
				{if $action=="user"}
                    {include 'user_current_loan.tpl'}
                {/if}{* action = user *}
                
                
                {if $action=="history"}
            	<div class="panel">
					{*if $current_orders}
					<div class="list current">
						<h4>Открытый займ.</h4>
						<ul class="table">
							{foreach $current_orders as $order}
							<li>
								<div>
									<span class="card master">
									</span>
								</div>
								<div>
									Займ на 
									<strong>{$order->amount*1} {$currency->sign|escape}</strong>
								</div>
								<div>
									Заявка
									<a href='order/{$order->url}'>
									<strong>{$order->id}</strong>
									</a>
								</div>
								<div>
									Дата заявки
									<strong>
									{$order->date|date}
									</strong>
								</div>
								<div>
								<!--
									Просрочен на
									<strong>2 дня</strong>
									-->
								</div>
								 
							</li>
							{/foreach}
						</ul>
					</div>
					{/if*}
					{if $orders}
					<div class="list">				
						<!--h4>Прочие займы  <span>.</span></h4-->
						<ul class="table">
							{foreach $orders as $order}
							{if $order->status != 4}
							<li>
								<div>
									<span class="card visa">
										
									</span>
								</div>
								<div>
									Заём на 
									<strong>{$order->amount*1} {$currency->sign|escape}</strong>
								</div>
								<div>
									Заявка
									<a href='order/{$order->url}'>
									<strong>{$order->id}</strong>
									</a>
                                    / {$order->id_1c}
								</div>
								<div>
									Дата заявки
									<strong>
									{$order->date|date}
									{$order->date|time}
									</strong>
								</div>
								<div>
									{$order->status_1c}
{*}
                                    {if $order->paid == 1}оплачен,{/if} 
									{if $order->status == 0}
									ждет обработки
									{elseif $order->status == 1}в обработке
									{elseif $order->status == 3}погашен
									{/if}
{*}
									{*
										Просрочен на
										<strong>4 дня</strong>
										*}
								</div>
								<div>
									{*
									Дата погашения
									<strong>10.02.2017</strong>
									*}
								</div>
							</li>
							{/if}
							{/foreach}
						</ul>
					</div>
					{/if}
				</div>
                {/if}{* action = history *}
                
                {if $action=="success"}
                
                {$meta_title="Оплата успешно принята"}
                <div class="panel">
                    <h1>Оплата успешно принята</h1>
                    <div class="about">
                        <p>Вы будете перенаправлены в свой Личный кабинет через несколько секунд.</p>
                	</div>
           		</div>
            	
                {/if}
                
                {if $action=="error"}
                <div class="panel">
                    <h1>Карта не привязана</h1>
                    <div class="about">
                        <p>Попробуйте заново или привяжите другую карту</p>
                   	</div>
         		</div>
            	{/if}
                
            </div>
		</div>
	</div>
</section>

<div style="display:none">
    
    <div id="autodebit">
        <form id="autodebit_form">
            
            <div class="alert-block">
                <div class="alert"></div>
                <button type="button" class="js-close-autodebit button button-inverse medium">Продолжить</button>
            </div>
            
            <div id="detach_block">
                <h1>Вы желаете отменить автоплатеж с карты <span class="autodebit_card_number"></span> ?</h1>
            </div>
            
            <div id="attach_block">
                <h1>Вы желаете подключить автоплатежи с карты <span class="autodebit_card_number"></span> ?</h1>
                <p>Нажимая "Подтвердить" я соглашаюсь и принимаю <a href="http://boostra.ru/files/docs/soglashenie-o-regulyarnyh-rekurrentnyh-platezhah-mkk-ooo-bustra.pdf" target="_blank">следующее соглашение</a></p>
            </div>
            
            <input type="hidden" name="card_attach" value="" />
            <input type="hidden" name="card_detach" value="" />
            
            <div class="actions">
                <button type="button" class="js-close-autodebit button button-inverse medium">Отменить</button>
                <button type="submit" class="button medium">Подтвердить</button>
            </div>
        </form>
    </div>
    
    {if 1}
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
        
        <div>
            <label class="spec_size">
				<div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
						<input class="js-service-doctor js-need-verify" type="checkbox" value="1" id="service_doctor_check" name="service_doctor" checked="" />
        			<span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
            </label>

            <!--a href="{$config->root_url}/files/specials/dogovor_150222.pdf" target="_blank">Договор</a-->

            <a href="{$config->root_url}/files/contracts/{$user->order['approved_file']}" target="_blank">Договор</a>
        </div>

		<div id="not_checked_info" style="display:none">
            <strong style="color:#f11">Вы должны согласиться с договором</strong>
        </div>
        
        <div id="service_insurance_div">
			<label class="spec_size">
                <div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
                    {if $user->id == 81199 || $is_admin || $is_CB}
                        <input class="js-service-insurance" type="checkbox" value="1" id="service_insurance_check" name="service_insurance"  />
                    {else}
                        <input class="js-service-insurance" type="checkbox" value="1" id="service_insurance_check" name="service_insurance" checked="true"  />

                    {/if}
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>            
            </label>
                 Выражаю свое желание заключить договор страхования  соответственно <a href="https://boostra.ru/files/docs/pravila--195-kombinirovannogo-strahovaniya-ot-neschastnyh-sluchaev-i-boleznej.pdf" target="_blank">правилам</a>,
                 страховая премия <span class="js-insure-amount">
                    {$approved_amount = $user->order['approved_amount']|default:0|replace:' ':''}
                    {if $approved_amount <= 2000}
                        {$insure = 0.23}
                    {elseif $approved_amount <= 4000}
                        {$insure = 0.18}
                    {elseif $approved_amount <= 7000}
                        {$insure = 0.15}
                    {elseif $approved_amount <= 10000}
                        {$insure = 0.14}
                    {elseif $smarty.cookies['utm_source']=='sms'} 
                        {$insure = 0.33}
                    {else} 
                        {$insure = 0.13}
                    {/if}
                    составляет {$approved_amount * $insure} руб.
                 </span>, срок страхования 30 дней, страховая сумма <span class="js-insure-premia">{$approved_amount * $insure * 20} руб</span> 
                 
        </div>
        
        <div>
            <label class="spec_size">
                <div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
                    {if $user->id == 81199 || $is_admin || $is_CB}
                        <input class="js-service-recurent" type="checkbox" value="1" id="service_recurent_check" name="service_recurent" />
        			{else}
                        <input class="js-service-recurent" type="checkbox" value="1" id="service_recurent_check" name="service_recurent"  checked="true"/>
                    {/if}
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div> 
            </label>    
                Согласен на подключение услуги реккурентных платежей, предоставляющейся в соответствии с <a class="block_1" href="#" target="_blank">"Соглашением"</a>.
				
        </div>
        
        <button title="%title%" type="button" class="mfp-close" style="color:green;font-size:20px;">ОК</button>
        
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
                <div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
        			<span style="opacity:1;margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>
                <a href="{$config->root_url}/{$config->docs_files_dir}{$doc->filename}" target="_blank">
                    {$doc->name|escape}
                </a>
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

		<div id="service_insurance_div">
			<label class="spec_size">
                <div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
                    {if $user->id == 81199 || $is_admin || $is_CB}
                        <input class="js-service-insurance" type="checkbox" value="1" id="service_insurance_check" name="service_insurance"  />
                    {else}
                        <input class="js-service-insurance" type="checkbox" value="1" id="service_insurance_check" name="service_insurance" checked="true"  />

                    {/if}
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        		</div>            
            </label>
                 Выражаю свое желание заключить договор страхования  соответственно <a href="https://boostra.ru/files/docs/pravila--195-kombinirovannogo-strahovaniya-ot-neschastnyh-sluchaev-i-boleznej.pdf" target="_blank">правилам</a>,
                 страховая премия <span class="js-insure-amount">
                    {$approved_amount = $user->order['approved_amount']|default:0|replace:' ':''}
                    {if $approved_amount <= 2000}
                        {$insure = 0.23}
                    {elseif $approved_amount <= 4000}
                        {$insure = 0.18}
                    {elseif $approved_amount <= 7000}
                        {$insure = 0.15}
                    {elseif $approved_amount <= 10000}
                        {$insure = 0.14}
                    {elseif $smarty.cookies['utm_source']=='sms'} 
                        {$insure = 0.33}
                    {else} 
                        {$insure = 0.13}
                    {/if}
                    составляет {$approved_amount * $insure} руб.
                 </span>, срок страхования 30 дней, страховая сумма <span class="js-insure-premia">{$approved_amount * $insure * 20} руб</span> 
                 
        </div>
        
        <div>
            <label class="spec_size">
                <div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">
                    {if $user->id == 81199 || $is_admin || $is_CB}
                        <input class="js-service-recurent" type="checkbox" value="1" id="service_recurent_check" name="service_recurent" />
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
        
        <button title="%title%" type="button" class="mfp-close" style="color:green;font-size:20px;">ОК</button>
        
    </div>
    {/if}
</div>

{if $user->skip_credit_rating === 'PAY'}
	<div id="modal_result_pay_credit_rating">
		<a onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void();">
			<img src="design/{$settings->theme}/img/modal_icons/close_modal.png" width="17"/>
		</a>
		<div class="text-center">
			<img src="design/{$settings->theme}/img/modal_icons/icon_success_pay_cr.svg" width="120"/>
			<h2>Поздравляем!</h2>
			<p><b>Теперь вероятность одобрения займа намного выше!</b></p>
			<p>Персональный балл кредитного рейтинга и рекомендации по его повышению появятся в личном кабинете</p>
		</div>
	</div>
	<script type="text/javascript">
		$(document).ready(function () {
			if (localStorage.getItem('new_user_pay_credit_rating')) {
				$.magnificPopup.open({
					items: {
						src: '#modal_result_pay_credit_rating'
					},
					type: 'inline',
					showCloseBtn: true,
					modal: true,
				});
				localStorage.removeItem('new_user_pay_credit_rating');
			}
		});
	</script>
{/if}
    

{* проверяем статус заявки через и аякс и если сменился перезагружаем страницу *}
{if $user->order && (!$user->order['status'] || $user->order['status'] == 1)}
<script type="text/javascript">
    $(function(){
        var _interval = setInterval(function(){
            $.ajax({
                url: 'ajax/check_status.php',
                data: {
                    order_id: "{$user->order['id']}",
                    number: "{$user->order['1c_id']}",
                    order_status: "{$user->order['status']}",
                },
                success: function(resp){
                    if (!!resp.change)
                        location.reload()
                }
            })
        }, 30000);
    })
</script>
{/if}

{if !$is_developer}
<script>
 window.addEventListener('sessionready', function(e){
	 $('#juicescore_useragent').val(navigator.userAgent)

	 if (typeof FingerprintID !== 'undefined' && FingerprintID)
	    $('#finkarta_fp').val(FingerprintID);
 })

 {if !empty($redirect)}
    /*setTimeout(function (){
        window.open('{$redirect}', '_blank');
    }, 3000);*/
 {/if}
{/if}
</script>

 