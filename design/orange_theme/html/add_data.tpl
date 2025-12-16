{* Страница заказа *}

{$meta_title = "Дополнение данных анкеты" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
	<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>

    <script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.7.5" type="text/javascript"></script>

	<script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.62" type="text/javascript"></script>

	<script src="design/{$settings->theme}/js/personal_data.app.js?v=1.71" type="text/javascript"></script>


    <script type="text/javascript" src="/js/autocomplete/jquery.autocomplete-min.js"></script>
    <link rel="stylesheet" href="/js/autocomplete/styles.css" />

    <script src="design/{$settings->theme}/js/additional_data.app.js?v=1.70" type="text/javascript"></script>


	<script src="design/{$settings->theme}/js/files_data.app.js?v=1.54" type="text/javascript"></script>

{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Дополнение данных анкеты</h1>
				<h5>В вашей анкете не хватает некорых данных.</h5>
			</hgroup>

            {if $need_add_fields|count > 0 || $need_add_work|count > 0}
            <form method="post">
				<div id="steps">


					<fieldset id="personal_data" style="display: block;;">

                        <!-- Личные данные -->
                        {if in_array('lastname', $need_add_fields) || in_array('firstname', $need_add_fields) || in_array('patronymic', $need_add_fields)}
                        <div class="clearfix">
                            <span class="title">Личные данные</span>

                            {if in_array('lastname', $need_add_fields)}
                            <label>
    							<input type="text" name="lastname" value="{$user->lastname}" placeholder="" required=""/>
                                <span class="floating-label">Фамилия</span>
                            </label>
    						{/if}

                            {if in_array('firstname', $need_add_fields)}
                            <label>
    							<input type="text" name="firstname" value="{$user->firstname}" placeholder="" required=""/>
                                <span class="floating-label">Имя</span>
                            </label>
                            {/if}

                            {if in_array('patronymic', $need_add_fields)}
    						<label>
    							<input type="tel" name="patronymic" value="{$user->patronymic}" placeholder="" required=""/>
                                <span class="floating-label">Отчество</span>
    						</label>
                            {/if}

                        </div>
                        {/if}


                        <!-- Персональные данные -->
                        {if in_array('gender', $need_add_fields)}
                        <div class="clearfix">
                            <span class="title">Персональные данные</span>
                        </div>
                        {/if}

                        {if in_array('gender', $need_add_fields) || in_array('birth', $need_add_fields) || in_array('birth_place', $need_add_fields) || in_array('marital_status', $need_add_fields)}

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

                            {if in_array('birth', $need_add_fields)}
                            <label class="">
    							<input type="text" name="birth" value="{$user->birth_place}" placeholder="" required=""/>
                                <span class="floating-label">Дата рождения</span>
    						</label>
                            {/if}

                            {if in_array('birth_place', $need_add_fields)}
                            <label class="">
    							<input type="text" name="birth_place" class="js-uppercase" value="{$user->birth_place}" placeholder="" required=""/>
                                <span class="floating-label">Место рождения</span>
    						</label>
                            {/if}

                            {if in_array('marital_status', $need_add_fields)}
                            <label >
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
                            {/if}

                        </div>
                        {/if}


                        <!-- Паспортные данные -->
                        {if in_array('passport_serial', $need_add_fields) || in_array('passport_date', $need_add_fields) || in_array('subdivision_code', $need_add_fields) || in_array('passport_issued', $need_add_fields)}
                        <div class="clearfix">
                            <span class="title">Паспортые данные</span>

                            {if in_array('passport_serial', $need_add_fields)}
                            <label>
    							<input type="tel" name="passport_serial" value="{$user->passport_serial}" placeholder="" required=""/>
                                <span class="floating-label">Серия и номер паспорта</span>
                            </label>
    						{/if}

                            {if in_array('passport_date', $need_add_fields)}
                            <label>
    							<input type="tel" name="passport_date" value="{$user->passport_date}" placeholder="" required=""/>
                                <span class="floating-label">Дата выдачи</span>
                            </label>
                            {/if}

                            {if in_array('subdivision_code', $need_add_fields)}
    						<label>
    							<input type="tel" name="subdivision_code" value="{$user->subdivision_code}" placeholder="" required=""/>
                                <span class="floating-label">Код подразделения</span>
    						</label>
                            {/if}

    						{if in_array('passport_issued', $need_add_fields)}
    						<label class="big">
    							<input type="text" class="js-uppercase js-cirylic js-cirylic-plus" name="passport_issued" value="{$user->passport_issued}" placeholder="" required=""/>
                                <span class="floating-label">Кем выдан</span>
                            </label>
                            {/if}

                        </div>
                        {/if}


                        {if in_array('regaddress', $need_add_fields)}
						<div class="register">

                            <span class="title">Адрес прописки</span>

                            <label class="{if $error=='empty_Regregion'}error{/if}">
								<input type="text" name="Regregion" value="" placeholder="" required="" rel="region" aria-required="true"/>
                                <span class="floating-label">Область</span>
                                {if $error=='empty_Regregion'}<span class="error">Укажите область в которой Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Regcity}readonly{/if}">
								<input type="text" name="Regcity" value="" placeholder="" required="" rel="city" aria-required="true"/>
                                <span class="floating-label">Город</span>
                                {if $error=='empty_Regcity'}<span class="error">Укажите город в котором Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Regstreet}readonly{/if}">
								<input type="text" name="Regstreet" value="" placeholder="" rel="street" />
                                <span class="floating-label">Улица</span>
                                {if $error=='empty_Regstreet'}<span class="error">Укажите улицу на которой Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Reghousing}readonly{/if}">
								<input type="text" name="Reghousing" value="" placeholder="" required="" rel="house" aria-required="true"/>
                                <span class="floating-label">Дом</span>
                                {if $error=='empty_Reghousing'}<span class="error">Укажите номер дома в котором Вы прописаны</span>{/if}
							</label>

							<label class="{if $user->Regbuilding}readonly{/if} ">
								<input type="text" name="Regbuilding" value="" placeholder="" class="adding sup" rel="building" aria-required="true"/>
							    <span class="floating-label">Строение</span>
                            </label>

							<label class="{if $user->Regroom}readonly{/if} ">
								<input type="text" name="Regroom" value="" placeholder="" class="adding sup" rel="flat" aria-required="true"/>
							    <span class="floating-label">Квартира</span>
                            </label>

							<input type="hidden" name="Regindex" id="prop_zip" value="">

							<input type="hidden" name="Regregion_shorttype" id="regregion_shorttype" value="">
							<input type="hidden" name="Regcity_shorttype" id="regcity_shorttype" value="">
							<input type="hidden" name="Regstreet_shorttype" id="regstreet_shorttype" value="">

						</div>
                        {/if}

                        {if in_array('faktaddress', $need_add_fields)}
						<div class="living" id="living_block">

                            <span class="title">Адрес проживания</span>

                            <label class="{if $error=='empty_Faktregion'}error{/if}">
								<input type="text" name="Faktregion" value="" placeholder="" required="" rel="region"/>
                                <span class="floating-label">Область</span>
                                {if $error=='empty_Faktregion'}<span class="error">Укажите область фактического проживания</span>{/if}
							</label>

							<label class="{if $user->Faktcity}readonly{/if}">
								<input type="text" name="Faktcity" value="" placeholder="" required="" rel="city"/>
                                <span class="floating-label">Город</span>
                                {if $error=='empty_Faktcity'}<span class="error">Укажите город фактического проживания</span>{/if}
							</label>

                            <label class="{if $user->Faktstreet}readonly{/if}">
								<input type="text" name="Faktstreet" value="" placeholder="" rel="street"/>
                                <span class="floating-label">Улица</span>
                                {if $error=='empty_Faktstreet'}<span class="error">Укажите улицу фактического проживания</span>{/if}
							</label>

							<label class="{if $user->Fakthousing}readonly{/if}">
								<input type="text" name="Fakthousing" value="" placeholder="" required="" rel="house"/>
                                <span class="floating-label">Дом</span>
                                {if $error=='empty_Fakthousing'}<span class="error">Укажите номер дома фактического проживания</span>{/if}
							</label>

							<label class="{if $user->Faktbuilding}readonly{/if}">
								<input type="text" name="Faktbuilding" value="" placeholder="" class="adding sup" rel="building"/>
							    <span class="floating-label">Строение</span>
                            </label>

							<label class="{if $user->Faktroom}readonly{/if}">
								<input type="text" name="Faktroom" value="" placeholder="" class="adding sup" rel="flat"/>
							    <span class="floating-label">Квартира</span>
                            </label>

                            <input type="hidden" name="Faktindex" id="prog_zip" value="">

							<input type="hidden" name="Faktregion_shorttype" id="faktregion_shorttype" value="">
							<input type="hidden" name="Faktcity_shorttype" id="faktcity_shorttype" value="">
							<input type="hidden" name="Faktstreet_shorttype" id="faktstreet_shorttype" value="">

						</div>
                        {/if}


                        {if in_array('contactpersons', $need_add_fields)}
                        <div class="clearfix">

                            <span class="title">Контактные лица</span>

                            <label class="readonly {if $error=='empty_contact_person_name'}error{/if}">
                                <input type="text" class="js-cirylic" name="contact_person_name" value="" placeholder="" required="true"/>
                                <span class="floating-label">ФИО контакного лица</span>
                                <small class="err error">{if $error=='empty_contact_person_name'}Укажите ФИО контакного лица{/if}</small>
                            </label>

                            <label class="readonly {if $error=='empty_contact_person_phone'}error{/if}">
                                <input type="text" class="" name="contact_person_phone" value="" placeholder="" required="true"/>
                                <span class="floating-label">Тел. контакного лица</span>
                                <small class="error">{if $error=='empty_contact_person_phone'}Укажите номер телефона контакного лица{/if}</small>
                            </label>

                            <label class="{if $error=='empty_contact_person_relation'}error{/if}">
                                <div class="select">
                                    <select name="contact_person_relation">
                                        <option value="" >Выберите значение</option>
                                        <option value="мать/отец" >мать/отец</option>
                                        <option value="муж/жена" >муж/жена</option>
                                        <option value="сын/дочь" >сын/дочь</option>
                                        <option value="коллега" >коллега</option>
                                        <option value="друг/сосед" >друг/сосед</option>
                                        <option value="иной родственник" >иной родственник</option>
                                    </select>
                                </div>
                                <span class="floating-label">Кем приходится</span>
                            </label>
                        </div>
                        {/if}

					</fieldset>

					{if in_array('income_base', $need_add_work) || in_array('workaddress', $need_add_work) || in_array('workdata', $need_add_work)}
					<fieldset id="additional_data" style="display: block;;">

                        {if in_array('workdata', $need_add_work)}
                        <div class="clearfix">
                            <span class="title">Данные о работе</span>

                            {$other_work_scope_active = 1}
                            {if !$user->work_scope}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Гос. служба'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Торговля'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Производство'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Обслуживание'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Финансы'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Пенсионер'}{$other_work_scope_active = 0}{/if}

    						<label id="work_scope_select" {if $other_work_scope_active}style="display:none"{/if}>
    							<div class="select">
                                    <select name="work_scope" {if $other_work_scope_active}disabled="true"{/if}>
                                        <option value="Гос. служба" {if $user->work_scope == 'Гос. служба'}selected=""{/if}>Гос. служба</option>
                                        <option value="Торговля" {if $user->work_scope == 'Торговля'}selected=""{/if}>Торговля</option>
                                        <option value="Производство" {if $user->work_scope == 'Производство'}selected=""{/if}>Производство</option>
                                        <option value="Обслуживание" {if $user->work_scope == 'Обслуживание'}selected=""{/if}>Обслуживание</option>
                                        <option value="Энергетика" {if $user->work_scope == 'Энергетика'}selected=""{/if}>Энергетика</option>
                                        <option value="Финансы" {if $user->work_scope == 'Финансы'}selected=""{/if}>Финансы</option>
                                        <option value="Пенсионер" {if $user->work_scope == 'Пенсионер'}selected=""{/if}>Пенсионер</option>
                                        <option value="Иное" {if $user->work_scope == 'Иное'}selected=""{/if}>Иное</option>
                                    </select>
                                </div>
                                <span class="floating-label">Сфера деятельности</span>
                                {if $error=='empty_work_scope'}<span class="error">Укажите сферу деятельности</span>{/if}
    						</label>

    						<label id="work_scope_input" class="readonly {if $error=='empty_work_scope'}error{/if}" {if !$other_work_scope_active}style="display:none"{/if}>
                                <input type="text" name="work_scope" value="{$user->work_scope}" {if !$other_work_scope_active}disabled="true"{/if} placeholder="" />
                                <a href="javascript:void(0);" class="close_work_scope"></a>
                                <span class="floating-label">Сфера деятельности</span>
                                {if $error=='empty_work_scope'}<span class="error">Укажите сферу деятельности</span>{/if}
    						</label>

    						<label class="readonly js-pensioner-hidden {if $error=='empty_profession'}error{/if}" {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>
    							<input type="text" name="profession" value="{$user->profession}" placeholder="" />
                                <span class="floating-label">Должность</span>
                            </label>

                            <label class="js-pensioner-hidden " {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>
    							<input type="text" class="" name="work_phone" value="{$user->work_phone}" placeholder="" />
                                <span class="floating-label">Рабочий телефон</span>
                            </label>

                        </div>

                        <div class="clearfix">

    						<label class="half js-pensioner-hidden {if $error=='empty_workplace'}error{/if}" {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>
    							<input type="text" name="workplace" value="{$user->workplace}" placeholder="" />
                                <span class="floating-label">Название организации</span>
                                {if $error=='empty_workplace'}<span class="error">Укажите сокращенное наименование организации</span>{/if}
                            </label>

    						<label class="half js-pensioner-hidden {if $error=='empty_workdirector_name'}error{/if}" {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>
    							<input type="text" name="workdirector_name" value="{$user->workdirector_name}" placeholder="" />
                                <span class="floating-label">ФИО руководителя</span>
                                {if $error=='empty_workdirector_name'}<span class="error">Укажите ФИО руководителя</span>{/if}
                            </label>

                        </div>
                        {/if}


                        {if in_array('workaddress', $need_add_work)}
                        <div class="register js-pensioner-hidden" {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>

                            <span class="title">Адрес Организации</span>

                            <label class="">
								<input type="text" name="Workregion" value="" placeholder="" rel="region" aria-required="true"/>
                                <span class="floating-label">Область</span>
							</label>

							<label class="{if $user->Workcity}readonly{/if}">
								<input type="text" name="Workcity" value="" placeholder="" rel="city" aria-required="true"/>
                                <span class="floating-label">Город</span>
							</label>

							<label class="{if $user->Workstreet}readonly{/if}">
								<input type="text" name="Workstreet" value="" placeholder="" rel="street" />
                                <span class="floating-label">Улица</span>
							</label>

							<label class="{if $user->Workhousing}readonly{/if}">
								<input type="text" name="Workhousing" value="" placeholder="" rel="house" aria-required="true"/>
                                <span class="floating-label">Дом</span>
							</label>

							<label class="">
								<input type="text" name="Workbuilding" value="" placeholder="" class="adding sup" rel="building" aria-required="true"/>
							    <span class="floating-label">Строение</span>
                            </label>

							<label class="">
								<input type="text" name="Workroom" value="" placeholder="" class="adding sup" rel="flat" aria-required="true"/>
							    <span class="floating-label">Офис</span>
                            </label>

							<input type="hidden" name="Workindex" id="work_zip" value="">

							<input type="hidden" name="Workregion_shorttype" id="regregion_shorttype" value="">
							<input type="hidden" name="Workcity_shorttype" id="regcity_shorttype" value="">
							<input type="hidden" name="Workstreet_shorttype" id="regstreet_shorttype" value="">

						</div>
                        {/if}

                        {if in_array('income_base', $need_add_work)}
                        <div class="clearfix">

                            <span class="title">Доходы</span>

                            <label class="half {if $error=='empty_income_base'}error{/if}">
    							<input type="text" class="js-digits" name="income_base" value="{$user->income_base}" placeholder="" required=""/>
                                <span class="floating-label">Основной, руб</span>
                                <small class="error">{if $error=='empty_income_base'}Укажите основной доход{/if}</small>
                            </label>

                        </div>
                        {/if}

					</fieldset>
                    {/if}

					<div class="next">
                        <button class="button big" id="doit" type="submit" name="neworder">Далее</button>
					</div>

				</div>
			</form>

            {elseif $need_add_files|count > 0}

            <form id="" method="POST" enctype="multipart/form-data" >

                {if $error=='error_upload'}
                <div class="alert alert-danger">
                    При передаче файлов произошла ошибка, попробуйте повторить позже.
                </div>
                {/if}

                <div class="js-error-block payment-block error" style="display:none">
                    <div class="payment-block-error">
                        <p>Ошибка при передаче</p>
                        <a href="/" class="button big button-inverse cancel_payment">Закончить</a>
                    </div>
                </div>

                <div id="file_form">

                    {if $need_add_files['face1']}
                    <fieldset class="face1-file file-block">

                        <legend>Фото анфас</legend>

                        <div class="alert alert-danger " style="display:none"></div>

                        <div class="user_files">
                        </div>

                        <div class="file-field" {if $face1_file}style="display:none"{/if}>
                            <label class="file-label">
                                <div class="file-label-image">
                                    <img src="design/{$settings->theme|escape}/img/face1.png" />
                                </div>
                                <span>Добавить файл</span>
                                <input type="file" name="face1" accept="image/jpeg,image/png" data-type="face1" />
                            </label>
                        </div>

                    </fieldset>
                    {/if}

                    {if $need_add_files['face2']}
                    <fieldset class="face2-file file-block">

                        <legend>Фото в профиль</legend>

                        <div class="alert alert-danger" style="display:none"></div>

                        <div class="user_files">
                        </div>

                        <div class="file-field" {if $face2_file}style="display:none"{/if}>
                            <label class="file-label">
                                <div class="file-label-image">
                                    <img src="design/{$settings->theme|escape}/img/face2.png" />
                                </div>
                                <span>Добавить файл</span>
                                <input type="file" name="face2" accept="image/jpeg,image/png" data-type="face2" />
                            </label>
                        </div>

                    </fieldset>
                    {/if}

                    {if $need_add_files['passport1']}
                    <fieldset class="passport1-file file-block">

                        <legend>Первая страница паспорта</legend>

                        <div class="alert alert-danger " style="display:none"></div>

                        <div class="user_files">
                        </div>

                        <div class="file-field" {if $passport1_file}style="display:none"{/if}>
                            <label class="file-label">
                                <div class="file-label-image">
                                    <img src="design/{$settings->theme|escape}/img/passport1.png" />
                                </div>
                                <span>Добавить файл</span>
                                <input type="file" name="passport1" accept="image/jpeg,image/png" data-type="passport1" />
                            </label>
                        </div>

                    </fieldset>
                    {/if}

                    {if $need_add_files['passport2']}
                    <fieldset class="passport2-file file-block">

                        <legend>Cтраница с регистрацией</legend>

                        <div class="alert alert-danger " style="display:none"></div>

                        <div class="user_files">
                        </div>

                        <div class="file-field" {if $passport2_file}style="display:none"{/if}>
                            <label class="file-label">
                                <div class="file-label-image">
                                    <img src="design/{$settings->theme|escape}/img/passport2.png" />
                                </div>
                                <span>Добавить файл</span>
                                <input type="file" name="passport2" accept="image/jpeg,image/png" data-type="passport2" />
                            </label>
                        </div>

                    </fieldset>
                    {/if}

                    {if $need_add_files['passport4']}
                    <fieldset class="passport4-file file-block">

                        <legend>Фото карты</legend>

                        <div class="alert alert-danger " style="display:none"></div>

                        <div class="user_files">
                        </div>

                        <div class="file-field" {if $passport4_file}style="display:none"{/if}>
                            <label class="file-label">
                                <div class="file-label-image">
                                    <img src="design/{$settings->theme|escape}/img/card_logo.png" />
                        <i style="font-size: 9px;">Приложите фото вашей именной карты так, чтобы были отчетливо видны последние 4 цифры её номера и фамилия владельца</i>
                                </div>
                                <span>Добавить файл</span>
                                <input type="file" name="passport4" accept="image/jpeg,image/png" data-type="passport4" />
                            </label>
                        </div>
                    </fieldset>
                    {/if}

                    {if $need_add_files['selfi']}
                    <fieldset class="selfi-file file-block">

                        <legend>Селфи</legend>

                        <div class="alert alert-danger " style="display:none"></div>

                        <div class="user_files">
                        </div>

                        <div class="file-field" {if $selfi_file}style="display:none"{/if}>
                            <label class="file-label">
                                <div class="file-label-image">
                                    <img src="design/{$settings->theme|escape}/img/selfi.png" />

                                </div>
                                <span>Добавить файл</span>
                                <input type="file" name="selfi" accept="image/jpeg,image/png" data-type="selfi" />
                            </label>
                        </div>
                    </fieldset>
                    {/if}

                    <p class="form-help">
                        * Максимальный размер файла: {($max_file_size/1024/1024)|round} Мб
                    </p>
                    <br />
                    <div class="clearfix next">
                        <input type="submit" name="confirm" class="button big" value="СОхранить" />
                    </div>

                </div>
            </form>
            {/if}

		</div>
	</div>
</section>
