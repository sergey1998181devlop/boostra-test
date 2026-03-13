<?php

require_once 'View.php';

class CompanyOrdersView extends View
{
    const LIMIT = 50;

    const FIELDS = [
        'personal' => [
            'name' => 'Персональные данные',
            'values' => [
                'lastname' => 'Фамилия',
                'firstname' => 'Имя',
                'patronymic' => 'Отчество',
                'birth' => 'Дата рождения',
                'birth_place' => 'Место рождения',
            ]
        ],
        'passport' => [
            'name' => 'Паспортные данные',
            'values' => [
                'passport_serial' => 'Паспорт серия-номер',
                'passport_issued' => 'Кем выдан',
                'passport_date' => 'Дата выдачи',
            ]
        ],
        'registration' => [
            'name' => 'Место регистрации',
            'values' => [
                'Regregion' => 'Регион',
                'Regcity' => 'Город',
                'Regstreet' => 'Улица',
                'Reghousing' => 'Дом',
                'Regroom' => 'Квартира',
            ]
        ],
        'contacts' => [
            'name' => 'Реквизиты',
            'values' => [
                'inn' => 'ИНН',
                'Snils' => 'СНИЛС',
                'ogrnip' => 'ОГРНИП',
                'phone_mobile' => 'Номер телефона',
                'company_form_email' => 'Электронная почта',
            ]
        ],
        'information' => [
            'name' => 'Доп. информация',
            'values' => [
                'amount' => 'Сумма',
                'status' => 'Статус',
                'tax' => 'Форма налогообложения',
                'okved' => 'ОКВЭД',
                'co_credit_target_id' => 'Цель кредитования',
                'created_at' => 'Дата создания',
                'updated_at' => 'Последняя дата изменения',
            ],
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

    public function fetch()
    {
        if (!in_array('company_orders', $this->manager->permissions))
        {
            return $this->design->fetch('403.tpl');
        }

        $action = $this->request->get('action');
        $this->design->assign('statuses', $this->company_orders::getStatuses());
        $this->design->assign('fields', self::FIELDS);

        switch ($action) {
            case 'view':
                $company_order = $this->company_orders->getItem($this->request->get('id'));
                $client = $this->users->get_user((int)$company_order->user_id);

                $user_data = [
                    'ogrnip' => $this->user_data->get((int)$company_order->user_id, 'ogrnip')->value ?? null,
                    'company_form_email' => $this->user_data->get((int)$company_order->user_id, 'company_form_email')->value ?? null,
                ];

                $this->design->assign('assignment_doc_url', $this->getAssignmentDocUrl($company_order));
                $this->design->assign('order', $company_order);
                $this->design->assign('client', $client);
                $this->design->assign('user_data', $user_data);

                return $this->design->fetch('company_order/view.tpl');
            case 'update':
                $this->design->assign('taxes', ['ОСНО', 'УСН', 'ЕСХН', 'ПСН']);
                $this->design->assign('credit_targets', $this->company_orders->getCreditTargets());
                if ($this->request->method() === 'POST') {
                    $this->company_orders->updateItem($this->request->get('id'), $_POST['update_data']);
                    $this->design->assign('update_message', 'Запись обновлена');
                }
                $company_order = $this->company_orders->getItem($this->request->get('id'));
                $this->design->assign('order', $company_order);
                return $this->design->fetch('company_order/update.tpl');
            case 'delete':
                $this->company_orders->deleteItem($this->request->get('id'));
                $this->design->assign('delete_message', 'Запись удалена');
                return $this->design->fetch('company_order/index.tpl');
            default:
                $currentPage = max(1, $this->request->get('page', 'integer'));
                $filter = $this->request->get('filter') ?? [];

                if (!empty($filter['user'])) {
                    $filter['user'] = array_filter($filter['user'], function ($val) {
                        return !empty($val);
                    });
                    $filter['user'] = array_map('trim', $filter['user']);
                }

                $totalItems = $this->company_orders->getItems($filter, true);
                $pagesNum = ceil($totalItems / self::LIMIT);

                $filter = array_merge($filter, [
                    'limit' => self::LIMIT,
                    'offset' => self::LIMIT * ($currentPage - 1),
                ]);
                $items = $this->company_orders->getItems($filter);

                $this->design->assign('current_page_num', $currentPage);
                $this->design->assign('total_pages_num', $pagesNum);
                $this->design->assign('total_items', $totalItems);
                $this->design->assign('items', $items);
                return $this->design->fetch('company_order/index.tpl');
        }
    }

    private function getAssignmentDocUrl($company_order): string
    {
        $company_order_id = $company_order->id;
        $user_id = $company_order->user_id;

        return $this->config->front_url . '/preview/' . (str_replace(
                'preview_',
                '',
                strtolower($this->documents::PREVIEW_PORUCHENIE_NA_PERECHISLENIE_MIKROZAJMA)
            )) . '?' . http_build_query(
                [
                    'user_id' => $user_id,
                    'params' => $this->documents->getCompanyOrderAssignmentParams($company_order_id),
                ]
            );
    }
}
