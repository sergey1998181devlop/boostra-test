<?php

namespace boostra\domains;

/**
 * @property $id             int
 * @property $user_id        int
 * @property $base_card      int
 * @property $name           string
 * @property $pan            string
 * @property $expdate        string
 * @property $approval_code  string
 * @property $token          string
 * @property $created        string
 * @property $operation_date string
 * @property $operation      int
 * @property $register_id    int
 * @property $transaction_id int
 * @property $file_id        int
 * @property $deleted        int
 * @property $autodebit      int
 */
class Card extends \boostra\domains\abstracts\EntityObject{
    
    public static function table(): string
    {
        return 'b2p_cards';
    }
}