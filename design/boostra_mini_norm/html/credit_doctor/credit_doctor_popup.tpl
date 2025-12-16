{* Модальное окно кредитного доктора *}
<div id="modal-creditdoctor">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content-creditdoctor">
            <div class="modal-header modal-header-prolongation">
                <h5 class="modal-title-prolongation" id="modalLabel">Финансовый доктор</h5>
                <a type="button" id="closeButtonModal"
                   class="btn-close btn-close-prolongation-x btn-close-prolongation btn-close-doctor"
                   data-bs-dismiss="modal" aria-label="Close">X</a>
            </div>
            <p class="text-center" style="text-decoration: underline;">ПО СВФСИС №20156</p>
            <div class="modal-body-prolongation">
                <p style="text-align: left">{$user->firstname|escape} дает возможность, получить консультацию у ИИ (искусственного интеллекта) по финансовым вопросам.</p>

                <p style="color: #FF8902; text-decoration: underline;">Вы получите:</p>
                <div class="modal-body__inner">
                    <div class="icon-text-creditdoctor-container one">
                        <strong>Решение финансовых проблем</strong>
                        <p>Программа позволяющим предложить персонализированную стратегию решения финансовых проблем
                            через: оптимальный подбор кредитных продуктов (реструктуризация, рефинансирование,
                            перекредитованние);</p>
                    </div>

                    <div class="icon-text-creditdoctor-container two">
                        <strong>Важная приоритетность</strong>
                        <p>Определить наиболее выгодную очередность закрытия кредитов, займов; сформировать (жалобы,
                            ходатайства, заявления) в ФССП и Суды; отменить судебный приказ; сохранить прожиточный
                            минимум должника;</p>
                    </div>
                    <div class="icon-text-creditdoctor-container three">
                        <strong>Программное обеспечение</strong>
                        <p>ПО является ИИ, который может выработать оптимальную стратегию увеличения кредитного
                            рейтинга, или снижения долговой нагрузки в 70-80% случаев, а также постоянно дообучается на
                            всех поступающих обращениях.</p>
                    </div>
                    <div class="icon-text-creditdoctor-container four">
                        <strong><a target="_blank" href="/files/doc/example_key.pdf">Образец ключа</a></strong>
                    </div>
                </div>
                <div class="btn-wrapper">
                    <p style="color: #FF8902;">Выберите комфортный для Вас тариф</p>
                </div>

                <div id="creditdoctor__wrapper" style="padding-bottom: 30px">
                    <ul class="credit_doctor_tariffs owl-carousel owl-theme">
                        {foreach $credit_doctor_tariffs as $tariff}
                            <li class="">
                                <input id="creditdoctor_{$tariff@index}"
                                       type="radio"
                                       name="creditdoctor_id"
                                       data-amount="{$tariff->price}"
                                />
                                <label for="creditdoctor_{$tariff@index}" class="tariff_label">
                                    <h5>{$tariff->price}  руб / {{$tariff->license_key_days}} дней</h5>
                                    <button class="button button-creditdoctor-inverse medium" type="button" onclick="selectCreditDoctor(0)">Выбрать
                                    </button>
                                </label>
                            </li>
                        {/foreach}
                    </ul>
                </div>
                <div>
                    <p style="font-size: 12px;">ООО «Финтех-маркет», ИНН: 6317164496, ОГРН: 1236300023849</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalContainerCreditCancel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="question-block-multipolis">
                    <h3 class="question-text-bold">Вы отказались от услуги «Финансовый доктор»</h3>
                    <button class="btn-prolongation-creditdoctor">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</div>

