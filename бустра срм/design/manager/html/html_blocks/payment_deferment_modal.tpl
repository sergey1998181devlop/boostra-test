<div class="modal fade" id="paymentDeferment" tabindex="-1" role="dialog"
     aria-labelledby="paymentDefermentLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" id="paymentDefermentForm" class="needs-validation" novalidate>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Отсрочка за ФД</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p>Одобрить клиенту отсрочку по платежу на 3 дня</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" class="btn btn-primary" id="approvePaymentDeferment" data-order_id="{$order->order_id}">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Одобрить
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
