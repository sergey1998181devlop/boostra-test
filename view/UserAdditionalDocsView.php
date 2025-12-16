<?php

use api\helpers\DocsHelper;

require_once('View.php');

class UserAdditionalDocsView extends View
{
    use \api\traits\JWTAuthTrait;

    public function fetch()
    {
        $this->jwtAuthValidate();

        $extra_docs = [];
        $userLastOrder = (array) $this->orders->get_last_order($this->user->id);

        if($userLastOrder['1c_status'] !== Orders::ORDER_1C_STATUS_CLOSED){
            $order_ids = $this->orders->get_order_ids($this->user->loan_history);
            $orders = $this->orders->get_orders(['id' => $order_ids]);
            $show_docs = $this->user_data->read($this->user->id, UserData::SHOW_EXTRA_DOCS) === '1';

            foreach ($orders as $order) {
                $documents = $this->documents->get_documents([
                    'order_id' => $order->id,
                    'type' => [
                        Documents::CONTRACT_STAR_ORACLE,
                        Documents::STAR_ORACLE_POLICY,
                        Documents::ORDER_FOR_EXECUTION_STAR_ORACLE,
                        Documents::ACCEPT_TELEMEDICINE,
                        Documents::DOC_MULTIPOLIS,
                    ],
                    'exclude_older_than_days' => $this->documents::COOLDOWN_DAYS
                ]);

                if (!$show_docs && $order->status_1c === '6.Закрыт') {
                    continue;
                }

                $loan_history = DocsHelper::filterByPattern($this->user->loan_history);
                $loan_id = $this->orders->get_loan_id($order->id, $loan_history);

                if (!$loan_id) {
                    continue;
                }

                $extra_docs[$loan_id]['date'] = date('d.m.Y', strtotime($this->orders->get_loan_date($order->id, $loan_history)));
                $extra_docs[$loan_id]['crm'] = $documents;
            }
        }

        $this->design->assign('extra_docs', $extra_docs);
        return $this->design->fetch('user_additional_docs.tpl');
    }

}
