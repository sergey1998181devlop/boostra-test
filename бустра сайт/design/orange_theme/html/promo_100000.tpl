<div class="promo_wrapper">
    <div>
        <a class="get_modal" href="javascript:void(0);"><img src="design/{$settings->theme|escape}/img/banner_promo100000.png" alt="promo 100000 banner" /></a>
    </div>
    <div>
        <a class="get_modal button medium" href="javascript:void(0);">Подробнее</a>
    </div>
    <div id="promo_wrapper" class="white-popup-modal">
        <a href="javascript:void(0);" onclick="$.magnificPopup.close();" class="close">&#9421;</a>
        {include 'promo_100000_page.tpl'}
    </div>
</div>

<script>
    $(".promo_wrapper .get_modal").on('click', function () {
        $.magnificPopup.open({
            items: {
                src: '#promo_wrapper'
            },
            type: 'inline',
            showCloseBtn: true
        });
    });
</script>
