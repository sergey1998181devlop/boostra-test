<?php

declare(strict_types=1);

require_once 'Simpla.php';

/**
 * Класс для работы с отвалами
 */
class Missings extends Simpla
{
    public const REGISTRATION_DATA_STEP = 'created';
    public const REGISTRATION_DATA_STEP_DATE = 'created';
    public const REGISTRATION_DATA_STEP_STRING = 'Ввод номера телефона / Страница с вводом ФИО';

    public const PERSONAL_DATA_STEP = 'personal_data_added';
    public const PERSONAL_DATA_STEP_DATE = 'personal_data_added_date';
    public const PERSONAL_DATA_STEP_STRING = 'Ввод паспортных данных';

    public const ACCEPT_DATA_STEP = 'accept_data_added';
    public const ACCEPT_DATA_STEP_DATE = 'accept_data_added_date';
    public const ACCEPT_DATA_STEP_STRING = 'Страница с предварительным решением';

    public const ADDRESS_DATA_STEP = 'address_data_added';
    public const ADDRESS_DATA_STEP_DATE = 'address_data_added_date';
    public const ADDRESS_DATA_STEP_STRING = 'Ввод адресов';

    public const CARD_DATA_STEP = 'card_added';
    public const CARD_DATA_STEP_DATE = 'card_added_date';
    public const CARD_DATA_STEP_STRING = 'Страница с привязкой карты';

    public const FILES_DATA_STEP = 'files_added';
    public const FILES_DATA_STEP_DATE = 'files_added_date';
    public const FILES_DATA_STEP_STRING = 'Страница с загрузкой фото';

    public const ADDITIONAL_DATA_STEP = 'additional_data_added';
    public const ADDITIONAL_DATA_STEP_DATE = 'additional_data_added_date';
    public const ADDITIONAL_DATA_STEP_STRING = 'Ввод данных о работе';

    public const STEPS = [
        self::PERSONAL_DATA_STEP,
        self::ACCEPT_DATA_STEP,
        self::ADDRESS_DATA_STEP,
        self::CARD_DATA_STEP,
        self::FILES_DATA_STEP,
        self::ADDITIONAL_DATA_STEP,
    ];

    public const STEPS_DATES = [
        self::PERSONAL_DATA_STEP_DATE,
        self::ACCEPT_DATA_STEP_DATE,
        self::ADDRESS_DATA_STEP_DATE,
        self::CARD_DATA_STEP_DATE,
        self::FILES_DATA_STEP_DATE,
        self::ADDITIONAL_DATA_STEP_DATE,
    ];

    public const STEPS_DATE_MAPPING = [
        self::REGISTRATION_DATA_STEP => self::REGISTRATION_DATA_STEP_DATE,
        self::PERSONAL_DATA_STEP => self::PERSONAL_DATA_STEP_DATE,
        self::ACCEPT_DATA_STEP => self::ACCEPT_DATA_STEP_DATE,
        self::ADDRESS_DATA_STEP => self::ADDRESS_DATA_STEP_DATE,
        self::CARD_DATA_STEP => self::CARD_DATA_STEP_DATE,
        self::FILES_DATA_STEP => self::FILES_DATA_STEP_DATE,
        self::ADDITIONAL_DATA_STEP => self::ADDITIONAL_DATA_STEP_DATE,
    ];

    public const STEPS_STRING_MAPPING = [
        self::REGISTRATION_DATA_STEP => self::REGISTRATION_DATA_STEP_STRING,
        self::PERSONAL_DATA_STEP => self::PERSONAL_DATA_STEP_STRING,
        self::ACCEPT_DATA_STEP => self::ACCEPT_DATA_STEP_STRING,
        self::ADDRESS_DATA_STEP => self::ADDRESS_DATA_STEP_STRING,
        self::CARD_DATA_STEP => self::CARD_DATA_STEP_STRING,
        self::FILES_DATA_STEP => self::FILES_DATA_STEP_STRING,
        self::ADDITIONAL_DATA_STEP => self::ADDITIONAL_DATA_STEP_STRING,
    ];

    public const STEP_NUMBER_MAPPING = [
        1 => self::REGISTRATION_DATA_STEP,
        2 => self::PERSONAL_DATA_STEP,
        3 => self::ACCEPT_DATA_STEP,
        4 => self::ADDRESS_DATA_STEP,
        5 => self::CARD_DATA_STEP,
        6 => self::FILES_DATA_STEP,
        7 => self::ADDITIONAL_DATA_STEP,
    ];

