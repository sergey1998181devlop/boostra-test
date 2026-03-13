<?php

namespace chats\mango\traits;

use chats\mango\traits\questionsAll;
use chats\mango\traits\answersAll;
use Simpla;

trait questions {

    use questionsAll,
        answersAll;

    public $step = 1;
    public $parent = 0;

    public function questionsAndAnswers() {
        return [
            'question' => $this->questionStep_1(),
            'answers' => $this->answersStep_1()
        ];
    }

    private function addQuestionInDB($data) {
        $obj = new Simpla();
        $query = $obj->db->placehold("
            INSERT INTO
                __questionTickets
            SET ?%", $data);
        $obj->db->query($query);
        return $obj->db->insert_id();
    }

    private function addAnswerInDB($data) {
        $obj = new Simpla();
        $query = $obj->db->placehold("
            INSERT INTO
                __mangoQuestionsForTheQuestionnaire
            SET ?%", $data);
        $obj->db->query($query);
        return $obj->db->insert_id();
    }

    private function setAnswers($answers, $parent) {
        foreach ($answers AS $answer) {
            $action = false;
            if (isset($answer['action'])) {
                $action = $answer['action'];
            }
            $dataAnswer = [
                'text' => $answer['text'],
                'type' => $answer['type'],
                'action' => $action,
                'questionId' => $parent
            ];
            if (isset($answer['buttonColor'])) {
                $dataAnswer['buttonColor'] = $answer['buttonColor'];
            }
            $this->parent = $this->addAnswerInDB($dataAnswer);
            if (isset($answer['childs'])) {
                $this->setQuestions($answer['childs']);
            }
        }
    }

    public function setQuestions($questions) {
        if (isset($questions['question'])) {
            $dataQuestion = [
                'parent' => $this->parent,
                'text' => $questions['question']
            ];
            $this->parent = $this->addQuestionInDB($dataQuestion);
        }
        if (isset($questions['answers'])) {
            $this->setAnswers($questions['answers'], $this->parent);
        }
    }

}
