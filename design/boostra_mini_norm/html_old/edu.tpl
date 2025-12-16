{* Для того чтобы обернуть центральный блок в шаблон, отличный от index.tpl *}
{* Укажите нужный шаблон строкой ниже. Это работает и для других модулей *}
{$wrapper = '' scope=parent}

{* Канонический адрес страницы *}
{$canonical="/{$page->url}" scope=parent}


<!DOCTYPE html>
<html lang="ru-RU">
<head>
	<base href="{$config->root_url}/"/>
	<title>{$meta_title|escape} - Бустра</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<!-- Переключение IE в последнию версию, на случай если в настройках пользователя стоит меньшая -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- Адаптирование страницы для мобильных устройств -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Запрет распознования номера телефона -->
	<meta name="format-detection" content="telephone=no">
	<meta name="SKYPE_TOOLBAR" content ="SKYPE_TOOLBAR_PARSER_COMPATIBLE">

	<meta property="og:locale" content="ru_RU" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="" />
	<meta property="og:url" content="http://boostra.ru" />
	<meta property="og:site_name" content="boostra" />
	<meta property="og:image" content="design/{$settings->theme|escape}/img/favicon.png" />
	<meta name="twitter:card" content="summary_large_image" />
	<meta name="twitter:description" content="{$meta_description|escape}" />
	<meta name="twitter:title" content="" />
	<meta name="twitter:image" content="design/{$settings->theme|escape}/img/favicon192x192.png" />

	{* Канонический адрес страницы *}
	{if isset($canonical)}<link rel="canonical" href="{$config->root_url}{$canonical}"/>{/if}

	<!-- Данное значение часто используют(использовали) поисковые системы -->
	<meta name="description" content="{$meta_description|escape}" />
	<meta name="keywords"    content="{$meta_keywords|escape}" />

	<!-- Традиционная иконка сайта, размер 16x16, прозрачность поддерживается. Рекомендуемый формат: .ico или .png -->
	<link rel="shortcut icon" type="image/ico" href="design/{$settings->theme|escape}/img/favicon.ico">

	<!-- Подключение файлов стилей -->
	<link rel="stylesheet" href="design/{$settings->theme|escape}/edu/css/styles.css?v=2.12">

	<link rel="stylesheet" href="design/{$settings->theme|escape}/edu/css/response_767.css?v=2.1" media="(max-width: 767px)">
	<link rel="stylesheet" href="design/{$settings->theme|escape}/edu/css/response_479.css?v=2.1" media="(max-width: 479px)">

	<!-- Обучение старых версий IE тегам html5 -->
	<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>

