{assign var="partners" value=[
['name' => 'Банки.ру', 'rating' => '6.6', 'max_rating' => '10', 'logo' => 'design/orange_theme/img/main_page/design-images/mfo-logo/bankiru.svg', width => "100%"],
['name' => 'Сравни.ру', 'rating' => '4.5', 'max_rating' => '5', 'logo' => 'design/orange_theme/img/main_page/design-images/mfo-logo/sravni.png'],
['name' => 'Google Play', 'rating' => '4.8', 'max_rating' => '5', 'logo' => 'design/orange_theme/img/main_page/design-images/mfo-logo/googleplay.png'],
['name' => 'App Store', 'rating' => '4', 'max_rating' => '5', 'logo' => 'design/orange_theme/img/main_page/design-images/mfo-logo/appstore.svg'],
['name' => 'Яндекс Карты', 'rating' => '3.9', 'max_rating' => '5', 'logo' => 'design/orange_theme/img/main_page/design-images/mfo-logo/yandexmaps.svg', width => "80%"],
['name' => 'Финуслуги', 'rating' => '4.8', 'max_rating' => '5', 'logo' => 'design/orange_theme/img/main_page/design-images/mfo-logo/finuslugi.webp'],
['name' => 'Выберу.ру', 'rating' => '4.8', 'max_rating' => '5', 'logo' => 'design/orange_theme/img/main_page/design-images/mfo-logo/vibery.svg'],
['name' => 'ВКонтакте', 'rating' => '4.9', 'max_rating' => '5', 'logo' => 'design/orange_theme/img/main_page/design-images/mfo-logo/vk.svg']
]}

<section class="partners-ratings section">
    <div class="partners-ratings__container">
        <div class="partners-ratings__grid">
            {foreach from=$partners item=partner}
                <div class="partners-ratings__card">
                    <div class="partners-ratings__left">
                        <div class="partners-ratings__icon">
                            <img class="partners-ratings__logo" alt="{$partner.name}" src="{$partner.logo}" style="width: {$partner.width}">
                        </div>
                    </div>
                    <div class="partners-ratings__right">
                        <div class="partners-ratings__rating">
                            <span class="partners-ratings__rating-value">{$partner.rating}</span>
                            <span class="partners-ratings__rating-divider">/</span>
                            <span class="partners-ratings__rating-max">{$partner.max_rating}</span>
                        </div>
                        <div class="partners-ratings__name">{$partner.name}</div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
</section>