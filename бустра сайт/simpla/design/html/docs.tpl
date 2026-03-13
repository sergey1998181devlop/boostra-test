{* Вкладки *}
{capture name=tabs}
	<li class="active"><a href="index.php?module=DocsAdmin">Документация</a></li>
{/capture}

{* Title *}
{$meta_title='Документация' scope=parent}
		
{* Поиск *}
{if $docs || $keyword}
<form method="get">
<div id="search">
	<input type="hidden" name="module" value='DocsAdmin'>
	<input class="search" type="text" name="keyword" value="{$keyword|escape}" />
	<input class="search_button" type="submit" value=""/>
</div>
</form>
{/if}

{* Заголовок *}
<div id="header">
	{if $docs_count}
	<h1>{$docs_count} {$docs_count|plural:'документ':'документов':'документа'}</h1>
	{else}
	<h1>Нет документов</h1>
	{/if}
	<a class="add" href="{url module=DocAdmin return=$smarty.server.REQUEST_URI}">Новый документ</a>
</div>	

{if $docs}
<div id="main_list">
	
	<!-- Листалка страниц -->
	{include file='pagination.tpl'}	
	<!-- Листалка страниц (The End) -->

	<form id="list_form" method="post">
	<input type="hidden" name="session_id" value="{$smarty.session.id}">
	
		<div id="list">
			{foreach $docs as $doc}
			<div class="row {if !$doc->visible}invisible{/if}">
				<input type="hidden" name="positions[{$doc->id}]" value="{$doc->position}" />
				<div class="move cell"><div class="move_zone"></div></div>
                <div class="checkbox cell">
					<input type="checkbox" name="check[]" value="{$doc->id}"/>				
				</div>
				<div class="coupon_name cell">			 	
                    <i>{$doc->created|date} {$doc->created|time}</i>
                    <br />
	 				<a href="{url module=DocAdmin id=$doc->id return=$smarty.server.REQUEST_URI}">{$doc->name|escape}</a>
				</div>
				<div class="coupon_discount cell">			 	
	 				<div class="detail">
	 				{$doc->description|truncate:100:'...'}
	 				</div>
				</div>
				<div class="coupon_details cell">			 	
					{if $doc->filename}
	 				<div class="detail" style="margin-left:10px;">
                        <a href="{$config->root_url}/{$config->docs_files_dir}{$doc->filename}" target="_blank" title="{$doc->filename}" style="white-space:nowrap;text-decoration:none">
                            <span style="display:inline-block;vertical-align:top;margin-right:10px;"><img src="design/images/file_icons/{$doc->icon}" width="24" /></span>
                            <span style="display:inline-block;vertical-align:middle">{$doc->filename_short|escape}</span>
                        </a>
	 				</div>
	 				{/if}
				</div>
				<div class="icons cell">
                    <a href="#" class="enable"></a>
					<a href='#' class="delete"></a>
				</div>
				<div class="clear"></div>
			</div>
			{/foreach}
		</div>
		
	
		<div id="action">
		<label id="check_all" class="dash_link">Выбрать все</label>
	
		<span id="select">
		<select name="action">
			<option value="set_visible">Сделать видимыми на сайте</option>
			<option value="unset_visible">Сделать невидимыми на сайте</option>
			<option value="set_info">Показывать в основных</option>
			<option value="unset_info">Не показывать в основных</option>
			<option value="set_register">Показывать при регистрации</option>
			<option value="unset_register">Не показывать при регистрации</option>
			<option value="delete">Удалить</option>
		</select>
		</span>
	
		<input id="apply_action" class="button_green" type="submit" value="Применить">
		
		</div>
				
	</form>	

	<!-- Листалка страниц -->
	{include file='pagination.tpl'}	
	<!-- Листалка страниц (The End) -->
	
</div>
{/if}

