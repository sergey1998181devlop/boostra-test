(function($) {
    $(document).ready(function() {

        var friendPayLinks = {};
        var isLoading = false;

        function logEvent(action, userId, overdueDays) {
            $.post('/ajax/promo_logger.php', {
                action: action,
                user_id: userId,
                overdue_days: overdueDays
            });
        }

        $(document).on('click', '.js-friend-pay', function(e) {
            e.preventDefault();

            var btn = $(this);
            var contractNumber = btn.data('contract');
            var userId = btn.data('user');
            var uid = btn.data('uid');
            var overdueDays = btn.data('overdue');

            logEvent('friend_pay_click', userId, overdueDays);

            if (friendPayLinks[contractNumber]) {
                copyAndShow(btn, friendPayLinks[contractNumber], userId, overdueDays);
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
                    contract_number: contractNumber,
                    user_id: userId,
                    uid: uid
                },
                success: function(response) {
                    if (response.success && response.short_link) {
                        friendPayLinks[contractNumber] = response.short_link;
                        copyAndShow(btn, response.short_link, userId, overdueDays);
                    } else {
                        btn.text('Ошибка, попробуйте позже');
                        btn.prop('disabled', false);
                    }
                },
                error: function() {
                    btn.text('Ошибка, попробуйте позже');
                    btn.prop('disabled', false);
                },
                complete: function() {
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
            } else {
                var tmp = $('<input>');
                $('body').append(tmp);
                tmp.val(link).select();
                document.execCommand('copy');
                tmp.remove();
            }

            btn.closest('.friend_payment_wrapper').find('.friend_payment_block').fadeIn('fast');
        }

        $(document).on('click', '.friend_more_link', function(e) {
            e.preventDefault();
            $(this).hide();
            $(this).closest('.friend_info').find('.friend_more_text').slideDown('fast');
        });

        $(document).on('click', '.friend_ok_btn', function() {
            $(this).closest('.friend_payment_block').fadeOut('fast');
        });

    });
})(jQuery);