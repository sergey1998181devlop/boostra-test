{function name=print_document}
    <label class="spec_size">
        <div class="checkbox" style="border-width:1px;width:16px!important;height:16px!important;">
            <input class="{if $accept_document.verify}js-need-verify{/if}{$accept_document.class}" type="checkbox"
                value="0" id="{$accept_document_key}" />
            <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
        </div>
    </label>
    <p>
        {if $accept_document_key == 'agreed_10'}Настоящим выражаю свое согласие{else}{if $isSafetyFlow}Настоящим подтверждаю, что полностью ознакомлен и согласен с{else} Я согласен с{/if}{/if}
        {if $accept_document.filename}
            <a href="{$accept_document.filename}" class="{$accept_document.link_class}"
                target="_blank">{$accept_document.docname}</a>
        {else}
            {$accept_document.docname}
            {if $accept_document_key == 'agreed_9'}
                <a class="pointer" id="btn-modal-telemed" data-modal="btn-modal-telemed">Подробнее</a>
            {/if}
        {/if}
    </p>
{/function}

{if $user_order['organization_id'] == $ORGANIZATION_FINLAB}
    {$accept_documents = [
        'agreed_1' => ['verify' => 1, 'filename' => '/files/docs/finlab/Obshchie-usloviya-OOO-MKK-FINLAB-ot-01.06.2024.docx', 'docname' => 'Общими условиями договора потребительского микрозайма', 'class' => ''],
        'agreed_2' => ['verify' => 1, 'filename' => '/files/docs/finlab/Pravila-predostavleniya-zajmov-01.06.2024-FINLAB.docx', 'docname' => 'Правилами предоставления займов ООО МКК «ФИНЛАБ»', 'class' => ''],
        'agreed_4' => ['verify' => 1, 'filename' => '/user/docs?action=pdn_excessed&organization_id=11', 'docname' => 'Уведомлением о повышенном риске невыполнения кредитных обязательств', 'link_class' => "micro-zaim-doc-js"],
        'agreed_5' => ['verify' => 1, 'filename' => '/user/docs?action=micro_zaim&organization_id=11', 'docname' => 'Заявлением о предоставлении микрозайма', 'link_class' => 'micro-zaim-doc-js'],
        'agreed_6' => ['verify' => 1, 'filename' => "/files/docs/finlab/Politika-konfidencial'nosti.docx", 'docname' => 'Политикой конфиденциальности ООО МКК «ФИНЛАБ»', 'class' => ''],
        'credit_doctor_checkbox' => [],
        'star_oracle' => [],
        'service_recurent_check' => ['verify' => 0, 'filename' => '/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf', 'docname' => 'Соглашением о применении регулярных (рекуррентных) платежах', 'class' => 'js-service-recurent'],
        'agreed_7' => ['verify' => 1, 'filename' => '/user/docs?action=soglasie_na_bki_finlab', 'docname' => 'на запрос кредитного отчета в бюро кредитных историй'],
        'agreed_offer_docs' => ['verify' => 1, 'docname' => "<a href=\"/user/docs?action=offer_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об акцепте оферты</a>, <a href=\"/user/docs?action=asp_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об АСП</a>, <a href=\"/user/docs?action=arbitration_agreement&order_id={$user_order['id']}\" target=\"_blank\">Арб.соглашение</a>, <a href=\"/user/docs?action=offer_arbitration_cessionary&order_id={$user_order['id']}\" target=\"_blank\">Оферта</a>"],
        'agreed_8' => ['verify' => 1, 'filename' => '/files/docs/finlab/Politika-bezopasnosti-platezhej-Best2Pay.pdf', 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)', 'class' => ''],
        'agreed_9' => ['verify' => 0, 'docname' => 'подключением ПО «ВитаМед» стоимостью 600 рублей, предоставляемой в соответствии с <a href="user/docs?action=additional_service_vita-med" target="_blank">заявлением о предоставлении дополнительных услуг.</a>'],
        'agreed_10' =>['verify' => 0, 'docname' => 'на уступку права требования', 'class' => 'js-agree-claim-value', 'show_only_safety_flow' => true]
        ]}
{elseif $user_order['organization_id'] == $ORGANIZATION_VIPZAIM}
    {$accept_documents = [
        'agreed_1' => ['verify' => 1, 'filename' => '/files/docs/viploan/obshchie-usloviya-ooo-mkk-vipzai-m-ot-01-06-2024.docx', 'docname' => 'Общими условиями договора потребительского микрозайма', 'class' => ''],
        'agreed_2' => ['verify' => 1, 'filename' => '/files/docs/viploan/pravila-predostavleniya-zai-mov-01-06-2024-vipzai-m.docx', 'docname' => 'Правилами предоставления займов ООО МКК «ВИПЗАЙМ»', 'class' => ''],
        'agreed_4' => ['verify' => 1, 'filename' => '/user/docs?action=pdn_excessed&organization_id=12', 'docname' => 'Уведомлением о повышенном риске невыполнения кредитных обязательств', 'link_class' => "micro-zaim-doc-js"],
        'agreed_5' => ['verify' => 1, 'filename' => '/files/docs/viploan/zayavlenie-o-predostavlenii-mikrozai-ma-vipzai-m.docx', 'docname' => 'Заявлением о предоставлении микрозайма', 'class' => ''],
        'agreed_6' => ['verify' => 1, 'filename' => "/files/docs/viploan/politika-konfidencialnosti.docx", 'docname' => 'Политикой конфиденциальности ООО МКК «ВИПЗАЙМ»', 'class' => ''],
        'credit_doctor_checkbox' => [],
        'star_oracle' => [],
        'service_recurent_check' => ['verify' => 0, 'filename' => '/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf', 'docname' => 'Соглашением о применении регулярных (рекуррентных) платежах', 'class' => 'js-service-recurent'],
        'agreed_7' => ['verify' => 1, 'filename' => '/user/docs?action=soglasie_na_bki', 'docname' => 'на запрос кредитного отчета в бюро кредитных историй'],
        'agreed_offer_docs' => ['verify' => 1, 'docname' => "<a href=\"/user/docs?action=offer_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об акцепте оферты</a>, <a href=\"/user/docs?action=asp_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об АСП</a>, <a href=\"/user/docs?action=arbitration_agreement&order_id={$user_order['id']}\" target=\"_blank\">Арб.соглашение</a>, <a href=\"/user/docs?action=offer_arbitration_cessionary&order_id={$user_order['id']}\" target=\"_blank\">Оферта</a>"],
        'agreed_8' => ['verify' => 1, 'filename' => 'files/docs/viploan/politika-bezopasnosti-platezhei-best2pay.pdf', 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)', 'class' => ''],
        'agreed_9' => ['verify' => 0, 'docname' => 'подключением ПО «ВитаМед» стоимостью 600 рублей, предоставляемой в соответствии с <a href="user/docs?action=additional_service_vita-med" target="_blank">заявлением о предоставлении дополнительных услуг.</a>'],
        'agreed_10' =>['verify' => 0, 'docname' => 'на уступку права требования', 'class' => 'js-agree-claim-value', 'show_only_safety_flow' => true]
        ]}
{elseif ($user_order['organization_id'] == $ORGANIZATION_RZS || $docs_default)}
    {$accept_documents = [
        'agreed_1' => ['verify' => 1, 'filename' => "/files/docs/rzs/accept_documents/obschie-usloviya.pdf", 'docname' => 'Общие условия договора займа'],
        'agreed_2' => ['verify' => 1, 'filename' => "/files/docs/rzs/accept_documents/pravila-predostavleniya.pdf", 'docname' => 'Правила предоставления займов'],
        'agreed_3' => ['verify' => 1, 'filename' => "/files/docs/rzs/register_user_docs/polozhenie-asp.pdf", 'docname' => 'Положение АСП'],
        'soglasie_recurrent' => ['verify' => 0, 'filename' => "/user/docs?action=soglasie_recurrent", 'docname' => 'Соглашение о регулярных (рекуррентных) платежах'],
        'agreed_offer_docs' => ['verify' => 1, 'docname' => "<a href=\"/user/docs?action=offer_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об акцепте оферты</a>, <a href=\"/user/docs?action=asp_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об АСП</a>, <a href=\"/user/docs?action=arbitration_agreement&order_id={$user_order['id']}\" target=\"_blank\">Арб.соглашение</a>, <a href=\"/user/docs?action=offer_arbitration_cessionary&order_id={$user_order['id']}\" target=\"_blank\">Оферта</a>"],
        'agreed_4' => ['verify' => 1, 'filename' => "/files/docs/rzs/get_loan_user_docs/Договор_об_условиях_предоставления_Акционерное_общество_«Сургутнефтегазбанк».pdf", 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)'],
        'agreed_5' => ['verify' => 1, 'filename' => "user/docs?action=micro_zaim&organization_id={$ORGANIZATION_RZS}", 'docname' => 'Заявлением о предоставлении микрозайма'],
        'credit_doctor_checkbox' => [],
        'star_oracle' => []
    ]}
{elseif $user_order['organization_id'] == $ORGANIZATION_LORD}
    {$accept_documents = [
        'agreed_1' => ['verify' => 1, 'filename' => "/files/docs/lord/accept_documents/obschie-usloviya.pdf", 'docname' => 'Общие условия договора займаа'],
        'agreed_2' => ['verify' => 1, 'filename' => "/files/docs/lord/accept_documents/pravila-predostavleniya.pdf", 'docname' => 'Правила предоставления займов'],
        'agreed_3' => ['verify' => 1, 'filename' => "/files/docs/lord/register_user_docs/polozhenie-asp.pdf", 'docname' => 'Положение АСП'],
        'soglasie_recurrent' => ['verify' => 0, 'filename' => "/user/docs?action=soglasie_recurrent", 'docname' => 'Соглашение о регулярных (рекуррентных) платежах'],
        'agreed_offer_docs' => ['verify' => 1, 'docname' => "<a href=\"/user/docs?action=offer_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об акцепте оферты</a>, <a href=\"/user/docs?action=asp_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об АСП</a>, <a href=\"/user/docs?action=arbitration_agreement&order_id={$user_order['id']}\" target=\"_blank\">Арб.соглашение</a>, <a href=\"/user/docs?action=offer_arbitration_cessionary&order_id={$user_order['id']}\" target=\"_blank\">Оферта</a>"],
        'agreed_4' => ['verify' => 1, 'filename' => '/files/docs/lord/Договор_об_условиях_предоставления_Акционерное_общество_«Сургутнефтегазбанк».pdf', 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)'],
        'agreed_5' => ['verify' => 1, 'filename' => "user/docs?action=micro_zaim&organization_id={$ORGANIZATION_LORD}", 'docname' => 'Заявлением о предоставлении микрозайма'],
        'credit_doctor_checkbox' => [],
        'star_oracle' => []
    ]}
{elseif ($user_order['organization_id'] == $ORGANIZATION_MOREDENEG)}
    {$accept_documents = [
        'agreed_1' => ['verify' => 1, 'filename' => "/files/docs/moredeneg/accept_documents/obschie-usloviya.pdf", 'docname' => 'Общие условия договора займа'],
        'agreed_2' => ['verify' => 1, 'filename' => "/files/docs/moredeneg/accept_documents/pravila-predostavleniya.pdf", 'docname' => 'Правила предоставления займов'],
        'agreed_3' => ['verify' => 1, 'filename' => "/files/docs/moredeneg/register_user_docs/polozhenie-asp.pdf", 'docname' => 'Положение АСП'],
        'soglasie_recurrent' => ['verify' => 0, 'filename' => "/preview/soglasie_recurrent?{http_build_query($mfo_params)}", 'docname' => 'Соглашение о регулярных (рекуррентных) платежах'],
        'agreed_offer_docs' => ['verify' => 1, 'docname' => "<a href=\"/user/docs?action=offer_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об акцепте оферты</a>, <a href=\"/user/docs?action=asp_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об АСП</a>, <a href=\"/user/docs?action=arbitration_agreement&order_id={$user_order['id']}\" target=\"_blank\">Арб.соглашение</a>, <a href=\"/user/docs?action=offer_arbitration_cessionary&order_id={$user_order['id']}\" target=\"_blank\">Оферта</a>"],
        'agreed_4' => ['verify' => 1, 'filename' => "/files/docs/moredeneg/get_loan_user_docs/Politika-bezopasnosti-platezhej-Best2Pay.pdf", 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)'],
        'agreed_5' => ['verify' => 1, 'filename' => "user/docs?action=micro_zaim&organization_id={$ORGANIZATION_MOREDENEG}", 'docname' => 'Заявлением о предоставлении микрозайма'],
        'credit_doctor_checkbox' => [],
        'star_oracle' => []
    ]}
{elseif $user_order['organization_id'] == $ORGANIZATION_FRIDA}
    {$accept_documents = [
        'agreed_1' => ['verify' => 1, 'filename' => "/files/docs/frida/accept_documents/obschie-usloviya.pdf", 'docname' => 'Общие условия договора займаа'],
        'agreed_2' => ['verify' => 1, 'filename' => "/files/docs/frida/accept_documents/pravila-predostavleniya.pdf", 'docname' => 'Правила предоставления займов'],
        'agreed_3' => ['verify' => 1, 'filename' => "/files/docs/frida/register_user_docs/polozhenie-asp.pdf", 'docname' => 'Положение АСП'],
        'soglasie_recurrent' => ['verify' => 0, 'filename' => "/user/docs?action=soglasie_recurrent", 'docname' => 'Соглашение о регулярных (рекуррентных) платежах'],
        'agreed_offer_docs' => ['verify' => 1, 'docname' => "<a href=\"/user/docs?action=offer_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об акцепте оферты</a>, <a href=\"/user/docs?action=asp_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об АСП</a>, <a href=\"/user/docs?action=arbitration_agreement&order_id={$user_order['id']}\" target=\"_blank\">Арб.соглашение</a>, <a href=\"/user/docs?action=offer_arbitration_cessionary&order_id={$user_order['id']}\" target=\"_blank\">Оферта</a>"],
        'agreed_4' => ['verify' => 1, 'filename' => '/files/docs/frida/get_loan_user_docs/Oferta-ob-usloviyah-ispolzovanie-servisa-processingovogo-centra.pdf', 'docname' => 'Оферта об использовании процессингового центра BEST2PAY'],
        'agreed_5' => ['verify' => 1, 'filename' => "user/docs?action=micro_zaim&organization_id={$ORGANIZATION_FRIDA}", 'docname' => 'Заявлением о предоставлении микрозайма'],
        'credit_doctor_checkbox' => [],
        'star_oracle' => []
    ]}
{else}
    {$accept_documents = [
        'agreed_1' => ['verify' => 1, 'filename' => '/files/docs/obschie-usloviya.pdf', 'docname' => 'Общими условиями договора потребительского микрозайма'],
        'agreed_2' => ['verify' => 1, 'filename' => '/files/docs/pravila-predostavleniya.pdf', 'docname' => 'Правилами предоставления займов ООО МКК «Аквариус»'],
        'agreed_3' => ['verify' => 1, 'filename' => '/files/docs/informatsiyaobusloviyahpredostavleniyaispolzovaniyaivozvrata.pdf', 'docname' => 'Правилами обслуживания и пользования услугами ООО МКК «Аквариус»'],
        'agreed_4' => ['verify' => 1, 'filename' => '/user/docs?action=pdn_excessed&organization_id=6', 'docname' => 'Уведомлением о повышенном риске невыполнения кредитных обязательств', 'link_class' => "micro-zaim-doc-js"],
        'agreed_5' => ['verify' => 1, 'filename' => '/user/docs?action=micro_zaim', 'docname' => 'Заявлением о предоставлении микрозайма','link_class' => "micro-zaim-doc-js"],
        'agreed_6' => ['verify' => 1, 'filename' => '/files/docs/politikakonfidentsialnosti.pdf', 'docname' => 'Политикой конфиденциальности ООО МКК «Аквариус»'],
        'credit_doctor_checkbox' => [],
        'star_oracle' => [],
        'service_recurent_check' => ['verify' => 0, 'filename' => '/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf', 'docname' => 'Соглашением о применении регулярных (рекуррентных) платежах', 'class' => 'js-service-recurent'],
        'agreed_7' => ['verify' => 1, 'filename' => '/preview/agreement_disagreement_to_receive_ko', 'docname' => 'на запрос кредитного отчета в бюро кредитных историй'],
        'agreed_offer_docs' => ['verify' => 1, 'docname' => "<a href=\"/user/docs?action=offer_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об акцепте оферты</a>, <a href=\"/user/docs?action=asp_agreement&order_id={$user_order['id']}\" target=\"_blank\">Соглашение об АСП</a>, <a href=\"/user/docs?action=arbitration_agreement&order_id={$user_order['id']}\" target=\"_blank\">Арб.соглашение</a>, <a href=\"/user/docs?action=offer_arbitration_cessionary&order_id={$user_order['id']}\" target=\"_blank\">Оферта</a>"],
        'agreed_8' => ['verify' => 1, 'filename' => '/files/docs/Договор_об_условиях_предоставления_Акционерное_общество_«Сургутнефтегазбанк».pdf', 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)'],
        'agreed_9' => ['verify' => 0, 'docname' => 'подключением ПО «ВитаМед» стоимостью <span id="tv_med_amount">600</span> рублей, предоставляемой в соответствии с <a href="user/docs?action=additional_service_vita-med" target="_blank">заявлением о предоставлении дополнительных услуг.</a>', 'class' => 'js-agree-claim-value'],
        'agreed_10' =>['verify' => 1, 'docname' => 'на уступку права требования', 'class' => 'js-agree-claim-value', 'show_only_safety_flow' => false]
        ]}
{/if}

