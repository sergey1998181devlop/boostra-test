{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
	<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>

    <script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.7.5" type="text/javascript"></script>

	<script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.64" type="text/javascript"></script>
    <script type="text/javascript" src="/js/autocomplete/jquery.autocomplete-min.js"></script>

	<script src="design/{$settings->theme}/js/personal_data.app.js?v=1.73" type="text/javascript"></script>
{/capture}

{literal}
    <style>
        .autocomplete-suggestions {
            position: absolute;
            display: block;
            margin: 0;
            padding: 0;
            border: 1px solid #c4c4c4;
            background-color: #fff;
            z-index: 9999;
            overflow-x: hidden;
            overflow-y: auto;
            min-width: 200px;
            max-height: 420px;
            color: #313131
        }

        .autocomplete-suggestions > div {
            margin: 0;
            padding: 8px 10px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis
        }

        .autocomplete-suggestions > div + div {
            border-top: 1px solid #ededed
        }

        .autocomplete-suggestions > div:hover {
            background-color: #f2f2f2;
            cursor: pointer
        }

        .autocomplete-suggestions > div.active {
            background-color: #e9e9e9
        }

        .autocomplete-suggestions > div strong {
            color: #038ebd
        }
    </style>
{/literal}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Паспортные данные</h1>
				<h5>Заполните паспортные данные.</h5>
			</hgroup>

            {include file='display_stages.tpl' current=3 percent=57 total_step=4}
            
			<form method="post" class="js-send-feedback" data-target="etap-pasport" id="personal_data"> 
				<div id="steps">
					
					{if $error == 'allready_exists'}
                    <div class="alert alert-danger">
                        Клиент с такими персональными данными уже зарегистрирован. 
                        <br />
                        Просим связаться с нами по номеру 8 800 333 30 73.
                    </div>

                    {else}
                    
					<fieldset style="display: block;;">
                        
                        <input type="hidden" value="personal_data" name="stage" />
                        
                        <div class="clearfix">
                            <div class="red" style="color:red">
                                Пожалуйста, внимательно заполните анкету точно как написано в паспорте. Ошибка при заполнении может стать причиной отказа
                            </div>
                            <label class="{if $error=='empty_passportCode'}error{/if}">
    							<input type="tel" name="passportCode" value="{$user->passport_serial}" placeholder="" required=""/>
                                <span class="floating-label">Серия и номер паспорта</span>
                                {if $error=='empty_passportCode'}<span class="error">Укажите серия и номер паспорта</span>{/if}
                            </label>
    						<label class="{if $error=='empty_passportDate'}error{/if}">
    							<input type="tel" name="passportDate" value="{$user->passport_date}" placeholder="" required=""/>
                                <span class="floating-label">Дата выдачи</span>
                                {if $error=='empty_passportDate'}<span class="error">Укажите дата выдачи паспорта</span>{/if}
                            </label>
    						<label class="{if $error=='empty_subdivisionCode'}error{/if}">
    							<input type="tel" name="subdivisionCode" value="{$user->subdivision_code}" placeholder="" required=""/>
                                <span class="floating-label">Код подразделения</span>
                                {if $error=='empty_subdivisionCode'}<span class="error">Укажите код подразделения</span>{/if}
    						</label>
    						<label class="big {if $error=='empty_passportWho'}error{/if}">
    							<input type="text" class="js-uppercase js-cirylic js-cirylic-plus" name="passportWho" value="{$user->passport_issued}" placeholder="" required=""/>
                                <span class="floating-label">Кем выдан</span>
                                <small class="error">{if $error=='empty_passportWho'}Укажите кем выдан паспорт{/if}</small>
                            </label>
                            {*}
                            <label class="{if $error=='empty_email'}error{/if}">
    							<input class="js-input-email" type="text" name="email" id="email" placeholder="" data-email="{$email}" value="{if $is_developer}admin@test.ru{else}{$email}{/if}" >
    							<small class="err error" id="err-email">{if $error=='empty_email'}Укажите электронную почту{/if}</small>
    							<span class="floating-label">Электронная почта</span>
                            </label>
                            {*}
                        </div>
                        
                        <div class="clearfix">
                            
                            <label class="text-left {if $error=='empty_gender'}error{/if}">
                                <div class="radio">
                                    <input type="radio" name="gender" value="female" {if $user->gender=='female'}checked="true"{/if} />
                                    <span></span>
                                </div>
                                Женщина
                            </label>
                            
                            <label class="text-left {if $error=='empty_gender'}error{/if}">
                                <div class="radio">
                                    <input type="radio" name="gender" value="male" {if $user->gender=='male'}checked="true"{/if} />
                                    <span></span>
                                </div>
                                Мужчина
                            </label>
                            
                        </div>
                        
                        <div class="clearfix">
                            
                            
                            <label class="medium {if $error=='empty_birth_place'}error{/if}">
    							<input type="text" name="birth_place" class="js-uppercase" value="{$user->birth_place}" placeholder="" required=""/>
                                <span class="floating-label">Место рождения</span>
                                {if $error=='empty_birth_place'}<span class="error">Укажите место рождения</span>{/if}
    						</label>    

                            <label class="{if $error=='empty_marital'}error{/if}">
    							<div class="select">
                                    <select name="marital_status">
                                        <option value="" {if !$user->marital_status}selected="true"{/if}>Выберите значение</option>
                                        <option value="женат/замужем" {if $user->marital_status == 'женат/замужем'}selected=""{/if}>женат/замужем</option>
                                        <option value="разведен/разведена" {if $user->marital_status == 'разведен/разведена'}selected=""{/if}>разведен/разведена</option>
                                        <option value="гражданский брак" {if $user->marital_status == 'гражданский брак'}selected=""{/if}>гражданский брак</option>
                                        <option value="вдовец/вдова" {if $user->marital_status == 'вдовец/вдова'}selected=""{/if}>вдовец/вдова</option>
                                        <option value="не замужем/холост" {if $user->marital_status == 'не замужем/холост'}selected=""{/if}>не замужем/холост</option>
                                    </select>
                                </div>
                                <span class="floating-label">Семейное положение</span>
    						</label>

                        </div>
						
                        <div class="next">
							<button class="button big" id="doit" type="submit" name="neworder">Далее</button>	
						</div>
					</fieldset>
                    {/if}
				</div>
			</form>
		</div>
	</div>
</section>

{literal}
    <script>
        $(document).ready(function () {
            $('[name="subdivisionCode"]').autocomplete({
                serviceUrl: 'ajax/dadata.php?action=fms_unit',
                onSelect: function(item){
                    $('[name="passportWho"]').val(item.data.name);
                    $('[name="subdivisionCode"]').val(item.data.code).removeClass('error');
                    $('#subdivisionCode-error').remove();
                },
                formatResult: function(item, short_value){
                    let _block = '',
                        c = "(" + short_value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&") + ")",
                        item_value = item.value.replace(RegExp(c, "gi"), "<strong>$1</strong>")

                    _block += '<span>'+item_value+'</span>';
                    return _block;
                }
            });
        });
    </script>
{/literal}
