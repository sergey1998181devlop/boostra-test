/* global managerInfo, data */

const mangoDialogBlock = getBlockById('mangoDialog');
const mangoUseBerthDayBlock = getBlockById('mangoUseBerthDay');
const mangoUserNameBlock = getBlockById('mangoUserName');
const mangoUserPhoneBlock = getBlockById('mangoUserPhone');
const mangoAcceptCallButton = getBlockById('mangoAcceptCall');
const mangoEndCallButton = getBlockById('mangoEndCall');
const mangoCommentBlockDiv = getBlockById('mangoCommentBlock');
const mangoLastCreditBlock = getBlockById('mangoLastCredit');
const mangoQuestionTicketsBlock = getBlockById('mangoQuestionTickets');
const mangoQuestionBlock = getBlockById('mangoQuestion');
const mangoAnswersBlock = getBlockById('mangoAnswers');

let Step = 1;
let parent = [];
let answers = [];
let resutAnswersAndQuestion;
let active = false;
let userInfo;
let name = 'Не найден';
let nameClient = false;
let userId = 0;
let callData;
let lastCredit;
let tiketId = 0;
let resCredit;

//закрытие диалогового окна
function mangoDialogClose() {
    active = false;
    mangoDialogBlock.style.display = 'none';
    mangoAcceptCallButton.style.display = 'none';
    mangoEndCallButton.style.display = 'none';
    mangoCommentBlockDiv.style.display = 'none';
    mangoUserPhoneBlock.innerHTML = '';
    mangoUserNameBlock.innerHTML = '';
    mangoLastCreditBlock.innerHTML = '';
    getBlockById('mangoSendComment').style.display = 'none';
}

//активация диалогового окна при входящем звонке или при переходе на другую страницу, если есть активный текущий звонок
async function mangoDialogOpen() {
    getBlockById('mangoDialogText').innerHTML = 'Входящий вызов';
    if (data) {
        callData = data;
        mangoQuestionTickets();
        mangoDialogBlock.style.display = 'block';
        if (!active) {
            mangoAcceptCallButton.style.display = 'block';
            mangoEndCallButton.style.display = 'none';
            mangoCommentBlockDiv.style.display = 'none';
            getBlockById('mangoSendComment').style.display = 'none';
        } else {
            mangoAcceptCallButton.style.display = 'none';
            mangoEndCallButton.style.display = 'block';
            mangoCommentBlockDiv.style.display = 'none';
            getBlockById('mangoSendComment').style.display = 'none';
        }
        userInfo = await getUserInfo(data.incoming.from.number);
        if (data.incoming.from.number) {
            mangoUserPhoneBlock.innerHTML = '+' + data.incoming.from.number;
        }
        let birth = '';
        mangoLastCreditBlock.innerHTML = 'Не найден';
        if (userInfo) {
            userId = userInfo.id;
            name = ' ' + userInfo.firstname + ' ' + userInfo.patronymic;
            birth = userInfo.birth;

        }
        getBlockById('LinkUserAccount').href = '/client/' + userId;
        mangoUseBerthDayBlock.innerHTML = birth;
        mangoUserNameBlock.innerHTML = name;
        mangoLastCreditBlock.innerHTML = ' Поиск информации...';
        if (!resCredit['info']) {
            resCredit = await getLastCreditInfo(userInfo.UID);
            if (resCredit.info) {
                setCreditInfo();
            }
        } else if (resCredit['info']) {
            setCreditInfo();
        } else {
            mangoLastCreditBlock.innerHTML = ' Нет активных займов.';
        }
    }
}

async function setCreditInfo() {
    mangoLastCreditBlock.innerHTML = ' Необходимая информация получена.';
    lastCredit = mangoObjectToArray(resCredit.info);
    mangoLastCreditBlock.innerHTML = '<a class="LinkUserAccount" href="/order/' + resCredit.info.id + '" target="_blank">' +
            ' номер : ' + resCredit.info['1c_id'] + ' дата : ' + getDate(resCredit.info.date) + ' статус : ' + resCredit.info['1c_status'] + '</a>';

}

