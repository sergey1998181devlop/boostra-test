{if $current_site_id === null || in_array('addcard_rejected_enabled', $site_setting_names) || in_array('send_complaint', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('addcard_rejected_enabled', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Привязка карт
        </h3>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Разрешить привязку при отсутствии средств</label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" name="addcard_rejected_enabled">
                        <option value="0" {if !$settings->addcard_rejected_enabled}selected{/if}>Выключено</option>
                        <option value="1" {if $settings->addcard_rejected_enabled}selected{/if}>Активно</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
    {if $current_site_id === null || in_array('send_complaint', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Отправка жалобы
        </h3>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label for="send_complaint">Включить кнопку отправки жалобы</label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" id="send_complaint" name="send_complaint">
                        <option value="0" {if !$settings->send_complaint}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->send_complaint}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
{/if}
