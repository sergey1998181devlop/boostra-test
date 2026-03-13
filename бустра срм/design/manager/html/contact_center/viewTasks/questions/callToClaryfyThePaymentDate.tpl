        <div id="taskСompleted" style="justify-content: center; text-align: center;">
            <button type="button" class=" btn waves-effect waves-light" 
                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                font-weight: bold; font-size: 12px; padding: 5px;"
                onclick="task.closeTask({$taskInfo->id});survey.closeBlock(survey.taskСompleted);">
                    Задача выполнена
            </button>
        </div>
        <div class="form-body" id="Questions">
            <div id="customerSurvey-1" class="customerSurvey" style="">
                <div id="question-1">
                    Совершите звонок клиенту
                </div>
                <div class="col" style="justify-content: center; text-align: center;">
                    <button type="button" class=" btn waves-effect waves-light" 
                            style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                            font-weight: bold; font-size: 12px; padding: 5px;"
                            onclick="survey.setStep(1, 1);survey.openCustomerSurvay(1, this.textContent);">Клиент взял трубку</button>
                    <button type="button" class=" btn waves-effect waves-light" 
                            style="margin: 2px; background: #F62D51; min-width: 120px; color: white;
                            font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1, 1);task.sendMessageToMessengers();survey.loggingTheSurvey(1, this.textContent);survey.closeCustomer(1);">Клиент не берет трубку</button>
                </div>
            </div>
            <div id="customerSurvey-2" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-2">
                        Добрый день {$userInfo->firstname} {$userInfo->patronymic}, специалист претензионного отдела 
                        {$manager->name}, организация Бустра. Разговор наш записывается. У Вас имеется просроченная задолжность
                        на сумму {$task->{'ОстатокОД'}+$task->{'ОстатокПроцентов'}+$task->{'ОстатокПени'}} руб. В течении двух дней 
                        необходимо пролонгировать или оплатить полностью Ваш долг. Какм образом будете совершать платеж?
                        На сайте или по реквизитам?
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="justify-content: center; text-align: center;">
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(2,1);survey.openCustomerSurvay(2, this.textContent);">Клиент будет оплачивать</button>
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #F62D51; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1,1);survey.openCustomerSurvay(2, this.textContent);task.sendToTheSump({$taskInfo->id});survey.loggingTheSurvey(2, this.textContent);">Клиент не будет оплачивать</button>

                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #F62D51; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1,1);survey.openCustomerSurvay(2, this.textContent);task.sendToTheSump({$taskInfo->id});survey.loggingTheSurvey(2, this.textContent);">Клиент будет оплачивать, но нет даты</button>

                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #F62D51; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1,1);survey.openCustomerSurvay(2, this.textContent);task.sendToTheSump({$taskInfo->id});survey.loggingTheSurvey(2, this.textContent);">Клиент будет оплачивать через суд</button>
                    </div>
                </div>
                {include file="../PreviousQuestion.tpl"}
            </div>
            <div id="customerSurvey-3" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-3">
                        <p>
                            Конец разговора
                        </p>
                        Сюда нужен текст
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="justify-content: center; text-align: center;">
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1,1);survey.closeCustomer(3);">Завершить разговор</button>
                    </div>
                </div>
            </div>
            <div id="customerSurvey-4" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-4">
                        Назовите пожалуйста дату, сумму и время до которого, Вы сможите, произвести оплату?
                    </div>
                </div>
                <div class="col">
                    <div>
                        <input class="form-control" id="answer-date-4" type="datetime-local" >
                    </div>
                    <div >
                        <input class="form-control" id="answer-summ-4" type="number" value="0"> рублей
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="justify-content: center; text-align: center;">
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1, 2);survey.saveDateAndSumm(4);survey.openCustomerSurvay(4, this.textContent);">Сохранить</button>
                    </div>
                </div>
                {include file="../PreviousQuestion.tpl"}
            </div>
            <div id="customerSurvey-5" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-5">
                        Отлично. Фиксируем дату, время и сумму. После оплаты свяжитесь с нами. Всего Вам доброго, досвидания!
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="justify-content: center; text-align: center;">
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1,1);survey.closeCustomer(5);">Сохранить</button>
                    </div>
                </div>
                {include file="../PreviousQuestion.tpl"}
            </div>
        </div>
