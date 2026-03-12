    <div class="friend_payment_wrapper">

        <div class="friend_action">
            <button
                    type="button"
                    class="payment_friend_button button js-friend-pay"
                    data-user-id="{$user->id}"
                    data-uid="{$user->uid}"
                    data-phone="{$user->phone_mobile}"
                    data-order-id="{$order_data->order->order_id}"
                    data-overdue-days="{$overdue_days}"
            >
                <img src="/design/boostra_mini_norm/assets/image/friend_payment.png" alt="" class="friend_btn_icon">
                Оплатит друг
            </button>

            <div class="friend_payment_loader">
                <div class="friend_payment_spinner"></div>
                <span class="friend_loader_text">Загрузка...</span>
            </div>
        </div>

        <div class="friend_payment_block" style="display:none;">
            <div class="friend_info">

                <div class="friend_info_title">
                    Поделитесь ссылкой на оплату
                </div>

                <div class="friend_info_subtitle">
                    Ваши персональные данные не раскрываем.
                </div>

                <a href="javascript:void(0)" class="friend_more_link">
                    Подробнее
                </a>

                <div class="friend_more_text" style="display:none;">
                    Отправляя ссылку, Вы даете согласие на передачу следующей информации о Вас и Вашем договоре третьим лицам для целей оплаты вашего займа:
                    Ваше Имя и Отчество, первая буква Фамилии, номер договора, общая сумма задолженности и дата платежа.
                    Вы осознаете, что данная информация будет доступна лицам, которым Вы направите ссылку.
                </div>

                <button type="button" class="friend_ok_btn">
                    Понятно
                </button>

            </div>
        </div>

    </div>



