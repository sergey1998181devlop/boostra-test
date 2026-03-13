<div class="modal" id="show-file-lk-modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body d-flex justify-content-center">
                <h4 class="modal-title modal-title-visible-need-hide" style="display:none">Вы точно хотите скрыть этот документ в личном кабинете клиента?</h4>
                <h4 class="modal-title modal-title-visible-need-show" style="display:none" >Вы точно хотите отобразить этот документ в личном кабинете клиента?</h4>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-success" id="show-file-lk-button-modal">Да</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Нет</button>
            </div>

        </div>
    </div>
</div>


<div class="modal" id="delete-modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body d-flex justify-content-center">
                <h4 class="modal-title">Вы уверены?</h4>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-success"  id="delete-button-modal">Да </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Нет </button>
            </div>

        </div>
    </div>
</div>

<div class="modal" id="unblock-asp-modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body d-flex justify-content-center">
                {if (in_array($manager->role, ['developer', 'contact_center_plus', 'admin', 'opr', 'ts_operator']) ) }
                    <h4 class="modal-title">Разброкировать АСП. Вы уверены?</h4>
                {else}
                    <h4 class="modal-title">Недостаточно прав для разблокировки АСП</h4>
                {/if}
            </div>

            <!-- Modal footer -->
            <div class="modal-footer d-flex justify-content-center">
                {if (in_array($manager->role, ['developer', 'contact_center_plus', 'admin', 'opr', 'ts_operator']) ) }
                    <button type="button" class="btn btn-success"  id="unblock-asp-button-modal">Да </button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Нет </button>
                {else}
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ОК </button>
                {/if}
            </div>

        </div>
    </div>
</div>


<div id="sms-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body send-sms-modal-div">
                <label for="phoneInput">Напишите номер телефона</label>
                <input  value="{$order->phone_mobile}" class="form form-control p-2 sms-phone" maxlength="11" id ='phoneInput'>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success send-sms" >Отправить </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Отмена </button>
            </div>
        </div>

    </div>
</div>

<div id="disable_check_reports_for_loan-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-white">Уверены, что хотите {if empty($order_data['disable_check_reports_for_loan'])}отключить {else}включить {/if}проверку ССП и КИ отчетов для данной заявки?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success disable_check_reports_for_loan-confirm">Да</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Отмена</button>
            </div>
        </div>

    </div>
</div>

<div id="disable_robot_calls_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Отключить исходящие звонки робота</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-white">
                <p class="mb-2">На сколько дней отключить?</p>
                <select class="form-control mb-2 js-disable-robot-calls-days-select" id="disable_robot_calls_days_select">
                    <option value="1">На 1 день</option>
                    <option value="2">На 2 дня</option>
                    <option value="3">На 3 дня</option>
                    <option value="4">На 4 дня</option>
                    <option value="5">На 5 дней</option>
                </select>
                <p class="mb-1 mt-3 small text-muted">Или введите своё значение:</p>
                <input type="number" class="form-control js-disable-robot-calls-days-custom" id="disable_robot_calls_days_custom" min="1" max="365" placeholder="дней" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success js-disable-robot-calls-submit">Отключить</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="leaveComplaint" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" style="text-align: center; font-size: 18px; color: white">
                <select class="form-control mb-2" id="selectSubject">
                    <option value="" disabled selected>Выберите тематику жалобы</option>
                    <option value="borrower">Заемщик</option>
                    <option value="ministry">МВД</option>
                    <option value="prosecutor">Прокуратура</option>
                    <option value="roskomnadzor">Роскомнадзор</option>
                    <option value="rospotrebnadzor">Роспотребнадзор</option>
                    <option value="sro">СРО</option>
                    <option value="third_person">Третье лицо </option>
                    <option value="fssp">ФССП</option>
                    <option value="central_bank">Центробанк</option>
                    <option value="hotline">Горячая линия</option>
                    <option value="email">Почта</option>
                    <option value="third-face">Жалоба взаимод. 3 лица</option>
                    <option value="bomber">Жалоба на бомбер</option>
                    <option value="threats">Жалобы угрозы (угрожали физ. расправой/оскорбления)</option>
                    <option value="robot">Жалобы робот</option>
                    <option value="sms">Жалобы на смс</option>
                    <option value="add_service">Жалобы на доп. услуги</option>
                </select>
                <select name="" id="complaint-order-number" class="form-control">
                    <option value="{$order->order_id}">{$order->order_id}</option>
                </select>
                <label for="complaint-comment" class="mt-2">Комментарии</label>
                <textarea  id="complaint-comment" class="form-control" cols="30" rows="10"></textarea>
            </div>
            <div class="modal-footer" style="margin: auto">
                <button type="button"  class="btn btn btn-success send-complaint">Отправить</button>
                <button type="button" class="btn btn btn-danger" data-dismiss="modal">Отмена</button>
            </div>
        </div>

    </div>
</div>
