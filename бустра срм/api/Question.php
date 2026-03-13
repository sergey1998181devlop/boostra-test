<?php

require_once('Simpla.php');

class Question extends Simpla {

    public function addQuestion($data) {
        $query = $this->db->placehold("
            INSERT INTO __mangoQuestion SET ?%
        ", (array) $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    public function getQuestionById($id) {
        $query = $this->db->placehold("
            SELECT *
            FROM __mangoQuestion
            WHERE TicketId = ?
        ", (int) $id);
        $this->db->query($query);
        $result = $this->db->results();
        return $result;
    }

    public function getQuestionByParent($parent = 0) {
        $query = $this->db->placehold("
                SELECT 
                    * 
                FROM 
                    __mangoAnswersToTheQuestionsForTheQuestionnaire
                WHERE
                    parent = ?
        ", $parent);
        $this->db->query($query);
        $result = $this->db->result();
        return $result;
    }
    
    public function getAnswersByParent($parent = 0) {
        $query = $this->db->placehold("
                SELECT 
                    * 
                FROM 
                    __mangoQuestionsForTheQuestionnaire
                WHERE
                    questionId = ?
        ", $parent);
        $this->db->query($query);
        $result = $this->db->results();
        return $result;
    }
    
    public function getQuestionAndAnswers($parent) {
        $Question = $this->getQuestionByParent($parent);
        return ['Answers'=> $this->getAnswersByParent($Question->id), 'Question'=> $Question];
    }

}
