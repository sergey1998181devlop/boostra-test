<!DOCTYPE html>
{*
	Общий вид страницы
	Этот шаблон отвечает за общий вид страниц без центрального блока.
	*}
	<html>
	<head>
		<base href="{$config->root_url}/"/>
		<title>{$meta_title|escape}</title>

		{* Метатеги *}
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="description" content="{$meta_description|escape}" />
		<meta name="keywords"    content="{$meta_keywords|escape}" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		{* Канонический адрес страницы *}
		{if isset($canonical)}<link rel="canonical" href="{$config->root_url}{$canonical}"/>{/if}

		<link rel="shortcut icon" type="image/ico" href="design/{$settings->theme|escape}/img/favicon.ico">

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

		<link rel="icon" href="design/{$settings->theme|escape}/img/favicon-32x32.png" sizes="32x32" />
		<link rel="icon" href="design/{$settings->theme|escape}/img/favicon192x192.png" sizes="192x192" />
		<link rel="apple-touch-icon-precomposed" href="design/{$settings->theme|escape}/img/favicon180x180.png" />
		<meta name="msapplication-TileImage" content="design/{$settings->theme|escape}/img/favicon270x270.png" />
		<link rel="image_src" href="design/{$settings->theme|escape}/img/favicon.png" />
		<meta content="design/{$settings->theme|escape}/img/social.png" name="og:image" property="og:image">

		<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/ion.rangeSlider.css?v=1.04"/>
		<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/magnific-popup.css"/>
		{if $add_order_css_js}
		<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>
		{/if}
		<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/style.css?v=2.07"/>
		<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/pages.css?v=1.144"/>
		<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/media.css?v=2.35" />
		{if $usedesk_config.operatorAvatar}
			<link rel="stylesheet" type="text/css"
				  href="design/{$settings->theme|escape}/css/usedesk-customizations.css?v=1.04"/>
			<style>
				:root {
					--usedesk-operator-avatar: url('{$usedesk_config.operatorAvatar|escape:'quotes'}');
				}
			</style>
		{/if}

		<script src="design/{$settings->theme}/js/jquery-2.1.3.min.js" type="text/javascript"></script>
		<script>
			window.usedeskConfig = {$usedesk_config_json};
		</script>
		<script src="design/{$settings->theme}/js/usedesk-validator.js?v=0.09"></script>

	</head>
	<body class="boy {if $page->id == 1}main{/if} {$body_class}">
		<div class="wrap">
			<header>
				<nav>
					<ul>
					<li class="logo"><a href=""><img src="design/{$settings->theme|escape}/img/logo.svg" alt="boostra"/></a></li>
						<li>
							<div class="response-open">
								<a href="#response-nav">
									<span></span>
									<span></span>
									<span></span>
								</a>
							</div>
							<div id="response-nav">
								<a class="close" href="#response-nav">
									<span></span>
									<span></span>
								</a>
								<ul>
									<li><a href="info#info">Информация</a></li>
									<li><a href="info#docs">Документы</a></li>
									<li class="nav"><a href="/contacts">Связаться с нами</a></li>
									<li class="nav"><a href="info#demands">Условия</a></li>
									<li class="nav"><a href="/edu">Развитие</a></li>
									<li class="nav"><a href="tel:88003333073">8 800 333 30 73</a></li>
									{if $user}
									<li>
										<a href="user" class="button medium">Кабинет заемщика</a>
									</li>
									{/if}
								</ul>
							</div>
							<ul>							
								<li class="nav"><a href="info#info">Условия</a></li>
								<li class="nav"><a href="/contacts">Связаться с нами</a></li>
								<li class="nav"><a href="/edu">Развитие</a></li>
								
								<li class="nav"><a href="tel:88003333073">8 800 333 30 73</a></li>
								{if $user}
								<li>
									<a href="user" class="button medium">Кабинет заемщика</a>
								</li>
								{/if}
							</ul>
						</li>
					</ul>
				</nav>
			</header>

			{$content}

			<footer>
				<ul>
					<li>
						<ul>
							<li><a href="info">Информация</a></li>
							<li><a href="info#docs">Документы</a></li>
							<li><a href="/contacts">Связаться с нами</a></li>
						</ul>
					</li>
					<li>
						<span class="copy">ООО МКК «ФД Норд», номер в реестре МФО 651403336005222</span>
					</li>
					<li>
						{*
						<span class="powered">Сайт разработали «<a href="http://sitemfo.ru/" class="reverse" target="_blank" rel="nofollow">Гуру МФО</a>»</span>
						*}
					</li>
				</ul>
			</footer>
		</div>
		

		{if $add_order_css_js}
		<div class="hidden">
			<div id="check" class="box">
				<h3>Проверка номера</h3>
				<p>На указанный Вами номер было отправлено<br/> SMS с кодом подтверждения.</p>
				<form action="#" method="post">
					<label>
						<div class="plup">
							<input type="tel" name="sign[code]" placeholder="Код из смс" required="" />
						</div>
						{*
						<span class="time">00:00</span>
						*}
					</label>
					<div>
						<button class="medium">Подтвердить телефон</button>
						<a href="#" class="new_sms">Отправить код еще раз</a>
					</div>
				</form>
			</div>
		</div>
		{/if}
		
		<script src="design/{$settings->theme}/js/ion.rangeSlider.min.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/response-nav.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/calculate.js?v=1.02" type="text/javascript"></script>

		{if $add_order_css_js}
		{* Скрипты раздела заявки *}
		<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/jquery.magnific-popup.min.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/jquery.countdown.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.7.5" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/jquery.steps.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/plup.jquery.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.1" type="text/javascript"></script>
		{if !$order_js}
		<script src="design/{$settings->theme}/js/neworder.js?v=1.1" type="text/javascript"></script>
		{else}
		<script src="design/{$settings->theme}/js/{$order_js}" type="text/javascript"></script>
		{/if}
		{if !$step_js}
		<script src="design/{$settings->theme}/js/step.jquery.js?v=1.26" type="text/javascript"></script>
		{else}
		<script src="design/{$settings->theme}/js/pts-tep.jquery.js?v=1.23" type="text/javascript"></script>
		{/if}
		{/if}

		{if $login_scripts}
		{* Скрипты раздела логин *}
		<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/login.validate.js?v=1.36" type="text/javascript"></script>
		{/if}

		<!-- Yandex.Metrika counter -->
		<script type="text/javascript" >
			(function (d, w, c) {
				(w[c] = w[c] || []).push(function() {
					try {
						w.yaCounter45594498 = new Ya.Metrika({
							id:45594498,
							clickmap:true,
							trackLinks:true,
							accurateTrackBounce:true,
							webvisor:true,
							trackHash:true
						});
					} catch(e) { }
				});

				var n = d.getElementsByTagName("script")[0],
				s = d.createElement("script"),
				f = function () { n.parentNode.insertBefore(s, n); };
				s.type = "text/javascript";
				s.async = true;
				s.src = "https://mc.yandex.ru/metrika/watch.js";

				if (w.opera == "[object Opera]") {
					d.addEventListener("DOMContentLoaded", f, false);
				} else { f(); }
			})(document, window, "yandex_metrika_callbacks");
		</script>
		<noscript><div><img src="https://mc.yandex.ru/watch/45594498" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
		<!-- /Yandex.Metrika counter -->
	</body>

	</html>