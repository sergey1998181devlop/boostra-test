{literal}
<style>
    #rs_payment_modal {
        width: 100%;
        background: #fff;
        padding: 25px 30px;
        border-radius: 18px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        font-family: 'Circe', sans-serif;
        position: relative;
        margin: 0 auto !important;
        left: 0 !important;
        right: 0 !important;
    }

    #rs_payment_modal h3 {
        font-size: 20px;
        margin-bottom: 18px;
        color: #2E2E2E;
        text-align: center;
    }

    #rs_payment_modal label {
        display: block;
        margin-top: 12px;
        font-size: 15px;
        font-weight: 500;
    }

    #rs_payment_modal select,
    #rs_payment_modal input[type="file"] {
        width: 100%;
        margin-top: 6px;
        padding: 8px 12px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 10px;
        background: #f9f9f9;
    }

    #rs_payment_modal .button.green {
        background-color: #00BB00;
        color: white;
        font-size: 16px;
        font-weight: 600;
        padding: 12px 28px;
        border: none;
        border-radius: 24px;
        margin-top: 20px;
        box-shadow: 0 4px 12px rgba(0, 187, 0, 0.3);
        transition: background-color 0.3s ease;
    }

    #rs_payment_modal .button.green:hover {
        background-color: #00a000;
    }

    .text-center {
        text-align: center;
    }

    .loader {
        margin-top: 15px;
    }

    .spinner {
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        border-top: 4px solid #3498db;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(-360deg); }
    }

    #rs_payment_modal .close-modal {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
    }
</style>
{/literal}

<div id="rs_payment_modal" class="mfp-hide white-popup-modal wrapper_border-green">
    <h3>Обратите внимание:</h3>
    <h6 style="margin-block-start: 0; margin-block-end: 0.5em;">Фиксация оплаты — это уведомление о том, что Вы произвели платёж.</h6>
    <h6 style="margin-block-start: 0.5em; margin-block-end: 0;">Платёж зачисляется на наш счёт в течение до 3 рабочих дней, в зависимости от банка и способа перевода.</h6>
    <span class="close-modal" onclick="$.magnificPopup.close();">&times;</span>
    <div class="help-text" style="color: #666; font-size: 14px; margin: 10px 0 15px; padding: 10px; background-color: #f5f5f5; border-left: 3px solid #ccc; border-radius: 3px;">
        <i class="fa fa-info-circle" style="margin-right: 5px; color: #3498db;"></i>
        Этот раздел предназначен только для фиксации оплаты, произведённой по банковским реквизитам в ручном режиме (например, через мобильное приложение или отделение банка).<br><br>
        Пожалуйста, не загружайте сюда чеки от оплат, совершённых через Личный кабинет или по кнопке "Best2Pay" — такие платежи фиксируются автоматически и не требуют подтверждения.<br><br>
        Также обращаем внимание:<br>
        — Загружать чек необходимо только при оплате полной суммы задолженности.<br>
        — Частичные платежи в обработку не принимаются.<br><br>
        Выберите номер договора и прикрепите чек для сверки.
    </div>
    <div class="rs-payment-form-container">
        <form action="/ajax/UploadPaymentRsHandler.php" method="POST" enctype="multipart/form-data" class="rs-payment-form">
            <input type="hidden" name="order_id" id="rs_order_id" value="">
            <input type="hidden" name="user_id" value="{$user->id}">

            <label for="rs_payment_contract">Номер договора:</label>
            <select name="contract_number" id="rs_payment_contract" required>
                {foreach $all_orders as $orders_data}
                    {foreach $orders_data as $order_data}
                        {if $order_data->balance->zaim_number != null}
                            <option
                                    value="{$order_data->balance->zaim_number}"
                                    data-order-id="{$order_data->order->order_id}">
                                {$order_data->balance->zaim_number}
                            </option>
                        {/if}
                    {/foreach}
                {/foreach}
            </select>

            <label for="rs_payment_file">Файл</label>
            <input type="file" name="rs_file" id="rs_payment_file" accept=".png,.jpg,.jpeg,.heic,.heif,.pdf,.doc,.docx,image/*" required>
            <div class="file-size-info" style="font-size: 12px; color: #666; margin-top: 5px;">Максимальный размер файла: 100 МБ</div>
            <div id="rs_file_error" style="font-size: 14px; color: red; display: none; margin-top: 5px;"></div>

            <div class="text-center">
                <button type="submit" class="button green">Отправить</button>
                <div id="rs_loader" class="loader" style="display: none;">
                    <div class="spinner"></div>
                    <span>Загрузка...</span>
                </div>
            </div>
        </form>
    </div>
</div>

