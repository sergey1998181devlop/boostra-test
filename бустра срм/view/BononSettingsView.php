<?PHP

require_once('View.php');

/**
 * https://tracker.yandex.ru/BOOSTRARU-3399
 */
class BononSettingsView extends View
{
    /** @var array Продаваемые в Bonon источники */
    private $sources;

    function __construct()
    {
        parent::__construct();

        $this->sources = $this->settings->bonon_sources ?? [
            'last_id' => 0,
            'rows' => [],
        ];
    }

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

        $is_bonon_enabled = $this->settings->bonon_enabled;
        $this->design->assign('bonon_enabled', $is_bonon_enabled);

        $this->design->assign('sources', $this->sources['rows']);

        return $this->design->fetch('bonon_settings.tpl');
    }

    /**
     * @return array
     */
    function getFields()
    {
        $utm_source = trim($this->request->post('utm_source'));
        if (empty($utm_source))
            return ['error' => 'Укажите лидген.'];

        $utm_medium = trim($this->request->post('utm_medium'));
        if (empty($utm_medium))
            return ['error' => 'Укажите вебмастера.'];

        $chance = $this->request->post('chance');
        if ($chance == '')
            return ['error' => 'Укажите шанс срабатывания.'];

        return [
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'chance' => (int)$chance
        ];
    }

    function addAction()
    {
        $fields = $this->getFields();
        if (!empty($fields['error']))
            return $fields;

        $this->sources['last_id'] += 1;
        $this->sources['rows'][$this->sources['last_id']] = (object)$fields;
        $this->settings->bonon_sources = $this->sources;

        return true;
    }

    function updateAction()
    {
        $fields = $this->getFields();
        if (!empty($fields['error']))
            return $fields;

        $id = $this->request->post('id', 'integer');
        if (empty($id) || empty($this->sources['rows'][$id]))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        $this->sources['rows'][$id] = (object)$fields;
        $this->settings->bonon_sources = $this->sources;

        $fields['success'] = 'Ок';
        return $fields;
    }

    function deleteAction()
    {
        $id = $this->request->post('id', 'integer');
        if (empty($id) || empty($this->sources['rows'][$id]))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        unset($this->sources['rows'][$id]);
        $this->settings->bonon_sources = $this->sources;

        return [
            'id' => $id,
            'success' => 'Ок'
        ];
    }
}
