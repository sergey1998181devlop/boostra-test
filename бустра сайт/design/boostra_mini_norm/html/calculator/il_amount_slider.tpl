{*Юегунок изменения суммы для ИЛ займов*}

<link rel="stylesheet" href="/design/orange_theme/html/calculator/calculator.css?v=1.101" />

{literal}
    <style>
        .range {
            display: -webkit-box; /* Для Safari */
            display: -ms-flexbox; /* Для IE10 */
            display: flex;
            position: relative;
            z-index: 1;
            height: 24px;
            -webkit-box-align: center; /* Для Safari */
            -ms-flex-align: center; /* Для IE10 */
            align-items: center;
            -webkit-box-pack: center; /* Для Safari */
            -ms-flex-pack: center; /* Для IE10 */
            justify-content: center;
        }

        .range input[type=range] {
            -webkit-appearance: none; /* Для Safari/Chrome */
            -moz-appearance: none; /* Для Firefox */
            appearance: none;
            cursor: pointer;
            width: 100%;
            margin: 0;
            background-color: #dfefff;
            background-image: -webkit-linear-gradient(left, #038aee, #038aee); /* Для Safari */
            background-image: -o-linear-gradient(left, #038aee, #038aee); /* Для Opera */
            background-image: linear-gradient(to right, #038aee, #038aee);
            background-size: 0% 100%;
            background-repeat: no-repeat;
            border-radius: 15px;
            height: 7px; /* Единая высота для всех браузеров */
        }

        .range input[type=range]::-webkit-slider-runnable-track {
            height: 7px;
            border-radius: 15px;
            background: transparent; /* Убираем фон у трека */
        }

        .range input[type=range]::-moz-range-track {
            height: 7px;
            border-radius: 15px;
            background: transparent;
        }

        .range input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            margin-top: -15px;
            background-color: #038aee;
            border: 6px solid #ffffff;
            box-shadow: 0 0 16px rgba(0, 0, 0, 0.16); /* Заменяем hex на rgba */
            -webkit-box-shadow: 0 0 16px rgba(0, 0, 0, 0.16);
            -moz-box-shadow: 0 0 16px rgba(0, 0, 0, 0.16);
            border-radius: 50%;
            width: 36px;
            height: 36px;
        }

        .range input[type=range]::-moz-range-thumb {
            background-color: #038aee;
            border: 6px solid #ffffff;
            box-shadow: 0 0 16px rgba(0, 0, 0, 0.16);
            -moz-box-shadow: 0 0 16px rgba(0, 0, 0, 0.16);
            border-radius: 50%;
            width: 36px;
            height: 36px;
        }

        /* Для Firefox - стилизация трека */
        .range input[type=range]::-moz-range-progress {
            background-color: #038aee;
            height: 7px;
            border-radius: 15px;
        }

        .il_amount_slider {
            width: 100%;
            padding: 28px 22px;
        }

        .il_amount_slider input {
            background: initial;
            box-sizing: border-box;
            font-size: 16px;
            color: initial;
            border: none;
            border-radius: initial;
            padding: initial;
            width: 100% !important;
        }

        .calculator__slider {
            margin-bottom: 27px;
            display: flex;
            flex-direction: column;
            gap: 12px
        }

        .calculator__slider-top, .calculator__slider-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .calculator__slider-top {
            line-height: 16px
        }

        .calculator__slider-top b {
            font-weight: 800;
            font-size: 24px
        }

        .calculator__slider-bottom {
            color: #6f7985;
            font-size: 12px
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
{/literal}

<div class="il_amount_slider" x-cloak x-data="ilAmountSlider({ldelim}amount: {$il_approved_amount}, maxAmount: {$il_approved_amount}, minAmount: 31000, orderId: {$order->id}{rdelim})">
    <div class="calculator">
        <div class="calculator__slider">
            <div class="calculator__slider-top">
                <span>Выберите сумму</span>
                <b x-text="formatNumber(amount)"> ₽</b>
            </div>
            <div class="range">
                <input type="range"
                       id="hero-range"
                       x-model="amount"
                       :min="minAmount"
                       :max="{$il_approved_amount}"
                       :step="1000"
                />
            </div>
            <div class="calculator__slider-bottom">
                <span x-text="minAmount"></span>
                <span>{$il_approved_amount}</span>
            </div>
        </div>
    </div>
    <button :disabled="loadingButton" :loading="loadingButton"  @click="submitChangeAmount" class="btn btn-primary">
        Подтверждаю сумму
    </button>
</div>

{literal}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ilAmountSlider', (config) => ({
                amount: config.amount,
                orderId: config.orderId,
                minAmount: config.minAmount,
                maxAmount: config.maxAmount,
                loadingButton: false,
                formatNumber(num) {
                    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                },
                submitChangeAmount() {
                    const amount = parseInt(this.amount, 10);
                    const order_id = this.orderId;

                    $.ajax({
                        url: 'ajax/loan.php?action=convert_order_to_il',
                        data: {
                            amount,
                            order_id,
                        },
                        method: 'POST',
                        beforeSend: () => {
                            this.loadingButton = true;
                        },
                        success: function (resp) {
                            if (resp.success) {
                                window.location.reload()
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                            alert(error);
                            console.log(error);
                        },
                    }).done(() => {
                        this.loadingButton = false;
                    });
                },
            }));
        });
    </script>
{/literal}
