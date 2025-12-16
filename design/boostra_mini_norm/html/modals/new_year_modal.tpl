<div id="new-year-modal-content" class="mfp-hide new-year-modal-content">
    <a id="close-modal" class="custom-close">X</a>
    <div class="new-year-modal-container">
        <img src="design/{$settings->theme|escape}/img/content/new_year/left_character.png" alt="Левый персонаж" class="new-year-character new-year-character-left">
        <img src="design/{$settings->theme|escape}/img/content/new_year/right_character.png" alt="Правый персонаж" class="new-year-character new-year-character-right">


        <div class="new-year-modal-text">
            <h4 style="font-weight: 700">Страшно милый</h4>
            <h4 style="font-weight: 700" class="new-year-modal-title">Новогодний розыгрыш 250 000 рублей</h4>
            <p><strong>Когда?</strong> 16 января 2024 года</p>
            <p><strong>Где?</strong> В официальном сообществе <a href="https://vk.com/boostra_zaim">Boostra - займы на карту</a></p>
            <p><strong>Как определим победителя?</strong> Случайным образом с помощью специального сервиса Randomus</p>
            <p><strong>Как стать участником?</strong></p>
            <ul>
                <li><strong>Участник 1 уровня.</strong> Доступно участие в розыгрыше 10 призов по 10 000 рублей.
                    <p><strong>Что нужно сделать?</strong> Открыть 1 не первый договор займа, пользоваться займом не
                        менее
                        16 дней и без просрочек, закрыть до 15.01.2024 г.</p>
                </li>
                <li><strong>Участник 2 уровня.</strong> Доступно участие в розыгрыше 5 призов по 30 000 рублей.
                    <p><strong>Что нужно сделать?</strong> Выполнить условия Уровня 1. Открыть ещё 1 договор займа,
                        пользоваться займом не менее 16 дней и без просрочек, закрыть до 15.01.2024 г.</p>
                </li>
            </ul>
        </div>
        <span>Забирай займы  - получай денежные призы!</span>
    </div>
    <div class="character-img-wrap">

        <img src="design/{$settings->theme|escape}/img/content/new_year/down_character.svg" alt="Нижний персонаж" class="new-year-character new-year-character-bottom">
    </div>
    <div class="new-year-snowflakes"></div>
</div>
{literal}
    <style>
        #close-modal {
            cursor: pointer;
            position: absolute;
            top: 15px;
            right: 15px;
            color: #000;
        }

        #new-year-modal-content {
            position: relative;
            background-color: #fff;
            color: #000;
            padding: 20px 40px;
            max-width: 500px;
            margin: 20px auto;
            border-radius: 10px;
            overflow: hidden;
        }

        .new-year-character {
            position: absolute;
        }

        .new-year-character-left {
            left: -40px;
            top: 37%;
            transform: translateY(-50%);
            width: 50px;
        }

        .new-year-character-right {
            right: -40px;
            top: 30%;
            transform: translateY(-50%);
            width: 102px;
        }

        .character-img-wrap {
            display: flex;
            justify-content: end;
            margin-right: 63px;
            margin-top: 40px;
        }

        .new-year-character-bottom {
            position: unset;
            width: 63px;
        }

        .new-year-modal-container {
            position: relative;
        }

        .new-year-modal-text h4 {
            text-align: center;
        }

        .new-year-modal-text a {
            color: #40C7F7;
        }

        .new-year-modal-text .new-year-modal-title {
            margin-bottom: 20px;
        }

        .new-year-modal-text p {
            margin: 0 0 10px;
        }

        .new-year-modal-text ul {
            list-style-type: none;
            padding: 0;
        }

        .new-year-modal-text li {
            margin-bottom: 10px;
        }

        .new-year-modal-text li p {
            margin: 0;
        }

        .new-year-modal-container span {
            display: flex;
            justify-content: center;
            text-align: center;
            font-weight: 600;
        }

        .new-year-snowflakes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .new-year-snowflake {
            position: absolute;
            width: 25px;
            height: 25px;
            background: url('/design/boostra_mini_norm/img/content/new_year/snowflake_big.svg') no-repeat center center;
            background-size: cover;
            animation: new-year-snowfall linear infinite;
            top: -15px;
        }

        @keyframes new-year-snowfall {
            to {
                transform: translateY(100vh);
            }
        }

        @media screen and (max-width: 768px){
            .new-year-character-left {
                left: -40px;
                top: 35%;
                width: 40px;
            }

            .new-year-character-right {
                right: -40px;
                top: 30%;
                width: 90px;
            }
        }

    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const snowflakeContainer = document.querySelector('#new-year-modal-content .new-year-snowflakes');

            for (let i = 0; i < 30; i++) {
                const snowflake = document.createElement('div');
                snowflake.className = 'new-year-snowflake';
                snowflake.style.left = Math.random() * 100 + '%';
                snowflake.style.animationDuration = Math.random() * 8 + 2 + 's';
                snowflake.style.animationDelay = Math.random() * 2 + 's';
                snowflakeContainer.appendChild(snowflake);
            }

            const detailsButton = document.querySelector('.details-button');
            const magnificPopupInstance = $.magnificPopup.instance;

            detailsButton.addEventListener('click', (event) => {
                event.preventDefault();
                $.magnificPopup.open({
                    items: {
                        src: '#new-year-modal-content',
                    },
                    showCloseBtn: true,
                    modal: true,
                    type: 'inline',
                });
            });

            document.addEventListener('click', (event) => {
                const closeBtn = event.target.closest('.custom-close');
                if (closeBtn) {
                    magnificPopupInstance.close();
                }
            });
        });
    </script>
{/literal}