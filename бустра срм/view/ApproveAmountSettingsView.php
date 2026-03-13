<?PHP

require_once('View.php');

/**
 * s_approve_amount_settings
 * @see https://tracker.yandex.ru/BOOSTRARU-3303
 */
class ApproveAmountSettingsView extends View
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
                case 'check':
                    $response = $this->checkAction();
                    break;
                case 'save-autoretry-settings':
                    $response = $this->saveAutoretrySettings();
                    break;
            }
            $this->json_output($response);
        }

        $amounts = $this->approve_amount_settings->getAll();
        $this->design->assign('amounts', $amounts);

        $managers = [];
        foreach ($this->managers->get_managers() as $manager)
            $managers[$manager->id] = $manager;

        $changelogs = $this->approve_amount_settings->getChangelogs($this->request->get('logs_filter'));
        foreach ($changelogs as $changelog) {
            if (!empty($managers[$changelog->manager_id]))
                $changelog->manager = $managers[$changelog->manager_id];

            foreach ($changelog->new_values as $key => $value)
                if (!isset($changelog->old_values[$key]))
                    $changelog->old_values[$key] = '';
        }

        $this->design->assign('changelogs', $changelogs);
        $this->design->assign('changelog_types', $this->approve_amount_settings->getChangelogTypes());

        $this->design->assign('approve_amount_settings_enabled', $this->approve_amount_settings->isEnabled());

        $min_scorista_ball_for_autoretry = $this->settings->min_scorista_ball_for_autoretry;
        $this->design->assign('min_scorista_ball_for_autoretry', (int)$min_scorista_ball_for_autoretry);

        $increased_order_amount_for_autoretry = $this->settings->increased_order_amount_for_autoretry;
        $this->design->assign('increased_order_amount_for_autoretry', (int)$increased_order_amount_for_autoretry);

        return $this->design->fetch('approve_amount_settings.tpl');
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
        if (empty($min_ball) || $min_ball < 0)
            return ['error' => 'Укажите мин. балл.'];

        $amount = $this->request->post('amount', 'integer');
        if (empty($amount) || $amount < 0)
            return ['error' => 'Укажите рекомендуемую сумму.'];

        return [
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'have_close_credits' => (int)$have_close_credits,
            'min_ball' => $min_ball,
            'amount' => $amount
        ];
    }

    function addAction()
    {
        $fields = $this->getFields();
        if (!empty($fields['error']))
            return $fields;

        $existRow = $this->approve_amount_settings->getSuitable($fields['utm_source'], $fields['utm_medium'], $fields['have_close_credits'], true);
        if (!empty($existRow)) {
            $fields['error'] = 'Настройка с такими параметрами уже есть.';
            return $fields;
        }

        $id = $this->approve_amount_settings->add($fields);
        $fields['id'] = $id;
        $this->approve_amount_settings->addChangelog($this->manager->id, 'add', [], $fields);
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

        $oldRow = $this->approve_amount_settings->get($id);
        $this->approve_amount_settings->update($id, $fields);
        $fields['id'] = $id;
        $this->approve_amount_settings->addChangelog($this->manager->id, 'update', $oldRow, $fields);
        $fields['success'] = 'Ок';
        return $fields;
    }

    function deleteAction()
    {
        $id = trim($this->request->post('id', 'integer'));
        if (empty($id))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        $oldRow = $this->approve_amount_settings->get($id);
        $this->approve_amount_settings->delete($id);
        $this->approve_amount_settings->addChangelog($this->manager->id, 'delete', $oldRow);

        return [
            'id' => $id,
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

        return [
            'request' => $fakeOrder,
            'response' => $this->approve_amount_settings->getByOrder($fakeOrder),
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

    /**
     * Сохранение настроек для автоповторов
     *
     * @return void
     */
    private function saveAutoretrySettings(): void
    {
        $this->settings->min_scorista_ball_for_autoretry = $this->request->post('min_scorista_ball_for_autoretry', 'integer');
        $this->settings->increased_order_amount_for_autoretry = $this->request->post('increased_order_amount_for_autoretry', 'integer');

        header( 'Location: /approve_amount_settings' );
        exit;
    }
}
