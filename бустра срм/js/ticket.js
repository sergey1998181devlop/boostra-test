function getBlocByIdForTicket(id) {
    if (document.getElementById(id)) {
        return document.getElementById(id);
    } else {
        return false;
    }
}

let globName = '';

async function get_clientFio(name) {
    let clientFioBlockInput = getBlocByIdForTicket('clientFioInput');
    globName = name;
    let data = false;
    let url = 'api.php?' + 'method=getClientInfoByFio&fio=' + clientFioBlockInput.value;
    data = await getInfo(url);
    let string = false;
    if (data) {
        string = '<div>';
        for (let i = 0; i < data.length; ++i) {
            string = string +
                    '<div class="promptBlockView" onclick="setDataByUserInfo(\'' + data[i]['id'] + '\', \'' + name + '\')">'
                    + data[i]['lastname'] + ' '
                    + data[i]['firstname'] + ' '
                    + data[i]['patronymic'] + ' '
                    + data[i]['birth']
                    + '<br> тел. ' + data[i]['phone_mobile'] +
                    '</div>';
        }
        string = string + '</div>';
    }
    return string;
}

function objectToArray(data) {
    let newData = new Array();
    let keys = Object.keys(data);
    let values = Object.values(data);
    for (let i = 0; i < keys.length; ++i) {
        newData[keys[i]] = values[i];
    }
    return newData;
}

async function setDataByUserInfo(userId, fildName) {
    let clientPhoneBlockInput = getBlocByIdForTicket('clientPhoneInput');
    let clientBerthDateBlockInput = getBlocByIdForTicket('clientBerthDateInput');
    let clientFioBlockInput = getBlocByIdForTicket('clientFioInput');
    globName = fildName;
    let url = 'api.php?' + 'method=getClientInfoById&id=' + userId;
    let data = await getInfo(url);
    clientFioBlockInput.value = data.lastname + ' ' + data.firstname + ' ' + data.patronymic;
    clientPhoneBlockInput.value = data.phone_mobile;
    clientBerthDateBlockInput.value = data.birth;
    closeBlockPrompt(fildName);
}

async function setDataOrder(orderId, fildName) {
    let contractNumberBlockInput = getBlocByIdForTicket('contractNumberInput');
    let orderNumberBlockInput = getBlocByIdForTicket('orderNumberInput');

    let creditSummBlockInput = getBlocByIdForTicket('creditSummInput');
    let creditDateBlockInput = getBlocByIdForTicket('creditDateInput');
    let clientPhoneBlockInput = getBlocByIdForTicket('clientPhoneInput');
    let clientBerthDateBlockInput = getBlocByIdForTicket('clientBerthDateInput');
    let clientFioBlockInput = getBlocByIdForTicket('clientFioInput');
    globName = fildName;
    let url = 'api.php?' + 'method=getInfoByOrderNumberId&order=' + orderId;
    let data = await getInfo(url);
    clientFioBlockInput.value = data.lastname + ' ' + data.firstname + ' ' + data.patronymic;
    clientPhoneBlockInput.value = data.phone_mobile;
    clientBerthDateBlockInput.value = data.birth;
    creditSummBlockInput.value = data.amount;
    creditDateBlockInput.value = new Date(data.date).toLocaleDateString("ru-RU");
    let Data = objectToArray(data);
    orderNumberBlockInput.value = data['1c_id'];
    contractNumberBlockInput.value = '';
    closeBlockPrompt(fildName);
}

async function setDataContract(contractId, fildName) {
    let contractNumberBlockInput = getBlocByIdForTicket('contractNumberInput');
    let orderNumberBlockInput = getBlocByIdForTicket('orderNumberInput');
    let creditSummBlockInput = getBlocByIdForTicket('creditSummInput');
    let creditDateBlockInput = getBlocByIdForTicket('creditDateInput');
    let clientPhoneBlockInput = getBlocByIdForTicket('clientPhoneInput');
    let clientBerthDateBlockInput = getBlocByIdForTicket('clientBerthDateInput');
    let clientFioBlockInput = getBlocByIdForTicket('clientFioInput');
    globName = fildName;
    let url = 'api.php?' + 'method=getInfoByContractNumberId&order=' + contractId;
    let data = await getInfo(url);
    clientFioBlockInput.value = data.lastname + ' ' + data.firstname + ' ' + data.patronymic;
    clientPhoneBlockInput.value = data.phone_mobile;
    clientBerthDateBlockInput.value = data.birth;
    creditSummBlockInput.value = data.zaim_summ;
    creditDateBlockInput.value = new Date(data.zaim_date).toLocaleDateString("ru-RU");
    contractNumberBlockInput.value = data.zaim_number;
    orderNumberBlockInput.value = '';
    closeBlockPrompt(fildName);
}


