<?php

namespace chats\mango\traits\questions\TechProblems;

trait answersTechProblems {

    public function answerTechStep_2() {
         return [
            [
                'text' => '(Уточните суть проблемы)',
                'type' => 'text',
                'action' => 'task.logging(task.ticketId, \''.$this->questionStep_3().'\', task.getTextFild());',
                'childs' => [
                    'question' => $this->questionStep_EndTechProblems(),
                    'answers' => $this->answersStep_14()
                ]
            ]
    ];
    }

    public function answerTechStep_1() {
        return [
            [
                'text' => 'Не получается войти в ЛК(не приходит звонок, нет кнопок Wats`App и Viber)',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action' => 'task.createTask(0, \'technicalProblems\');task.logging(task.ticketId, \''.$this->questionStep_3().'\', this.textContent);',
                'childs' => [
                    'question' => '',
                    'answers' => $this->answersStep_2()
                ]
            ],
            [
                'text' => 'Не может получить деньги (крутиться &#8734;)',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action' => 'task.createTask(0, \'technicalProblems\');',
                'childs' => [
                    'question' => '(Уточните суть проблемы)',
                    'answers' => $this->answersStep_2()
                ]
            ],
            [
                'text' => 'Не приходит SMS',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action' => 'task.createTask(0, \'technicalProblems\');',
                'childs' => [
                    'question' => '(Уточните суть проблемы)',
                    'answers' => $this->answersStep_2()
                ]
            ],
            [
                'text' => 'Не пришли деньги на карту',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action' => 'task.createTask(0, \'technicalProblems\');',
                'childs' => [
                    'question' => '(Уточните суть проблемы)',
                    'answers' => $this->answersStep_2()
                ]
            ],
            [
                'text' => 'Проблемы с документами в ЛК',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action' => 'task.createTask(0, \'technicalProblems\');',
                'childs' => [
                    'question' => '(Уточните суть проблемы)',
                    'answers' => $this->answersStep_2()
                ]
            ],
            [
                'text' => 'Не отображается оплата в ЛК',
                'type' => 'button',
                'buttonColor' => '#F62D51',
                'action' => 'task.createTask(0, \'technicalProblems\');',
                'childs' => [
                    'question' => '(Уточните суть проблемы)',
                    'answers' => $this->answersStep_2()
                ]
            ]
        ];
    }

}
