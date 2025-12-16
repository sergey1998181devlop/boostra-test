{* Вкладки *}
{capture name=tabs}
	{if in_array('orders', $manager->permissions)}
		<li {if $order->status==0}class="active"{/if}><a href="index.php?module=OrdersAdmin&status=0">Новые</a></li>
		<li {if $order->status==1}class="active"{/if}><a href="index.php?module=OrdersAdmin&status=1">Приняты</a></li>
		<li {if $order->status==2}class="active"{/if}><a href="index.php?module=OrdersAdmin&status=2">Выданы</a></li>
		<li {if $order->status==3}class="active"{/if}><a href="index.php?module=OrdersAdmin&status=3">Отказаны</a></li>
		<li {if $order->status==4}class="active"{/if}><a href="index.php?module=OrdersAdmin&status=4">Удалены</a></li>
	{if $keyword}
	<li class="active"><a href="{url module=OrdersAdmin keyword=$keyword id=null label=null}">Поиск</a></li>
	{/if}
	{/if}
	{if in_array('labels', $manager->permissions)}
	<li><a href="{url module=OrdersLabelsAdmin keyword=null id=null page=null label=null}">Метки</a></li>
	{/if}
{/capture}


{if $order->id}
{$meta_title = "Займ №`$order->id`" scope=parent}
{else}
{$meta_title = 'Новый займ' scope=parent}
{/if}

<!-- Основная форма -->
<form method=post id=order enctype="multipart/form-data">
<input type=hidden name="session_id" value="{$smarty.session.id}">

<div id="name">
	<input name=id type="hidden" value="{$order->id|escape}"/> 
	<h1>{if $order->id}Займ №{$order->id|escape}{else}Новый Займ{/if}
	<select class=status name="status">
		<option value='0' {if $order->status == 0}selected{/if}>Новый</option>
		<option value='1' {if $order->status == 1}selected{/if}>Принят</option>
		<option value='2' {if $order->status == 2}selected{/if}>Выдан</option>
		<option value='3' {if $order->status == 3}selected{/if}>Отказ</option>
		<option value='4' {if $order->status == 4}selected{/if}>Удален</option>
	</select>
	</h1>
	<a href="{url view=print id=$order->id}" target="_blank"><img src="./design/images/printer.png" name="export" title="Печать Займа"></a>


	<div id=next_order>
		{if $prev_order}
		<a class=prev_order href="{url id=$prev_order->id}">←</a>
		{/if}
		{if $next_order}
		<a class=next_order href="{url id=$next_order->id}">→</a>
		{/if}
	</div>
		
</div> 


{if $message_error}
<!-- Системное сообщение -->
<div class="message message_error">
	<span class="text">{if $message_error=='error_closing'}Нехватка товара на складе{else}{$message_error|escape}{/if}</span>
	{if $smarty.get.return}
	<a class="button" href="{$smarty.get.return}">Вернуться</a>
	{/if}
</div>
<!-- Системное сообщение (The End)-->
{elseif $message_success}
<!-- Системное сообщение -->
<div class="message message_success">
	<span class="text">{if $message_success=='updated'}Займ обновлен{elseif $message_success=='added'}Займ добавлен{else}{$message_success}{/if}</span>
	{if $smarty.get.return}
	<a class="button" href="{$smarty.get.return}">Вернуться</a>
	{/if}
</div>
<!-- Системное сообщение (The End)-->
{/if}



<div id="order_details">
	<h2>Детали Займа</h2>
	
	<div id="user">
	<ul class="order_details">
		<li>
			<label class=property>Дата</label>
			<div class="edit_order_detail view_order_detail">
			{$order->date} {$order->time}
			</div>
		</li>
		<li>
			<label class=property>Ip</label>
			<div class="edit_order_detail view_order_detail">
			{$order->ip}
			</div>
		</li>
	</ul>
	</div>

	
	{if $labels}
	<div class='layer'>
	<h2>Метка</h2>
	<!-- Метки -->
	<ul>
		{foreach $labels as $l}
		<li>
		<label for="label_{$l->id}">
		<input id="label_{$l->id}" type="checkbox" name="order_labels[]" value="{$l->id}" {if in_array($l->id, $order_labels)}checked{/if}>
		<span style="background-color:#{$l->color};" class="order_label"></span>
		{$l->name}
		</label>
		</li>
		{/foreach}
	</ul>
	<!-- Метки -->
	</div>
	{/if}

	
	<div class='layer'>
	<h2>Заемщик <a href='#' class="edit_user"><img src='design/images/pencil.png' alt='Редактировать' title='Редактировать'></a> {if $user}<a href="#" class='delete_user'><img src='design/images/delete.png' alt='Удалить' title='Удалить'></a>{/if}</h2>
		<div class='view_user'>
		{if !$user}
			Не зарегистрирован
		{else}
			<a href='index.php?module=UserAdmin&id={$user->id}' target=_blank>{$user->lastname} {$user->firstname} {$user->patronymic}</a> <br/>{$user->phone_mobile|escape}
		{/if}
		</div>
		<div class='edit_user' style='display:none;'>
		<input type=hidden name=user_id value='{$user->id}'>
		<input type=text id='user' class="input_autocomplete" placeholder="Выберите пользователя">
		</div>
	</div>
	

	
	<div class='layer'>
	<h2>Примечание <a href='#' class="edit_note"><img src='design/images/pencil.png' alt='Редактировать' title='Редактировать'></a></h2>
	<ul class="order_details">
		<li>
			<div class="edit_note" style='display:none;'>
				<label class=property>Ваше примечание (не видно пользователю)</label>
				<textarea name="note">{$order->note|escape}</textarea>
			</div>
			<div class="view_note" {if !$order->note}style='display:none;'{/if}>
				<label class=property>Ваше примечание (не видно пользователю)</label>
				<div class="note_text">{$order->note|escape}</div>
			</div>
		</li>
	</ul>
	</div>
		
