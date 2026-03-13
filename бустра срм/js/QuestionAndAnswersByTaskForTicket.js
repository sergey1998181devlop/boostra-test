/* global managerInfo, nameClient, userInfo, answers, userId, data, amountOfDebtVar, taskInfo */
let mangoQuestionBlockTask = getBlockByTask('Question');
function getBlockByTask(id) {
    return document.getElementById(id);
}
StepQuestion = 1;
let ParentQuestion = new Array();

async function sendDataTask(url) {
    let results = await fetch(url);
    let res = await results.json();
    return res.Data;
}

let QuestionBlock = getBlockByTask('QuestionAndAnswers');

function setParentQuestion() {
    if (StepQuestion === 1) {
        ParentQuestion[StepQuestion] = 0;
    }
}

//замена шаблона имени менеджера 
function setQuestionTask(string) {
    let replacement = setManagerNameTask();
    let toReplace = '{managerName}';
    let toReplaceRegex = new RegExp(toReplace, "g");
    return setUserNameTask(string.replace(toReplaceRegex, replacement));
}


//установка имени менеджера
function setManagerNameTask() {
    return managerInfo.name_1c;
}

//замена шаблона имени клиента
function setUserNameTask(string) {
    let replacement = false;
    if (nameClient) {
        replacement = nameClient;
    } else {
        if (userInfo) {
            replacement = userInfo.firstname + ' ' + userInfo.patronymic;
        }
    }
    let toReplace = '{clientName}';
    let toReplaceRegex = new RegExp(toReplace, "g");
    return amountOfDebt(string.replace(toReplaceRegex, replacement));
}

function amountOfDebt(string) {
    let replacement = amountOfDebtVar;
    let toReplace = '{amountOfDebt}';
    let toReplaceRegex = new RegExp(toReplace, "g");
    return string.replace(toReplaceRegex, replacement);
}


async function complaintToTheCollectionServiceTask() {
    let url = '/chats.php?chat=mango&method=complaintToTheCollectionService' +
            '&managerId=' + managerInfo.id +
            '&managerName=' + managerInfo.name_1c +
            '&phone=' + userInfo.phone_mobile +
            '&tiketId=' + tiketIdTask;
    tiketIdTask = await mangoSend(url);
}

let data = new Object();
data.incoming = new Object();
data.incoming.from = '';

async function getQuestionAndAnswers() {
    setParentQuestion();
    let url = '/chats.php?chat=mango&method=questionTickets&step=' + StepQuestion +
            '&parent=' + Number(ParentQuestion[StepQuestion]) +
            '&type=outgoing';
    let res = await sendDataTask(url);
    userInfo = taskInfo[0];
    data.incoming.from.number = taskInfo[0].phone_mobile;
    resutAnswersAndQuestion = res;
    if (res) {
        StepQuestion = res.step;
        if (res.question) {
            mangoQuestionBlockTask.innerHTML = setQuestionTask(res.question.text);
        } else {
            mangoQuestionBlockTask.innerHTML = '';
            ParentQuestion[StepQuestion] = 0;
        }
        if (res.answers) {
            let str = '<div class="row"> ';
            let mangoAnswersBlock = getBlockByTask('Answers');
            for (let item of res.answers) {
                let action = 'taskAnswer(' + item.id + ', \'' + item.type + '\', \'' + item.action + '\', \'' + item.actionParams + '\');';
                if (item.type === 'button') {
                    str += '<button id="answer-' + item.id + '" type="button"' +
                            'class="col btn waves-effect waves-light"' +
                            'style="margin: 2px; background: ' +
                            item.buttonColor + '; min-width: 120px; color: white; font-weight: bold; font-size: 12px; padding: 5px;"' +
                            ' onclick="' + action + '">' + item.text + '</button>';
                } else if (item.type === 'text') {
                    str += '<textarea class="form-control" id="answer-' + item.id + '" style="padding: 2px;width: 100%;"' +
                            ' placeholder="' + item.text + '"></textarea>' +
                            '<div style="margin: 2px;">' +
                            '<button onclick="' + action + '" ' +
                            'class="btn btn-success waves-effect waves-light" style="margin: 2px;' +
                            'min-width: 120px; color: white; font-weight: bold; font-size: 12px; padding: 5px;">'+res.question.text+'</button></div>';
                } else if (item.type === 'date') {
                    str += '<div style="color: white; font-weight: bold;">' + item.text + '</div>' +
                            '<input class="form-control" id="answer-' + item.id + '" type="datetime-local" >' +
                            '<div style="margin: 2px;"><button onclick="' + action + '" ' +
                            'class="btn btn-success waves-effect waves-light">Запомнить ответ клиента</button></div>';
                } else if (item.type === 'dateAndSumm') {
                    str += '<div style="color: white; font-weight: bold;">' + item.text + '</div>' +
                            '<div style="width: 100%;"><input class="form-control" id="answer-date-' + item.id + '" type="datetime-local" ></div>' +
                            '<div style="width: 100%;"><div><input class="form-control" id="answer-summ-' + item.id + '" type="number" value="0"> рублей</div></div>' +
                            '<button onclick="' + action + '" class="btn btn-success waves-effect waves-light" style="margin: 2px;' +
                            'min-width: 120px; color: white; font-weight: bold; font-size: 12px; padding: 5px;">Запомнить ответ клиента</button>';
                }
            }
            mangoAnswersBlock.innerHTML = str + '</div>';
        } else {
            getBlockByTask('QuestionAndAnswers').style.display = 'none';

            StepQuestion = 1;
            ParentQuestion[StepQuestion] = 0;
        }
    }
}

