{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user/upload" scope=parent}

{$body_class = "gray" scope=parent}
 
{$add_order_css_js = true scope=parent}

{assign var="access_modified_file" value="{empty(count($user->loan_history)) && !in_array($last_order['1c_status'], ['3.Одобрено', '5.Выдан'])}"}
{capture_array key="footer_page_scripts"}
    <script type="module">
        import * as pdfjsLib from '/js/pdf.mjs';
        
        pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdf.worker.mjs';
        
        window.pdfjsLib = pdfjsLib;
    </script>
{/capture_array}
{literal}
<style>
    #img_modal {
        width: 1200px;
        max-width: 90%;
    }
    #img_modal img {
        max-width: 100%;
        height: auto;
    }
    #img_modal .close {
        text-align: right;
    }
    #img_wrapper {
        width: 100%;
        text-align: center;
    }

    div:has(.pasport1__title) {
        text-align: start;
    }

    .pasport1__title {
    }

    .fieldset-passport__wrapper,
    .fieldset-card__wrapper {
        box-shadow: 0px 4px 20px 0px #02113B1A;
        border-radius: 32px;
        padding: clamp(22px, 14.5px + 2.222vw, 50px);
        /* max-width: 42%; */
        display: inline-block;
        width: calc(50% - 16px);
        min-width: 280px;
        box-sizing: border-box;
    }

    .fieldset-other__wrapper {
        box-shadow: 0px 4px 20px 0px #02113B1A;
        border-radius: 32px;
        padding: clamp(22px, 14.5px + 2.222vw, 50px);
        margin: 32px 0 0;
    }

    .fieldsets_flex {
        display: flex;
        gap: 32px;
        align-items: stretch;
        flex-wrap: wrap;
    }

    #files_form fieldset.file-block {
        width: 100%;
    }

    #passport1_list, #passport-other_list, #passport4_list {
        margin-left:auto;
        margin-right:auto;
        background-color: #038AEE;
        transition: background .35s;
        color: #FFF;
        padding: 0.4rem 3rem;
        font-size: 1.04rem;
        border-radius: 232px;
    }

    #passport1_list:hover {
        background: #0278d3;
        text-decoration: none;
    }

    .file-label:has(#passport1_list) {
        display: flex;
    }

    @media (max-width: 720px) {
        .fieldset-passport__wrapper,
        .fieldset-card__wrapper {
            width: 100%;
        }
    }
</style>
<script>

