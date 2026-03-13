<?php

namespace chats\mango\traits\outgoing;

use chats\mango\traits\questions\ComplaintToTheCollectionService\questionsComplaintToTheCollectionService;
use chats\mango\traits\questions\ComplaintToTheCollectionService\answersComplaintToTheCollectionService;

trait outgoingQuestions {

    use questionsComplaintToTheCollectionService,
        answersComplaintToTheCollectionService;

    public function outgoingQuestionStep_1() {
        return 'Добрый день {clientName}! Специалист претезионного отдела {managerName}, '
                . 'организация Бустра. Разговор наш записывается. У Вас имеется просроченная задолжность на сумму {amountOfDebt}.'
                . ' В течении 2х дней необходимо пролонгировать или оплатить полностью Ваш долг. Каким образом будете Совершать платеж? на сайте или по реквизитам?';
    }

    public function outgoingQuestionStart() {
        return 'Позвонить клиенту.';
    }

}
