{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}
{*$add_order_css_js = true scope=parent*}

{capture name=page_scripts}
<script>
    $(function(){
        $('#personal_data').submit(function(){
            sendMetric('reachGoal', 'etap-predreshenie');
        })
    })
</script>
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			{if $amount > 0}
            
            <hgroup>
				<h1 class="green">Займ предварительно одобрен</h1>
				<h5>Привяжите карту для быстрого получения денег.</h5>
			</hgroup>

			<form method="post" id="personal_data"> 

                <input type="hidden" value="accept_data" name="stage" />
                
				<div id="steps">
                    <fieldset style="display:block">
                        <div>
                            <p></p>
                            <p>Сумма займа: {$amount} P</p>
                            <p>Срок займа: {$period} {$period|plural:'день':'дней':'дня'}</p>
                            <p>Проценты: {$percent} P</p>
                            <p>Вернуть: {$amount+$percent} P</p>                
                        </div>
                        
                        <div class="next">
            				<button class="button big" id="doit" type="submit" name="neworder">Получить деньги</button>	
            			</div>
                        
                    </fieldset>
                </div>
    		</form>
            
            {else}
            
            <hgroup>
				<h1 class="red">Отказано в займе</h1>
				<h5>Наши партнеры могут одобрить {$partner_amount} P.</h5>

				<div id="steps">
                    <fieldset style="display:block">
                        
                        <div class="next">
            				<a class="button big" id="doit" href="https://112credit.ru/">Получить деньги</a>	
            			</div>
                        
                    </fieldset>
                </div>

			</hgroup>
            
            {/if}
        </div>
	</div>
</section>