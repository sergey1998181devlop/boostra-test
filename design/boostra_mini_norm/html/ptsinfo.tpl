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
						<div class="about">Возраст<br/> от 21 года</div>
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
					<li><a href="/files/uploads/pravila-predostavleniya-zaimov.pdf" target="_blank">Правила предоставления займа</a></li>
					<li><a href="/files/uploads/individualnue_uslovia.pdf" target="_blank">Договор потребительского займа</a></li>
					<li><a href="/files/uploads/ogrn.jpg" target="_blank">Свидетельство ОГРН</a></li>
					<li><a href="/files/uploads/rekvisity.pdf" target="_blank">Реквизиты Организации</a></li>
					<li><a href="/files/uploads/pravila_obrabotk_personalnuh_dannuh.pdf" target="_blank">Правила обработки персональных данных</a></li>
				</ul>
			</div>
			<div id="contacts">
				<h4>Контакты</h4>
				<div>
					<div>
					ИНН/КПП 6321341772/632101001, ОГРН 1146320005226, ОКПО 21290962,
					</div>
					<div>
						р/с 40701810112300000784 в ПАО АКБ "АВАНГАРД", к/с 30101810000000000201,
						БИК ‎044525201
					</div>
					<div>
						Юридический адрес: 445030, Самарская область, г.Тольятти, ул.Тополиная, дом 49, офис 1
					</div>
					<div>
						Фактический адрес: 445030, Самарская область, г.Тольятти, ул.Тополиная, дом 49, офис 1
					</div>
					<div>
						Почтовый адрес: 445030, Самарская область, г.Тольятти, ул.Тополиная, дом 49, офис 1
					</div>
					<div>
						Директор Вороной Игорь Юрьевич на основании Устава от 07.03.2017 г.
					</div>
				</div>
				<div>Электронная почта: <a href="mailto:info@boostra.ru">info@boostra.ru</a></div>
				<div>Телефон: <a href="tel:88003333073">8 800 333 30 73</a></div>
			</div>
			{/if}
		</div>
	</div>
</section>