</div>


<div id="purchases">

	{*

	<div class="block discount layer">
		<h2>Скидка</h2>
		<input type=text name=discount value='{$order->discount}'> <span class=currency>%</span>		
	</div>

	<div class="subtotal layer">
	С учетом скидки<b> {($subtotal-$subtotal*$order->discount/100)|round:2} {$currency->sign}</b>
	</div> 
	
	<div class="block discount layer">
		<h2>Купон{if $order->coupon_code} ({$order->coupon_code}){/if}</h2>
		<input type=text name=coupon_discount value='{$order->coupon_discount}'> <span class=currency>{$currency->sign}</span>		
	</div>

	<div class="subtotal layer">
	С учетом купона<b> {($subtotal-$subtotal*$order->discount/100-$order->coupon_discount)|round:2} {$currency->sign}</b>
	</div> 

	

	<div class="total layer">
	Итого<b> {$order->total_price} {$currency->sign}</b>
	</div>

	*}

	<div class="block">
		<ul>
			<h2>Информация о займе</h2>

			<hr/>

			<li><label class=property>Желаемая сумма</label><b>{$order->amount}</b></li>
			<li><label class=property>Срок</label><b>{$order->period}</b></li>

			<br/><br/><br/>

			<h2>Информация о заемщике</h2>

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

			<li><label class=property>Серия Номер паспорта</label>{$user->passportCode}</li>
			<li><label class=property>Код подразделения</label>{$user->subdivisionCode}</li>
			<li><label class=property>Дата выдачи паспорта</label>{$user->passportDate}</li>

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
			<li><label class=property></label>Заявка(юзер)</li>
			<li><label class=property>utm_source</label>{$order->utm_source} ({$user->utm_source})</li>
			<li><label class=property>utm_medium</label>{$order->utm_medium} ({$user->utm_medium})</li>
			<li><label class=property>utm_campaign</label>{$order->utm_campaign} ({$user->utm_campaign})</li>
			<li><label class=property>utm_content</label>{$order->utm_content} ({$user->utm_content})</li>
			<li><label class=property>utm_term</label>{$order->utm_term} ({$user->utm_term})</li>
			<li><label class=property>webmaster_id</label>{$order->webmaster_id} ({$user->webmaster_id})</li>
			<li><label class=property>click_hash</label>{$order->click_hash} ({$user->click_hash})</li>

			<br/><br/><hr/>

		</ul>
	</div>
	
		
		
	<div class="block payment">
		<h2>Оплата</h2>
				<select name="payment_method_id">
				<option value="0">Не выбрана</option>
				{foreach $payment_methods as $pm}
				<option value="{$pm->id}" {if $pm->id==$payment_method->id}selected{/if}>{$pm->name}</option>
				{/foreach}
				</select>
		
		<input type=checkbox name="paid" id="paid" value="1" {if $order->paid}checked{/if}> <label for="paid" {if $order->paid}class="green"{/if}>Займ оплачен</label>		
	</div>

 
	{if $payment_method}
	<div class="subtotal layer">
	К оплате<b> {$order->total_price|convert:$payment_currency->id} {$payment_currency->sign}</b>
	</div>
	{/if}


	<div class="block_save">
	<input type="checkbox" value="1" id="notify_user" name="notify_user">
	<label for="notify_user">Уведомить покупателя о состоянии Займа</label>

	<input class="button_green button_save" type="submit" name="" value="Сохранить" />
	</div>


</div>


</form>
<!-- Основная форма (The End) -->


