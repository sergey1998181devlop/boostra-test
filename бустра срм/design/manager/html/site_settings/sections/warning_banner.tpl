<hr class="mt-3 mb-4"/>
{if $current_site_id}
    <div class="row">
        <div class="col-12">
            <h3 class="box-title">Настройки баннера предупреждений</h3>

            {assign var="banner_config" value=$settings->site_warning_banner_config|json_decode:true}
            {if !$banner_config}
                {assign var="banner_config" value=[
                "enabled" => false,
                "show_on_main_page" => false,
                "message" => "",
                "style" => "info",
                "show_from" => null,
                "desktop" => ["background_color" => "#2196F3", "text_color" => "#fff", "font_size" => "16px", "font_weight" => "normal", "padding" => "12px 20px", "border_radius" => "4px"],
                "mobile" => ["background_color" => "#2196F3", "text_color" => "#fff", "font_size" => "14px", "font_weight" => "normal", "padding" => "10px 15px", "border_radius" => "4px"],
                "timeout" => ["enabled" => false, "minutes" => 1440],
                "position" => "top",
                "closeable" => false,
                "animation" => "slide"
                ]}
            {/if}

            <div class="form-group mb-3">
                <div class="row pb-2">
                    <div class="col-12 col-md-6">
                        <label>Активность баннера</label>
                    </div>
                    <div class="col-12 col-md-6">
                        <select class="form-control" name="banner_config[enabled]">
                            <option value="0" {if !$banner_config.enabled}selected{/if}>Выключено</option>
                            <option value="1" {if $banner_config.enabled}selected{/if}>Включено</option>
                        </select>
                    </div>
                </div>

                <div class="row pb-2">
                    <div class="col-12 col-md-6">
                        <label>Отображать на главной странице</label>
                    </div>
                    <div class="col-12 col-md-6">
                        <select class="form-control" name="banner_config[show_on_main_page]">
                            <option value="0" {if !$banner_config.show_on_main_page}selected{/if}>Нет</option>
                            <option value="1" {if $banner_config.show_on_main_page}selected{/if}>Да</option>
                        </select>
                    </div>
                </div>

                <div class="row pb-2">
                    <div class="col-12 col-md-6">
                        <label>Стиль баннера</label>
                    </div>
                    <div class="col-12 col-md-6">
                        <select class="form-control" name="banner_config[style]" id="banner_style">
                            <option value="info" {if $banner_config.style == 'info'}selected{/if}>Информация (синий)
                            </option>
                            <option value="warning" {if $banner_config.style == 'warning'}selected{/if}>Предупреждение
                                (оранжевый)
                            </option>
                            <option value="error" {if $banner_config.style == 'error'}selected{/if}>Ошибка (красный)
                            </option>
                            <option value="success" {if $banner_config.style == 'success'}selected{/if}>Успех
                                (зеленый)
                            </option>
                            <option value="custom" {if $banner_config.style == 'custom'}selected{/if}>Пользовательский
                            </option>
                        </select>
                    </div>
                </div>

                <div class="row pb-2">
                    <div class="col-12 col-md-6">
                        <label>Текст сообщения</label>
                    </div>
                    <div class="col-12 col-md-6">
                        <textarea class="form-control" name="banner_config[message]" id="banner_message" required
                                  rows="4">{$banner_config.message|escape}</textarea>
                    </div>
                </div>

                <div class="banner-custom-settings"
                     style="display: {if $banner_config.style == 'custom'}block{else}none{/if};">
                    <div class="row pb-2 mt-3">
                        <div class="col-12">
                            <h5>Настройки для Desktop</h5>
                        </div>
                    </div>
                    <div class="row pb-2">
                        <div class="col-12 col-md-6">
                            <label>Цвет фона</label>
                        </div>
                        <div class="col-12 col-md-6">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="color" class="form-control banner-desktop-bg-color"
                                       name="banner_config[desktop][background_color]"
                                       value="{$banner_config.desktop.background_color|default:'#2196F3'}"
                                       style="height: 38px; width: 80px; padding: 2px;">
                                <input type="text" class="form-control banner-desktop-bg-text"
                                       value="{$banner_config.desktop.background_color|default:'#2196F3'}"
                                       style="flex: 1;">
                            </div>
                        </div>
                    </div>
                    <div class="row pb-2">
                        <div class="col-12 col-md-6">
                            <label>Цвет текста</label>
                        </div>
                        <div class="col-12 col-md-6">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="color" class="form-control banner-desktop-text-color"
                                       name="banner_config[desktop][text_color]"
                                       value="{$banner_config.desktop.text_color|default:'#fff'}"
                                       style="height: 38px; width: 80px; padding: 2px;">
                                <input type="text" class="form-control banner-desktop-text-text"
                                       value="{$banner_config.desktop.text_color|default:'#fff'}"
                                       style="flex: 1;">
                            </div>
                        </div>
                    </div>
                    <div class="row pb-2">
                        <div class="col-12 col-md-6">
                            <label>Размер шрифта</label>
                        </div>
                        <div class="col-12 col-md-6">
                            <input type="text" class="form-control" name="banner_config[desktop][font_size]"
                                   value="{$banner_config.desktop.font_size|default:'16px'}" placeholder="16px">
                        </div>
                    </div>

                    <div class="row pb-2 mt-3">
                        <div class="col-12">
                            <h5>Настройки для Mobile</h5>
                        </div>
                    </div>
                    <div class="row pb-2">
                        <div class="col-12 col-md-6">
                            <label>Цвет фона</label>
                        </div>
                        <div class="col-12 col-md-6">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="color" class="form-control banner-mobile-bg-color"
                                       name="banner_config[mobile][background_color]"
                                       value="{$banner_config.mobile.background_color|default:'#2196F3'}"
                                       style="height: 38px; width: 80px; padding: 2px;">
                                <input type="text" class="form-control banner-mobile-bg-text"
                                       value="{$banner_config.mobile.background_color|default:'#2196F3'}"
                                       style="flex: 1;">
                            </div>
                        </div>
                    </div>
                    <div class="row pb-2">
                        <div class="col-12 col-md-6">
                            <label>Цвет текста</label>
                        </div>
                        <div class="col-12 col-md-6">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="color" class="form-control banner-mobile-text-color"
                                       name="banner_config[mobile][text_color]"
                                       value="{$banner_config.mobile.text_color|default:'#fff'}"
                                       style="height: 38px; width: 80px; padding: 2px;">
                                <input type="text" class="form-control banner-mobile-text-text"
                                       value="{$banner_config.mobile.text_color|default:'#fff'}"
                                       style="flex: 1;">
                            </div>
                        </div>
                    </div>
                    <div class="row pb-2">
                        <div class="col-12 col-md-6">
                            <label>Размер шрифта</label>
                        </div>
                        <div class="col-12 col-md-6">
                            <input type="text" class="form-control" name="banner_config[mobile][font_size]"
                                   value="{$banner_config.mobile.font_size|default:'14px'}" placeholder="14px">
                        </div>
                    </div>
                </div>
                <div class="row pb-2 mt-3">
                    <div class="col-12">
                        <h5>Настройки отложенного показа</h5>
                    </div>
                </div>
                <div class="row pb-2">
                    <div class="col-12 col-md-6">
                        <label>Отложенный показ</label>
                        <small class="text-muted d-block">Оставьте пустым для немедленного показа. Баннер будет показан с указанной даты и времени (по МСК)</small>
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="datetime-local" class="form-control" name="banner_config[show_from]"
                               value="{if $banner_config.show_from}{$banner_config.show_from|date_format:'%Y-%m-%dT%H:%M'}{/if}"
                               min="{$smarty.now|date_format:'%Y-%m-%dT%H:%M'}" max="2099-12-31T23:59">
                    </div>
                </div>
                <div class="row pb-2 mt-3">
                    <div class="col-12 col-md-6">
                        <label>Закрываемый баннер</label>
                        <small class="text-muted d-block">Пользователь сможет закрыть баннер</small>
                    </div>
                    <div class="col-12 col-md-6">
                        <select class="form-control" name="banner_config[closeable]" id="banner_closeable">
                            <option value="0" {if !$banner_config.closeable}selected{/if}>Нет</option>
                            <option value="1" {if $banner_config.closeable}selected{/if}>Да</option>
                        </select>
                    </div>
                </div>

                <div class="banner-timeout-settings" style="display: {if $banner_config.closeable}block{else}none{/if};">
                    <div class="row pb-2 mt-3">
                        <div class="col-12">
                            <h5>Настройки таймаута</h5>
                        </div>
                    </div>
                    <div class="row pb-2">
                        <div class="col-12 col-md-6">
                            <label>Включить таймаут</label>
                            <small class="text-muted d-block">После скрытия баннер не будет показываться указанное
                                время</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <select class="form-control" id="banner_timeout_enabled" name="banner_config[timeout][enabled]">
                                <option value="0" {if !$banner_config.timeout.enabled}selected{/if}>Выключено</option>
                                <option value="1" {if $banner_config.timeout.enabled}selected{/if}>Включено</option>
                            </select>
                        </div>
                    </div>
                    <div id="banner_timeout_minutes_row" style="display: {if $banner_config.timeout.enabled}block{else}none{/if};">
                        <div class="row pb-2">
                            <div class="col-12 col-md-6">
                                <label>Минут до повторного показа</label>
                            </div>
                            <div class="col-12 col-md-6">
                                <input type="number" class="form-control" name="banner_config[timeout][minutes]"
                                       value="{if $banner_config.timeout.minutes}{$banner_config.timeout.minutes|intval}{elseif $banner_config.timeout.hours}{$banner_config.timeout.hours * 60|intval}{else}1440{/if}" 
                                       min="1" max="10080" step="1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row pb-2">
                    <div class="col-12 col-md-6">
                        <label>Позиция баннера</label>
                    </div>
                    <div class="col-12 col-md-6">
                        <select class="form-control" name="banner_config[position]">
                            <option value="top" {if $banner_config.position == 'top'}selected{/if}>Сверху</option>
                            <option value="bottom" {if $banner_config.position == 'bottom'}selected{/if}>Снизу</option>
                            <option value="right" {if $banner_config.position == 'right'}selected{/if}>Справа (push)</option>
                            <option value="left" {if $banner_config.position == 'left'}selected{/if}>Слева (push)</option>
                        </select>
                    </div>
                </div>

                <div class="row pb-2">
                    <div class="col-12 col-md-6">
                        <label>Тип анимации</label>
                    </div>
                    <div class="col-12 col-md-6">
                        <select class="form-control" name="banner_config[animation]">
                            <option value="slide" {if $banner_config.animation == 'slide'}selected{/if}>Слайд</option>
                            <option value="fade" {if $banner_config.animation == 'fade'}selected{/if}>Плавное появление
                            </option>
                            <option value="none" {if $banner_config.animation == 'none'}selected{/if}>Без анимации
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr class="mt-3 mb-4"/>
{/if}
