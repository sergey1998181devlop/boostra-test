{if $current_site_id === null || in_array('ping3', $site_setting_names)}
    <hr class="mt-3 mb-4"/>
    <h3 class="box-title">Настройки Ping3</h3>
    <div class="form-group mb-3">
        <div class="row">
            <div class="col-5">
                <h5 class="box-title">Настройка повторных клиентов</h5>
                <small>UTM метки (через запятую)</small>
            </div>
            <div class="col">
                <div class="col">
                            <textarea
                                    id="partner_api_repeat_client_utm_sources"
                                    name="partner_api_repeat_client_utm_sources"
                                    class="form-control"
                                    rows="4"
                            >{if $settings->partner_api_repeat_client_utm_sources}{implode(',', $settings->partner_api_repeat_client_utm_sources)}{/if}</textarea>
            </div>
        </div>
    </div>
{/if}