function UploadApp()
{
    let app = this;
    const access_modified_file = "{/literal}{$access_modified_file|escape:'javascript'}{literal}";
    app.lastUploadError = undefined;
    app.init = function(){
        $(document).on('change', '[type=file]', function(){
            app.upload(this, false);
        });

        const $label = $('.file-label-with-svg');
        const $userFiles = $('#files_form .passport-files .user_files');


        if ($userFiles.children().length === 0) {
            $label.show();
        }
        
        $(document).on('click', '.remove-file', function(e){
            e.preventDefault();

            if (1 || confirm('Вы хотите удалить файл? Операцию не возможно будет отменить.'))
            {
                app.remove($(this));
            }
            return false;
        });

        $(document).on('click', '.user_files img', function (e) {
            let img = document.createElement('img');
            img.src = $(this).data('original');
            $("#img_wrapper").html(img);

            $.magnificPopup.open({
                items: {
                    src: '#img_modal'
                },
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
        });
    };
    
    app.remove = function($this){
        
        var file_id = $this.data('id');
        var $fileblock = $this.closest('.file-block');

        /* $.ajax({
            url: 'ajax/upload.php',
            data: {
                id: file_id,
                action: 'remove'
            },
            type: 'POST',
            dataType: 'json',
            beforeLoad: function(){

            },
            success: function(resp){
                if (!!resp.error)
                {
                    var error_text = '';
                    if (resp.error == 'max_file_size')
                        error_text = 'Превышен максимально допустимый размер файла.';
                    else
                        error_text = resp.error;
                        
                    $fileblock.find('.alert').html(error_text).fadeIn();
                }
                else
                {
                    $fileblock.find('.alert').fadeOut();
                    
                    $this.closest('.user_file').fadeOut();
                    
                    $fileblock.find('.file-field').fadeIn();
                }
            }
        }); */
        
        $.ajax({
            url: 'ajax/upload.php',
            data: {
                id: file_id,
                action: 'remove'
            },
            type: 'POST',
            dataType: 'json',
            beforeLoad: function(){

            },
            success: function(resp){
                if (!!resp.error)
                {
                    var error_text = '';
                    if (resp.error == 'max_file_size')
                        error_text = 'Превышен максимально допустимый размер файла.';
                    else
                        error_text = resp.error;
                        
                    $fileblock.find('.alert').html(error_text).fadeIn();
                }
                else
                {
                    $fileblock.find('.alert').fadeOut();
                    
                    $this.closest('.user_file').fadeOut();
                    
                    $fileblock.find('.file-field').fadeIn();
                }
                }
            }).done(function () {
            if ($('#files_form .passport-files .user_files').children().length === 0) {
                $('.file-label-with-svg').show();
            }
        });
        
    };
    
    app.upload = async function(input) {
        $('#files_form').addClass('loading');

        let self = $(input),
            fileBlock = self.closest('.file-block'),
            _type = self.data('type'),
            file = input.files[0],
            form_data = new FormData();
        fileBlock = fileBlock.length !== 0 ? fileBlock : $('.passport-files');

        form_data.append('file', input.files[0])
        form_data.append('type', _type);
        form_data.append('action', 'add');
        if (!file) return;
        let pageCount;
        let pdfError;
        if(input.files[0].type === "application/pdf") {
            try {
                const arrayBuffer = await file.arrayBuffer();
                const pdf = await window.pdfjsLib.getDocument({ data: new Uint8Array(arrayBuffer) }).promise;
                pageCount = pdf.numPages;
            } catch(error) {
                if(error?.name === "PasswordException"){
                    fileBlock.find('.alert').html('Не удалось прочитать PDF или файл защищён паролем').fadeIn();
                    app.lastUploadError = error.name;
                }
            }
        }
        let pdfPages = pageCount === 2 ? [1, 2] : [1];
        form_data.append('pdf_pages', JSON.stringify(pdfPages));
        if(pageCount > 2) {
            fileBlock.find('.alert').html('PDF содержит больше 2 страниц').fadeIn();
        } else {
        $.ajax({
            url        : 'ajax/upload.php',
            data       : form_data,
            type       : 'POST',
            dataType   : 'json',
            processData: false,
            contentType: false,
            beforeLoad : function () {
                fileBlock.addClass('loading');
            },
            success    : function (resp) {
                input.value = ''
                if (!!resp.error) {
                    var error_text = '';
                    if (resp.error == 'max_file_size')
                        error_text = 'Превышен максимально допустимый размер файла.';
                    else if (resp.error == 'error_uploading')
                        error_text = 'Файл не удалось загрузить, попробуйте еще.';
                    else if (resp.error == 'extension')
                        error_text = 'Файл не удалось загрузить, Недопустимое расширение файла. Допускается загрузка форматов: ' + resp.allowed_extensions.join(', ');
                    else
                        error_text = resp.error;

                    fileBlock.find('.alert').html(error_text).fadeIn();
                } else {
                    fileBlock.find('.alert').fadeOut();

                    var _preview = '';

                    _preview += '<div class="user_file">';
                    _preview += '<div class="image-status uploaded" title="Файл загружен, отправьте его на проверку. Не отправленные файлы, через 5 дней после загрузки, будут удалены"><span></span></div>';
                    _preview += '<img src="' + resp.filename + '" data-original="{/literal}{$config->root_url}/{$config->original_images_dir}{literal}' + resp.name + '" />';

                    if (resp.type === 'passport' && access_modified_file) {
                        _preview += '<a href="javascript:void(0);" class="remove-file" data-id="'+resp.id+'">&times;</a>';
                    }

                    _preview += '</div>';

                    fileBlock.find('.user_files').append(_preview);

                    if (_type != 'passport')
                        fileBlock.find('.file-field').fadeOut();

                    self.closest('form').find('[name=confirm]').fadeIn();

                    if (self.hasClass('js-replace')) {
                        var _id = self.data('replace');
                        $.ajax({
                            url       : 'ajax/upload.php',
                            data      : {
                                id    : _id,
                                action: 'remove'
                            },
                            type      : 'POST',
                            dataType  : 'json',
                            beforeLoad: function () {

                            },
                            success   : function (resp) {
                                if (resp.error) {
                                    alert(resp.error);
                                    return;
                                }
                                fileBlock.find('.alert').fadeOut();

                                $('#file_' + _id).fadeOut();
                                fileBlock.find('.js-replace-block').remove();
                            }
                        });
                    }
                }
            }
        }).done(function () {
            if ($('#files_form .passport-files .user_files').children().length !== 0) {
                        $('.file-label-with-svg').hide();
                    }
            $('#files_form').removeClass('loading');
        });
    }
    app.lastUploadError = undefined;
    }
    
    ;(function(){
        app.init();
    })();
};
new UploadApp();

</script>
{/literal}

<section id="private">
	<div>
		<div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">

    		{include file='user_nav.tpl' current='upload'}

			<div class="content">
				<div class="panel">
                    
                    <form id="files_form" method="POST" enctype="multipart/form-data" >
                        
                        {if $error=='error_upload'}
                        <div class="alert alert-danger">
                            При передаче файлов произошла ошибка, попробуйте повторить позже.
                        </div>
                        {/if}
                        <div class="fieldsets_flex">
                        <div class="fieldset-passport__wrapper">
                        <p class="pasport1__title">Разворот главной страницы паспорта (2-3 стр.)</p>
                        <fieldset class="passport1-file file-block" {* style="background:url('design/{$settings->theme|escape}/img/passport1.png') right center no-repeat;background-size:contain;" *}>
                            
                            {* <legend>Разворот главной страницы паспорта (2-3 стр.)</legend> *}

                            <div class="alert alert-danger " style="display:none"></div>
                            
                            <div class="user_files">
                                {if $passport1_file}
                                <div class="user_file" id="file_{$passport1_file->id}">
                                    {if $passport1_file->status == 0}
                                    <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span></div>
                                    {elseif $passport1_file->status == 1}
                                    <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                    {elseif $passport1_file->status == 2}
                                    <div class="image-status accept" title="Файл принят"><span></span></div>
                                    {elseif $passport1_file->status == 3}
                                    <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                    {/if}
                                    <input type="hidden" name="user_files[]" value="{$passport1_file->id}" />
                                    <img src="{$passport1_file->name|resize:100:100}" data-original="{$config->root_url}/{$config->original_images_dir}{$passport1_file->name}" />
                                </div>
                                {/if}
                            </div>
                            
                            <div class="file-field" {if $passport1_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <div class="user_file__img-wrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                            <circle cx="30" cy="30" r="30" fill="#038AEE"/>
                                            <path d="M29.9999 30.7826L35.6572 36.4386L33.7705 38.3253L31.3332 35.888V43.3346H28.6665V35.8853L26.2292 38.3253L24.3425 36.4386L29.9999 30.7826ZM29.9999 16.668C32.2892 16.6681 34.4986 17.5095 36.2081 19.0321C37.9176 20.5548 39.008 22.6526 39.2719 24.9266C40.9309 25.3792 42.3782 26.4003 43.3607 27.8117C44.3432 29.223 44.7985 30.9348 44.647 32.6477C44.4954 34.3607 43.7467 35.966 42.5317 37.1829C41.3167 38.3998 39.7126 39.1511 37.9999 39.3053V36.62C38.614 36.5331 39.2047 36.3247 39.7374 36.0069C40.2701 35.6891 40.7341 35.2683 41.1022 34.7691C41.4704 34.2699 41.7353 33.7023 41.8816 33.0996C42.0278 32.4968 42.0524 31.8709 41.954 31.2585C41.8555 30.6461 41.6359 30.0594 41.3081 29.5329C40.9803 29.0063 40.5508 28.5504 40.0447 28.1917C39.5386 27.8331 38.9661 27.5789 38.3607 27.4441C37.7552 27.3093 37.129 27.2966 36.5185 27.4066C36.7273 26.4345 36.7161 25.4279 36.4855 24.4607C36.2549 23.4935 35.8109 22.5901 35.186 21.8167C34.5611 21.0433 33.7711 20.4194 32.8738 19.9909C31.9766 19.5623 30.9949 19.3399 30.0005 19.3399C29.0062 19.3399 28.0245 19.5623 27.1272 19.9909C26.23 20.4194 25.44 21.0433 24.8151 21.8167C24.1901 22.5901 23.7461 23.4935 23.5156 24.4607C23.285 25.4279 23.2737 26.4345 23.4825 27.4066C22.2661 27.1782 21.0087 27.4423 19.987 28.141C18.9653 28.8396 18.263 29.9155 18.0345 31.132C17.8061 32.3484 18.0702 33.6058 18.7689 34.6275C19.4675 35.6492 20.5434 36.3515 21.7599 36.58L21.9999 36.62V39.3053C20.2871 39.1511 18.6831 38.3998 17.468 37.1829C16.253 35.966 15.5043 34.3607 15.3528 32.6477C15.2012 30.9348 15.6565 29.223 16.639 27.8117C17.6215 26.4003 19.0688 25.3792 20.7279 24.9266C20.9915 22.6525 22.0817 20.5545 23.7913 19.0318C25.5009 17.5091 27.7105 16.6678 29.9999 16.668Z" fill="white"/>
                                            <script xmlns=""/>
                                        </svg>
                                        <p class="files-form__label-par">Загрузить файл</p>
                                    </div>
                                    <input type="file" name="passport1" accept="image/jpeg,image/png,image/heic,application/pdf" data-type="passport1" />
                                </label>
                            </div>
                            
                            {if $passport1_file->status == 3 || $access_modified_file}
                                <div class="file-field js-replace-block">
                                    {if $passport4_file->status == 3}
                                        <small style="color:#d33;">Файл не прошел проверку и его необходимо заменить</small>
                                    {/if}
                                    <label class="file-label">
                                        <span id="passport1_list">Заменить файл</span>
                                        <input type="file" name="passport1_replace" class="js-replace" accept="image/jpeg,image/png,image/heic,application/pdf" data-type="passport1" data-replace="{$passport1_file->id}" />
                                    </label>
                                </div>
                            {/if}
                            
                        </fieldset>
                        </div>
                        <div class="fieldset-card__wrapper">
                        <p class="pasport1__title">Фото карты</p>
                        <fieldset class="passport4-file file-block" {* style="background:url('design/{$settings->theme|escape}/img/card_logo.png') right center no-repeat;background-size:contain;" *}>
                            
                            {* <legend>Фото карты</legend> *}

                            <div class="alert alert-danger " style="display:none"></div>
                            
                            <div class="user_files">
                                {if $passport4_file}
                                    <div class="user_file" id="file_{$passport4_file->id}">
                                        {if $passport4_file->status == 0}
                                            <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span>
                                            </div>
                                        {elseif $passport4_file->status == 1}
                                            <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                        {elseif $passport4_file->status == 2}
                                            <div class="image-status accept" title="Файл принят"><span></span></div>
                                        {elseif $passport4_file->status == 3}
                                            <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                        {/if}
                                        <input type="hidden" name="user_files[]" value="{$passport4_file->id}"/>
                                        <img src="{$passport4_file->name|resize:100:100}"
                                             data-original="{$config->root_url}/{$config->original_images_dir}{$passport4_file->name}"/>
                                    </div>

                                    {if $passport4_file->status == 3 || $access_modified_file}
                                        <div class="file-field js-replace-block">
                                            {if $passport4_file->status == 3}
                                                <small style="color:#d33;">Файл не прошел проверку и его необходимо заменить</small>
                                            {/if}
                                            <label class="file-label">
                                                <span id="passport4_list">Заменить файл</span>
                                                <input type="file" name="passport4_replace" class="js-replace" accept="image/jpeg,image/png,image/heic,application/pdf" data-type="passport4" data-replace="{$passport4_file->id}" />
                                            </label>
                                        </div>
                                    {/if}
                                {/if}
                            </div>

                            <div class="file-field" {if $passport4_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <div class="user_file__img-wrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                            <circle cx="30" cy="30" r="30" fill="#038AEE"/>
                                            <path d="M29.9999 30.7826L35.6572 36.4386L33.7705 38.3253L31.3332 35.888V43.3346H28.6665V35.8853L26.2292 38.3253L24.3425 36.4386L29.9999 30.7826ZM29.9999 16.668C32.2892 16.6681 34.4986 17.5095 36.2081 19.0321C37.9176 20.5548 39.008 22.6526 39.2719 24.9266C40.9309 25.3792 42.3782 26.4003 43.3607 27.8117C44.3432 29.223 44.7985 30.9348 44.647 32.6477C44.4954 34.3607 43.7467 35.966 42.5317 37.1829C41.3167 38.3998 39.7126 39.1511 37.9999 39.3053V36.62C38.614 36.5331 39.2047 36.3247 39.7374 36.0069C40.2701 35.6891 40.7341 35.2683 41.1022 34.7691C41.4704 34.2699 41.7353 33.7023 41.8816 33.0996C42.0278 32.4968 42.0524 31.8709 41.954 31.2585C41.8555 30.6461 41.6359 30.0594 41.3081 29.5329C40.9803 29.0063 40.5508 28.5504 40.0447 28.1917C39.5386 27.8331 38.9661 27.5789 38.3607 27.4441C37.7552 27.3093 37.129 27.2966 36.5185 27.4066C36.7273 26.4345 36.7161 25.4279 36.4855 24.4607C36.2549 23.4935 35.8109 22.5901 35.186 21.8167C34.5611 21.0433 33.7711 20.4194 32.8738 19.9909C31.9766 19.5623 30.9949 19.3399 30.0005 19.3399C29.0062 19.3399 28.0245 19.5623 27.1272 19.9909C26.23 20.4194 25.44 21.0433 24.8151 21.8167C24.1901 22.5901 23.7461 23.4935 23.5156 24.4607C23.285 25.4279 23.2737 26.4345 23.4825 27.4066C22.2661 27.1782 21.0087 27.4423 19.987 28.141C18.9653 28.8396 18.263 29.9155 18.0345 31.132C17.8061 32.3484 18.0702 33.6058 18.7689 34.6275C19.4675 35.6492 20.5434 36.3515 21.7599 36.58L21.9999 36.62V39.3053C20.2871 39.1511 18.6831 38.3998 17.468 37.1829C16.253 35.966 15.5043 34.3607 15.3528 32.6477C15.2012 30.9348 15.6565 29.223 16.639 27.8117C17.6215 26.4003 19.0688 25.3792 20.7279 24.9266C20.9915 22.6525 22.0817 20.5545 23.7913 19.0318C25.5009 17.5091 27.7105 16.6678 29.9999 16.668Z" fill="white"/>
                                            <script xmlns=""/>
                                        </svg>
                                        <p class="files-form__label-par">Загрузить файл</p>
                                    </div>
                                    <input type="file" name="passport4" accept="image/jpeg,image/png,image/heic,application/pdf" data-type="passport4" />
                                </label>
                            </div>
                        </fieldset>
                        </div>
                        </div>
                        <div class="fieldset-other__wrapper">
                            <p class="pasport1__title">Дополнительные фото</p>
                            <fieldset class="passport-files file-block width100">
                                
                                {* <legend>Дополнительные фото</legend> *}

                                <div class="alert alert-danger" style="display:none"></div>
                                
                                <label for="passport[]" class="file-label-image file-label-with-svg" style="display:none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                        <circle cx="30" cy="30" r="30" fill="#038AEE"/>
                                        <path d="M29.9999 30.7826L35.6572 36.4386L33.7705 38.3253L31.3332 35.888V43.3346H28.6665V35.8853L26.2292 38.3253L24.3425 36.4386L29.9999 30.7826ZM29.9999 16.668C32.2892 16.6681 34.4986 17.5095 36.2081 19.0321C37.9176 20.5548 39.008 22.6526 39.2719 24.9266C40.9309 25.3792 42.3782 26.4003 43.3607 27.8117C44.3432 29.223 44.7985 30.9348 44.647 32.6477C44.4954 34.3607 43.7467 35.966 42.5317 37.1829C41.3167 38.3998 39.7126 39.1511 37.9999 39.3053V36.62C38.614 36.5331 39.2047 36.3247 39.7374 36.0069C40.2701 35.6891 40.7341 35.2683 41.1022 34.7691C41.4704 34.2699 41.7353 33.7023 41.8816 33.0996C42.0278 32.4968 42.0524 31.8709 41.954 31.2585C41.8555 30.6461 41.6359 30.0594 41.3081 29.5329C40.9803 29.0063 40.5508 28.5504 40.0447 28.1917C39.5386 27.8331 38.9661 27.5789 38.3607 27.4441C37.7552 27.3093 37.129 27.2966 36.5185 27.4066C36.7273 26.4345 36.7161 25.4279 36.4855 24.4607C36.2549 23.4935 35.8109 22.5901 35.186 21.8167C34.5611 21.0433 33.7711 20.4194 32.8738 19.9909C31.9766 19.5623 30.9949 19.3399 30.0005 19.3399C29.0062 19.3399 28.0245 19.5623 27.1272 19.9909C26.23 20.4194 25.44 21.0433 24.8151 21.8167C24.1901 22.5901 23.7461 23.4935 23.5156 24.4607C23.285 25.4279 23.2737 26.4345 23.4825 27.4066C22.2661 27.1782 21.0087 27.4423 19.987 28.141C18.9653 28.8396 18.263 29.9155 18.0345 31.132C17.8061 32.3484 18.0702 33.6058 18.7689 34.6275C19.4675 35.6492 20.5434 36.3515 21.7599 36.58L21.9999 36.62V39.3053C20.2871 39.1511 18.6831 38.3998 17.468 37.1829C16.253 35.966 15.5043 34.3607 15.3528 32.6477C15.2012 30.9348 15.6565 29.223 16.639 27.8117C17.6215 26.4003 19.0688 25.3792 20.7279 24.9266C20.9915 22.6525 22.0817 20.5545 23.7913 19.0318C25.5009 17.5091 27.7105 16.6678 29.9999 16.668Z" fill="white"/>
                                        <script xmlns=""/>
                                    </svg>
                                    <p>Добавить фото</p>
                                </label>

                                <div class="user_files">
                                {foreach $passport_files as $pfile}
                                    <div class="user_file" id="file_{$pfile->id}">
                                        {if $pfile->status == 0}
                                        <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span></div>
                                        {elseif $pfile->status == 1}
                                        <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                        {elseif $pfile->status == 2}
                                        <div class="image-status accept" title="Файл принят"><span></span></div>
                                        {elseif $pfile->status == 3}
                                        <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                        {/if}
                                        <input type="hidden" name="user_files[]" value="{$pfile->id}" />
                                        <img src="{$pfile->name|resize:100:100}" data-original="{$config->root_url}/{$config->original_images_dir}{$pfile->name}" />
                                        {if $access_modified_file}
                                            <a href="javascript:void(0);" class="remove-file" data-id="{$pfile->id}">&times;</a>
                                        {/if}
                                    </div>
                                {/foreach}
                                </div>
                                
                                <div class="file-field passport-field" {if ($passport_files|count) > 20}style="display:none"{/if}>

                                <label class="file-label passport-label">
                                    <span id="passport-other_list">Добавить Файл</span>
                                    <input type="file" class="passport-input" id="passport[]" name="passport[]" accept="image/jpeg,image/png,image/heic,application/pdf" data-type="passport" />
                                </label>
                            </div>
                            
                        </fieldset>
                        </div>

                        {if $has_rejected_photo}
                            <input type="submit" name="confirm" class="button medium"  value="Отправить файлы на проверку" />
                        {/if}

                        <style>
                            @media (max-width: 768px) {
                                .mobile-green-bg {
                                    background-color: #93cd52 !important;
                                }
                            }
                        </style>
                        <p
                                class="form-help "
                                style="font-weight: bold; text-align: left;"
                        >
                            Сделайте качественные фото, <span
                                    style="color: red;">и вероятность одобрения повысится!</span>
                        <ul
                                class=""
                                style="font-weight: bold; text-align: left;"
                        >
                            <li>располагайте документы так, чтобы они полностью помещались на фотографии;</li>
                            <li>текст должен быть читаемым и полностью виден;</li>
                            <li>исключите блики на фото.</li>
                        </ul>
                        </p>
                        <p class="form-help">
                            * Максимальный размер файла: {($max_file_size/1024/1024)|round} Мб
                        </p>
                        <br/>
                        <div class="clearfix next">
                            {if $is_developer}
                                <a class="button big button-inverse" id="" href="account?step=additional">Назад</a>
                            {/if}
                            <input type="submit" name="confirm" class="button big"
                                   {if !$have_new_file}style="display:none"{/if} value="Далее"/>
                        </div>
                        
                    </form>
					
				</div>
				
			</div>
		</div>
	</div>
    <div id="img_modal" class="white-popup-modal mfp-hide">
        <a href="javascript:void(0);" onclick="$.magnificPopup.close();" class="close">&#9421;</a>
        <div class="modal-content">
            <div id="img_wrapper"></div>
        </div>
    </div>
</section>

<script>
jQuery(document).ready(function($) {
    var $form       = $('#files_form');
    var $block      = $form.find('.passport-files');
    var $files      = $block.find('.user_files');
    var $label      = $block.find('.file-label-with-svg');

    if ($files.children().length === 0) {
        $label.show();
    }
});
</script>