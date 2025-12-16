<div id="promo_banner">
{*    {$user->id|print_r:true}*}
    {foreach $promo_banners as $promo_banner}
{*        {$promo_banner->id|print_r:true}*}
        <div class="banner-content">
            <div class="banner-text">
                <p class="banner-text__desc">{$promo_banner->banner_text}
                    <a class="details-button">Подробнее</a>
                </p>
            </div>
            <div class="banner-image">
                <img src="design/{$settings->theme|escape}/img/content/new_year/{$promo_banner->banner_img_bg}"
                     alt="Monsters"/>
            </div>
        </div>
        <button id="copyButton" class="my-code-button">
            {if $promo_banner->id == 10}
                Мой код участника
            {elseif $user_code}
                {$user_code}
                <div class="copy-clipboard"></div>
            {else}
                Мой код участника
            {/if}
            <div class="ornament-left"></div>
            <div class="ornament-center"></div>
            <div class="ornament-right"></div>
        </button>
        <div id="copySuccess" style="display: none;">Код скопирован!</div>
        <div class="dialog-box">

        </div>
    {/foreach}
</div>
{include file="modals/new_year_modal.tpl"}
<style>
    #promo_banner {
        position: relative;
        max-width: 675px;
        margin-bottom: 2rem;
    }

    .banner-content {
        display: flex;
        align-items: flex-start;
        max-height: 280px;
    }

    .banner-text {
        max-width: 322px;
        position: relative;
        padding: 18px 10px 18px 28px;
        background-image: url('/design/boostra_mini_norm/img/content/new_year/union.svg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: #000000;
        height: 175px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .banner-text__desc {
        font-size: 18px !important;
        line-height: 1.2 !important;
    }

    .details-button {
        color: #40C7F7;
        text-decoration: underline;
        cursor: pointer;
    }

    .banner-image {
        text-align: right;
        position: relative;
        margin-top: 63px;
        margin-left: -5px;
    }

    .banner-image img {
        max-width: 100%;
    }

    #promo_banner .anime_text {
        -webkit-mask-image: linear-gradient(-75deg, rgba(0, 0, 0, .6) 30%, #000 50%, rgba(0, 0, 0, .6) 70%);
        -webkit-mask-size: 200%;
        animation: wave 2s infinite;
        color: var(--level_main_color);
    }

    .dialog-box {
        background-color: #fff;
        padding: 10px;
        border-radius: 5px;
        max-width: 513px;
    }

    .my-code-button {
        position: relative;
        padding: 12px 40px;
        max-width: 235px;
        height: 40px;
        background: #0997FF;
        color: #FFFFFF;
        border-radius: 5px;
        z-index: 100;
        margin-bottom: 35px;
    }

    .copy-clipboard {
        position: absolute;
        top: 50%;
        right: 5px;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        background: url('/design/boostra_mini_norm/img/icons/copy_clipboard.svg') no-repeat center center;
        background-size: contain;
    }

    .ornament-left, .ornament-center, .ornament-right {
        position: absolute;
        bottom: 0;
        width: 22px;
        height: 25px;

        z-index: -1;
    }

    .ornament-left {
        left: 0;
        top: 38px;
        background-size: cover;
        background-image: url("/design/boostra_mini_norm/img/content/new_year/christmas_ball.svg");
    }

    .ornament-center {
        left: 43%;
        top: 37px;
        width: 22px;
        height: 25px;
        background-size: cover;
        background-image: url("/design/boostra_mini_norm/img/content/new_year/christmas_ball_center.svg");
        /*transform: translateX(-50%);*/
    }

    .ornament-right {
        right: 10%;
        top: 38px;
        background-size: cover;
        background-image: url("/design/boostra_mini_norm/img/content/new_year/christmas_ball.svg");
    }

    @media screen and (max-width: 768px) {
        #promo_banner {
            display: flex;
            flex-flow: column;
        }

        .banner-content {
            flex-flow: column;
        }

        .banner-text {
            max-width:281px;
            margin-bottom: 2rem;
            padding: 7px 20px 18px 28px;
        }

        .banner-image {
            text-align: center;
            position: relative;
        }

        .banner-image img {
            position: absolute;
            bottom: -60px;
            left: 336px;
            max-width: unset;
        }

        .my-code-button {
            max-width: 300px;
            width: 300px;
            margin-bottom: 45px;
            font-size: 1rem;
        }
    }

    @media screen and (max-width: 600px) {
        .banner-text {
            max-width: 268px;
            padding: 35px 29px 18px 29px;
        }

        .banner-content {
            max-height: 248px;
        }
    }

    @media screen and (max-width: 542px) {
        .banner-image img {
            left: 70px;
            top: -80px;
        }

        .banner-content {
            margin-bottom: 230px;
        }
    }

    @media screen and (max-width: 414px) {
        .banner-text {
            max-width: 281px;
            padding: 27px 11px 18px 29px;
        }
    }

    @media screen and (max-width: 393px) {
        .banner-text {
            padding: 22px 10px 18px 25px;
        }
    }

    @media screen and (max-width: 374px) {
        .banner-text {
            padding: 26px 14px 18px 17px;
        }
    }

    @media screen and (max-width: 361px) {
        .banner-text {
            max-width: 249px;
            padding: 0 14px 0 17px;
        }
    }

    @-webkit-keyframes wave {
        from {
            -webkit-mask-position: 150%;
        }
        to {
            -webkit-mask-position: -50%;
        }
    }

</style>
<script>
    const userId = {$user->id};
    const additionalText = '{$promo_banner->additional_text|escape}';
    const currentBannerId = '{$promo_banner->id}';
</script>
{literal}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('#copyButton').addEventListener('click', function () {
                const buttonText = this.innerText;

                if (buttonText !== 'Мой код участника') {
                    copyToClipboard(buttonText);
                    document.getElementById('copySuccess').style.display = 'block';
                    setTimeout(() => {
                        document.getElementById('copySuccess').style.display = 'none';
                    }, 3000);
                } else {
                    console.log('Нельзя скопировать код, так как он еще не сгенерирован.');
                }
            });

            function copyToClipboard(text) {
                navigator.clipboard.writeText(text)
                    .then(() => console.log('Текст скопирован успешно!'))
                    .catch(err => console.error('Ошибка при копировании текста:', err));
            }

            const promoBanners = document.querySelectorAll('.banner-content');
            promoBanners.forEach(banner => {
                const myCodeButton = document.querySelector('.my-code-button');
                const dialogBox = document.querySelector('.dialog-box');

                if (['7', '8', '9', '11'].includes(currentBannerId)) {
                    dialogBox.innerHTML = additionalText;
                    dialogBox.style.border = '2px dashed #40C7F7';
                } else {
                    myCodeButton.addEventListener('click', () => {
                        dialogBox.innerHTML = additionalText;
                        dialogBox.style.border = '2px dashed #40C7F7';
                        fetch('ajax/promo_banners_actions.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'get_promo_code',
                                user_id: userId,
                            }),
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log(data);
                                if (data.promo_code_message !== 'Невозможно сгенерировать код.') {
                                    myCodeButton.innerHTML = data.promo_code_message;
                                }
                            })
                            .catch(error => {
                                console.error('Ошибка при обработке запроса:', error);
                            });
                    });
                }
            });
        });
    </script>
{/literal}