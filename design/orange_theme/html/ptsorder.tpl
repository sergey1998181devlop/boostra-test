{* Страница заказа *}

{$meta_title = "Заявка на займ под залог ПТС | Boostra" scope=parent}
{$add_order_css_js = true scope=parent}
<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Для получения займа заполните анкету</h1>
				<h5>Она короткая. Мы не будем никому звонить.</h5>
			</hgroup>
			<div class="slider-box">
				<div class="progress">
					<span class="irs js-irs-0 irs-disabled">
						<span class="irs">
							<span class="irs-line" tabindex="-1">
								<span class="irs-line-mid"></span>
							</span>
							<span class="irs-min">начало анкеты</span>
							<span class="irs-max">выдача займа</span>
							<span class="irs-single" style="left: 4.57369%;">личные данные</span>
						</span>
						<span class="irs-grid"></span>
						<span class="irs-bar" style="width: 8.5%;"></span>
						<span class="irs-bar-edge"></span>
						<span class="irs-slider single" style="left: 18.5%;"></span>
						<span class="irs-disable-mask"></span>
					</span>
				</div>
			</div>
			<form method="post" id="neworder" action="/neworder">
				<div id="steps">
					<fieldset>
						<label>
							<input type="text" name="lastname" id="lastname" placeholder="" value="" required="" aria-required="true">
							<small class="err error" id="err-lastname"></small>
							<span class="floating-label">Фамилия</span>
						</label>
						<label>
							<input type="text" name="firstname" id="firstname" placeholder="" value="" required="" aria-required="true">
							<small class="err error" id="err-firstname"></small>
							<span class="floating-label">Имя</span>
						</label>
						<label>
							<input type="text" name="patronymic" id="patronymic" placeholder="" value="" required="" aria-required="true">
							<small class="err error" id="err-patronymic"></small>
							<span class="floating-label">Отчество</span>
						</label>
						<label>
							<input type="tel" name="birthday" placeholder="Дата рождения" required=""/>
						</label>
						<label>
							<input type="tel" name="phone" placeholder="Телефон" required=""/>
						</label>
						<label>
							<input type="text" name="email" placeholder="Электронная почта" required=""/>
						</label>
						<div class="next">
							<span class="button big">Далее</span>
						</div>
					</fieldset>
					<fieldset>
						<label>
							<input type="tel" name="passportCode" placeholder="Серия и номер паспорта" required=""/>
						</label>
						<label>
							<input type="tel" name="passportDate" placeholder="Дата выдачи" required=""/>
						</label>
						<label>
							<input type="tel" name="subdivisionCode" placeholder="Код подразделения" required=""/>
						</label>
						<label class="big">
							<input type="text" name="passportWho" placeholder="Кем выдан" required=""/>
						</label>
						<div class="next">
							<a href="#prev" class="prev">Назад</a>
							<span class="button big">Далее</span>
						</div>
					</fieldset>
					<fieldset>
						<label>
							<input type="text" name="carManufacturer" placeholder="Марка" required=""/>
						</label>
						<label>
							<input type="text" name="carModel" placeholder="Модель" required=""/>
						</label>
						<label>
							<input type="tel" name="carYear" placeholder="Год выпуска" required=""/>
						</label>
						<div class="next">
							<a href="#prev" class="prev">Назад</a>
							<span class="button big">Далее</span>
						</div>
					</fieldset>
					<fieldset>
						<div class="register">
							<span class="title">Адрес прописки</span>
							<label>
								<input type="text" name="Regregion" placeholder="Область" required="" rel="region"/>
							</label>
							<label>
								<input type="text" name="Regcity" placeholder="Город" required="" rel="city"/>
							</label>
							<label>
								<input type="text" name="Regstreet" placeholder="Улица" rel="street"/>
							</label>
							<label>
								<input type="text" name="Reghousing" placeholder="Дом" required="" rel="house"/>
							</label>
							<label>
								<input type="text" name="Regbuilding" placeholder="Строение" class="adding sup" rel="building"/>
							</label>
							<label>
								<input type="text" name="Regroom" placeholder="Квартира" class="adding sup" rel="flat"/>
							</label>
							<label class="big left">
								<div class="checkbox">
									<input type="checkbox" value="None" id="equal" name="equal" />
									<span></span>
								</div> Совпадает с адресом проживания
							</label>
						</div>
						<div class="living">
							<span class="title">Адрес проживания</span>
							<label>
								<input type="text" name="Faktregion" placeholder="Область" required="" rel="region"/>
							</label>
							<label>
								<input type="text" name="Faktcity" placeholder="Город" required="" rel="city"/>
							</label>
							<label>
								<input type="text" name="Faktstreet" placeholder="Улица" rel="street"/>
							</label>
							<label>
								<input type="text" name="Fakthousing" placeholder="Дом" required="" rel="house"/>
							</label>
							<label>
								<input type="text" name="Faktbuilding" placeholder="Строение" class="adding sup" rel="building"/>
							</label>
							<label>
								<input type="text" name="Faktroom" placeholder="Квартира" class="adding sup" rel="flat"/>
							</label>
							<input type="hidden" name="prop_okato" id="prop_okato" value="">
							<input type="hidden" name="prop_city_type" id="prop_city_type" value="">
							<input type="hidden" name="prop_street_type_long" id="prop_street_type_long" value="">
							<input type="hidden" name="prop_street_type_short" id="prop_street_type_short" value="">
							<input type="hidden" name="prog_okato" id="prog_okato" value="">
							<input type="hidden" name="prog_city_type" id="prog_city_type" value="">
							<input type="hidden" name="prog_street_type_long" id="prog_street_type_long" value="">
							<input type="hidden" name="prog_street_type_short" id="prog_street_type_short" value="">
						</div>
						<label class="big left">
							<div class="checkbox">
								<input type="checkbox" value="None" id="citizen" name="citizen]\" required="" />
								<span></span>
							</div> Являюсь гражданином РФ, даю согласие на <a style="text-decoration: underline;" href="/files/uploads/pravila_obrabotk_personalnuh_dannuh.pdf" target="_blank">обработку и хранение персональных данных</a>
						</label>
						<label class="big left">
							<div class="checkbox">
								<input type="checkbox" value="None" id="conditions" name="conditions" required="" />
								<span></span>
							</div> С <a style="text-decoration: underline;" href="/files/uploads/individualnue_uslovia.pdf" target="_blank">условиями предоставления микрозаймов</a> ознакомлен, не действую в интересах третьих лиц
						</label>
						<div class="next">
							<input name="check_human" type="hidden" value=""/>
							<input name="check_sms" type="hidden" value=""/>
							<button class="button big" id="doit" type="submit" name="neworder" disabled>Готово</button>
							<a href="#prev" class="prev">Назад</a>
						</div>
					</fieldset>
					<fieldset>
						<div class="result">
							<h3>Все прошло отлично!</h3>
							<p>В течение 5 минут мы рассмотрим Вашу&nbsp;заявку.<br>
							Ответ будет сообщен по СМС, или звонком.</p>
							{*
							<a href="login.html" class="button big">Кабинет заемщика</a>
							*}
						</div>
						<div class="pixel_metric">
						{if $pixel}
							{$pixel}
						{/if}
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
</section>
