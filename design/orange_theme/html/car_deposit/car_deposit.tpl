{* Шаблон текстовой страницы *}

{* Канонический адрес страницы *}
{$canonical="/{$page->url}" scope=parent}

<section id="car_deposit">
	<div class="box">
		<div class="window d-flex gap-4 container rounded-3 align-items-center">
			{include 'car_deposit/car_deposit_form.tpl' modificator="form-container__desktop"}
			<div class="info d-flex align-self-stretch flex-column">
				<div class="title">
					<h3 class="text-white fw-bold">Займ под залог авто</h3>
					<h3 class="text-white fw-bold">Автомобиль и ПТС остаются у вас</h3>
				</div>
				<div class="terms container">
					<div class="keys">
						<img alt="Ключи" src="/design/orange_theme/img/auto-keys.png" />
					</div>
					<ul class="terms-list text-white fw-bold">
						<li>Процентная ставка — 0,23% в день</li>
						<li>Срок — от 3 до 30 месяцев</li>
						<li>Сумма займа — от 30 000</br> до 500 000 рублей</li>
					</ul>
				</div>
			</div>
		</div>
		{include 'car_deposit/car_deposit_form.tpl' modificator="form-container__mobile"}
	</div>
	<button id="success-modal-trigger" type="button" class="btn btn-primary d-none" data-bs-toggle="modal"
		data-bs-target="#success-modal">
	</button>
	{if $isSuccess}
		<div class="modal fade" id="success-modal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<h3>Ваша <span class="orange">заявка</span> на заём под ПТС <span class="orange">отправлена</span>
					</h3>
					<hr>
					<h4>Вы также можете <span class="orange">подать заявку на обычный заём</span></h4>
					<div class="first-loan">
						<h1>Первый заём </br><span class="orange">под 0%</span></h1>
						<img alt="Успех" src="/design/orange_theme/img/success-modal-image.png" />
					</div>
					<a class="btn btn-primary btn-lg" href="/">Получить</a>
				</div>
			</div>
		</div>
	{/if}
</section>

<script src="design/orange_theme/js/jquery.inputmask.min.js" type="text/javascript"></script>
<script src="design/orange_theme/js/car_deposit.js?v=1.005" type="text/javascript"></script>