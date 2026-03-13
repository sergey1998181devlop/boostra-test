<?PHP

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

require_once('View.php');

/**
 * Class VkSendingSettingsView
 * /vk_sending_settings
 * https://tracker.yandex.ru/BOOSTRARU-3218
 */
class VkSendingSettingsView extends View
{
    // Поля получаемые из POST запроса при сохранении записи
    const SAVE_FIELDS = [
        'send_hour',
        'day_from',
        'day_to',
        'age_from',
        'age_to',
        'gender',
        'scorista_ball_from',
        'scorista_ball_to',
        'scorista_decision',
        'utm_source',
        'organization_id',
        'message'
    ];

    function fetch()
    {
        if ($action = $this->request->post('action')) {
            $response = ['error' => 'Unknown action'];
            switch ($action) {
                case 'save':
                    $response = $this->saveAction();
                    break;
                case 'delete':
                    $response = $this->deleteAction();
                    break;
                case 'load':
                    $response = $this->loadAction();
                    break;
                case 'toggle':
                    $response = $this->toggleAction();
                    break;
            }
            $this->json_output($response);
        }

        $organizations = [];
        foreach ($this->organizations->getList() as $organization) {
            $organizations[$organization->id] = $organization->short_name;
        }

        $messages = $this->vk_message_settings->getAll();
        foreach ($messages as $message) {
            if (!empty($message->organization_id))
                $message->organization_name = $organizations[$message->organization_id] ?? 'id';
            else
                $message->organization_name = 'Любая';
        }

        $this->design->assign('messages', $messages);
        $this->design->assign('vk_bot_enabled', $this->vk_message_settings->isEnabled());

        $statistic = $this->vk_message_settings->getVkStatistic();
        if (!empty($statistic) && !empty($statistic['success'])) {
            $messages = [];
            foreach ($statistic['messages'] as $message) {
                $messages[$message['created_date']][] = $message;
            }
            foreach ($messages as &$values) {
                $values[0]['rowspan'] = count($values);
            }
            $statistic['messages'] = $messages;
        }
        else {
            $statistic = [];
        }
        $this->design->assign('statistic', $statistic);

        return $this->design->fetch('vk_sending_settings.tpl');
    }

    function saveAction()
    {
        $row = [];
        foreach (self::SAVE_FIELDS as $field) {
            $val = $this->request->post($field);
            if (isset($val))
                $row[$field] = $val;
        }

        $id = $this->request->post('id');
        if (empty($id)) {
            // Добавление
            try {
                $this->vk_message_settings->add($row);
            }
            catch (Exception $e) {
                return ['error' => 'Ошибка при добавлении записи, проверьте корректность полей.'];
            }
        }
        else {
            // Редактирование
            try {
                $this->vk_message_settings->update($id, $row);
            }
            catch (Exception $e) {
                return ['error' => 'Ошибка при обновлении записи, проверьте корректность полей.'];
            }
        }

        return true;
    }

    function deleteAction()
    {
        $id = trim($this->request->post('id', 'integer'));
        if (empty($id))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        $this->vk_message_settings->delete($id);

        return [
            'id' => $id,
            'success' => 'Ок'
        ];
    }

    function loadAction()
    {
        $id = trim($this->request->post('id', 'integer'));
        if (empty($id))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        return (array)$this->vk_message_settings->get($id);
    }

    function toggleAction()
    {
        $id = trim($this->request->post('id', 'integer'));
        if (empty($id))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        $enabled = $this->request->post('enabled', 'boolean');
        $this->vk_message_settings->update($id, [
            'enabled' => (int)(!$enabled)
        ]);

        return [
            'id' => $id,
            'success' => 'Ок'
        ];
    }
}
