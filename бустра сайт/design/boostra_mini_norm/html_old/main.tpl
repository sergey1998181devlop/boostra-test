{* Главная страница магазина *}

{* Для того чтобы обернуть центральный блок в шаблон, отличный от index.tpl *}
{* Укажите нужный шаблон строкой ниже. Это работает и для других модулей *}
{$wrapper = 'index.tpl' scope=parent}

{* Канонический адрес страницы *}
{$canonical="" scope=parent}

<section id="loan">
	<div>
		<hgroup>
			<h1>{$page->header}</h1>
			<h5>Высокий процент одобрений!</h5>
		</hgroup>
		<form action="neworder" method="get">
			<div id="calculator">
				<div class="slider-box">
					<div class="money">
						<input type="text" id="money-range" name="amount" value="3000" />
					</div>
					<div class="period">
						<input type="text" id="time-range" name="period" value="13" />
					</div>
				</div>
				<div class="result">К возврату <span class="total">4 300</span> руб. до <span class="date">13 марта</span></div>
				<button class="button big">Получить займ</button>
				<div class="payment-methods">
					<img src="design/{$settings->theme|escape}/img/visa.svg" alt="VISA" />
					<img src="design/{$settings->theme|escape}/img/master-card.svg" alt="MasterCard" />
					<img src="design/{$settings->theme|escape}/img/maestro.svg" alt="Maestro"/>
				</div>
			</div>
		</form>
	</div>
</section>