{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}

{capture name=page_scripts}
<script src="design/{$settings->theme|escape}/js/user.js?v=1.45" type="text/javascript"></script>
<script>
    $(function(){
        $('.toggle_reason').click(function(e){
            e.preventDefault();

            if ($(this).hasClass('open'))
            {
                $(this).removeClass('open');
                $(this).closest('li').find('.loan_reason').slideUp();
            }
            else
            {
                $(this).addClass('open');
                $(this).closest('li').find('.loan_reason').slideDown();
            }
        });
    })
</script>
{/capture}


<style>
    #private .tabs .content > * {
        vertical-align:top;
    }
    .toggle_reason {
        color:#d33;
    }
    .loanhistory {
        width:1000px;
        padding:0;
        margin: 20px 0 0 0;
    }
    .loanhistory li {
        display:flex;
        width:100%;
        border-bottom: 1px solid #ccc;
        padding:10px;
    }
    .loanhistory li:hover {
        background:#efefef;
    }
    .loanhistory li > div {
        display:inline-block;
        vertical-align:middle;
    }
    .loanhistory li div.loan_number {
        width:30%;
        text-align:left;
    }
    .loanhistory li div.loan_organization {
        width:30%;
        text-align:left;
    }
    .loanhistory li div.loan_status {
        width:19%;
        text-align:left;
    }
    .loanhistory li div.loan_date {
        width:27%;
        text-align:left;
    }
    .loanhistory li div.loan_reason {
        width:100%;
        display:none;
        text-align:left;
        padding: 5px;
        box-sizing: border-box;
    }
    .loanhistory li div.contract_number {
        width: 28%;
        text-align: left;
    }
    .loanhistory li div.loan_card {
        width:30%;
        text-align:left;
    }
    @media (max-width:860px)
    {
        .loanhistory {
            width:500px;
        }
        .loanhistory li{
            flex-wrap: wrap;
        }
    }
    @media (max-width:540px)
    {
        .loanhistory {
            width:100%;
        }
        .loanhistory li div.contract_number, .loanhistory li div.loan_card,
        .loanhistory li div.loan_reason, .loanhistory li div.loan_status,
        .loanhistory li div.loan_date, .loanhistory li div.loan_number {
            width: 100%;
            text-align: left;
        }
    }
</style>

<section id="private">


	<div>
		<div class="tabs ">

            {include file='user_nav.tpl' current='user'}

            <div class="content">

				<div class="panel">
					{if $loans}
                    <h1 style="text-align:left">История заявок</h1>
					<div class="list">
						<!--h4>Прочие займы  <span>.</span></h4-->
						<ul class="loanhistory">
							{foreach $loans as $loan}
							<li>
                                <!--
                                <div class="loan_number">
                                    Номер:
                                    {$loan->number}
                                </div>
                                -->
								<div class="loan_date">
									Дата заявки
									<strong>
									{$loan->date|date}
									</strong>
								</div>
                                <div class="loan_organization">
									Компания
									<strong>
									{$loan->organization_name}
									</strong>
								</div>
								<div class="loan_status">
									Статус:
                                    {if $loan->status}
                                        {$loan->status}
                                    {else}
                                        На рассмотрении
                                    {/if}

                                    {if $loan->reason}
                                    <a href="#" class="toggle_reason">Подробнее</a>
                                    {/if}
								</div>
                                <div class="contract_number">
                                    Номер договора: {if empty($loan->contract_id)}—{else}{$loan->contract_id}{/if}
                                </div>
                                {if $loan->card}
                                    <div class="loan_card">
                                        Номер карты : {$loan->card}
                                    </div>
                                {else}
                                    <div class="loan_card">
                                    </div>
                                {/if}
                                {if $loan->reason}
                                <div class="loan_reason">
                                    Причина отказа: {$loan->reason}
                                </div>
                                {/if}
							</li>
							{/foreach}
						</ul>
					</div>
                    {else}
                        <h4>Заявки не найдены</h4>
					{/if}
				</div>


            </div>
		</div>
	</div>
</section>