function getDate(stringDate) {
    return new Date(Date.parse(stringDate)).toLocaleDateString('ru-Ru');
}

//преобразование обьекта в массив (полезно когда в обьекте есть свойства начинающиеся с цифры) 
async function mangoObjectToArray(data) {
    let newData = new Array();
    let keys = Object.keys(data);
    let values = Object.values(data);
    for (let i = 0; i < keys.length; ++i) {
        newData[keys[i]] = values[i];
    }
    return newData;
}

//информация о клиенте по его номеру телефона
async function getUserInfo(phoneNumber) {
    let url = '/chats.php?chat=main&method=getUserInfoByPhone&phone=' + phoneNumber;
    let res = await mangoSend(url);
    if (res) {
        return res;
    }
}

//информация о последнем займе
async function getLastCreditInfo(userId) {
    let url = '/chats.php?chat=main&method=getLastCreditInfoByUserId&userId=' + userId;
    let res = await mangoSend(url);
    if (res) {
        return res;
    }
}

//установка имени клиента с его слов
function theClient_sNameAsHeIntroducedHimselfMango(a, id) {
    let name;
    name = getBlockById('answer-' + id).value;
    if (name) {
        nameClient = name;
    } else {
        name = mangoUserNameBlock.textContent;
        getBlockById('answer-' + id).value = name;
        nameClient = name;
    }
}

//установка имени менеджера
function setManagerName() {
    return managerInfo.name_1c;
}

//замена шаблона имени клиента
function setUserName(string) {
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
    return string.replace(toReplaceRegex, replacement);
}

//замена шаблона имени менеджера 
function setQuestion(string) {
    let replacement = setManagerName();
    let toReplace = '{managerName}';
    let toReplaceRegex = new RegExp(toReplace, "g");
    return setUserName(string.replace(toReplaceRegex, replacement));
}

//вывод вопросов и ответов
async function mangoQuestionTickets() {
    if (Step > 1) {
        getBlockById('previousQuestion').style.display = 'block';
    } else {
        parent[Step] = 0;
        getBlockById('previousQuestion').style.display = 'none';
    }
    let url = '/chats.php?chat=mango&method=questionTickets&step=' + Step + '&parent=' + Number(parent[Step]) + '&type=incoming';
    let res = await mangoSend(url);
    resutAnswersAndQuestion = res;
    if (res.step) {
        Step = res.step;
        if (res.question) {
            mangoQuestionBlock.innerHTML = setQuestion(res.question.text);
        } else {
            mangoQuestionBlock.innerHTML = '';
            parent[Step] = 0;
        }
        let answers = '';
        if (res.answers) {
            for (let item of res.answers) {
                let action = 'mangoAnswer(' + item.id + ', \'' + item.type + '\', \'' + item.action + '\', \'' + item.actionParams + '\');';
                if (item.type === 'button') {
                    answers += '<button id="answer-' + item.id + '" type="button"' +
                            'class="col btn waves-effect waves-light"' +
                            'style="margin: 2px; background: ' +
                            item.buttonColor + '; min-width: 120px; color: white; font-weight: bold; font-size: 12px; padding: 5px;"' +
                            ' onclick="' + action + '">' + item.text + '</button>';
                } else if (item.type === 'text') {
                    answers += '<textarea class="form-control" id="answer-' + item.id + '" style="padding: 2px;width: 100%;"' +
                            ' placeholder="' + item.text + '"></textarea>' +
                            '<div style="margin: 2px;">' +
                            '<button onclick="' + action + '" ' +
                            'class="btn btn-success waves-effect waves-light" style="margin: 2px;' +
                            'min-width: 120px; color: white; font-weight: bold; font-size: 12px; padding: 5px;">Запомнить ответ клиента</button></div>';
                } else if (item.type === 'date') {
                    answers += '<div style="color: white; font-weight: bold;">' + item.text + '</div>' +
                            '<input class="form-control" id="answer-' + item.id + '" type="datetime-local" >' +
                            '<div style="margin: 2px;"><button onclick="' + action + '" ' +
                            'class="btn btn-success waves-effect waves-light">Запомнить ответ клиента</button></div>';
                } else if (item.type === 'dateAndSumm') {
                    answers += '<div style="color: white; font-weight: bold;">' + item.text + '</div>' +
                            '<div style="width: 100%;"><input class="form-control" id="answer-date-' + item.id + '" type="datetime-local" ></div>' +
                            '<div style="width: 100%;"><div><input class="form-control" id="answer-summ-' + item.id + '" type="number" value="0"> рублей</div></div>' +
                            '<button onclick="' + action + '" class="btn btn-success waves-effect waves-light" style="margin: 2px;' +
                            'min-width: 120px; color: white; font-weight: bold; font-size: 12px; padding: 5px;">Запомнить ответ клиента</button>';


                }
            }
        } else {
            mangoAnswersBlock.innerHTML = '';
            getBlockById('previousQuestion').style.display = 'none';
        }
        mangoAnswersBlock.innerHTML = answers;
        mangoQuestionTicketsBlock.style.display = 'block';
    } else {
        mangoQuestionBlock.innerHTML = '';
        mangoAnswersBlock.innerHTML = '';
        mangoQuestionTicketsBlock.style.display = 'none';
    }

    setCallData();
}

