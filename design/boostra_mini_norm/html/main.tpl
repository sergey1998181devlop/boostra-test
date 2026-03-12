{* Главная страница магазина *}

{* Для того чтобы обернуть центральный блок в шаблон, отличный от index.tpl *}
{* Укажите нужный шаблон строкой ниже. Это работает и для других модулей *}
{$wrapper = 'index.tpl' scope=parent}

{* Канонический адрес страницы *}
{$canonical="" scope=parent}

<section id="loan">
	<div>
        {if $sms_salut}
            <div style="font-weight:bold;font-size:2rem;margin:2rem 0;color:#e5613e">С возвращением!</div>
        {/if}
		<hgroup>
            <div class="header_call_the_manager">
                <div class="call_the_manager">
                    <h5 style="color:#e5613e;">Руководитель службы заботы о клиентах</h5>
                    <h5><a style="color:#e5613e;" href="tel:89310094643">8-931-009-46-43</a></h5>
                </div>
                {*<div class="credit_holidays">
                    <a href="/credit_holidays" class="credit_holidays_link">Кредитные каникулы</a>
                </div>*}
            </div>

			<h1>{$page->header}</h1>
			<h5>Высокий процент одобрений!</h5>

            
            {*<h5 style=""><a  class="discount_title" href="{if $user}user{else}neworder{/if}?period=7&amount=8000">Первый заём под 0% на 7 дней за регистрацию!</a> <span style="color:#222;">*</span></h5>
            *}<br />
            <h2>Получи первый заём <span class="text-orange calculate_green">под 0%</span></h2>
            {*<a href="{$config->root_url}/files/docs/zaim_0.pdf" target="_blank">* Условия акции «ПЕРВЫЙ ЗАЁМ 0%»</a>*}
            

        </hgroup>
		<form id="main_page_form" action="{if $user}user{else}neworder{/if}" method="get">
			<div id="calculator">
				<div class="slider-box">
                    <input type="hidden" id="percent" value="{if $user_discount}{$user_discount->percent/100}{else}0.0{/if}" />
    				<input type="hidden" id="max_period" value="{if $user_discount}{$user_discount->max_period}{else}14{/if}" />
                    {*
                    <input type="hidden" id="percent" value="{if $user_discount}{$user_discount->percent/100}{else}0.01{/if}" />
    				<input type="hidden" id="max_period" value="{if $user_discount}{$user_discount->max_period}{else}16{/if}" />
                    *}
                        <div class="money ion_slider_wrapper">
                            <span class="ion-btn ion-minus"></span>
                            <input type="text" id="money-range" name="amount" value="9000" />
                            <span class="ion-btn ion-plus"></span>
                        </div>
                        <div class="period ion_slider_wrapper">
                            <span class="ion-btn ion-minus"></span>
                            <input type="text" id="time-range" name="period" value="7" />
                            <span class="ion-btn ion-plus"></span>
                        </div>
				</div>
				<div class="result">К возврату <span class="total">3 210</span> руб. до <span class="date"></span></div>


