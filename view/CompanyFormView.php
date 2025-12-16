<?PHP

require_once('View.php');
require_once dirname(__DIR__) . '/api/TelegramApi.php';

class CompanyFormView extends View
{
    private $errors = [];

    public function __construct()
    {
        parent::__construct();
        if (empty($this->user)) {
            header('Location: ' . $this->config->root_url . '/user/login');
            exit();
        }
    }

    const TOKEN = '8020365070:AAGmAOray1ExuOdaKITMPHgshLlOgoY02MQ';
    const CHANNEL_ID = '-1002279935019';

	public function fetch()
	{
        $this->design->assign('body_class', 'bg-white max-h');
        $this->design->assign('fields', $this->getFormFields());

        // @todo сделать проверку на существующую заявку при одобрении на основной заём
        $has_new_orders = $this->company_orders->getItems(['user_id' => $this->user->id]);
        if (!empty($has_new_orders)) {
            header('Location: ' . $this->config->root_url . '/user');
            exit();
        }

        // определим нужно ли показывать ссылку на займы для ИП и ООО
        $show_company_form = $this->company_orders->checkShowHref();
        $this->design->assign('show_company_form', $show_company_form);

        if (!$show_company_form) {
            $this->design->assign('warning', (object)[
                'title' => 'Приём заявок временно приостановлен!',
                'description' => 'Попробуйте подать заявку завтра, приносим свои извинения.',
            ]);
        }

        if ($this->request->method('post') && $show_company_form) {
            $this->validate();
            if (empty($this->errors)) {
                $this->saveOrder();
                $this->design->assign('success', 1);
            } else {
                $this->design->assign('errors', $this->errors);
            }
        }

        $this->design->assign('taxes', ['ОСНО', 'УСН', 'ЕСХН', 'ПСН']);
        $this->design->assign('credit_targets', $this->company_orders->getCreditTargets());

        return $this->design->fetch('company_form.tpl');
	}

    /**
     * Возвращает поля для заполнения формы
     * @return array[]
     */
    private function getFormFields(): array
    {
        $form_fields = [
            'order' => [
                'name' => 'Данные о заявки',
                'values' => [
                    'amount' => 'Сумма',
                    'tax' => 'Форма налогообложения',
                    'okved' => 'ОКВЭД',
                    'co_credit_target_id' => 'Цель кредитования',
                ],
            ],
            'personal' => [
                'name' => 'Персональные данные',
                'values' => [],
            ],
            'payment' => [
                'name' => 'Платежные реквизиты',
                'values' => [
                    'bank_name' => 'Наименование банка',
                    'bank_place' => 'Расположение банка (город)',
                    'bank_cor_wallet' => 'Кор/сч банка',
                    'bank_bik' => 'Бик',
                    'bank_user_wallet' => 'Расчетный счёт',
                ],
            ],
        ];

        if (empty($this->user->inn)) {
            $form_fields['personal']['values']['inn'] = 'ИНН';
        }

        $company_form_email = $this->user_data->get($this->user->id, 'company_form_email')->value ?? null;

        if (empty($company_form_email)) {
            $form_fields['personal']['values']['company_form_email'] = 'E-mail';
        }

        if (empty($this->user->Snils)) {
            $form_fields['personal']['values']['Snils'] = 'СНИЛС';
        }

        $ogrnip = $this->user_data->get($this->user->id, 'ogrnip')->value ?? null;
        if (empty($ogrnip)) {
            $form_fields['personal']['values']['ogrnip'] = 'ОГРНИП';
        }

        if (empty($form_fields['personal']['values'])) {
            unset($form_fields['personal']);
        }

        return $form_fields;
    }

    /**
     * Сохраняем результат
     * @return void
     */
    private function saveOrder()
    {
        $this->updateUser();
        $company_order_id = $this->addOrder();

        if (!empty($this->user)) {
            $this->scorings->add_scoring(
                [
                    'user_id' => $this->user->id,
                    'status' => $this->scorings::STATUS_NEW,
                    'type' => $this->scorings::TYPE_EGRUL,
                    'created' => date('Y-m-d H:i:s'),
                    'scorista_id' => $company_order_id,
                ]
            );
        }

        $this->sentMessage($company_order_id);
    }

    /**
     * Отправим уведомление в Телеграм
     * @param int $company_order_id
     * @return void
     */
    private function sentMessage(int $company_order_id)
    {
        $TG = new TelegramApi(
            [
                'token' => self::TOKEN,
                'chat_id' => self::CHANNEL_ID,
            ]
        );

        $user_url = trim($this->config->back_url, '/') . '/client/' . $this->user->id;
        $order_url = trim($this->config->back_url, '/') . '/company_order/view/' . $company_order_id;
        $message = "Поступила новая заявка" . PHP_EOL . PHP_EOL;
        $message .= "<b>Пользователь</b>: <a href='$user_url'>Посмотреть в ЦРМ</a>" . PHP_EOL;
        $message .= "<b>Предзаявка на сумму {$_POST['order']['amount']} RUB</b>: <a href='$order_url'>Посмотреть в ЦРМ</a>" . PHP_EOL;
        $message .= "<b>IP клиента</b>: " . $_SERVER['REMOTE_ADDR'] . PHP_EOL;

        $TG->sendMessage($message);
    }

    /**
     * Обновим поля пользователя
     * @return void
     */
    private function updateUser()
    {
        $field_list_user = ['inn', 'Snils'];
        $field_list_data_user = ['ogrnip', 'company_form_email'];

        $update_user_fields = [];
        $user_id = (int)$this->user->id;

        foreach ($field_list_user as $field) {
            if (!empty($_POST['personal'][$field]) && empty($this->user->{$field})) {
                $update_user_fields[$field] = trim($_POST['personal'][$field]);
            }
        }

        if (!empty($update_user_fields)) {
            $this->users->update_user($user_id, $update_user_fields);
        }

        foreach ($field_list_data_user as $field) {
            if (!empty($_POST['personal'][$field]) && empty($this->user_data->get($user_id, $field))) {
                $this->user_data->set($user_id, $field, trim($_POST['personal'][$field]));
            }
        }
    }

    /**
     * Добавляет новую заявку
     * @return mixed
     */
    private function addOrder()
    {
        $data_insert = [
            'status' => $this->company_orders::STATUS_NEW,
            'user_id' => $this->user->id,
            'ip' => $_SERVER['REMOTE_ADDR'],
        ];

        foreach (['order', 'payment'] as $key) {
            $post_data = $this->request->post($key);
            foreach ($post_data as $field => $value) {
                $data_insert[$field] = $value;
            }
        }

        return $this->company_orders->addItem($data_insert);
    }

    /**
     * Валидация запроса
     * @return void
     */
    private function validate(): void
    {
        $user_fields = $this->getFormFields();

        foreach ($user_fields as $key => $field) {
            $data_post = $this->request->post($key);
            foreach ($field['values'] as $field_key => $field_value) {
                if (empty($data_post[$field_key])) {
                    $this->errors[$field_key] = 'Заполните поле ' . $field_value;
                }
            }
        }

        $smart_token = $this->request->post('smart-token');
        $captcha_validate = \api\YaSmartCaptcha::check_captcha($smart_token);

        if (!$captcha_validate) {
            $this->errors['captcha'] = 'Проверка роботом не пройдена';
        }
    }
}
