{if $current_site_id === null || in_array('recurring_payment_so', $site_setting_names) || in_array('vitamed_bankpay_disable', $site_setting_names) || in_array('hide_order_information', $site_setting_names) || in_array('whitelist_dop', $site_setting_names) || in_array('additional_work_scope', $site_setting_names) || in_array('display_policy_days', $site_setting_names) || in_array('auto_disable_additional_services', $site_setting_names) || in_array('payment_methods_btn', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('recurring_payment_so', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Списание ЗО на оплате через рекуррентный платеж</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="recurring_zo">Списание ЗО на оплате через рекуррентный платеж</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="recurring_payment_so"
                            name="recurring_payment_so">
                        <option value="0" {if !$settings->recurring_payment_so}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->recurring_payment_so}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('vitamed_bankpay_disable', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Отключить Витамед если оплачивал через расчетный счет</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="vitamed_bankpay_disable">Не добавлять услугу Витамед клиентам с оплатой через расчетный счет</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="vitamed_bankpay_disable"
                            name="vitamed_bankpay_disable">
                        <option value="0" {if !$settings->vitamed_bankpay_disable}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->vitamed_bankpay_disable}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('hide_order_information', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Скрытие информации в ЛК </h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="hide_order_information"> Скрытие информации(сумма займа) в ЛК</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="hide_order_information"
                            name="hide_order_information">
                        <option value="0" {if !$settings->hide_order_information}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->hide_order_information}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('whitelist_dop', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Отключение всех ДОПов для пользователей из белого списка</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="whitelist_dop">Отключение всех ДОПов для пользователей из белого списка</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="whitelist_dop"
                            name="whitelist_dop">
                        <option value="0" {if !$settings->whitelist_dop}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->whitelist_dop}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('additional_work_scope', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Доп статусы в анкете</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="additional_work_scope">Показывать самозанятый и пенсионер</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="additional_work_scope"
                            name="additional_work_scope">
                        <option value="0" {if !$settings->additional_work_scope}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->additional_work_scope}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>

<div class="row">
    {if $current_site_id === null || in_array('display_policy_days', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Настройка отображения полисов</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="additional_work_scope">Кол-во дней через которое отображаются полисы</label>
                </div>
                <div class="col-6 col-md-6">
                    <input type="number"
                           min="0"
                           step="1"
                           class="form-control"
                           name="display_policy_days"
                           value="{if $settings->display_policy_days >= 0}{$settings->display_policy_days}{else}3{/if}">
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
<div class="row">
    {if $current_site_id === null || in_array('auto_disable_additional_services', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Автоматически отключать все активные допы</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="auto_disable_additional_services">Отключать при создании тикета</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="auto_disable_additional_services" name="auto_disable_additional_services">
                        <option value="0" {if !$settings->auto_disable_additional_services}selected{/if}>Выключено</option>
                        <option value="1" {if $settings->auto_disable_additional_services}selected{/if}>Включено</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('payment_methods_btn', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Кнопка Возможные способы оплаты</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="payment_methods_btn">Показ кнопки о возможных способах оплаты в ЛК</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="payment_methods_btn"
                            name="payment_methods_btn">
                        <option value="0" {if !$settings->payment_methods_btn}selected{/if}>Выключен</option>
                        <option value="1" {if $settings->payment_methods_btn}selected{/if}>Включен</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
{/if}