//сохранение текущего состояния (как правила перед возможной перезагрузкой страницы)
function setCallData(url = false) {
    let data = new Array();
    data['Step'] = Step;
    data['answers'] = new Array();
    data['parent'] = new Array();
    if (Step) {
        let step = Step;
        for (let i = 0; i < Step; i++) {
            let questionStep = Step - i;
            if (i > 0) {
                step = step - 1;
                data['answers'][questionStep] = Object.assign({}, answers[questionStep]);
                data['parent'][step] = parent[step];
            }
        }
    }
    data['resutAnswersAndQuestion'] = Object.assign({}, resutAnswersAndQuestion);
    data['active'] = active;
    data['userInfo'] = Object.assign({}, userInfo);
    data['resCredit'] = Object.assign({}, resCredit);
    data['lastCredit'] = Object.assign({}, lastCredit);
    data['name'] = name;
    data['nameClient'] = nameClient;
    data['userId'] = userId;
    data['callData'] = Object.assign({}, callData);
    data['tiketId'] = tiketId;
    let formData = new FormData();
    formData.append('data', JSON.stringify(Object.assign({}, data)));
    if (!url) {
        url = '/chats.php?chat=mango&method=setCallData' +
                '&managerMangoNumber=' + managerInfo.mango_number;
    }
    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        async: false,
        cache: false,
        contentType: false,
        processData: false
    });
}

// получение елементов HTML по его идентификатору
function getBlockById(id) {
    return document.getElementById(id);
}

// сброс шагов опроса
function stepOne() {
    Step = 1;
    parent[Step] = 0;
    mangoQuestionTickets();
}

//действия при выборе ответа
async function mangoAnswer(answerId, type, action, actionParams) {
    let text = '';
    if (eval('typeof ' + action + 'Mango') !== 'undefined') {
        eval(action + 'Mango(\'' + actionParams + '\', ' + answerId + ')');
    }
    let questionStep = Step - 1;
    let answer;
    let question = mangoQuestionBlock.textContent;
    if (type === 'date' || type === 'text') {
        answer = getBlockById('answer-' + answerId);
        text = answer.value;
    } else if (type === 'dateAndSumm') {
        let date = getBlockById('answer-date-' + answerId).value;
        let summ = getBlockById('answer-summ-' + answerId).value;
        text = 'До ' + date + ' оплачу ' + summ + ' рублей';
    } else {
        answer = getBlockById('answer-' + answerId);
        text = answer.textContent;
    }
    parent[Step] = answerId;
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
    mangoQuestionTickets();
}

// сохранение результатов опроса
function saveClientSurveyResultsMango() {
    let url = '/chats.php?chat=mango&method=saveClientSurveyResults' +
            '&userId=' + userId +
            '&managerMangoNumber=' + managerInfo.mango_number;
    mangoSend(url);
}

