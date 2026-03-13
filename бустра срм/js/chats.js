/* global clientId */

let sendMessageBlock;
let cache = '';
async function getMessages() {
    let block = document.getElementById('messangers');
    let url = '/chats.php?chat=main&method=getMessages&id=' + clientId;
    let results = await fetch(url);
    let res = await results.json();
    if (res.Data) {
        let messages = res.Data;
        let content = '';
        let style = '';
        let userName = '';
        let status = 0;
        let messageStatus = '';
        let messageClass = '';
        for (let i = 0; i < messages.length; i++) {
            if (messages[i].status === '1') {
                style = '';
                userName = 'Менеджер';
                if (messages[i].message_status === "0") {
                    messageStatus = 'Отправлено';
                    messageClass = 'style="color: #1885AB"';
                } else if (messages[i].message_status === "2") {
                    messageStatus = 'Прочитано';
                    messageClass = 'style="color: #1885AB"';
                } else {
                    messageStatus = 'Доставлено';
                    messageClass = 'style="color: #1885AB"';
                }
            } else {
                style = ' class="reverse"';
                userName = res.user_info.lastname + ' ' + res.user_info.firstname + ' ' + res.user_info.patronymic;
                if (messages[i].message_status === "0") {
                    messageStatus = 'Новое';
                    messageClass = 'class="countNewMessages" title="Пометить как прочитано" style="color:red;" onClick="readTheMessage(\'' + messages[i].id + '\')"';
                } else if (messages[i].message_status === "2") {
                    messageStatus = 'Прочитано';
                    messageClass = 'style="color: #1885AB"';
                } else {
                    messageStatus = '';
                    messageClass = '';
                }
            }
            content = content + '\
                <div class=\"chat-box\">\n\
                    <ul class="chat-list">\n\
                        <li' + style + '>\n\
                            <div class="chat-content">\n\
                                <h5>' + userName + '</h5>\n\
                                <div class="box bg-light-info">\n\
                                    ' + messages[i].text + '\
                                </div>\n\
                            </div>\n\
                            <div class="chat-time">\n\
                                <div>' + messages[i].date + '</div>\
                                <div>' + messages[i].chat_type + '</div>\
                                <div ' + messageClass + '>' + messageStatus + '</div>\
                            </div>\n\
                        </li>\n\
                    </ul>\n\
                </div>';
        }
        if (cache !== res.cache) {
            if (res.newMessages.length > 0) {
                setCountNewMessage(res.newMessages.length);
            } else {
                let blockCount = document.getElementById('countNewMessages');
                if(blockCount){
                    blockCount.innerHTML = '';
                }
            }
            cache = res.cache;
            block.innerHTML = content;
        }
    }
}

async function readTheMessage(id) {
    let url = '/chats.php?chat=main&method=readTheMessage&id=' + id;
    await fetch(url);
    await getMessages();
}

function setCountNewMessage(count) {
    document.getElementById('countNewMessages').innerHTML = count;
}

let sendMethodTypeWhatsApp = 'sendText';
let sendMethodTypeViber = 'sendText';
let activSenderBlock = false;
let attachFile = false;
let activPopap = false;
let attachFiles = false;
let activUploadDialog = false;
let uploadData = false;

async function sendMessageWhatsApp() {
    let url = false;
    let text = sendMessageBlock.value;
    if (sendMethodTypeWhatsApp == 'sendText') {
        url = '/chats.php?chat=whatsapp&id=' + clientId
                + '&text=' + text
                + '&method=' + sendMethodTypeWhatsApp
                + '&class=messages';
    } else {
        url = '/chats.php?chat=whatsapp&id=' + clientId
                + '&caption=' + text
                + '&filename=' + uploadData.Data.name
                + '&text=' + uploadData.Data.url
                + '&method=' + sendMethodTypeWhatsApp
                + '&class=messages';
    }
    let result = sendPreLoader(url);
    sendMethodTypeWhatsApp = 'sendText';
    uploadData = false;
    document.getElementById('attachFiles').innerHTML = '';
    await getMessages();
    sendMessageBlock.value = "";
}

function setInputText(){
    attachFile = false;
    document.getElementById('attachFile').style.display = 'none';
    document.getElementById('sendInMessanger').style.display = 'none';
    activSenderBlock = false;
}

async function sendMessageViber() {
    let url = false;
    let text = sendMessageBlock.value;
    if (sendMethodTypeViber === 'sendText') {
        url = '/chats.php?chat=viber&id=' + clientId
                + '&text=' + text
                + '&method=' + sendMethodTypeViber
                + '&class=messages';
    } else {
        url = '/chats.php?chat=viber&id=' + clientId
                + '&url=' + uploadData.Data.url
                + '&name=' + uploadData.Data.name
                + '&text=' + text
                + '&method=' + sendMethodTypeViber
                + '&class=messages';
    }
    let result = sendPreLoader(url);
    sendMethodTypeViber = 'sendText';
    uploadData = false;
    document.getElementById('attachFiles').innerHTML = '';
    await getMessages();
    sendMessageBlock.value = "";
}

