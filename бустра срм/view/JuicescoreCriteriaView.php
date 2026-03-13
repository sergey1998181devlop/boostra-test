<?PHP

require_once('View.php');

/**
 * Class JuicescoreCriteriaView
 * s_juicescore_criteria
 * https://tracker.yandex.ru/BOOSTRARU-2530
 */
class JuicescoreCriteriaView extends View
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
                case 'delete':
                    $response = $this->deleteAction();
                    break;
            }
            $this->json_output($response);
        }

        $criterias = $this->juicescoreCriteria->getAll();
        $this->design->assign('criterias', $criterias);

        return $this->design->fetch('juicescore_criteria.tpl');
    }

    /**
     * @return array
     */
    function getFields()
    {
        $name = trim($this->request->post('name'));
        if (empty($name))
            return ['error' => 'Укажите имя критерия.'];

        $required_ball = $this->request->post('required_ball');
        if ($required_ball == '')
            return ['error' => 'Укажите мин. балл.'];

        return [
            'name' => $name,
            'required_ball' => (float)$required_ball
        ];
    }

    function addAction()
    {
        $fields = $this->getFields();
        if (!empty($fields['error']))
            return $fields;

        $this->juicescoreCriteria->add($fields);
        return true;
    }

    function updateAction()
    {
        $fields = $this->getFields();
        if (!empty($fields['error']))
            return $fields;

        $this->juicescoreCriteria->update($fields['name'], $fields);
        $fields['success'] = 'Ок';
        return $fields;
    }

    function deleteAction()
    {
        $name = trim($this->request->post('name'));
        if (empty($name))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        $this->juicescoreCriteria->delete($name);
        return [
            'name' => $name,
            'success' => 'Ок'
        ];
    }
}
