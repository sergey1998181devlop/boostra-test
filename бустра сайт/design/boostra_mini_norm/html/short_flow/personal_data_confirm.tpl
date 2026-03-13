{$meta_title = "Заявка на заём | Boostra" scope=parent}

{capture name=page_scripts}
	<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
	<script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
{/capture}

{literal}
	<style>
		.next-buttons {
			display: flex;
			flex-wrap: wrap-reverse;
			justify-content: right;
			gap: 10px;
			max-width: max-content;
			margin: auto;
		}

		.confirm-tab {
			margin-bottom: 20px;
			display: flex;
			flex-wrap: wrap;
			justify-content: stretch;
			gap: 20px;
		}

		.confirm-block {
			display: flex;
			flex-direction: column;
			justify-content: left;
			width: max-content;
		}
		.confirm-block > * {
			width: max-content;
		}

		.confirm-break {
			flex-basis: 100%;
			height: 0;
		}
	</style>
{/literal}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Проверка данных</h1>
				<h5>Проверьте распознанные данные</h5>
				{include file='display_stages.tpl' current=2 percent=20 total_step=6}
			</hgroup>

			<div style="margin-top: 20px;">
				<div>
					<div class="confirm-tab" id="confirm-tab-1">
						<div class="confirm-block">
							<strong>Фамилия</strong>
							<span>{$user->lastname}</span>
						</div>
						<div class="confirm-block">
							<strong>Имя</strong>
							<span>{$user->firstname}</span>
						</div>
						{if $has_patronymic}
							<div class="confirm-block">
								<strong>Отчество</strong>
								<span>{$user->patronymic}</span>
							</div>
						{/if}
						<div class="confirm-block">
							<strong>Пол</strong>
							<span>{if $user->gender == "male"}Мужской{else}Женский{/if}</span>
						</div>
						<div class="confirm-break"></div>
						<div class="confirm-block">
							<strong>Дата рождения</strong>
							<span>{$user->birth}</span>
						</div>
						<div class="confirm-block">
							<strong>Место рождения</strong>
							<span>{$user->birth_place}</span>
						</div>
					</div>

					<div class="confirm-tab" id="confirm-tab-2" style="display: none">
						<div class="confirm-block">
							<strong>Серия и номер паспорта</strong>
							<span>{$user->passport_serial}</span>
						</div>
						<div class="confirm-block">
							<strong>Дата выдачи</strong>
							<span>{$user->passport_date}</span>
						</div>
						<div class="confirm-block">
							<strong>Код подразделения</strong>
							<span>{$user->subdivision_code}</span>
						</div>
						<div class="confirm-break"></div>
						<div class="confirm-block">
							<strong>Выдан</strong>
							<span>{$user->passport_issued}</span>
						</div>
					</div>

					<div class="confirm-tab" id="confirm-tab-3" style="display: none">
						<div class="confirm-block">
							<strong>Регион прописки</strong>
							<span>{$user->Regregion}{if $user->Regregion_shorttype} {$user->Regregion_shorttype}.{/if}</span>
						</div>
						<div class="confirm-block">
							<strong>Населённый пункт</strong>
							<span>{if $user->Regcity_shorttype}{$user->Regcity_shorttype}. {/if}{$user->Regcity}</span>
						</div>
						<div class="confirm-block">
							<strong>Улица</strong>
							<span>{if $user->Regstreet_shorttype}{$user->Regstreet_shorttype}. {/if}{$user->Regstreet}</span>
						</div>
						<div class="confirm-break"></div>
						{if $user->Regbuilding}
							<div class="confirm-block">
								<strong>Строение</strong>
								<span>{$user->Regbuilding}</span>
							</div>
						{/if}
						{if $user->Reghousing}
							<div class="confirm-block">
								<strong>Дом</strong>
								<span>{$user->Reghousing}</span>
							</div>
						{/if}
						{if $user->Regroom}
							<div class="confirm-block">
								<strong>Квартира</strong>
								<span>{$user->Regroom}</span>
							</div>
						{/if}
					</div>
				</div>
				<div class="next-buttons">
					<button class="button small button-inverse" id="js-btn-incorrect">В данных есть ошибка</button>
					<button class="button" id="js-btn-confirm" data-tab="1">Продолжить</button>
				</div>

				<div class="incorrect-data" style="display: none">
					<h4>Спасибо!</h4>
					<h5>Мы проверим качество распознавания и скорректируем данные.</h5>
				</div>

				<div class="correct-data" style="display: none">
					<h4>Спасибо!</h4>
				</div>
			</div>
		</div>
	</div>
</section>

{literal}
	<script>
		$(document).ready(function() {
			let firstBtnClicked = false;
			setTimeout(() => {
				// Чтобы не сбросить уже увеличенный прогресбар
				if (!firstBtnClicked)
					setProgressBar(22);
			}, 2000);

			$('#js-btn-confirm').click(function() {
				firstBtnClicked = true;
				let $this = $(this);

				let currentTab = $this.data('tab');
				$(`#confirm-tab-${currentTab}`).hide();

				let newTab = currentTab + 1;
				if (newTab <= 3) {
					$(`#confirm-tab-${newTab}`).css('display', 'flex');
					$this.data('tab', newTab);

					if (newTab === 2)
						setProgressBar(25);
					else if (newTab === 3)
						setProgressBar(28);

					return;
				}

				setProgressBar(32);

				$('.next-buttons').hide();
				$('.correct-data').show();

				$.ajax({
					data: {
						solution: 'correct',
					},
					type: 'POST',
					complete: function() {
						setTimeout(() => {
							window.location.reload();
						}, 2000);
					},
				});
			});

			$('#js-btn-incorrect').click(function () {
				firstBtnClicked = true;
				setProgressBar(32);

				$('.confirm-tab').hide();
				$('.next-buttons').hide();
				$('.incorrect-data').show();

				$.ajax({
					data: {
						solution: 'incorrect',
					},
					type: 'POST',
					complete: function() {
						setTimeout(() => {
							window.location.reload();
						}, 5000);
					},
				});
			});
		});
	</script>
{/literal}