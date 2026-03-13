{if $current_site_id === null || in_array('banner_url_a', $site_setting_names) || in_array('banner_clicks_a', $site_setting_names) || in_array('banner_url_b', $site_setting_names) || in_array('banner_clicks_b', $site_setting_names) || in_array('zero_discount_enabled', $site_setting_names)}
    <div class="row">
        {if $current_site_id === null || in_array('banner_url_a', $site_setting_names)}
            <div class="col-12 col-md-10">
                <h3 class="box-title">Ссылки баннера и процент кликов</h3>
                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="row pb-2">
                                <div class="col-12 col-md-6">
                                    <label for="banner_url_a">Первая ссылка</label>
                                </div>
                                <div class="col-12 col-md-6">
                                    <input id="banner_url_a" class="form-control" name="banner_url_a"
                                           value="{$settings->banner_url_a}"/>
                                </div>
                            </div>
                            <div class="row pb-2">
                                <div class="col-12 col-md-6">
                                    <label for="banner_clicks_a">Клики по первой ссылке, %</label>
                                </div>
                                <div class="col-12 col-md-6">
                                    <input id="banner_clicks_a" type="number" min="0" max="100" class="form-control"
                                           name="banner_clicks_a" value="{$settings->banner_clicks_a|default:100}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="row pb-2">
                                <div class="col-12 col-md-6">
                                    <label for="banner_url_b">Вторая ссылка</label>
                                </div>
                                <div class="col-12 col-md-6">
                                    <input id="banner_url_b" class="form-control" name="banner_url_b"
                                           value="{$settings->banner_url_b}"/>
                                </div>
                            </div>
                            <div class="row pb-2">
                                <div class="col-12 col-md-6">
                                    <label for="banner_clicks_b">Клики по второй ссылке, %</label>
                                </div>
                                <div class="col-12 col-md-6">
                                    <input id="banner_clicks_b" type="number" min="0" max="100" class="form-control"
                                           name="banner_clicks_b" value="{$settings->banner_clicks_b|default:0}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        {if $current_site_id === null || in_array('zero_discount_enabled', $site_setting_names)}
            <div class="col-12 col-md-10">
                <h3 class="box-title">Выводить надпись: 0% первые 5 дней</h3>
                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-6 col-md-6">
                            <label for="zero_discount_enabled"></label>
                        </div>
                        <div class="col-6 col-md-6">
                            <select class="form-control" id="zero_discount_enabled" name="zero_discount_enabled">
                                <option value="0" {if !$settings->zero_discount_enabled}selected{/if}>Нет</option>
                                <option value="1" {if $settings->zero_discount_enabled}selected{/if}>Да</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    </div>
    <hr class="mt-3 mb-4"/>
{/if}
