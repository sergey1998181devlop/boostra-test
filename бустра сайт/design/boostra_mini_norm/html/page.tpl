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

				<div class="partners_docs">
					<div class="partner_title" data-modal="modal4">
						<h2>Русзаймсервис</h2>
					</div>
					<div class="partner_title" data-modal="modal5">
						<h2>Лорд</h2>
					</div>
				</div>

				<div id="modal4" class="modal_partners">
					<div class="modal-content">
						<span class="close" data-modal="modal4">&times;</span>
						<h2 class="text-center">Русзаймсервис</h2>
						<p class="text-center">Документы Русзаймсервис</p>
						<ul>
							<li><a target="_blank" href="/files/docs/ruszaim/1-extract_from_state_register_of_microfinance_organizations.pdf">Выписка из государственного реестра МФО</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/3-tax_identification_number_certificate.pdf">Свидетельство ИНН</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/4-charter.pdf">Устав</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/5-personal_data_processing_and_storage_policy.pdf">Политика обработки и хранения персональных данных.pdf</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/6-agreement_on_use_of_handwritten_signature_analogue.pdf">Соглашение об использовании аналога собственноручной подписи</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/7-general_terms_of_loan_agreement.pdf">Общие условия договора займа</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/8-rules_for_providing_loans.pdf">Правила предоставления займов</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/9-information_for_financial_services_recipients.pdf">Информация для получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/10-privacy_policy.pdf">Политика конфиденциальности</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/11-procedure_for_reviewing_appeals_of_financial_services_recipients.pdf">Порядок рассмотрения обращений получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/12-baseline_standard_for_protecting_rights_and_interests_of_financial_services_recipients.pdf">Базовый стандарт защиты прав и интересов
									получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/13-baseline_standard_for_risk_management_of_microfinance_organizations.pdf">Базовый стандарт по управлению рисками микрофинансовых
									организаций</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/14-baseline_standard_for_microfinance_organizations_operations_on_the_financial_market.pdf">Базовый стандарт совершения МФО операций на
									финансовом рынке</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/15-law_of_the_russian_federation_07_02_1992_no_2300-1_on_protection_of_consumer_rights.pdf">Закон РФ от 07.02.1992 № 2300-1 'О защите прав
									потребителей'</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/16-information_brochure_of_the_bank_of_russia_on_microfinance_organizations.pdf">Информационная брошюра Банка России об МФО</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/17-information_on_submitting_an_appeal_to_fu.pdf">Информация о подаче обращения в адрес ФУ</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/18-information_on_risks_of_access_to_protected_information.pdf">Информация о рисках доступа к защищаемой информации</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/19-offer_on_using_the_best2pay_processing_center.pdf">Оферта об использовании процессного центра BEST2PAY</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/20-best2pay_payment_security_policy.pdf">Политика безопасности платежей BEST2PAY</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/21-memo_of_the_bank_of_russia_on_credit_holidays_for_svo_participants.pdf">Памятка Банка России о кредитных каникулах для участников СВО</a>
							</li>
							<li><a target="_blank" href="/files/docs/ruszaim/22-information_on_credit_holidays_federal_law_353-fz.pdf">Информация о кредитных каникулах 353-ФЗ</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/23-information_on_credit_holidays_federal_law_377-fz.pdf">Информация о кредитных каникулах 377-ФЗ</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/24-list_of_third_parties_to_whom_user_data_is_transferred.pdf">Перечень третьих лиц, которым передаются пользовательские данные</a></li>
							<li><a target="_blank" href="/files/docs/ruszaim/25-links_to_site_pages_used_for_client_acquisition.pdf">Ссылки на страницы сайтов, используемых для привлечения клиентов</a></li>
						</ul>
					</div>
				</div>
				<div id="modal5" class="modal_partners">
					<div class="modal-content">
						<span class="close" data-modal="modal5">&times;</span>
						<h2 class="text-center">Лорд </h2>
						<p class="text-center">Документы Лорд</p>
						<ul>
							<li><a target="_blank" href="/files/docs/lord/1-extract_from_state_register_of_microfinance_organizations.pdf">Выписка из государственного реестра МФО</a></li>
							<li><a target="_blank" href="/files/docs/lord/2-extract_from_register_of_sro_members.pdf">Выписка из реестра членов СРО</a></li>
							<li><a target="_blank" href="/files/docs/lord/3-tax_identification_number_certificate.pdf">Свидетельство ИНН</a></li>
							<li><a target="_blank" href="/files/docs/lord/4-charter.pdf">Устав</a></li>
							<li><a target="_blank" href="/files/docs/lord/5-personal_data_processing_and_storage_policy.pdf">Политика обработки и хранения персональных данных.pdf</a></li>
							<li><a target="_blank" href="/files/docs/lord/6-agreement_on_use_of_handwritten_signature_analogue.pdf">Соглашение об использовании аналога собственноручной подписи</a></li>
							<li><a target="_blank" href="/files/docs/lord/7-general_terms_of_loan_agreement.pdf">Общие условия договора займа</a></li>
							<li><a target="_blank" href="/files/docs/lord/8-rules_for_providing_loans.pdf">Правила предоставления займов</a></li>
							<li><a target="_blank" href="/files/docs/lord/9-information_for_financial_services_recipients.pdf">Информация для получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/lord/10-privacy_policy.pdf">Политика конфиденциальности</a></li>
							<li><a target="_blank" href="/files/docs/lord/11-procedure_for_reviewing_appeals_of_financial_services_recipients.pdf">Порядок рассмотрения обращений получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/lord/12-baseline_standard_for_protecting_rights_and_interests_of_financial_services_recipients.pdf">Базовый стандарт защиты прав и интересов
									получателей финансовых услуг</a></li>
							<li><a target="_blank" href="/files/docs/lord/13-baseline_standard_for_risk_management_of_microfinance_organizations.pdf">Базовый стандарт по управлению рисками микрофинансовых
									организаций</a></li>
							<li><a target="_blank" href="/files/docs/lord/14-baseline_standard_for_microfinance_organizations_operations_on_the_financial_market.pdf">Базовый стандарт совершения МФО операций на
									финансовом рынке</a></li>
							<li><a target="_blank" href="/files/docs/lord/15-law_of_the_russian_federation_07_02_1992_no_2300-1_on_protection_of_consumer_rights.pdf">Закон РФ от 07.02.1992 № 2300-1 'О защите прав
									потребителей'</a></li>
							<li><a target="_blank" href="/files/docs/lord/16-information_brochure_of_the_bank_of_russia_on_microfinance_organizations.pdf">Информационная брошюра Банка России об МФО</a></li>
							<li><a target="_blank" href="/files/docs/lord/17-information_on_submitting_an_appeal_to_fu.pdf">Информация о подаче обращения в адрес ФУ</a></li>
							<li><a target="_blank" href="/files/docs/lord/18-information_on_risks_of_access_to_protected_information.pdf">Информация о рисках доступа к защищаемой информации</a></li>
							<li><a target="_blank" href="/files/docs/lord/19-offer_on_using_the_best2pay_processing_center.pdf">Оферта об использовании процессного центра BEST2PAY</a></li>
							<li><a target="_blank" href="/files/docs/lord/20-best2pay_payment_security_policy.pdf">Политика безопасности платежей BEST2PAY</a></li>
							<li><a target="_blank" href="/files/docs/lord/21-memo_of_the_bank_of_russia_on_credit_holidays_for_svo_participants.pdf">Памятка Банка России о кредитных каникулах для участников СВО</a>
							</li>
							<li><a target="_blank" href="/files/docs/lord/22-information_on_credit_holidays_federal_law_353-fz.pdf">Информация о кредитных каникулах 353-ФЗ</a></li>
							<li><a target="_blank" href="/files/docs/lord/23-information_on_credit_holidays_federal_law_377-fz.pdf">Информация о кредитных каникулах 377-ФЗ</a></li>
							<li><a target="_blank" href="/files/docs/lord/24-list_of_third_parties_to_whom_user_data_is_transferred.pdf">Перечень третьих лиц, которым передаются пользовательские данные</a></li>
							<li><a target="_blank" href="/files/docs/lord/25-links_to_site_pages_used_for_client_acquisition.pdf">Ссылки на страницы сайтов, используемых для привлечения клиентов</a></li>
						</ul>
					</div>
				</div>

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
			{*}
            <div id="docs">
				<h4>ООО МКК "Аквариус"</h4>
				<ul>
					{foreach $docs as $doc}
                    {if $doc->in_info}
                    <li><a href="{$config->root_url}/{$config->docs_files_dir}{$doc->filename}?v={$doc->version}" target="_blank">{$doc->name|escape}</a></li>
                    {/if}
                    {/foreach}
				</ul>
			</div>
            {*}

			<div id="contacts">
                {*}
				<h4>Контакты</h4>
				<div>
					<div>
						ИНН/ КПП организации: 9714011290/771401001<br/>
                        ОГРН 1237700365506<br/>
					</div>
					<div>
						р/с 40701810900000008895
						в АО «Тинькофф Банк» корсчет 30101810145250000974, БИК 044525974
					</div>
					<div>
						Юридический адрес: 125319, г. Москва., вн. тер. г. муниципальный округ Аэропорт, ул. Академика Ильюшина, д. 12, помещ. 2/1
					</div>
                    <div>
						Местонахождение постоянно действующего исполнительного органа: 125319, г. Москва., вн. тер. г. муниципальный округ Аэропорт, ул. Академика Ильюшина, д. 12, помещ. 2/1
                    </div>
                    <br />
					<div>
                        Режим работы:<br />
                        понедельник-пятница - с 9-00 до 18-00<br />
                        суббота-воскресенье - выходной
                    </div>
                    <br />
                    <div>
						Генеральный директор Поздняков Сергей Владимирович на основании Устава.
					</div>
				</div>
				<div>Телефон: <a href="tel:88005518881">8 (800) 551-88-81</a></div>
				<br />
				
                <div>
					<div style="display: flex; align-items: center;">
						<img src="design/{$settings->theme|escape}/img/qr_code_1.png" alt="Код QR" width="80" height="80" style="margin-right: 10px;">
						Официальный сайт финансового уполномоченного в сети "Интернет". Финансовый уполномоченный осуществляет досудебное урегулирование споров между потребителями финансовых услуг и финансовыми организациями.
					</div>
					<br />
					<div>
						<div style="display: flex; align-items: center;">
							<img src="design/{$settings->theme|escape}/img/qr_code_2.png" alt="Код QR" width="80" height="80" style="margin-right: 10px;">
							Сайт Федеральной службы судебных приставов в сети "Интернет", содержащий форму для подачи жалоб и обращений на нарушение прав и законных интересов физических лиц при осуществлении деятельности по возврату просроченной задолженности физических лиц, возникшей из денежных обязательств.
						</div>
					</div>

				</div>
                {*}

					<div>
						{include file="callBackForm.tpl"}
					</div>

		</div>
			{/if}
				{*}
                <br />
				<div>
					<div style="display: flex; align-items: center;">
						<img src="design/{$settings->theme|escape}/img/qr_code_1.png" alt="Код QR" width="80" height="80" style="margin-right: 10px;">
						Официальный сайт финансового уполномоченного в сети "Интернет". Финансовый уполномоченный осуществляет досудебное урегулирование споров между потребителями финансовых услуг и финансовыми организациями.
					</div>
					<br />
					<div>
						<div style="display: flex; align-items: center;">
							<img src="design/{$settings->theme|escape}/img/qr_code_2.png" alt="Код QR" width="80" height="80" style="margin-right: 10px;">
							Сайт Федеральной службы судебных приставов в сети "Интернет", содержащий форму для подачи жалоб и обращений на нарушение прав и законных интересов физических лиц при осуществлении деятельности по возврату просроченной задолженности физических лиц, возникшей из денежных обязательств.
						</div>
					</div>

				</div>
                {*}
			</div>
		</div>
</section>
