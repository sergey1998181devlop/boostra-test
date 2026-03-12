<div class="conditions">
    {foreach $accept_documents as $accept_document_key => $accept_document}
        {if $accept_document_key == 'agreed_9'}
            {if $notOverdueLoan || $isSafetyFlow || $applied_promocode->disable_additional_services}
                {continue}
            {/if}
        {/if}

        {if $accept_document_key == 'credit_doctor_checkbox'}
            {include file="credit_doctor/credit_doctor_checkbox.tpl" idkey=$user_order['id']}
            {continue}
        {/if}
        {if $accept_document_key == 'tv_medical'}
            {include file="tv_medical/tv_medical_checkbox.tpl" idkey=$user_order['id']}
            {continue}
        {/if}

        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="{if $accept_document['verify']}js-need-verify{/if} {$accept_document['class']}" type="checkbox" value="1" id="{$accept_document_key}"/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>{if $accept_document_key == 'agreed_10'}Настоящим выражаю свое согласие{else} Настоящим подтверждаю, что полностью ознакомлен и согласен с{/if}
                {if isset($accept_document['filename']) && $accept_document['filename']}
                    <a href="{$accept_document['filename']}" class="{$accept_document['link_class']}" target="_blank">
                        {$accept_document['docname']}
                    </a>
                {else}
                    {$accept_document['docname']}
                    {if $accept_document_key == 'agreed_9'}
                        <a type="button" class="pointer" id="btn-modal-telemed" data-modal="btn-modal-telemed">Подробнее</a>
                    {/if}
                {/if}
            </p>
        </div>
    {/foreach}

    <div>
        <label class="spec_size">
            <div class="checkbox"
                 style="border-width: 1px;width: 16px !important;height: 16px !important;">
                <input class="js-service-doctor js-need-verify" type="checkbox" value="1" id="service_doctor_check" name="service_doctor"/>
                <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
            </div>
        </label>
        <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
            <a class="contract_approve_file" href="{$config->root_url}/files/contracts/{$user_order['approved_file']}" target="_blank">Договором</a></p>
    </div>
    <div id="not_checked_info" style="display:none">
        <strong style="color:#f11">Вы должны согласиться с договором и нажать "Получить деньги"</strong>
    </div>
</div>
