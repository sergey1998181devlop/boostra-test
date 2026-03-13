<?PHP

require_once('View.php');

/**
 * Class ScoristaLeadgidSettingsView
 * s_leadgid_scorista
 * https://tracker.yandex.ru/BOOSTRARU-2459
 */
class ScoristaLeadgidSettingsView extends View
{
    /** @var string[] Логины менеджеров которые могут смотреть эту страницу с настройками */
    const ALLOWED_MANAGER_LOGINS = [
        'admin',
        'opr',
        'Voronoy.IYU',
    ];

    /**
     * Параметры на странице проверки лидгена
     *
     * ```
     * 'Название' => ['тип', 'значение по-умолчанию'];
     * ```
     */
    const PARAMS_IN_CHECK = [
        'utm_source' => ['string', 'Любой'],
        'utm_medium' => ['string', 'Любой'],
        'have_close_credits' => ['integer', 0],
        'scorista_ball' => ['integer', 1000],
    ];

    function fetch()
    {
        if (!$this->canViewSettings())
            return false;

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

                case 'add_factor':
                    $response = $this->addFactorAction();
                    break;
                case 'update_factor':
                    $response = $this->updateFactorAction();
                    break;
                case 'delete_factor':
                    $response = $this->deleteFactorAction();
                    break;

                case 'check':
                    $response = $this->checkAction();
                    break;
            }
            $this->json_output($response);
        }

        $leadgids = $this->leadgidScorista->getAll();
        $this->design->assign('leadgids', $leadgids);

        $factors = $this->leadgidScorista->getStopFactors();
        $this->design->assign('factors', $factors);

        $managers = [];
        foreach ($this->managers->get_managers() as $manager)
            $managers[$manager->id] = $manager;

        $changelogs = $this->leadgidScorista->getChangelogs($this->request->get('logs_filter'));
        foreach ($changelogs as $changelog) {
            if (!empty($managers[$changelog->manager_id]))
                $changelog->manager = $managers[$changelog->manager_id];

            foreach ($changelog->new_values as $key => $value)
                if (!isset($changelog->old_values[$key]))
                    $changelog->old_values[$key] = '';
        }

        $this->design->assign('changelogs', $changelogs);
        $this->design->assign('changelog_types', $this->leadgidScorista->getChangelogTypes());

        $this->design->assign('leadgid_scorista_enabled', $this->leadgidScorista->isEnabled());

        return $this->design->fetch('scorista_leadgid_settings.tpl');
    }

    /**
     * @return array
     */
    function getFields()
    {
        $utm_source = trim($this->request->post('utm_source'));
        if (empty($utm_source))
            return ['error' => 'Укажите utm_source.'];

        $utm_medium = trim($this->request->post('utm_medium'));
        if (empty($utm_medium))
            return ['error' => 'Укажите utm_medium.'];

        $have_close_credits = $this->request->post('have_close_credits');
        if ($have_close_credits == '' || ((int)$have_close_credits < 0 || (int)$have_close_credits > 1))
            return ['error' => 'Укажите тип. 0 - НК, 1 - ПК.'];

        $min_ball = $this->request->post('min_ball', 'integer');
        if (empty($min_ball))
            return ['error' => 'Укажите мин. балл.'];

        $amount = $this->request->post('amount', 'integer');
        if (!isset($amount) || $amount < 0) // $amount = 0 - Корректно
            return ['error' => 'Укажите рекомендуемую сумму.'];

        $type = $this->request->post('type');
        if ($type == '' || ((int)$type < 0 || (int)$type > 1))
            return ['error' => 'Некорректный тип'];

        return [
            'type' => (int)$type,
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'have_close_credits' => (int)$have_close_credits,
            'min_ball' => $min_ball,
            'amount' => $amount,
        ];
    }

    function addAction()
    {
        $fields = $this->getFields();
        if (!empty($fields['error']))
            return $fields;

        $existLeadgid = $this->leadgidScorista->getLeadgid($fields['utm_source'], $fields['utm_medium'], $fields['have_close_credits'], true, $fields['type']);
        if (!empty($existLeadgid)) {
            $fields['error'] = 'Настройка с такими параметрами уже есть.';
            return $fields;
        }

        $id = $this->leadgidScorista->add($fields);
        $fields['id'] = $id;
        $this->leadgidScorista->addChangelog($this->manager->id, 'add', [], $fields);
        return true;
    }

    function updateAction()
    {
        $id = $this->request->post('id', 'integer');
        if (empty($id))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        $fields = $this->getFields();
        if (!empty($fields['error']))
            return $fields;

        $oldLeadgid = $this->leadgidScorista->get($id);
        $this->leadgidScorista->update($id, $fields);
        $fields['id'] = $id;
        $this->leadgidScorista->addChangelog($this->manager->id, 'update', $oldLeadgid, $fields);
        $fields['success'] = 'Ок';
        return $fields;
    }

    function deleteAction()
    {
        $id = trim($this->request->post('id', 'integer'));
        if (empty($id))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        $oldLeadgid = $this->leadgidScorista->get($id);
        $this->leadgidScorista->delete($id);
        $this->leadgidScorista->addChangelog($this->manager->id, 'delete', $oldLeadgid);

        return [
            'id' => $id,
            'success' => 'Ок'
        ];
    }

    function addFactorAction()
    {
        $factor = trim($this->request->post('factor'));
        if (empty($factor))
            return ['error' => 'Укажите название стоп-фактора.'];

        if (!empty($this->leadgidScorista->getStopFactor($factor)))
            return ['error' => 'Стоп-фактор с таким названием уже существует.'];

        $comment = trim($this->request->post('comment'));

        $this->leadgidScorista->addStopFactor($factor, $comment);
        return true;
    }

    function updateFactorAction()
    {
        $factor = trim($this->request->post('factor'));
        if (empty($factor))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        $update = [
            'factor' => trim($this->request->post('new_factor')) ?: $factor,
            'comment' => trim($this->request->post('comment')),
        ];
        $this->leadgidScorista->updateStopFactor($factor, $update);

        $update['success'] = 'Ok';
        return $update;
    }

    function deleteFactorAction()
    {
        $factor = trim($this->request->post('factor'));
        if (empty($factor))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        $this->leadgidScorista->deleteStopFactor($factor);

        return [
            'factor' => $factor,
            'success' => 'Ок'
        ];
    }

    function checkAction()
    {
        $fakeOrder = new stdClass();
        foreach (self::PARAMS_IN_CHECK as $paramName => $data) {
            // Получаем параметр из запроса и пытаемся преобразовать к нужному типу
            $param = trim($this->request->post($paramName, $data[0]));

            // Если параметра нет или не смогли преобразовать - используем значение по-умолчанию
            if (empty($param))
                $param = $data[1];

            $fakeOrder->$paramName = $param;
        }

        $type = trim($this->request->post('type', 'integer'));
        return [
            'request' => $fakeOrder,
            'response' => $this->leadgidScorista->getByOrder($fakeOrder, $type),
        ];
    }

    /**
     * Проверка на доступ к этой странице.
     *
     * Доступ есть у менеджеров с ролью ``developer`` и у логинов из ``ALLOWED_MANAGER_LOGINS``
     * @return bool
     */
    function canViewSettings()
    {
        if ($this->manager->role == 'developer' || $this->manager->role == 'ts_operator' || in_array($this->manager->login, self::ALLOWED_MANAGER_LOGINS))
            return true;
        return false;
    }
}
