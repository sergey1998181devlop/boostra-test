{* Cooling-off period timer - UI only - Show only for loans >= 50,000 RUB *}
{if $user->order['status'] == 17} 
    {assign var="confirm_timestamp" value=$user->order['confirm_date']|strtotime|default:$smarty.now} 
    {assign var="cooling_off_end" value=$confirm_timestamp + (4 * 60 * 60)}
    {assign var="current_time" value=$smarty.now}
    {if $current_time < $cooling_off_end}
    {capture name="page_styles"}
        <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/cooling-off.css" />
    {/capture}
    {capture name=page_scripts}
    <script src="design/{$settings->theme|escape}/js/cooling-off/cooling-off.js" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/cooling-off/modal.js" type="text/javascript"></script>
    {/capture}

<div class="cooling-off-container">
    <h3>Договор подписан! Ожидайте, мы переводим Вам заём на карту</h3>

    <p class="greeting">Уважаемый заемщик!</p>

    <p class="legal-text">
        В соответствии с требованиями статьи 9.3 Федерального закона от 21.12.2013 г. № 353-ФЗ "О потребительском
        кредите (займе)" - передача денежных средств по договору займа будет осуществлена не ранее чем через 4 (четыре)
        часа после подписания одобренной заявки.
    </p>

    <p class="legal-text">
        Уведомляем, что в соответствии со статьей 9.4 Федерального закона от 21.12.2013 г. № 353-ФЗ "О потребительском
        кредите (займе)" Вы вправе отказаться от получения займа до истечения вышеуказанного срока.
    </p>

    <p class="legal-text">
        По истечении 4-х (четырех) часов сумма займа будет автоматически перечислена выбранным Вами способом получения
        денег. Ожидайте!
    </p>


    <div class="countdown-timer" id="countdown-timer" data-end-timestamp="{$cooling_off_end}" data-confirm-timestamp="{$confirm_timestamp}">
        <span id="countdown-display">00 : 00 : 00</span>
    </div>

    

    <div class="timer-buttons">
        <button class="cancel-btn" id="cooling-off-cancel-btn">
            Отказаться
        </button>
        {* <button class="promo-btn">
            У меня есть промокод
        </button> *}
    </div>

    
</div>
{/if}
{/if}