<link href="design/{$settings->theme|escape}/css/user_docs__references.css" rel="stylesheet" type="text/css">

<section id="references_wrapper" class="--hide">
    <div class="tab_wrapper">
            <div class="tab_tabs">
                <h2>Справки доступные для скачивания</h2>
                <div id = 'reference-table'>
                    <table class="table table-references">
                        {foreach $loan_history as $loan_history_item}
                            {if $loan_history_item->loan_percents_summ == 0 && $loan_history_item->loan_body_summ == 0}
                                <tr>
                                    <td style="text-align: left">
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
                            </tr>
                        {/foreach}


                    </table>
                </div>

            </div>
        </div>

</section>

{* Модальное окно для информации о проданном договоре *}
<div id="soldLoanModal" class="sold-loan-modal-overlay">
    <div class="sold-loan-modal">
        <div class="sold-loan-modal-header">
            <h3>Договор продан</h3>
            <button class="sold-loan-modal-close">&times;</button>
        </div>
        <div class="sold-loan-modal-body">
            <p id="soldLoanMessage"></p>
            <div id="soldLoanContact"></div>
        </div>
        <div class="sold-loan-modal-footer">
            <button class="sold-loan-modal-btn" onclick="closeSoldLoanModal()">Закрыть</button>
        </div>
    </div>
</div>

<script src="design/{$settings->theme|escape}/js/user_docs__references.js"></script>
