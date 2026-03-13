{if $current_site_id === null || in_array('refinance_settings', $site_setting_names) || in_array('sbp_enabled', $site_setting_names) || in_array('sbp_recurrents_enabled', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('refinance_settings', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Реструктуризация
        </h3>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Статус</label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" name="refinance_settings[enabled]">
                        <option value="0" {if !$settings->refinance_settings['enabled']}selected{/if}>Выключено</option>
                        <option value="1" {if $settings->refinance_settings['enabled']}selected{/if}>Активно</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-md-6 col-12">
                    <div class="row pb-2">
                        <div class="col-12 col-md-8">
                            <label>Процентная ставка, %</label>
                        </div>
                        <div class="col-12 col-md-4">
                            <input  class="form-control" name="refinance_settings[percent]" value="{$settings->refinance_settings['percent']}"/>
                        </div>
                    </div>
                    <div class="row pb-2">
                        <div class="col-12 col-md-8">
                            <label>Первый платеж, %</label>
                        </div>
                        <div class="col-12 col-md-4">
                            <input  class="form-control" name="refinance_settings[first_pay]" value="{$settings->refinance_settings['first_pay']}"/>
                        </div>
                    </div>
                    <div class="row pb-2">
                        <div class="col-12 col-md-8">
                            <label>День просрочки займа, при котором предлагать рефинанс</label>
                        </div>
                        <div class="col-12 col-md-4">
                            <input  class="form-control" name="refinance_settings[days_overdue]" value="{$settings->refinance_settings['days_overdue']}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="row pb-2">
                        <div class="col-12 col-md-8">
                            <label>Количество платежей</label>
                        </div>
                        <div class="col-12 col-md-4">
                            <input  class="form-control" name="refinance_settings[pay_count]" value="{$settings->refinance_settings['pay_count']}"/>
                        </div>
                    </div>
                    <div class="row pb-2" style="display:none">
                        <div class="col-12 col-md-8">
                            <label>Период платежей, дней</label>
                        </div>
                        <div class="col-12 col-md-4">
                            <input  class="form-control" name="refinance_settings[pay_period]" value="{$settings->refinance_settings['pay_period']}"/>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    {/if}
    {if $current_site_id === null || in_array('sbp_enabled', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Оплата по СБП
        </h3>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12">
                    <label>Разрешить оплату по СБП в личном кабинете клиента</label>
                </div>
                <div class="col-12">
                    {foreach $organizations_for_sbp as $org}
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" {if $settings->sbp_enabled[$org->id]}checked{/if} name="sbp_enabled[{$org->id}]" value="1" id="sbp_enabled_{$org->id}">
                            <label class="form-check-label" for="sbp_enabled_{$org->id}">
                                {$org->short_name|escape}
                            </label>
                        </div>
                    {/foreach}
                </div>
            </div>

            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Разрешить рекуррентные платежи через СБП</label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" name="sbp_recurrents_enabled" id = "sbp_recurrents_enabled">
                        <option value="0" {if !$settings->sbp_recurrents_enabled}selected{/if}>Выключено</option>
                        <option value="1" {if $settings->sbp_recurrents_enabled}selected{/if}>Активно</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
{/if}
