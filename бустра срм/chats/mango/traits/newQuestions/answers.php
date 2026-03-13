<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPTrait.php to edit this template
 */

namespace chats\mango\traits\newQuestions;

use chats\mango\traits\newQuestions\questions;

/**
 *
 * @author alexey
 */
trait answers {

    use questions;

    public function start() {
        return [
            [
                'qiestion' => $this->step_1,
                'answers' => $this->step_1()
            ]
        ];
    }
    
    public function step_1() {
        return [
            [
                'text' => 'Введите ответ клиента',
                'type' => 'text',
                'action' => 'theClient_sNameAsHeIntroducedHimself',
                'childs' => [
                    'question' => $this->step_2,
                    'answers' => $this->answersStep_2()
                ]
            ]
        ];
    }
    
     public function answersStep_2() {
        return [
            [
                'text' => 'Жалоба на службу взыскания',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action'=> 'task.createTicket(4, userId, resCredit.info.id, \'Звонок\');',
                'childs' => [
                    'question' => $this->collectorComplaint_1,
                    'answers' => $this->answersStep_3()
                ]
            ],
            [
                'text' => 'Технические проблемы',
                'type' => 'button',
                'action'=> 'task.createTicket(9, userId, resCredit.info.id, \'Звонок\');',
                'buttonColor' => '#F62D51',
                'childs' => [
                    'question' => $this->techProblem_1,
                    'answers' => $this->answerTechStep_1()
                ]
            ],
            [
                'text' => 'Возврат страховки',
                'action'=> 'task.createTicket(3, userId, resCredit.info.id, \'Звонок\');',
                'type' => 'button',
                'childs' => [
                    'question' => $this->returnStrah,
                    'answers' => $this->answerStrahStep_1()
                ]
            ],
            [
                'text' => 'Дополнительные услуги',
                'type' => 'button'
            ],
            [
                'text' => 'Иное',
                'type' => 'button'
            ]
        ];
    }

}
