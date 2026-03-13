{* ============================================ *}
{* СЕКЦИЯ: Модальные окна                      *}
{* ============================================ *}

{* Модалка операций по договору *}
<div class="modal fade" id="loan_operations" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loan_operations_title">Операции по договору</h5>
        <button type="button" class="btn-close btn" data-bs-dismiss="modal" aria-label="Close">
            <i class="fas fa-times text-white"></i>
        </button>
      </div>
      <div class="modal-body">
      </div>
    </div>
  </div>
</div>

{* Модалка добавления комментария *}
<div id="modal_add_comment" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_comment" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="" />
                    <input type="hidden" name="user_id" value="" />
                    <input type="hidden" name="block" value="cctasks" />
                    <input type="hidden" name="action" value="add_comment" />

                    <input type="hidden" name="task_id" value="" />
                    <input type="hidden" name="uid" value="" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Комментарий:</label>
                        <textarea class="form-control" name="text"></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{* Модалка перспективы *}
<div id="modal_perspective" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Изменить статус на "Перспектива"</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_perspective" action="order/{$order->order_id}">

                    <input type="hidden" name="task_id" value="" />
                    <input type="hidden" name="action" value="add_perspective" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Когда обещает:</label>
                        <input type="text" name="perspective_date" class="form-control js-perspective" value="" />
                    </div>
                    <div class="form-group">
                        <label for="name" class="control-label text-white">Комментарий:</label>
                        <textarea class="form-control" name="text"></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{* Модалка перезвона *}
<div id="modal_recall" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Изменить статус на "Перезвонить"</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_recall" action="order/{$order->order_id}">

                    <input type="hidden" name="task_id" value="" />
                    <input type="hidden" name="action" value="add_recall" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <div>
                            <label for="name" class="control-label text-white">Отправить номер в дайлер через:</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="dont-call" />
                            <label for="">не звонить</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="0" />
                            <label for="">0 ч.</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="1" />
                            <label for="">1 ч.</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="2" />
                            <label for="">2 ч.</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="3" />
                            <label for="">3 ч.</label>
                        </div>
                        <div class="radio-div">
                            <input type="radio" name="recall_date" class="form-control js-recall" value="4" />
                            <label for="">4 ч.</label>
                        </div>

                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{* Модалка отправки SMS *}
<div id="modal_send_sms" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Отправить смс-сообщение?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">


                <div class="card">
                    <div class="card-body">

                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                <form class="js-sms-form">
                                    <input type="hidden" name="user_id" value="" />
                                    <input type="hidden" name="action" value="send_sms" />
                                    <input type="hidden" name="type" value="sms" />
                                    <div class="form-group">
                                        <label for="name" class="control-label">Выберите шаблон сообщения:</label>
                                        <select name="template_id" class="form-control">
                                            {foreach $sms_templates as $sms_template}
                                            <option value="{$sms_template->id}" title="{$sms_template->template|escape}">{$sms_template->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <div class="row">
                                            <div class="col-3">
                                                <button type="button" class="btn btn-danger float-left waves-effect" data-dismiss="modal">Отменить</button>
                                            </div>
                                            <div class="col-9 text-right">
                                                <button type="button" value="{$task->user->id}"class="mr-3 btn btn-info waves-effect waves-light js-send-megafon">Отправить</button>

                                               {* <button type="button" class="btn btn-success waves-effect waves-light js-send-whatsapp">Whatsapp</button>  *}
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{* Модалка распределения *}
<div id="modal_distribute" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Распределить договора</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_distribute" action="">

                    <input type="hidden" name="action" value="distribute" />
                    <input type="hidden" name="period" value="{$filter_period}" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label"><strong>Менеджеры для распределения:</strong></label>
                        <ul class="list-unstyled" style="max-height:250px;overflow:hidden auto;">

                        </ul>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Распределить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{* Модалка перезвона робота *}
<div id="recallRobo" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label for="robo-number-input">ID PDS кампании для перезвона</label>
                <input id="robo-number-input" type="text" class="form form-control p-2 mb-2 bg-white robo-number text-dark" placeholder="ID PDS куда отправим номера на перезвон">
                <small class="form-text text-muted">ID кампании заполняется автоматически в зависимости от выбранной МКК</small>
                
                <label for="attempt-numbers-input" class="mt-2">Количество попыток для перезвона</label>
                <input id="attempt-numbers-input" type="text" class="form form-control p-2 mb-2 bg-white attempt-numbers text-dark" placeholder="Количество попыток">
                
                <label for="interval-input" class="mt-2">Интервал между звонками (в часах)</label>
                <input id="interval-input" type="text" class="form form-control p-2 mb-2 bg-white interval text-dark" placeholder="Интервал в часах">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success recall-robo">Ок </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Отмена </button>
            </div>
        </div>

    </div>
</div>

{* Модалка результатов отправки в Vox *}
<div id="voxResultModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Результат отправки в Vox</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="vox-result-message"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Ок</button>
            </div>
        </div>
    </div>
</div>