async function sendPreLoader(url) {
    document.getElementById('sendInMessanger').style.display = 'none';
    let preloader = document.getElementById('chatPreloader');
    preloader.style.display = 'block';
    let result = await fetch(url);
    if (result) {
        preloader.style.display = 'none';
        activSenderBlock = false;
        return result;
    }
}

function attachFileDialog() {
    if (!attachFile) {
        attachFile = true;
        document.getElementById('attachFile').style.display = 'block';
    } else {
        attachFile = false;
        document.getElementById('attachFile').style.display = 'none';
    }
    document.getElementById('sendInMessanger').style.display = 'none';
    activSenderBlock = false;
}

function attachFoto() {
    if (activPopap) {
        closeUploadPopap(activPopap);
    }
    attachFileDialog();
    document.getElementById('uploadImagePopap').style.display = 'block';
    activPopap = 'uploadImagePopap';
    

}

function attachVideo() {
    if (activPopap) {
        closeUploadPopap(activPopap);
    }
    attachFileDialog();
    document.getElementById('uploadVideoPopap').style.display = 'block';
    activPopap = 'uploadVideoPopap';

}

function attachDocument() {
    if (activPopap) {
        closeUploadPopap(activPopap);
    }
    attachFileDialog();
    document.getElementById('uploadDocumentPopap').style.display = 'block';
    activPopap = 'uploadDocumentPopap';
}

function closeUploadPopap(popap) {
    document.getElementById(popap).style.display = 'none';
    if (activUploadDialog) {
        document.getElementById(activUploadDialog + 'Msg').innerHTML = '';
    }
}

function attach() {
    if (uploadData.Data) {
        let type = uploadData.Data.type;
        if (type === 'image') {
            sendMethodTypeViber = 'sendImage';
            sendMethodTypeWhatsApp = 'sendFile';
            attachFiles = '<img style="height: 100px; width: auto; cursor: pointer;" title="Посмотреть полно-размерное фото" onclick="openFullImage(this);" src="' + uploadData.Data.url + '"/>';
        } else if (type === 'video') {
            sendMethodTypeViber = 'sendVideo';
            sendMethodTypeWhatsApp = 'sendFile';
            attachFiles = '<video controls style="height: 100px; width: auto;" src="' + uploadData.Data.url + '"></audio>';
        } else if (type === 'document') {
            sendMethodTypeViber = 'sendFile';
            sendMethodTypeWhatsApp = 'sendFile';
            attachFiles = '<a target="_blank" href="' + uploadData.Data.url + '">Скачать документ для просмотра</a>';
        } else {
            sendMethodTypeViber = 'sendText';
            sendMethodTypeWhatsApp = 'sendText';
        }
        document.getElementById('attachFiles').innerHTML = attachFiles;
    }
}

function uploadFile(type) {
    activUploadDialog = type;
    $("form[name='" + type + "Upload']").submit(function (e) {
        var formData = new FormData($(this)[0]);
        $.ajax({
            url: 'chats.php?chat=main&method=uploadFiles&fileType=' + type,
            type: "POST",
            data: formData,
            async: false,
            success: function (msg) {
                uploadData = JSON.parse(msg);
                document.getElementById(type + 'Msg').innerHTML = uploadData.result;
                if (!uploadData.Data.error) {
                    closeUploadPopap(activPopap);
                    attach();
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });
        e.preventDefault();
    });
}

function snedMessage() {
    if (!activSenderBlock) {
        activSenderBlock = true;
        document.getElementById('sendInMessanger').style.display = 'block';
    } else {
        activSenderBlock = false;
        document.getElementById('sendInMessanger').style.display = 'none';
    }
    attachFile = false;
    document.getElementById('attachFile').style.display = 'none';
}

async function initChats() {
    sendMessageBlock = document.getElementById(MessageTextBlock);
    await getMessages();
    if (!is_developer) {
        setInterval(getMessages, 5000);
    }
}

function send(url) {
    var x = new XMLHttpRequest();
    x.open("GET", url);
    x.send(null);
    return x;
}

function closePopapImage() {
    document.getElementById('popapImageFull').innerHTML = '';
    document.getElementById('imagePopap').style.display = 'none';
}

function openFullImage(img) {
    document.getElementById('imagePopap').style.display = 'block';
    let image = '<img onclick="closePopapImage();" style="max-height :90vmin; max-width: 90vmax; cursor:pointer;" title="Закрыть" src="' + img.src + '"/>';
    document.getElementById('popapImageFull').innerHTML = image;
}