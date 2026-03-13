{if $current_site_id === null || in_array('url_findzen', $site_setting_names) || in_array('header_email_block', $site_setting_names) || in_array('header_email', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('url_findzen', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Ссылки на онлайн сервисы</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="url_findzen">ФИНДЗЕН</label>
                </div>
                <div class="col-6 col-md-6">
                    <input class="form-control" name="url_findzen" value="{$settings->url_findzen}" />
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('header_email_block', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Блок с почтой
        </h3>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Отображение почты в заголовке сайта</label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" id="header_email_block" name="header_email_block">
                        <option value="0" {if !$settings->header_email_block}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->header_email_block}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label>Адрес почты в заголовке сайта</label>
                </div>
                <div class="col-12 col-md-6">
                    <input class="form-control" name="header_email" value="{$settings->header_email}" type="text" />
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
{/if}