{* Квиз кредитного доктора *}
<div class="popup__wrapper">

    <div class="popup tac popup-step-start-end" data-popup-id="0">
        <div class="popup__title">
            Мы поможем<br>избавиться<br>от долгов!
        </div>
        <div>
            Ответьте на 4 простых вопроса,<br>чтобы начать путь к лучшей жизни!
        </div>
        <button class="popup__go mt-4">
            <img src="design/{$settings->theme|escape}/img/credit_doctor/go.svg" class="pl-1">
        </button>
    </div>

    <form class="popup tac popup-step d-none" data-popup-id="1">
        <div>
            Пройдите короткий тест, чтобы<br>получить индивидуальный план<br>снижения долговой нагрузки
        </div>
        <div class="popup-step__center-block">
            <div class="font-Bold">
                #1<br>Вы сегодня получали<br>отказ в займе?
            </div>
            <div class="popup__checkboxes inline mt-4">
                <div>
                    <input class="popup-step__checkbox" type="radio" name="inlineRadioOptions" required id="popup1answer1"
                           value="1">
                    <label for="popup1answer1">Да</label>
                </div>
                <div>
                    <input class="popup-step__checkbox" type="radio" name="inlineRadioOptions" required id="popup1answer2"
                           value="2">
                    <label for="popup1answer2">Нет</label>
                </div>
            </div>
        </div>
        <button class="popup__next mt-4">
            Далее
        </button>
    </form>

    <form class="popup tac popup-step d-none" data-popup-id="2">
        <div>
            Пройдите короткий тест, чтобы<br>получить индивидуальный план<br>снижения долговой нагрузки
        </div>
        <div>
            <div class="font-Bold">
                #2<br>Вы планировали получить займ,<br>чтобы с его помощью оплатить<br>другой займ?
            </div>
            <div class="popup__checkboxes inline mt-4">
                <div>
                    <input class="popup-step__checkbox" type="radio" name="inlineRadioOptions" required id="popup2answer1"
                           value="1">
                    <label for="popup2answer1">Да</label>
                </div>
                <div>
                    <input class="popup-step__checkbox" type="radio" name="inlineRadioOptions" required id="popup2answer2"
                           value="2">
                    <label for="popup2answer2">Нет</label>
                </div>
            </div>
        </div>
        <button class="popup__next mt-4">
            Далее
        </button>
    </form>

    <form class="popup tac popup-step d-none" data-popup-id="3">
        <div>
            Пройдите короткий тест, чтобы<br>получить индивидуальный план<br>снижения долговой нагрузки
        </div>
        <div>
            <div class="font-Bold">
                #3<br>Какой у вас<br>ежемесячный доход?
            </div>
            <div class="popup__checkboxes column mt-4">
                <div>
                    <input class="popup-step__checkbox" type="radio" name="inlineRadioOptions" required id="popup3answer1"
                           value="1">
                    <label for="popup3answer1">до 30 000 рублей</label>
                </div>
                <div>
                    <input class="popup-step__checkbox" type="radio" name="inlineRadioOptions" required id="popup3answer2"
                           value="2">
                    <label for="popup3answer2">30 000 - 50 000 рублей</label>
                </div>
                <div>
                    <input class="popup-step__checkbox" type="radio" name="inlineRadioOptions" required id="popup3answer3"
                           value="3">
                    <label for="popup3answer3">более 50 000 рублей</label>
                </div>
            </div>
        </div>
        <button class="popup__next mt-4">
            Далее
        </button>
    </form>

    <form class="popup tac popup-step d-none" data-popup-id="4">
        <div>
            Пройдите короткий тест, чтобы<br>получить индивидуальный план<br>снижения долговой нагрузки
        </div>
        <div>
            <div class="font-Bold">
                #4<br>Какая у вас долговая нагрузка<br>в настоящий момент?
            </div>
            <div class="popup__checkboxes column mt-4">
                <div>
                    <input class="popup-step__checkbox" type="radio" name="inlineRadioOptions" required id="popup4answer1"
                           value="1">
                    <label for="popup4answer1">до 30 000 рублей</label>
                </div>
                <div>
                    <input class="popup-step__checkbox" type="radio" name="inlineRadioOptions" required id="popup4answer2"
                           value="2">
                    <label for="popup4answer2">30 000 - 50 000 рублей</label>
                </div>
                <div>
                    <input class="popup-step__checkbox" type="radio" name="inlineRadioOptions" required id="popup4answer3"
                           value="3">
                    <label for="popup4answer3">более 50 000 рублей</label>
                </div>
            </div>
        </div>
        <button class="popup__next mt-4">
            Далее
        </button>
    </form>

    <div class="popup tac popup-step-start-end d-none" data-popup-id="5">
        <div>
            <img src="design/{$settings->theme|escape}/img/credit_doctor/loading.svg" class="popup__loading-icon">
        </div>
        <div class="popup__small-title font-Bold mt-4">
            Рассчитываем индивидуальный план снижения Вашей долговой нагрузки
        </div>
    </div>

    <form class="popup tac popup-step d-none" data-popup-id="6">
        <a type="button" id="closeButtonModal" class="popup-close btn-close-doctor" data-bs-dismiss="modal" aria-label="Close">X</a>
        <div style="margin-top: 35px;">
            <img src="design/{$settings->theme|escape}/img/credit_doctor/check.svg" >
        </div>
        <div class="font-bold text-center">
            План готов! Он появится в Вашем личном кабинет после оплаты услуги
        </div>
        <button class="popup__next mt-4">
            Оплатить
        </button>
    </form>
