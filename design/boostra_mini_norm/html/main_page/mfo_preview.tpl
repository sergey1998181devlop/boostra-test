<div class="partner-item">
    <div class="item-header">
        <div class="item-header__image">
            <img style="width: 100%" src="{$partner.logo}" alt="">
        </div>
        <div class="item-header__name">
            <b>{$partner.name}</b>
            {if $partner.percent != '0'}
                <noindex>
            {/if}
                <span>Займ под {$partner.percent}%</span>
            {if $partner.percent != '0'}
                </noindex>
            {/if}
        </div>
        <div class="item-header__about">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M10 20C4.477 20 0 15.523 0 10C0 4.477 4.477 0 10 0C15.523 0 20 4.477 20 10C20 15.523 15.523 20 10 20ZM10 18C12.1217 18 14.1566 17.1571 15.6569 15.6569C17.1571 14.1566 18 12.1217 18 10C18 7.87827 17.1571 5.84344 15.6569 4.34315C14.1566 2.84285 12.1217 2 10 2C7.87827 2 5.84344 2.84285 4.34315 4.34315C2.84285 5.84344 2 7.87827 2 10C2 12.1217 2.84285 14.1566 4.34315 15.6569C5.84344 17.1571 7.87827 18 10 18ZM11 8.5V13H12V15H8V13H9V10.5H8V8.5H11ZM11.5 6C11.5 6.39782 11.342 6.77936 11.0607 7.06066C10.7794 7.34196 10.3978 7.5 10 7.5C9.60218 7.5 9.22064 7.34196 8.93934 7.06066C8.65804 6.77936 8.5 6.39782 8.5 6C8.5 5.60218 8.65804 5.22064 8.93934 4.93934C9.22064 4.65804 9.60218 4.5 10 4.5C10.3978 4.5 10.7794 4.65804 11.0607 4.93934C11.342 5.22064 11.5 5.60218 11.5 6Z"
                      fill="#818C99"/>
            </svg>
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
