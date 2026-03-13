{if $current_site_id === null || in_array('registration_disabled_captcha', $site_setting_names) || in_array('flow_after_personal_data', $site_setting_names) || in_array('t_bank_button_registration', $site_setting_names) || in_array('esia_button_registration', $site_setting_names) || in_array('auto_step_no_need_for_underwriter', $site_setting_names)}
<div class="row">
    <div class="col-12 col-md-10">
        <h3 class="box-title">Настройки пользователей</h3>

        {if $current_site_id === null || in_array('registration_disabled_captcha', $site_setting_names)}
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="registration_disabled_captcha">Выключить капчу на регистрации</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="registration_disabled_captcha"
                            name="registration_disabled_captcha">
                        <option value="0" {if !$settings->registration_disabled_captcha}selected{/if}>Нет</option>
                        <option value="1" {if $settings->registration_disabled_captcha}selected{/if}>Да</option>
                    </select>
                </div>
            </div>
        </div>
        {/if}

        {if $current_site_id === null || in_array('flow_after_personal_data', $site_setting_names)}
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6">
                    <h5 class="box-title">Флоу телефон после ФИО и паспорта НК</h5>
                </div>
                <div class="col">
                    <div class="row">
                        <div class="col-5">
                            <label for="flow_after_personal_data[status]">Статус флоу</label>
                        </div>
                        <div class="col">
                            <select class="form-control" id="flow_after_personal_data[status]" name="flow_after_personal_data[status]">
                                <option value="0" {if !$settings->flow_after_personal_data.status}selected{/if}>Нет</option>
                                <option value="1" {if $settings->flow_after_personal_data.status}selected{/if}>Да</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-5">
                            <label for="flow_after_personal_data[utm_sources]">UTM метки (через запятую)</label>
                        </div>
                        <div class="col">
                            <textarea
                                    id="flow_after_personal_data[utm_sources]"
                                    name="flow_after_personal_data[utm_sources]"
                                    class="form-control"
                                    rows="4"
                            >{if $settings->flow_after_personal_data.utm_sources}{implode(',', $settings->flow_after_personal_data.utm_sources)}{/if}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {/if}

        {if $current_site_id === null || in_array('t_bank_button_registration', $site_setting_names)}
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6">
                    <h5 class="box-title">Включить кнопку T-Bank на регистрации</h5>
                </div>
                <div class="col">
                    <div class="row">
                        <div class="col-5">
                            <label for="t_bank_button_registration[status]">Статус</label>
                        </div>
                        <div class="col">
                            <select class="form-control" id="t_bank_button_registration[status]" name="t_bank_button_registration[status]">
                                <option value="0" {if !$settings->t_bank_button_registration.status}selected{/if}>Нет</option>
                                <option value="1" {if $settings->t_bank_button_registration.status}selected{/if}>Да</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-5">
                            <label for="t_bank_button_registration[utm_sources]">UTM метки (через запятую)</label>
                        </div>
                        <div class="col">
                            <textarea
                                    id="t_bank_button_registration[utm_sources]"
                                    name="t_bank_button_registration[utm_sources]"
                                    class="form-control"
                                    rows="4"
                            >{if $settings->t_bank_button_registration.utm_sources}{implode(',', $settings->t_bank_button_registration.utm_sources)}{/if}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {/if}

        {if $current_site_id === null || in_array('esia_button_registration', $site_setting_names)}
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6">
                    <h5 class="box-title">Включить кнопку ГосУслуг на регистрации</h5>
                </div>
                <div class="col">
                    <div class="row">
                        <div class="col-5">
                            <label for="esia_button_registration[status]">Статус</label>
                        </div>
                        <div class="col">
                            <select class="form-control" id="esia_button_registration[status]" name="esia_button_registration[status]">
                                <option value="0" {if !$settings->esia_button_registration.status}selected{/if}>Нет</option>
                                <option value="1" {if $settings->esia_button_registration.status}selected{/if}>Да</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-5">
                            <label for="esia_button_registration[utm_sources]">UTM метки (через запятую)</label>
                        </div>
                        <div class="col">
                            <textarea
                                    id="esia_button_registration[utm_sources]"
                                    name="esia_button_registration[utm_sources]"
                                    class="form-control"
                                    rows="4"
                            >{if $settings->esia_button_registration.utm_sources}{implode(',', $settings->esia_button_registration.utm_sources)}{/if}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {/if}

        {if $current_site_id === null || in_array('il_nk_loan_edit_amount', $site_setting_names)}
            <div class="form-group mb-3">
                <div class="row">
                    <div class="col-6">
                        <h5 class="box-title">Включить бегунок изменения суммы для ИЛ в ПДЛ займе НК</h5>
                    </div>
                    <div class="col">
                        <div class="row">
                            <div class="col-5">
                                <label for="il_nk_loan_edit_amount[status]">Статус</label>
                            </div>
                            <div class="col">
                                <select class="form-control" id="il_nk_loan_edit_amount[status]" name="il_nk_loan_edit_amount[status]">
                                    <option value="0" {if !$settings->il_nk_loan_edit_amount.status}selected{/if}>Нет</option>
                                    <option value="1" {if $settings->il_nk_loan_edit_amount.status}selected{/if}>Да</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-5">
                                <label for="il_nk_loan_edit_amount[utm_sources]">UTM метки (через запятую)</label>
                            </div>
                            <div class="col">
                            <textarea
                                    id="il_nk_loan_edit_amount[utm_sources]"
                                    name="il_nk_loan_edit_amount[utm_sources]"
                                    class="form-control"
                                    rows="4"
                            >{if $settings->il_nk_loan_edit_amount.utm_sources}{implode(',', $settings->il_nk_loan_edit_amount.utm_sources)}{/if}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        {if $current_site_id === null || in_array('no_need_for_underwriter_card_step_disabled', $site_setting_names)}
            <div class="form-group mb-3">
                <div class="row">
                    <div class="col-6">
                        <h5 class="box-title">Пропуск шага привязки карты</h5>
                        <small>(при наличии признака no_need_for_underwriter)</small>
                    </div>
                    <div class="col">
                        <div class="row">
                            <div class="col-5">
                                <label for="no_need_for_underwriter_card_step_disabled[status]">Статус</label>
                            </div>
                            <div class="col">
                                <select class="form-control" id="no_need_for_underwriter_card_step_disabled[status]" name="no_need_for_underwriter_card_step_disabled[status]">
                                    <option value="0" {if !$settings->no_need_for_underwriter_card_step_disabled.status}selected{/if}>Нет</option>
                                    <option value="1" {if $settings->no_need_for_underwriter_card_step_disabled.status}selected{/if}>Да</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-5">
                                <label for="no_need_for_underwriter_card_step_disabled[utm_sources]">UTM метки (через запятую)</label>
                            </div>
                            <div class="col">
                            <textarea
                                    id="no_need_for_underwriter_card_step_disabled[utm_sources]"
                                    name="no_need_for_underwriter_card_step_disabled[utm_sources]"
                                    class="form-control"
                                    rows="4"
                            >{if $settings->no_need_for_underwriter_card_step_disabled.utm_sources}{implode(',', $settings->no_need_for_underwriter_card_step_disabled.utm_sources)}{/if}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        {if $current_site_id === null || in_array('auto_step_no_need_for_underwriter', $site_setting_names)}
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6">
                    <h5 class="box-title">Автоматическое прохождение шагов при скористе</h5>
                    <small>(no_need_for_underwriter)</small>
                </div>
                <div class="col">
                    <div class="row">
                        <div class="col-5">
                            <label for="auto_step_no_need_for_underwriter[files_added]">Шаг фото</label>
                        </div>
                        <div class="col">
                            <select class="form-control" id="auto_step_no_need_for_underwriter[files_added]" name="auto_step_no_need_for_underwriter[files_added]">
                                <option value="0" {if !$settings->auto_step_no_need_for_underwriter.files_added}selected{/if}>Нет</option>
                                <option value="1" {if $settings->auto_step_no_need_for_underwriter.files_added}selected{/if}>Да</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-5">
                            <label for="auto_step_no_need_for_underwriter[additional_data_added]">Шаг работа</label>
                        </div>
                        <div class="col">
                            <select disabled class="form-control" id="auto_step_no_need_for_underwriter[additional_data_added]" name="auto_step_no_need_for_underwriter[additional_data_added]">
                                <option value="0" {if !$settings->auto_step_no_need_for_underwriter.additional_data_added}selected{/if}>Нет</option>
                                <option value="1" {if $settings->auto_step_no_need_for_underwriter.additional_data_added}selected{/if}>Да</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-5">
                            <label for="auto_step_no_need_for_underwriter[utm_sources]">UTM метки (через запятую)</label>
                        </div>
                        <div class="col">
                            <textarea
                                    id="auto_step_no_need_for_underwriter[utm_sources]"
                                    name="auto_step_no_need_for_underwriter[utm_sources]"
                                    class="form-control"
                                    rows="4"
                            >{if $settings->auto_step_no_need_for_underwriter.utm_sources}{implode(',', $settings->auto_step_no_need_for_underwriter.utm_sources)}{/if}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {/if}

    </div>
</div>
<hr class="mt-3 mb-4" />
{/if}
