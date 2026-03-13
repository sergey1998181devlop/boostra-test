{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user" scope=parent}

{$body_class = "gray" scope=parent}

{$meta_title = "Кабинет заёмщика" scope=parent}

<section id="private">
	<div>
		<div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">
			<div class="nav">
				<ul>
					<li><a href="/user" {if $action=='user'}class="current"{/if}>Текущий займ</a></li>
					{*
					<li><a href="user/history" {if $action == 'history'}class="current"{/if}>История займов</a></li>
					*}
					<li><a href="user/logout">Выйти</a></li>
				</ul>
			</div>
			<div class="content">
				{if $action=="user"}
				<div class="panel">
					{if $user->balance->zaim_number}
					<div class="about">
						<div>Номер займа  <ins>{$user->balance->zaim_number}</ins></div>
					</div>
					<div class="split">
						<ul>
							<li>
								<div>Остаток Основного долга</div>
								<div>{$user->balance->ostatok_od|convert}</div>
							</li>
							<li>
								<div>Остаток Процентов</div>
								<div>{$user->balance->ostatok_percents|convert}</div>
							</li>
							{if $user->balance->ostatok_peni}
							<li>
								<div>Остаток Пени</div>
								<div>{$user->balance->ostatok_peni|convert}</div>
							</li>
							{/if}
							<li>
								<div>Итого</div>
								<div>{($user->balance->ostatok_od+$user->balance->ostatok_percents+$user->balance->ostatok_peni)|convert}</div>
							</li>
						</ul>
					</div>
					<div class="action">
						<a href="repay.html" class="button medium">Погасить займ</a>
						<a href="extend.html" class="button medium">Продлить займ</a>
					</div>
					{else}
					<div class="about">
						<div>Открытых займов не найдено</div>
					</div>
					{/if}
				</div>
				{elseif $action=="history"}
				<div class="panel">
					{if $current_orders}
					<div class="list current">
						<h4>Открытый займ.</h4>
						<ul class="table">
							{foreach $current_orders as $order}
							<li>
								<div>
									<span class="card master">
									</span>
								</div>
								<div>
									Займ на 
									<strong>{$order->amount|convert} {$currency->sign|escape}</strong>
								</div>
								<div>
									Заявка
									<a href='order/{$order->url}'>
									<strong>{$order->id}</strong>
									</a>
								</div>
								<div>
									Дата заявки
									<strong>
									{$order->date|date}
									</strong>
								</div>
								<div>
								{*
									Просрочен на
									<strong>2 дня</strong>
									*}
								</div>
								<div>
									<a href="repay.html" class="popup button">Погасить</a>
								</div>
							</li>
							{/foreach}
						</ul>
					</div>
					{/if}
					{if $orders}
					<div class="list">				
						<h4>Прочие займы  <span>.</span></h4>
						<ul class="table">
							{foreach $orders as $order}
							{if $order->status != 4}
							<li>
								<div>
									<span class="card visa">
										
									</span>
								</div>
								<div>
									Займ на 
									<strong>{$order->amount|convert} {$currency->sign|escape}</strong>
								</div>
								<div>
									Заявка
									<a href='order/{$order->url}'>
									<strong>{$order->id}</strong>
									</a>
								</div>
								<div>
									Дата заявки
									<strong>
									{$order->date|date}
									</strong>
								</div>
								<div>
									{if $order->paid == 1}оплачен,{/if} 
									{if $order->status == 0}
									ждет обработки
									{elseif $order->status == 1}в обработке
									{elseif $order->status == 3}погашен
									{/if}
									{*
										Просрочен на
										<strong>4 дня</strong>
										*}
								</div>
								<div>
									{*
									Дата погашения
									<strong>10.02.2017</strong>
									*}
								</div>
							</li>
							{/if}
							{/foreach}
						</ul>
					</div>
					{/if}
				</div>
				{/if}

			</div>
		</div>
	</div>
</section>