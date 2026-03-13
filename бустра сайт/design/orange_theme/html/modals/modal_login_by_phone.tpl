<!-- Modal -->
<div class="modal fade" id="modal_phone" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form>
                <input name="check_user" type="hidden" value="1" />
                <input name="huid" type="hidden" value="{$settings->hui}" />
                <div class="modal-header border-bottom-0">
                    <h2 class="modal-title mx-auto fw-bold fs-5 text-dark w-75 text-center" id="staticBackdropLabel">Введите номер телефона</h2>
                    <button type="button" class="btn-close btn-absolute" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-0">
                    <p class="font-size-small text-center">Мы пришлём Вам код для продолжения оформления заявки</p>
                    <input class="form-control" type="text" name="phone" placeholder="Номер телефона" />
                </div>
                <div class="modal-footer border-top-0">
                    <div class="d-grid w-100">
                        <button onclick="validatePhone()" type="button" class="btn btn-primary"><small>Подтвердить номер</small></button>
                    </div>
                    <div class="validate_sms_wrapper w-100 d-flex align-items-center justify-content-between">
                        <div class="timerOutWrapper d-none"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>