function get_clientBerthDate() {
    return false;
}



async function get_clientPhone(name) {
    let clientPhoneBlockInput = getBlocByIdForTicket('clientPhoneInput');
    globName = name;
    let url = 'api.php?' + 'method=getClientInfoByPhone&phone=' + clientPhoneBlockInput.value.replace(/[^\d]/g, '');
    let data = await getInfo(url);
    let string = false;
    if (data.length > 0) {
        string = '<div>';
        for (let i = 0; i < data.length; ++i) {
            string = string +
                    '<div class="promptBlockView" onclick="setDataByUserInfo(\'' + data[i]['id'] + '\', \'' + name + '\')">'
                    + data[i]['lastname'] + ' '
                    + data[i]['firstname'] + ' '
                    + data[i]['patronymic'] + ' '
                    + data[i]['birth'] + ' г.р. '
                    + '<br> тел. ' + data[i]['phone_mobile'] +
                    '</div>';
        }
        string = string + '</div>';
    }
    return string;
}

function get_creditDate() {
    return false;
}

function get_creditSumm() {
    return false;
}

async function get_orderNumber(name) {
    let orderNumberBlockInput = getBlocByIdForTicket('orderNumberInput');
    globName = name;
    let url = 'api.php?' + 'method=getInfoByOrderNumber&order=' + orderNumberBlockInput.value;
    let data = await getInfo(url);
    let string = false;
    if (data.length > 0) {
        string = '<div>';
        for (let i = 0; i < data.length; ++i) {
            string = string +
                    '<div class="promptBlockView" onclick="setDataOrder(\'' + data[i]['1c_id'] + '\', \'' + name + '\')">'
                    + '№ ' + data[i]['1c_id']
                    + ' от ' + new Date(data[i]['created']).toLocaleDateString("ru-RU") + ' <br>'
                    + ' сумма ' + data[i]['amount'] + ' '
                    + ' статус ' + data[i]['1c_status'] + ' <br>'
                    + data[i]['lastname'] + ' '
                    + data[i]['firstname'] + ' '
                    + data[i]['patronymic'] + ' '
                    + data[i]['birth'] + ' г.р. '
                    + '<br> тел. ' + data[i]['phone_mobile'] +
                    '</div>'
                    ;
        }
        string = string + '</div>';
    }
    return string;
}

async function get_contractNumber(name) {
    let contractNumberBlockInput = getBlocByIdForTicket('contractNumberInput');
    globName = name;
    let url = 'api.php?' + 'method=getInfoByContractNumber&order=' + contractNumberBlockInput.value;
    let data = await getInfo(url);
    let string = false;
    if (data.length > 0) {
        string = '<div>';
        for (let i = 0; i < data.length; ++i) {
            string = string +
                    '<div class="promptBlockView" onclick="setDataContract(\'' + data[i]['id'] + '\', \'' + name + '\')">'
                    + '№ ' + data[i]['zaim_number'] +
                    ' от ' + new Date(data[i]['zaim_date']).toLocaleDateString("ru-RU") +
                    ' сумма ' + data[i]['zaim_summ'] + ' <br>'
                    + data[i]['lastname'] + ' '
                    + data[i]['firstname'] + ' '
                    + data[i]['patronymic'] + ' '
                    + data[i]['birth'] + ' г.р. '
                    + '<br> тел. ' + data[i]['phone_mobile'] +
                    '</div>';
        }
        string = string + '</div>';
    }
    return string;
}

function get_appealDate() {

}

function setDataSearch(values, name) {
    globName = name;
    getBlocByIdForTicket('sourceInput').value = values;
    closeBlockPrompt(name);
}

