<?php

namespace App\Models;

use App\Core\Models\BaseModel;

class SmsMessages extends BaseModel
{
    public string $table = 's_sms_messages';
    /*
     * Типы SMS сообщений
     */
    public const TYPE_TICKET_CREATED = 'ticket_created';
    public const TYPE_TICKET_IN_WORK = 'ticket_in_work';

}
