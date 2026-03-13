<?php

namespace chats\mango\traits;

use Simpla;

trait mangoQuestionnaire {

    use StandartMethods;

    /**
     * Получение вопросов и ответов для анкеты
     */
    public function questionTickets($data) {
        $answers = false;
        if (!$data['step']) {
            $data['step'] = 1;
        } else {
            $data['step']++;
        }
        if(!isset($data['type'])){
            $data['type'] = false;
        }
        $question = $this->getQuestion($data['parent'], $data['step'], $data['type']);
        if ($question) {
            $answers = $this->getAnswers($question->id);
        } else {
            $question = false;
        }
        echo json_encode((object) ["Data" => ['question' => $question, 'answers' => $answers, 'step' => $data['step']]]);
        exit();
    }

    /**
     * получение вопросов для анкетирования
     */
    private function getQuestion($parent, $step, $type) {
        $obj = new Simpla();
        $addSql = false;
        settype($parent, 'integer');
        if ($step <= 2) {
            $addSql = "type = '" . $type . "'";
        } else {
            $addSql = "parent = " . $parent;
        }
        $query = $obj->db->placehold("
            SELECT
                *
            FROM
                __mangoAnswersToTheQuestionsForTheQuestionnaire
            WHERE
        ");
        $query .= $addSql;
        $obj->db->query($query);
        return $obj->db->result();
    }

    /**
     * получение ответов для вопросов к анкете
     */
    private function getAnswers($questionId) {
        $obj = new Simpla();
        settype($questionId, 'integer');
        $query = $obj->db->placehold("
            SELECT
                *
            FROM
                __mangoQuestionsForTheQuestionnaire
            WHERE
                questionId = " . $questionId . "
        ");
        $obj->db->query($query);
        return $obj->db->results();
    }

    /**
     * добавление коментария
     */
    public function addComment($data) {
        $simplaObj = new Simpla();
        $comment = [
            'id_ticket' => $data['tiketId'],
            'comment' => 'Входящий звонок от : '
            . $data['userName'] . ' ' . date('Y-m-d H:i:s'),
            'date_create' => date('Y-m-d H:i:s'),
            'manager' => $data['managerId']
        ];
        if (!empty($data['text'])) {
            $comment['comment'] .= ' текст комментария : ' . $data['text'];
        }
        $id = $simplaObj->tickets->add_comment($comment);
        $this->returnJson($id);
    }

}
