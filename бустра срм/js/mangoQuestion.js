
class MangoDialog extends Tasks {

    parent = 0;
    answer = '';
    question = '';
    managerInfo;
    activ = false;
    userInfo;
    userName = 'Не найден';
    ticketId = 0;
    creditInfo;
    data = {};

    // проверка завершения вызова
    async getDisconnectStatus() {
        let url = '/chats.php?chat=mango&method=getDisconnected&managerMangoNumber=' + this.managerInfo.mango_number;
        let obj = await this.sendGet(url);
        return obj;
    }

    // проверка установки соединения входящего вызова
    async callStatus() {
        let url = '/chats.php?chat=mango&method=callStatus&managerMangoNumber=' + this.managerInfo.mango_number;
        let obj = await this.sendGet(url);
        return obj;
    }

    // проверка входящего вызова и завершения вызова
    dialogInit = async () => {
        let disconnect = await this.getDisconnectStatus();
        if (!this.activ) {
            let callStatus = await this.callStatus();
            await this.initInfo(callStatus);
            this.activ = true;
        } else if (!this.activ && !disconnect) {
            this.getCallDataInLocalFile();
            this.activ = true;
        } else if (this.activ && !disconnect) {
            this.activ = true;
        } else if (disconnect) {
            this.mangoEndCall();
        }
    }

    getCallDataInLocalFile() {

    }

    async setCallDataInLocalFile() {
        let url = '/chats.php?chat=mango&method=setNewCallData' +
                '&managerMangoNumber=' + this.managerInfo.mango_number;
        await this.sendPost(url, this.data);
    }

    mangoDialogBlock = 'mangoDialog';

    // инициализация и получение информации о клиенте и его займе
    async initInfo(callStatus) {
        if (callStatus.Data) {
            this.activ = true;
            this.data.managerInfo = this.managerInfo;
            let data = callStatus.Data;
            this.data.call = data.callData;
            let phone = data.incoming.from.number;
            let userData = await this.getUserInfo(phone);
            if (userData.Data) {
                this.userInfo = userData.Data;
                this.data.userInfo = this.userInfo;
                this.setUserInfo();
                this.openBlock(this.mangoDialogBlock);
                this.setTextBlock(this.mangoLastCreditBlock, ' Идет поиск информации...');
                let creditData = await this.getUserCreditInfo(this.userInfo.UID);
                if (!creditData.faultcode) {
                    this.creditInfo = creditData;
                    this.data.creditInfo = creditData;
                } else {
                    this.setTextBlock(this.mangoLastCreditBlock, ' Информация не найдена.');
                    this.data.creditInfo = false;
                }
            }
        }
        await this.setCallDataInLocalFile();
    }

    mangoLastCreditBlock = 'mangoLastCredit';
    mangoUserNameBlock = 'mangoUserName';
    mangoUserPhoneBlock = 'mangoUserPhone';
    mangoUseBerthDayBlock = 'mangoUseBerthDay';
    mangoSendCommentButton = 'mangoSendComment';
    previousQuestionButton = 'previousQuestion';
    mangoAcceptCallButton = 'mangoAcceptCall';
    mangoEndCallButton = 'mangoEndCall';
    mangoDialogTextBlock = 'mangoDialogText';
    userAccountLink = 'LinkUserAccount';

    // вывод информации о клиенте и его займае
    setUserInfo() {
        this.setTextBlock(this.mangoUserNameBlock, this.setUserNameAndPatronymic());
        this.setTextBlock(this.mangoUserPhoneBlock, this.setUserAccountLink(this.userInfo.phone_mobile));
        this.setTextBlock(this.mangoUseBerthDayBlock, this.userInfo.birth);
        this.setTextBlock(this.mangoDialogTextBlock, ' Входящий звонок от ' + this.setUserAccountLink(this.userInfo.lastname + ' ' + this.setUserNameAndPatronymic()));
        this.openBlock(this.mangoEndCallButton);
        this.getBlock(this.userAccountLink).href = '/client/' + this.userInfo.id;
        this.closeBlock(this.mangoSendCommentButton);
        this.closeBlock(this.previousQuestionButton);
        this.closeBlock(this.mangoAcceptCallButton);
    }

    // установка ссылки на страницу клиента
    setUserAccountLink(text) {
        return '<a href="/client/' + this.userInfo.id + '">' + text + '</a>';
    }

    // установка имени и отчества клиента
    setUserNameAndPatronymic() {
        return this.userInfo.firstname + ' ' + this.userInfo.patronymic;
    }

    // получить информацию о займе клиента
    async getUserCreditInfo(uid) {
        let url = '/chats.php?chat=main&method=getUserCreditInfo&uid = ' + uid;
        let res = await this.sendGet(url);
        if (res) {
            return res;
        }
        return false;
    }

    // получить информацию о клиенте по номеру телефона
    async getUserInfo(userPhoneNumber) {
        let url = '/chats.php?chat=main&method=getUserInfoByPhone&phone=' + userPhoneNumber;
        let res = await this.sendGet(url);
        if (res) {
            return res;
        }
        return false;
    }

    mangoCommentBlock = 'mangoCommentBlock';
    
    // завершить разговор
    async mangoEndCall() {
        console.log(this.data.call);
        let url = '/chats.php?chat=mango&method=endCall&callId=' + this.data.call.incoming.call_id +
                '&managerMangoNumber=' + this.managerInfo.mango_number;
        let res = await this.sendGet(url);
        if (res) {
            this.closeBlock(this.mangoEndCallButton);
            this.openBlock(this.mangoSendCommentButton);
            this.openBlock(this.mangoCommentBlock);
        }
    }

    start() {
        this.dialogInit;
    }
}
;
