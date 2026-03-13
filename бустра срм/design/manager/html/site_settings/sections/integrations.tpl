{if $current_site_id === null || in_array('voximplant_ai_enabled', $site_setting_names) || in_array('vc_enabled', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('voximplant_ai_enabled', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Оператор ИИ ФРОМТЕК на горячей линии </h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="voximplant_ai_enabled"></label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="voximplant_ai_enabled"
                            name="voximplant_ai_enabled">
                        <option value="0" {if !$settings->voximplant_ai_enabled}selected{/if}>Нет
                        </option>
                        <option value="1" {if $settings->voximplant_ai_enabled}selected{/if}>Да
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('vc_enabled', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Виртуальная карта</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="vc_enabled">Выдача займов через виртуальную карту</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="vc_enabled"
                            name="vc_enabled">
                        <option value="0" {if !$settings->vc_enabled}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->vc_enabled}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
{/if}
