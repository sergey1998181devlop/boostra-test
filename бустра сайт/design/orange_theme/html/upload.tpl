{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user/upload" scope=parent}

{$body_class = "gray" scope=parent}
 
{$add_order_css_js = true scope=parent}

{literal}
<style>
    
 
</style>
<script>

function UploadApp()
{
    var app = this;

    app.init = function(){
console.log('run')    
        
        $(document).on('change', '[type=file]', function(){
            app.upload(this);
        });
        
        $(document).on('click', '.remove-file', function(e){
            e.preventDefault();

            if (1 || confirm('Вы хотите удалить файл? Операцию не возможно будет отменить.'))
            {
                app.remove($(this));
            }
            return false;
        });

    };
    
    app.remove = function($this){
        
        var file_id = $this.data('id');
        var $fileblock = $this.closest('.file-block');

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
        });
        
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
        });
        
    };
    
    app.upload = function(input){
        
        var $this = $(input);
        
        var $fileblock = $this.closest('.file-block');
        var _type = $this.data('type');
        
        var form_data = new FormData();
                    
        form_data.append('file', input.files[0])
        form_data.append('type', _type);        
        form_data.append('action', 'add');        

        $.ajax({
            url: 'http://51.250.17.98/ajax/upload.php',
            data: form_data,
            type: 'POST',
            dataType: 'json',
            processData : false,
            contentType : false
        });

        $.ajax({
            url: 'ajax/upload.php',
            data: form_data,
            type: 'POST',
            dataType: 'json',
            processData : false,
            contentType : false, 
            beforeLoad: function(){
                $fileblock.addClass('loading');
            },
            success: function(resp){
                if (!!resp.error)
                {
                    var error_text = '';
                    if (resp.error == 'max_file_size')
                        error_text = 'Превышен максимально допустимый размер файла.';
                    else if (resp.error == 'error_uploading')
                        error_text = 'Файл не удалось загрузить, попробуйте еще.';
                    else if (resp.error == 'extension')
                        error_text = 'Файл не удалось загрузить, Недопустимое расширение файла. Допускается загрузка форматов: '+resp.allowed_extensions.join(', ');
                    else
                        error_text = resp.error;
                        
                    $fileblock.find('.alert').html(error_text).fadeIn();
                }
                else
                {
                    $fileblock.find('.alert').fadeOut();
                    
                    var _preview = '';
                    
                    _preview += '<div class="user_file">';
                    _preview += '<div class="image-status uploaded" title="Файл загружен, отправьте его на проверку. Не отправленные файлы, через 5 дней после загрузки, будут удалены"><span></span></div>';
                    _preview += '<img src="'+resp.filename+'" />';
                    _preview += '<a href="javascript:void(0);" class="remove-file" data-id="'+resp.id+'">&times;</a>';
                    _preview += '</div>';
                    
                    $fileblock.find('.user_files').append(_preview);
                    
                    if (_type != 'passport')
                        $fileblock.find('.file-field').fadeOut();
                    
                    $this.closest('form').find('[name=confirm]').fadeIn();
                    
                    if ($this.hasClass('js-replace'))
                    {
                        var _id = $this.data('replace');
                        $.ajax({
                            url: 'ajax/upload.php',
                            data: {
                                id: _id,
                                action: 'remove'
                            },
                            type: 'POST',
                            dataType: 'json',
                            beforeLoad: function(){
                
                            },
                            success: function(resp){
                                if(resp.error) {
                                    alert(resp.error);
                                    return;
                                }
                                $fileblock.find('.alert').fadeOut();
                                
                                $('#file_'+_id).fadeOut();
                                $fileblock.find('.js-replace-block').remove();
                            }
                        });
                    }
                }
                
            }
        });
        
    }
    
    ;(function(){
        app.init();
    })();
};
new UploadApp();

  
/*        
        $(document).on('change', '[type=file]', function(){
            $(this).closest('label').find('span').html($(this).val());
            $(this).closest('.file-field').addClass('uploaded');
        });
        var jj = $('.passport-files .user_file').length;
        var $passport_input = $('.passport-field').clone(true);
        $(document).on('change', '.passport-input', function(){
            if (jj < 10 && $(this).val() != '')
            {
                var $insert = $passport_input.clone(true);
                $('.passport-files').append($insert);
                jj++;
            }
        });
        
        $(document).on('click', '.remove-file', function(e){
            e.preventDefault();
            if ($(this).closest('.file-block').hasClass('passport-files'))
                jj--;
            $(this).closest('.file-block').find('.file-field').show();
            $(this).closest('.user_file').remove();
        });
*/        
</script>
{/literal}

