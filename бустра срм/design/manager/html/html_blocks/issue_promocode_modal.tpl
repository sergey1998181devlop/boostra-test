<div class="modal fade" id="generatePromoCodeModal" tabindex="-1" role="dialog"
     aria-labelledby="generatePromoCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="generatePromoCodeForm" class="needs-validation" novalidate>
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Генерация промокода</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    {if $clientId}
                        <input type="hidden" name="user_id" value="{$clientId}">
                    {/if}
                    
                    <!-- Название -->
                    <div class="form-group">
                        <label for="promoTitle">Название <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="promoTitle" name="title"
                               placeholder="Введите название промокода" required
                               minlength="3" maxlength="50"
                               pattern="[A-Za-zА-Яа-я0-9\s-_]+"
                               data-error="Название должно содержать только буквы, цифры, пробелы и дефисы">
                        <div class="invalid-feedback">Пожалуйста, введите корректное название промокода</div>
                    </div>

                    <!-- Даты -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dateStart">Дата начала <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dateStart" name="date_start"
                                       required min="{$smarty.now|date_format:'%Y-%m-%d'}">
                                <div class="invalid-feedback">Выберите дату начала</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dateEnd">Дата окончания <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dateEnd" name="date_end"
                                       required min="{$smarty.now|date_format:'%Y-%m-%d'}">
                                <div class="invalid-feedback">Выберите дату окончания</div>
                            </div>
                        </div>
                    </div>

                    <!-- Ставка -->
                    <div class="form-group">
                        <label for="rate">Процентная ставка (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="rate" name="rate"
                                   min="0" max="0.8" step="0.1" required value="0"
                                   placeholder="0.0">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="invalid-feedback">Введите ставку от 0 до 0.8%</div>
                    </div>

                    <!-- Количество -->
                    <div class="form-group">
                        <label for="quantity" class="d-flex align-items-center">
                            Лимит использования
                            <i class="mdi mdi-information ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="0 для массовых промокодов"></i>
                        </label>
                        <input type="number" class="form-control" id="quantity" name="quantity"
                               min="0" value="1" placeholder="Введите лимит использования">
                        <div class="invalid-feedback">Введите корректное значение</div>
                    </div>

                    {if $haveCloseCredits}
                        <div class="form-group mt-4">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input"
                                       id="disableAdditionalServices" name="disable_additional_services">
                                <label class="custom-control-label" for="disableAdditionalServices">
                                    Отключить дополнительные услуги
                                </label>
                            </div>

                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input"
                                       id="isMandatoryIssue" name="is_mandatory_issue">
                                <label class="custom-control-label" for="isMandatoryIssue">
                                    Обязательно к выдаче
                                </label>
                            </div>
                        </div>
                    {/if}
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" class="btn btn-primary" id="generateButton">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Сгенерировать
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>