<?php

require_once 'View.php';

class OrganizationsView extends View
{
    public function fetch()
    {
        if ($this->request->method('post')) {
            switch ($this->request->post('action', 'string')):

                case 'add':

                    $data = $this->organizations->addOrganizations($this->request->post('data'));

                    $this->json_output(array(
                        'id' => $data["id"],
                        'data' => $data["data"],
                        'success' => 'Новая организация добавлена!'
                    ));

                    break;

                case 'update':

                    $id = $this->request->post('id', 'integer');
                    $data = $this->request->post('data');

                    $rest = $this->organizations->update($id, $data);

                    $this->json_output(array(
                        'id' => $id,
                        'data' => $rest,
                        'success' => 'Организация обновлена'
                    ));
                    break;

                case 'delete':

                    $id = $this->request->post('id', 'integer');
                    $this->organizations->delete($id);

                    $this->json_output(array(
                        'id' => $id,
                        'success' => 'Организация успешно удалена!'
                    ));

                    break;

            endswitch;
        }

        $items = $this->organizations->getList();
        $this->design->assign('items', $items);


        return $this->design->fetch('organizations.tpl');
    }

}
