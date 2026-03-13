<?php

require_once 'Simpla.php';

use api\traits\setPages;
use api\traits\Sorts;

class Docs extends Simpla
{
    const UPLOAD_ACTION = 1;
    const UPDATE_ACTION = 2;
    const DELETE_ACTION = 3;

    use setPages, Sorts;

    public function get_docs()
    {
        $query = $this->db->placehold("SELECT id, name, filename, description, created, position, in_info, visible FROM __docs ORDER BY position ASC");
        $this->db->query($query);
        return $this->db->results();
    }

    public function add_doc($doc)
    {
        // Получаем максимальное значение position
        $query = $this->db->placehold("SELECT MAX(position) as max_position FROM __docs");
        $this->db->query($query);
        $result = $this->db->result();
        $new_position = $result->max_position + 1;

        $query = $this->db->placehold("INSERT INTO __docs (name, filename, description, created, position, in_info, visible) VALUES(?, ?, ?, ?, ?, 0, 1)",
            $doc['name'],
            $doc['filename'],
            $doc['description'],
            $doc['created'],
            $new_position);
        $this->db->query($query);
        $id = $this->db->insert_id();
        $this->log($id, self::UPLOAD_ACTION);
        return $id;
    }

    public function update_doc($id, $doc)
    {
        $query = $this->db->placehold("UPDATE __docs SET name = ?, description = ?, created = ? WHERE id = ?",
            $doc['name'],
            $doc['description'],
            $doc['created'],
            (int)$id);
        $this->db->query($query);
        $this->log($id, self::UPDATE_ACTION);
        return $id;
    }

    public function update_positions($positions)
    {
        foreach ($positions as $position => $id) {
            $position++;
            $query = $this->db->placehold("UPDATE __docs SET position = ? WHERE id = ?",
                (int)$position,
                (int)$id);
            $this->db->query($query);
            $this->log($id, self::UPDATE_ACTION);
        }
    }

    public function delete_doc($id)
    {
        $query = $this->db->placehold("DELETE FROM __docs WHERE id = ?", (int)$id);
        $this->db->query($query);
        $this->log($id, self::DELETE_ACTION);
    }

    public function update_visibility($id, $newState)
    {
        $query = $this->db->placehold("UPDATE __docs SET in_info = ? WHERE id = ?", (int)$newState, (int)$id);
        $this->db->query($query);
        $this->log($id, self::UPDATE_ACTION);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Логирует действия с документами.
     * 
     * @param int $doc_id    ID документа
     * @param int $action_id ID действия ( UPLOAD_ACTION, UPDATE_ACTION и т.д )
     */
    public function log(int $doc_id, int $action_id)
    {
        $query = $this->db->placehold("INSERT INTO __docs_logs SET ?%", [
            "doc_id" => $doc_id,
            "manager_id" => $this->getManagerId(),
            "action_id" => $action_id,
            "created" => (new DateTime)->format('Y-m-d H:i:s'),
        ]);
        $this->db->query($query);
    }

    /**
     * Возвращает логи действий с документами. Использует пагинацию.
     * 
     * @return array Массив объектов с логами.
     */
    public function get_logs()
    {
        $query = $this->db->placehold("
            SELECT DATE_FORMAT(dl.created, '%d.%m.%Y %H:%i:%s') as created,
                m.name as manager_name,
                dla.name as action_name,
                dl.doc_id as doc_id
            FROM __docs_logs dl
            LEFT JOIN s_docs d ON dl.doc_id = d.id
            LEFT JOIN s_managers m ON dl.manager_id = m.id
            LEFT JOIN __docs_logs_actions dla ON dl.action_id = dla.id
            ORDER BY dl.created DESC
        ");

        $query .= $this->getLimit($query);

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Проверяет, может ли менеджер просматривать список документов.
     * 
     * @return bool
     */
    public function can_view_docs($manager): bool {
        return in_array($manager->role, ['admin', 'developer', 'yurist', 'opr', 'ts_operator']);
    }
}
