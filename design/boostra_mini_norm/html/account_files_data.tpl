{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
	<script src="design/{$settings->theme}/js/files_data.app.js?v=1.70" type="text/javascript"></script>
{/capture}
<style>
    .mini-stages {
        text-align: center;
    }
</style>
<section id="worksheet" class="worksheet_files-data">
	<div>
		<div class="box box_files">

            {include file='display_stages.tpl' current=5 percent=85 total_step=5}
                <hgroup>
                    <h3 style="color: #008000FF;display: none;">Карта успешно привязана.</h3>
                    <h1 class="files_data__title">Идентификация</h1>
    				<h5 class="files_data__subtitle">Остался последний шаг! Прикрепите фотографии.</h5>
                </hgroup>
            
                <form id="files_form" method="POST" enctype="multipart/form-data" >
                    
                <input type="hidden" name="stage" value="add_files" />
                
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

                    {include "ab_banner_registration.tpl"}

                    <p class="pasport1__title">Добавьте разворот главной страницы паспорта (2-3)</p>

                    <fieldset class="passport1-file file-block file-block-reg">
                        
                        {* <legend>Разворот главной страницы паспорта (2-3 стр.)</legend> *}
    
                        <div class="alert alert-danger " style="display:none"></div>
                        
                        <div class="user_files">
                            {if $passport1_file}                                                
                            <label class="file-label">
                                <div class="file-label-image">
                                    <img src="{$passport1_file->name|resize:100:100}" />
                                </div>
                                <span class="js-remove-file" data-id="{$passport1_file->id}"
                                      {if !empty($hide_delete_passport_photo_button)}style="visibility: hidden"{/if}>Удалить</span>
                                <input type="hidden" id="passport1" name="user_files[]" value="{$passport1_file->id}" />
                            </label>
                            {/if}
                        </div>
                        
                        <div class="file-field" {if $passport1_file}style="display:none"{/if}>
                            <div class="file-label">
                                <label for="user_file_passport1" class="file-label-image">
                                    <div class="user_file__img-wrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                            <circle cx="30" cy="30" r="30" fill="#038AEE"/>
                                            <path d="M29.9999 30.7826L35.6572 36.4386L33.7705 38.3253L31.3332 35.888V43.3346H28.6665V35.8853L26.2292 38.3253L24.3425 36.4386L29.9999 30.7826ZM29.9999 16.668C32.2892 16.6681 34.4986 17.5095 36.2081 19.0321C37.9176 20.5548 39.008 22.6526 39.2719 24.9266C40.9309 25.3792 42.3782 26.4003 43.3607 27.8117C44.3432 29.223 44.7985 30.9348 44.647 32.6477C44.4954 34.3607 43.7467 35.966 42.5317 37.1829C41.3167 38.3998 39.7126 39.1511 37.9999 39.3053V36.62C38.614 36.5331 39.2047 36.3247 39.7374 36.0069C40.2701 35.6891 40.7341 35.2683 41.1022 34.7691C41.4704 34.2699 41.7353 33.7023 41.8816 33.0996C42.0278 32.4968 42.0524 31.8709 41.954 31.2585C41.8555 30.6461 41.6359 30.0594 41.3081 29.5329C40.9803 29.0063 40.5508 28.5504 40.0447 28.1917C39.5386 27.8331 38.9661 27.5789 38.3607 27.4441C37.7552 27.3093 37.129 27.2966 36.5185 27.4066C36.7273 26.4345 36.7161 25.4279 36.4855 24.4607C36.2549 23.4935 35.8109 22.5901 35.186 21.8167C34.5611 21.0433 33.7711 20.4194 32.8738 19.9909C31.9766 19.5623 30.9949 19.3399 30.0005 19.3399C29.0062 19.3399 28.0245 19.5623 27.1272 19.9909C26.23 20.4194 25.44 21.0433 24.8151 21.8167C24.1901 22.5901 23.7461 23.4935 23.5156 24.4607C23.285 25.4279 23.2737 26.4345 23.4825 27.4066C22.2661 27.1782 21.0087 27.4423 19.987 28.141C18.9653 28.8396 18.263 29.9155 18.0345 31.132C17.8061 32.3484 18.0702 33.6058 18.7689 34.6275C19.4675 35.6492 20.5434 36.3515 21.7599 36.58L21.9999 36.62V39.3053C20.2871 39.1511 18.6831 38.3998 17.468 37.1829C16.253 35.966 15.5043 34.3607 15.3528 32.6477C15.2012 30.9348 15.6565 29.223 16.639 27.8117C17.6215 26.4003 19.0688 25.3792 20.7279 24.9266C20.9915 22.6525 22.0817 20.5545 23.7913 19.0318C25.5009 17.5091 27.7105 16.6678 29.9999 16.668Z" fill="white"/>
                                            <script xmlns=""/>
                                        </svg>
                                        <p class="files-form__label-par">Загрузить файл</p>
                                    </div>
                                </label>

                                {* <label onclick="sendMetric('reachGoal', 'get_user_photo_3');"  class="get_mobile_photo photo_btn not-visible-sm" for="user_file_passport1">
                                    Сделать фото
                                </label> *}

                                {* <label onclick="sendMetric('reachGoal', 'download_user_photo_3');"  class="photo_btn" for="user_file_passport1">Загрузить фото</label> *}
                                <input type="file" id="user_file_passport1" name="passport1" accept="image/*" data-type="passport1" />
                            </div>
                        </div>
                    </fieldset>

                    <div id="other_files" >
                    {foreach $passport_files as $key => $user_file}
                        <p class="pasport1__title">Дополнительный файл</p>
                        <fieldset class="user-file file-block file-block-reg">

                            {* <legend>Дополнительный файл</legend> *}

                            <div class="alert alert-danger " style="display:none"></div>

                            <div class="user_files">
                                {if $user_file}
                                <label class="file-label">
                                    <div class="file-label-image">
                                        <img src="{$user_file->name|resize:100:100}" />
                                    </div>
                                    <span class="js-remove-file" data-id="{$user_file->id}">Удалить</span>
                                    <input type="hidden" name="user_files[]" value="{$user_file->id}" />
                                </label>
                                {/if}
                            </div>

                            <div class="file-field" {if $user_file}style="display:none"{/if}>
                                <div class="file-label">
                                    <label for="user_file_user_file_{$key}" class="file-label-image">
                                        <div class="user_file__img-wrapper">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                                <circle cx="30" cy="30" r="30" fill="#038AEE"/>
                                                <path d="M29.9999 30.7826L35.6572 36.4386L33.7705 38.3253L31.3332 35.888V43.3346H28.6665V35.8853L26.2292 38.3253L24.3425 36.4386L29.9999 30.7826ZM29.9999 16.668C32.2892 16.6681 34.4986 17.5095 36.2081 19.0321C37.9176 20.5548 39.008 22.6526 39.2719 24.9266C40.9309 25.3792 42.3782 26.4003 43.3607 27.8117C44.3432 29.223 44.7985 30.9348 44.647 32.6477C44.4954 34.3607 43.7467 35.966 42.5317 37.1829C41.3167 38.3998 39.7126 39.1511 37.9999 39.3053V36.62C38.614 36.5331 39.2047 36.3247 39.7374 36.0069C40.2701 35.6891 40.7341 35.2683 41.1022 34.7691C41.4704 34.2699 41.7353 33.7023 41.8816 33.0996C42.0278 32.4968 42.0524 31.8709 41.954 31.2585C41.8555 30.6461 41.6359 30.0594 41.3081 29.5329C40.9803 29.0063 40.5508 28.5504 40.0447 28.1917C39.5386 27.8331 38.9661 27.5789 38.3607 27.4441C37.7552 27.3093 37.129 27.2966 36.5185 27.4066C36.7273 26.4345 36.7161 25.4279 36.4855 24.4607C36.2549 23.4935 35.8109 22.5901 35.186 21.8167C34.5611 21.0433 33.7711 20.4194 32.8738 19.9909C31.9766 19.5623 30.9949 19.3399 30.0005 19.3399C29.0062 19.3399 28.0245 19.5623 27.1272 19.9909C26.23 20.4194 25.44 21.0433 24.8151 21.8167C24.1901 22.5901 23.7461 23.4935 23.5156 24.4607C23.285 25.4279 23.2737 26.4345 23.4825 27.4066C22.2661 27.1782 21.0087 27.4423 19.987 28.141C18.9653 28.8396 18.263 29.9155 18.0345 31.132C17.8061 32.3484 18.0702 33.6058 18.7689 34.6275C19.4675 35.6492 20.5434 36.3515 21.7599 36.58L21.9999 36.62V39.3053C20.2871 39.1511 18.6831 38.3998 17.468 37.1829C16.253 35.966 15.5043 34.3607 15.3528 32.6477C15.2012 30.9348 15.6565 29.223 16.639 27.8117C17.6215 26.4003 19.0688 25.3792 20.7279 24.9266C20.9915 22.6525 22.0817 20.5545 23.7913 19.0318C25.5009 17.5091 27.7105 16.6678 29.9999 16.668Z" fill="white"/>
                                                <script xmlns=""/>
                                            </svg>
                                            <p class="files-form__label-par">Загрузить файл</p>
                                        </div>
                                    </label>

                                    {* <label onclick="sendMetric('reachGoal', 'get_user_photo_7');" for="user_file_user_file_{$key}"  class="get_mobile_photo photo_btn not-visible-sm">
                                        Сделать фото
                                    </label>

                                    <label onclick="sendMetric('reachGoal', 'download_user_photo_7');"  class="photo_btn" for="user_file_user_file_{$key}">Загрузить фото</label> *}
                                    <input type="file" id="user_file_user_file_{$key}" name="user_file" accept="image/jpeg,image/png" data-type="passport" />
                                </div>
                            </div>
                        </fieldset>
                    {/foreach}
                    <div id="new_other_file">
                        <p class="pasport1__title">Дополнительный файл</p>
                        <fieldset {* id="new_other_file" *} class="user-file file-block file-block-reg">
                            {* <legend>Дополнительный файл</legend> *}
        
                            <div class="alert alert-danger " style="display:none"></div>
                            
                            <div class="user_files">
                                
                            </div>
                            
                            <div class="file-field">
                                <div class="file-label">
                                    <label for="user_file_user_file" class="file-label-image">
                                        <div class="user_file__img-wrapper">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                                <circle cx="30" cy="30" r="30" fill="#038AEE"/>
                                                <path d="M29.9999 30.7826L35.6572 36.4386L33.7705 38.3253L31.3332 35.888V43.3346H28.6665V35.8853L26.2292 38.3253L24.3425 36.4386L29.9999 30.7826ZM29.9999 16.668C32.2892 16.6681 34.4986 17.5095 36.2081 19.0321C37.9176 20.5548 39.008 22.6526 39.2719 24.9266C40.9309 25.3792 42.3782 26.4003 43.3607 27.8117C44.3432 29.223 44.7985 30.9348 44.647 32.6477C44.4954 34.3607 43.7467 35.966 42.5317 37.1829C41.3167 38.3998 39.7126 39.1511 37.9999 39.3053V36.62C38.614 36.5331 39.2047 36.3247 39.7374 36.0069C40.2701 35.6891 40.7341 35.2683 41.1022 34.7691C41.4704 34.2699 41.7353 33.7023 41.8816 33.0996C42.0278 32.4968 42.0524 31.8709 41.954 31.2585C41.8555 30.6461 41.6359 30.0594 41.3081 29.5329C40.9803 29.0063 40.5508 28.5504 40.0447 28.1917C39.5386 27.8331 38.9661 27.5789 38.3607 27.4441C37.7552 27.3093 37.129 27.2966 36.5185 27.4066C36.7273 26.4345 36.7161 25.4279 36.4855 24.4607C36.2549 23.4935 35.8109 22.5901 35.186 21.8167C34.5611 21.0433 33.7711 20.4194 32.8738 19.9909C31.9766 19.5623 30.9949 19.3399 30.0005 19.3399C29.0062 19.3399 28.0245 19.5623 27.1272 19.9909C26.23 20.4194 25.44 21.0433 24.8151 21.8167C24.1901 22.5901 23.7461 23.4935 23.5156 24.4607C23.285 25.4279 23.2737 26.4345 23.4825 27.4066C22.2661 27.1782 21.0087 27.4423 19.987 28.141C18.9653 28.8396 18.263 29.9155 18.0345 31.132C17.8061 32.3484 18.0702 33.6058 18.7689 34.6275C19.4675 35.6492 20.5434 36.3515 21.7599 36.58L21.9999 36.62V39.3053C20.2871 39.1511 18.6831 38.3998 17.468 37.1829C16.253 35.966 15.5043 34.3607 15.3528 32.6477C15.2012 30.9348 15.6565 29.223 16.639 27.8117C17.6215 26.4003 19.0688 25.3792 20.7279 24.9266C20.9915 22.6525 22.0817 20.5545 23.7913 19.0318C25.5009 17.5091 27.7105 16.6678 29.9999 16.668Z" fill="white"/>
                                                <script xmlns=""/>
                                            </svg>
                                            <p class="files-form__label-par">Загрузить файл</p>
                                        </div>
                                    </label>

                                    {* <label onclick="sendMetric('reachGoal', 'get_user_photo_7');" for="user_file_user_file"  class="get_mobile_photo photo_btn not-visible-sm">
                                        Сделать фото
                                    </label>

                                    <label onclick="sendMetric('reachGoal', 'download_user_photo_7');"  class="photo_btn" for="user_file_user_file">Загрузить фото</label> *}
                                    <input type="file" id="user_file_user_file" name="user_file" accept="image/jpeg,image/png" data-type="passport" />
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    </div>
                    
                    <style>
                        @media (max-width: 768px) {
                            .mobile-green-bg {
                                background-color: #93cd52!important;
                            }
                        }
                    </style>
                    <div class="clearfix">
                        <input type="button" id="add_file" class="button button-inverse small" value="Добавить еще файл" />
                    </div>
                   <!-- <p
                        class="form-help mobile-green-bg"
                        style="font-weight: bold; text-align: left;"
                    >
                        Сделайте качественные фото, <span style="color: red;">и вероятность одобрения повысится!</span>
                        <ul
                            class="mobile-green-bg"
                            style="font-weight: bold; text-align: left;"
                        >
                            <li>располагайте документы так, чтобы они полностью помещались на фотографии;</li>
                            <li>текст должен быть читаемым и полностью виден;</li>
                            <li>исключите блики на фото.</li>
                            <li style="color: red;">Заём не выдаётся на Яндекс кошелек, Золотую корону, КИВИ и иные платежные системы.</li>
                            <li style="color: red;">Фото карты должны соответствовать прикрепленной на предыдущем этапе карты к платежной системе.</li>
                        </ul>
                    </p> -->
                    <p class="form-help">
                        * Максимальный размер файла: {($max_file_size/1024/1024)|round} Мб
                    </p>
                    <br />
                    <div class="clearfix next files_clearfix-buttons">
                        {if $is_developer}
                        <a class="button big button-inverse" id="" href="account?step=accept" >Назад</a>
                        {/if}
                        <input type="submit" name="confirm" class="button big files__add-file-btn" {if !$have_new_file}style="display:none"{/if} value="Далее" />
                    </div>
                    
                    
                </div>
            </form>
					
            
		</div>
	</div>
</section>

<div style="display:none">
    <div id="camera">
        <a href="javascript:void(0);" onclick="$.magnificPopup.close();" class="close">&#9421;</a>
        <div>
            <video id="video">Включите камеру и дайте разрешения для камеры повторно</video>
            <p class="text-red"></p>
        </div>
        <div class="camera_footer">
            <canvas id="canvas"></canvas>
            <div>
                <img src="" alt="Превью" id="photo" />
            </div>
            <button id="save_photo">Сохранить</button>
            <button id="get_photo">Сделать фото</button>
        </div>
    </div>
</div>


{* проверяем статус заявки через и аякс и если сменился перезагружаем страницу *}
{if $user_order && !$user_order->scorista_sms_sent}
<script type="text/javascript">
    $(function(){
        var _interval = setInterval(function(){
            $.ajax({
                url: 'ajax/check_1c_scorista.php',
                data: {
                    number: "{$user_order->id_1c}"
                },
                success: function(resp){
                    if (!!resp.sent)
                    {
                        _interval.clearInterval();
                    }
                }
            })
        }, 30000);
    })
</script>
{/if}

<script type="text/javascript">
    $(document).ready(function () {
        sendMetric('reachGoal', 'open_page_photo');
    });
</script>