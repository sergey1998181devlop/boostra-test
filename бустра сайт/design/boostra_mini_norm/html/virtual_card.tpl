<link rel="stylesheet" type="text/css" href="/design/{$settings->theme}/js/virtual_card/built/index.css?v=2">

<div class="vc-container">
    <div id="virCardData"
         data-theme="{$settings->theme|escape:'html'}"
         data-status="{$virtual_card_data.status|escape:'html'}"
         data-balance="{$virtual_card_data.balance|default:0}"
         data-pan="{$virtual_card_data.pan|escape:'html'}"
         data-is-credited="{if $virtual_card_data.isCredited}true{else}false{/if}"
    >
    </div>

    <div class="sidebar">
        {include file='user_tab.tpl'}
    </div>

    <div id="vc-app"></div>
</div>

<script defer src="/design/{$settings->theme}/js/virtual_card/index.js"></script>
