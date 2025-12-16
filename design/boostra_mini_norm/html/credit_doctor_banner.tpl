<div id="creditDoctorBanner" class="additional_service__banner">
    <img src="design/{$settings->theme|escape}/img/banners/credit_doctor_bg.png" alt="Кредитный доктор">
    <div class="additional_service__banner___text">
        <div>
            <h2>Финансовый доктор</h2>
        </div>
        <div class="additional_service__banner___details">
            Поможет вам избавиться<br>от долгов!
        </div>
        {if $payCredit}
            <div class="additional_service__pay__credit__banner___details">
                ПОДКЛЮЧИТЕ<br> БЕСПЛАТНО <b>ТОЛЬКО <br>СЕГОДНЯ!</b>
            </div>
        {/if}
        <div class="additional_service__banner___get">
            {if $payCredit}
                <button class="additional_service_pay_credit_banner_button btn" data-user="{$user->id}" data-event="2"
                        type="button" data-order_id="{$order_id}">Погасить заём
                </button>
            {elseif $has_license}
                <a href="{$license_url}" target="_blank" class="btn">Перейти в систему</a>
            {else}
                <a href="javascript:void(0);" class="btn" id="generateFDKeyBtn">Получить</a>
            {/if}
        </div>
    </div>
</div>

{*{literal}*}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateButton = document.getElementById('generateFDKeyBtn');

            if (generateButton) {
                generateButton.addEventListener('click', function() {
                    sendMetric('reachGoal', 'cd_banner_click_lk');
                    generateFDKey();
                });
            }
        });

        function generateFDKey() {
            $.ajax({
                url: 'ajax/generate_fd_key.php',
                method: 'POST',
                dataType: 'json',
                data: { user_id: '{$user->id}' },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        window.open(response.login_url, '_blank');
                        location.reload();
                    } else {
                        alert(response.message || 'Не удалось сгенерировать ключ');
                    }
                },
                error: function() {
                    alert('Ошибка при запросе на сервер');
                }
            });
        }

        $('body').on('click', '.additional_service_pay_credit_banner_button', function() {
            console.log( $('.full_payment_button[data-order_id="'+$(this).attr('data-order_id')+'"]'))
            $('.full_payment_button[data-order_id="'+$(this).attr('data-order_id')+'"]').click();
        })
    </script>
{*{/literal}*}
