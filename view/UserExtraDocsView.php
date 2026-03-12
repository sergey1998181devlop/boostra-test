<?php

use api\helpers\DocsHelper;

require_once('View.php');

class UserExtraDocsView extends View
{
    use \api\traits\JWTAuthTrait;

    public function fetch()
    {
        $this->jwtAuthValidate();

        $extra_docs = [];

        $order_ids = $this->orders->get_order_ids($this->user->loan_history);
        $orders = $this->orders->get_orders(['id' => $order_ids]);

        $show_docs = $this->user_data->read($this->user->id, UserData::SHOW_EXTRA_DOCS) === '1';

        $documentIds = [];

        foreach ($orders as $order) {
            $documents = $this->documents->get_documents([
                'order_id' => $order->id,
                'type' => [
                    Documents::CREDIT_DOCTOR_POLICY,
                    Documents::CONTRACT_USER_CREDIT_DOCTOR,
                    Documents::ORDER_FOR_EXECUTION_CREDIT_DOCTOR,
                    Documents::PENALTY_CREDIT_DOCTOR,
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

            foreach ($documents as $document){
                $documentIds[] = $document->id;
            }
        }

        $user_balance = $this->users->get_user_balance($this->user->id);
        $zaim_date = $user_balance->zaim_date;
        $user_data = $this->user_data->readAll($this->user->id);

        $this->design->assign('user_data', $user_data);
        $this->design->assign('zaim_date', $zaim_date);
        $this->design->assign('extra_docs', $extra_docs);

       // $this->getClientVisibleDocs($documentIds);

        return $this->design->fetch('user_extra_docs.tpl');
    }

    private function getClientVisibleDocs($documentIds)
    {
        $extra_docs = [];
        $orders = $this->orders->get_orders(['user_id' => $this->user->id]);

        foreach ($orders as $order) {
            $documents = $this->documents->get_documents([
                'order_id' => $order->id,
                'client_visible' => 1,
                'filter_not_ids' => $documentIds
            ]);

            if($documents){
                $extra_docs[$order->id]['document_section_name'] = " Документы по договору N".$order->id.' от '.date('d.m.Y', strtotime($order->date));
                $extra_docs[$order->id]['documents'] = $documents;
            }
        }

        $this->design->assign('client_visible_docs', $extra_docs);
    }

}
