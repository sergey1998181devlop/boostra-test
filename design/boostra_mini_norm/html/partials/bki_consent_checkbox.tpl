<div>
    <label class="spec_size">
        <div class="checkbox" style="border-width: 1px;width: 16px !important;height: 16px !important;">
            <input class="js-need-value js-bki-consent-checkbox" type="checkbox" {if $bki_consent}checked{/if} />
            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
        </div>
    </label>
    <p>
        Я согласен на <a href="{$config->root_url}/preview/agreement_disagreement_to_receive_ko" target="_blank">
            направление запросов в БКИ
        </a>
    </p>
</div>
