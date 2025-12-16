{if $show_rating_banner}
    <style type="text/css">
        .credit_rating_wrapper {
            display: -ms-grid;
            display: grid;
            -webkit-box-pack: start;
            -ms-flex-pack: start;
            justify-content: flex-start;
            margin-bottom: 15px;
        }
        .rating-collapsed {
            display: none;
        }

        .credit_rating_wrapper h2 {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            padding: 10px 0;
        }

        #rating_speedometer {
            position: relative;
            margin: auto;
            max-width: -webkit-max-content;
            max-width: -moz-max-content;
            max-width: max-content;
        }

        #rating_arrow {
            left: calc(50% - 40px);
            margin: auto;
            position: absolute;
            bottom: 22px;
            display: inline-block;
            -webkit-transform-origin: bottom center;
            -ms-transform-origin: bottom center;
            transform-origin: bottom center;
            -webkit-transition-duration: 1s;
            -o-transition-duration: 1s;
            transition-duration: 1s;
            -webkit-transition-property: all;
            -o-transition-property: all;
            transition-property: all;
            -webkit-transition-timing-function: ease-in-out;
            -o-transition-timing-function: ease-in-out;
            transition-timing-function: ease-in-out;
            -webkit-transform: rotate(-90deg);
            -ms-transform: rotate(-90deg);
            transform: rotate(-90deg);
        }
        @media all and (max-width:480px)
        {
            #rating_arrow {
                left: calc(50% - 15px) !important;
                width: 17%;
                bottom: 12px;
            }

            #rating_meter {
                width: 100% !important;
            }

            #rating_collapsed_block,
            .credit-rating-result,
            #rating_collapsed_block_text,
            #rating_collapsed_block_button {
                width: 100% !important;
            }
        }
        .get-credit-rating-button {
            display: grid;
            text-align: center;
            max-width: max-content;
            width: 100%;
            grid-gap: 5px;
        }
        .get-credit-rating-button small {
            font-size: 16px;
        }
        .spinner_wrapper {
            position: fixed;
            height: 100vh;
            width: 100vw;
            top: 0;
        }
        .spinner_wrapper:before {
            content: '';
            background-color: rgba(255,255,255,.5);
            display: block;
            position: fixed;
            height: 100vh;
            width: 100vw;
            top: 0;
            left: 0;
            z-index: 1;
        }
        .spinner_wrapper .spinner_text {
            left: calc(50% - 150px);
            padding: 0;
            position: absolute;
            top: 50%;
            z-index: 99999;
            display: inline-block;
            margin-top: 50px;
            font-size: 21px;
            font-weight: bold;
        }
        #success_message_pay {
            padding: 5px;
            display: inline-block;
        }
        @media screen and (max-width: 520px) {
            .button.medium.get-credit-rating-button {
                padding: 15px;
                width: 100%;
                box-sizing: border-box;
                max-width: initial;
                display: inline-block;
            }
            .get-credit-rating-button small {
                font-size: 0.9rem;
            }
        }
    </style>
    {if $user->order['status_1c'] == '3.Одобрено'}
        <style type="text/css">
            @media screen and (max-width: 768px) {
                .credit_rating_wrapper {
                    display: none;
                }
            }
        </style>
    {/if}
    <div class="credit_rating_wrapper {if $collapse_rating_banner}rating-collapsed{/if}">
        <div id="rating_speedometer">
            <img id="rating_meter" src="design/{$settings->theme|escape}/img/svg/rating.svg" width="auto" height="auto" />
            <img id="rating_arrow" src="design/{$settings->theme|escape}/img/svg/arrow_rating.svg" width="80" height="auto" />
        </div>
        <div style="display: flex; justify-content: center;">
            {if $score && $view_score}
                Ваш кредитный рейтинг составляет <span id="rating-ball" class="mr-x-10"></span>
            {elseif $view_score && !$score}
                <b>Ваш кредитный рейтинг рассчитывается</b>
            {/if}
        </div>
        <div
            {if $collapse_rating_banner}id="rating_collapsed_block" style="overflow: hidden; width: 500px; margin: auto;"{/if}
        >
            {if $collapse_rating_banner}
                <div
                    id="rating_collapsed_block_text"
                    style="width: 60%;
                            float: left;
                            font-size: 14px;
                            font-weight: bold;
                            line-height: 1.9;"
                >
                    Персональный кредитный рейтинг – это оценка финансовой надёжности заёмщика.
                    Узнайте, что сказывается на вероятности получения займа и повысьте возможные шансы одобрения следующей заявки.
                </div>
                <div
                    id="rating_collapsed_block_button"
                    style="width: 40%; float: right;"
                >
                    <div
                        class="button get-credit-rating-small-button"
                        style="text-align: center;
                                font-size: 14px;
                                font-weight: bold;
                                line-height: 18px;
                                padding: 10px 0;
                                border-radius: 10px;
                                background-color: #2fad25;
                                color: #000;"
                    >
                        ПОЛУЧИТЕ СВОЙ КРЕДИТНЫЙ РЕЙТИНГ
                        <br/><small>и персональные рекомендации</small>
                    </div>
                </div>
            {else}
                <div class="button medium get-credit-rating-button">
                    {if !$user_approved && $score && $view_score}
                        Закажите перерасчет своего кредитного рейтинга и попробуйте подать Заявку заново
                    {elseif !$user_approved}
                        Узнайте свой кредитный рейтинг и попробуйте подать Заявку заново
                    {elseif $score && $view_score}
                        Заказать перерасчет кредитного рейтинга
                    {else}
                        Нажми и узнай свой кредитный рейтинг
                    {/if}
                </div>
            {/if}
        </div>
        <div class="credit-rating-result" style="width: 500px;"></div>
    </div>
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    {if $user_lk_page}
        <script src="design/{$settings->theme|escape}/js/credit_rating.app.js?v=1.0050" type="text/javascript"></script>
    {/if}
    <script type="text/javascript">
       $('.get-credit-rating-button').on('click', function (){
            if (is_developer) {
                console.info('ym reachGoal new_credit_rating_get_rating');
            } else {
                ym(45594498,'reachGoal','new_credit_rating_get_rating')
            }

           {if $is_credit_rating_page}
                sendCustomMetric(4);
           {/if}
        });
        $('.get-credit-rating-small-button').on('click', function () {
            {if $is_credit_rating_page}
                sendCustomMetric(4);
            {/if}

            if (is_developer) {
                console.info('ym reachGoal new_credit_rating_get_rating');
            } else {
                ym(45594498,'reachGoal','new_credit_rating_get_rating')
            }
        });

        $(document).ready(function () {
            {if $score && $view_score}
                $('#rating_arrow').css('transition-duration', '3s');

                let speed = parseInt('{$score}'),
                    center = 375,
                    resultDeg = 0;

                let oneDeg = 90 / center; // находим сколько 1 градус от значения 375
                let activeDeg = speed - center; // найдем сколько значение от введенного
                resultDeg = Math.ceil(oneDeg * activeDeg);

                $('#rating_arrow').css('transform', 'rotate(' + resultDeg + 'deg)');

                {*setTimeout(function () {
                    let html = '<h2><div style="color: {$score_data.color};">{$score}</div><span>|</span><div>{$score_data.title}</div></h2>';
                    $(html).hide().appendTo($('.credit_rating_wrapper')).show('slow');
                }, 7000);*}

            $('#rating-ball').html('<div style="color: {$score_data.color};">{$score}</div>');
            {else}
                $('#rating_arrow').css('transform', 'rotate(75deg)');
                setTimeout(function () {
                    $('#rating_arrow').css('transform', 'rotate(-90deg)');
                }, 1000);
            {/if}
            window.showRatingBanner = function(elem) {
                sendMetric('reachGoal', 'new_cr_reject_link_click');
                document.querySelector('.credit_rating_wrapper').style.display = 'grid';
                return false;
            }
        });
    </script>
{/if}