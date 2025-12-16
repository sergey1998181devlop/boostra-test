{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
	<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
	<script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.11" type="text/javascript"></script>
	{*<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>*}

	<script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.7.5" type="text/javascript"></script>

	{*<script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.64" type="text/javascript"></script>*}
	<script type="text/javascript" src="/js/autocomplete/jquery.autocomplete-min.js"></script>
	<link rel="stylesheet" href="/js/autocomplete/styles.css" />

	<script src="design/{$settings->theme}/js/personal_data.app.js?v=1.75" type="text/javascript"></script>
	{if $settings->addresses_is_dadata}{* подсказки через dadata *}
		<script src="design/{$settings->theme}/js/dadata_init.js?v=1.12" type="text/javascript"></script>
	{else}{* подсказки через kladr *}
		<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>
		<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
		<script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.62" type="text/javascript"></script>
	{/if}

{/capture}

<style>
	.floating-label.required::after {
		content: '*';
		color: red;
		margin-left: 4px;
	}

	.select {
		position: relative;
		display: inline-block;
		width: 100%;
	}

	.select select {
		width: 100%;
		padding-right: 40px;
		color: #000;
		cursor: pointer;
		-webkit-appearance: none;
		-moz-appearance: none;
		appearance: none;
	}

	.select::after {
		content: '▼';
		position: absolute;
		top: 50%;
		right: 15px;
		transform: translateY(-50%);
		pointer-events: none;
		color: #555;
		font-size: 12px;
	}

	.autocomplete-items {
		position: absolute;
		border: 1px solid #d4d4d4;
		border-bottom: none;
		border-top: none;
		z-index: 99;
		top: 70%;
		left: 0;
		right: 0;
	}

	.autocomplete-items div {
		padding: 10px;
		cursor: pointer;
		background-color: #fff;
		border-bottom: 1px solid #d4d4d4;
	}

	.autocomplete-items div:hover {
		background-color: #e9e9e9;
	}

	.autocomplete-active {
		background-color: DodgerBlue !important;
		color: #ffffff;
	}
</style>


<section id="worksheet">
	<div>
		<div class="box">

			{include file='display_stages.tpl' current=4 percent=63 total_step=4}

			
			<h1 class="reg_title">Адрес</h1>
			

			<form method="post" id="personal_data" class="js-send-feedback" data-target="etap-propiska">
				<div id="steps">

					{if $error == 'allready_exists'}
						<div class="alert alert-danger">
							Клиент с такими персональными данными уже зарегистрирован.
							<br />
							Просим связаться с нами по номеру 8 800 333 30 73.
						</div>

					{else}

						{include "ab_banner_registration.tpl"}

						<fieldset style="display: block;;">

							<input type="hidden" value="address_data" name="stage" />

							<span class="title">Адрес прописки</span>
							<div class="register">
							<label class="{if $user->Regregion}readonly{/if} {if $error=='empty_Regregion'}error{/if}" id="regregion-label">
								<input id="Regregion" type="text" name="Regregion" value="{if $registration_region}{$registration_region|escape}{else}{$user->Regregion|escape}{/if}" placeholder="" required="required"/>
								<span class="floating-label required">Область/Регион/Край</span>
									{if $error=='empty_Regregion'}<span class="error">Укажите Регион в которой Вы прописаны</span>{/if}
								</label>

								<label class="{if $user->Regcity}readonly{/if} {if $error=='empty_Regcity'}error{/if}" id="regcity-label">
									<input type="text" name="Regcity" value="{if $registration_city}{$registration_city|escape}{else}{$user->Regcity|escape}{/if}" placeholder="" required="required" rel="city" aria-required="true" data-selected = 'false'/>
									<span class="floating-label required">Населенный пункт</span>
									{if $error=='empty_Regcity'}<span class="error">Укажите Населенный пункт в котором Вы прописаны</span>{/if}
								</label>

								<label class="{if $user->Regstreet}readonly{/if} {if $error=='empty_Regstreet'}error{/if}" id="regstreet-label">
									<input type="text" name="Regstreet" value="{if $registration_street}{$registration_street|escape}{else}{$user->Regstreet|escape}{/if}" placeholder="" rel="street" data-selected = 'false'/>
									<span class="floating-label">Улица</span>
									{if $error=='empty_Regstreet'}<span class="error">Укажите улицу на которой Вы прописаны</span>{/if}
								</label>

								<label class="{if $user->Reghousing}readonly{/if} {if $error=='empty_Reghousing'}error{/if}" id="reghousing-label">
									<input type="text" name="Reghousing" value="{if $registration_house}{$registration_house|escape}{else}{$user->Reghousing|escape}{/if}" placeholder="" rel="house" aria-required="true" data-selected = 'false'/>
									<span class="floating-label">Номер дома</span>
									{if $error=='empty_Reghousing'}<span class="error">Укажите номер дома в котором Вы прописаны</span>{/if}
									<small>если есть</small>
								</label>

								<label class="{if $user->Regbuilding}readonly{/if} ">
									<input inputmode="numeric" type="text" name="Regbuilding" value="{if $registration_building}{$registration_building|escape}{else}{$user->Regbuilding|escape}{/if}" placeholder="" class="adding sup" rel="building" aria-required="true"/>
									<span class="floating-label">Строение</span>
									<small>если есть</small>
								</label>

								<label class="{if $user->Regroom}readonly{/if} ">
									<input inputmode="numeric" type="text" name="Regroom" value="{if $registration_apartment}{$registration_apartment|escape}{else}{$user->Regroom|escape}{/if}" placeholder="" class="adding sup" rel="flat" aria-required="true"/>
									<span class="floating-label">Номер квартиры</span>
									<small>если есть</small>
								</label>

								<label class="big left align-center">
									<div class="checkbox check_address">
										<input type="checkbox" value="1" id="equal" name="equal" {if $equal}checked="true"{/if} />
										<span></span>
									</div> Адрес регистрации совпадает с адресом проживания
								</label>

							</div>

							<div class="living" id="living_block" {if $equal}style="display:none"{/if}>

								<span class="title">Адрес проживания</span>

							<label class="{if $error=='empty_Faktregion'}error{/if} region--label" id="faktregion-label">
								<input id="Faktregion" type="text" name="Faktregion" value="{if $factual_region}{$factual_region|escape}{else}{$user->Faktregion|escape}{/if}" placeholder="" required="required"/>
								<span class="floating-label floating-label-default required">Область/Регион/Край</span>
									{if $error=='empty_Faktregion'}<span class="error">Укажите Регион фактического проживания</span>{/if}
								</label>

								<label class="{if $user->Faktcity}readonly{/if} {if $error=='empty_Faktcity'}error{/if}"  id="faktcity-label">
									<input type="text" name="Faktcity" value="{if $residence_city}{$residence_city|escape}{else}{$user->Faktcity|escape}{/if}" placeholder="" required="" rel="city" data-selected = 'false'/>
									<span class="floating-label required">Населенный пункт</span>
									{if $error=='empty_Faktcity'}<span class="error">Укажите Населенный пункт фактического проживания</span>{/if}
								</label>

								<label class="{if $user->Faktstreet}readonly{/if} {if $error=='empty_Faktstreet'}error{/if}" id="faktstreet-label">
									<input type="text" name="Faktstreet" value="{if $residence_street}{$residence_street|escape}{else}{$user->Faktstreet|escape}{/if}" placeholder="" rel="street" data-selected = 'false'/>
									<span class="floating-label">Улица</span>
									{if $error=='empty_Faktstreet'}<span class="error">Укажите улицу фактического проживания</span>{/if}
								</label>

								<label class="{if $user->Fakthousing}readonly{/if} {if $error=='empty_Fakthousing'}error{/if}" id="fakthousing-label">
									<input type="text" name="Fakthousing" value="{if $residence_house}{$residence_house|escape}{else}{$user->Fakthousing|escape}{/if}" placeholder="" rel="house" data-selected = 'false'/>
									<span class="floating-label">Номер дома</span>
									{if $error=='empty_Fakthousing'}<span class="error">Укажите номер дома фактического проживания</span>{/if}
									<small>если есть</small>
								</label>

								<label class="{if $user->Faktbuilding}readonly{/if}">
									<input inputmode="numeric" type="text" name="Faktbuilding" value="{if $residence_building}{$residence_building|escape}{else}{$user->Faktbuilding|escape}{/if}" placeholder="" class="adding sup" rel="building"/>
									<span class="floating-label">Строение</span>
									<small>если есть</small>
								</label>

								<label class="{if $user->Faktroom}readonly{/if}">
									<input inputmode="numeric" type="text" name="Faktroom" value="{if $residence_apartment}{$residence_apartment|escape}{else}{$user->Faktroom|escape}{/if}" placeholder="" class="adding sup" rel="flat"/>
									<span class="floating-label">Номер квартиры</span>
									<small>если есть</small>
								</label>

								<input type="hidden" name="Regindex" id="prop_zip" value="{if $registration_zipCode}{$registration_zipCode|escape}{else}{$user->Regindex|escape}{/if}">

								<input type="hidden" name="Regregion_shorttype" id="regregion_shorttype" value="{$user->Regregion_shorttype}">
								<input type="hidden" name="Regcity_shorttype" id="regcity_shorttype" value="{$user->Regcity_shorttype}">
								<input type="hidden" name="Regstreet_shorttype" id="regstreet_shorttype" value="{$user->Regstreet_shorttype}">

								<input type="hidden" name="prop_okato" id="prop_okato" value="">
								<input type="hidden" name="prop_city_type" id="prop_city_type" value="">
								<input type="hidden" name="prop_street_type_long" id="prop_street_type_long" value="">
								<input type="hidden" name="prop_street_type_short" id="prop_street_type_short" value="">

								<input type="hidden" name="Faktindex" id="prog_zip" value="{if $residence_zipCode}{$residence_zipCode|escape}{else}{$user->Faktindex|escape}{/if}">

								<input type="hidden" name="Faktregion_shorttype" id="faktregion_shorttype" value="{$user->Faktregion_shorttype}">
								<input type="hidden" name="Faktcity_shorttype" id="faktcity_shorttype" value="{$user->Faktcity_shorttype}">
								<input type="hidden" name="Faktstreet_shorttype" id="faktstreet_shorttype" value="{$user->Faktstreet_shorttype}">

								<input type="hidden" name="prog_okato" id="prog_okato" value="">
								<input type="hidden" name="prog_city_type" id="prog_city_type" value="">
								<input type="hidden" name="prog_street_type_long" id="prog_street_type_long" value="">
								<input type="hidden" name="prog_street_type_short" id="prog_street_type_short" value="">

							</div>

							<div class="next">
								<button class="button big" id="doit" type="submit" name="neworder">Получить решение</button>
							</div>
						</fieldset>
					{/if}
				</div>
			</form>

			{include 'modals/inactivity_modal.tpl'}
		</div>
	</div>
</section>
<script type="text/javascript">
	function autocomplete(inp, arr) {
		var currentFocus;
		var lastValidValue = "";

		var aliases = {
			"Москва": ["мск", "москва"],
			"Санкт-Петербург": ["спб", "питер", "санкт-петербург"],
			"Московская область": ["мо", "подмосковье", "московская"],
			"Ленинградская область": ["ло", "ленинградская"],
			"Краснодарский край": ["краснодар", "кк", "краснодарский"],
			"Свердловская область": ["екатеринбург", "екб", "свердловская"],
			"Новосибирская область": ["новосибирск", "нск", "новосибирская"],
			"Нижегородская область": ["нижний новгород", "нн", "нижегородская"],
			"Республика Татарстан": ["татарстан", "рт", "казань"],
			"Красноярский край": ["красноярск", "красноярский"],
			"Челябинская область": ["челябинск", "челябинская"],
			"Самарская область": ["самара", "самарская"],
			"Ростовская область": ["ростов", "ростовская"],
			"Омская область": ["омск", "омская"],
			"Волгоградская область": ["волгоград", "волгоградская"],
			"Пермский край": ["пермь", "пермский"],
			"Воронежская область": ["воронеж", "воронежская"],
			"Республика Башкортостан": ["башкортостан", "уфа", "рб"],
			"Приморский край": ["владивосток", "приморский"],
			"Хабаровский край": ["хабаровск", "хабаровский"],
			"Сахалинская область": ["сахалин", "сахалинская"]
		};

		function matchesSearch(regionName, searchValue) {
			var searchUpper = searchValue.toUpperCase();
			var regionUpper = regionName.toUpperCase();

			if (regionUpper.indexOf(searchUpper) !== -1) {
				return true;
			}

			if (aliases[regionName]) {
				for (var j = 0; j < aliases[regionName].length; j++) {
					if (aliases[regionName][j].toUpperCase().indexOf(searchUpper) !== -1) {
						return true;
					}
				}
			}

			return false;
		}

		function getMatchPriority(regionName, searchValue) {
			var searchUpper = searchValue.toUpperCase();
			var regionUpper = regionName.toUpperCase();
			var index = regionUpper.indexOf(searchUpper);

			if (index === 0) {
				return 0;
			}

			if (index !== -1) {
				return 10000 + index;
			}

			if (aliases[regionName]) {
				var minAliasIndex = 999999;
				var foundAtStart = false;

				for (var j = 0; j < aliases[regionName].length; j++) {
					var aliasUpper = aliases[regionName][j].toUpperCase();
					var aliasIndex = aliasUpper.indexOf(searchUpper);

					if (aliasIndex === 0) {
						foundAtStart = true;
						minAliasIndex = Math.min(minAliasIndex, aliasIndex);
					} else if (aliasIndex !== -1) {
						minAliasIndex = Math.min(minAliasIndex, 11000 + aliasIndex);
					}
				}

				if (foundAtStart) {
					return 1000;
				}

				if (minAliasIndex !== 999999) {
					return minAliasIndex;
				}
			}

			return 999999;
		}

		function highlightMatch(regionName, searchValue) {
			var searchUpper = searchValue.toUpperCase();
			var regionUpper = regionName.toUpperCase();
			var index = regionUpper.indexOf(searchUpper);

			if (index !== -1) {
				return regionName.substr(0, index) +
						"<strong>" + regionName.substr(index, searchValue.length) + "</strong>" +
						regionName.substr(index + searchValue.length);
			}

			return "<strong>" + regionName + "</strong>";
		}

		inp.addEventListener("input", function(e) {
			var a, b, i, val = this.value;
			closeAllLists();
			if (!val) { return false;}
			currentFocus = -1;

			var matches = [];
			for (i = 0; i < arr.length; i++) {
				if (matchesSearch(arr[i], val)) {
					var priority = getMatchPriority(arr[i], val);
					matches.push({
						name: arr[i],
						priority: priority
					});
				}
			}

			// Сортируем по приоритету (меньшее значение = выше приоритет)
			matches.sort(function(a, b) {
				return a.priority - b.priority;
			});

			// Ограничиваем 10 результатами
			matches = matches.slice(0, 10);

			/* создайте элемент DIV, который будет содержать элементы (значения): */
			a = document.createElement("DIV");
			a.setAttribute("id", this.id + "autocomplete-list");
			a.setAttribute("class", "autocomplete-items");
			/* добавьте элемент DIV в качестве дочернего элемента контейнера автозаполнения: */
			this.parentNode.appendChild(a);

			/* выводим отсортированные результаты */
			for (i = 0; i < matches.length; i++) {
				/* создайте элемент DIV для каждого соответствующего элемента: */
				b = document.createElement("DIV");
				/* подсветите найденную часть: */
				b.innerHTML = highlightMatch(matches[i].name, val);
				/* вставьте поле ввода, которое будет содержать значение текущего элемента массива: */
				b.innerHTML += "<input type='hidden' value='" + matches[i].name + "'>";
				/* выполнение функции, когда кто-то нажимает на значение элемента (элемент DIV): */
				b.addEventListener("click", function(e) {
					/* вставьте значение для текстового поля автозаполнения: */
					inp.value = this.getElementsByTagName("input")[0].value;
					lastValidValue = inp.value;
					/* закройте список значений автозаполнения,
                    (или любые другие открытые списки значений автозаполнения : */
					closeAllLists();
				});
				a.appendChild(b);
			}
		});

		/* выполнение функции нажимает клавишу на клавиатуре: */
		inp.addEventListener("keydown", function(e) {
			var x = document.getElementById(this.id + "autocomplete-list");
			if (x) x = x.getElementsByTagName("div");
			if (e.keyCode == 40) {
				currentFocus++;
				addActive(x);
			} else if (e.keyCode == 38) {
				currentFocus--;
				addActive(x);
			} else if (e.keyCode == 13) {
				e.preventDefault();
				if (x && x.length > 0) {
					if (currentFocus == -1) {
						currentFocus = 0;
					}
					if (x[currentFocus]) {
						x[currentFocus].click();
					}
				}
			}
		});

		inp.addEventListener("blur", function(e) {
			setTimeout(function() {
				var isValid = arr.some(function(item) {
					return item.toUpperCase() === inp.value.toUpperCase();
				});

				if (!isValid) {
					inp.value = "";
				} else {
					lastValidValue = inp.value;
				}
				closeAllLists();
			}, 200);
		});

		function addActive(x) {
			if (!x) return false;
			removeActive(x);
			if (currentFocus >= x.length) currentFocus = 0;
			if (currentFocus < 0) currentFocus = (x.length - 1);
			x[currentFocus].classList.add("autocomplete-active");
		}

		function removeActive(x) {
			for (var i = 0; i < x.length; i++) {
				x[i].classList.remove("autocomplete-active");
			}
		}

		function closeAllLists(elmnt) {
			var x = document.getElementsByClassName("autocomplete-items");
			for (var i = 0; i < x.length; i++) {
				if (elmnt != x[i] && elmnt != inp) {
					x[i].parentNode.removeChild(x[i]);
				}
			}
		}

		document.addEventListener("click", function (e) {
			closeAllLists(e.target);
		});
	}

	$(document).ready(function () {
		var regRegions = [];
		{foreach $regions as $region}
			regRegions.push('{$region->name|escape}');
		{/foreach}
		autocomplete(document.getElementById("Regregion"), regRegions);
		autocomplete(document.getElementById("Faktregion"), regRegions);
	});

	var juicyLabConfig = {
		completeButton: "#doit",
		apiKey: "{$juiceScoreToken}"
	};

	{literal}
	let sessionIdExist = (document.cookie.match(/(?:^|;\s*)juicescore_session_id=([^;]*)/) || [])[1];
	if (sessionIdExist) {
		window.addEventListener('load', function () {
			$('#juicescore_session_id').val($.cookie('juicescore_session_id'));
		});
	}
	else {
		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = "https://score.juicyscore.com/static/js.js";
		var x = document.getElementsByTagName('head')[0];
		x.appendChild(s);

		window.addEventListener('load', function () {
			juicyScoreApi.getSessionId()
					.then(function (sessionId) {
						$.cookie('juicescore_session_id', sessionId, {expires: 1, path: '/'});
						$('#juicescore_session_id').val(sessionId);
					});
		});
	}
	{/literal}
</script>
<noscript>
	<img style="display:none;" src="https://score.juicyscore.com/savedata/?isJs=0"/>
</noscript>