{if $current_site_id === null || in_array('notice_contact_me_enabled', $site_setting_names) || in_array('notice_contact_me_enabled_for', $site_setting_names) || in_array('faq_highlight_enabled', $site_setting_names) || in_array('faq_highlight_delay', $site_setting_names)}
<div class="row">
    {if $current_site_id === null || in_array('notice_contact_me_enabled', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Нотис "Свяжитесь со мной" в ЛК</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="notice_contact_me_enabled">Включить нотис "Свяжитесь со мной" в ЛК</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="notice_contact_me_enabled"
                            name="notice_contact_me_enabled">
                        <option value="0" {if !$settings->notice_contact_me_enabled}selected{/if}>Нет</option>
                        <option value="1" {if $settings->notice_contact_me_enabled}selected{/if}>Да</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="notice_contact_me_enabled_for">Показывать нотис только для:</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="notice_contact_me_enabled_for" name="notice_contact_me_enabled_for">
                        <option value="1" {if $settings->notice_contact_me_enabled_for == 1}selected{/if}>ПК</option>
                        <option value="2" {if $settings->notice_contact_me_enabled_for == 2}selected{/if}>НК</option>
                        <option value="3" {if $settings->notice_contact_me_enabled_for == 3}selected{/if}>Всех</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


<div class="row">
    {if $current_site_id === null || in_array('faq_highlight_enabled', $site_setting_names)}
    <div class="col-12 col-md-10">
        <h3 class="box-title">Подсветка раздела "Вопросы и ответы" в ЛК</h3>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="faq_highlight_enabled">Включить подсветку</label>
                </div>
                <div class="col-6 col-md-6">
                    <select class="form-control" id="faq_highlight_enabled"
                            name="faq_highlight_enabled">
                        <option value="0" {if !$settings->faq_highlight_enabled}selected{/if}>Нет
                        </option>
                        <option value="1" {if $settings->faq_highlight_enabled}selected{/if}>Да
                        </option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-6 col-md-6">
                    <label for="faq_highlight_delay">Время нахождения на странице (в минутах) до
                        подсветки</label>
                </div>
                <div class="col-6 col-md-6">
                    <input type="number"
                           id="faq_highlight_delay"
                           min="0"
                           max="60"
                           step="1"
                           class="form-control"
                           name="faq_highlight_delay"
                           value="{$settings->faq_highlight_delay}">
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
<hr class="mt-3 mb-4" />
{/if}
