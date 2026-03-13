<label class="big left align-center">
    <div class="checkbox check_address" style="border-width: 1px;">
        {if empty($rcl_loan)}
        <input class="js-need-verify js-bki-consent-checkbox" type="checkbox" value="0"/>
        <span></span>
        {/if}
    </div>
    <span>Я согласен на <a href="{$config->root_url}/user/docs?action=soglasie_na_bki"
                     target="_blank">
        направление запросов в Бюро кредитных историй
    </a></span>
</label>