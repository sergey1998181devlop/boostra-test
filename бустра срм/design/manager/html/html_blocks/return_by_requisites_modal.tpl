{* Модальное окно возврата по реквизитам *}
<div class="modal fade" id="modal_return_by_requisites" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Возврат по реквизитам</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form_return_by_requisites">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Заявка</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="rr_order_number" readonly>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Клиент</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="rr_client_info" readonly>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Вид услуги</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="rr_service_name" readonly>
                        </div>
                    </div>
                    
                    <hr>

                    <h5 class="mb-3">Реквизиты банка</h5>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">ФИО получателя <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="rr_recipient_fio" placeholder="ФИО получателя перевода" maxlength="255">
                            <small class="form-text text-muted">Укажите ФИО держателя счёта (может отличаться от ФИО клиента)</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="btn-group btn-group-toggle mb-3" data-toggle="buttons">
                            <label class="btn btn-outline-primary active">
                                <input type="radio" name="requisites_mode" value="saved" checked> Сохраненные реквизиты
                            </label>
                            <label class="btn btn-outline-primary">
                                <input type="radio" name="requisites_mode" value="new"> Новые реквизиты
                            </label>
                        </div>
                    </div>

                    <div id="rr_saved_requisites_block">
                        <div class="form-group">
                            <label>Выберите сохраненные реквизиты</label>
                            <select class="form-control" id="rr_saved_requisites">
                                <option value="">Выберите реквизиты</option>
                            </select>
                        </div>
                    </div>

                    <div id="rr_new_requisites_block" style="display:none;">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Номер счета <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="rr_account_number" placeholder="20 цифр" maxlength="20" pattern="[0-9]{20}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <small class="form-text text-muted">Только цифры, 20 символов</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">БИК Банка <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="rr_bik" placeholder="9 цифр" maxlength="9" pattern="[0-9]{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <small class="form-text text-muted">Только цифры, 9 символов</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Наименование банка</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="rr_bank_name" placeholder="Например, ПАО Сбербанк">
                            </div>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="rr_save_requisites" checked>
                            <label class="form-check-label" for="rr_save_requisites">
                                Сохранить реквизиты для следующих возвратов
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rr_set_default">
                            <label class="form-check-label" for="rr_set_default">
                                Сделать по умолчанию
                            </label>
                        </div>
                    </div>
                    
                    <hr>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label font-weight-bold">Сумма возврата (руб) <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="rr_amount" min="0" max="" step="0.01" required oninput="if(this.max && parseFloat(this.value) > parseFloat(this.max)) this.value = this.max;">
                            <small class="form-text text-muted">Максимальная сумма: <strong id="rr_max_amount"></strong> руб</small>
                        </div>
                    </div>

                    <div id="rr_alert" class="alert" style="display:none;"></div>

                    <input type="hidden" id="rr_service_type">
                    <input type="hidden" id="rr_service_id">
                    <input type="hidden" id="rr_order_id">
                    <input type="hidden" id="rr_operation_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-success" id="btn_rr_send">
                    <i class="fa fa-paper-plane"></i> Отправить
                </button>
            </div>
        </div>
    </div>
</div>
