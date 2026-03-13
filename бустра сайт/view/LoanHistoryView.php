<?php

require_once('View.php');

class LoanHistoryView extends View
{
    use \api\traits\JWTAuthTrait;

    public function fetch()
    {
        $this->jwtAuthValidate();

        $this->show_unaccepted_agreement_modal();

        $loans = array();
        $response = $this->soap->get_user_history($this->user->uid);

        if (!empty($response))
        {
            foreach ($response as $item)
            {
                $loan = new StdClass();
                $loan->date = $item->ДатаЗаявки;
                $loan->status = $item->СтатусЗаявки;
                $loan->number = $item->НомерЗаявки;
                $loan->reason = $item->Причина;
                
                $loans[] = $loan;
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item);echo '</pre><hr />';
            }
        }
        $card_list = $this->get_user_cards($this->user);

        usort($loans, function($a, $b){ 
            return strtotime($a->date) - strtotime($b->date);
        });

        foreach ($loans as $loan) {
            if ($order = $this->orders->get_order_by_1c($loan->number)) {
                foreach ($card_list as $card) {
                    if (!empty($order->card_id) && $order->card_id == $card->id){
                        $loan->card = $card->pan;
                    }
                }

                $loan->contract_id = $this->orders->get_loan_id($order->id, $this->user->loan_history);
                $loan->organization_name = $this->organizations->get_organization($order->organization_id)->name;
            }
        }
        $this->design->assign('loans', $loans);
        
        return $this->design->fetch('loan_history.tpl');
    }

    /**
     * @param $user
     * @return array
     */
    private function get_user_cards($user): array
    {
        if ($user->uid == "Error") {
            return [];
        }

        $cards = [];
        $b2p_enabled = $this->settings->b2p_enabled || $this->user->use_b2p;
        if ($b2p_enabled) {
            return array_map(function ($card) {
                $card->autodebiting = false;
                $card->rebill_id = false;

                return $card;
            }, $this->best2pay->get_cards(['user_id' => $user->id]));
        }

        $soap_cards = $this->notify->soap_get_card_list($user->uid);

        if ($soap_cards) {
            foreach ($soap_cards as $card) {
                if ($card->Status == 'A') {
                    $new_card = new stdClass();
                    $new_card->id = $card->CardId;
                    $new_card->pan = $card->Pan;
                    $new_card->rebill_id = $card->RebillId;


                    $cards[] = $new_card;
                }
            }
        }
        return $cards;
    }
}