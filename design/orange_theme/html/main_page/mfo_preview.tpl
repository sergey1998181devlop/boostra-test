<div class="partner-item">
    <div class="item-header">
        <div class="item-header__image">
            <img style="width: 100%" src="{$partner.logo}" alt="">
        </div>
        <div class="item-header__name">
            <b>{$partner.name}</b>
            {if $partner.percent != '0'}
            {/if}
                <span class="is_noindex">Займ под {$partner.percent}%</span>
            {if $partner.percent != '0'}
            {/if}
        </div>
    </div>
    <div class="item-body">
        <div class="content">
            <a href="{if $partnerDoc}{$partnerDoc}{else}#{/if}" target="_blank" rel="noopener" class="docs-link">
                <div class="docs">Документы</div>
            </a>
        </div>
        <div class="content">
            <div class="ireccommend">
                Рекомендуем
            </div>
            <div class="partner-rating">
                <img alt="" src="design/orange_theme/img/landing/star-icon.svg" loading="lazy">
                {$partner.rating}
            </div>
        </div>
        <div class="content">
            <div class="group">
                <span>Сумма</span>
                <b>до {$partner.amount|number_format:0:" ":" "} ₽</b>
            </div>
            <div class="group">
                <span>Срок</span>
                <b>до {$partner.days}</b>
            </div>
        </div>
        <div class="content">
            <div class="group">
                <span>Ставка</span>
                <b>Бесплатно*</b>
            </div>
            <div class="group">
                <span>Одобрение</span>
                {if $partner.approve == 1}
                    <b class="yellow">Среднее</b>
                {elseif $partner.approve == 2}
                    <b class="green">Отличное</b>
                {else}
                    <b class="red">низкое</b>
                {/if}
            </div>
        </div>
    </div>
    <a class="item-bottom" {if !$same_page} target="_blank" {/if} href="/init_user?amount=30000&period=16" onclick="clickHunter()">
        <div class="submit-offer">
            Получить деньги
        </div>
    </a>
</div>