{if !$isSafetyFlow && $isAllowedTestLeadgid}
    {assign var="accept_documents" value=$accept_documents|@array_diff_key:['agreed_8' => '', 'service_recurent_check' => '', 'agreed_4' => '', 'soglasie_recurrent' => '']}
{/if}

<div class="docs_wrapper">
    <div class="conditions">
        {foreach $accept_documents as $accept_document_key => $accept_document}
            {if $accept_document_key == 'agreed_10'}
                {if $isSafetyFlow}
                    {* безопасный флоу: видно и не отмечено *}
                    <div id="agreed_10_container">
                        <label class="spec_size">
                            <div class="checkbox" style="border-width:1px;width:16px!important;height:16px!important;">
                                <input class="js-need-verify js-agree-claim-value" type="checkbox" id="agree_claim_value"
                                    name="agree_claim_value" value="0" />
                                <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
                            </div>
                        </label>
                        <p>Настоящим выражаю свое согласие на уступку права требования</p>
                    </div>
                {else}
                    {* опасный флоу: скрыто и отмечено *}
                    <div id="agreed_10_container" style="display:none;">
                        <label class="spec_size">
                            <div class="checkbox" style="border-width:1px;width:16px!important;height:16px!important;">
                                <input class="js-need-verify js-agree-claim-value" type="checkbox" id="agree_claim_value"
                                    name="agree_claim_value" value="1" checked="checked" />
                                <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
                            </div>
                        </label>
                        <p>Настоящим выражаю свое согласие на уступку права требования</p>
                    </div>
                {/if}

                {continue}
            {/if}

            {if $accept_document_key == 'soglasie_recurrent'}
                <div>
                    {print_document accept_document_key=$accept_document_key accept_document=$accept_document}
                </div>
                {continue}
            {/if}

            {if $accept_document_key == 'agreed_9'}
                {if $notOverdueLoan || $isSafetyFlow || $applied_promocode->disable_additional_services}
                    {continue}
                {/if}
            {/if}
            {if $accept_document_key == 'credit_doctor_checkbox'}
                {if $showExtraService['financial_doctor']['show']}
                    {include file="credit_doctor/credit_doctor_checkbox.tpl" idkey=$user_order['id']}
                {/if}
                {continue}
            {/if}
            {if $accept_document_key == 'star_oracle'}
                {if $showExtraService['star_oracle']['show']}
                    {include file="star_oracle/star_oracle_checkbox.tpl" idkey=$user_order['id']}
                {/if}
                {continue}
            {/if}

            <div>
                {if $accept_document_key == 'agreed_1' && ! $isSafetyFlow}
                    {print_document accept_document_key='agreed_5' accept_document=$accept_documents['agreed_5']}
                </div>
                <div>
                {/if}

                {if $accept_document_key != 'agreed_5' && ($accept_document_key == 'agreed_offer_docs' || ! $isSafetyFlow)}
                    {print_document accept_document_key=$accept_document_key accept_document=$accept_document}
                {/if}
            </div>

            {if $accept_document_key == 'agreed_4' && ! $isSafetyFlow}
                <div>
                    <label class="spec_size">
                        <div class="checkbox" style="border-width: 1px;width: 16px !important;height: 16px !important;">
                            <input class="js-service-doctor js-need-verify" type="checkbox" value="1" id="service_doctor_check"
                                name="service_doctor" />
                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                        </div>
                    </label>
                    <p>Я согласен с
                        {if $accept_contract_url}
                            <a class="accept_confirm_href" href="{$accept_contract_url}" target="_blank">Индивидуальными условиями
                                договором займа</a>
                        {else}
                            <a class="contract_approve_file"
                                href="{$config->root_url}/files/contracts/{$user_order['approved_file']}"
                                target="_blank">Индивидуальными условиями договором займа</a>
                        {/if}
                    </p>
                </div>
            {/if}
        {/foreach}

        {if $isSafetyFlow}
            <div>
                <label class="spec_size">
                    <div class="checkbox" style="border-width: 1px;width: 16px !important;height: 16px !important;">
                        <input class="js-service-doctor js-need-verify" type="checkbox" value="1" id="service_doctor_check"
                            name="service_doctor" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                <p>Я согласен с
                    {if $accept_contract_url}
                        <a class="accept_confirm_href" href="{$accept_contract_url}" target="_blank">Индивидуальными условиями
                            договором займа</a>
                    {else}
                        <a class="contract_approve_file"
                            href="{$config->root_url}/files/contracts/{$user_order['approved_file']}"
                            target="_blank">Индивидуальными условиями договором займа</a>
                    {/if}
                </p>
            </div>
        {/if}

        <div id="not_checked_info" style="display:none">
            <strong style="color:#f11">Вы должны согласиться с договором и нажать "Получить деньги"</strong>
        </div>
    </div>
</div>
