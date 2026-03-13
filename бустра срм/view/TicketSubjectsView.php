<?php
require_once 'View.php';

class TicketSubjectsView extends View
{

    public function fetch()
    {
        if ($this->request->method('post')) {
            $action = $this->request->post('action', 'string');

            if ($action == 'add') {
                $this->createSubject();
            } elseif ($action == 'delete') {
                $this->removeSubject();
            } elseif ($action == 'update') {
                $this->updateSubject();
            }

        } else {
            $action = $this->request->get('action', 'string');

            if ($action == 'list') {
                return $this->listSubjectsView();
            }
        }
    }

    /***
     * Get list subjects
     *
     * @return string
     */
    private function listSubjectsView(): string
    {

        $this->design->assign('subjects', $this->tickets->getSubjects());

        return $this->design->fetch('contact_center/tickets_subjects.tpl');

    }

    /**
     * Create new subject
     *
     * @return void
     */
    private function createSubject(): void
    {
        // string to array
        mb_parse_str($this->request->post('data'), $dataForm);

        $query = $this->db->placehold("
            INSERT INTO __mytickets_subjects SET ?%
        ", $dataForm);
        $this->db->query($query);

        $idSubject = $this->db->insert_id();

        $outputDate = $idSubject > 0
            ? ['success' => true,
                'id' => $idSubject,
                'data' => $dataForm,
                'message' => 'Новый справочник добавлен!']

            : ['success' => false,
                'data' => $dataForm,
                'message' => 'Ошибка при добавлении!'];


        $this->json_output($outputDate);

    }

    /**
     * Remove element
     *
     * @return void
     */
    private function removeSubject()
    {
        $id = $this->request->post('id', 'integer');

        $query = $this->db->placehold("
            DELETE FROM __mytickets_subjects WHERE id = ?
        ", (int)$id);
        $this->db->query($query);

        $this->json_output(array(
            'id' => $id,
            'success' => 'Организация успешно удалена!'
        ));
    }

    /**
     * Update subject
     *
     * @return void
     */
    private function updateSubject()
    {
        $id = $this->request->post('id', 'integer');
        $data = $this->request->post('data');

        $query = $this->db->placehold("
            UPDATE __mytickets_subjects SET ?% WHERE id = ?
        ", $data, (int)$id);
        $this->db->query($query);

        $this->json_output(array(
            'id' => $id,
            'success' => 'Справочник обновлён!'
        ));
    }
}