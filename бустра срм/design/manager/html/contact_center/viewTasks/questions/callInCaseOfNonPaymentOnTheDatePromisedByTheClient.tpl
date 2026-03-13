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
                            font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(2, 1);task.sendMessageToMessengers();survey.loggingTheSurvey(1, this.textContent);survey.closeCustomer(1);">Клиент не берет трубку</button>
                </div>
            </div>
            <div id="customerSurvey-2" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-2">
                        Добрый день {$userInfo->firstname} {$userInfo->patronymic}, специалист претензионного отдела 
                        {$manager->name}, организация Бустра. Разговор наш записывается. 
                        - Вы не выполнили своё обещание по оплате!?
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="justify-content: center; text-align: center;">
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1, 1);survey.openCustomerSurvay(2, this.textContent);">Да. Клиент произведет оплату</button>
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #F62D51; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(2, 1);survey.openCustomerSurvay(2, this.textContent);task.sendToTheSump({$taskInfo->id});survey.loggingTheSurvey(2, this.textContent);">Нет. Клиент не хочет платить</button>
                    </div>
                </div>
                {include file="../PreviousQuestion.tpl"}
            </div>
            <div id="customerSurvey-3" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-3">
                        После оплаты свяжитесь с нами по телефону.
                        Всего Вам доброго, досвидания!
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
                        Согласитесь, что каждый человек должен выполнять обещания, которые дает?
                    </div>
                </div>
                <div class="col">
                    <div>
                        <textarea id="answer-text-4" class="form-control"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="justify-content: center; text-align: center;">
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1, 2);survey.openCustomerSurvay(4, survey.getTextInput('answer-text-4'));">Далее</button>
                    </div>
                </div>
                {include file="../PreviousQuestion.tpl"}
            </div>
            <div id="customerSurvey-5" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-5">
                        - Вы согласны, что несдержанные обещания будут негативно сказываться на Вашей кредитной истории?
                    </div>
                </div>
                <div class="col">
                    <div>
                        <textarea id="answer-text-5" class="form-control"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="justify-content: center; text-align: center;">
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1, 1);survey.openCustomerSurvay(5, survey.getTextInput('answer-text-5'));">Далее</button>
                    </div>
                </div>
                {include file="../PreviousQuestion.tpl"}
            </div>
            <div id="customerSurvey-6" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-6">
                        (выберите вариант ответа)
                    </div>
                </div>
                <div class="col">
                    <div class="col" style="justify-content: center; text-align: center;">
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1, 1);survey.openCustomerSurvay(6, this.textContent);">Согласился оплатить</button>
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #F62D51; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(3, 3);survey.openCustomerSurvay(6, this.textContent);">Не согласен оплачивать</button>
                    </div>
                </div>
                {include file="../PreviousQuestion.tpl"}
            </div>
            <div id="customerSurvey-7" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-7">
                        - И так мы с Вами договорились на возврат.
                        Надеюсь я могу Вам доверять и последующие договоренности будут соблюдены?
                    </div>
                </div>
                <div class="col">
                    <div>
                        <textarea id="answer-text-5" class="form-control"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="justify-content: center; text-align: center;">
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1, 1);survey.openCustomerSurvay(7, survey.getTextInput('answer-text-7'));">Далее</button>
                    </div>
                </div>
                {include file="../PreviousQuestion.tpl"}
            </div>
            <div id="customerSurvey-8" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-8">
                        - Обязана Вам сообщить, что если Вы нарушите свое обещание снова, ваш договор будет передан на принудительное взыскание!
                        Либо через суд, либо это будет продажа по цессии. Все Вам доброго, досвидания!
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="justify-content: center; text-align: center;">
                        <button type="button" class=" btn waves-effect waves-light" 
                                style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(0, 1);survey.closeCustomer(8);">Завершить разговор</button>
                    </div>
                </div>
                {include file="../PreviousQuestion.tpl"}
            </div>
            <div id="customerSurvey-9" class="customerSurvey" style="">
                <div class="row">
                    <div id="question-9">
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
                                font-weight: bold; font-size: 12px; padding: 5px;" onclick="survey.setStep(1,1);survey.closeCustomer(9);">Завершить разговор</button>
                    </div>
                </div>
            </div>
        </div>
