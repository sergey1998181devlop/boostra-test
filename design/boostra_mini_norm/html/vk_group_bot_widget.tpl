{* Виджет с сообществом бустры - Подписка на общение с ботом *}

<div id="vk_bot_modal" class="wrapper_border-green white-popup-modal wrapper_border-green mfp-hide">
    <div class="modal-close-btn hidden" onclick="$.magnificPopup.close();">
        <img alt="Закрыть" src="/design/{$settings->theme}/img/user_credit_doctor/close.png" />
    </div>
    <div class="modal-header">
        <h4>Проведите время с пользой</h4>
    </div>
    <div>
        <p>Наш умный бот может дать вам <strong>бесплатный совет</strong> о том, как улучшить кредитную историю!</p>
        <h5 class="text-green">Напишите <strong>"Привет"</strong></h5>
        <div id="vk_group_bot"></div>
    </div>
</div>

<style>
    #vk_bot_modal {
        margin: auto;
        padding-bottom: 0;
    }

    #vk_group_bot {
        position: relative !important;
        margin-top: 1rem ! Important;
        width: 100% !important;
        text-align: center;
        right: 0 !important;
    }
</style>

<script src="https://vk.com/js/api/openapi.js?169" type="text/javascript"></script>
<script type="text/javascript" defer>
    let vk_widget_allowed = true;

    const VkBotWidget = VK.Widgets.CommunityMessages("vk_group_bot", 228140726, {
        'welcomeScreen': 0,
        'expanded': 1,
        'expandTimeout': 0,
        'disableNewMessagesSound': 1,
        'disableTitleChange': 1,
        'chatJoinHash': 'test_hash',
        'buttonType': 'no_button',
        'onCanNotWrite': function (reason) {
            vk_widget_allowed = false;
        }
    });

    let vk_widget_pause_until = localStorage.getItem("vk_widget_pause_until");
    let vk_widget_counter = localStorage.getItem("vk_widget_counter");
    let current_timestamp = Math.floor(Date.now() / 1000);

    if (!vk_widget_counter)
        vk_widget_counter = 0;

    if (vk_widget_pause_until) {
        if (vk_widget_pause_until - current_timestamp < 0) {
            vk_widget_pause_until = null;
            vk_widget_counter = 0;
            localStorage.setItem("vk_widget_pause_until", vk_widget_pause_until);
        }
        else {
            vk_widget_allowed = false;
        }
    }

    vk_widget_counter += 1;
    if (vk_widget_counter > 3 && vk_widget_allowed) {
        vk_widget_counter = 0;
        vk_widget_pause_until = current_timestamp + 86400; // Прекращаем показ на сутки
        localStorage.setItem("vk_widget_pause_until", vk_widget_pause_until);
        vk_widget_allowed = false;
    }
    localStorage.setItem("vk_widget_counter", vk_widget_counter);


    setTimeout(function () {
        if (!vk_widget_allowed)
            return;

        $.magnificPopup.open({
            items: {
                src: '#vk_bot_modal'
            },
            type: 'inline',
            showCloseBtn: false,
            closeOnBgClick: false,
            enableEscapeKey: false,
            callbacks: {
                open: function() {
                    setTimeout(function () {
                        if (!vk_widget_allowed)
                            $.magnificPopup.close();
                        else
                            $('#vk_bot_modal .modal-close-btn').removeClass('hidden');
                    }, 3000);
                },
            }
        });
    }, 6000);
</script>