{*
                <div class="info_main_page" style="color: #e5613e;font-size: 24px;font-weight: bold;margin: 20px 0;">
                    Команда "Бустра" поздравляет вас с Новым Годом. <br />
                    Приём заявок начнётся с 5 января.
                </div>
*}

				<button class="button big main-page-button" onclick="sendMetric('reachGoal', 'registration_click_get_zaim')" style="">Получить бесплатно</button>

                <a href="user/login" class="main-acc-link">Войти в личный кабинет</a>

                <div class="display-grid auto-fit-md text-left grid-gap-2">
                    <div class="shadow">
                        <h3 class="my-3">Как получить микрозайм онлайн?</h3>
                        <p>Заполните анкету за 2 минуты, а остальное наши специалисты сделают за Вас. Вы получите деньги через 10 минут после подачи заявки.</p>
                    </div>
                    <div class="shadow">
                        <h3 class="my-3">Требования к заёмщику</h3>
                        <div class="display-grid main-page-icons">
                            <div class="display-flex">
                                <img src="design/{$settings->theme}/img/person.svg" alt="Возраст 18 лет">
                                <p>Возраст 18 лет</p>
                            </div>
                            <div class="display-flex">
                                <img src="design/{$settings->theme}/img/passport.svg" alt="Паспорт гражданина РФ">
                                <p>Паспорт гражданина РФ</p>
                            </div>
                            <div class="display-flex">
                                <img src="design/{$settings->theme}/img/phone.svg" alt="Активный номер телефона">
                                <p>Активный номер телефона</p>
                            </div>
                            <div class="display-flex">
                                <img src="design/{$settings->theme}/img/card.svg" alt="Именная банковская карта">
                                <p>Именная банковская карта</p>
                            </div>
                        </div>
                    </div>
                    <div class="shadow">
                        <h3 class="my-3">Новому клиенту</h3>
                        <p>Если Вы обращаетесь впервые, то для Вас доступно от 1000 до 30 000 рублей сроком на 14 дней <span class="text-orange calculate_green">под 0%</span></p>
                        <p><a href="{$config->root_url}/files/docs/zaim_0.pdf" target="_blank"><small>Условия акции для новых клиентов</small></a></p>
                    </div>
                </div>

                <div class="reviews">
                    <h3 class="my-3">Отзывы</h3>
                    <div class="owl-carousel">
                        <div>
                            <div class="review-item">
                                <p><q>Не первый раз беру. Быстро и удобно. Техподдержка быстро отвечает.</q></p>
                                <p class="text-grey">Анатолий, Самара</p>
                            </div>
                        </div>
                        <div>
                            <div class="review-item">
                                <p><q>У меня плохая КИ, думала, не дадут! В итоге всё идеально. Получила 12 000.</q></p>
                                <p class="text-grey">Екатерина, Архангельск</p>
                            </div>
                        </div>
                        <div>
                            <div class="review-item">
                                <p><q>Процент высокий надо платить, но во всех микрокредитных сейчас так. Здесь хотя бы одобряют мне без проблем.</q></p>
                                <p class="text-grey">Александр, Тюмень</p>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="https://play.google.com/store/apps/details?id=com.boostra.Boostra" target="_blank" style="padding-top:15px;display:block;">
                    <img src="design/{$settings->theme|escape}/img/applink.png" />
                </a>

                {*<button id="gosuslugi" type="button"></button>*}

                <div class="payment-methods">
					<img src="design/{$settings->theme|escape}/img/visa.svg" alt="VISA" />
					<img src="design/{$settings->theme|escape}/img/master-card.svg" alt="MasterCard" />
					<img src="design/{$settings->theme|escape}/img/maestro.svg" alt="Maestro"/>
                    <img src="design/{$settings->theme|escape}/img/logo_cart_world.jpg" alt="Mir"/>
				</div>
			</div>
		</form>
	</div>
</section>

<form id="modal_phone" class="mfp-hide white-popup-modal">
    <div class="modal-close-btn" onclick="$.magnificPopup.close();">
        <img alt="Закрыть" src="/design/{$settings->theme}/img/user_credit_doctor/close.png" />
    </div>
    <div class="modal-header">
        <h4>Форма входа / регистрации</h4>
    </div>
    <div class="modal-content">
        {if $user}
            <ul class="menu">
                <li class="nav"><a href="{$lk_url}" >Личный кабинет</a></li>
                <li class="nav"><a href="user/logout" >Выйти</a></li>
            </ul>
        {else}
            <input name="check_user" type="hidden" value="1" />
            <div class="input-inline input-control">
                <input name="phone" autocomplete="tel" inputmode="tel" type="tel" value="" />
                <small>Телефон</small>
            </div>
        {/if}
    </div>
    {if !$user}
        <div class="modal-footer">
            <div class="input-control">
                <button onclick="validatePhone();" type="button" class="orange-btn">Применить</button>
                <div class="timerOutWrapper"></div>
            </div>
        </div>
    {/if}
</form>

<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
{literal}
    <script type="text/javascript">
        $('.main-page-button').on('click', function (e){
           e.preventDefault();
            $.magnificPopup.open({
                items: {
                    src: '#modal_phone'
                },
                type: 'inline',
                showCloseBtn: true
            });
        });

        function addWidthReview () {
            let width = window.innerWidth - (window.innerWidth - $('.wrap').width());
            $('#loan .reviews').css('width', width + 'px');
        }

        $(document).ready(function()
        {
            $('input[type="tel"]').inputmask("+7 (999) 999-99-99");
            addWidthReview();
        });

        $(window).on('resize', function(){
            //addWidthReview();
        });
    </script>
{/literal}
