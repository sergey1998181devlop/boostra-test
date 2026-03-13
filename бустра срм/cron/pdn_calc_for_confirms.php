<?php
//error_reporting(-1);
//ini_set('display_errors', 'On');
//ini_set('max_execution_time', '3600');
//
//require_once __DIR__.'/../api/Simpla.php';
//
//class PdnCalculationForConfirms extends Simpla
//{
//    function __construct()
//    {
//        $this->run();
//    }
//
//    private function run()
//    {
//        $orders = $this->getOrdersData();
//        foreach ($orders as $order) {
//            $i = 0;
//            $pdn = $this->pdnCalculation->getPdn($order,$i);
//            $this->orders->update_order($order->id, ['pdn_nkbi_loan' => $pdn ?? 0]);
//            $confirm_date = new DateTime($order->confirm_date);
//            $this->pdnCalculation->updateLoanDate( $order->id,$confirm_date->format('Y-m-d'));
//        }
//
//    }
//    private function getOrdersData()
//    {
//        $from = date('Y-m-d 00:00:00');
//        $to = date('Y-m-d 23:59:59');
//        $query = $this->db->placehold("Select  o.id, o.date,o.contract_id,o.confirm_date, o.period,o.amount,o.percent,o.order_uid,o.status,u.id as user_id,CONCAT_WS(' ', u.lastname, u.firstname, u.patronymic) as fio, u.phone_mobile,u.Regregion,t.name_zone from s_orders o
//                                       JOIN s_users u on o.user_id = u.id
//                                       LEFT JOIN  s_time_zones t on u.timezone_id = t.time_zone_id
//                                        where (pdn_nkbi_loan is null OR pdn_nkbi_loan = 0)
//                                            AND status = 10
//                                            AND confirm_date between ? AND ?",$from, $to);
//        $this->db->query($query);
//        return $this->db->results();
//    }
//
//}
//
//new PdnCalculationForConfirms();
