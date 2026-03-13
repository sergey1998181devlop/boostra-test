<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">
            <i class="fas fa-bell mr-2"></i>
            Звуковое уведомление о новых тикетах
        </h4>
        <button type="button" class="btn btn-primary" id="save-sound-ticket-notice">
            <i class="fas fa-save"></i> Сохранить
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Настройка определяет, кто будет получать звуковое уведомление при появлении новых тикетов.
            Включайте только те направления, где уведомления действительно нужны — выбор отражается сразу во
            всём контакт-центре.
        </p>

        <div class="row">
            <div class="col-md-6 col-lg-4">
                <div class="form-group">
                    <label for="sound_ticket_notice">Получатели уведомлений</label>
                    <select class="form-control" id="sound_ticket_notice" name="sound_ticket_notice">
                        <option value="" {if $settings->sound_ticket_notice == ''}selected{/if}>Выключено</option>
                        <option value="COLLECTION" {if $settings->sound_ticket_notice == 'COLLECTION'}selected{/if}>Только взыскание</option>
                        <option value="EXTRAS_AND_OTHERS" {if $settings->sound_ticket_notice == 'EXTRAS_AND_OTHERS'}selected{/if}>Только допы и прочие</option>
                        <option value="ALL" {if $settings->sound_ticket_notice == 'ALL'}selected{/if}>Все тикеты (допы + взыскание)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6 col-lg-4">
                <div class="form-group">
                    <label for="check_interval_sec">Интервал проверки новых тикетов (секунды)</label>
                    <input type="number" class="form-control" id="check_interval_sec" 
                           name="check_interval_sec" value="{$ticket_sound_settings.check_interval_sec|default:10}" min="1" />
                    <small class="form-text text-muted">Как часто проверять наличие новых тикетов</small>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="form-group">
                    <label for="remind_interval_min">Интервал напоминания (минуты)</label>
                    <input type="number" class="form-control" id="remind_interval_min" 
                           name="remind_interval_min" value="{$ticket_sound_settings.remind_interval_min|default:15}" min="1" />
                    <small class="form-text text-muted">Через сколько минут напомнить о тикете, если он всё ещё новый</small>
                </div>
            </div>
        </div>

        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle mr-2"></i>
            При выборе «Только взыскание» звук слышат только менеджеры в соответствующей теме. Аналогично для «Допы и прочие».
        </div>
    </div>
</div>


