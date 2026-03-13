{if $current_site_id === null || in_array('safe_flow', $site_setting_names) || in_array('unsafe_flow', $site_setting_names) || in_array('fake_dops', $site_setting_names) || in_array('attention_scammers', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('safe_flow', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Безопасное флоу для органики</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="safe_flow"> Безопасное флоу  на НК и ПК.</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="safe_flow"
                            name="safe_flow">
                        <option value="0" {if !$settings->safe_flow}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->safe_flow}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('unsafe_flow', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Опасное флоу</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="unsafe_flow">Опасное флоу.</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="unsafe_flow"
                            name="unsafe_flow">
                        <option value="0" {if !$settings->unsafe_flow}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->unsafe_flow}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('fake_dops', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Фейковые ДОПы</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="fake_dops"> Фейковые ДОПы</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="fake_dops"
                            name="fake_dops">
                        <option value="0" {if !$settings->fake_dops}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->fake_dops}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('attention_scammers', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Осторожно мошенники</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="attention_scammers"> Осторожно мошенники</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="attention_scammers"
                            name="attention_scammers">
                        <option value="0" {if !$settings->attention_scammers}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->attention_scammers}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
    
    
<div class="row">
    {if $current_site_id === null || in_array('show_order_information_days', $site_setting_names)}
        <div class="col-12 col-md-10">
            <h3 class="box-title">Срок принудительного показа информации в ЛК, дней</h3>
            <div class="form-group mb-3">
                <div class="row">
                    <div class="col-6 col-md-6">
                        <label for="show_order_information_days">Срок принудительного показа информации (сумма займа) в ЛК, дней</label>
                        <small class="text-muted d-block">Автоматически отключает принудительный показ через N дней после включения. По умолчанию: 14 дней</small>
                    </div>
                    <div class="col-6 col-md-6">
                        <input type="number" class="form-control" id="show_order_information_days"
                               name="show_order_information_days"
                               value="{$settings->show_order_information_days|intval}"
                               min="1" max="365" step="1" placeholder="14">
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>
    
<hr class="mt-3 mb-4" />
{/if}

{if $current_site_id === null || in_array('organization_switch', $site_setting_names)}
<div class="row">
    <div class="col-12 col-md-10">
        <h3 class="box-title">Переключение организации на заявке <span class="text-primary">НК</span> первички (Фрида). После сохранения перезапустить скоринги</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-3">
                    <label for="organization_switch[enabled]"> Статус</label>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-control" id="organization_switch[enabled]" name="organization_switch[enabled]">
                        <option value="0" {if !$settings->organization_switch['enabled']}selected{/if}>Выключено</option>
                        <option value="1" {if $settings->organization_switch['enabled']}selected{/if}>Включено</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label for="organization_switch[test_user_only]"> Только тестовый клиент <b>(для ВКЛ)</b></label>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-control" id="organization_switch[test_user_only]" name="organization_switch[test_user_only]">
                        <option value="0" {if !$settings->organization_switch['test_user_only']}selected{/if}>Все клиенты</option>
                        <option value="1" {if $settings->organization_switch['test_user_only']}selected{/if}>Только тестовый</option>
                    </select>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-6 col-md-3">
                    <label for="organization_switch[chance]">Шанс смены организации (0-100%)</label>
                </div>
                <div class="col-6 col-md-3">
                    <input type="number"
                        id="organization_switch[chance]"
                        min="0"
                        max="100"
                        step="1"
                        class="form-control"
                        name="organization_switch[chance]"
                        value="{$settings->organization_switch['chance']}"
                    >
                </div>

                <div class="col-6 col-md-3">
                    <label for="organization_switch[max_limit]">Максимум смен в день (0 - без ограничений)</label>
                </div>
                <div class="col-6 col-md-3">
                    <input type="number"
                        id="organization_switch[max_limit]"
                        min="0"
                        step="1"
                        class="form-control"
                        name="organization_switch[max_limit]"
                        value="{$settings->organization_switch['max_limit']}"
                    >
                </div>

                <div class="col-6 col-md-3">
                    <label for="organization_switch[max_pdn]">Максимальный ПДН</label>
                </div>
                <div class="col-6 col-md-3">
                    <input type="number"
                           id="organization_switch[max_pdn]"
                           min="0"
                           step="1"
                           class="form-control"
                           name="organization_switch[max_pdn]"
                           value="{$settings->organization_switch['max_pdn']}"
                    >
                </div>

                <div class="col-6 col-md-3">
                    <label for="organization_switch[enabled_river]" id="organization_switch[enabled_river]">Ручеек</label>
                </div>
                <div class="col-6 col-md-3">
                    <select name="organization_switch[enabled_river]" class="form-control">
                        <option value="0" {if !$settings->organization_switch['enabled_river']}selected=""{/if}>Не выбрано</option>
                        {for $number=4 to 5}
                            <option value="{$number}" {if $settings->organization_switch['enabled_river'] == $number}selected=""{/if}>
                                {if $number == 4}Старый без ВКЛ (4-я версия){/if}
                                {if $number == 5}Новый с ВКЛ (5-я версия){/if}
                            </option>
                        {/for}
                    </select>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-3">
                    <label for="organization_switch[utm_sources]">UTM метки <b>(для ВКЛ)</b></label>
                </div>
                <div class="col-9">
                    <textarea
                            id="organization_switch[utm_sources]"
                            name="organization_switch[utm_sources]"
                            class="form-control"
                            rows="4"
                    >{if $settings->organization_switch['utm_sources']}{implode(',', $settings->organization_switch['utm_sources'])}{/if}
                    </textarea>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-3">
                    <label for="organization_switch[new_organization_id]">Смена <strong>на</strong> организацию</label>
                </div>
                <div class="col-9">
                    <select name="organization_switch[new_organization_id]" id="organization_switch[new_organization_id]" class="form-control">
                        <option value="0" {if !$settings->organization_switch['new_organization_id']}selected=""{/if}>Не выбрано</option>
                        {foreach $organizations as $org}
                            <option value="{$org->id}" {if $settings->organization_switch['new_organization_id'] == $org->id}selected=""{/if}>{$org->short_name|escape}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-6">
                    <label for="organization_switch[auto_base_organization_switch][enabled]">Автоматическое переключение базовой организации</label>
                </div>
                <div class="col-6">
                    <select class="form-control"
                            id="organization_switch[auto_base_organization_switch][enabled]"
                            name="organization_switch[auto_base_organization_switch][enabled]">
                        <option value="0" {if !$settings->organization_switch['auto_base_organization_switch']['enabled']}selected{/if}>Выключено</option>
                        <option value="1" {if $settings->organization_switch['auto_base_organization_switch']['enabled']}selected{/if}>Включено</option>
                    </select>
                </div>
            </div>

            <div class="row mt-1 align-items-end">
                <div class="col-12">
                    <label for="organization_switch[auto_base_organization_switch][organization_1][organization_id]">Базовая организация 1</label>
                    <select
                            id="organization_switch[auto_base_organization_switch][organization_1][organization_id]"
                            name="organization_switch[auto_base_organization_switch][organization_1][organization_id]"
                            class="form-control">
                        <option value="0" {if !$settings->organization_switch['auto_base_organization_switch']['organization_1']['organization_id']}selected=""{/if}>Не выбрано</option>
                        {foreach $organizations as $org}
                            <option value="{$org->id}" {if $settings->organization_switch['auto_base_organization_switch']['organization_1']['organization_id'] == $org->id}selected=""{/if}>{$org->short_name|escape}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-4 mt-2">
                    <label for="organization_switch[auto_base_organization_switch][organization_1][chance]">Какой процент заявок должен создаваться c базовой организацией 1</label>
                    <input type="number"
                           id="organization_switch[auto_base_organization_switch][organization_1][chance]"
                           min="0"
                           max="100"
                           class="form-control"
                           name="organization_switch[auto_base_organization_switch][organization_1][chance]"
                           value="{$settings->organization_switch['auto_base_organization_switch']['organization_1']['chance']}"
                    >
                </div>
                <div class="col-4 mt-2">
                        <label for="organization_switch[auto_base_organization_switch][organization_1][max_issuance_amount]">Максимальная сумма выдачи за день на базовую организацию 1 (0 - без ограничений)</label>
                    <input type="number"
                           id="organization_switch[auto_base_organization_switch][organization_1][max_issuance_amount]"
                           min="0"
                           class="form-control"
                           name="organization_switch[auto_base_organization_switch][organization_1][max_issuance_amount]"
                           value="{$settings->organization_switch['auto_base_organization_switch']['organization_1']['max_issuance_amount']}"
                    >
                </div>
                <div class="col-4 mt-2">
                    <label for="organization_switch[auto_base_organization_switch][organization_1][min_balance]">Минимальный остаток баланса для создания заявок с организацией 1 (0 - без ограничений)</label>
                    <input type="number"
                           id="organization_switch[auto_base_organization_switch][organization_1][min_balance]"
                           min="0"
                           class="form-control"
                           name="organization_switch[auto_base_organization_switch][organization_1][min_balance]"
                           value="{$settings->organization_switch['auto_base_organization_switch']['organization_1']['min_balance']}"
                    >
                </div>
            </div>

            <div class="row mt-4 align-items-end">
                <div class="col-12">
                    <label for="organization_switch[auto_base_organization_switch][organization_2][organization_id]">Базовая организация 2</label>
                    <select
                            id="organization_switch[auto_base_organization_switch][organization_2][organization_id]"
                            name="organization_switch[auto_base_organization_switch][organization_2][organization_id]" class="form-control">
                        <option value="0" {if !$settings->organization_switch['auto_base_organization_switch']['organization_2']['organization_id']}selected=""{/if}>Не выбрано</option>
                        {foreach $organizations as $org}
                            <option value="{$org->id}" {if $settings->organization_switch['auto_base_organization_switch']['organization_2']['organization_id'] == $org->id}selected=""{/if}>{$org->short_name|escape}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-4 mt-2">
                    <label for="organization_switch[auto_base_organization_switch][organization_2][chance]">Какой процент заявок должен создаваться c базовой организацией 2</label>
                    <input type="number"
                           id="organization_switch[auto_base_organization_switch][organization_2][chance]"
                           min="0"
                           max="100"
                           class="form-control"
                           name="organization_switch[auto_base_organization_switch][organization_2][chance]"
                           value="{$settings->organization_switch['auto_base_organization_switch']['organization_2']['chance']}"
                    >
                </div>
                <div class="col-4 mt-2">
                        <label for="organization_switch[auto_base_organization_switch][organization_2][max_issuance_amount]">Максимальная сумма выдачи за день на базовую организацию 2 (0 - без ограничений)</label>
                    <input type="number"
                           id="organization_switch[auto_base_organization_switch][organization_2][max_issuance_amount]"
                           min="0"
                           class="form-control"
                           name="organization_switch[auto_base_organization_switch][organization_2][max_issuance_amount]"
                           value="{$settings->organization_switch['auto_base_organization_switch']['organization_2']['max_issuance_amount']}"
                    >
                </div>
                <div class="col-4 mt-2">
                    <label for="organization_switch[auto_base_organization_switch][organization_2][min_balance]">Минимальный остаток баланса для создания заявок с организацией 2 (0 - без ограничений)</label>
                    <input type="number"
                           id="organization_switch[auto_base_organization_switch][organization_2][min_balance]"
                           min="0"
                           class="form-control"
                           name="organization_switch[auto_base_organization_switch][organization_2][min_balance]"
                           value="{$settings->organization_switch['auto_base_organization_switch']['organization_2']['min_balance']}"
                    >
                </div>
            </div>
        </div>
    </div>
</div>
<hr class="mt-3 mb-4" />
{/if}

{*
{if in_array('allow_simplified_flow', $site_setting_names)}
<div class="row">
        <div class="col-12 col-md-10">
            <h3 class="box-title">Флаг пропуска запроса КИ в Акси (Фрида)</h3>
            <div class="form-group mb-3">
                <div class="row">
                    <div class="col-6 col-md-6">
                        <label for="allow_simplified_flow"> Флаг <strong>allow_simplified_flow</strong> (Только автозаявки)</label>
                    </div>
                    <div class="col-6 col-md-6">
                        <select class="form-control" id="allow_simplified_flow"
                                name="allow_simplified_flow">
                            <option value="0" {if !$settings->allow_simplified_flow}selected{/if}>Выключен</option>
                            <option value="1" {if $settings->allow_simplified_flow}selected{/if}>Включен</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
</div>
<hr class="mt-3 mb-4" />
{/if}*}
