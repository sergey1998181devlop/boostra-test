<?php

class AutomationFailsView extends View
{
    public function fetch()
    {
        if (!in_array('settings', $this->manager->permissions))
        	return $this->design->fetch('403.tpl');

        if ($this->request->method('post'))
        {
            $query = $this->db->placehold("SELECT * FROM automation_fails");
            $this->db->query($query);
            $itemsFromDB = $this->db->results();
            $this->design->assign('items', $itemsFromDB);

            $itemsFromDBWithKeys = [];

            array_map(function ($item) use (&$itemsFromDBWithKeys) {
                $itemsFromDBWithKeys[$item->id] = $item;
            }, $itemsFromDB);

            $itemsFromRequest = $this->request->post('items');

            foreach ($itemsFromRequest as $item) {
                if (
                    $item['is_active'] != $itemsFromDBWithKeys[$item['id']]->is_active ||
                    $item['show_at'] != $itemsFromDBWithKeys[$item['id']]->show_at ||
                    $item['text'] != $itemsFromDBWithKeys[$item['id']]->text
                ) {
                    $query = $this->db->placehold("UPDATE automation_fails SET ?% WHERE id = ?", [
                        'is_active' => $item['is_active'],
                        'show_at' => $item['show_at'],
                        'text' => $item['text'],
                    ], $item['id']);

                    $this->db->query($query);
                }
            }
        }


        $query = $this->db->placehold("SELECT * FROM automation_fails");
        $this->db->query($query);
        $itemsFromDB = $this->db->results();
        $this->design->assign('items', $itemsFromDB);


        return $this->design->fetch('automation_fails.tpl');
    }
}