<section id="private">
	<div>
		<div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">
			<div class="nav">
				<ul>
					<li><a href="/user?user_id={$user->id}" {if $action=='user'}class="current"{/if}>Текущий заём</a></li>
				    {*}
					<li><a href="/user?user_id={$user->id}&action=history" {if $action == 'history'}class="current"{/if}>История займов</a></li>
					{*}
                    <li><a href="/user/loanhistory">Мои заявки</a></li>					
                    <li><a href="/user/upload" class="current">Мои файлы</a></li>					
					<li><a href="/user/docs">Документы</a></li>
                    <li><a href="user/logout">Выйти</a></li>
				</ul>
			</div>
			<div class="content">
				<div class="panel">
                    
                    {if !($face1_file && $face2_file && $passport_files)}
                    <p>Для подтверждения личности прикрепите фото паспорта и 2 фото лица</p>
                    {/if}
                    
                    <form id="files_form" method="POST" enctype="multipart/form-data" >
                        
                        {if $error=='error_upload'}
                        <div class="alert alert-danger">
                            При передаче файлов произошла ошибка, попробуйте повторить позже.
                        </div>
                        {/if}
                        {*
                        <fieldset class="face1-file file-block" style="background:url('design/{$settings->theme|escape}/img/face1.png') right center no-repeat;background-size:contain;">
                            
                            <legend>Фото анфас</legend>

                            <div class="alert alert-danger " style="display:none"></div>
                            
                            <div class="user_files">
                                {if $face1_file}
                                <div class="user_file" id="file_{$face1_file->id}">
                                    {if $face1_file->status == 0}
                                    <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span></div>
                                    {elseif $face1_file->status == 1}
                                    <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                    {elseif $face1_file->status == 2}
                                    <div class="image-status accept" title="Файл принят"><span></span></div>
                                    {elseif $face1_file->status == 3}
                                    <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                    {/if}
                                    <input type="hidden" name="user_files[]" value="{$face1_file->id}" />
                                    <img src="{$face1_file->name|resize:100:100}" />
                                    {if $face1_file->status == 0 || $face1_file->status == 3}
                                    <a href="javascript:void(0);" class="remove-file" data-id="{$face1_file->id}">&times;</a>
                                    {/if}
                                </div>
                                {/if}
                            </div>
                            
                            <div class="file-field" {if $face1_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <span id="file_list">Добавить файл</span>
                                    <input type="file" name="face1" accept="image/jpeg,image/png" data-type="face1" />
                                </label>
                            </div>
                            
                            {if $face1_file->status == 3}
                            <div class="file-field js-replace-block">
                                <small style="color:#d33;">Файл не прошел проверку и его необходимо заменить</small>
                                <label class="file-label">
                                    <span id="file_list">Заменить файл</span>
                                    <input type="file" name="face1_replace" class="js-replace" accept="image/jpeg,image/png" data-type="face1" data-replace="{$face1_file->id}" />
                                </label>
                            </div>
                            {/if}
                            
                        </fieldset>
                        <fieldset class="face2-file file-block" style="background:url('design/{$settings->theme|escape}/img/face2.png') right center no-repeat;background-size:contain;">
                            
                            <legend>Фото в профиль</legend>
                            
                            <div class="alert alert-danger" style="display:none"></div>
                            
                            <div class="user_files">
                                {if $face2_file}
                                <div class="user_file" id="file_{$face2_file->id}">
                                    {if $face2_file->status == 0}
                                    <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span></div>
                                    {elseif $face2_file->status == 1}
                                    <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                    {elseif $face2_file->status == 2}
                                    <div class="image-status accept" title="Файл принят"><span></span></div>
                                    {elseif $face2_file->status == 3}
                                    <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                    {/if}
                                    <input type="hidden" name="user_files[]" value="{$face2_file->id}" />
                                    <img src="{$face2_file->name|resize:100:100}" />
                                    {if $face2_file->status == 0 || $face2_file->status == 3}
                                    <a href="javascript:void(0);" class="remove-file" data-id="{$face2_file->id}">&times;</a>
                                    {/if}
                                </div>
                                {/if}
                            </div>
                            
                            <div class="file-field" {if $face2_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <span>Добавить файл</span>
                                    <input type="file" name="face2" accept="image/jpeg,image/png" data-type="face2" />
                                    
                                </label>
                            </div>

                            {if $face2_file->status == 3}
                            <div class="file-field js-replace-block">
                                <small style="color:#d33;">Файл не прошел проверку и его необходимо заменить</small>
                                <label class="file-label">
                                    <span id="file2_list">Заменить файл</span>
                                    <input type="file" name="face2_replace" class="js-replace" accept="image/jpeg,image/png" data-type="face2" data-replace="{$face2_file->id}" />
                                </label>
                            </div>
                            {/if}
                            
                            
                        </fieldset>
                        *}
                        <fieldset class="passport1-file file-block" style="background:url('design/{$settings->theme|escape}/img/passport1.png') right center no-repeat;background-size:contain;">
                            
                            <legend>Первая страница паспорта</legend>

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
                                    <img src="{$passport1_file->name|resize:100:100}" />
                                    {if $passport1_file->status == 0 || $passport1_file->status == 3}
                                    <a href="javascript:void(0);" class="remove-file" data-id="{$passport1_file->id}">&times;</a>
                                    {/if}
                                </div>
                                {/if}
                            </div>
                            
                            <div class="file-field" {if $passport1_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <span id="file_list">Добавить файл</span>
                                    <input type="file" name="passport1" accept="image/jpeg,image/png" data-type="passport1" />
                                </label>
                            </div>
                            
                            {if $passport1_file->status == 3}
                            <div class="file-field js-replace-block">
                                <small style="color:#d33;">Файл не прошел проверку и его необходимо заменить</small>
                                <label class="file-label">
                                    <span id="passport1_list">Заменить файл</span>
                                    <input type="file" name="passport1_replace" class="js-replace" accept="image/jpeg,image/png" data-type="passport1" data-replace="{$passport1_file->id}" />
                                </label>
                            </div>
                            {/if}
                            
                        </fieldset>
                        
                        <fieldset class="passport2-file file-block" style="background:url('design/{$settings->theme|escape}/img/passport2.png') right center no-repeat;background-size:contain;">
                            
                            <legend>Cтраница с регистрацией</legend>

                            <div class="alert alert-danger " style="display:none"></div>
                            
                            <div class="user_files">
                                {if $passport2_file}
                                <div class="user_file" id="file_{$passport2_file->id}">
                                    {if $passport2_file->status == 0}
                                    <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span></div>
                                    {elseif $passport2_file->status == 1}
                                    <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                    {elseif $passport2_file->status == 2}
                                    <div class="image-status accept" title="Файл принят"><span></span></div>
                                    {elseif $passport2_file->status == 3}
                                    <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                    {/if}
                                    <input type="hidden" name="user_files[]" value="{$passport2_file->id}" />
                                    <img src="{$passport2_file->name|resize:100:100}" />
                                    {if $passport2_file->status == 0 || $passport2_file->status == 3}
                                    <a href="javascript:void(0);" class="remove-file" data-id="{$passport2_file->id}">&times;</a>
                                    {/if}
                                </div>
                                {/if}
                            </div>
                            
                            <div class="file-field" {if $passport2_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <span id="file_list">Добавить файл</span>
                                    <input type="file" name="passport2" accept="image/jpeg,image/png" data-type="passport2" />
                                </label>
                            </div>
                            
                            {if $passport2_file->status == 3}
                            <div class="file-field js-replace-block">
                                <small style="color:#d33;">Файл не прошел проверку и его необходимо заменить</small>
                                <label class="file-label">
                                    <span id="passport2_list">Заменить файл</span>
                                    <input type="file" name="passport2_replace" class="js-replace" accept="image/jpeg,image/png" data-type="passport2" data-replace="{$passport2_file->id}" />
                                </label>
                            </div>
                            {/if}
                            
                        </fieldset>
                        
                        <fieldset class="passport4-file file-block" style="background:url('design/{$settings->theme|escape}/img/card_logo.png') right center no-repeat;background-size:contain;">
                            
                            <legend>Фото карты</legend>

                            <div class="alert alert-danger " style="display:none"></div>
                            
                            <div class="user_files">
                                {if $passport4_file}
                                <div class="user_file" id="file_{$passport4_file->id}">
                                    {if $passport4_file->status == 0}
                                    <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span></div>
                                    {elseif $passport4_file->status == 1}
                                    <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                    {elseif $passport4_file->status == 2}
                                    <div class="image-status accept" title="Файл принят"><span></span></div>
                                    {elseif $passport4_file->status == 3}
                                    <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                    {/if}
                                    <input type="hidden" name="user_files[]" value="{$passport4_file->id}" />
                                    <img src="{$passport4_file->name|resize:100:100}" />
                                    {if $passport4_file->status == 0 || $passport4_file->status == 3}
                                    <a href="javascript:void(0);" class="remove-file" data-id="{$passport4_file->id}">&times;</a>
                                    {/if}
                                </div>
                                {/if}
                            </div>
                            
                            <div class="file-field" {if $passport4_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <span id="file_list">Добавить файл</span>
                                    <input type="file" name="passport4" accept="image/jpeg,image/png" data-type="passport4" />
                                </label>
                            </div>
                            
                            {if $passport4_file->status == 3}
                            <div class="file-field js-replace-block">
                                <small style="color:#d33;">Файл не прошел проверку и его необходимо заменить</small>
                                <label class="file-label">
                                    <span id="passport4_list">Заменить файл</span>
                                    <input type="file" name="passport4_replace" class="js-replace" accept="image/jpeg,image/png" data-type="passport4" data-replace="{$passport4_file->id}" />
                                </label>
                            </div>
                            {/if}
                            
                        </fieldset>
                        
                        <fieldset class="selfi-file file-block" style="background:url('design/{$settings->theme|escape}/img/selfi.png') right center no-repeat;background-size:contain;">
                            
                            <legend>Селфи</legend>

                            <div class="alert alert-danger " style="display:none"></div>
                            
                            <div class="user_files">
                                {if $selfi_file}
                                <div class="user_file" id="file_{$selfi_file->id}">
                                    {if $selfi_file->status == 0}
                                    <div class="image-status uploaded" title="Файл загружен, отправьте его на проверку"><span></span></div>
                                    {elseif $selfi_file->status == 1}
                                    <div class="image-status sended" title="Файл отправлен на проверку"><span></span></div>
                                    {elseif $selfi_file->status == 2}
                                    <div class="image-status accept" title="Файл принят"><span></span></div>
                                    {elseif $selfi_file->status == 3}
                                    <div class="image-status dismiss" title="Файл отклонен"><span></span></div>
                                    {/if}
                                    <input type="hidden" name="user_files[]" value="{$selfi_file->id}" />
                                    <img src="{$selfi_file->name|resize:100:100}" />
                                    {if $selfi_file->status == 0 || $selfi_file->status == 3}
                                    <a href="javascript:void(0);" class="remove-file" data-id="{$selfi_file->id}">&times;</a>
                                    {/if}
                                </div>
                                {/if}
                            </div>
                            
                            <div class="file-field" {if $selfi_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <span id="file_list">Добавить файл</span>
                                    <input type="file" name="selfi" accept="image/jpeg,image/png" data-type="selfi" />
                                </label>
                            </div>
                            
                            {if $selfi_file->status == 3}
                            <div class="file-field js-replace-block">
                                <small style="color:#d33;">Файл не прошел проверку и его необходимо заменить</small>
                                <label class="file-label">
                                    <span id="selfi_list">Заменить файл</span>
                                    <input type="file" name="selfi_replace" class="js-replace" accept="image/jpeg,image/png" data-type="selfi" data-replace="{$selfi_file->id}" />
                                </label>
                            </div>
                            {/if}
                            
                        </fieldset>
                        
                        <fieldset class="passport-files file-block width100">
                            
                            <legend>Дополнительные фото</legend>

                            <div class="alert alert-danger" style="display:none"></div>
                            
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
                                    <img src="{$pfile->name|resize:100:100}" />
                                    {if $pfile->status == 0 || $pfile->status == 3}
                                    <a href="javascript:void(0);" class="remove-file" data-id="{$pfile->id}">&times;</a>
                                    {/if}
                                </div>
                            {/foreach}
                            </div>
                            
                            <div class="file-field passport-field" {if ($passport_files|count) > 20}style="display:none"{/if}>
                                <label class="file-label passport-label">
                                    <span>Добавить файл</span>
                                    <input type="file" class="passport-input" name="passport[]" accept="image/jpeg,image/png" data-type="passport" />
                                </label>
                            </div>
                            
                        </fieldset>
                                                
                        <input type="submit" name="confirm" class="button medium" {if !$have_new_file}style="display:none"{/if} value="Отправить файлы на проверку" />
                        
                        <p class="form-help">
                            * Максимальный размер файла: {($max_file_size/1024/1024)|round} Мб
                        </p>
                        
                    </form>
					
				</div>
				
			</div>
		</div>
	</div>
</section>