
<div id="mangoDialog" style="font-size: 12px; padding-left: 15px;">
    <div class="container-fluid" style="font-size: 12px; padding: 5px;">
        <div class="modal-header" style="font-size: 12px; padding: 5px;">
            <h4 class="modal-title" id="mangoDialogText" style="font-size: 16px; padding: 5px; font-weight: bold;"></h4>
            <button type="button" class="close" onclick="mangoDialogClose();">×</button>
        </div>
        <div class="row">
                <div class="col-4">
                    <div>
                        <div class="modal-title" style="font-size: 14px;">от : <a id="LinkUserAccount" class="LinkUserAccount" target="_blank"><span id="mangoUserName"></span></a></div>
                        <div class="modal-title" style="font-size: 14px;">Телефон : <span style="font-weight: bold; color: white;" id="mangoUserPhone"></span></div>
                        <div class="modal-title" style="display: none; font-size: 14px;">Дата рождения : <span style="font-size: 18px; font-weight: bold; color: white;" id="mangoUseBerthDay"></span></div>
                        <div class="modal-title" style="font-size: 14px;">Последний займ :<span id="mangoLastCredit"> Информация недоступна</span></div>
                    </div>
                </div>
                <div class="col-6">
                    <div id="mangoQuestionTickets" style="display: none; padding: 5px; text-align: center;">
                        <div id="mangoQuestion"style="font-size: 14px; font-weight: bold; color: white; margin-bottom: 5px;"></div>
                        <div id="mangoAnswers" class="row"></div>
                        <br/>
                        
                    </div>
                    <div style="display: none;" id="mangoCommentBlock">
                        <p>Оставить коментарий : </p>
                        <p>
                            <textarea id="mangoComment" class="form-control" style="width: 100%; height: 80px;"></textarea>
                        </p>
                    </div>
                </div>
                <div class="col-2">
                    <div>
                        <button type="button" id="mangoAcceptCall" class="btn btn-success waves-effect waves-light" style="display: block; margin: 5px; padding: 5px; font-size: 12px;" onclick="mangoAcceptCall();">Принять вызов</button>
                        <button type="button" id="mangoEndCall" class="btn btn-success waves-effect waves-light" style="display: none; margin: 5px; padding: 5px; font-size: 12px;" onclick="mangoEndCall();">Завершить разговор</button>
                        <button type="button" id="mangoSendComment" class="btn btn-success waves-effect waves-light" style="margin: 5px; padding: 5px; font-size: 12px;" onclick="mangoSendComment();">Отправить</button>
                        <button type="button" id="previousQuestion" class="btn btn-success waves-effect waves-light" style="margin: 5px; padding: 5px; font-size: 12px;" onclick="previousQuestion();">Вернуться к предыдущему вопросу</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div style="height: 1px; border: solid 1px black;"></div>
</div>