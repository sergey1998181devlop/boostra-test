class CustomerSurvey extends mainApps {

    step = 1;
    question = 'question-';
    customer = 'customerSurvey-';
    allCustomers = 'customerSurvey';
    previousQuestion = 'previousQuestion-';
    Questions = 'Questions';
    taskСompleted = 'taskСompleted';
    lastStep = 0;
    promiseSumm = 'answer-summ-';
    promiseDate = 'answer-date-';

    constructor() {
        super();
        if (this.getAllCustomers().length > 0) {
            this.openActivBlock();
        }
        if (this.getBlock(this.Questions)) {
            this.openBlock(this.Questions);
        }
        if (this.getBlock(this.taskСompleted)) {
            this.closeBlock(this.taskСompleted);
        }
    }

    setStep(step, last) {
        this.step = this.step + step;
        this.lastStep = last;
    }

    // вернуться к предыдущему вопросу
    setPreviousQuestion() {
        this.step = this.step - this.lastStep;
        if (this.step < 1) {
            this.step = 1;
        }
        this.openActivBlock();
    }

    // показать активный блок
    openActivBlock() {
        let count = this.getAllCustomers().length + 1;
        for (let i = 1; i < count; i++) {
            if (i !== this.step) {
                this.closeBlock(this.customer + i);
            } else {
                this.openBlock(this.customer + i);
            }
        }
    }

    // запомнить дату и время названную клиентом
    saveDateAndSumm(id) {
        let promiseDate = this.getTextInput(this.promiseDate + id);
        let promiseSumm = this.getTextInput(this.promiseSumm + id);
        this.loggingTheSurvey(id, promiseDate + ' обещает внести ' + promiseSumm);
        let oneDay = 1000 * 60 * 60 * 24;
        let days = Math.ceil((new Date(promiseDate) - new Date()) / oneDay);
        task.createTask(days, 'controlOfPaymentOnTheDatePromisedByTheClient');
    }

    // логирование результата в конце опроса
    closeCustomer(id) {
        this.loggingTheSurvey(id, 'Разговор завершён');
        this.closeBlock(this.Questions);
        this.openBlock(this.taskСompleted);
    }

    // логирование опроса клиента
    loggingTheSurvey(idTextBlock, inputText) {
        let question = this.getTextBlock(this.question + idTextBlock);
        let answer = inputText;
        let url = 'api.php?method=loggingSurvey&ticketId=' + task.taskInfo.ticketId +
                '&managerId=' + task.managerInfo.id +
                '&question=' + question +
                '&answer=' + answer;
        this.sendGet(url);
    }

    // открыть блок опроса
    // block id текущего блока
    //
    // text текст для логирования
    openCustomerSurvay(block, inputText) {
        this.openActivBlock();
        this.loggingTheSurvey(block, inputText);
    }

    // получить все блоки опроса
    getAllCustomers() {
        return this.getAllBlocks(this.allCustomers);
    }

}
;