// создание задачи о напоминании звонка
async function sheduleCallUserMango(phone = data.incoming.from.number) {
    let url = '/chats.php?chat=mango&method=insuranceRefund' +
            '&managerId=' + managerInfo.id +
            '&managerName=' + managerInfo.name_1c +
            '&phone=' + phone +
            '&tiketId=' + tiketId;
    tiketId = await mangoSend(url);
}

// создание тикета при жалобах на технические проблемы
async function technicalProblemsMango(problem) {
    let url = '/chats.php?chat=mango&method=technicalProblems' +
            '&managerId=' + managerInfo.id +
            '&problem=' + problem +
            '&managerName=' + managerInfo.name_1c +
            '&phone=' + data.incoming.from.number +
            '&tiketId=' + tiketId;
    tiketId = await mangoSend(url);
}

// создание тикета при иных вариантах ответа
async function otherMango() {
    let url = '/chats.php?chat=mango&method=insuranceRefund' +
            '&managerId=' + managerInfo.id +
            '&managerName=' + managerInfo.name_1c +
            '&phone=' + data.incoming.from.number +
            '&tiketId=' + tiketId;
    tiketId = await mangoSend(url);
}

// создание тикета при дополнительных услугах
async function additionalServicesMango() {
    let url = '/chats.php?chat=mango&method=insuranceRefund' +
            '&managerId=' + managerInfo.id +
            '&managerName=' + managerInfo.name_1c +
            '&phone=' + data.incoming.from.number +
            '&tiketId=' + tiketId;
    tiketId = await mangoSend(url);
}

// создание тикета при возврате страховки
async function insuranceRefundMango() {
    let url = '/chats.php?chat=mango&method=insuranceRefund' +
            '&managerId=' + managerInfo.id +
            '&managerName=' + managerInfo.name_1c +
            '&phone=' + data.incoming.from.number +
            '&tiketId=' + tiketId;
    tiketId = await mangoSend(url);
}

// создание тикета при жалобах на службу взыскания
async function complaintToTheCollectionServiceMango() {
    let url = '/chats.php?chat=mango&method=complaintToTheCollectionService' +
            '&managerId=' + managerInfo.id +
            '&managerName=' + managerInfo.name_1c +
            '&phone=' + data.incoming.from.number +
            '&tiketId=' + tiketId;
    tiketId = await mangoSend(url);
}

// отправка сообщения при потверждении наличия записи разговора
function sendMessegeUserOnEmailInTicketsMango() {
    let url = '/chats.php?chat=mango&method=sendMessegeUserOnEmailInTickets' +
            '&userId=' + userId +
            '&managerMangoNumber=' + managerInfo.mango_number +
            '&tiketId=' + tiketId;
    mangoSend(url);
}

//предыдущий вопрос
function previousQuestion() {
    Step = Step - 2;
    if (Step < 1) {
        Step = 1;
        parent = false;
        parent = [];
        parent[Step] = 0;
    }
    mangoQuestionTickets();
}

//принять вызов
async function mangoAcceptCall() {
    let url = '/chats.php?chat=mango&method=acceptCall'
            + '&holded_call_id=' + callData.connected.call_id
            + '&transfer_initiator_number=' + callData.incoming.from.number
            + '&transferred_call_id=' + callData.incoming.call_id;
    let res = await mangoSend(url);
    if (res) {
        active = true;
        mangoAcceptCallButton.style.display = 'none';
        mangoEndCallButton.style.display = 'block';
        mangoCommentBlockDiv.style.display = 'none';
        return res.Data;
    }
}

// завершить разговор
async function mangoEndCall() {
    let url = '/chats.php?chat=mango&method=endCall&callId=' + callData.incoming.call_id +
            '&managerMangoNumber=' + managerInfo.mango_number;
    let res = await mangoSend(url);
    if (res) {
        active = true;
        mangoQuestionTicketsBlock.style.display = 'none';
        mangoAcceptCallButton.style.display = 'none';
        mangoEndCallButton.style.display = 'none';
        mangoCommentBlockDiv.style.display = 'block';
        getBlockById('mangoSendComment').style.display = 'block';
        let url = '/chats.php?chat=mango&method=setCallDataByEndCall' +
                '&managerMangoNumber=' + managerInfo.mango_number +
                '&managerId=' + managerInfo.id +
                '&userId=' + userId +
                '&ticket=' + tiketId;
        fetch(url);
    }
}

