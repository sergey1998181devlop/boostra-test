{if $current_site_id === null || in_array('repay_max_count', $site_setting_names) || in_array('repay_timeout', $site_setting_names) || in_array('installments_enabled', $site_setting_names) || in_array('autoconfirm_enabled', $site_setting_names) || in_array('autoconfirm_flow_utm_sources', $site_setting_names) || in_array('autoconfirm_2_flow_utm_sources', $site_setting_names) || in_array('autoconfirm_2_flow_cross_utm_sources', $site_setting_names) || in_array('autoconfirm_crm_auto_approve_utm_sources', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('repay_max_count', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Перевыдача при отсутствии средств B2P
        </h3>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Количество попыток выдачи</label>
                </div>
                <div class="col-12 col-md-6">
                    <input class="form-control" name="repay_max_count" value="{$settings->repay_max_count}" />
                </div>
            </div>
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Таймаут между попытками, минут</label>
                </div>
                <div class="col-12 col-md-6">
                    <input class="form-control" name="repay_timeout" value="{$settings->repay_timeout}" />
                </div>
            </div>
        </div>
    </div>
    {/if}

    {if $current_site_id === null || in_array('installments_enabled', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Инстоллменты
        </h3>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Статус</label>
                </div>
                <div class="col-12 col-md-6">
                    <select name="installments_enabled" class="form-control">
                        <option value="0" {if !$settings->installments_enabled}selected{/if}>Отключены</option>
                        <option value="1" {if $settings->installments_enabled}selected{/if}>Включены</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>

{if $current_site_id === null || in_array('autoconfirm_enabled', $site_setting_names) || in_array('autoconfirm_flow_utm_sources', $site_setting_names) || in_array('autoconfirm_2_flow_utm_sources', $site_setting_names) || in_array('autoconfirm_2_flow_cross_utm_sources', $site_setting_names) || in_array('autoconfirm_crm_auto_approve_utm_sources', $site_setting_names)}
<div class="row">
    <div class="col-12 col-md-10">
        <h3 class="box-title">
            Авто подтверждение
        </h3>

        {if $current_site_id === null || in_array('autoconfirm_enabled', $site_setting_names)}
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Автоподтверждение договора при подаче заявки</label>
                </div>
                <div class="col-12 col-md-6">
                    <select name="autoconfirm_enabled" class="form-control">
                        <option value="0" {if !$settings->autoconfirm_enabled}selected{/if}>Отключено</option>
                        <option value="1" {if $settings->autoconfirm_enabled}selected{/if}>Включено</option>
                    </select>
                </div>
            </div>
        </div>
        {/if}

        {if $current_site_id === null || in_array('autoconfirm_flow_utm_sources', $site_setting_names)}
        <div class="row mt-2">
            <div class="col-5">
                <label for="autoconfirm_flow_utm_sources">UTM метки НК (через запятую), для автовыдачи</label>
            </div>
            <div class="col">
                <textarea
                        id="autoconfirm_flow_utm_sources"
                        name="autoconfirm_flow_utm_sources"
                        class="form-control"
                        rows="4"
                >{if $settings->autoconfirm_flow_utm_sources}{implode(',', $settings->autoconfirm_flow_utm_sources)}{/if}</textarea>
            </div>
        </div>
        {/if}

        {if $current_site_id === null || in_array('autoconfirm_2_flow_utm_sources', $site_setting_names)}
        <div class="row mt-2">
            <div class="col-5">
                <label for="autoconfirm_2_flow_utm_sources">UTM метки НК (через запятую), для автовыдачи 2</label>
            </div>
            <div class="col">
                <textarea
                        id="autoconfirm_2_flow_utm_sources"
                        name="autoconfirm_2_flow_utm_sources"
                        class="form-control"
                        rows="4"
                >{if $settings->autoconfirm_2_flow_utm_sources}{implode(',', $settings->autoconfirm_2_flow_utm_sources)}{/if}</textarea>
            </div>
        </div>
        {/if}

        {if $current_site_id === null || in_array('autoconfirm_2_flow_cross_utm_sources', $site_setting_names)}
        <div class="row mt-2">
            <div class="col-5">
                <label for="autoconfirm_2_flow_cross_utm_sources">UTM метки cross_order (через запятую), для автовыдачи 2</label>
            </div>
            <div class="col">
                <textarea
                        id="autoconfirm_2_flow_cross_utm_sources"
                        name="autoconfirm_2_flow_cross_utm_sources"
                        class="form-control"
                        rows="4"
                >{if $settings->autoconfirm_2_flow_cross_utm_sources}{implode(',', $settings->autoconfirm_2_flow_cross_utm_sources)}{/if}</textarea>
            </div>
        </div>
        {/if}

        {if $current_site_id === null || in_array('autoconfirm_crm_auto_approve_utm_sources', $site_setting_names)}
        <div class="row mt-2">
            <div class="col-5">
                <label for="autoconfirm_crm_auto_approve_utm_sources">UTM метки ПК/НК (через запятую), для crm_auto_approve</label>
            </div>
            <div class="col">
                <textarea
                        id="autoconfirm_crm_auto_approve_utm_sources"
                        name="autoconfirm_crm_auto_approve_utm_sources"
                        class="form-control"
                        rows="4"
                >{if $settings->autoconfirm_crm_auto_approve_utm_sources}{implode(',', $settings->autoconfirm_crm_auto_approve_utm_sources)}{/if}</textarea>
            </div>
        </div>
        {/if}
    </div>
</div>
{/if}
<hr class="mt-3 mb-4" />
{/if}
