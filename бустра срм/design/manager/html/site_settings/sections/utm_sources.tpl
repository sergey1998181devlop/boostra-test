{assign var="has_non_organic" value=($current_site_id === null || in_array('non_organic_utm_sources', $site_setting_names))}
{assign var="has_disable_bank" value=($current_site_id === null || in_array('disable_bank_selection_utm_sources', $site_setting_names))}
{assign var="has_returning" value=($current_site_id === null || in_array('returning_users_flow_utm_sources', $site_setting_names))}
{assign var="has_mark418" value=($current_site_id === null || in_array('mark_418_test_leadgids', $site_setting_names))}

{if $has_non_organic}
<div class="row">
    <div class="col-12 col-md-10">
        <h3 class="box-title">Неорганические метки</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col">
                    <div class="row mt-2">
                        <div class="col-5">
                            <label for="non_organic_utm_sources">UTM метки (через запятую)</label>
                        </div>
                        <div class="col">
                            <textarea
                                    id="non_organic_utm_sources"
                                    name="non_organic_utm_sources"
                                    class="form-control"
                                    rows="4"
                            >{if $settings->non_organic_utm_sources}{implode(',', $settings->non_organic_utm_sources)}{/if}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}

{if $has_disable_bank}
<div class="row">
    <div class="col-12 col-md-10">
        <h3 class="box-title">UTM метки для отключения выбора банка (id банка получаем при привязке карты)</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col">
                    <div class="row mt-2">
                        <div class="col-5">
                            <label for="disable_bank_selection_utm_sources">UTM метки (через запятую)</label>
                        </div>
                        <div class="col">
                            <textarea
                                    id="disable_bank_selection_utm_sources"
                                    name="disable_bank_selection_utm_sources"
                                    class="form-control"
                                    rows="4"
                                    placeholder="yandex_direct, google_ads, facebook_ads"
                            >{if $settings->disable_bank_selection_utm_sources}{implode(',', $settings->disable_bank_selection_utm_sources)}{/if}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}

{if $has_returning}
<div class="row">
    <div class="col-12 col-md-10">
        <h3 class="box-title">UTM метки для возвращающихся НК после длительного отсутствия для продолжения регистрации</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col">
                    <div class="row mt-2">
                        <div class="col-5">
                            <label for="returning_users_flow_utm_sources">UTM метки (через запятую)</label>
                        </div>
                        <div class="col">
                            <textarea
                                    id="returning_users_flow_utm_sources"
                                    name="returning_users_flow_utm_sources"
                                    class="form-control"
                                    rows="4"
                                    placeholder="yandex_returning, google_returning, facebook_returning"
                            >{if $settings->returning_users_flow_utm_sources}{implode(',', $settings->returning_users_flow_utm_sources)}{/if}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}

{if $has_mark418}
<div class="row">
    <div class="col-12 col-md-10">
        <h3 class="box-title">Тестовые UTM метки для MARK-418</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col">
                    <div class="row mt-2">
                        <div class="col-5">
                            <label for="mark_418_test_leadgids">UTM метки (через запятую)</label>
                        </div>
                        <div class="col">
                            <textarea
                                    id="mark_418_test_leadgids"
                                    name="mark_418_test_leadgids"
                                    class="form-control"
                                    rows="4"
                            >{if $settings->mark_418_test_leadgids}{implode(',', $settings->mark_418_test_leadgids)}{/if}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr class="mt-3 mb-4" />
{/if}
