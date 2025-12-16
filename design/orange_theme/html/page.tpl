{* Шаблон текстовой страницы *}

{* Канонический адрес страницы *}
{$canonical="/{$page->url}" scope=parent}


<section id="info">
	<div>
		<div class="box">
			<div>
				<!-- Заголовок страницы -->
				<h1 data-page="{$page->id}">{$page->header|escape}</h1>
				<!-- Тело страницы -->
				{$page->body}
			</div>

            {if $page->url == 'credit_holidays'}
			{/if}

            {if $page->url == 'covid19'}
            <div id="docs">

            <div id="covid">
                <p>
                    Уважаемый клиент, сообщаем вам, что в соответствии с "Федеральным законом от 3 апреля 2020 г. N 106-ФЗ
                    "О внесении изменений в Федеральный закон "О Центральном банке Российской Федерации (Банке России)" и отдельные законодательные акты Российской Федерации в части особенностей изменения условий кредитного договора, договора займа", вы имеете право обратиться с заявлением на реструктуризацию займа или с запросом о предоставлении кредитных каникул
                </p>
                <ul>
                    {foreach $docs as $doc}
                    {if $doc->id == 32 || $doc->id == 33 || $doc->id == 34}
                    <li><a href="{$config->root_url}/{$config->docs_files_dir}{$doc->filename}" target="_blank">{$doc->name|escape}</a></li>
                    {/if}
                    {/foreach}
                </ul>
            </div>

			</div>
            {/if}

            {if $page->url == 'info'}
			<div id="demands">
				<h4>Требования к заемщику</h4>
				<ul>
					<li>
						<div class="icon passport"></div>
						<div class="about">Паспорт<br/> гражданина РФ</div>
					</li>
					<li>
						<div class="icon bcard"></div>
						<div class="about">Именная<br/> банковская карта</div>
					</li>
					<li>
						<div class="icon age"></div>
						<div class="about">Возраст<br/> от 18 лет</div>
					</li>
					<li>
						<div class="icon number"></div>
						<div class="about">Активный<br/> номер мобильного</div>
					</li>
				</ul>
			</div>
			<div id="docs">
				<h4>Документы МФО</h4>
				<ul>
					{foreach $docs as $doc}
                    {if $doc->in_info}
                    <li><a href="{$config->root_url}/{$config->docs_files_dir}{$doc->filename}" target="_blank">{$doc->name|escape}</a></li>
                    {/if}
                    {/foreach}
                    {*}
                    <li><a href="/files/uploads/pravila_predostavleniya_mikrozaimov.pdf" target="_blank">Правила предоставления займа</a></li>
					<li><a href="/files/uploads/OGRN fd Nord.jpg" target="_blank">Свидетельство ОГРН</a></li>
					<li><a href="/files/uploads/INN fd Nord.jpg" target="_blank">Свидетельство ИНН</a></li>
					<li><a href="/files/uploads/Положение о раскрытии информации о лицах, оказывающих влияние на решения.pdf" target="_blank">Положение о раскрытии информации о лицах, оказывающих влияние на решения</a></li>
                    <li><a href="/files/uploads/bazoviy_standart_zaschity_prav.pdf" target="_blank">Базовый стандарт защиты прав и интересов физических и юридических лиц - получателей финансовых услуг</a></li>
					<li><a href="/files/uploads/svidetelstvo.jpg" target="_blank">Свидетельство ЦБ</a></li>
					<li><a href="/files/uploads/Список лиц, оказывающих влияние на решения общества.pdf" target="_blank">Список лиц, оказывающих существенное (прямое или косвенное) влияние на решения</a></li>
					<li><a href="/files/uploads/Условия привлечения денежных средств от физ.лиц.pdf" target="_blank">Условия привлечения денежных средств от физ. лиц</a></li>
					<li><a href="/files/uploads/Федеральный закон от 4 июня 2018 г N 123 ФЗ Об уполномоченном по правам потребит.rtf" target="_blank">Федеральный закон от 4 июня 2018 г N 123 ФЗ Об уполномоченном по правам потребителей финансовых услуг</a></li>
					{*}

				</ul>


			</div>
			<div id="contacts">
				<h4>Контакты</h4>
				<div>
					<div>
    					ИНН 6317102210<br/>
                        КПП 631701001<br/>
                        ОГРН 114 631 700 4030<br/>
                        ОКПО 33529201
					</div>
					<div>
						р/с 40702810229180002011
                        в ФИЛИАЛ НИЖЕГОРОДСКИЙ АО АЛЬФА-БАНК Г НИЖНИЙ НОВГОРОД
                        БИК 042202824
                        КОР 30101810200000000824
					</div>
					<div>
						Юридический адрес: 443099, САМАРСКАЯ ОБЛАСТЬ, Г.О. САМАРА, ВН.Р-Н САМАРСКИЙ, Г САМАРА, УЛ ФРУНЗЕ, Д. 48, ОФИС 10
					</div>
					<div>
						Почтовый адрес: 443080,Самарская область, город Самара, ул. Революционная 70 лит. 2 оф. 235
					</div>
                    <br />
					<div>
                        Режим работы:<br />
                        понедельник-пятница - с 9-00 до 18-00<br />
                        суббота-воскресенье - выходной
                    </div>
                    <br />
                    <div>
						Директор Вороной Игорь Юрьевич на основании Устава от 07.03.2017 г.
					</div>
				</div>
				<div>Телефон: <a href="tel:88003333073">8 800 333 30 73</a></div>
			</div>
                                        <div>
                                            {include file="callBackForm.tpl"}
                                        </div>
			{/if}


		</div>
	</div>
</section>
