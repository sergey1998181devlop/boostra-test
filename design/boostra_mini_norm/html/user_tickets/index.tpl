{$canonical="user/tickets" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}

{literal}
    <link rel="stylesheet" href="/design/boostra_mini_norm/css/user_tickets.css">
    <link rel="stylesheet" href="/design/boostra_mini_norm/css/select2/select2.min.css"  />
{/literal}

<section id="private">
    <div>
        <div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">

            {include file='user_nav.tpl' current='form'}

            <div class="tickets-container">
                <div class="tickets-header">
                    <h2 class="tickets-title">Управление обращениями</h2>
                    <button class="tickets-btn tickets-btn-primary create-ticket" onclick="openCheckFaqModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-plus" width="16" height="16"
                             viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                             stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Создать обращение
                    </button>
                </div>

                {include 'user_tickets/list.tpl'}
            </div>
        </div>
    </div>
</section>

{include 'user_tickets/check_faq.tpl'}

<div id="createModal" class="ticket-create-modal">
    <div class="ticket-create-modal-content">
        {include 'user_tickets/create.tpl'}
    </div>
</div>


<div id="detailModal" class="tickets-modal-overlay tickets-ticket-modal">
    <div class="tickets-modal-content tickets-ticket-content">
        {include 'user_tickets/detail.tpl'}
    </div>
</div>

{include 'user_tickets/scripts.tpl'}