{*Калькулятор для главной страницы*}

<script src="/design/orange_theme/html/calculator/calculator.js?v=1.001"></script>
<link rel="stylesheet" href="/design/orange_theme/html/calculator/calculator.css?v=1.102" />

<div class="hero__calculator" x-data="calculator({ldelim}isOrganic: {if $is_organic}true{else}false{/if}, isDeveloper: {if $is_developer}true{else}false{/if}, useSamePage: {if $smarty.get.utm_source == 'bankiru' || $same_page}true{else}false{/if}{rdelim})">
    <div class="hero__calculator__slides is_noindex" x-show="isOrganic" x-cloak>
        <div class="hero__calculator__slide"
             :class="{ldelim} 'active': activeSlide === 0 {rdelim}"
             @click="setSlide(0)">
            <p>Займы для физ. лиц</p>
        </div>
        <div class="hero__calculator__slide"
             :class="{ldelim} 'active': activeSlide === 1 {rdelim}"
             @click="setSlide(1)">
            <p>Займы для ИП</p>
        </div>
    </div>
    <div class="calculator" :data-percent="percent">
        <p class="calculator__title" x-html="title">Первый заём <b><a href="/files/docs/polozhenie0.pdf" target="_blank">бесплатно*</a></b></p>

        <div class="calculator__text zero-percent-notice" x-show="!isOrganic">
            <div>
                <svg width="14" height="17" viewBox="0 0 14 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.609 1.411L7 0L13.391 1.411C13.5637 1.44914 13.7182 1.54466 13.8289 1.68177C13.9396 1.81888 14 1.9894 14 2.16518V9.88241C13.9999 10.6457 13.8102 11.3971 13.4477 12.07C13.0852 12.7429 12.561 13.3165 11.9218 13.7399L7 17L2.07822 13.7399C1.4391 13.3166 0.915025 12.7431 0.552493 12.0703C0.189961 11.3976 0.000183833 10.6463 0 9.88318V2.16518C3.03627e-05 1.9894 0.0603851 1.81888 0.171103 1.68177C0.281821 1.54466 0.436284 1.44914 0.609 1.411ZM1.55556 2.78491V9.88241C1.55556 10.3912 1.682 10.8922 1.92365 11.3408C2.16529 11.7894 2.51467 12.1718 2.94078 12.454L7 15.1431L11.0592 12.454C11.4852 12.1719 11.8345 11.7896 12.0762 11.3411C12.3178 10.8926 12.4443 10.3919 12.4444 9.88318V2.78491L7 1.58409L1.55556 2.78491ZM7.77778 6.95455H10.1111L6.22222 12.3636V8.5H3.88889L7.77778 3.09091V6.95455Z" fill="#6F7985"/>
                </svg>
                <span>Сегодня одобрено более 99% заявок</span>
            </div>
        </div>
        <p class="calculator__text" x-show="infoMessage">
            <span class="calculator__text__info" x-text="infoMessage">&nbsp;</span>
        </p>
        <div class="calculator__slider">
            <div class="calculator__slider-top">
                <span>Выберите сумму</span>
                <b x-text="formatNumber(amount) + '\u00A0₽'">30 000 ₽</b>
            </div>
            <div class="range">
                <input type="range"
                       id="hero-range"
                       x-model="amount"
                       :min="sumConfig.min"
                       :max="sumConfig.max"
                       :step="sumConfig.step"
                       @change="onSumChange"
                       :style="sliderStyle(amount, sumConfig.min, sumConfig.max)"/>
            </div>
            <div class="calculator__slider-bottom" x-html="sumLabels"><span>1 000</span><span>50 000</span></div>
        </div>

        {* Ползунок срока — для органики при sum <= 30000 или для ИП *}
        <div class="calculator__slider is_noindex" x-show="showPeriodSlider" x-cloak>
            <div class="calculator__slider-top">
                <span>Выберите срок</span>
                <b>
                    <b x-text="period"></b>
                    <span x-text="periodUnit"></span>
                </b>
            </div>
            <div class="range">
                <input type="range"
                       id="hero-range-long"
                       x-model="period"
                       :min="periodConfig.min"
                       :max="periodConfig.max"
                       :step="periodConfig.step"
                       @change="onPeriodChange"
                       :style="sliderStyle(period, periodConfig.min, periodConfig.max)"/>
            </div>
            <div class="calculator__slider-bottom" x-html="periodLabels"></div>
        </div>

        {* Блок статистики "Вы вернете / Ставка" при sum <= 30000 *}
        <div class="calculator__stat" x-show="showStatBlock">
            <div class="calculator__stat-section">
                <span>Вы вернете</span>
                <b x-text="formatNumber(total) + '\u00A0₽'">30 000 ₽</b>
            </div>
            <div class="calculator__stat-section">
                <span>Ставка</span>
                <b x-text="displayPercent + ' %'">0 %</b>
            </div>
        </div>

        {* Блок "Готовый график" при sum > 30000 *}
        <div class="calculator__ready-plan" x-show="showReadyPlan" x-cloak>
            <div class="calculator__ready-plan-section">
                <span>Платеж раз в 2 недели</span>
                <b x-text="formatNumber(paymentAmount) + '\u00A0₽'"></b>
            </div>
            <div class="calculator__ready-plan-section">
                <span>Последний платеж</span>
                <b x-text="lastPaymentDate"></b>
            </div>
        </div>

        <div class="calculator__footer">
            {if $t_bank_button_registration_access || $esia_button_registration_access}
                <div class="auth__buttons is_noindex" x-show="!isIP">
                    {if $t_bank_button_registration_access}
                        <a onclick="sendMetric('reachGoal', 'tid_main')" class="tid_button" href="{$t_id_auth_url}">
                            Быстрее через
                            <img src="/design/boostra_mini_norm/assets/image/tinkoff-id-small.png" alt="" />
                            T-ID
                        </a>
                    {/if}
                    {if $esia_button_registration_access}
                        <a onclick="sendMetric('reachGoal', 'gu_main')" class="esia_button" href="{$esia_redirect_url}">
                            Быстрее через
                            <img class="" height="24" src="/design/boostra_mini_norm/assets/image/esia_logo.png" alt="" />
                            Госуслуги
                        </a>
                    {/if}
                </div>
                <div class="divider" x-show="activeSlide === 0 && !showReadyPlan">
                    <span>ИЛИ</span>
                </div>
            {/if}
            <button id="hero-btn" class="button button--primary button--big calc_button" @click="submitOrder">
                <span x-show="!showReadyPlan && showWithoutCoeff">Получить бесплатно</span>
                <span x-show="showReadyPlan || !showWithoutCoeff">Получить деньги</span>
            </button>
            <div class="calculator__text zero-percent-notice" x-show="activeSlide === 0 && !showReadyPlan" style="margin: 15px 0px;">
                <div style="justify-content: center;">
                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.50016 17.4167C5.12779 17.4167 1.5835 13.8724 1.5835 9.50004C1.5835 5.12767 5.12779 1.58337 9.50016 1.58337C13.8725 1.58337 17.4168 5.12767 17.4168 9.50004C17.4168 13.8724 13.8725 17.4167 9.50016 17.4167ZM9.50016 15.8334C11.1799 15.8334 12.7908 15.1661 13.9785 13.9784C15.1662 12.7907 15.8335 11.1797 15.8335 9.50004C15.8335 7.82034 15.1662 6.20943 13.9785 5.0217C12.7908 3.83397 11.1799 3.16671 9.50016 3.16671C7.82046 3.16671 6.20955 3.83397 5.02182 5.0217C3.83409 6.20943 3.16683 7.82034 3.16683 9.50004C3.16683 11.1797 3.83409 12.7907 5.02182 13.9784C6.20955 15.1661 7.82046 15.8334 9.50016 15.8334ZM8.7085 5.54171H10.2918V7.12504H8.7085V5.54171ZM8.7085 8.70837H10.2918V13.4584H8.7085V8.70837Z" fill="#6F7985"/>
                    </svg>
                    <a href="/files/docs/polozhenie0.pdf" target="_blank">
                        <span>Правила акции «Заем под 0%»</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <button id="floating-hero-btn" :class="{ 'floating-visible': showFloatingBtn }" @click="submitOrder" class="button button--primary button--big">
        <span x-show="!showReadyPlan && showWithoutCoeff">Получить бесплатно</span>
        <span x-show="showReadyPlan || !showWithoutCoeff">Получить деньги</span>
    </button>
</div>
