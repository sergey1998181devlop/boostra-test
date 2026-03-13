<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'View.php';

/**
 * Генерация отчёта по кредитному рейтингу
 * Class OrderRatingReportView
 */
class PartnerHrefView extends View
{
    public function fetch()
    {
        $method = mb_strtolower($this->request->method());
        $action = $this->request->get('action');
        $is_ajax = (bool)($this->request->get('ajax'));
        if (in_array($method, ['post', 'get', 'delete', 'put']) && method_exists(self::class, $method) && $is_ajax) {
            header("Content-type: application/json; charset=UTF-8");
            header("Cache-Control: must-revalidate");
            header("Pragma: no-cache");
            header("Expires: -1");

            $this->$method();
            exit();
        }

        if ($action && method_exists(self::class, $action)) {
            $this->$action();
            header( 'Location:' . $this->config->root_url . '/partner_href' );
        }

        $this->design->assign('items', $this->PartnerHref->getAll());
        $this->design->assign('click_hunter', $this->settings->click_hunter ?: [
            'partner_name' => '',
            'url' => '',
            'status' => 0,
        ]);

        return $this->design->fetch('partner_href.tpl');
    }

    private function get()
    {
        $id = (int)$this->request->get('id');
        $result = $this->PartnerHref->getItem($id);
        echo json_encode(compact('result'));
    }

    private function post()
    {
        $data = $_POST;
        $result = $this->PartnerHref->addItem($data);
        $this->response->json_output(compact('result'));
    }

    private function delete()
    {
        $id = (int)$this->request->get('id');
        $result = $this->PartnerHref->deleteItem($id);
        $this->response->json_output(compact('result'));
    }

    private function put()
    {
        $id = (int)$this->request->get('id');
        parse_str(parse_url(file_get_contents('php://input'), PHP_URL_PATH), $data);
        $result = $this->PartnerHref->updateItem($id, $data);
        $this->response->json_output(compact('result'));
    }

    private function getAll()
    {
        return $this->PartnerHref->getAll();
    }

    private function click_hunter()
    {
        $data = $this->request->post('click_hunter');
        $this->settings->click_hunter = array_map('trim', $data);
    }
}