    /**
     * Check if a missing client has a manager
     *
     * @param StdClass $client
     * @param string|null $missingManagerId
     * @return bool
     */
    public function checkInProgress(StdClass $client, string $missingManagerId = null): bool
    {
        if (
            ($missingManagerId && $client->missing_manager_id == $missingManagerId)
            || $client->missing_manager_id > 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if a missing client has a manager and his all registration steps were filled
     *
     * @param StdClass $client
     * @param string|null $missingManagerId
     * @return bool
     */
    public function checkCompleted(StdClass $client, string $missingManagerId = null): bool
    {
        if (
            ($missingManagerId
            && $client->missing_manager_id == $missingManagerId
            && $client->additional_data_added == 1)
            || (
                $client->missing_manager_id > 0
                && (int) $client->additional_data_added === 1
//                && date('d.m.Y', strtotime($client->additional_data_added_date ?: '') ?: 0) === date('d.m.Y')
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if a missing client has all registration steps filled
     *
     * @param StdClass $client
     * @return bool
     */
    public function checkFilled(StdClass $client): bool
    {
        return ($client->additional_data_added == 1)
        || (date('d.m.Y', strtotime($client->additional_data_added_date ?: '') ?: 0) === date('d.m.Y'));
    }

    /**
     * Check if a missing client is unhandled
     *
     * @param StdClass $client
     * @return bool
     */
    public function checkUnhandled(StdClass $client): bool
    {
        return !$this->checkInProgress($client) && !$this->checkFilled($client);
    }

    /**
     * Get clients last calls
     *
     * @param array $clients
     * @return array
     * @throws Exception
     */
    private function getClientsLastCalls(array $clients): array
    {
        $lastCalls = [];
        
        if (empty($clients)) {
            return $lastCalls;
        }

        $calls = $this->voxCalls->get_calls([
            'user_id' => array_column( $clients, 'id')
        ]);
        
        foreach ($calls as $call) {
            if (!array_key_exists($call->user_id, $lastCalls)) {
                $lastCalls[$call->user_id] = $call;
            }
        }

        return $lastCalls;
    }

    /**
     * Get client's last missing step number
     *
     * @param StdClass $client
     * @return int
     */
    public function getLastStepNumber(StdClass $client): int
    {
        $result = 1;

        foreach (self::STEPS_DATE_MAPPING as $step => $date) {
            if ($client->first_missing_date == $client->$date) {
                $result = array_search($step, self::STEP_NUMBER_MAPPING) ?? $result;
                break;
            }
        }
        return $result;

    }

    /**
     * Get managers' name, last call date, date, time, etc. for a client
     *
     * @param array $clients
     * @return array
     * @throws Exception
     */
    public function getClientsAdditionalData(array $clients,$from, $to): array
    {
        // Get managers for their names
        $managersIdName = [];
        $managers       = $this->managers->get_managers();
        $lastCalls      = $this->getClientsLastCalls( $clients );
        foreach ($managers as $manager) {
            $managersIdName[$manager->id] = $manager->name_1c;
        }
        
        // Get the latest orders for all given clients
        $ids          = array_column( $clients, 'id' );
        $users_orders = $this->orders->get_order_status_by_user_ids( $ids, 'user_id' );
        $status_name = '1c_status';
        
        array_walk($clients, function ($client) use ($to, $from, $lastCalls, $managersIdName, $users_orders, $status_name) {

            if (
                !$client->first_missing_date
                || !($client->first_missing_date >= $from)
                || !($client->first_missing_date <= $to)
            ) {
                return $client;
            }

            $client->date = date('d.m.Y', strtotime($client->first_missing_date));
            $client->time = date('H:i', strtotime($client->first_missing_date));
            $client->manager_name = $managersIdName[$client->missing_manager_id] ?? '';
            $client->contact_step = $client->stage_in_contact
                ? self::STEPS_STRING_MAPPING[self::STEP_NUMBER_MAPPING[(int)$client->stage_in_contact]]
                : '';
            $client->completed = isset( $client->additional_data_added ) && (int) $client->additional_data_added === 1 ? 'Да' : 'Нет' ;
            $client->last_step = self::STEPS_STRING_MAPPING[
                self::STEP_NUMBER_MAPPING[$this->getLastStepNumber($client)]
            ];
            $client->last_call = $lastCalls[$client->id]->created ?? null;
            if ($client->call_status) {
                $client->call_status = $client->call_status == Users::CALL_STATUS_NOT_OK
                    ? Users::CALL_STATUS_MAP[Users::CALL_STATUS_NOT_OK]
                    : Users::CALL_STATUS_MAP[Users::CALL_STATUS_OK];
            }

            if ($client->continue_order) {
                $client->continue_order = $client->continue_order == Users::CONTINUE_ORDER_NO
                    ? Users::CONTINUE_ORDER_MAP[Users::CONTINUE_ORDER_NO]
                    : Users::CONTINUE_ORDER_MAP[Users::CONTINUE_ORDER_YES];
            }
            
            $client->loan_issued = isset( $users_orders[$client->id] ) && $users_orders[$client->id]->$status_name === '5.Выдан' ? 'Да' : 'Нет';
            
            return $client;
        });
        
        return $clients;
    }
    
    /**
     * Gets opened loans from JSON (like in s_users.loan_history)
     *
     * @param string JSON $loans
     *
     * @return false|mixed
     */
    private function getOpenedLoans( string $loans )
    {
        $loans = json_decode( $loans, true );
        
        return ! $loans
            ? []
            : array_filter(
                $loans,
                static function( $loan ){
                    return empty ( $loan->close_date );
                }
            );
    }
    
}