{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user/upload" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}

{literal}


{/literal}

<link href="design/{$settings->theme|escape}/css/user_docs__extra_services.css" rel="stylesheet" type="text/css" >

<section id="private">
	<div>
		<div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">

            {include file='user_nav.tpl' current='docs'}

			<div class="content">
				<div class="panel">
{*                    {if $userHasDocuments}*}
{*                        <button class="download" style="float: left; margin-left: 60px; cursor: pointer;">*}
{*                            Скачать документы*}
{*                        </button>*}
{*                    {/if}*}
{*                    <div id="loading" style="display:none;">Ожидайте, архив формируется...</div>*}
                    <p>{$credit_rating_paid_message}</p>
                    {if $page_error || in_array($user->id, [1530250,1248134])}
                        <div>
                            <h2>Раздел сейчас не доступен!</h2>
                            <br />
                            <h4>Попробуйте зайти на эту страницу позже.</h4>
                        </div>
                    {else}
                        {* Активные займы *}
                        {foreach $order_docs as $loan => $data}
                            {if $data['hidden'] || $data['is_closed']}{continue}{/if}
                            <div class="loan_docs" {if !$current_loan || $current_loan != $loan}data-hidden="1"{/if}>
                                <h2 class="js-toggle-loan-docs"><ins>{$loan}{if $data['date']} от {$data['date']}{/if}</ins> <span>Нажмите, чтобы раскрыть</span></h2>
                                <div class="loan_docs_list">
                                    <ul class="docs_list">
                                        {foreach $data['crm'] as $doc}
                                            {if $doc && $doc->name}
                                                <li>
                                                    {if !$doc->replaced}
                                                        <a href="{$config->root_url}/document/{$doc->user_id}/{$doc->id}.pdf" target="_blank">
                                                            {$doc->name|escape}
                                                        </a>
                                                    {else}
                                                        <a href="/files/doc/doc_id_{$doc->id}.pdf"
                                                           target="_blank">{$doc->name|escape}</a>
                                                    {/if}
                                                </li>
                                            {/if}
                                        {/foreach}
                                        {foreach $data['1c'] as $doc}
                                            {if $doc->uid != '19e7e23e-4ea3-426f-8f36-86deff750c38'}
                                                <li>
                                                    <a href="{$config->root_url}/user/docs/{$doc->uid}" target="_blank">
                                                        {$doc->name|escape}
                                                    </a>
                                                </li>
                                            {/if}
                                        {/foreach}
{*                                        {foreach $data['asp'] as $doc}*}
{*                                            <li>*}
{*                                                <a href="files/asp/{$doc->file_name}"*}
{*                                                   target="_blank">*}
{*                                                    Согласие субъекта на иные способы и частоту взаимодействия от {$doc->date_added|date} по займу №{$doc->zaim_number}*}
{*                                                </a>*}
{*                                            </li>*}
{*                                        {/foreach}*}
                                        {*{foreach $data['balance'] as $doc}
                                            <li>
                                                <a href="user/details/{$doc['НомерЗайма']}" target="_blank">
                                                    Расшифровка по займу {$doc['НомерЗайма']}
                                                </a>
                                            </li>
                                        {/foreach}*}
                                        {foreach $data['uploaded'] as $doc}
                                            <li>
                                                <a href="files/uploaded_files/{$doc->name}"
                                                   target="_blank"
                                                   class="uploaded-doc-link"
                                                   data-original-name="{$doc->name|escape}">
                                                    {$doc->name}
                                                </a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                    <div class="js-expand-loan-docs"></div>
                                </div>
                            </div>
                        {/foreach}
                        {* Закрытые займы *}
                        {assign var="hasClosedLoans" value=false}
                        {foreach $order_docs as $loan => $data}
                            {if $data['hidden'] || !$data['is_closed']}{continue}{/if}

                            {if !$hasClosedLoans}
                                {assign var="hasClosedLoans" value=true}
                                <h2 class="docs-header">Закрытые займы</h2>
                            {/if}

                            <div class="loan_docs closed_loan_name">
                                <h3 class="docs-header">{$loan}</h3>
                            </div>
                        {/foreach}

                        {if !empty($order_docs)}
                            <h2 class="docs-header">Остальные документы</h2>
                        {/if}

                        <ul class="docs_list">
                            {if $doc_bki}
                                <li>
                                    <a href="{$doc_bki}" target="_blank">
                                        Согласие клиента на получение информации из бюро кредитных историй
                                    </a>
                                </li>
                            {/if}

                            {if $docArbitrationAgreement}
                                <li>
                                    <a href="{$docArbitrationAgreement}" target="_blank">
                                        Арбитражное соглашение
                                    </a>
                                </li>
                            {/if}

                            {if $docArbitrationCessionary}
                                <li>
                                    <a href="{$docArbitrationCessionary}" target="_blank">
                                        Оферта (Предложение)
                                    </a>
                                </li>
                            {/if}

                            {if $last_order_id}
                                <li>
                                    <a href="/user/docs?action=asp_agreement&order_id={$last_order_id}" target="_blank">
                                        Соглашение об использовании АСП
                                    </a>
                                </li>

                                <li>
                                    <a href="/user/docs?action=offer_agreement&order_id={$last_order_id}" target="_blank">
                                        Соглашение о порядке акцепта оферты
                                    </a>
                                </li>
                            {/if}

                            {if $has_recurring_consent}
                                <li>
                                    <a href="/user/docs?action=soglasie_recurrent" target="_blank">
                                        Соглашение о применении регулярных (рекуррентных) платежей
                                    </a>
                                </li>
                            {/if}

                            {if $user->id == 56219}
                            <li>
                                <a href="{$config->root_url}/files/specials/v8_6194_34.pdf" target="_blank">
                                    Уведомление о цессии
                                </a>
                            </li>
                            {/if}

                            {if $user->id == 664871}
                            <li>
                                <a href="{$config->root_url}/files/doc/Договор.pdf" target="_blank">
                                    Договор
                                </a>
                            </li>
                            {/if}

                            {if $user->id == 592934}
                                <li>
                                    <a href="{$config->root_url}/files/doc/Dopolnitelnoe_soglashenie_Gavrilova_A.V.pdf" target="_blank">
                                        Дополнительное соглашение Гаврилова А.В
                                    </a>
                                </li>
                            {/if}

                            {if $penaltyCreditDoctorLink}
                                <li>
                                    <a href="{$penaltyCreditDoctorLink}" target="_blank" onclick="sendMetric('reachGoal', 'findzen')">
                                        Доступ к онлайн-сервису "Финдзен"
                                    </a>
                                </li>
                            {/if}

                            {if $penaltyCreditDoctorActivate}
                                <li id="activate_pcd" style="cursor: pointer"
                                    data-loan-id="{$loan_number}"
                                    data-action="activate"
                                    data-addon="pcd"
                                >
                                    <a>
                                        Доступ к онлайн-сервису "Финдзен"
                                    </a>
                                </li>
                            {/if}

                            {foreach $paid_loan_references as $paid_loan_reference}
                                <li>
                                    <a href="/document/{$user->id|escape:'url'}/{$paid_loan_reference['loan_document_id']|escape:'url'}.pdf"
                                       target="_blank">
                                        Справка о погашении займа №{$paid_loan_reference['number']}
                                    </a>
                                </li>
                            {/foreach}

                            {foreach $crm_docs as $crm_doc}
                                {if $crm_doc && $crm_doc->name}
                                    <li>
                                        {if $crm_doc->type == 'ASP_AGREEMENT'}
                                            {if !$crm_doc->replaced}
                                                <a href="{$config->root_url}/document/{$crm_doc->user_id}/{$crm_doc->id}.pdf" target="_blank">
                                                    {$crm_doc->name|escape}
                                                </a>
                                                <br>
                                                <a href="javascript:void(0)" onclick="showAspAgreementModal('{$crm_doc->id}', '{$crm_doc->user_id}')" style="font-size: 12px; color: #007bff;">
                                                    Подтвердить подписание через АСП
                                                </a>
                                            {else}
                                                <a href="/files/doc/doc_id_{$crm_doc->id}.pdf" target="_blank">
                                                    {$crm_doc->name|escape}
                                                </a>
                                                <br>
                                                <a href="javascript:void(0)" onclick="showAspAgreementModal('{$crm_doc->id}', '{$crm_doc->user_id}')" style="font-size: 12px; color: #007bff;">
                                                    Подтвердить подписание через АСП
                                                </a>
                                            {/if}
                                        {else}
                                            {if !$crm_doc->replaced}
                                                <a href="{$config->root_url}/document/{$crm_doc->user_id}/{$crm_doc->id}.pdf" target="_blank">
                                                    {$crm_doc->name|escape}
                                                    {if in_array($crm_doc->type,['PRICINA_OTKAZA_I_REKOMENDACII','ZAYAVLENIYE_OTKAZA_REKOMENDACII'])}
                                                        {$crm_doc->order_id}
                                                    {/if}
                                                </a>
                                            {else}
                                                <a href="/files/doc/doc_id_{$crm_doc->id}.pdf"
                                                     target="_blank">{$crm_doc->name|escape}</a>
                                            {/if}
                                        {/if}
                                    </li>
                                {/if}
                            {/foreach}
{*                            {if !empty($additional_action_2)}*}
{*                                <li>Уведомление*}
{*                                    <a href="user/docs?action=additional_service_2"*}
{*                                       target="_blank">*}
{*                                        О предоставлении дополнительных услуг*}
{*                                    </a>*}
{*                                </li>*}
{*                            {/if}*}
                            {if $loan_history}
                                <li><a id="link-references">Справки</a></li>
                                <li><a id="link-cessii">Цессии и Агентские договоры</a></li>
                            {/if}
                        </ul>

                        {if $loan_history}
                            {include file='user_docs__references.tpl'}
                            {include file='user_docs__notices_of_assigment.tpl'}
                        {/if}

                    {/if}
				</div>

			</div>
		</div>
	</div>
</section>
<script>
    var userId = '{$user_id}';

    document.addEventListener('DOMContentLoaded', function () {
        const uploadedLinks = document.querySelectorAll('.uploaded-doc-link');

        uploadedLinks.forEach(link => {
            const original = link.getAttribute('data-original-name');
            if (!original) return;

            // Добавляет пробел между строчными буквами (или цифрами) и заглавными в названии Документа
            let spaced = original.replace(/([а-яё0-9])([А-ЯЁ])/gu, '$1 $2');

            // Добавляет пробел между парой заглавных букв, если за ними идёт строчная в названии Документа
            spaced = spaced.replace(/([А-ЯЁ])([А-ЯЁ])(?=[а-яё])/gu, '$1 $2');

            link.textContent = spaced.trim();
        });
    });
</script>
    {literal}
        <script type="text/javascript">

            $('.download').click(function() {
                $('#loading').show();
                var url = 'ajax/download_documents.php?action=download_zip&user_id=' + userId;
                var downloadTimer = setInterval(function() {
                    if (document.readyState === 'complete') {
                        clearInterval(downloadTimer);
                        $('#loading').hide();
                    }
                }, 2000);
                window.location.href = url;
            });

            $('.js-toggle-loan-docs').click(function() {
                let $loan_docs = $(this).closest('.loan_docs');
                let isHidden = $loan_docs.attr('data-hidden') === '1';
                $loan_docs.attr('data-hidden', isHidden ? '0' : '1');
            });

            $('.js-expand-loan-docs').click(function() {
                let $loan_docs = $(this).closest('.loan_docs');
                $loan_docs.attr('data-hidden', '0');
            });
        </script>
    {/literal}

    <!-- Modal window for ASP Agreement confirmation -->
    <div id="aspAgreementModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeAspAgreementModal()">&times;</span>
                <h2>Подтверждение подписания через АСП</h2>
            </div>
            <div class="modal-body">
                <p>Для подтверждения подписания документа введите код, отправленный на ваш номер телефона:</p>
                <input type="text" id="aspCode" placeholder="Введите код" maxlength="6">
                <div id="aspError" style="color: red; display: none;"></div>
                <div id="aspSuccess" style="color: green; display: none;"></div>
            </div>
            <div class="modal-footer">
                <button onclick="confirmAspAgreement()" id="confirmButton">Подтвердить</button>
                <button onclick="closeAspAgreementModal()">Отмена</button>
            </div>
        </div>
    </div>

    <style>
        .docs-header {
            text-align: left;
            margin-left: 60px;
        }

        @media only screen and (max-width: 900px) {
            .loan_docs h2 {
                margin-left: 0 !important;
                width: auto !important;
            }
            .docs-header {
                margin-left: 0 !important;
            }
            .panel > ul.docs_list {
                margin-left: 0 !important;
            }
        }

        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
        }
        .modal-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .modal-body input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .modal-footer {
            margin-top: 15px;
            text-align: right;
        }
        .modal-footer button {
            margin-left: 10px;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .modal-footer button:first-child {
            background-color: #4CAF50;
            color: white;
        }
        .modal-footer button:last-child {
            background-color: #f44336;
            color: white;
        }
    </style>

    <script>
        var currentDocId = '';
        var currentUserId = '';

        function showAspAgreementModal(docId, userId) {
            currentDocId = docId;
            currentUserId = userId;
            document.getElementById('aspAgreementModal').style.display = 'block';
            document.getElementById('aspCode').focus();
        }

        function closeAspAgreementModal() {
            document.getElementById('aspAgreementModal').style.display = 'none';
            document.getElementById('aspCode').value = '';
            document.getElementById('aspError').style.display = 'none';
            document.getElementById('aspSuccess').style.display = 'none';
        }

        function confirmAspAgreement() {
            var code = document.getElementById('aspCode').value.trim();
            if (!code) {
                showError('Введите код подтверждения');
                return;
            }

            document.getElementById('confirmButton').disabled = true;
            document.getElementById('confirmButton').textContent = 'Подтверждаю...';

            $.ajax({
                url: 'ajax/confirm_asp_agreement.php',
                type: 'POST',
                data: {
                    document_id: currentDocId,
                    user_id: currentUserId,
                    asp_code: code
                },
                success: function(response) {
                    if (response.success) {
                        showSuccess('Документ успешно подписан через АСП');
                        setTimeout(function() {
                            closeAspAgreementModal();
                            location.reload();
                        }, 2000);
                    } else {
                        showError(response.error || 'Ошибка при подтверждении');
                    }
                },
                error: function() {
                    showError('Ошибка соединения с сервером');
                },
                complete: function() {
                    document.getElementById('confirmButton').disabled = false;
                    document.getElementById('confirmButton').textContent = 'Подтвердить';
                }
            });
        }

        function showError(message) {
            document.getElementById('aspError').textContent = message;
            document.getElementById('aspError').style.display = 'block';
            document.getElementById('aspSuccess').style.display = 'none';
        }

        function showSuccess(message) {
            document.getElementById('aspSuccess').textContent = message;
            document.getElementById('aspSuccess').style.display = 'block';
            document.getElementById('aspError').style.display = 'none';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAspAgreementModal();
            }
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('aspAgreementModal');
            if (event.target == modal) {
                closeAspAgreementModal();
            }
        }

        // Allow Enter key to submit
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && document.getElementById('aspAgreementModal').style.display === 'block') {
                confirmAspAgreement();
            }
        });

        $('#activate_pcd').on('click', function (e) {
            e.preventDefault();

            const $el    = $(e.currentTarget);
            const loanId = $el.data('loan-id');
            const action = $el.data('action');
            const addon  = $el.data('addon');

            // блокируем на время запроса
            $el.css('pointer-events', 'none').addClass('is-loading');

            $.ajax({
                url: '/ajax/actions_additionals.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    loan_id: loanId,
                    action: action,
                    type_addon: addon,
                },
                success: function (resp) {
                    if (resp && resp.success) {
                        location.reload();
                    } else {
                        alert(resp?.message || 'Ошибка выполнения');
                    }
                },
                error: function (xhr) {
                    console.log(xhr);
                    alert('Сеть/сервер недоступен');
                },
                complete: function () {
                    $el.css('pointer-events', '').removeClass('is-loading');
                }
            });
        });
    </script>
</section>