//отправить коментарий
async function mangoSendComment() {
    active = false;
    let text = getBlockById('mangoComment').value;
    let url = '/chats.php?chat=mango&method=addComment' +
            '&text=' + text +
            '&userName=' + name +
            '&tiketId=' + tiketId +
            '&userId=' + userId +
            '&managerId=' + managerInfo.id;
    let res = await mangoSend(url);
    Step = 1;
    parent[Step] = 0;
    answers = [];
    if (res) {
        getBlockById('mangoComment').value = '';
        mangoDialogClose();
    }
    tiketId = 0;
}

//инициализация вызова
async function mangoDialogInit() {
    let obj;
    if (managerInfo.mango_number) {
        let url = '/chats.php?chat=mango&method=callStatus&managerMangoNumber=' + managerInfo.mango_number;
        obj = await mangoSend(url);
    }
    if (obj && !active) {
        data = obj;
        if (obj.callData) {
            active = true;
            setDataInfo(obj.callData);
        } else {
            Step = 1;
            active = false;
        }
        await mangoDialogOpen();
        await mangoAcceptCall();
    }
    let disconnect = await getDisconnectStatus();
    if (disconnect) {
        getBlockById('mangoDialogText').innerHTML = 'Вызов завершен';
        await mangoEndCall();
        await setDisconnect();
        active = false;
    }
}

// удаления дампа завершения вызова
async function setDisconnect() {
    let url = '/chats.php?chat=mango&method=setDisconnected&managerMangoNumber=' + managerInfo.mango_number;
    let obj = await mangoSend(url);
    return obj;
}

// проверка завершения вызова
async function getDisconnectStatus() {
    let url = '/chats.php?chat=mango&method=getDisconnected&managerMangoNumber=' + managerInfo.mango_number;
    let obj = await mangoSend(url);
    return obj;
}

//установка параметров среды (как правило происходит после перезагрузки или  переходе с другой страницы)
async function setDataInfo(call = data) {
    if (call.Step) {
        Step = Number(call.Step) - 1;
    } else {
        Step = 1;
    }
    if (call.active) {
        active = call.active;
    } else {
        active = false;
    }
    if (call.name) {
        name = call.name;
    } else {
        name = false;
    }
    if (call.nameClient) {
        nameClient = call.nameClient;
    } else {
        nameClient = false;
    }
    if (call.answers) {
        answers = call.answers;
    } else {
        answers = [];
    }
    if (call.parent) {
        for (let i = -1; i < Step; i++) {
            if (call.parent[i]) {
                parent[i] = call.parent[i];
                parent[Step] = call.parent[Step];
            }
        }
    } else {
        parent[Step] = 0;
    }
    if (call.resutAnswersAndQuestion) {
        resutAnswersAndQuestion = call.resutAnswersAndQuestion;
    } else {
        resutAnswersAndQuestion;
    }
    if (call.userInfo) {
        userInfo = call.userInfo;
    } else {
        userInfo;
    }
    if (parent[Step] === 0) {
        Step = 1;
    }
    if (call.tiketId) {
        tiketId = call.tiketId;
    }
    if (call.resCredit) {
        resCredit = call.resCredit;
    } else {
        resCredit = false;
    }
    if (call.lastCredit) {
        lastCredit = call.lastCredit;
    } else {
        lastCredit = false;
    }
    return true;
}

//атправка GET запроса
async function mangoSend(url) {
    let results = await fetch(url);
    let res = await results.json();
    if (res) {
        return res.Data;
    }
}

// проверка наличия входящего звонка по интервалу
if (!is_developer)
    setInterval(mangoDialogInit, 3000);

// проверка наличия входящего звонка при открытии страницы
mangoDialogInit();