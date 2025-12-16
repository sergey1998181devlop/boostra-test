{literal}
    <style>
        #accept-docs-list {
            margin-top: 1em;
        }

        #accept-docs-list > div{
            display: flex;
        }

        #accept-docs-list #accept-docs-list_input {
            width: 25px;
            height: 25px;
            cursor: pointer;
            position: relative;
            -webkit-appearance: none;
            border: 2px solid #5a5a5a;
            border-radius: 4px;
            outline: none;
            padding-bottom: 0;
        }

        #accept-docs-list #accept-docs-list_input:checked {
            background-color: #0b0;
            border-color: #0b0;
        }

        #accept-docs-list #accept-docs-list_input:checked:before {
            content: "✓";
            position: absolute;
            color: white;
            font-size: 16px;
            left: 3px;
            top: -2px;
        }

        #accept-docs-list label[for='accept-docs-list_input'] {
            display: flex;
            text-align: left;
            gap: 10px;
        }

        #accept-docs-list_modal {
            max-height: 90vh;
            overflow-y: auto;
        }

        .accept-docs-list_close-modal-button {
            background: #0b0;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }

        #accept-docs-list_modal .modal-content {
            display: flex;
            flex-flow: column;
            gap: 20px;
        }

        #accept-docs-list_modal .conditions > div {
            display: flex;
            align-items: center;
        }

        #accept_credit #accept-docs-list_open-modal {
            color: initial;
            text-decoration: none;
        }

        @media screen and (max-width: 768px) {
            #accept-docs-list_modal {
                max-width: 90vw;
            }
        }
    </style>
{/literal}

