{* Модалка предложения кросс-ордера для НК после подписания основного займа *}

<div id="auto_confirm_2_cross_order_modal" class="auto-confirm-2-cross-order-modal" style="display: none;">
    <div class="auto-confirm-2-cross-order-modal__overlay"></div>
    <div class="auto-confirm-2-cross-order-modal__content">
        <div class="auto-confirm-2-cross-order-modal__header">
            <img class="auto-confirm-2-cross-order-modal__icon" src="design/{$settings->theme|escape}/img/svg/confetti.svg" alt="Поздравляем!" />
            <h2 class="auto-confirm-2-cross-order-modal__title">
                Отличная новость!<br>
                Вам одобрено ещё <span id="cross_order_amount">{$cross_order_amount|default:$order->amount|default:0}</span> рублей
            </h2>
            <p class="auto-confirm-2-cross-order-modal__subtitle">
                На срок 21 день
            </p>
        </div>
        
        <div class="auto-confirm-2-cross-order-modal__buttons">
            <button type="button" class="auto-confirm-2-cross-order-modal__btn-accept" id="auto_confirm_2_cross_order_accept">
                Получить ещё
            </button>
            <button type="button" class="auto-confirm-2-cross-order-modal__btn-decline" id="auto_confirm_2_cross_order_decline">
                Получить позже
            </button>
        </div>
        
        <input type="hidden" id="cross_order_parent_order_id" value="{$order->id|default:0}" />
        <input type="hidden" id="cross_order_user_phone" value="{$user->phone_mobile|default:''}" />
        <input type="hidden" id="cross_order_user_amount" value="{$cross_order_amount|default:$order->amount|default:0}" />
    </div>
</div>

<style>
.auto-confirm-2-cross-order-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9000;
}

.auto-confirm-2-cross-order-modal__overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1;
}

.auto-confirm-2-cross-order-modal__content {
    position: absolute;
    top: 50%;
    left: 20px;
    right: 20px;
    transform: translateY(-50%);
    max-width: 500px;
    margin: 0 auto;
    background: #fff;
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    z-index: 2;
}

.auto-confirm-2-cross-order-modal__icon {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
}

.auto-confirm-2-cross-order-modal__title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

.auto-confirm-2-cross-order-modal__title span {
    color: #4CAF50;
}

.auto-confirm-2-cross-order-modal__subtitle {
    font-size: 14px;
    color: #666;
    margin-bottom: 30px;
}

.auto-confirm-2-cross-order-modal__buttons {
    width: 100%;
}

.auto-confirm-2-cross-order-modal__btn-accept,
.auto-confirm-2-cross-order-modal__btn-decline {
    display: block;
    width: 100%;
    max-width: none;
    box-shadow: none;
    padding: 16px 40px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 14px;
    font-weight: 700;
    line-height: 24px;
    letter-spacing: 0;
    text-align: center;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    transition: background-color 0.3s;
    box-sizing: border-box;
}

.auto-confirm-2-cross-order-modal__btn-accept {
    margin-bottom: 15px;
    background: #0A91ED;
    color: #fff;
}

.auto-confirm-2-cross-order-modal__btn-accept:hover {
    background: #0880D6;
}

.auto-confirm-2-cross-order-modal__btn-decline {
    background: transparent;
    color: #4E8FF5;
    border: 2px solid #4E8FF5;
}

.auto-confirm-2-cross-order-modal__btn-decline:hover {
    background: #F0F7FF;
}

.mfp-wrap {
    z-index: 100000 !important;
}

.mfp-bg {
    z-index: 99999 !important;
}

@media (max-width: 600px) {
    .auto-confirm-2-cross-order-modal__content {
        left: 15px;
        right: 15px;
        padding: 30px 20px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
    }
    
    .auto-confirm-2-cross-order-modal__title {
        font-size: 20px;
    }
}
</style>

<script>
(function() {
    'use strict';
    
    var AutoConfirm2CrossOrderModal = {
        
        hide: function() {
            var modal = document.getElementById('auto_confirm_2_cross_order_modal');
            if (modal) {
                modal.remove();
            }
        },
        
        acceptCrossOrder: function() {
            var self = this;
            var orderId = document.getElementById('cross_order_parent_order_id').value;
            var phone   = document.getElementById('cross_order_user_phone').value;
            var amount  = document.getElementById('cross_order_user_amount').value;


            self.hide();
            if (typeof CrossOrderNKSign !== 'undefined') {
                CrossOrderNKSign.show(orderId, amount, phone);
            } else {
                console.error('CrossOrderNKSign не определён');
            }
        },
        
        declineCrossOrder: function() {

            this.hide();

            var cardSection = document.getElementById('card-add-section');
            if (cardSection) {
                cardSection.style.display = 'flex';

                if (typeof $ !== 'undefined') {
                    $('.js-need-verify').prop('checked', true);
                    $('#not_checked_info').hide();
                }
            }
            
            var sbpWrapper = document.getElementById('sbp-bank-selection-wrapper');
            if (sbpWrapper) {
                sbpWrapper.style.display = 'block';
            }
        },
        
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        }
    };

    var showModalOnLoad = {if !empty($show_auto_confirm_cross_order_asp)}true{else}false{/if};

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {

            var acceptBtn = document.getElementById('auto_confirm_2_cross_order_accept');
            if (acceptBtn) {
                acceptBtn.addEventListener('click', function() {
                    AutoConfirm2CrossOrderModal.acceptCrossOrder();
                });
            }

            var declineBtn = document.getElementById('auto_confirm_2_cross_order_decline');
            if (declineBtn) {
                declineBtn.addEventListener('click', function() {
                    AutoConfirm2CrossOrderModal.declineCrossOrder();
                });
            }

            var modal = document.getElementById('auto_confirm_2_cross_order_modal');
            if (modal && showModalOnLoad) {
                modal.style.display = 'block';
            }
        });
    } else {
        var acceptBtn = document.getElementById('auto_confirm_2_cross_order_accept');
        if (acceptBtn) {
            acceptBtn.addEventListener('click', function() {
                AutoConfirm2CrossOrderModal.acceptCrossOrder();
            });
        }
        
        var declineBtn = document.getElementById('auto_confirm_2_cross_order_decline');
        if (declineBtn) {
            declineBtn.addEventListener('click', function() {
                AutoConfirm2CrossOrderModal.declineCrossOrder();
            });
        }

        var modal = document.getElementById('auto_confirm_2_cross_order_modal');
        if (modal && showModalOnLoad) {
            modal.style.display = 'block';
        }
    }

    window.AutoConfirm2CrossOrderModal = AutoConfirm2CrossOrderModal;
})();
</script>
