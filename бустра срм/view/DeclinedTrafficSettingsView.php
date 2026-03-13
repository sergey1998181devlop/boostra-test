<?php

class DeclinedTrafficSettingsView extends View
{
    /** @var array Продаваемые в Bonon источники */
    private $sources;

    public function fetch()
    {
        if (!in_array('declined_traffic_settings', $this->manager->permissions)) {
        	return $this->design->fetch('403.tpl');
        }

        $sites_list = $this->sites->getAllSites();
        $current_site = $this->request->get('site_id');
        if(!$current_site) {
            header('Location: ' . $this->config->root_url . '/declined_traffic_settings?site_id=' . $sites_list[0]->site_id);
            exit();
        }

        $this->settings->setSiteId($current_site);

        $this->sources = $this->settings->bonon_sources ?? [
            'last_id' => 0,
            'rows' => [],
        ];

        $href_data = [];
        $href_keys = [
            'bonon-background-complaint' => [
                'nk' => 'Фоновая "Пожаловаться" НК',
                'pk' => 'Фоновая "Пожаловаться" ПК',
            ],
            'bonon-background-login' => [
                'pk' => 'Фоновая на логин в ЛК',
            ],
            'bonon-background' => [
                'nk' => 'ФОНОВАЯ ВИТРИНА БЕЗ АВТОРИЗАЦИИ',
                'pk' => 'Основная фоновая ПК',
            ],
            'bonon-comeback' => [
                'nk' => 'КАМБЕКЕР БЕЗ АВТОРИЗАЦИИ',
                'pk' => 'Основной камбекер ПК',
            ],
            'bonon-shop-window-decline' => [
                'nk' => 'НК ОСНОВНАЯ (отказные)',
                'pk' => 'ПК ОСНОВНАЯ (отказные)',
            ],
            'bonon-background-decline' => [
                'nk' => 'НК ФОНОВАЯ (отказные)',
                'pk' => 'ПК ФОНОВАЯ (отказные)',
            ],
            'bonon-comeback-decline' => [
                'nk' => 'НК КАМБЕКЕР (отказные)',
                'pk' => 'ПК КАМБЕКЕР (отказные)',
            ],
            'bonon-shop-window-overdue' => [
                'nk' => 'Просрочники баннер НК',
                'pk' => 'ОСНОВНАЯ ПРОСРОЧНИКИ',
            ],
            'bonon-background-overdue' => [
                'nk' => 'Просрочники фоновая НК',
                'pk' => 'ФОНОВАЯ ПРОСРОЧНИКИ',
            ],
            'bonon-comeback-overdue' => [
                'nk' => 'Просрочники камбекер НК',
                'pk' => 'КАМБЕКЕР ПРОСРОЧНИКИ',
            ],
            'bonon-shop-window-refinance' => [
                'nk' => 'Перезайм баннер НК',
                'pk' => 'ОСНОВАНАЯ "ПЕРЕЗАЙМ" (2 дня до погашения)',
            ],
            'bonon-background-refinance' => [
                'nk' => 'Перезайм фоновая НК',
                'pk' => 'Перезайм фоновая ПК',
            ],
            'bonon-comeback-refinance' => [
                'nk' => 'Перезайм камбекер НК',
                'pk' => 'КАМБЕКЕР ПЕРЕЗАЙМ ',
            ],
        ];

        if ($action = $this->request->post('action')) {
            $response = 'Unknown action';
            switch ($action) {
                case 'toggle-bonon':
                    $response = $this->toggleAction();
                    break;
                case 'add-token':
                    $response = $this->addTokenAction();
                    break;
                case 'update-token':
                    $response = $this->updateTokenAction();
                    break;
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
        } elseif($this->request->method('post')) {
            foreach($_POST as $key => $href) {
                [$link_type, $client_type, $setting_id] = explode(':', $key);
                if($setting_id) {
                    $this->db->query('UPDATE s_partner_href SET href = ? WHERE id = ?', $href, $setting_id);
                } else {
                    $this->db->query('INSERT INTO s_partner_href SET ?%', 
                                        [
                                            'site_id' => $current_site,
                                            'href' => $href,
                                            'link_type' => $link_type,
                                            'client_type' => $client_type,
                                        ]);
                }
            }
        }

        $this->db->query('SELECT
                            ltypes.link_type
                            , ltypes.client_type
                            , ph.id
                            , ph.href
                            , ph.date_added
                          FROM (SELECT DISTINCT link_type, client_type
                                FROM s_partner_href
                                WHERE link_type IN (?@)) ltypes
                          LEFT JOIN s_partner_href ph
                            ON ltypes.link_type = ph.link_type
                            AND ltypes.client_type = ph.client_type
                            AND ph.site_id = ?'
                         , array_keys($href_keys), $current_site);
        
        foreach($this->db->results() as $row) {
            $href_data["{$row->link_type}:{$row->client_type}"] = $row;
        }

        $this->db->query("SELECT * FROM application_tokens WHERE app IN ('bonon-pk', 'bonon-nk', 'bonon-nk-acc') AND site_id = ?", $current_site);
        $this->design->assign('tokens', $this->db->results());

        $this->design->assign('current_site', $current_site);
        $this->design->assign('sites_list', $sites_list);
        $this->design->assign('href_data', $href_data);
        $this->design->assign('href_keys', $href_keys);
        $this->design->assign('bonon_enabled', $this->settings->bonon_enabled);
        $this->design->assign('sources', $this->sources['rows']);
        
        return $this->design->fetch('declined_traffic_settings.tpl');
    }

    /**
     * @return array
     */
    function getFields()
    {
        $site_id = trim($this->request->get('site_id'));
        if (empty($site_id)) {
            return ['error' => 'Укажите сайт.'];
        }

        $utm_source = trim($this->request->post('utm_source'));
        if (empty($utm_source)) {
            return ['error' => 'Укажите лидген.'];
        }

        $utm_medium = trim($this->request->post('utm_medium'));
        if (empty($utm_medium)) {
            return ['error' => 'Укажите вебмастера.'];
        }

        $chance = $this->request->post('chance');
        if ($chance == '') {
            return ['error' => 'Укажите шанс срабатывания.'];
        }

        return [
            'site_id' => $site_id,
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'chance' => (int)$chance
        ];
    }

    /**
     * @return array
     */
    function getTokenFields()
    {
        $site_id = trim($this->request->get('site_id'));
        if (empty($site_id)) {
            return ['error' => 'Укажите сайт.'];
        }

        $name = trim($this->request->post('name'));
        if (empty($name)) {
            return ['error' => 'Укажите название.'];
        }

        $body = trim($this->request->post('body'));
        if (empty($body)) {
            return ['error' => 'Укажите токен.'];
        }

        $type = $this->request->post('type');
        if (!in_array($type, ['bonon-pk', 'bonon-nk', 'bonon-nk-acc'])) {
            return ['error' => 'Укажите тип токена.'];
        }

        $state = $this->request->post('state', 'int');

        return [
            'site_id' => $site_id,
            'name' => $name,
            'token' => $body,
            'app' => $type,
            'enabled' => $state,
        ];
    }

    function addAction()
    {
        $site_id = trim($this->request->get('site_id'));
        if (empty($site_id)) {
            return ['error' => 'Укажите сайт.'];
        }

        $fields = $this->getFields();
        if (!empty($fields['error'])) {
            return $fields;
        }

        $this->sources['last_id'] += 1;
        $this->sources['rows'][$this->sources['last_id']] = (object)$fields;
        $this->settings->bonon_sources = $this->sources;

        return true;
    }

    function toggleAction()
    {
        $site_id = trim($this->request->get('site_id'));
        if (empty($site_id)) {
            return ['error' => 'Укажите сайт.'];
        }

        $this->settings->bonon_enabled = $this->request->post('value', 'integer');

        return true;
    }

    function addTokenAction()
    {
        $fields = $this->getTokenFields();
        if (!empty($fields['error'])) {
            return $fields;
        }

        $query = $this->db->placehold("INSERT INTO application_tokens SET ?%", $fields);
        $this->db->query($query);

        return true;
    }

    function updateAction()
    {
        $fields = $this->getFields();
        if (!empty($fields['error'])) {
            return $fields;
        }

        $id = $this->request->post('id', 'integer');
        if (empty($id) || empty($this->sources['rows'][$id])) {
            return ['error' => 'Произошла ошибка, обновите страницу.'];
        }

        $this->sources['rows'][$id] = (object)$fields;
        $this->settings->bonon_sources = $this->sources;

        $fields['success'] = 'Ок';
        return $fields;
    }

    function updateTokenAction()
    {
        $id = $this->request->post('id', 'integer');
        $fields = $this->getTokenFields();
        if (!empty($fields['error'])) {
            return $fields;
        }

        $query = $this->db->placehold("UPDATE application_tokens SET ?% WHERE id = ?", $fields, $id);
        $this->db->query($query);

        $this->db->query("SELECT apt.*, 'Ok' success FROM application_tokens apt WHERE apt.id = ?", $id);
        
        return $this->db->result();
    }

    function deleteAction()
    {
        $id = $this->request->post('id', 'integer');
        if (empty($id) || empty($this->sources['rows'][$id])) {
            return ['error' => 'Произошла ошибка, обновите страницу.'];
        }

        unset($this->sources['rows'][$id]);
        $this->settings->bonon_sources = $this->sources;

        return [
            'id' => $id,
            'success' => 'Ок'
        ];
    }
}
