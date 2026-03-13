<?php

require_once 'View.php';


/**
 * Class StopListWebIdView
 * Класс для работы со стоп листами трафика
 */
class StopListWebIdView extends View
{
    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        $sources = $this->orders->getUtmSources();
        $this->design->assign('sources', $sources);

        $results = $this->stop_list_web_id->getItems();
        $this->design->assign('results', $results);

        return $this->design->fetch('stop_list_web_id_view.tpl');
    }

    /**
     * Получает список webmaster_id по метке
     */
    private function getWebmasterIds()
    {
        $filter_utm_source = $this->request->get('utm_source', 'string');
        $webmaster_ids = $this->orders->getWebmasterIds($filter_utm_source);

        $this->design->assign('webmaster_ids', $webmaster_ids);
        $response =  $this->design->fetch('html_blocks/filter_webmaster_ids.tpl');

        $this->response->html_output($response);
    }

    /**
     * Добавляет записи
     * @return void
     */
    private function addItems()
    {
        $utm_source = $this->request->post('utm_source');
        $web_master_ids = $this->request->post('filter_webmaster_id');
        $results = [];

        foreach ($web_master_ids as $web_master_id) {
            $data = compact('utm_source', 'web_master_id');
            if (empty($this->stop_list_web_id->findItems($data))) {
                $id = $this->stop_list_web_id->addItem($data);
                $results[] = array_merge($data, compact('id'));
            }
        }

        $this->response->json_output(compact('results'));
    }

    /**
     * Удаляет запись
     * @return void
     */
    private function deleteItem()
    {
        $id = $this->request->post('id', 'number');
        $this->stop_list_web_id->deleteItem($id);
        $this->response->json_output('ok');
    }
}
