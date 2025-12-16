{literal}
    <style>
        #tv_medical__wrapper h3 {
            margin-bottom: 10px;
        }

        #tv_medical__wrapper p span {
            font-family: initial;
        }

        #tv_medical__wrapper > ul {
            padding-left: 0;
            display: flex;
            max-width: 100%;
            list-style: none;
            gap: 5px;
        }

        #tv_medical__wrapper > ul > li {
            overflow-wrap: anywhere;
            flex: 1 1 0;
        }

        #modal-telemed label {
            text-align: center;
            display: grid;
            height: 100%;
            padding: 10px;
            border-radius: 15px;
            border: 1px solid #0D64E2;
            box-sizing: border-box;
            cursor: pointer;
        }

        #modal-multipolis label {
            text-align: center;
            display: grid;
            height: 100%;
            padding: 10px;
            border-radius: 15px;
            border: 1px solid #E65B5B;
            box-sizing: border-box;
            cursor: pointer;
        }

        #modal-telemed [name="tv_medical_id"]:checked:not(:disabled) + label {
            background: radial-gradient(circle at 100% 100%, #ffffff 0, #ffffff 14px, transparent 14px) 0% 0%/15px 15px no-repeat,
            radial-gradient(circle at 0 100%, #ffffff 0, #ffffff 14px, transparent 14px) 100% 0%/15px 15px no-repeat,
            radial-gradient(circle at 100% 0, #ffffff 0, #ffffff 14px, transparent 14px) 0% 100%/15px 15px no-repeat,
            radial-gradient(circle at 0 0, #ffffff 0, #ffffff 14px, transparent 14px) 100% 100%/15px 15px no-repeat,
            linear-gradient(#ffffff, #ffffff) 50% 50%/calc(100% - 2px) calc(100% - 30px) no-repeat,
            linear-gradient(#ffffff, #ffffff) 50% 50%/calc(100% - 30px) calc(100% - 2px) no-repeat,
            linear-gradient(90deg, #0D64E2 0%, #2197E7 100%);
        }

        #modal-multipolis [name="multipolis_id"]:checked:not(:disabled) + label {
            background: radial-gradient(circle at 100% 100%, #ffffff 0, #ffffff 14px, transparent 14px) 0% 0%/15px 15px no-repeat,
            radial-gradient(circle at 0 100%, #ffffff 0, #ffffff 14px, transparent 14px) 100% 0%/15px 15px no-repeat,
            radial-gradient(circle at 100% 0, #ffffff 0, #ffffff 14px, transparent 14px) 0% 100%/15px 15px no-repeat,
            radial-gradient(circle at 0 0, #ffffff 0, #ffffff 14px, transparent 14px) 100% 100%/15px 15px no-repeat,
            linear-gradient(#ffffff, #ffffff) 50% 50%/calc(100% - 2px) calc(100% - 30px) no-repeat,
            linear-gradient(#ffffff, #ffffff) 50% 50%/calc(100% - 30px) calc(100% - 2px) no-repeat,
            linear-gradient(90deg, #E21212 0%, #E65B5B 100%);
        }

        #tv_medical__wrapper [name="tv_medical_id"]:disabled + label {
            cursor: no-drop;
            opacity: .5;
        }

        #tv_medical__wrapper [name="multipolis_id"]:disabled + label {
            cursor: no-drop;
            opacity: .5;
        }

        #tv_medical__wrapper label ul {
            text-align: left;
            margin: 20px 0;
            font-size: 13px;
            list-style-type: none;
            padding-left: 20px;
            position: relative;
            box-sizing: border-box;
        }

        #tv_medical__wrapper label ul li {
            margin: 10px 0;
            list-style-position: outside;
            padding-left: 10px;
        }

        #tv_medical__wrapper label ul li::marker {
            font-size: 16px;
        }

        #modal-multipolis label ul > li::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 0;
            width: 10%;
            height: 30%;
            background: url('/design/boostra_mini_norm/img/svg/assignment.svg') no-repeat center center / contain;
        }


        #modal-telemed label ul > li::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 0;
            width: 10%;
            height: 30%;
            background: url('/design/boostra_mini_norm/img/svg/heart.svg') no-repeat center center / contain;
        }


        #modal-telemed [name="tv_medical_id"] {
            display: none;
        }

        #tv_medical__wrapper label button {
            margin: auto;
            display: flex;
            text-transform: uppercase;
            border: 3px solid;
        }

        #tv_medical__wrapper label span {
            margin: auto;
            display: flex;
            text-transform: uppercase;
            border: 3px solid;
        }

        @media screen and (max-width: 768px) {
            #tv_medical__wrapper label ul li {
                font-size: .85rem;
                padding-left: 5px;
            }

            #tv_medical__wrapper label ul, #modal-telemed label ul {
                margin: 10px 0;
                padding-left: 25px;
            }

            #tv_medical__wrapper label ul li::marker {
                font-size: 12px;

            }

            #tv_medical__wrapper label button {
                border: 2px solid;
                overflow-wrap: break-word;
                padding: 5px;
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            #tv_medical__wrapper label span {
                border: 2px solid;
                overflow-wrap: break-word;
                padding: 5px;
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            #tv_medical__wrapper > ul {
                flex-flow: column;
            }
        }

        @media screen and (max-width: 540px) {
            #modal-telemed label ul > li::before, #modal-multipolis label ul > li::before {
                left: 0 !important;
            }
        }

        /* ----------------------- модальные окна, слайдер ------------------- */

        #modal-multipolis, #modal-telemed {
            background: #fff;
            max-width: 718px;
            border-radius: 20px;
            margin: 0 auto;
            padding: 20px;
        }

        #modal-multipolis .modal-content,
        #modal-telemed .modal-content {
            width: unset;
            border: unset;
            margin: 0 auto;
        }

        #modalContainerTelemedCancel .modal-content,
        #modalContainerMultiCancel .modal-content,
        #modalContainerMulti .modal-content,
        #modalContainer .modal-content {
            margin: 0 auto;
            width: unset;
            border: unset;
        }

        #modalContainer, #modalContainerMulti,
        #modalContainerTelemedCancel,
        #modalContainerMultiCancel {
            background: #fff;
            max-width: 500px;
            border-radius: 20px;
            margin: 0 auto;
            padding: 20px;
        }

        .question-block {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-content: center;
            align-items: center;
            text-align: center;
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
            width: 599px;
            margin: 0 auto;
        }

        .modal-body-prolongation p {
            padding: 0 15px;
        }

        .icon-text-container {
            display: flex;
            align-items: center;
        }

        .icon-prolongation {
            width: 41px;
            height: 41px;
        }

        #modal-telemed .profile {
            display: flex;
            align-items: center;
            background: radial-gradient(circle at 100% 100%, #ffffff 0, #ffffff 14px, transparent 14px) 0% 0%/15px 15px no-repeat,
            radial-gradient(circle at 0 100%, #ffffff 0, #ffffff 14px, transparent 14px) 100% 0%/15px 15px no-repeat,
            radial-gradient(circle at 100% 0, #ffffff 0, #ffffff 14px, transparent 14px) 0% 100%/15px 15px no-repeat,
            radial-gradient(circle at 0 0, #ffffff 0, #ffffff 14px, transparent 14px) 100% 100%/15px 15px no-repeat,
            linear-gradient(#ffffff, #ffffff) 50% 50%/calc(100% - 2px) calc(100% - 30px) no-repeat,
            linear-gradient(#ffffff, #ffffff) 50% 50%/calc(100% - 30px) calc(100% - 2px) no-repeat,
            linear-gradient(90deg, #0D64E2 0%, #2197E7 100%);
            border-radius: 15px;
            padding: 5px;
        }

        #modal-multipolis .profile {
            display: flex;
            align-items: center;
            background: radial-gradient(circle at 100% 100%, #ffffff 0, #ffffff 14px, transparent 14px) 0% 0%/15px 15px no-repeat,
            radial-gradient(circle at 0 100%, #ffffff 0, #ffffff 14px, transparent 14px) 100% 0%/15px 15px no-repeat,
            radial-gradient(circle at 100% 0, #ffffff 0, #ffffff 14px, transparent 14px) 0% 100%/15px 15px no-repeat,
            radial-gradient(circle at 0 0, #ffffff 0, #ffffff 14px, transparent 14px) 100% 100%/15px 15px no-repeat,
            linear-gradient(#ffffff, #ffffff) 50% 50%/calc(100% - 2px) calc(100% - 30px) no-repeat,
            linear-gradient(#ffffff, #ffffff) 50% 50%/calc(100% - 30px) calc(100% - 2px) no-repeat,
            linear-gradient(90deg, #E21212 0%, #E65B5B 100%);
            border-radius: 15px;
            padding: 5px;
        }

        .profile-image img {
            width: 100px;
            height: auto;
            border-radius: 50%;
            margin-right: 15px;
        }

        .profile-info {
            flex: 1;
        }

        .name {
            font-size: 18px;
            font-weight: bold;
        }

        .position {
            font-size: 16px;
        }

        .experience {
            font-size: 14px;
            color: #888;
        }

        .owl-carousel-prolongation {
            max-width: 100%;
            overflow: hidden;
        }

        .owl-carousel-prolongation .owl-stage-outer {
            margin-bottom: 20px;
        }

        .owl-carousel-prolongation .owl-stage {
            display: flex;
            flex-wrap: wrap;
            transition: all 0.25s ease 0s;
        }

        .owl-carousel-prolongation .owl-item {
            /*max-width: 455px!important;*/
            /*margin-right: 10px;*/
        }

        .owl-carousel-prolongation .owl-item.active {
            background-color: transparent;
        }

        .owl-carousel-prolongation .owl-nav {
            display: none;
        }

        .owl-carousel-prolongation .owl-nav button {
            /*background: none;*/
            /*border: none;*/
            /*font-size: 20px;*/
        }

        .owl-carousel-prolongation .owl-dots {
            display: flex;
            justify-content: center;
        }

        #modal-multipolis .owl-carousel-prolongation .owl-dot {
            background: transparent;
            border: 1px solid #E21212;
            padding: unset;
            box-shadow: unset;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            margin: 0 5px;
        }

        #modal-multipolis .owl-carousel-prolongation .owl-dot.active {
            background: #E21212;
        }

        .owl-carousel-prolongation .owl-dot {
            background: transparent;
            border: 1px solid #339AE5;
            padding: unset;
            box-shadow: unset;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            margin: 0 5px;
        }

        .owl-carousel-prolongation .owl-dot.active {
            background: #339AE5;
        }

        .btn-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .btn-close-quiz {
            position: absolute;
            right: -8px !important;
            bottom: 40px !important;
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

        .btn-prolongation-tvmedical {
            max-width: 340px;
            height: 39px;
            border-radius: 20px;
            border: none;
            color: #FFFFFF;
            font-weight: 900;
            background: linear-gradient(to right, #0D64E2, #2197E7);
            padding: 0 10px;
            transition: all 0.25s ease 0s;
        }

        .btn-prolongation-tvmedical:hover {
            background: transparent;
            border-radius: 20px;
            border: 1px solid #0D64E2;
            color: #2197E7;
            transition: all 0.25s ease 0s;

        }

        .btn-prolongation-telemed {
            width: 100%;
            height: 39px;
            border-radius: 20px;
            border: none;
            color: #FFFFFF;
            font-weight: 900;
            background: linear-gradient(to right, #0D64E2, #2197E7);
            padding: 0 10px;
            transition: all 0.25s ease 0s;
        }

        .btn-prolongation-telemed:hover {
            background: transparent;
            border-radius: 20px;
            border: 1px solid #0D64E2;
            color: #2197E7;
            transition: all 0.25s ease 0s;

        }

        .text-wrapper p {
            padding-top: 15px;
            text-align: center;
        }


        .choices-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            justify-items: center;
        }

        .choices-container label {
            display: flex;
            /*justify-content: center;*/
            align-items: center;
            position: relative;
            padding-left: 35px;
            margin-bottom: 12px;
            cursor: pointer;
            font-size: 17px;
            user-select: none;

        }

        .choices-container label:nth-last-child(2):first-child {
            grid-column: 1 / 3;
        }

        .choices-container label:last-child {
            grid-column: 1 / 3;
            justify-self: center;
        }

        .choices-container span {
            position: absolute;
            top: 0;
            left: 0;
            height: 25px;
            width: 25px;
            background-color: #c9c9c9;
            border-radius: 50%;
        }

        .choices-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .choices-container label input:checked ~ span {
            background: linear-gradient(to bottom, #0D64E2, #2197E7);
        }

        .choices-container span:after {
            content: "";
            position: absolute;
            display: none;
        }

        .choices-container label input:checked ~ span:after {
            display: block;
        }

        .choices-container label span:after {
            position: absolute;
            top: 9px;
            left: 8px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            content: '';
            background: url("design/orange_theme/img/tick.svg") 0 0 no-repeat;
        }

        .btn-prolongation-tvmedical {
            background: linear-gradient(to right, #0D64E2, #2197E7);
            color: #fff;
            width: 100%;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-prolongation-multipolis {
            background: linear-gradient(to right, #E21212, #E65B5B);
            color: #fff;
            width: 100%;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-prolongation-multipolis:hover {
            background: transparent;
            transition: background-color 0.3s ease;
            color: #E21212;
            border: 1px solid #E21212;
            border-radius: 20px;
        }

        .btn-prolongation-tvmedical.next-button {
            padding: 7px;
            margin-top: 50px;
        }

        .btn-prolongation-tvmedical:disabled {
            background-color: #d9d9d9;
            cursor: not-allowed;
        }

        .button-telemed-inverse {
            color: #FFFFFF;
            background: linear-gradient(to right, #0D64E2, #2197E7);
            box-shadow: none;
            padding: 10px 33px;
            border-radius: 1.6rem;
            text-transform: unset !important;
        }

        .button-multipolis-inverse {
            color: #FFFFFF;
            background: linear-gradient(to right, #E21212, #E65B5B);
            box-shadow: none;
            padding: 10px 33px;
            border-radius: 1.6rem;
            text-transform: unset !important;
        }

        #tv_medical__wrapper li label {
            position: relative;
        }

        #tv_medical__wrapper li label .button-telemed-inverse {
            background-color: transparent;
            padding: 10px 33px;
            color: #ffffff;
            border: 1px solid #0D64E2;
        }

        #tv_medical__wrapper li label .button-telemed-inverse:hover {
            background-color: transparent;
            padding: 10px 33px;
            color: #0D64E2;
            border: 1px solid #0D64E2;
        }

        #tv_medical__wrapper li label .button-multipolis-inverse {
            background-color: transparent;
            padding: 10px 33px;
            color: #ffffff;
            border: 1px solid #E65B5B;

        }

        #tv_medical__wrapper li label .button-multipolis-inverse:hover {
            background-color: transparent;
            padding: 10px 33px;
            color: #E21212;
            border: 1px solid #E65B5B;

        }

        #tv_medical__wrapper li input:checked + label .button {
            background: linear-gradient(to right, #0D64E2, #2197E7);
            padding: 10px 33px;
            color: #ffffff;
        }

        #tv_medical__wrapper li input:checked + label .button-multipolis-inverse {
            background: linear-gradient(to right, #E21212, #E65B5B);
            padding: 10px 33px;
            color: #ffffff;
        }

        #modal-multipolis li input:checked + label {
            border: 2px solid #E21212;
        }

        .question-text {
            color: #808080;
        }

        .question-text-bold {
            color: #000000;
            margin-bottom: 50px;
        }

        .question-block:not(:first-child) {
            display: none;
        }

        .paragraph-container {
            position: relative;
        }

        .image1::before {
            content: url('design/boostra_mini_norm/img/icons/icon_med.png');
            display: block;
            margin-right: 10px;
            position: absolute;
            left: -15px;
            top: 0;
        }

        .image2::before {
            content: url('design/boostra_mini_norm/img/icons/icon_pills.png');
            display: block;
            margin-right: 10px;
            position: absolute;
            left: -15px;
            top: 0;
        }

        .image3::before {
            content: url('design/boostra_mini_norm/img/icons/icon_card.png');
            display: block;
            margin-right: 10px;
            position: absolute;
            left: -15px;
            top: 0;
        }

        .image4::before {
            content: url('design/boostra_mini_norm/img/icons/icon_percent.png');
            display: block;
            margin-right: 10px;
            position: absolute;
            left: -15px;
            top: 0;
        }

        @media screen and (max-width: 768px) {
            .icon-prolongation {
                width: 30px;
                height: 30px;
            }

            #modal-telemed label ul > li:first-child::before {
                top: 7px;
                left: -16px;
                width: 10%;
                height: 20%;
            }

            #modal-telemed label ul > li:nth-child(2)::before {
                top: 32px;
                left: -16px;
                width: 10%;
                height: 20%;
            }

            #modal-telemed label ul > li:last-child::before {
                top: 10px;
                left: -15px;
                width: 10%;
                height: 30%;
            }

            #modal-multipolis label ul > li::before {
                top: 9px;
                left: -25px;
            }

            .modal-body-prolongation {
                width: 100% !important;
            }

            #tv_medical__wrapper li label .button-multipolis-inverse, #tv_medical__wrapper li input:checked + label .button-multipolis-inverse, #tv_medical__wrapper li label .button-telemed-inverse, #tv_medical__wrapper li input:checked + label .button {
                padding: 10px 0;
            }

        }

        .mfp-bg {
            width: 100% !important;
            position: fixed !important;
        }

        .pointer {
            cursor: pointer;
        }
        
        a.btn-modal-telemed{
            text-decoration: underline;
        }
    </style>
{/literal}

<div class="hidden">
    <div id="modal-telemed">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-prolongation">
                    <div class="title-wrap">
                        <h4 class="modal-title-prolongation" id="modalLabel">Вита-мед</h4>
                    </div>
                    <a type="button" id="closeButtonModal"
                       class="btn-close btn-close-modal  btn-close-prolongation-x" data-bs-dismiss="modal"
                       aria-label="Close">X</a>
                </div>
                <div class="modal-body-prolongation">
                    <div class="paragraph-container image1">
                        <p>
                            Возврат НДФЛ за приобретенное лекарство или оказанное лечение - в этом режиме вы можете
                            получить консультацию по заполнению 3НДФЛ с целью получения вычета.</p>
                    </div>
                    <div class="paragraph-container image2">
                        <p>
                            Поиск выгодных аналогов лекарств - Мы подберём для вас все аналоги искомого лекарственного
                            средства, а также сравним стоимость препаратов и наличие в ближайших аптеках.</p>
                    </div>
                    <div class="paragraph-container image3">
                        <p>
                            Запись к врачу - помощь в записи к  врачу на удобное для вас время, через информационную
                            систему
                            здравоохранения ЕГИСЗ.</p>
                    </div>
                    <div class="paragraph-container image4">
                        <p>
                            Возврат 25% от стоимости купленного лекарственного препарата в любой аптеке</p>
                    </div>

                    <div class="text-wrapper">
                        <p>Проверьте прямо сейчас, нужна ли Вам помощь врача-терапевта.</p>
                    </div>
                    <div class="btn-wrapper">
                        <button class="btn-prolongation-tvmedical" id="startButton" type="button"
                                data-bs-toggle="modal-prol" data-bs-target="#modal-prol">Проверить себя
                        </button>
                    </div>
                    <div id="tv_medical__wrapper">
                        <ul>
                            <li>
                                <input id="tv_medical_0"
                                       type="radio"
                                       name="tv_medical_id"
                                       data-amount="{$tv_medical_tariffs[0]->price}"
                                       data-number="{$user->balance->zaim_number}"
                                       value="{$vita_med_tariffs[1]->id}"
                                       checked
                                />
                                <label for="tv_medical_0">
                                    <h3><b>Лайт</b></h3>
                                    <h5>{$tv_medical_tariffs[0]->price} руб / {$tv_medical_tariffs[0]->days} дней</h5>
                                    <ul>
                                        <li>Консультация с 9 до 19 ч по мск</li>
                                    </ul>
                                    <span class="button btn-choose-tariff medium" type="button" data-amount="{$tv_medical_tariffs[0]->price}">Выбрать</span>

                                </label>
                            </li>
                            <li>
                                <input id="tv_medical_1"
                                       type="radio"
                                       name="tv_medical_id"
                                       data-amount="{$tv_medical_tariffs[0]->price}"
                                       data-number="{$user->balance->zaim_number}"
                                       value="{$vita_med_tariffs[1]->id}"
                                />
                                <label for="tv_medical_1">
                                    <h3><b>Комфорт</b></h3>
                                    <h5>{$tv_medical_tariffs[1]->price} руб / {$tv_medical_tariffs[1]->days} дней</h5>
                                    <ul>
                                        <li>Консультация круглосуточно</li>
                                    </ul>
                                    <span class="button btn-choose-tariff medium" type="button" data-amount="{$tv_medical_tariffs[1]->price}">Выбрать</span>
                                </label>
                            </li>
                            <li>
                                <input id="tv_medical_2"
                                       type="radio"
                                       name="tv_medical_id"
                                       data-amount="{$tv_medical_tariffs[0]->price}"
                                       data-number="{$user->balance->zaim_number}"
                                       value="{$vita_med_tariffs[1]->id}"
                                />
                                <label for="tv_medical_2">
                                    <h3><b>Премиум</b></h3>
                                    <h5>{$tv_medical_tariffs[2]->price} руб / {$tv_medical_tariffs[2]->days} дней</h5>
                                    <ul>
                                        <li>Консультация круглосуточно</li>
                                    </ul>
                                    <span class="button btn-choose-tariff medium" type="button" data-amount="{$tv_medical_tariffs[2]->price}">Выбрать</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p style="font-size: 12px;">ООО МКК «Аквариус» ИНН 9714011290, ОГРН 1237700365506</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {* Прочие модальные окна из той же серии *}
    <div id="modalContainer">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-prolongation">
                    <h5 class="modal-title text-center" id="modalTitle">Пройдите короткий тест, чтобы понять, нужна ли
                        Вам помощь
                        врача-терапевта</h5>
                </div>
                <div class="modal-body">
                    <div class="question-block" data-question="1">
                        <br class="question-text">#1<br> Сколько вам лет?</p>
                        <div class="choices-container" id="choicesContainer1">
                            <label>
                                <input type="radio" name="question1" value="до 20">
                                <span></span>
                                до 20
                            </label>
                            <label>
                                <input type="radio" name="question1" value="21-30">
                                <span></span>
                                21-30
                            </label>
                            <label>
                                <input type="radio" name="question1" value="31-40">
                                <span></span>
                                31-40
                            </label>
                            <label>
                                <input type="radio" name="question1" value="41-50">
                                <span></span>
                                41-50
                            </label>
                            <label>
                                <input type="radio" name="question1" value="более 51 года">
                                <span></span>
                                более 51 года
                            </label>
                        </div>
                        <button class="btn-prolongation-tvmedical next-button" data-next="2">Далее</button>
                    </div>
                    <div class="question-block" data-question="2" style="display: none;">
                        <p class="question-text">#2<br> Выберите пол:</p>
                        <div class="choices-container" id="choicesContainer2">
                            <label>
                                <input type="radio" name="question2" value="Мужчина">
                                <span></span>
                                Мужчина
                            </label>
                            <label>
                                <input type="radio" name="question2" value="Женщина">
                                <span></span>
                                Женщина
                            </label>
                        </div>
                        <button class="btn-prolongation-tvmedical next-button" data-next="3">Далее</button>
                    </div>
                    <div class="question-block" data-question="3" style="display: none;">
                        <p class="question-text">#3<br> Бывает ли у вас повышенное/пониженное артериальное давление?</p>
                        <div class="choices-container" id="choicesContainer3">
                            <label>
                                <input type="radio" name="question3" value="Да">
                                <span></span>
                                Да
                            </label>
                            <label>
                                <input type="radio" name="question3" value="Нет">
                                <span></span>
                                Нет
                            </label>
                            <label>
                                <input type="radio" name="question3" value="Не измеряю">
                                <span></span>
                                Не измеряю
                            </label>
                        </div>
                        <button class="btn-prolongation-tvmedical next-button" data-next="4">Далее</button>
                    </div>
                    <div class="question-block" data-question="4" style="display: none;">
                        <p class="question-text">#4<br> Бывает ли у вас отдышка?</p>
                        <div class="choices-container" id="choicesContainer4">
                            <label>
                                <input type="radio" name="question4" value="Да">
                                <span></span>
                                Да
                            </label>
                            <label>
                                <input type="radio" name="question4" value="Нет">
                                <span></span>
                                Нет
                            </label>
                        </div>
                        <button class="btn-prolongation-tvmedical next-button" data-next="5">Далее</button>
                    </div>
                    <div class="question-block" data-question="5" style="display: none;">
                        <h3 class="question-text-bold">{$user->firstname|escape}, Вам требуется консультация
                            врача-терапевта хотя бы каждые полгода.
                            Обратитесь к специалисту по месту жительства или воспользуйтесь услугой Вита-мед.</h3>
                        <button class="btn-prolongation-tvmedical next-button" data-next="6">Далее</button>
                    </div>
                    <div class="question-block" data-question="6" style="display: none;">
                        <div class="modal-header modal-header-prolongation">
                            <a type="button" class="btn-close btn-close-prolongation-x btn-modal-telemed"
                               data-bs-dismiss="modalContainerMulti"
                               aria-label="Close" id="nextButton">X</a>
                        </div>
                        <div class="question-block-multipolis">
                            <h3 class="question-text-bold">Врач Вас ждет! Записаться на прием вы сможете в личном кабинете
                                после
                                оплаты услуги</h3>
                            <button class="btn-prolongation-tvmedical" id="nextButton">Оплатить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalContainerMulti">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-prolongation">
                    <a type="button" class="btn-close btn-close-prolongation-x btn-close-prolongation"
                       data-bs-dismiss="modalContainerMulti"
                       aria-label="Close" id="closeButton">X</a>
                </div>
                <div class="modal-body">
                    <div class="question-block-multipolis">
                        <h3 class="question-text-bold">Запись на консультацию доступна в личном кабинете после покупки
                            услуги</h3>
                        <button class="btn-prolongation-multipolis btn-prolongation-multi-consult" id="nextButtonMulti">
                            Оплатить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalContainerMultiCancel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="question-block-multipolis">
                        <h3 class="question-text-bold">Вы отказались от услуги «Консьерж сервис»</h3>
                        <button class="btn-prolongation-multipolis btn-close-multi">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalContainerTelemedCancel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="question-block-multipolis">
                        <h3 class="question-text-bold">Вы отказались от услуги «Вита-мед»</h3>
                        <button class="btn-prolongation-telemed btn-close-telemed">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {* Модальное окно вита-мед *}
</div>


<script type="text/javascript">

  const elements = document.getElementsByClassName('btn-choose-tariff');

  if (elements) {
    Array.from(elements).forEach(element => {
      element.addEventListener('click', (event) => {
        document.getElementById('tv_med_amount').innerHTML=element.getAttribute('data-amount')
        $.magnificPopup.open({
          items: { src: '#document_wrapper'},
          type: 'inline',
          showCloseBtn: false,
          modal: true,
        });
        $.magnificPopup.close();
      });
    });
  }

</script>
