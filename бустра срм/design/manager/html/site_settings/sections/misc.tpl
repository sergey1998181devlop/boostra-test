{if $current_site_id === null || in_array('prolongation_disable_timeout_minutes', $site_setting_names)}
<div class="row">
    <div class="col-12 col-md-10">
        <h3 class="box-title">Корректировка таймаута пролонгации</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col">
                    <div class="row mt-2">
                        <div class="col-5">
                            <label for="prolongation_disable_timeout_minutes">от 0 - 60 в минутах</label>
                        </div>
                        <div class="col">
                            <input type="number"
                                   id="prolongation_disable_timeout_minutes"
                                   min="0"
                                   max="60"
                                   step="1"
                                   class="form-control"
                                   name="prolongation_disable_timeout_minutes"
                                   value="{$settings->prolongation_disable_timeout_minutes}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr class="mt-3 mb-4" />
{/if}

{if $current_site_id === null || in_array('progress_bar_available', $site_setting_names)}
<div class="row">
    <div class="col-12 col-md-6">
        <h3 class="box-title">Прогресс-бар просрочки в ЛК</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-12 col-md-6">
                    <label for="overdue_progress_enabled">Статус</label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" id="overdue_progress_enabled" name="progress_bar_available">
                        <option value="0" {if !$settings->progress_bar_available}selected{/if}>Отключен</option>
                        <option value="1" {if $settings->progress_bar_available}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<hr class="mt-3 mb-4" />
{/if}

<div class="row">
    {if $current_site_id === null || in_array('base_organization_id', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">Организации для выдачи займов</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="return_threshold_days_fd">Основная организация</label>
                </div>
                <div class="col-6 col-md-6">
                    <select name="base_organization_id" class="form-control">
                        {foreach $organizations as $org}
                            {if in_array($org->id, $organizations_for_issuance['base']) || $settings->base_organization_id == $org->id}
                            <option value="{$org->id}" {if $settings->base_organization_id == $org->id}selected=""{/if}>{$org->short_name|escape}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="cross_organization_id">Первая кросс-заявка</label>
                </div>
                <div class="col-6 col-md-6">
                    <select name="cross_organization_id" class="form-control">
                        <option value="0" {if !$settings->cross_organization_id}selected=""{/if}>Нет</option>
                        {foreach $organizations as $org}
                            {if in_array($org->id, $organizations_for_issuance['cross']) || $settings->cross_organization_id == $org->id}
                            <option value="{$org->id}" {if $settings->cross_organization_id == $org->id}selected=""{/if}>{$org->short_name|escape}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="cross_organization_id2">Вторая кросс-заявка</label>
                </div>
                <div class="col-6 col-md-6">
                    <select name="cross_organization_id2" class="form-control">
                        <option value="0" {if !$settings->cross_organization_id2}selected=""{/if}>Нет</option>
                        {foreach $organizations as $org}
                            {if in_array($org->id, $organizations_for_issuance['cross']) || $settings->cross_organization_id2 == $org->id}
                                <option value="{$org->id}" {if $settings->cross_organization_id2 == $org->id}selected=""{/if}>{$org->short_name|escape}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
<div class="row">
    {if $current_site_id === null || in_array('newyear_promotion_enabled', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">Новогодняя акция</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-12 col-md-6">
                    <label for="newyear_promotion_enabled">Включить новогоднюю акцию</label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" id="newyear_promotion_enabled"
                            name="newyear_promotion_enabled">
                        <option value="0" {if !$settings->newyear_promotion_enabled}selected{/if}>Выключена</option>
                        <option value="1" {if $settings->newyear_promotion_enabled}selected{/if}>Включена</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
<div class="row">
    {if $current_site_id === null || in_array('fake_order_enabled', $site_setting_names)}
        <div class="col-12 col-md-6">
            <h3 class="box-title">Иммитация оффера в ЛК (фейк-автозаявка)</h3>
            <div class="form-group mb-3">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <label for="fake_order_enabled">Оффер включен</label>
                    </div>
                    <div class="col-12 col-md-6">
                        <select class="form-control" id="fake_order_enabled"
                                name="fake_order_enabled">
                            <option value="0" {if !$settings->fake_order_enabled}selected{/if}>Выключен</option>
                            <option value="1" {if $settings->fake_order_enabled}selected{/if}>Включен</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>