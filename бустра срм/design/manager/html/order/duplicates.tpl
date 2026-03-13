<div id="duplicates" class="tab-pane" role="tabpanel">
    <div class="row">
        <div class="col-12">
            <div class="tab-content br-n pn">
                <div id="navpills-orders" class="tab-pane active">
                    <div class="card">
                        <div class="card-body">
                            {if $userDuplicates}
                                <table class="table">
                                    <tr>
                                        <th>Профиль</th>
                                        <th>Совпадение</th>
                                    </tr>
                                    {foreach $userDuplicates as $userId => $duplicates}
                                        {foreach $duplicates as $duplicate}
                                            <tr>
                                                <td><a href="/client/{$userId}">{$userId}</a></td>
                                                <td>{$duplicate}</td>
                                            </tr>
                                        {/foreach}
                                    {/foreach}
                                </table>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>