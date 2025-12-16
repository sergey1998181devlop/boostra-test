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
                    //_preview += '<a href="javascript:void(0);" class="remove-file" data-id="'+resp.id+'">&times;</a>';
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

  
</script>
{/literal}

<section id="private">
	<div>
		<div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">
			
            {include file='user_nav.tpl' current='user'}
            
			<div class="content">
				<div class="panel">
                     
                    <form id="files_form" method="POST" enctype="multipart/form-data" >
                        
                        {if $error=='error_upload'}
                        <div class="alert alert-danger">
                            При передаче файлов произошла ошибка, попробуйте повторить позже.
                        </div>
                        {/if}
                        
                        
                        <fieldset class="passport4-file file-block" style="background:url('design/{$settings->theme|escape}/img/card_logo.png') 70% center no-repeat;padding: 30px;height:130px;width:90%">
                            
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
                                    {*if $passport4_file->status == 0 || $passport4_file->status == 3}
                                    <a href="javascript:void(0);" class="remove-file" data-id="{$passport4_file->id}">&times;</a>
                                    {/if*}
                                </div>
                                {/if}
                            </div>
                            
                            <div class="file-field" {if $passport4_file}style="display:none"{/if}>
                                <label class="file-label">
                                    <span id="file_list">Добавьте фото карты <br />{$card->pan}</span>
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
                        
                        

                        <style>
                            @media (max-width: 768px) {
                                .mobile-green-bg {
                                    background-color: #93cd52 !important;
                                }
                            }
                        </style>
                        
                        <p
                                class="form-help mobile-green-bg"
                                style="font-weight: bold; text-align: left;"
                        >
                            Сделайте качественные фото, <span
                                    style="color: red;">и вероятность одобрения повысится!</span>
                        <ul
                                class="mobile-green-bg"
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
                        
                    </form>
					
				</div>
				
			</div>
		</div>
	</div>
</section>