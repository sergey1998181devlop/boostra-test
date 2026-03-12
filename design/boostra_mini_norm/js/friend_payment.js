(function ($) {
    $(document).ready(function () {

        var friendPayLinks = {};
        var isLoading = false;

        function logEvent(action, userId, overdueDays) {
            $.post('/ajax/promo_logger.php', {
                action: action,
                user_id: userId,
                overdue_days: overdueDays
            });
        }

        $(document).on('click', '.js-friend-pay', function (e) {
            e.preventDefault();

            var btn = $(this);
            var wrapper = btn.closest('.friend_payment_wrapper');

            wrapper.find('.friend_payment_block').slideDown(200);

            var userId = btn.data('user-id');
            var uid = btn.data('uid');
            var overdueDays = btn.data('overdue-days');
            var phone = btn.data('phone');
            var orderId =
                btn.data('order-id') ||
                $('input[name="order_id"]').val();

            logEvent('friend_pay_click', userId, overdueDays);

            if (friendPayLinks[orderId]) {
                copyAndShow(btn, friendPayLinks[orderId], userId, overdueDays);
                return;
            }

            if (isLoading) return;

            isLoading = true;
            btn.prop('disabled', true).text('Загрузка...');

            $.ajax({
                url: '/ajax/get_friend_payment_link.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    user_id: userId,
                    uid: uid,
                    overdue_days: overdueDays,
                    phone: phone,
                    order_id: orderId
                },
                success: function (response) {
                    if (response.success && response.short_link) {
                        friendPayLinks[orderId] = response.short_link;
                        copyAndShow(btn, response.short_link, userId, overdueDays);
                    } else {
                        btn.text('Ошибка, попробуйте позже');
                        btn.prop('disabled', false);
                    }
                },
                error: function () {
                    btn.text('Ошибка, попробуйте позже');
                    btn.prop('disabled', false);
                },
                complete: function () {
                    isLoading = false;
                }
            });
        });

        function copyAndShow(btn, link, userId, overdueDays) {
            btn.text(link + ' ✔');
            btn.prop('disabled', false);

            logEvent('friend_pay_copy', userId, overdueDays);

            if (navigator.clipboard) {
                navigator.clipboard.writeText(link);
            }
        }

        $(document).on('click', '.friend_more_link', function (e) {
            e.preventDefault();

            var link = $(this);
            var text = link.closest('.friend_info').find('.friend_more_text');

            text.slideDown(200);
            link.hide();
        });

        $(document).on('click', '.friend_ok_btn', function () {
            var wrapper = $(this).closest('.friend_payment_wrapper');

            wrapper.find('.friend_payment_block').slideUp(200);
        });

    });
})(jQuery);