function get_source(name) {
    globName = name;
    let string = '\
         <div class="promptBlockView" onclick="setDataSearch(\'Электронная почта\', \'' + name + '\')">Электронная почта</div>' +
            '<div class="promptBlockView" onclick="setDataSearch(\'Звонок\', \'' + name + '\')">Звонок</div>' +
            '<div class="promptBlockView" onclick="setDataSearch(\'Whats`App\', \'' + name + '\')">Whats`App</div>' +
            '<div class="promptBlockView" onclick="setDataSearch(\'Viber\', \'' + name + '\')">Viber</div>' +
            '<div class="promptBlockView" onclick="setDataSearch(\'Telegram\', \'' + name + '\')">Telegram</div>' +
            '<div class="promptBlockView" onclick="setDataSearch(\'SMS\', \'' + name + '\')">SMS</a>';
    return string;
}

function get_acceptFio() {

}

function openBlockPromptSearch(name) {
    globName = name;
    let block = getBlocByIdForTicket(name + 'Prompt');
    let text = eval("get_" + name + "('" + name + "')");
    if (text) {
        block.innerHTML = text;
        block.style.display = 'block';
    } else {
        closeBlockPrompt(name);
    }
}

function closeBlockPrompt(name) {
    globName = name;
    let block = getBlocByIdForTicket(name + 'Prompt');
    if (block) {
        block.style.display = 'none';
    }
}

async function get_tag(name) {
    globName = name;
    let url = 'api.php?' + 'method=getTicketsTags';
    let tags = await getInfo(url);
    let string = '';
    if (tags.length > 0) {
        for (let i = 0; i < tags.length; ++i) {
            string = string +
                    '<div class="promptBlockView" style="background: ' + tags[i]['color'] + '; padding: 3px; min-width: 200px;">' +
                    '<div onclick="setTicketTag(\'' + tags[i]['id'] + '\', \'' + name + '\')" style="float: left;font-weight:bold; font-size: 12px; cursor:pointer;">' + tags[i]['name'] + '</div>' +
                    '<div title="Редактировать тег" onclick="editTicketTag(\'' + tags[i]['id'] + '\', \'' + name + '\')" style="float: right; cursor:pointer"> <img style="width: 15px;" src="/design/manager/assets/images/icon/edit.svg"></div>' +
                    '<div style="clear: both;"></div>' +
                    '</div>';
        }
    }
    string = string +
            '<div class="promptBlockView" style="padding: 3px;">' +
            '<div onclick="newTicketTag(\'' + name + '\')" style="font-weight:bold; font-size: 12px; cursor:pointer;">Создать новый тег</div>' +
            '</div>';
    return string;
}

function newTicketTag(name) {
    globName = 'newTicketTag';
    closeBlockPrompt(name);
    let block = getBlocByIdForTicket('newTicketTag');
    block.innerHTML = '\
                    <div class="promptBlockView" style="">' +
            '<div><h5 style="color: black; padding: 3px;">Добавить новый тег</h5></div>' +
            '<form>' +
            '<div style="text-align: center; background:#f0f0f0; color: black">' +
            '<div>'
            + '<div>Введите название нового тега</div>' +
            '<input id="nameNewTag" type="text" value="">' +
            '</div>' +
            '<div>' +
            '<div>выберите цвет для нового тега</div>' +
            '<input id="colorNewTag" type="color" value="#f0f0f0">' +
            '</div>' +
            '<br/><button type="button" onclick="saveNewTicketTag(\'newTicketTag\');">Сохранить</button>' +
            '</div>' +
            '</form>' +
            '</div>' +
            '</div>';
    block.style.display = 'block';
}


async function saveNewTicketTag(name) {
    globName = name;
    let tagName = getBlocByIdForTicket('nameNewTag').value;
    let tagColor = getBlocByIdForTicket('colorNewTag').value;
    let url = 'api.php?' + 'method=newTicketsTag&name=' + tagName + '&color=' + tagColor.replace(/[^\w\d]/g, '');
    await getInfo(url);
    getBlocByIdForTicket(name).style.display = 'none';
    openBlockPrompt('tag');
    window.location.reload(false);

}

