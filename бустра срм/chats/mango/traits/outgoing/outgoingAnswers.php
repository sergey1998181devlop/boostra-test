<?php

namespace chats\mango\traits\outgoing;

use chats\mango\traits\questions\ComplaintToTheCollectionService\questionsComplaintToTheCollectionService;
use chats\mango\traits\questions\ComplaintToTheCollectionService\answersComplaintToTheCollectionService;

trait outgoingAnswers {

    use questionsComplaintToTheCollectionService,
        answersComplaintToTheCollectionService;

    public function outgoingAnswerStart() {
        return [
            [
                'text' => 'Клиент взял трубку',
                'type' => 'button',
                'action' => '',
                'actionParams' => '',
                'childs' => [
                    'question' => $this->outgoingQuestionStep_1(),
                    'answers' => $this->outgoingAnswerStep_1()
                ]
            ],
            [
                'text' => 'Клиент не берет трубку',
                'type' => 'button',
                'action' => '',
                'actionParams' => '',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => 'Отправить сообщение',
                    'answers' => $this->outgoingAnswerSendMessage()
                ]
            ],
        ];
    }

    public function outgoingAnswerEnd() {
        return [
            [
                'text' => 'Завершить',
                'type' => 'button',
                'action' => '',
                'actionParams' => ''
            ]
        ];
    }

    public function outgoingAnswerSendMessage() {
        return [
            [
                'text' => 'Отправить',
                'type' => 'text',
                'action' => 'sendMessage',
                'actionParams' => '',
                'childs' => [
                    'question' => '',
                    'answers' => $this->outgoingAnswerEnd()
                ]
            ]
        ];
    }

    public function outgoingAnswerStep_1() {
        return [
            [
                'text' => 'На сайте',
                'type' => 'button',
                'action' => '',
                'actionParams' => '',
                'childs' => [
                    'question' => $this->questionStep_13(),
                    'answers' => $this->answersStep_13()
                ]
            ],
            [
                'text' => 'По реквизитам',
                'type' => 'button',
                'action' => '',
                'actionParams' => '',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => $this->questionStep_13(),
                    'answers' => $this->answersStep_13()
                ]
            ],
            [
                'text' => 'Отказывается платить',
                'type' => 'button',
                'action' => '',
                'actionParams' => '',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => 'Ваш ответ принят. Спасибо за уделенное время. Всего Вам доброго досвидания!',
                    'answers' => $this->answersStep_14()
                ]
            ],
        ];
    }

}
