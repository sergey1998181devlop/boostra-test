{* Вкладки *}
{capture name=tabs}
	<li class="active"><a href="index.php?module=DocsAdmin">Документация</a></li>
{/capture}

{if $doc->id}
{$meta_title = $doc->name scope=parent}
{else}
{$meta_title = "Новый документ" scope=parent}
{/if}


{* On document load *}
{literal}

<script>

$(function() {

    // Удаление изображений
	$(".images a.delete").click( function() {
		$("input[name='delete_file']").val('1');
		$(this).closest("ul").fadeOut(200, function() { $(this).remove(); });
		return false;
	});

	
});


</script>
{/literal}

{if $message_success}
<!-- Системное сообщение -->
<div class="message message_success">
	<span>
        {if $message_success == 'added'}Запись добавлена
        {elseif $message_success == 'updated'}Запись обновлена
        {else}{$message_success}{/if}
    </span>
	{if $smarty.get.return}
	<a class="button" href="{$smarty.get.return}">Вернуться</a>
	{/if}
</div>
<!-- Системное сообщение (The End)-->
{/if}

{if $message_error}
<!-- Системное сообщение -->
<div class="message message_error">
	<span>
        {if $message_error == 'extension_error'}Недопустимое разрешение файла
        {elseif $message_error == 'upload_error'}Не удалось загрузить файл
        {elseif $message_error == 'save_error'}Не удалось сохранить файл
        {elseif $message_error == 'empty_name'}Укажите название документа
        {else}{$message_error}{/if}
    </span>
	{if $smarty.get.return}
		<a class="button" href="{$smarty.get.return}">Вернуться</a>
	{/if}
	</div>
<!-- Системное сообщение (The End)-->
{/if}


<!-- Основная форма -->
<form method="post" id="product" enctype="multipart/form-data">
<input type="hidden" name="session_id" value="{$smarty.session.id}">
	<div id="name">
		<input class="name" name="name" type="text" value="{$doc->name|escape}"/> 
		<input name="id" type="hidden" value="{$doc->id|escape}"/> 
		
        <div class="checkbox">
            <input type="checkbox" name="visible" id="visible_checkbox" value="1" {if $doc->visible}checked="true"{/if} />
            <label for="visible_checkbox">Видимый</label>
        </div>
	</div> 

	<!-- Левая колонка свойств товара -->
	<div id="column_left">
		<div class="block layer">
			<ul>
				<li>
                    <label class="property">Дата добавления</label>
                    <input type="text" disabled="true" value="{$doc->created|date} {$doc->created|time}" />
                </li>
                <li><label class="property">Описание</label><textarea name="description" />{$doc->description|escape}</textarea></li>
			</ul>
		</div>
		<!-- Параметры страницы (The End)-->

        <div class="block layer">
            <h2>Отображение документов</h2>
            <ul>
                <li>
                    <label class="property" style="width:100%">
                        <input type="checkbox" name="in_info" id="info_checkbox" value="1" {if $doc->in_info}checked="true"{/if} />
                        <span>Основные</span>
                    </label>
                    <label class="property" style="width:100%">
                        <input type="checkbox" name="in_register" id="register_checkbox" value="1" {if $doc->in_register}checked="true"{/if} />
                        <span>Регистрация</span>
                    </label>
                </li>
            </ul>
        </div>
			
	</div>
	<!-- Левая колонка свойств товара (The End)--> 
	
	<!-- Правая колонка свойств товара -->	
	<div id="column_right">
        
        {*blog_image*}
		<div class="block layer images">
			<h2>Файл</h2>
            <p>(максимальный размер файла &mdash; {if $config->max_upload_filesize>1024*1024}{$config->max_upload_filesize/1024/1024|round:'2'} МБ{else}{$config->max_upload_filesize/1024|round:'2'} КБ{/if})</p>
			<br />
            <input class="upload_image" name="file" type="file"/>			
			<input type="hidden" name="delete_file" value=""/>
			{if $doc->filename}
			<ul>
				<li style="height:100px;">
					<a href="#" class="delete"><img src="design/images/cross-circle-frame.png"/></a>
					<a target="_blank" href="{$config->root_url}/{$config->docs_files_dir}{$doc->filename}">
                        <img src="design/images/file_icons/{$doc->icon}"/>
                    </a>
				</li>
			</ul>
			{/if}
		</div>
        {*/blog_image*}
	</div>
	<!-- Правая колонка свойств товара (The End)--> 
	
	<input class="button_green button_save" type="submit" name="" value="Сохранить" />
	
</form>
<!-- Основная форма (The End) -->
