<?php

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

require_once dirname(__DIR__) . '/api/Simpla.php';

/**
 * Class ReportHelper
 * генерирует различные блоки для отчётов, фильтры, select и т.д.
 */
class ReportHelper extends Simpla {

    /**
     * @return void
     * @throws Exception
     */
    public function run()
    {
        $action = $this->request->get('action', 'string');
        if (method_exists(self::class, $action)) {
            $this->{$action}();
        } else {
            http_response_code(403);
            throw new Exception('Method not exists');
        }
    }

//    /**
//     * Получает список utm меток в периоде когда создавались заявки
//     * @return void
//     */
//    private function getUtmSourcesByDate()
//    {
//        $filter_date_accept = [
//            'filter_date_start' => $this->request->post('filter_date_start', 'string'),
//            'filter_date_end' => $this->request->post('filter_date_end', 'string'),
//        ];
//
//        $utm_sources = $this->orders->getUtmSourcesByFilter(compact('filter_date_accept'));
//        $this->design->assign('utm_sources', $utm_sources);
//        $html =  $this->design->fetch('html_blocks/filter_utm_sources.tpl');
//
//        $this->response->html_output($html);
//    }

    /**
     * Получает список webmaster в периоде когда создавались заявки и по utm меткам
     * @return void
     */
    private function getWebmasterIdByFilter()
    {

        $webmaster_ids = ['0111','0112','0113','0114','0115','0116','0117','0118', '5555'];

        $this->design->assign('webmaster_ids', $webmaster_ids);
        $html =  $this->design->fetch('html_blocks/filter_webmaster_ids.tpl');

        $this->response->html_output($html);
    }
}

(new ReportHelper())->run();