async function setTicketTag(id, name) {

    let tagBlockInput = getBlocByIdForTicket('tagInput');
    globName = name;
    let url = 'api.php?' + 'method=getTicketsTag&tag=' + id;
    let tag = await getInfo(url);
    tagBlockInput.value = tag.name;
    tagBlockInput.style.background = tag.color;
}

async function editTicketTag(id, name = false) {
    if (name) {
        closeBlockPrompt(name);
    } else {
        closeBlockPrompt(globName);
    }
    let url = 'api.php?' + 'method=getTicketsTag&tag=' + id;
    let tag = await getInfo(url);
    globName = 'newTicketTag';
    let block = getBlocByIdForTicket('newTicketTag');
    block.innerHTML = '\
        <div class="promptBlockView" style="">' +
            '<div><h5 style="color: black; padding: 3px;">Редактировать тег</h5></div>' +
            '<form>' +
            '<div style="text-align: center; background:#f0f0f0; color: black">' +
            '<div>'
            + '<div>Введите название тега</div>' +
            '<input id="nameTag" type="text" value="' + tag.name + '">' +
            '</div>' +
            '<div>' +
            '<div>выберите цвет для тега</div>' +
            '<input id="colorTag" type="color" value="' + tag.color + '">' +
            '<input id="idTag" type="hidden" value="' + tag.id + '">' +
            '</div>' +
            '<br/><button type="button" onclick="saveTicketTag(\'newTicketTag\');">Сохранить</button>' +
            '</div>' +
            '</form>' +
            '</div>' +
            '</div>';
    block.style.display = 'block';
}

async function saveTicketTag(name) {
    globName = name;
    let tagName = getBlocByIdForTicket('nameTag').value;
    let tagColor = getBlocByIdForTicket('colorTag').value;
    let tagId = getBlocByIdForTicket('idTag').value;
    let url = 'api.php?' + 'method=saveTicketTag&name=' + tagName + '&color=' + tagColor.replace(/[^\w\d]/g, '') + '&id=' + tagId;
    await getInfo(url);
    getBlocByIdForTicket(name).style.display = 'none';
    openBlockPrompt('tag');
}

async function openBlockPrompt(name) {
    let clientPhoneBlockInput = getBlocByIdForTicket('clientPhoneInput');
    if (globName) {
        closeBlockPrompt(globName);
    }
    globName = name;
    let stringInput = '';
    if (name === 'clientPhone') {
        stringInput = clientPhoneBlockInput.value.replace(/[^\d]/g, '');
    } else if (name === 'tag') {
        stringInput = 'new window';
    } else {
        stringInput = getBlocByIdForTicket(name + 'Input').value;
    }
    if (stringInput.length > 2) {
        let block = getBlocByIdForTicket(name + 'Prompt');
        let text = await eval("get_" + name + "('" + name + "')");
        if (text) {
            block.innerHTML = text;
            block.style.display = 'block';
            return true;
        } else {
            closeBlockPrompt(name);
        }
    } else {
        closeBlockPrompt(name);
    }
    return false;
}

function closeAllOpenBlocksPrompt() {
    closeBlockPrompt(globName);
}

async function getInfo(url) {
    let result = await fetch(url);
    let res = await result.json();
    if (res.result) {
        return res.data;
    }
}

function closeTicetCommentForm() {
    let block = getBlocByIdForTicket('modal_add_comment');
    let n = document.getElementsByClassName('modal-backdrop');
    for (let e of n){
        e.style.display = 'none';
    }
    block.style.display = 'none';
}

function openTicketCommentForm() {
    let n = document.getElementsByClassName('modal-backdrop');
    for (let e of n){
        e.style.display = 'block';
    }
    let block = getBlocByIdForTicket('modal_add_comment');
    block.style.display = 'block';
}

async function saveTicketComment() {
    let id_ticket = getBlocByIdForTicket('id_ticket').value;
    let manager = getBlocByIdForTicket('manager_ticket').value;
    let comment = getBlocByIdForTicket('comment_ticket').value;
    let url = 'api.php?method=addCommentTickets&id_ticket=' + id_ticket + '&manager=' + manager + '&comment=' + comment;
    let res = await getInfo(url);
    if (res) {
        closeTicetCommentForm();
    }
}










