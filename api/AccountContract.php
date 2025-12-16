<?php
require_once( __DIR__ . '/../api/Simpla.php');

/**
 * Class AccountContract
 * класс для работы ЛК "Оплата по договору"
 */
class AccountContract extends Simpla
{
    public const SESSION_KEY = 'CONTRACT_USER';

    /**
     * Забирает массово займы
     * @param string $uid
     * @return array
     */
    public function getUserContracts(string $uid): array
    {
        return $this->soap->get_user_balances_array_1c($uid);
    }

    public function getIlDetail($contract_number): array
    {
        return $this->soap->get_il_details($contract_number);
    }

    /**
     * Генерация новой транзакции
     * @param object $user
     * @param float $amount
     * @param $contract_number
     * @return array
     */
    public function getPaymentLink(object $user, float $amount, $contract_number): array
    {
        $contracts_1c = $_SESSION[$this->account_contract::SESSION_KEY];

        $contract_1c = null;
        foreach ($contracts_1c as $item) {
            if (isset($item["НомерЗайма"]) && $item["НомерЗайма"] === $contract_number) {
                $contract_1c = $item;
                break;
            }
        }
        
        if ($contract_1c && ($contract = $this->contracts->get_contract_by_params(['number' => $contract_number]))) {
            $order = $this->orders->get_order($contract->order_id);

            $instalment = $contract_1c['IL'] != 0;

            if ($instalment) {
                $il_data = $this->account_contract->getIlDetail($contract_number);
                $fullAmount = $il_data['ОбщийДолг'] - $il_data['Баланс'];
            } else {
                $fullAmount = $contract_1c['ОстатокОД'] + $contract_1c['ОстатокПроцентов']+$contract_1c['ШтрафнойКД'];
            }


            $action_type = $amount == $fullAmount ? $this->star_oracle::ACTION_TYPE_FULL_PAYMENT : $this->star_oracle::ACTION_TYPE_PARTIAL_PAYMENT;
            
            $oracle_amount=0;
            if ($starOracle = $this->star_oracle->getStarOraclePrice($amount)) {
                if ($amount == $fullAmount) {
                    if ($order->additional_service_so_repayment) {
                        $oracle_amount = $starOracle->price;
                    } elseif ($order->half_additional_service_so_repayment) {
                        $oracle_amount = round($starOracle->price / 2);
                    }
                } elseif ($order->additional_service_so_partial_repayment) {
                    $oracle_amount = $starOracle->price;
                } elseif ($order->half_additional_service_so_partial_repayment) {
                    $oracle_amount = round($starOracle->price / 2);
                }
            }

            $tv_med_amount=0;
            if ($tv_medical = $this->tv_medical->getVItaMedPrice($amount)) {
                if ($amount == $fullAmount) {
                    if ($order->additional_service_repayment) {
                        $tv_med_amount = $tv_medical->price;
                    } elseif ($order->half_additional_service_repayment) {
                        $tv_med_amount = round($tv_medical->price / 2);
                    }
                } elseif ($order->additional_service_partial_repayment) {
                    $tv_med_amount = $tv_medical->price;
                } elseif ($order->half_additional_service_partial_repayment) {
                    $tv_med_amount = round($tv_medical->price / 2);
                }
            }
            
            $params = array(
                'user_id' => $user->id,
                'order_id' => $order->id,
                'number' => $contract->number,
                'card_id' => '',
                'amount' => $amount + $oracle_amount + $tv_med_amount,
                'insure' => 0,
                'star_oracle' => (bool)$oracle_amount,
                'star_oracle_amount' => $oracle_amount,
                'multipolis' => 0,
                'multipolis_amount' => 0,
                'tv_medical' => (bool)$tv_med_amount,
                'tv_medical_id' => $tv_medical->id ?? 0,
                'tv_medical_amount' => $tv_med_amount,
                'prolongation' => 0,
                'asp' => 0,
                'payment_type' => 'debt',
                'calc_percents' => 0,
                'grace_payment' => 0,
                'organization_id' => $order->organization_id,
                'chdp' => 0,
                'pdp' => 0,
                'contract_payment' => 1,
                'create_from' => 'acc_con',
                'action_type' => $action_type,
            );

            // отправляем запрос в B2P
            $response = $this->best2pay->get_payment_link($params);
            if ($response) {
                $payment = $this->best2pay->get_payment($response);
                if ($payment) {
                    return [
                        'Success' => true,
                        'PaymentURL' => $payment->payment_link
                    ];
                }
            } else {
                return [
                    'Success' => false,
                    'message' => 'При получении ссылки произошла ошибка'
                ];
            }
        }
        return [
            'Success' => false,
            'message' => 'Договор не найден',
        ];
    }
}
