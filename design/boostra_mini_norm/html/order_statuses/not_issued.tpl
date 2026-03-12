{if $show_popup_to_repeat_issuance_order}
    <div class="status-box status-box--error">
        <div class="status-box__icon">
            <svg viewBox="0 0 24 24">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>
        </div>
        <div class="status-box__content">
            <h3 class="status-box__title">Ошибка перевода средств</h3>
            <p class="status-box__text">При попытке перевода денежных средств на предоставленный Вами счет произошла ошибка.</p>
            <p class="status-box__text">Пожалуйста, выберите другой банк и мы автоматически повторим попытку.</p>
            <p class="status-box__text">Убедитесь, что Ваши данные в банке актуальны и совпадают с данными на сайте.</p>
            <p class="status-box__text" style="margin-top: 12px;">
                <button class="button choose_bank" type="button" onclick="openSbpBanksModal()">
                    Выбрать банк для выплаты
                </button>
            </p>
        </div>
    </div>
{else}
    <div class="status-box status-box--error">
        <div class="status-box__icon">
            <svg viewBox="0 0 24 24">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>
        </div>
        <div class="status-box__content">
            <h3 class="status-box__title">Ошибка перевода средств</h3>
            <p class="status-box__text">При попытке перевода денежных средств на предоставленный Вами счет произошла ошибка.</p>
            <p class="status-box__text">Для получения займа обратитесь, пожалуйста, в поддержку.</p>
        </div>
    </div>
{/if}