<div id="right_menu">
    
    <ul>
        <li {if !$filter}class="selected"{/if}><a href="{url page=null filter=null}">Все документы</a></li>
        <li {if $filter=='visible'}class="selected"{/if}><a href="{url page=null filter='visible'}">Видимые на сайте</a></li>
        <li {if $filter=='unvisible'}class="selected"{/if}><a href="{url page=null filter='unvisible'}">Невидимые на сайте</a></li>
        <li {if $filter=='info'}class="selected"{/if}><a href="{url page=null filter='info'}">Основные</a></li>
        <li {if $filter=='register'}class="selected"{/if}><a href="{url page=null filter='register'}">Регистрация</a></li>
    </ul>
    
</div>

{* On document load *}
{literal}

<script>
$(function() {
    
    // Скрыт/Видим
	$("a.enable").click(function() {
		var icon        = $(this);
		var line        = icon.closest(".row");
		var id          = line.find('input[type="checkbox"][name*="check"]').val();
		var state       = line.hasClass('invisible')?1:0;
		icon.addClass('loading_icon');
		$.ajax({
			type: 'POST',
			url: 'ajax/update_object.php',
			data: {'object': 'doc', 'id': id, 'values': {'visible': state}, 'session_id': '{/literal}{$smarty.session.id}{literal}'},
			success: function(data){
				icon.removeClass('loading_icon');
				if(state)
					line.removeClass('invisible');
				else
					line.addClass('invisible');				
			},
			dataType: 'json'
		});	
		return false;	
	});
    
	// Сортировка списка
	$("#list").sortable({
		items:             ".row",
		tolerance:         "pointer",
		handle:            ".move_zone",
		scrollSensitivity: 40,
		opacity:           0.7, 
		forcePlaceholderSize: true,
		axis: 'y',
		
		helper: function(event, ui){		
			if($('input[type="checkbox"][name*="check"]:checked').size()<1) return ui;
			var helper = $('<div/>');
			$('input[type="checkbox"][name*="check"]:checked').each(function(){
				var item = $(this).closest('.row');
				helper.height(helper.height()+item.innerHeight());
				if(item[0]!=ui[0]) {
					helper.append(item.clone());
					$(this).closest('.row').remove();
				}
				else {
					helper.append(ui.clone());
					item.find('input[type="checkbox"][name*="check"]').attr('checked', false);
				}
			});
			return helper;			
		},	
 		start: function(event, ui) {
  			if(ui.helper.children('.row').size()>0)
				$('.ui-sortable-placeholder').height(ui.helper.height());
		},
		beforeStop:function(event, ui){
			if(ui.helper.children('.row').size()>0){
				ui.helper.children('.row').each(function(){
					$(this).insertBefore(ui.item);
				});
				ui.item.remove();
			}
		},
		update:function(event, ui)
		{
			$("#list_form input[name*='check']").attr('checked', false);
			$("#list_form").ajaxSubmit(function() {
				colorize();
			});
		}
	});

	// Раскраска строк
	function colorize()
	{
		$("#list div.row:even").addClass('even');
		$("#list div.row:odd").removeClass('even');
	}
	// Раскрасить строки сразу
	colorize();

	// Выделить все
	$("#check_all").click(function() {
		$('#list input[type="checkbox"][name*="check"]').attr('checked', 1-$('#list input[type="checkbox"][name*="check"]').attr('checked'));
	});	

	// Удалить 
	$("a.delete").click(function() {
		$('#list input[type="checkbox"][name*="check"]').attr('checked', false);
		$(this).closest(".row").find('input[type="checkbox"][name*="check"]').attr('checked', true);
		$(this).closest("form").find('select[name="action"] option[value=delete]').attr('selected', true);
		$(this).closest("form").submit();
	});
		
	// Подтверждение удаления
	$("form").submit(function() {
		if($('#list input[type="checkbox"][name*="check"]:checked').length>0)
			if($('select[name="action"]').val()=='delete' && !confirm('Подтвердите удаление'))
				return false;	
	});
});

</script>
{/literal}