<body>
	
	<div class="site_wrap">
		<!-- Шапка -->
		<header>
			<div class="cont">
				<div class="logo">
					<a href="/">
						<img src="design/{$settings->theme|escape}/edu/images/logo.png" alt="">
					</a>
				</div>

				<ul class="menu">
					{foreach $pages as $p}
					{* Выводим только страницы из первого меню *}
					{if $p->menu_id == 1}
					<li {if $page && $page->id == $p->id}class="selected"{/if}>
						<a data-page="{$p->id}" href="{$p->url}">{$p->name|escape}</a>
					</li>
					{/if}
					{/foreach}
					<li class="nav"><a href="/edu">Развитие</a></li>
					{if $user}
					<li>
						<a href="user" class="button medium">Кабинет заемщика</a>
					</li>
					{/if}
				</ul>

				<div class="tel">
					<a href="tel:88003333073">8 800 333 30 73</a>
				</div>

				<a href="#" class="mob_menu_link">
					<span></span>
					<span></span>
					<span></span>
				</a>
			</div>
		</header>
		<!-- End Шапка -->


		<!-- Основная часть -->
		<section class="first_section">
			<div class="cont">
				<div class="logo_big">
					<img src="design/{$settings->theme|escape}/edu/images/russia.png" alt="Команда МФО 2018">
					<div class="team">
						<b>
							Команда<br/>
							МФО <span>2018</span>
						</b>
						<p>
							Финансовое объединение<br/>
							единомышленников
						</p>
					</div>
				</div>
				<div class="slogan_2018">
					<p>
						<span>Мы объединяем <br/>29 мкк</span> в единую <br/>Федеральную <br/>МФК
					</p>
				</div>
				<div class="clear"></div>
			</div>
		</section>

		{*
		<section class="section_text">
			<div class="cont small">
				<div class="text_block">
					<h1>Развитие</h1>

					<p>Наша компания работает для активных и ответственных людей, ценящих свое время, желающих зарабатывать и умеющих тратить, идущих к своей цели, но стремящихся жить здесь и сейчас.</p>

					<p>Мы оперативно решаем вопрос предоставления займа, не требуем залогов и лишних документов, предлагаем прозрачные индивидуальные условия сотрудничества. Наши средства максимально доступны для предпринимателей с четким планом действий, а также для тех, кто умеет «держать слово».</p>
				</div>
			</div>
		</section>
		*}

		<section class="section_text">
			<div class="cont small">
				<div class="text_block">
					<h2 class="likeh1">С нами Вы получите:</h2>
				</div>
				<div class="items horizontal">
					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/money.png" alt="Фондирование от прямого инвестора">
						</div>
						<div class="text">Фондирование от прямого инвестора</div>
						<a href="/contact" title="Связаться" class="button">Получить</a>
					</div>
					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/top_it.png" alt="Топовые IT решения">
						</div>
						<div class="text">Топовые IT решения, недоступные мелким игрокам</div>
						<a href="/contact" title="Связаться" class="button">Получить</a>
					</div>
					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/people.png" alt="Высококвалифицированный штат">
						</div>
						<div class="text">Высококвалифицированный штат сотрудников за счет применения единых стандартов обучения и проведения профессиональных тренингов</div>
						<a href="/contact" title="Связаться" class="button">Получить</a>
					</div>
					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/buh.png" alt="Бухгалтерия">
						</div>
						<div class="text">Цетрализованное решение проблем, связаных с переходом на ЕПС и нововведениями ЦБ РФ</div>
						<a href="/contact" title="Связаться" class="button">Получить</a>
					</div>
				</div>
				<p class="zamanuha">Всем партнерам <span>3 месяца</span> бесплатного бухгалтерского обслуживания!</p>
			</div>
		</section>


		<section class="what_do">
			<div class="cont small">
				<div class="items">
					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/icon1.png" alt="">
						</div>

						<div class="box">
							<div class="img">
								<img src="design/{$settings->theme|escape}/edu/images/ic_logo1.png" alt="">
							</div>

							<div class="text">Решаем потребности по расширению Вашей клиентской базы через лидогенерацию и эффективный колл-центр.</div>
						</div>
					</div>

					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/icon2.png" alt="">
						</div>

						<div class="box">
							<div class="img">
								<img src="design/{$settings->theme|escape}/edu/images/ic_logo2.png" alt="">
							</div>

							<div class="text">Решаем проблемы сбора денег, услуги коллекторского агентства. Эффективность 20%. Лучший результат по России 2017 года. Обладаем всеми необходимыми лицензиями от Федеральной службы судебных приставов.</div>
						</div>
					</div>

					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/icon3.png" alt="">
						</div>

						<div class="box">
							<div class="img">
								<img src="design/{$settings->theme|escape}/edu/images/ic_logo3.png" alt="">
							</div>

							<div class="text">Осуществляем полное ведение бух. учета МКК, Вы готовы ко всем сложностям в 2018 году, связанными с ЕПС и МСФО?</div>
						</div>
					</div>

					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/icon4.png" alt="">
						</div>

						<div class="box">
							<div class="img">
								<img src="design/{$settings->theme|escape}/edu/images/ic_logo4.png" alt="">
							</div>

							<div class="text">Программное обеспечение. Гарантирует повышение прибыли. Первая ERP система в займах. Первое предложение в вашем регионе - ПО бесплатно.</div>
						</div>
					</div>

					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/icon5.png" alt="">
						</div>

						<div class="box">
							<div class="img">
								<img src="design/{$settings->theme|escape}/edu/images/ic_logo5.png" alt="">
							</div>

							<div class="text">Юридические услуги включающие нормативно правовое сопровождение в области противодействия отмыванию доходов и финансированию терроризма (ПОД/ФТ), судебную практику, разработка локальных нормативных актов компании с учетом изменений и требований законодательства.</div>
						</div>
					</div>

					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/icon6.png" alt="">
						</div>

						<div class="box">
							<div class="img">
								<img src="design/{$settings->theme|escape}/edu/images/ic_logo6.png" alt="">
							</div>

							<div class="text">Колл-центр с эффективно организованной работой по привлечению новых клиентов и работой с постоянными клиентами.</div>
						</div>
					</div>

					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/icon7.png" alt="">
						</div>

						<div class="box">
							<div class="img">
								<img src="design/{$settings->theme|escape}/edu/images/ic_logo7.png" alt="">
							</div>

							<div class="text">Услуги тренинг центра, направленные на повышение квалификации сотрудников организации по эффективной выдаче и сбору денежных средств.</div>

							<br/>
							<a href="http://boostra.pro/" target="_blank" class="button" rel="nofollow">Перейти</a>
						</div>
					</div>

					<div class="item">
						<div class="icon">
							<img src="design/{$settings->theme|escape}/edu/images/icon8.png" alt="">
						</div>

						<div class="box">
							<div class="img">
								<img src="design/{$settings->theme|escape}/edu/images/ic_logo8.png" alt="">
							</div>

							<div class="text">Услуги тренинг центра, направленные на повышение квалификации сотрудников организации по эффективной выдаче и сбору денежных средств.</div>
						</div>
					</div>
				</div>
			</div>
		</section>


		<section class="contact_info">
			<div class="cont small">
				<div class="ttile">Мы готовы рассмотреть любые формы сотрудничества по любым вопросам: от полного сопровождения Вашего бизнеса на условиях франшизы до партнерских отношений по каждому конкретному вопросу.</div>

				<div class="tel">
					ТЕЛ: <a href="tel:88003333073">8 800 333 30 73</a>
				</div>

				<div class="text">Микрокредитная компания Общество с ограниченной ответственностью «Бустра» ИНН/КПП 6317102210/631701001, ОГРН 1146317004030, ОКПО 33529201, р/с 40702810229180002011 в филиале «Нижегородский» АО «Альфабанк» г. Нижний Новгород, к/с 30101810200000000824, БИК 042202824 Юридический адрес: 443099, г. Самара, ул. Максима Горького д. 119 к. 15</div>
			</div>
		</section>
		<!-- End Основная часть -->


		<!-- Подвал -->
		<footer>
			<div class="cont">
				<ul class="menu">
					{foreach $pages as $p}
					{* Выводим только страницы из первого меню *}
					{if $p->menu_id == 1}
					<li {if $page && $page->id == $p->id}class="selected"{/if}>
						<a data-page="{$p->id}" href="{$p->url}">{$p->name|escape}</a>
					</li>
					{/if}
					{/foreach}
					<li class="nav"><a href="/edu">Развитие</a></li>
					<li class="nav"><a href="/boostra-politika">Политика данных</a></li>
					{if $user}
					<li>
						<a href="user" class="button medium">Кабинет заемщика</a>
					</li>
					{/if}
				</ul>

				<div class="copy">ООО МКК «ФД Норд», номер в реестре МФО 651403336005222</div>
			</div>
		</footer>
		<!-- End Подвал -->
	</div>


	<!-- Подключение javascript файлов -->
	<script src="design/{$settings->theme|escape}/edu/js/jquery-3.1.1.min.js"></script>
	<script src="design/{$settings->theme|escape}/edu/js/jquery-migrate-1.4.1.min.js"></script>
	<script src="design/{$settings->theme|escape}/edu/js/scripts.js"></script>


</body>
</html>