</div>
{literal}
    <style>

        /* ----------------------- стили для кредитного доктора------------------- */

        #modal-creditdoctor {
            background: #fff;
            max-width: 718px;
            border-radius: 20px;
            margin: 0 auto;
            padding: 20px;
        }

        #modalContainerCreditCancel {
            background: #fff;
            max-width: 500px;
            border-radius: 20px;
            margin: 0 auto;
            padding: 20px;
        }

        #modal-creditdoctor label {
            text-align: center;
            display: grid;
            height: 100%;
            padding: 10px;
            border-radius: 15px;
            border: 1px solid #FF8902;
            box-sizing: border-box;
            cursor: pointer;
            margin: 2px;
        }

        #modal-creditdoctor [name="creditdoctor_id"]:checked:not(:disabled) + label {
            background: radial-gradient(circle at 100% 100%, #ffffff 0, #ffffff 14px, transparent 14px) 0% 0%/15px 15px no-repeat,
            radial-gradient(circle at 0 100%, #ffffff 0, #ffffff 14px, transparent 14px) 100% 0%/15px 15px no-repeat,
            radial-gradient(circle at 100% 0, #ffffff 0, #ffffff 14px, transparent 14px) 0% 100%/15px 15px no-repeat,
            radial-gradient(circle at 0 0, #ffffff 0, #ffffff 14px, transparent 14px) 100% 100%/15px 15px no-repeat,
            linear-gradient(#ffffff, #ffffff) 50% 50%/calc(100% - 2px) calc(100% - 30px) no-repeat,
            linear-gradient(#ffffff, #ffffff) 50% 50%/calc(100% - 30px) calc(100% - 2px) no-repeat,
            linear-gradient(90deg, #FF8902 0%, #FFA948 100%);
        }

        #creditdoctor__wrapper label ul {
            text-align: left;
            margin: 20px 0;
            font-size: 13px;
            list-style-type: none;
            padding-left: 20px;
            position: relative;
            box-sizing: border-box;
        }

        #modal-creditdoctor label ul > li::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 0;
            width: 10%;
            height: 30%;
            background: url('/design/boostra_mini_norm/img/svg/book.svg') no-repeat center center / contain;
        }

        .icon-text-creditdoctor-container {
            display: flex;
            flex-direction: column;
        }

        .icon-text-creditdoctor-container strong {
            text-align: left;
        }

        .button-creditdoctor-inverse {
            color: #FFFFFF;
            background: linear-gradient(to right, #FF8902, #FFA948);
            box-shadow: none;
            padding: 10px 33px;
            border-radius: 1.6rem;
            text-transform: unset !important;
        }

        .btn-close-prolongation {
            position: absolute;
            right: 0;
            bottom: 5px;
            color: #0a001f;
            cursor: pointer;
            background: transparent;
            border: none;
            box-shadow: unset;
        }

        .btn-close-prolongation-x {
            position: absolute;
            right: 0;
            bottom: 5px;
            color: #0a001f;
            cursor: pointer;
            background: transparent;
            border: none;
            box-shadow: unset;
        }

        .question-text-bold {
            color: #000000;
            margin-bottom: 50px;
        }

        .btn-prolongation-creditdoctor{
            width: 100%;
            height: 39px;
            border-radius: 20px;
            border: none;
            color: #FFFFFF;
            font-weight: 900;
            background: linear-gradient(to right, #FF8902, #FFA948);
            padding: 0 10px;
            transition: all 0.25s ease 0s;
        }

        .btn-prolongation-creditdoctor:hover {
            background: transparent;
            transition: background-color 0.3s ease;
            color: #E21212;
            border: 1px solid #E21212;
            border-radius: 20px;
        }

        #btn-modal-creditdoctor, #btn-modal-staroracle {
            cursor:pointer;
        }

        #creditdoctor__wrapper label ul {
            text-align: left;
            margin: 20px 0;
            font-size: 13px;
            list-style-type: none;
            padding-left: 20px;
            position: relative;
            box-sizing: border-box;
        }

        #creditdoctor__wrapper > ul {
            padding-left: 0;
            display: flex;
            max-width: 100%;
            list-style: none;
            gap: 5px;
        }

        #creditdoctor__wrapper > ul > li {
            min-width: 150px;
            flex: 1 1 0;
        }

        #creditdoctor__wrapper li label .button-creditdoctor-inverse {
            background-color: transparent;
            padding: 10px 33px;
            color: #ffffff;
            border: 1px solid #FFA948;

        }

        #creditdoctor__wrapper li input:checked + label .button-creditdoctor-inverse {
            background: linear-gradient(to right, #FF8902, #FFA948);
            padding: 10px 33px;
            color: #ffffff;
        }

        #creditdoctor__wrapper li label .button-creditdoctor-inverse:hover {
            background-color: transparent;
            padding: 10px 33px;
            color: #FF8902;
            border: 1px solid #FFA948;

        }

        #creditdoctor__wrapper label button {
            margin: auto;
            display: flex;
            text-transform: uppercase;
            border: 3px solid;
        }



        #creditdoctor__wrapper > ul {
            padding-left: 0;
            display: flex;
            max-width: 100%;
            list-style: none;
            gap: 5px;
        }

        #creditdoctor__wrapper > ul > li {
            overflow-wrap: anywhere;
            flex: 1 1 0;
        }

        #creditdoctor__wrapper li label .button-creditdoctor-inverse {
            background-color: transparent;
            padding: 10px 33px;
            color: #ffffff;
            border: 1px solid #FFA948;

        }


        #creditdoctor__wrapper li input:checked + label .button-creditdoctor-inverse {
            background: linear-gradient(to right, #FF8902, #FFA948);
            padding: 10px 33px;
            color: #ffffff;
        }

        #creditdoctor__wrapper li label .button-creditdoctor-inverse:hover {
            background-color: transparent;
            padding: 10px 33px;
            color: #FF8902;
            border: 1px solid #FFA948;

        }

        .icon-text-creditdoctor-container {
            position: relative;
        }

        .modal-header-prolongation {
            position: relative;
            display: flex;
            justify-content: center;
            border-bottom: none;
            margin-top: 20px;
            padding-bottom: 0;
        }

        .modal-body-prolongation {
            flex: 1 1 auto;
            padding: var(--bs-modal-padding);
            max-width: 599px;
            margin: 0 auto;
        }

        .modal-body__inner .icon-text-creditdoctor-container.one::before {
            content: '';
            position: absolute;
            top: 0;
            left: -40px;
            width: 25px;
            height: 26px;
            background: url('/design/boostra_mini_norm/img/svg/tablet.svg') no-repeat center center / contain;
        }

        .modal-body__inner .icon-text-creditdoctor-container.two::before {
            content: '';
            position: absolute;
            top: 0;
            left: -40px;
            width: 25px;
            height: 26px;
            background: url('/design/boostra_mini_norm/img/svg/docs.svg') no-repeat center center / contain;
        }

        .modal-body__inner .icon-text-creditdoctor-container.three::before {
            content: '';
            position: absolute;
            top: 0;
            left: -40px;
            width: 25px;
            height: 26px;
            background: url('/design/boostra_mini_norm/img/svg/tools.svg') no-repeat center center / contain;
        }

        .modal-body__inner .icon-text-creditdoctor-container.four::before {
            content: '';
            position: absolute;
            top: 0;
            left: -40px;
            width: 25px;
            height: 26px;
            background: url('/design/boostra_mini_norm/img/svg/chart.svg') no-repeat center center / contain;
        }

        #modal-creditdoctor [name="tv_medical_id"] {
            display: none;
        }

        #creditdoctor__wrapper label button {
            margin: auto;
            display: flex;
            text-transform: uppercase;
            border: 3px solid;
        }


        @media screen and (max-width: 768px) {
            #creditdoctor__wrapper > ul {
                flex-wrap: wrap;
            }

            #creditdoctor__wrapper > ul > li {
                flex: unset;
            }

            .icon-text-creditdoctor-container strong {
                padding: 0 15px;
            }

            .modal-body__inner .icon-text-creditdoctor-container.one::before {
                left: -15px;
            }

            .modal-body__inner .icon-text-creditdoctor-container.two::before {
                left: -15px;
            }

            .modal-body__inner .icon-text-creditdoctor-container.three::before {
                left: -15px;
            }

            .modal-body__inner .icon-text-creditdoctor-container.four::before {
                left: -15px;
            }

            .modal-body__inner .icon-text-creditdoctor-container > p {
                padding-left: 15px;
            }

            #modal-creditdoctor label ul > li::before {
                left: -20px;
                height: 20px;
            }

        }

        @media screen and (max-width: 540px) {
            #modal-creditdoctor label ul > li::before {
                left: -15px;
            }
        }


        .popup__wrapper {
            position: fixed;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            /*background: rgb(0 0 0 / 70%);*/
            -webkit-backdrop-filter: blur(2px);
            backdrop-filter: blur(2px);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .popup {
            width: 315px;
            height: 405px;
            background-color: white;
            border-radius: 20px;
            padding: 20px;
            font-size: 15px;
        }

        .popup-step[data-popup-id="6"] {
            position: relative;
        }

        .popup-close {
            position: absolute;
            right: 5%;
            cursor: pointer;
        }

        .popup__title {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: bold;
            text-align: center;
        }

        .popup__go {
            width: 64px;
            height: 64px;
            border-radius: 100%;
            background: #FFA73B;
        }

        .popup__next {
            background: #FF8800;
            border-radius: 20px;
            padding: 16px;
            width: 100%;
            color: white;
        }

        .popup__checkboxes.inline {
            display: flex;
            justify-content: space-around;
            width: 150px;
            margin: auto;
        }

        .popup__checkboxes.column {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .popup__checkboxes > div {
            display: flex;
            align-items: center;
        }

        .popup__checkboxes input {
            width: 20px;
            height: 20px;
        }

        .popup__checkboxes label {
            font-size: 16px;
            margin-bottom: 0;
            margin-left: 6px;
            cursor: pointer;
        }

        .popup__small-title {
            font-size: 20px;
        }

        .popup__loading-icon {
            animation: icon-spin 1.5s linear infinite;
        }

        .d-none {
            display: none !important;
        }

        .popup-step-start-end {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .popup-step {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-direction: column;
        }

        .popup-step__center-block {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .popup-step__checkbox {
            appearance: checkbox;
        }

        .mt-4 {
            margin-top: 1.5rem !important;
        }

        .font-Bold {
            font-weight: bold;
            text-align: center;
        }


        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-360deg); }
        }

        .popup__loading-icon {
            animation: spin 1s linear infinite;
        }

        .owl-carousel .owl-item{
            height: min-content;
        }
        
        .tariff_label h5{
            padding: 10px 0 20px;
        }


    </style>
    <script>
        function selectCreditDoctor(index) {
            document.getElementById('creditdoctor_' + index).checked = true;
        }

        $('.credit_doctor_tariffs').owlCarousel({
          dots: false,
          loop: true,
        });
    </script>
{/literal}
