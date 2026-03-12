<div id="payment_methods_modal" class="payment-methods-modal">
    <div class="payment-methods-content">
        <button class="payment-methods-close" onclick="closePaymentMethodsModal()">&times;</button>

        <div class="payment-method">
            <h3>Мгновенные оплаты</h3>
            <p>Оплата через личный кабинет осуществляется мгновенно, что позволяет сразу же погасить текущий займ. После
                оплаты в зависимости от условий компании, у вас может появиться возможность оформить новый займ.</p>
        </div>

        <div class="payment-method">
            <h3>Оплата по реквизитам</h3>
            <p>При оплате по банковским реквизитам возможны дополнительные расходы: комиссия вашего банка может
                достигать до 10%. Дата зачисления платежа считается днем поступления средств на счет Заимодавца, что
                может занимать до 3 рабочих дней в зависимости от банка. Учтите, что в этот период начисляются проценты
                за каждый день задержки. <a href="/user/faq?action=user_section&section_id=12&q=92">Актуальные
                    реквизиты</a>.</p>
            <p class="warning">*Обратите внимание: задержки с банковским переводом и несоблюдение сроков оплаты могут
                быть зафиксированы в базе данных БКИ и негативно повлиять на вашу кредитную историю.</p>
        </div>

        <div class="payment-method">
            <h3>Оплата через Почту России</h3>
            <p>Этот способ занимает до 5 рабочих дней, при этом комиссия отсутствует. Дата зачисления платежа считается
                днем поступления средств на счет Заимодавца, что может занимать до 5 дней в зависимости от банка.
                Учтите, что в этот период начисляются проценты за каждый день задержки.</p>
            <p class="warning">*Обратите внимание: задержки с почтовым переводом и несоблюдение сроков оплаты могут быть
                зафиксированы в базе данных БКИ и негативно повлиять на вашу кредитную историю.</p>
        </div>
    </div>
</div>

<script>
    {literal}

    function openPaymentMethodsModal() {
        const modal = document.getElementById('payment_methods_modal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closePaymentMethodsModal() {
        const modal = document.getElementById('payment_methods_modal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    window.onclick = function (event) {
        const modal = document.getElementById('payment_methods_modal');
        if (event.target == modal) {
            closePaymentMethodsModal();
        }
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closePaymentMethodsModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const openButton = document.querySelector('.payment-methods-open');
        if (openButton) {
            openButton.addEventListener('click', openPaymentMethodsModal);
        }
    });

    {/literal}
</script>