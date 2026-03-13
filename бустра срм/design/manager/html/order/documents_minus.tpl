    <div class="row">
        <div class="col-12">
            <div id="navpills-orders" class="tab-pane active">
                <div class="card">
                    <div class="card-body">
                        <div id = 'docs-table'>
                            <table class="table">
                                {foreach $minus_docs as $document}
                                    <tr>
                                        <td>
                                            <a href="{$document->url}" target="_blank">{$document->name|escape}</a>
                                        </td>
                                    </tr>
                                {/foreach}

                                {if !empty($creditworthiness_assessment)}
                                    <tr>
                                        <td>
                                            <a href="{$config->front_url}/document/{$order->user_id}/{$order->order_id}?action=creditworthiness_assessment&from_crm=1"
                                               target="_blank">Лист оценки платежеспособности заемщика</a>
                                        </td>
                                    </tr>
                                {/if}
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>