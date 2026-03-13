{*
 * Секция: Автозаявки и выдача для НК
 *}
{if $current_site_id === null || in_array('sum_order_auto_approve', $site_setting_names) || in_array('enable_loan_nk', $site_setting_names) || in_array('enable_b2p_for_nk', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('sum_order_auto_approve', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Автозаявки - максимально одобренная сумма скорингом (Аксилинк)
        </h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-12 col-md-6">
                    <label><small>Сумма из скоринга</small></label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" name="sum_order_auto_approve[scoring]">
                        <option value="sum" {if $settings->sum_order_auto_approve['scoring'] === 'sum'}selected{/if}>С учетом ПДН (мин)</option>
                        <option value="sum_no_pti" {if $settings->sum_order_auto_approve['scoring'] === 'sum_no_pti'}selected{/if}>Без учета ПДН (макс)</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 col-md-6">
                    <label><small>Если нет результата от скоринга, сумма по умолчанию</small></label>
                </div>
                <div class="col-12 col-md-6">
                    <input class="form-control" name="sum_order_auto_approve[default]" value="{$settings->sum_order_auto_approve['default']}" />
                </div>
            </div>
        </div>
    </div>
    {/if}

    {if $current_site_id === null || in_array('enable_loan_nk', $site_setting_names) || in_array('enable_b2p_for_nk', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Выдача для НК
        </h3>
        <div class="form-group mb-3">
            <div class="">
                <select class="form-control" name="enable_loan_nk">
                    <option value="1" {if $settings->enable_loan_nk}selected{/if}>Включена</option>
                    <option value="0" {if !$settings->enable_loan_nk}selected{/if}>Отключена</option>
                </select>
            </div>
        </div>
        <div class="row pt-2">
            <div class="col-12 col-md-6">
                <label>Выдача НК через Best2pay</label>
            </div>
            <div class="col-12 col-md-6">
                <select class="form-control" name="enable_b2p_for_nk">
                    <option value="1" {if $settings->enable_b2p_for_nk}selected{/if}>Выдавать через Best2pay</option>
                    <option value="0" {if !$settings->enable_b2p_for_nk}selected{/if}>Выдавать через Tinkoff</option>
                </select>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
{/if}
