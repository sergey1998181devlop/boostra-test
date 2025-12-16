<div class="sms-header">
    <div>
        <h3><b>Введите код из СМС</b></h3>
    </div>
    <div class="sms_code_wrapper">
        <div>
            <div>
                <input name="sms_code" value="" type="text" />
                <a href="#" id="sms-repeat" style="display: none;">Отправить код повторно</a>
            </div>
            <span id="sms-timer"></span>
        </div>
    </div>
</div>
<div class="sms-content">
    <label for="contract_main">
        <input id="contract_main" type="checkbox" name="contract_main" value="1" />
        <div class="checkbox"></div>
        <span><a href="{$config->root_url}/{$config->docs_files_dir}cd_dogovor.pdf" target="_blank">Договор</a> оказание услуг с ООО "Алфавит"</span>
    </label>
    <label for="personal_distribution">
        <input id="personal_distribution" type="checkbox" name="personal_distribution" value="1" />
        <div class="checkbox"></div>
        <span><a href="{$config->root_url}/{$config->docs_files_dir}cd_soglasie_na_rasprostranenie_peredachu_personalnyh_danyh.pdf" target="_blank">Согласие на распространение (передачу) персональных данных</a></span>
    </label>
    <label for="personal_processing">
        <input id="personal_processing" type="checkbox" name="personal_processing" value="1" />
        <div class="checkbox"></div>
        <span><a href="{$config->root_url}/{$config->docs_files_dir}cd_soglasie_na_obrabotku_personalnyh_dannyh.pdf" target="_blank">Согласие на обработку персональных данных</a></span>
    </label>
</div>
<div class="sms-footer">
    <button class="orange-btn" disabled id="access_sms">Подтвердить</button>
</div>
