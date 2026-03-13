<?php

namespace chats\mango\traits\questions\ComplaintToTheCollectionService;

use chats\mango\traits\questions\ComplaintToTheCollectionService\questionsComplaintToTheCollectionService AS Question;


trait answersComplaintToTheCollectionService {

    use Question;

    public function answersStep_3() {
        return [
            [
                'text' => 'Звонки третьим лицам',
                'type' => 'button',
                'action' => 'task.createTask(0, \'complaintCollectionService\');task.logging(task.ticketId, \''.$this->questionStep_3().'\', this.textContent);',
                'childs' => [
                    'question' => $this->questionStep_4(),
                    'answers' => $this->answersStep_4()
                ]
            ],
            [
                'text' => 'Звонки на работу',
                'type' => 'button',
                'action' => 'task.createTask(0, \'complaintCollectionService\');task.logging(task.ticketId, \''.$this->questionStep_3().'\', this.textContent);',
                'childs' => [
                    'question' => $this->questionStep_4(),
                    'answers' => $this->answersStep_4()
                ]
            ],
            [
                'text' => ' Взаимодействие с клиентом',
                'type' => 'button',
                'action' => 'task.createTask(0, \'complaintCollectionService\');task.logging(task.ticketId, \''.$this->questionStep_3().'\', this.textContent);',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => $this->questionStep_4(),
                    'answers' => $this->answersStep_4()
                ]
            ]
        ];
    }

    public function answersStep_4() {
        return [
            [
                'text' => 'Выберите дату и время озвученную клиентом',
                'type' => 'date',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_4().'\', task.getDateFild();',
                'childs' => [
                    'question' => $this->questionStep_5(),
                    'answers' => $this->answersStep_5()
                ]
            ],
            [
                'text' => 'Клиент не помнит дату и время звонка',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_4().'\', this.textContent);',
                'childs' => [
                    'question' => $this->questionStep_5(),
                    'answers' => $this->answersStep_5()
                ]
            ]
        ];
    }

    public function answersStep_5() {
        return [
            [
                'text' => 'Введите ответ клиента',
                'type' => 'text',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_5().'\', task.getTextFild());',
                'childs' => [
                    'question' => $this->questionStep_6(),
                    'answers' => $this->answersStep_6()
                ]
            ],
            [
                'text' => 'Клиент не помнит кто звонил',
                'type' => 'button',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_5().'\', this.textContent);',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => $this->questionStep_6(),
                    'answers' => $this->answersStep_6()
                ]
            ]
        ];
    }

    public function answersStep_6() {
        return [
            [
                'text' => 'Введите ответ клиента',
                'type' => 'text',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_6().'\', task.getTextFild());',
                'childs' => [
                    'question' => $this->questionStep_7(),
                    'answers' => $this->answersStep_7()
                ]
            ],
            [
                'text' => 'Клиент не помнит с какого номера звонили',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_6().'\', this.textContent);',
                'childs' => [
                    'question' => $this->questionStep_7(),
                    'answers' => $this->answersStep_7()
                ]
            ]
        ];
    }

    public function answersStep_7() {
        return [
            [
                'text' => 'Введите ответ клиента',
                'type' => 'text',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_7().'\', task.getTextFild());',
                'childs' => [
                    'question' => $this->questionStep_8(),
                    'answers' => $this->answersStep_8()
                ]
            ],
            [
                'text' => 'Клиент не помнит как представился сотрудник',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_7().'\', this.textContent);',
                'childs' => [
                    'question' => $this->questionStep_8(),
                    'answers' => $this->answersStep_8()
                ]
            ]
        ];
    }

    public function answersStep_8() {
        return [
            [
                'text' => 'Введите ответ клиента',
                'type' => 'text',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_8().'\', task.getTextFild());',
                'childs' => [
                    'question' => $this->questionStep_9(),
                    'answers' => $this->answersStep_9()
                ]
            ],
            [
                'text' => 'Клиент не помнит о чем говорили',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => $this->questionStep_9(),
                    'answers' => $this->answersStep_9()
                ]
            ]
        ];
    }

    public function answersStep_9() {
        return [
            [
                'text' => 'Да',
                'type' => 'button',
                'action' => 'task.runTask(\'sendMessageToEmail\', '
                . '\'Мы готовы принять Ваши записи в рамках зафиксированой жалобы. Для этого отправьте нам скриншоты'
                . '/аудиозапись на электронную почту info@boostra.ru в теме письма необходимо указать ФИО и дату\')'
                . ';task.logging(task.ticketId, \''.$this->questionStep_9().'\', this.textContent);',
                'childs' => [
                    'question' => $this->questionStep_10(),
                    'answers' => $this->answersStep_10()
                ]
            ],
            [
                'text' => 'Нет',
                'type' => 'button',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_9().'\', this.textContent);',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => $this->questionStep_10(),
                    'answers' => $this->answersStep_10()
                ]
            ]
        ];
    }

    public function answersStep_10() {
        return [
            [
                'text' => 'Запишите ответ клиента',
                'type' => 'text',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_10().'\', task.getTextFild());',
                'childs' => [
                    'question' => $this->questionStep_11(),
                    'answers' => $this->answersStep_11()
                ]
            ]
        ];
    }

    public function answersStep_11() {
        return [
            [
                'text' => 'Да',
                'type' => 'button',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_11().'\', this.textContent);',
                'childs' => [
                    'question' => $this->questionStep_12(),
                    'answers' => $this->answersStep_12()
                ]
            ],
            [
                'text' => 'Нет',
                'type' => 'button',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_11().'\', this.textContent);',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => $this->questionStep_EndNoPayment(),
                    'answers' => $this->answersStep_14()
                ]
            ],
        ];
    }

    public function answersStep_12() {
        return [
            [
                'text' => 'На сайте',
                'type' => 'button',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_12().'\', this.textContent);',
                'childs' => [
                    'question' => $this->questionStep_13(),
                    'answers' => $this->answersStep_13()
                ]
            ],
            [
                'text' => 'По реквизитам',
                'type' => 'button',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_12().'\', this.textContent);',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => $this->questionStep_13(),
                    'answers' => $this->answersStep_13()
                ]
            ],
        ];
    }

    public function answersStep_13() {
        return [
            [
                'text' => 'Введите ответ клиента',
                'type' => 'dateAndSumm',
                'action' => 'task.createTask(task.getDaysForTask(), \'controlOfPaymentOnTheDatePromisedByTheClient\', 0);task.logging(task.ticketId, \''.$this->questionStep_12().'\', task.getDateSummFild());',
                'childs' => [
                    'question' => $this->questionStep_14(),
                    'answers' => $this->answersStep_14()
                ]
            ]
        ];
    }

    public function answersStep_14() {
        return [
            [
                'text' => 'Сохранить ответы клиента',
                'type' => 'button',
                'action' => 'saveClientSurveyResults',
            ]
        ];
    }

}
