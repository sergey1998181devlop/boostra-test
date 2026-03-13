<?PHP

require_once('View.php');

class SprVersionsView extends View
{
    function fetch()
    {
        if ($action = $this->request->post('action')) {
            $response = 'Unknown action';
            switch ($action) {
                case 'add':
                    $response = $this->addAction();
                    break;

                case 'update':
                    $response = $this->updateAction();
                    break;
            }
            $this->json_output($response);
        }

        $versions = $this->spr_versions->getAll();
        $this->design->assign('versions', $versions);

        $managers = $this->managers->get_managers();
        $manager_names = [];
        foreach ($managers as $manager) {
            $manager_names[$manager->id] = $manager->name_1c;
        }
        $this->design->assign('manager_names', $manager_names);

        return $this->design->fetch('spr_versions.tpl');
    }

    function addAction()
    {
        $description = $this->request->post('description', 'string');
        $description = trim($description);
        if (empty($description)) {
            return ['error' => 'Описание версии не должно быть пустое'];
        }

        $this->spr_versions->add([
            'description' => $description,
            'manager_id' => $this->manager->id,
        ]);

        return true;
    }

    function updateAction()
    {
        $description = $this->request->post('description', 'string');
        $description = trim($description);
        if (empty($description)) {
            return ['error' => 'Описание версии не должно быть пустое'];
        }

        $id = $this->request->post('id', 'int');
        if (empty($id) || empty($this->spr_versions->get($id))) {
            return ['error' => 'Произошла ошибка, обновите страницу'];
        }

        $this->spr_versions->update($id, [
            'description' => $description,
            'manager_id' => $this->manager->id,
        ]);

        return true;
    }
}
