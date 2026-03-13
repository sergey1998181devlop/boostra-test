    <div class="row">
        <div class="col-12">
            <div id="navpills-orders" class="tab-pane active">
                <div class="card">
                    <div class="card-body">
                        <div style="display: flex;justify-content: flex-end" class="mb-3">
                            {if in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator'])}
                                <button
                                        class="btn btn-success"
                                        id="uploadBtn"
                                >
                                    Добавить
                                </button>
                                <input
                                        type="file"
                                        id="pdfFile"
                                        style="display: none;"
                                        accept=".pdf, .doc, .docx"
                                        data-user = "{$order->user_id}"
                                        data-order = "{$order->order_id}"
                                >
                            {/if}
                        </div>
                        <div id = 'docs-table'>
                            <table class="table">

                                {foreach $documents as $document}
                                    <tr>
                                        <td>
                                            {if !($document->replaced)}
                                                <a href="{$document->doc_url}/document/{$document->user_id}/{$document->id}?from_crm=1"
                                                   target="_blank"
                                                   class="doc-link"
                                                   data-original-name="{$document->name|escape}">{$document->name|escape}</a>
                                            {else}
                                                <a href="{$document->doc_url}/files/doc/doc_id_{$document->id}.pdf"
                                                   target="_blank"
                                                   class="doc-link"
                                                   data-original-name="{$document->name|escape}">{$document->name|escape}</a>
                                            {/if}
                                        </td>
                                        <td>
                                            {$document->created|date} {$document->created|time}
                                        </td>
                                        {if  in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator'])}
                                            <td>

                                                <button class="btn btn-info btn-file-lk btn-file-lk-{$document->id}"
                                                        data-toggle="modal" data-target="#show-file-lk-modal"
                                                        data-document-id="{$document->id}" data-client_visible="{$document->client_visible}"id="file-lk-button"
                                                > {if $document->client_visible == 1}Непоказывать в ЛК  {else} Отобразить в ЛК {/if}
                                                </button>
                                            </td>
                                            <td>
                                                <button class="btn btn-danger btn-delete-for-user"
                                                        data-toggle="modal" data-target="#delete-modal"
                                                        data-document-id="{$document->id}" id="delete-button"
                                                        value="{$document->id}" data-table='1'
                                                        data-user="{$order->user_id}">Удалить
                                                </button>
                                            </td>
                                            <td>
                                                <button class="btn btn-primary btn-replace">
                                                    Заменить
                                                </button>
                                                <input
                                                        type="file"
                                                        class="replace-pdf-file"
                                                        style="display: none;"
                                                        accept=".pdf, .doc, .docx"
                                                        data-user = "{$order->user_id}"
                                                        data-order = "{$order->order_id}"
                                                        data-doctype = "document"
                                                        data-uid = '{$document->id}'
                                                >
                                            </td>
                                            <td>
                                                {if !($document->replaced)}
                                                    <button class="btn btn-info btn-download-file" data-type="not_rep" data-user ="{$document->user_id}" data-doc = "{$document->id}">
                                                        Скачать
                                                    </button>
                                                {else}
                                                    <button class="btn btn-info btn-download-file" data-type = 'rep'
                                                            data-user ="{$document->user_id}" data-doc = "{$document->id}">
                                                        Скачать
                                                    </button>
                                                {/if}
                                            </td>
                                        {/if}
                                    </tr>
                                {/foreach}
                                {foreach $asp_zaim_list as $asp_zaim}
                                    <tr>
                                        <td>
                                            <a href="{$config->front_url}/files/asp/{$asp_zaim->file_name}"
                                               target="_blank">Согласие субъекта на иные способы и частоту
                                                взаимодействия по займу N{$asp_zaim->zaim_number}</a>
                                        </td>
                                        <td></td>
                                        {if in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator'])}
                                            <td>
                                                <button class="btn btn-danger btn-delete-for-user"
                                                        data-toggle="modal" data-target="#delete-modal"
                                                        id="delete-button"
                                                        value="{$asp_zaim->id}" data-table='2'
                                                        data-user="{$order->user_id}">Удалить
                                                </button>
                                            </td>
                                            <td>
                                                <button class="btn btn-primary btn-replace">
                                                    Заменить
                                                </button>
                                                <input
                                                        type="file"
                                                        class="replace-pdf-file"
                                                        style="display: none;"
                                                        accept=".pdf, .doc, .docx"
                                                        data-user = "{$order->user_id}"
                                                        data-order = "{$order->order_id}"
                                                        data-doctype = "asp_zaim"
                                                        data-uid = {$asp_zaim->zaim_number}
                                                >
                                            </td>
                                            <td>
                                                <button class="btn btn-info btn-download-file" data-type = 'asp'
                                                        data-user ="{$order->user_id}" data-doc = "{$asp_zaim->zaim_number}">
                                                    Скачать
                                                </button>
                                            </td>
                                        {/if}
                                    </tr>
                                {/foreach}
                                {if $order->organization_id == 13 or $order->organization_id == 15}
                                    <tr>
                                        <td>
                                            <a href="{$config->front_url}/preview/soglasie_recurrent?{$recurrent_doc_url_params}"
                                               target="_blank">Соглашение о регулярных (рекуррентных) платежах {$recurrent_doc_name}</a>
                                        </td>
                                    </tr>
                                {/if}
                                {foreach $removed_user_cabinet_docs as $removed_user_cabinet_doc}
                                    <tr>
                                        <td>
                                            <a href="{$removed_user_cabinet_doc->url}" target="_blank">{$removed_user_cabinet_doc->name|escape}</a>
                                        </td>
                                        <td>
                                            {$removed_user_cabinet_doc->created|date} {$removed_user_cabinet_doc->created|time}
                                        </td>
                                    </tr>
                                {/foreach}
                                <tr>
                                    {if $page_error}
                                    <div>
                                        <h2>Раздел сейчас не доступен!</h2>
                                        <br/>
                                        <h4>Попробуйте зайти на эту страницу позже.</h4>
                                    </div>
                                    {elseif $uid_docs|count > 0}
                                    {foreach $uid_docs as $doc}
                                    {if !($doc->hide)}
                                <tr>
                                    <td>
                                        <a href="{$config->root_url}/order/{$order->order_id}?action=load&uid={$doc->uid}&type={$doc->name|escape}"
                                           target="_blank">
                                            {$doc->name|escape}
                                        </a>
                                    </td>
                                    <td></td>
                                    {if in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator'])}
                                        <td>
                                            <button class="btn btn-danger btn-delete-for-user"
                                                    data-toggle="modal" data-target="#delete-modal"
                                                    id="delete-button"
                                                    value="{$doc->uid}" data-table='3'
                                                    data-zaim="{$zaimNumber}"
                                                    data-type="{$doc->name}"
                                            >Удалить
                                            </button>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-replace">
                                                Заменить
                                            </button>
                                            <input
                                                    type="file"
                                                    class="replace-pdf-file"
                                                    style="display: none;"
                                                    accept=".pdf, .doc, .docx"
                                                    data-user="{$order->user_id}"
                                                    data-order="{$order->order_id}"
                                                    data-uid="{$doc->uid}"
                                                    data-type="{$doc->name}"
                                                    data-doctype="doc_1c"
                                                    data-zaim="{$zaimNumber}"
                                            >
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-download-file"
                                                    data-type="load" data-user ="{$document->user_id}" data-doc-type = "{$doc->name|escape}" data-doc = '{$doc->uid}' data-order="{$order->order_id}">
                                                Скачать
                                            </button>
                                        </td>
                                    {/if}
                                </tr>
                                {/if}
                                {/foreach}
                                {elseif $user_docs|count > 0}
                                {foreach $user_docs as $in => $doc}
                                    <tr>
                                        <td>
                                            <a href="{$config->root_url}/files/contracts/{$doc->filename}?{math equation='rand(100000,999999)'}"
                                               target="_blank">
                                                {if $doc->type == 'contract'}Договор
                                                {elseif $doc->type == 'application'}Заявление о предоставлении микрозайма
                                                {elseif $doc->type == 'other'}Прочие сведения и заверения о клиенте
                                                {elseif $doc->type == 'consent'}Согласие клиента на получение информации из бюро кредитных историй
                                                {elseif $doc->type == 'statementprolongation'}Заявление о пролонгации договора микрозайма
                                                {elseif $doc->type == 'prolongation'}Дополнительное соглашение
                                                {elseif $doc->type == 'insure'}Полис-оферта комбинированного страхования
                                                {elseif $doc->type == 'cession'}Уведомление по цессии
                                                {else}Документ{/if}
                                            </a>
                                        </td>
                                        <td></td>
                                        <td>
                                            <button class="btn btn-danger" data-toggle="modal"
                                                    data-target="#delete-modal">Удалить
                                            </button>
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-download-file"
                                                    data-type="c" data-user ="{$document->user_id}"  data-doc = '{$doc->filename}'>
                                                Скачать
                                            </button>
                                        </td>
                                    </tr>
                                {/foreach}

                                {elseif $docs_bki}

                                <tr>
                                    <td><a href="{$config->root_url}/files/contracts/{$docs_bki[0]}"
                                           target="_blank">
                                            Согласие клиента на получение информации из бюро кредитных
                                            историй
                                        </a>
                                    </td>
                                    <td></td>
                                    <td>
                                        <button class="btn btn-danger" data-toggle="modal"
                                                data-target="#delete-modal">Удалить
                                        </button>
                                    </td>
                                    <td>
                                        <button class="btn btn-info">Скачать</button>
                                    </td>
                                </tr>
                                {/if}
                                {foreach $uploaded_docs as  $doc}
                                    <tr>
                                        <td class="file-name">
                                            <a href="{$config->front_url}/files/uploaded_files/{$doc->name}"
                                               target="_blank">
                                                {$doc->name}
                                            </a>
                                        </td>
                                        <td></td>
                                        {if in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator'])}
                                            <td>
                                                <button class="btn btn-danger btn-delete-for-user"
                                                        data-toggle="modal" data-target="#delete-modal"
                                                        id="delete-button"
                                                        value="{$doc->id}" data-table='4'
                                                >
                                                    Удалить
                                                </button>
                                            </td>
                                            <td>
                                                <button class="btn btn-primary btn-replace">
                                                    Заменить
                                                </button>
                                                <input
                                                        type="file"
                                                        class="replace-pdf-file"
                                                        style="display: none;"
                                                        accept=".pdf, .doc, .docx"
                                                        data-user = "{$order->user_id}"
                                                        data-order = "{$order->order_id}"
                                                        data-doctype = "uploaded_files"
                                                >
                                            </td>
                                            <td>
                                                <button class="btn btn-info btn-download-file"
                                                        data-type="up"  data-doc-type = "{$doc->name|escape}" >
                                                    Скачать
                                                </button>
                                            </td>
                                        {/if}
                                    </tr>
                                {/foreach}

                                {if $additional_reference}
                                    {foreach $order->user->loan_history as $loan_history_item}
                                        {if $loan_history_item->number == $additional_reference.loanId}
                                            <tr>
                                                <td>
                                                    <a href="#"
                                                       class="download-additional-reference"
                                                       data-loan-id="{$additional_reference.loanId}"
                                                    >Справка о всех дополнительных услугах</a>
                                                </td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        {/if}
                                    {/foreach}
                                {/if}

                                {if !empty($creditworthiness_assessment)}
                                    <tr>
                                        <td>
                                            <a href="{$config->front_url}/document/{$order->user_id}/{$order->order_id}?action=creditworthiness_assessment&from_crm=1"
                                               target="_blank">Лист оценки платежеспособности заемщика</a>
                                        </td>
                                    </tr>
                                {/if}

                                {if $manager->role != 'verificator_minus'}
                                    <tr>
                                        <td>
                                            <form action="{$smarty.server.REQUEST_URI}" method="POST">
                                                <input type="hidden" name="action" value="get_user_files"/>
                                                <input type="hidden" name="order_id"
                                                       value="{$order->order_id}"/>
                                                <button class="btn btn-outline-info" type="submit">Скачать досье
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                {/if}
                            </table>
                        </div>
                    </div>
                </div>
                {if $order->user->loan_history}
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <h3>Справки</h3>
                            </div>
                            <div id = 'reference-table'>
                                <table class="table">

                                    {foreach $order->user->loan_history as $loan_history_item}
                                        {if $loan_history_item->loan_percents_summ == 0 && $loan_history_item->loan_body_summ == 0}
                                            <tr>
                                                <td>
                                                    <a href="#"
                                                       class="download-reference"
                                                       data-loan-id="{$loan_history_item->number}"
                                                       data-reference-type="SPRAVKA_O_ZAKRITII"
                                                    >{$loan_history_item->number} - Справка о закрытии </a>
                                                </td>
                                            </tr>
                                        {/if}
                                        <tr>
                                            <td>
                                                <a href="#"
                                                   class="download-reference"
                                                   data-loan-id="{$loan_history_item->number}"
                                                   data-reference-type="SPRAVKA_O_ZADOLZHENNOSTI"
                                                >{$loan_history_item->number} - Справка о сумме задолженности </a>
                                            </td>
                                            <td></td>
                                        </tr>
                                    {/foreach}
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <h3>Цессии и Агентские договоры</h3>
                            </div>
                            <div id='cessii-table'>
                                <table class="table">
                                    {foreach $order->user->loan_history as $loan_history_item}
                                        {if $loan_history_item->number|strstr:$order->order_id}
                                            <tr>
                                                <td>
                                                    <a href="#"
                                                       class="download-cessii"
                                                       data-loan-id="{$loan_history_item->number}"
                                                    >{$loan_history_item->number}</a>
                                                </td>
                                                <td></td>
                                            </tr>
                                        {/if}
                                    {/foreach}
                                </table>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>

        </div>
    </div>