// создание задачи о напоминании звонка
async function sheduleCallUserTicket() {
    let url = '/chats.php?chat=mango&method=sheduleCall' +
            '&managerId=' + managerInfo.id +
            '&managerName=' + managerInfo.name_1c +
            '&phone=' + taskInfo[0].phone_mobile +
            '&tiketId=' + tiketIdTask;
    let response = await fetch(url, {
        method: 'POST',
        taskInfo
    });
    tiketIdTask = await response.json();
}

function saveClientSurveyResultsTicket() {
    let url = '/chats.php?chat=mango&method=saveClientSurveyResults' +
            '&userId=' + userId +
            '&managerMangoNumber=' + managerInfo.mango_number;
    mangoSend(url);
}


function sendMessageTicket() {
    let url = '/chats.php?chat=mango&method=sendMessageUsers' +
            '&userId=' + taskInfo[0].user_id;
    mangoSend(url);
}

async function taskAnswer(answerId, type, action, actionParams) {
    let text = '';
    if (eval('typeof ' + action + 'Ticket') !== 'undefined') {
        eval(action + 'Ticket(\'' + actionParams + '\', ' + answerId + ')');
    }
    let questionStep = StepQuestion - 1;
    let answer;
    let question = mangoQuestionBlockTask.textContent;
    if (type === 'date' || type === 'text') {
        answer = getBlockByTask('answer-' + answerId);
        text = answer.value;
    } else if (type === 'dateAndSumm') {
        let date = getBlockByTask('answer-date-' + answerId).value;
        let summ = getBlockByTask('answer-summ-' + answerId).value;
        text = 'До ' + date + ' оплачу ' + summ + ' рублей';
    } else {
        answer = getBlockByTask('answer-' + answerId);
        text = answer.textContent;
    }
    ParentQuestion[StepQuestion] = answerId;
    answers[questionStep] = [];
    answers[questionStep]["Survey"] = question;
    answers[questionStep]["Answer"] = text;
    answers[questionStep]["Action"] = action;
    let url = '/chats.php?chat=mango&method=saveSurveyResults' +
            '&Answer=' + text +
            '&Survey=' + question +
            '&Action=' + action +
            '&userId=' + userId +
            '&managerMangoNumber=' + managerInfo.mango_number;
    mangoSend(url);
    setCallData();
    getQuestionAndAnswers();
}

getQuestionAndAnswers();