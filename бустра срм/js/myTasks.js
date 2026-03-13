class Tasks extends mainApps {

    commentTaskBlock = 'commentTask';
    commentTaskTextBlock = 'commentText';
    commentTaskBlockManagerId;
    commentTaskBlockTaskId;
    commentTicketBlock = 'ticketComment';
    commentTicketTextBlock = 'commentText';
    newTicketBlock = 'addNewTicket';
    taskInfo;
    creditInfo;
    managerInfo;
    userInfo;
    lengthPromptText = 2;
    promptData = new Array();
    clientTelephoneBlock = 'clientTelInput';
    clientNameBlock = 'clientNameInput';
    clientBirthBlock = 'clientBirthInput';
    clientIdBlock = 'clientIdInput';
    bidNumberBlock = 'bidNumberInput';
    creditNumberBlock = 'creditNumberInput';
    creditIdBlock = 'creditId';
    tiketId = 0;
    json;

    // открыть блок подсказки
    openPromtBlock(data, blockPromptId) {
        let block = this.getBlock(blockPromptId);
        if (block) {
            this.activBlock = blockPromptId;
            this.setTextBlock(this.activBlock, this.setPromtText(data));
        }
    }

    setPromtText(data) {
        let string = '<div style="background: #f1effd; padding: 3px; width:100%;">';
        for (let el of data) {
            string += '<div class="promptBlockView" onclick="' + el.action + ';">' + el.content + '</div>';
        }
        return string + '</div>';
    }

    // закрыть активный блок
    closeActivBlock() {
        let block = this.getBlock(this.activBlock);
        if (block) {
            this.clearBlock(this.activBlock);
            this.closeBlock(this.activBlock);
        }
    }

    // получить информацию о клиентах по номеру телефона
    async getUsersByPhone(phone, blockPromtId) {
        if (phone.length > this.lengthPromptText) {
            let url = 'api.php?method=getClientInfoByPhone&phone=' + phone;
            this.json = await this.sendGet(url);
            if (this.json.data) {
                this.openPromtBlock(this.setJsonInfoByClient('task.setUserInfo'), blockPromtId);
            } else {
                this.closeActivBlock();
            }
        } else {
            this.closeActivBlock();
        }
    }

    // получить информацию о клиентах по Ф.И.О.
    async getUsersByFio(text, blockPromtId) {
        if (text.length > this.lengthPromptText) {
            let url = 'api.php?method=getClientInfoByFio&fio=' + text;
            this.json = await this.sendGet(url);
            if (this.json.data) {
                this.openPromtBlock(this.setJsonInfoByClient('task.setUserInfo'), blockPromtId);
            } else {
                this.closeActivBlock();
            }
        } else {
            this.closeActivBlock();
        }
    }

    // получить информацию о клиентах по номеру займа
    async getUsersByContractNumber(text, blockPromtId) {
        if (text.length > this.lengthPromptText) {
            let url = 'api.php?method=getInfoByOrderNumber&order=' + text;
            this.json = await this.sendGet(url);
            if (this.json.data) {
                this.openPromtBlock(this.setJsonInfoByContract('task.setContractInfo'), blockPromtId);
            } else {
                this.closeActivBlock();
            }
        } else {
            this.closeActivBlock();
        }
    }

    // получить информацию о клиентах по номеру договора
    async getUsersByCreditNumber(text, blockPromtId) {
        if (text.length > this.lengthPromptText) {
            let url = 'api.php?method=getInfoByContractNumberId&order=' + text;
            this.json = await this.sendGet(url);
            if (this.json.data) {
                this.openPromtBlock(this.setJsonInfoByContract('task.setContractInfo'), blockPromtId);
            } else {
                this.closeActivBlock();
            }
        } else {
            this.closeActivBlock();
        }
    }

    setContractInfo(dataNumber) {
        this.setClientOrderNumber(dataNumber);
        this.setClientCreditNumber(dataNumber);
        this.setClientCreditId(dataNumber);
        this.setValue(this.ticketAddCreditId, this.promptData[dataNumber].id);
        this.setUserInfo(dataNumber);
    }

    setJsonInfoByContract(action) {
        let data = new Array();
        if (this.json.data) {
            let el = false;
            for (let i = 0; i < this.json.data.length; i++) {
                el = this.json.data[i];
                data[i] = new Array();
                this.promptData[i] = el;
                data[i]['content'] = '<div style="font-size: 10px;">' + el.lastname + ' ' + el.firstname + ' ' + el.patronymic +
                        '<p style="font-size: 10px;">Дата рождения: ' + el.birth + ' <br>' +
                        'Телефон: ' + el.phone_mobile;
                if (el['1c_id']) {
                    data[i]['content'] += '<br>' + 'Номер заявки: ' + el['1c_id'];
                }
                if (el.zaim_number) {
                    data[i]['content'] += '<br>' + el.zaim_number;
                }
                data[i]['content'] += '</p></div>';
                data[i]['action'] = action + '(' + i + ')';
            }
        }
        return data;
    }

    // получение и установка информации из базы данных
    setJsonInfoByClient(action) {
        let data = new Array();
        if (this.json.data) {
            let el = false;
            for (let i = 0; i < this.json.data.length; i++) {
                el = this.json.data[i];
                data[i] = new Array();
                this.promptData[i] = el;
                data[i]['content'] = '<div style="font-size: 10px;">' + el.lastname + ' ' + el.firstname + ' ' + el.patronymic +
                        '<p style="font-size: 10px;">Дата рождения: ' + el.birth + ' <br>' +
                        'Телефон: ' + el.phone_mobile + '</p>' +
                        '</div>';
                data[i]['action'] = action + '(' + i + ')';
            }
        }
        return data;
    }

    // ввести номер заявки в поле
    setClientOrderNumber(dataNumber) {
        let block = this.getBlock(this.bidNumberBlock);
        if (block) {
            if (this.promptData[dataNumber]['1c_id']) {
                this.setValue(this.bidNumberBlock, this.promptData[dataNumber]['1c_id']);
                this.setValue('orderNumberInputNewTicket', this.promptData[dataNumber]['1c_id']);
            } else {
                this.clearInput('orderNumberInputNewTicket');
                this.clearInput(this.bidNumberBlock);
            }
        }
    }

    // ввести номер договора в поле
    setClientCreditNumber(dataNumber) {
        let block = this.getBlock(this.creditNumberBlock);
        if (block) {
            if (this.promptData[dataNumber].zaim_number) {
                this.setValue(this.creditNumberBlock, this.promptData[dataNumber].zaim_number);
                this.setValue('contractNumberInputNewTicket', this.promptData[dataNumber].zaim_number);
            } else {
                this.clearInput(this.creditNumberBlock);
                this.clearInput('contractNumberInputNewTicket');
            }
        }
    }

    // ввести id договора в поле
    setClientCreditId(dataNumber) {
        let block = this.getBlock(this.creditIdBlock);
        if (block) {
            this.setValue('clientIdInputNewTicket', this.promptData[dataNumber].id);
            this.setValue(this.creditIdBlock, this.promptData[dataNumber].id);
        }
    }

    // установка информации о клиенте
    setUserInfo(dataNumber) {
        this.setValue(this.ticketAddUserId, this.promptData[dataNumber].id);
        this.setValue(this.taskAddUserId, this.promptData[dataNumber].id);
        this.setValue(this.taskAddCreditId, this.promptData[dataNumber].id);
        this.setValue(this.ticketAddCreditId, this.promptData[dataNumber].id);
        this.setClientBirth(dataNumber);
        this.setClientName(dataNumber);
        this.setClientTelephone(dataNumber);
        this.setClientId(dataNumber);
        this.closeActivBlock();
    }

    // ввести id клиента в поле
    setClientId(dataNumber) {
        let block = this.getBlock(this.clientIdBlock);
        if (block) {
            //ticketAddUserId

            this.setValue(this.clientIdBlock, this.promptData[dataNumber].id);
        }
    }

    // ввести день рождения клиента в поле
    setClientBirth(dataNumber) {
        let block = this.getBlock(this.clientBirthBlock);
        if (block) {
            this.setValue('clientBerthDateInputNewTicket', this.promptData[dataNumber].birth);
            this.setValue(this.clientBirthBlock, this.promptData[dataNumber].birth);
        }
    }

    // ввести имя клиента в поле
    setClientName(dataNumber) {
        let block = this.getBlock(this.clientNameBlock);
        if (block) {
            this.setValue(this.clientNameBlock,
                    this.promptData[dataNumber].lastname + ' ' +
                    this.promptData[dataNumber].firstname + ' ' +
                    this.promptData[dataNumber].patronymic
                    );

            this.setValue('clientFioInputNewTicket',
                    this.promptData[dataNumber].lastname + ' ' +
                    this.promptData[dataNumber].firstname + ' ' +
                    this.promptData[dataNumber].patronymic
                    );
        }
    }

    // ввести номер телефона в поле
    setClientTelephone(dataNumber) {
        let block = this.getBlock(this.clientTelephoneBlock);
        if (block) {
            this.setValue('clientPhoneInputNewTicket', this.promptData[dataNumber].phone_mobile);
            this.setValue(this.clientTelephoneBlock, this.promptData[dataNumber].phone_mobile);
        }
    }

    // сохранить статус задачи
    async saveStatus(value, id = false) {
        if (!id) {
            id = this.taskInfo.id;
        }
        let url = 'api.php?method=updateTaskStatus&id=' + id + '&taskStatus=' + value;
        await this.sendGet(url);
        if (value === '2') {
            document.location.href = "/ccmytascks";
        }
        ;
    }

    // логирование опроса клиента
    logging(ticketId, question, answer) {
        let url = 'api.php?method=loggingSurvey&ticketId=' + ticketId +
                '&managerId=' + task.managerInfo.id +
                '&question=' + question +
                '&answer=' + answer;
        this.sendGet(url);
    }

    async saveTicketComment(id) {
        let text = this.getValue(id);
        let url = 'api.php?method=addCommentTickets' +
                '&tiсketId=' + this.tiketId +
                '&comment=' + text +
                '&managerId=' + this.managerInfo.id;
        console.log(url);
        let result = await this.sendGet(url);
        this.clearInput(this.commentTicketTextBlock);
        this.closeBlock(this.commentTicketBlock);
        window.location.reload(true);
    }

    taskAddInputChanel = 'inputChanelTask';
    taskAddDateComplition = 'dateComplition';
    taskAddTaskDate = 'taskDate';
    taskAddCreditId = 'creditId';
    taskAddUserId = 'userId';
    taskAddManagerId = 'managerId';
    taskAddTaskType = 'purpose';
    taskAddTicketId = 'ticketId';
    addTicketTaskBlock = 'addTicketTask';

    // сохранить новую задачу для тикета
    async saveTicketTask() {

        let inputChanel = this.getValue(this.taskAddInputChanel);
        let dateComplition = this.getValue(this.taskAddDateComplition);
        let taskDate = this.getValue(this.taskAddTaskDate);
        let creditId = this.getValue(this.taskAddCreditId);
        let userId = this.getValue(this.taskAddUserId);
        let managerId = this.getValue(this.taskAddManagerId);
        let taskType = this.getValue(this.taskAddTaskType);
        let ticketId = this.getValue(this.taskAddTicketId);
        let day = this.getCountDays(taskDate);
        let url = 'api.php?method=createNewTask&' +
                'ticketId=' + ticketId +
                '&userId=' + userId +
                '&taskType=' + taskType +
                '&taskDate=' + day +
                '&managerId=' + managerId +
                '&inputChanel=' + inputChanel +
                '&dateComplition=' + dateComplition +
                '&creditId=' + creditId +
                '&taskStatus=0';
        this.closeBlock(this.addTicketTaskBlock);
        await this.sendGet(url);
        window.location.reload(true);
    }

    ticketAddCreditId = 'creditId';
    ticketAddCreditNumber = 'creditNumberInput';
    ticketAddBidNumber = 'bidNumberInput';
    ticketAddUserId = 'userId';
    ticketAddClientPhone = 'clientTelInput';
    ticketAddClientBirth = 'clientBirthInput';
    ticketAddClientName = 'clientNameInput';
    ticketAddManagerId = 'managerId';
    ticketAddDateCreate = 'dateCreate';
    ticketAddInputChanel = 'inputChanel';
    ticketAddThem = 'them';
    ticketAddExecutManagerId = 'executManagerId';

    // сохранить новый тикет
    async saveNewTicket() {
        let creditId = this.getValue(this.ticketAddCreditId);
        let creditNumber = this.getValue(this.ticketAddCreditNumber);
        let bidNumber = this.getValue(this.ticketAddBidNumber);
        let userId = this.getValue(this.ticketAddUserId);
        let clientPhone = this.getValue(this.ticketAddClientPhone);
        let clientBirth = this.getValue(this.ticketAddClientBirth);
        let clientName = this.getValue(this.ticketAddClientName);
        let managerId = this.getValue(this.ticketAddManagerId);
        let dateCreate = this.getValue(this.ticketAddDateCreate);
        let inputChanel = this.getValue(this.ticketAddInputChanel);
        let them = this.getValue(this.ticketAddThem);
        let executManagerId = this.getValue(this.ticketAddExecutManagerId);
        let appealNumber = this.getValue('appealNumber');
        let url = 'api.php?method=createNewTicket&' +
                'creditId=' + creditId +
                '&executorManagerId=' + executManagerId +
                '&dateCreate=' + dateCreate +
                '&them=' + them +
                '&userId=' + userId +
                '&managerId=' + managerId +
                '&appealNumber=' + appealNumber +
                '&inputChanel=' + inputChanel;
        this.closeBlock(this.newTicketBlock);
        return await this.sendGet(url);
    }

    // сохранить комментарий задачи
    async saveCommentTask() {
        let text = this.getValue(this.commentTaskTextBlock);
        let url = '/api.php?method=saveCommentTasks' +
                '&comment=' + text +
                '&managerId=' + this.managerInfo.id +
                '&taskId=' + this.taskInfo.id;
        let result = await this.sendGet(url);
        this.clearInput(this.commentTaskTextBlock);
        this.closeBlock(this.commentTaskBlock);
        window.location.reload(true);
        return result;
    }
    
    async createTicket(them, userId, creditId, inputChanel){
        let url = 'api.php?method=createNewTicket&' +
                'creditId=' + creditId +
                '&them=' + them +
                '&userId=' + userId +
                '&managerId=' + this.managerInfo.id +
                '&inputChanel=' + inputChanel;
        return await this.sendGet(url);
    }
    
    getDaysForTask(){
        
    }
    
    getDateFild(){
        
    }
    
    getTextFild(){
        
    }
    
    getDateSummFild(){
        
    }
    
    async runTask(task, taskContent){
        let url = 'api.php?method=' + task + '&content=' + taskContent;
        this.createTask(0, task, 2);
        return await this.sendGet(url);
    }

    // создать новую задачу через day дней c task типом 
    async createTask(day, task, status = 0) {
        let url = 'api.php?method=createNewTask&' +
                'ticketId=' + this.taskInfo.ticketId +
                '&userId=' + this.userInfo.id +
                '&taskType=' + task +
                '&taskDate=' + day +
                '&inputChanel=' + this.taskInfo.inputChanel +
                '&creditId=' + this.creditInfo.id +
                '&taskStatus=' + status;
        return await this.sendGet(url);
    }

    // закрыть задачу
    async closeTask(id) {
        await this.saveStatus(2, id);
        document.location.href = "/ccmytascks";

    }

    async sendMessageToEmail(text, create = false, header) {
        let url = 'api.php?method=sendMessageToEmail&text=' + text + this.addMessageTextForEmail +
                '&to=' + this.userInfo.email + '&them=' + header;
        if (create) {
            this.createTask(0, 'sendMessageToEmail', 2);
        }
        let urlComment = '/api.php?method=saveCommentTasks' +
                '&comment=Отправлено сообщение на  Email "' + text +
                '"&managerId=' + this.managerInfo.id +
                '&taskId=' + this.taskInfo.id +
                '&from=' + this.userInfo.email;
        await this.sendGet(urlComment);
        return await this.sendGet(url);
    }

    addMessageTextForEmail = '<p>Уважаемый(ая) клиент, ' +
            'убедительная просьба дальнейшую переписку по вашему обращению вести в одном из доступных Вам мессенджеров, ' +
            ' <a href="https://api.whatsapp.com/send?phone=79649733185">WhatsApp</a> или ' +
            '<a href="viber://pa?chatURI=boostrarubot">Viber</a>' +
            '</p>';

    setUserName() {
        return this.userInfo.firstname + ' ' + this.userInfo.patronymic;
    }

    setApeealNumber() {
        return this.taskInfo.ticketId;
    }

    async sendMessageToSms(text, create = false) {
        let url = 'api.php?method=sendMessageToSms&text=' + text + this.addMessageTextForEmail +
                '&phone=' + this.userInfo.phone_mobile;
        if (create) {
            this.createTask(0, 'sendMessageToSms', 2);
        }
        let urlComment = '/api.php?method=saveCommentTasks' +
                '&comment=Отправлено СМС сообщение "' + text +
                '" на номер ' + this.userInfo.phone_mobile +
                '&managerId=' + this.managerInfo.id +
                '&taskId=' + this.taskInfo.id +
                '&phone=' + this.userInfo.phone_mobile;
        await this.sendGet(urlComment);
        return await this.sendGet(url);
    }

    async sendMessageToMessengers(text, create = false) {
        let url = 'api.php?method=sendMessageToMessangers&text=' + text +
                '&id=' + this.userInfo.id + '&phone=' + this.userInfo.phone_mobile;
        if (create) {
            this.createTask(0, 'sendMessageToMessangers', 2);
        }
        let urlComment = '/api.php?method=saveCommentTasks' +
                '&comment=Отправлено сообщение"' + text +
                '"  в мессенджер&managerId=' + this.managerInfo.id +
                '&taskId=' + this.taskInfo.id;
        await this.sendGet(urlComment);
        return await this.sendGet(url);
    }

    // отправить в отстойник
    async sendToTheSump(taskId) {

    }

    commentNewTiketForAppeal = 'commentNewTiketForAppeal';
    appealNumberNewTiketForAppeal = 'appealNumberNewTiketForAppeal';
    managerIdNewTiketForAppeal = 'managerIdNewTiketForAppeal';
    dateCreateNewTiketForAppeal = 'dateCreateNewTiketForAppeal';
    inputChanelNewTiketForAppeal = 'inputChanelNewTiketForAppeal';
    themNewTiketForAppeal = 'themNewTiketForAppeal';
    userIdNewTiketForAppeal = 'userId';
    creditIdNewTiketForAppeal = 'creditId';
    executManagerIdNewTiketForAppeal = 'executManagerIdNewTiketForAppeal';

    async saveNewTicketForAppeal() {
        let appealNumberNewTiketForAppeal = this.getValue(this.appealNumberNewTiketForAppeal);
        let creditIdNewTiketForAppeal = this.getValue(this.creditIdNewTiketForAppeal);
        let executManagerIdNewTiketForAppeal = this.getValue(this.executManagerIdNewTiketForAppeal);
        let dateCreateNewTiketForAppeal = this.getValue(this.dateCreateNewTiketForAppeal);
        let themNewTiketForAppeal = this.getValue(this.themNewTiketForAppeal);
        let userIdNewTiketForAppeal = this.getValue(this.userIdNewTiketForAppeal);
        let managerIdNewTiketForAppeal = this.getValue(this.managerIdNewTiketForAppeal);
        let inputChanelNewTiketForAppeal = this.getValue(this.inputChanelNewTiketForAppeal);
        let commentNewTiketForAppeal = this.getValue(this.commentNewTiketForAppeal);
        let url = 'api.php?method=createNewTicket&' +
                'inputChanel=' + inputChanelNewTiketForAppeal + '&' +
                'managerId=' + managerIdNewTiketForAppeal + '&' +
                'userId=' + userIdNewTiketForAppeal + '&' +
                'status=1' + '&' +
                'them=' + themNewTiketForAppeal + '&' +
                'dateCreate=' + dateCreateNewTiketForAppeal + '&' +
                'executorManagerId=' + executManagerIdNewTiketForAppeal + '&' +
                'creditId=' + creditIdNewTiketForAppeal + '&' +
                'appealNumber=' + appealNumberNewTiketForAppeal + '&' +
                'commentTicket=' + commentNewTiketForAppeal;
        await this.sendGet(url);
        document.location.href = "/appeals";
    }

}
;
