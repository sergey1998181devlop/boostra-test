<?php

namespace chats\mango\traits;

use chats\mango\traits\questions\TechProblems\answersTechProblems AS TechProblems;
use chats\mango\traits\questions\TechProblems\questionTechProblems AS TechQuestions;
use chats\mango\traits\questions\ComplaintToTheCollectionService\questionsComplaintToTheCollectionService;
use chats\mango\traits\questions\ComplaintToTheCollectionService\answersComplaintToTheCollectionService;

trait answersAll {

    use TechProblems,
        TechQuestions,
        answersComplaintToTheCollectionService,
        questionsComplaintToTheCollectionService;

    public function answersStep_1() {
        return [
            [
                'text' => 'Введите ответ клиента',
                'type' => 'text',
                'action' => 'theClient_sNameAsHeIntroducedHimself',
                'childs' => [
                    'question' => $this->questionStep_2(),
                    'answers' => $this->answersStep_2()
                ]
            ],
            [
                'text' => 'Клиент не представился',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action' => 'stepOne'
            ]
        ];
    }

    public function answersStep_2() {
        return [
            [
                'text' => 'Жалоба на службу взыскания',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => $this->questionStep_3(),
                    'answers' => $this->answersStep_3()
                ]
            ],
            [
                'text' => 'Технические проблемы',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => $this->answerTechStep_1(),
                    'answers' => $this->answerTechStep_1()
                ]
            ],
            [
                'text' => 'Возврат страховки',
                'action' => 'insuranceRefund',
                'actionParams' => '',
                'type' => 'button'
            ],
            [
                'text' => 'Дополнительные услуги',
                'action' => 'additionalServices',
                'actionParams' => '',
                'type' => 'button'
            ],
            [
                'text' => 'Иное',
                'action' => 'other',
                'actionParams' => '',
                'type' => 'button'
            ]
        ];
    }

}
