{* Для того чтобы обернуть центральный блок в шаблон, отличный от index.tpl *}
{* Укажите нужный шаблон строкой ниже. Это работает и для других модулей *}
{$wrapper = 'index.tpl' scope=parent}
{$meta_title = "Займ под залог ПТС | Boostra" scope=parent}
{* Канонический адрес страницы *}
{$canonical="pts" scope=parent}
{$body_class="pts" scope=parent}
{$order_js="ptsorder.js" scope=parent}
{$step_js="pts-step.jquery.js" scope=parent}

<section id="loan">
	<div>
		<hgroup>
			<h1>{$page->header}</h1>
			<h5>До 80 процентов от цены Авто!</h5>
		</hgroup>
		<form action="ptsorder" method="get">
			<div id="calculator">

				<button class="button big">Получить займ</button>
				<div class="payment-methods">
					<img src="design/{$settings->theme|escape}/img/visa.svg" alt="VISA" />
					<img src="design/{$settings->theme|escape}/img/master-card.svg" alt="MasterCard" />
					<img src="design/{$settings->theme|escape}/img/maestro.svg" alt="Maestro"/>
				</div>

				<section id="info">
					<div>
						<div class="box">

							<div id="demands" style="text-align: center;">
								<h4>Требования к заемщику</h4>
								<ul>
									<li style="text-align: center;">
										<div class="icon passport"></div>
										<div class="about">Паспорт<br/> гражданина РФ</div>
									</li>
									<li style="text-align: center;">
										<div class="icon bill"></div>
										<div class="about">Паспорт<br/> Транспортного Средства</div>
									</li>
									<li style="text-align: center;">
										<div class="icon age"></div>
										<div class="about">Возраст<br/> от 21 года</div>
									</li>
									<li style="text-align: center;">
										<div class="icon number"></div>
										<div class="about">Активный<br/> номер мобильного</div>
									</li>
								</ul>
							</div>
							<div id="docs" >
								<h5 style="text-align: center;">Документы МФО</h5>
								<ul>
									<li><a href="/files/uploads/pravila-predostavleniya-zaimov.pdf" target="_blank">Правила предоставления займа</a></li>
									<li><a href="/files/uploads/individualnue_uslovia.pdf" target="_blank">Договор потребительского займа</a></li>
									<li><a href="/files/uploads/ogrn.jpg" target="_blank">Свидетельство ОГРН</a></li>
									<li><a href="/files/uploads/rekvisity.pdf" target="_blank">Реквизиты Организации</a></li>
									<li><a href="/files/uploads/pravila_obrabotk_personalnuh_dannuh.pdf" target="_blank">Правила обработки персональных данных</a></li>
								</ul>
							</div>

							<div id="contacts">
								<h5>Контакты</h5>
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

						</div>
					</div>
				</section>
			</div>
		</form>
	</div>
</section>