{* On document load *}
{literal}
<script src="design/js/autocomplete/jquery.autocomplete-min.js"></script>

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
	
	// Удаление товара
	$(".purchases a.delete").live('click', function() {
		 $(this).closest(".row").fadeOut(200, function() { $(this).remove(); });
		 return false;
	});
 

	// Добавление товара 
	var new_purchase = $('.purchases #new_purchase').clone(true);
	$('.purchases #new_purchase').remove().removeAttr('id');

	$("input#add_purchase").autocomplete({
  	serviceUrl:'ajax/add_order_product.php',
  	minChars:0,
  	noCache: false, 
  	onSelect:
  		function(suggestion){
  			new_item = new_purchase.clone().appendTo('.purchases');
  			new_item.removeAttr('id');
  			new_item.find('a.purchase_name').html(suggestion.data.name);
  			new_item.find('a.purchase_name').attr('href', 'index.php?module=ProductAdmin&id='+suggestion.data.id);
  			
  			// Добавляем варианты нового товара
  			var variants_select = new_item.find('select[name*=purchases][name*=variant_id]');
			for(var i in suggestion.data.variants)
			{
				sku = suggestion.data.variants[i].sku == ''?'':' (арт. '+suggestion.data.variants[i].sku+')';
  				variants_select.append("<option value='"+suggestion.data.variants[i].id+"' price='"+suggestion.data.variants[i].price+"' amount='"+suggestion.data.variants[i].stock+"'>"+suggestion.data.variants[i].name+sku+"</option>");
  			}
  			
  			if(suggestion.data.variants.length>1 || suggestion.data.variants[0].name != '')
  				variants_select.show();
  				  				
			variants_select.bind('change', function(){change_variant(variants_select);});
				change_variant(variants_select);
  			
  			if(suggestion.data.image)
  				new_item.find('img.product_icon').attr("src", suggestion.data.image);
  			else
  				new_item.find('img.product_icon').remove();

			$("input#add_purchase").val('').focus().blur(); 
  			new_item.show();
  		},
		formatResult:
			function(suggestion, currentValue){
				var reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
				var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
  				return (suggestion.data.image?"<img align=absmiddle src='"+suggestion.data.image+"'> ":'') + suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>');
			}
  		
  });
  
  // Изменение цены и макс количества при изменении варианта
  function change_variant(element)
  {
		price = element.find('option:selected').attr('price');
		amount = element.find('option:selected').attr('amount');
		element.closest('.row').find('input[name*=purchases][name*=price]').val(price);
		
		// 
		amount_select = element.closest('.row').find('select[name*=purchases][name*=amount]');
		selected_amount = amount_select.val();
		amount_select.html('');
		for(i=1; i<=amount; i++)
			amount_select.append("<option value='"+i+"'>"+i+" {/literal}{$settings->units}{literal}</option>");
		amount_select.val(Math.min(selected_amount, amount));


		return false;
  }
  
  
	// Редактировать покупки
	$("a.edit_purchases").click( function() {
		 $(".purchases span.view_purchase").hide();
		 $(".purchases span.edit_purchase").show();
		 $(".edit_purchases").hide();
		 $("div#add_purchase").show();
		 return false;
	});
  
	// Редактировать получателя
	$("div#order_details a.edit_order_details").click(function() {
		 $("ul.order_details .view_order_detail").hide();
		 $("ul.order_details .edit_order_detail").show();
		 return false;
	});
  
	// Редактировать примечание
	$("div#order_details a.edit_note").click(function() {
		 $("div.view_note").hide();
		 $("div.edit_note").show();
		 return false;
	});
  
	// Редактировать пользователя
	$("div#order_details a.edit_user").click(function() {
		 $("div.view_user").hide();
		 $("div.edit_user").show();
		 return false;
	});
	$("input#user").autocomplete({
		serviceUrl:'ajax/search_users.php',
		minChars:0,
		noCache: false, 
		onSelect:
			function(suggestion){
				$('input[name="user_id"]').val(suggestion.data.id);
			}
	});
  
	// Удалить пользователя
	$("div#order_details a.delete_user").click(function() {
		$('input[name="user_id"]').val(0);
		$('div.view_user').hide();
		$('div.edit_user').hide();
		return false;
	});

	// Посмотреть адрес на карте
	$("a#address_link").attr('href', 'http://maps.yandex.ru/?text='+$('#order_details textarea[name="address"]').val());
  
	// Подтверждение удаления
	$('select[name*=purchases][name*=variant_id]').bind('change', function(){change_variant($(this));});
	$("input[name='status_deleted']").click(function() {
		if(!confirm('Подтвердите удаление'))
			return false;	
	});

});

</script>

<style>
.autocomplete-suggestions{
background-color: #ffffff;
overflow: hidden;
border: 1px solid #e0e0e0;
overflow-y: auto;
}
.autocomplete-suggestions .autocomplete-suggestion{cursor: default;}
.autocomplete-suggestions .selected { background:#F0F0F0; }
.autocomplete-suggestions div { padding:2px 5px; white-space:nowrap; }
.autocomplete-suggestions strong { font-weight:normal; color:#3399FF; }
</style>
{/literal}