{if $user_order['organization_id'] == $ORGANIZATION_FINLAB}
    {$accept_documents = [
    'agreed_1' => ['verify' => 1, 'filename' => '/files/docs/finlab/Obshchie-usloviya-OOO-MKK-FINLAB-ot-01.06.2024.docx', 'docname' => 'Общими условиями договора потребительского микрозайма', 'class' => ''],
    'agreed_2' => ['verify' => 1, 'filename' => '/files/docs/finlab/Pravila-predostavleniya-zajmov-01.06.2024-FINLAB.docx', 'docname' => 'Правилами предоставления займов ООО МКК «ФИНЛАБ»', 'class' => ''],
    'agreed_4' => ['verify' => 1, 'filename' => '/user/docs?action=pdn_excessed&organization_id=11', 'docname' => 'Уведомлением о повышенном риске невыполнения кредитных обязательств', 'link_class' => "micro-zaim-doc-js"],
    'agreed_5' => ['verify' => 1, 'filename' => '/user/docs?action=micro_zaim&organization_id=11', 'docname' => 'Заявлением о предоставлении микрозайма', 'link_class' => 'micro-zaim-doc-js'],
    'agreed_6' => ['verify' => 1, 'filename' => "/files/docs/finlab/Politika-konfidencial'nosti.docx", 'docname' => 'Политикой конфиденциальности ООО МКК «ФИНЛАБ»', 'class' => ''],
    'credit_doctor_checkbox' => [],
    'star_oracle' => [],
    'agreed_7' => ['verify' => 1, 'filename' => '/user/docs?action=soglasie_na_bki_finlab', 'docname' => 'на запрос кредитного отчета в бюро кредитных историй'],
    'agreed_8' => ['verify' => 1, 'filename' => '/files/docs/finlab/Politika-bezopasnosti-platezhej-Best2Pay.pdf', 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)', 'class' => ''],
    'agreed_9' => ['verify' => 0, 'docname' => 'подключением ПО «ВитаМед» стоимостью 600 рублей, предоставляемой в соответствии с <a href="user/docs?action=additional_service_vita-med" target="_blank">заявлением о предоставлении дополнительных услуг.</a>'],
    'agreed_10' =>['verify' => 0, 'docname' => 'на уступку права требования', 'class' => 'js-agree-claim-value']
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
    'agreed_8' => ['verify' => 1, 'filename' => 'files/docs/viploan/politika-bezopasnosti-platezhei-best2pay.pdf', 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)', 'class' => ''],
    'agreed_9' => ['verify' => 0, 'docname' => 'подключением ПО «ВитаМед» стоимостью 600 рублей, предоставляемой в соответствии с <a href="user/docs?action=additional_service_vita-med" target="_blank">заявлением о предоставлении дополнительных услуг.</a>'],
    'agreed_10' =>['verify' => 0, 'docname' => 'на уступку права требования', 'class' => 'js-agree-claim-value']
    ]}
{else}
    {$accept_documents = [
    'agreed_1' => ['verify' => 1, 'filename' => '/files/docs/obschie-usloviya.pdf', 'docname' => 'Общими условиями договора потребительского микрозайма'],
    'agreed_2' => ['verify' => 1, 'filename' => '/files/docs/pravila-predostavleniya.pdf', 'docname' => 'Правилами предоставления займов ООО МКК «Аквариус»'],
    'agreed_3' => ['verify' => 1, 'filename' => '/files/docs/informatsiyaobusloviyahpredostavleniyaispolzovaniyaivozvrata.pdf', 'docname' => 'Правилами обслуживания и пользования услугами ООО МКК «Аквариус»'],
    'agreed_4' => ['verify' => 1, 'filename' => '/user/docs?action=pdn_excessed&organization_id=6', 'docname' => 'Уведомлением о повышенном риске невыполнения кредитных обязательств', 'link_class' => "micro-zaim-doc-js"],
    'agreed_5' => ['verify' => 1, 'filename' => '/user/docs?action=micro_zaim', 'docname' => 'Заявлением о предоставлении микрозайма'],
    'agreed_6' => ['verify' => 1, 'filename' => '/files/docs/politikakonfidentsialnosti.pdf', 'docname' => 'Политикой конфиденциальности ООО МКК «Аквариус»'],
    'credit_doctor_checkbox' => [],
    'star_oracle' => [],
    'agreed_7' => ['verify' => 1, 'filename' => '/preview/agreement_disagreement_to_receive_ko', 'docname' => 'на запрос кредитного отчета в бюро кредитных историй'],
    'agreed_8' => ['verify' => 1, 'filename' => '/files/docs/Договор_об_условиях_предоставления_Акционерное_общество_«Сургутнефтегазбанк».pdf', 'docname' => 'Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)'],
    'service_recurent_check' => ['verify' => 0, 'filename' => '/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf', 'docname' => 'Соглашением о применении регулярных (рекуррентных) платежах', 'class' => 'js-service-recurent'],
    'agreed_9' => ['verify' => 0, 'docname' => 'подключением ПО «ВитаМед» стоимостью <span id="tv_med_amount">600</span> рублей, предоставляемой в соответствии с <a href="user/docs?action=additional_service_vita-med" target="_blank">заявлением о предоставлении дополнительных услуг.</a>'],
    'agreed_10' =>['verify' => 0, 'docname' => 'на уступку права требования', 'class' => 'js-agree-claim-value']
    ]}
{/if}
<div class="docs_wrapper">
    {if $isSafeFlow}
        <p class="toggle-conditions-accept toggle-conditions-accept">Я согласен со всеми условиями:
            <span class="arrow">
                        <img src="{$config->root_url}/design/boostra_mini_norm/img/icons/chevron-svgrepo-com.svg" alt="Arrow" />
                    </span>
        </p>
        {include 'accept_credit/docs_list.tpl'}
    {else}
        <div id="accept-docs-list">
            <div>
                <label for="accept-docs-list_input">
                    <input type="checkbox" name="accept_docs_list" id="accept-docs-list_input" />
                    <span style="flex: 1;">Подписывая договор я соглашаюсь и подписываю <a id="accept-docs-list_open-modal" href="javascript:void(0);">документы</a></span>
                </label>
            </div>
            <div id="accept-docs-list_modal" class="white-popup modal mfp-hide">
                <div class="modal-content">
                    <div style="align-self: flex-end; position: sticky; top: 0;">
                        <button type="button" class="accept-docs-list_close-modal-button" onclick="$.magnificPopup.close()">OK</button>
                    </div>
                    {include 'accept_credit/docs_list.tpl'}
                </div>
            </div>
        </div>
    {/if}
</div>

{capture_array key="footer_page_scripts"}
    <script type="text/javascript">
        document.getElementById('accept-docs-list_open-modal').addEventListener('click', function () {
            $.magnificPopup.open({
                items: {
                    src: '#accept-docs-list_modal'
                },
                type: 'inline',
                showCloseBtn: true,
                modal: true,
            });
        });

        document.getElementById('accept-docs-list_input').addEventListener('change', function () {
            if (this.checked) {
                const checkboxes = document.querySelectorAll('#accept-docs-list_modal input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
            }
        });
    </script>
{/capture_array}
