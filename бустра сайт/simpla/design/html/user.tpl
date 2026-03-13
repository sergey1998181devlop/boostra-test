{* Вкладки *}
{capture name=tabs}
<li class="active"><a href="index.php?module=UsersAdmin">Заемщики</a></li>
{if in_array('groups', $manager->permissions)}<li><a href="index.php?module=GroupsAdmin">Группы</a></li>{/if}
{if in_array('coupons', $manager->permissions)}<li><a href="index.php?module=CouponsAdmin">Купоны</a></li>{/if}
{/capture}

{if $user->id}
{$meta_title = $user->name|escape scope=parent}
{/if}

{if $message_success}
<!-- Системное сообщение -->
<div class="message message_success">
	<span class="text">{if $message_success=='updated'}Пользователь отредактирован{else}{$message_success|escape}{/if}</span>
	{if $smarty.get.return}
	<a class="button" href="{$smarty.get.return}">Вернуться</a>
	{/if}
</div>
<!-- Системное сообщение (The End)-->
{/if}

{if $message_error}
<!-- Системное сообщение -->
<div class="message message_error">
	<span class="text">{if $message_error=='login_exists'}Пользователь с таким email уже зарегистрирован
		{elseif $message_error=='empty_name'}Введите имя пользователя
		{elseif $message_error=='empty_email'}Введите email пользователя
		{else}{$message_error|escape}{/if}</span>
		{if $smarty.get.return}
		<a class="button" href="{$smarty.get.return}">Вернуться</a>
		{/if}
	</div>
	<!-- Системное сообщение (The End)-->
	{/if}



	<!-- Основная форма -->
	<form method=post id=product>
		<input type=hidden name="session_id" value="{$smarty.session.id}">
		<div id="name">
			<h1>
				{$user->lastname} {$user->firstname} {$user->patronymic}
			</h1>
			<input name=id type="hidden" value="{$user->id|escape}"/> 
			<div class="checkbox">
				<input name="enabled" value='1' type="checkbox" id="active_checkbox" {if $user->enabled}checked{/if}/> <label for="active_checkbox">Активен</label>
			</div>
            <div class="checkbox">
				<input name="file_uploaded" value='1' type="checkbox" id="file_uploaded_checkbox" {if $user->file_uploaded}checked{/if}/> 
                <label for="file_uploaded_checkbox">Файлы загружены</label>
			</div>
		</div> 


		<div id=column_left>
			<!-- Левая колонка свойств товара -->

			<!-- Параметры страницы -->
			<div class="block">
				<ul>
					{if $groups}
					<li>
						<label class=property>Группа</label>
						<select name="group_id">
							<option value='0'>Не входит в группу</option>
							{foreach $groups as $g}
							<option value='{$g->id}' {if $user->group_id == $g->id}selected{/if}>{$g->name|escape}</option>
							{/foreach}
						</select>
					</li>
					{/if}
					<li><label class=property>Email</label><input name="email" class="simpla_inp" type="text" value="{$user->email|escape}" /></li>
					<li><label class=property>Дата регистрации</label><input name="email" class="simpla_inp" type="text" disabled value="{$user->created|date}" /></li>
					<li><label class=property>Последний IP</label><input name="email" class="simpla_inp" type="text" disabled value="{$user->last_ip|escape}" /></li>
				</ul>
			</div>


			<input class="button_green button_save" type="submit" name="user_info" value="Сохранить" />
		</div>
		

		<!-- Левая колонка свойств товара (The End)--> 

		

		
	</form>
	<!-- Основная форма (The End) -->

	<br style="
	clear: both;
	">
	<hr style="
	margin: 30px 0px;
	">


	
	<div class="block" id="column_left">
		{if $orders}
		<form id="list" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">
			<h2>Заявки пользователя</h2>

			<div>		
				
				{foreach $orders as $order}
				<div class="{if $order->paid}green{/if} row">
					<div class="checkbox cell">
						<input type="checkbox" name="check[]" value="{$order->id}" />				
					</div>
					<div class="order_date cell">
						{$order->date|date} {$order->date|time}
						<br/>
						{if $order->status==0}
						Новый 
						{/if}
						{if $order->status==1}
						Принят 
						{/if}
						{if $order->status==2}
						Выдан 
						{/if}
						{if $order->status==3}
						Отказ 
						{/if}
						{if $order->status==4}
						Удален 
						{/if}
						({$order->status})
					</div>
					<div class="name cell">
						<a href="{url module=OrderAdmin id=$order->id return=$smarty.server.REQUEST_URI}">№{$order->id}{if $order->id_1c}<br/>(1c - {$order->id_1c}){/if}</a>
					</div>
					<div class="name cell">
						{$order->amount}&nbsp;{$currency->sign} (Срок {$order->period})
					</div>
					<div class="icons cell">
						{if $order->paid}
						<img src='design/images/cash_stack.png' alt='Оплачен' title='Оплачен'>
						{else}
						<img src='design/images/cash_stack_gray.png' alt='Не оплачен' title='Не оплачен'>				
						{/if}	
					</div>
					<div class="icons cell">
						<a href='#' class=delete></a>		 	
					</div>
					<div class="clear"></div>
				</div>
				{/foreach}
			</div>

			<div id="action">
				<label id='check_all' class='dash_link'>Выбрать все</label>

				<span id=select>
					<select name="action">
						<option value="delete">Удалить</option>
					</select>
				</span>


				<input id="apply_action" class="button_green" name="user_orders" type="submit" value="Применить">
			</div>
		</form>
		{/if}
		{if $payments}
		<h2>Платежи пользователя</h2>
		{foreach $payments as $p}
		{/foreach}
		{/if}
	</div>
	

	<!-- Параметры страницы -->
	<div id="column_right">
		<div class="block">
			<ul>
				<h2>Контакты</h2>

				<hr/>
				
				<li><label class=property>Сотовый телефон</label>{$user->phone_mobile}</li>
				<li><label class=property>Электронная почта</label>{$user->email}</li>
				<li><label class=property>Фамилия</label>{$user->lastname}</li>
				<li><label class=property>Имя</label>{$user->firstname}</li>
				<li><label class=property>Отчество</label>{$user->patronymic}</li>
				<li><label class=property>Дата рождения</label>{$user->birth}</li>

				<br/><br/>

				<h2>Паспорт</h2>

				<hr/>

				<li><label class=property>Серия Номер паспорта</label>{$user->passport_serial}</li>
				<li><label class=property>Код подразделения</label>{$user->subdivision_code}</li>
				<li><label class=property>Дата выдачи паспорта</label>{$user->passport_date}</li>
				<li><label class=property>Кем выдан</label>{$user->passport_issued}</li>

				<br/><br/>

				<h2>Адрес регистрации</h2>

				<hr/>

				<li><label class=property>Регион</label>{$user->Regregion}</li>
				<li><label class=property></label>{$user->Regdistrict}</li>
				<li><label class=property>Город</label>{$user->Regcity}</li>
				<li><label class=property>Населенный пункт</label>{$user->Reglocality}</li>
				<li><label class=property>Улица</label>{$user->Regstreet}</li>
				<li><label class=property>Дом</label>{$user->Reghousing}</li>
				<li><label class=property>Строение</label>{$user->Regbuilding}</li>
				<li><label class=property>Квартира</label>{$user->Regroom}</li>

				<br/><br/>

				<h2>Адрес фактического проживвания</h2>

				<hr/>

				<li><label class=property>Регион</label>{$user->Faktregion}</li>
				<li><label class=property></label>{$user->Faktdistrict}</li>
				<li><label class=property>Город</label>{$user->Faktcity}</li>
				<li><label class=property>Населенный пункт</label>{$user->Faktlocality}</li>
				<li><label class=property>Улица</label>{$user->Faktstreet}</li>
				<li><label class=property>Дом</label>{$user->Fakthousing}</li>
				<li><label class=property>Строение</label>{$user->Faktbuilding}</li>
				<li><label class=property>Квартира</label>{$user->Faktroom}</li>

				<br/><br/>

				<h2>Данные по сайту и партнеру</h2>

				<hr/>

				<li><label class=property>Сайт</label>{$user->site_id}</li>
				<li><label class=property>Партнер</label>{$user->partner_id}</li>
				<li><label class=property>Название партнера</label>{$user->partner_name}</li>

			</ul>
		</div>
	</div>
	{* On document load *}
	{literal}

	<script>
		$(function() {

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
		$('#list input[type="checkbox"][name*="check"]').attr('checked', $('#list input[type="checkbox"][name*="check"]:not(:checked)').length>0);
	});	

	// Удалить 
	$("a.delete").click(function() {
		$('#list input[type="checkbox"][name*="check"]').attr('checked', false);
		$(this).closest(".row").find('input[type="checkbox"][name*="check"]').attr('checked', true);
		$(this).closest("form#list").find('select[name="action"] option[value=delete]').attr('selected', true);
		$(this).closest("form#list").submit();
	});

	// Подтверждение удаления
	$("#list").submit(function() {
		if($('select[name="action"]').val()=='delete' && !confirm('Подтвердите удаление'))
			return false;	
	});
});

</script>
{/literal}
