{*
 * Секция: Пролонгация и организации для списания
 *}
{if $current_site_id === null || in_array('enable_prolongation_checkbox', $site_setting_names) || in_array('fake_try_prolongation_checkbox', $site_setting_names) || in_array('b2p_dop_organization', $site_setting_names) || in_array('tinkoff_dop_organization', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('enable_prolongation_checkbox', $site_setting_names) || in_array('fake_try_prolongation_checkbox', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Отмена страховки при пролонгации
        </h3>
        <div class="form-group mb-3">
            <div class="row pb-2">
                <div class="col-12 col-md-6">
                    <label><small>Галочка по дефолту стоит на доппродукте пролонгации у <strong class="text-primary">НК</strong>. <br />Клиент может снять.</small></label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" name="enable_prolongation_checkbox[nk]">
                        <option value="1" {if $settings->enable_prolongation_checkbox['nk']}selected{/if}>Списание НЕ происходит при снятой галочке</option>
                        <option value="0" {if !$settings->enable_prolongation_checkbox['nk']}selected{/if}>Списание происходит при снятой галочке</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <label><small>Галочка по дефолту стоит на доппродукте пролонгации у <strong class="text-success">ПК</strong>. <br />Клиент может снять.</small></label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" name="enable_prolongation_checkbox[pk]">
                        <option value="1" {if $settings->enable_prolongation_checkbox['pk']}selected{/if}>Списание НЕ происходит при снятой галочке</option>
                        <option value="0" {if !$settings->enable_prolongation_checkbox['pk']}selected{/if}>Списание происходит при снятой галочке</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <label><small>Галочка снимается с 5 раза. </small></label>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control" name="fake_try_prolongation_checkbox">
                        <option value="5" {if $settings->fake_try_prolongation_checkbox}selected{/if}>Да</option>
                        <option value="0" {if !$settings->fake_try_prolongation_checkbox}selected{/if}>Нет</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}

    {if $current_site_id === null || in_array('b2p_dop_organization', $site_setting_names) || in_array('tinkoff_dop_organization', $site_setting_names)}
    <div class="col-12 col-md-6">
        <h3 class="box-title">
            Списание допуслуги при выдаче
        </h3>
        <div class="row pt-2">
            <div class="col-12 col-md-6">
                <label>Организация для списания при выдаче через <strong>Best2pay</strong></label>
            </div>
            <div class="col-12 col-md-6">
                <select class="form-control" name="b2p_dop_organization">
                    <option value="AL" {if $settings->b2p_dop_organization == 'AL'}selected{/if}>Алфавит</option>
                    <option value="Boostra" {if $settings->b2p_dop_organization == 'Boostra'}selected{/if}>Бустра</option>
                </select>
            </div>
        </div>
        <div class="row pt-2">
            <div class="col-12 col-md-6">
                <label>Организация для списания при выдаче через <strong>Tinkoff</strong></label>
            </div>
            <div class="col-12 col-md-6">
                <select class="form-control" name="tinkoff_dop_organization">
                    <option value="AL" {if $settings->tinkoff_dop_organization == 'AL'}selected{/if}>Алфавит</option>
                    <option value="Boostra" {if $settings->tinkoff_dop_organization == 'Boostra'}selected{/if}>Бустра</option>
                </select>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
{/if}
