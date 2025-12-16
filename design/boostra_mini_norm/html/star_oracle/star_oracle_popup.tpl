{* Модальное окно кредитного доктора *}
<div id="modal-staroracle">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-prolongation">
                <h5 class="modal-title-prolongation" id="modalLabel">Звездный Оракул</h5>
                <a type="button" id="closeButtonModal"
                   class="btn-close btn-close-prolongation-x btn-close-prolongation btn-close-doctor"
                   data-bs-dismiss="modal" aria-label="Close">X</a>
            </div>
{*            <div class="modal-header">*}
{*                <a type="button" id="closeButtonModal" class="btn-close btn-close-modal  pointer" data-bs-dismiss="modal" aria-label="Close">X</a>*}
{*            </div>*}
            <div class="">

                <p>С помощью “Звездного Оракула” можно прогнозировать и управлять событиями своей жизни</p>

                <h2>4 ВИДА ГОРОСКОПА</h2>
                <ul>
                    <li>Гороскоп</li>
                    <li>Карты Таро</li>
                    <li>Натальная карта</li>
                    <li>Толкователь снов</li>
                </ul>

                <h3>Ежедневный гороскоп</h3>
                <p>Гороскоп на каждый день по знакам зодиака поможет вам определить свое поведение не только сегодня, но и на несколько дней вперед.</p>

                <h3>Карты таро</h3>
                <p>Система карт, каждая из которых обладает своим значением и помогает лучше понять прошлое, настоящее и будущее. Широко используется для предсказания будущего, самопознания и духовного развития.</p>

                <h3>Натальная карта</h3>
                <p>Карта, которая показывает положение планет на небе на момент рождения человека. Используется в астрологии для анализа характера и судьбы.</p>

                <h3>Толкователь снов</h3>
                <p>Снотолкователь предназначен для истолкования сновидений, а также для ониромантии (предсказания будущего по снам)</p>


                <strong><a target="_blank" href="/files/doc/oracul_key.pdf">Образец ключа</a></strong>
            </div>
        </div>
    </div>
</div>

{literal}
    <style>

        /* ----------------------- стили для кредитного доктора------------------- */

        #modal-staroracle {
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

        #modal-staroracle label {
            text-align: center;
            display: grid;
            height: 100%;
            padding: 10px;
            border-radius: 15px;
            border: 1px solid #FF8902;
            box-sizing: border-box;
            cursor: pointer;
        }

        #modal-staroracle [name="creditdoctor_id"]:checked:not(:disabled) + label {
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

        #modal-staroracle label ul > li::before {
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

        #btn-modal-creditdoctor {
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

        #modal-staroracle [name="tv_medical_id"] {
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

            #modal-staroracle label ul > li::before {
                left: -20px;
                height: 20px;
            }

        }

        @media screen and (max-width: 540px) {
            #modal-staroracle label ul > li::before {
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


    </style>
    <script>
        function selectCreditDoctor(index) {
            document.getElementById('creditdoctor_' + index).checked = true;
        }
    </script>
{/literal}
