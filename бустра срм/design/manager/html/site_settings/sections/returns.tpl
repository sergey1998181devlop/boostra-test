{if $current_site_id === null || in_array('return_threshold_days_fd', $site_setting_names) || in_array('return_threshold_days_zo', $site_setting_names)}
<div class="row">
    <div class="col-12 col-md-10">
        <h3 class="box-title">Настройки дней, в течении которых клиенту виден ФД и ЗО, после возврата ФД</h3>

        {if $current_site_id === null || in_array('return_threshold_days_fd', $site_setting_names)}
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="return_threshold_days_fd">Финансовый доктор</label>
                </div>
                <div class="col-6 col-md-6">
                    <input type="number"
                           id="return_threshold_days_fd"
                           min="0"
                           max="999"
                           step="1"
                           class="form-control"
                           name="return_threshold_days_fd"
                           value="{$settings->return_threshold_days_fd}">
                </div>
            </div>
        </div>
        {/if}

        {if $current_site_id === null || in_array('return_threshold_days_zo', $site_setting_names)}
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="return_threshold_days_zo">Звездный оракул</label>
                </div>
                <div class="col-6 col-md-6">
                    <input type="number"
                           id="return_threshold_days_zo"
                           min="0"
                           max="999"
                           step="1"
                           class="form-control"
                           name="return_threshold_days_zo"
                           value="{$settings->return_threshold_days_zo}">
                </div>
            </div>
        </div>
        {/if}
    </div>
</div>
<hr class="mt-3 mb-4" />
{/if}
