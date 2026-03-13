{if $current_site_id === null || in_array('site_warning_message_enabled', $site_setting_names) || in_array('site_warning_message_enabled_main_page', $site_setting_names) || in_array('site_warning_message', $site_setting_names) || in_array('site_warning_message_background_color', $site_setting_names) || in_array('site_warning_message_text_color', $site_setting_names) || in_array('addresses_is_dadata', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('site_warning_message_enabled', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Предупреждение на сайте
        </h3>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Активность сообщения</label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" name="site_warning_message_enabled">
                        <option value="0" {if !$settings->site_warning_message_enabled}selected{/if}>Выключено</option>
                        <option value="1" {if $settings->site_warning_message_enabled}selected{/if}>Активно</option>
                    </select>
                </div>
            </div>
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Отображать на главной</label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" name="site_warning_message_enabled_main_page">
                        <option value="0" {if !$settings->site_warning_message_enabled_main_page}selected{/if}>Выключено</option>
                        <option value="1" {if $settings->site_warning_message_enabled_main_page}selected{/if}>Включено</option>
                    </select>
                </div>
            </div>
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Текст сообщения</label>
                </div>
                <div class="col-12 col-md-6">
                    <textarea class="form-control" name="site_warning_message">{$settings->site_warning_message}</textarea>
                </div>
            </div>
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Цвет фона баннера</label>
                </div>
                <div class="col-12 col-md-6">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="color" 
                               class="form-control" 
                               name="site_warning_message_background_color" 
                               id="warning_color_picker"
                               value="{$settings->site_warning_message_background_color|default:'#f00'}" 
                               style="height: 38px; width: 80px; padding: 2px;">
                        <input type="text" 
                               class="form-control" 
                               id="warning_color_text"
                               value="{$settings->site_warning_message_background_color|default:'#f00'}" 
                               placeholder="#f00" 
                               pattern="{literal}^#[0-9A-Fa-f]{6}${/literal}"
                               style="flex: 1;">
                    </div>
                    <small class="text-muted">Формат: #RRGGBB (например, #fff или #ffffff)</small>
                </div>
            </div>
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Цвет текста баннера</label>
                </div>
                <div class="col-12 col-md-6">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="color" 
                               class="form-control" 
                               name="site_warning_message_text_color" 
                               id="warning_text_color_picker"
                               value="{$settings->site_warning_message_text_color|default:'#fff'}" 
                               style="height: 38px; width: 80px; padding: 2px;">
                        <input type="text" 
                               class="form-control" 
                               id="warning_text_color_text"
                               value="{$settings->site_warning_message_text_color|default:'#fff'}" 
                               placeholder="#fff" 
                               pattern="{literal}^#[0-9A-Fa-f]{6}${/literal}"
                               style="flex: 1;">
                    </div>
                    <small class="text-muted">Формат: #RRGGBB (например, #fff или #ffffff)</small>
                </div>
            </div>
        </div>
    </div>
    {/if}
    {if $current_site_id === null || in_array('addresses_is_dadata', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Подсказки адресов на сайте
        </h3>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Плагин для подсказок</label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" name="addresses_is_dadata">
                        <option value="1" {if $settings->addresses_is_dadata}selected{/if}>Дадата</option>
                        <option value="0" {if !$settings->addresses_is_dadata}selected{/if}>Kladr.js</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
{/if}
