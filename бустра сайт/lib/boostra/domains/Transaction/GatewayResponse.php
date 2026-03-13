<?php

namespace boostra\domains\Transaction;

/**
 *      Success
 * @property int    $order_id      798139167
 * @property string $order_state   COMPLETED
 * @property string $reference     ba8da6c2-93a6-4f39-97ea-60bb94b40e0d
 * @property int    $id            1204334102
 * @property string $date          2023.09.01 13:18:13
 * @property string $type          PURCHASE
 * @property string $state         APPROVED
 * @property int    $reason_code   1
 * @property string $message       Successful financial transaction
 * @property string $name          UNKNOWN NAME
 * @property string $pan           555957******5131
 * @property string $token         UID-like-string
 * @property int    $amount        33000
 * @property int    $currency      643
 * @property string $approval_code 2WZ61K
 * @property string $expdate       07/2024
 * @property string $signature     ZTA3YmVjZDAxNzdiZDFhM2NkYzIwNzllZTQzZGJhNGY
 *
 *      Error
 * @property string $description Invalid something
 * @property int $code           109
 */
class GatewayResponse extends \boostra\domains\abstracts\ValueObject
{
    public function __construct( $params = [] ){
        
        $params = is_string( $params )
            ? simplexml_load_string($params)
            : $params;
        
        parent::__construct( $params );
    }
    
    public function init()
    {
        $this->order_id    = isset( $this->order_id )    ? (int) $this->order_id    : 0;
        $this->id          = isset( $this->id )          ? (int) $this->id          : 0;
        $this->reason_code = isset( $this->reason_code ) ? (int) $this->reason_code : 1;
        $this->amount      = isset( $this->amount )      ? (int) $this->amount      : 0;
        $this->currency    = isset( $this->currency )    ? (int) $this->currency    : 0;
    }

    public function isError( $method, $correct_state )
    {
        // Статуса нет, значит ошибка
        if( ! isset( $this->state ) ){
            throw new \Exception("Возврат не выполнен. Операция: '$method'. Ошибка: $this->description. Код: $this->code Статус: $this->state" );
        }
        
        // Статус не тот
        if( $this->state !== $correct_state ){
            $this->message = $this->getErrorDescriptionByReasonCode( $this->reason_code );
            
            throw new \Exception("Возврат не выполнен. Операция: '$method'. Статус: $this->state. Ошибка: Не верный статус. Ошибка: $this->message. Код: $this->reason_code" );
        }
        
        /**
        * Если reason code !== 1, тогда ошибка лежит в message
        * Так как примеров всех ответов нет, то можно использовать API для перевода текста ошибок, обязательно выводить при этом код.
        */
        if( $this->reason_code !== 1 ){
            $this->message = $this->getErrorDescriptionByReasonCode( $this->reason_code );
        
            throw new \Exception("Возврат не выполнен. Операция: '$method'. Ошибка: $this->message. Код: $this->reason_code" );
        }
    }
    
    private function getErrorDescriptionByReasonCode( $code )
    {
        switch( $code ){
            
            // Response codes
            case 0:  return 'Операция отклонена по другим причинам. Требуется уточнение у ПЦ.';
            case 1:  return 'Успешно';
            case 2:  return 'Неверный срок действия Банковской карты';
            case 3:  return 'Неверный статус Банковской карты на стороне Эмитента';
            case 4:  return 'Операция отклонена Эмитентом';
            case 5:  return 'Операция недопустима для Эмитента';
            case 6:  return 'Недостаточно средств на счёте Банковской карты';
            case 7:  return 'Превышен установленный для ТСП лимит на сумму операций (дневной, недельный, месячный) или сумма операции выходит за пределы установленных границ';
            case 8:  return 'Операция отклонена по причине срабатывания системы предотвращения мошенничества';
            case 9:  return 'Заказ уже находится в процессе оплаты. Операция, возможно, задублировалась';
            case 10: return 'Системная ошибка';
            case 11: return 'Ошибка 3DS аутентификации';
            case 12: return 'Указано неверное значение секретного кода карты';
            case 13: return 'Операция отклонена по причине недоступности Эмитента и/или Банкаэквайрера';
            // No 14th in documentation
            case 15: return 'BIN платёжной карты присутствует в черных списках';
            case 16: return 'BIN 2 платёжной карты присутствует в черных списках';
            case 17: return 'Заказ просрочен';
            case 18: return 'Неверно задан параметр "month"/"reference" ';
            case 19: return 'Операция оспаривается плательщиком';
            
            // Error codes
            case 100: return 'Неправильный ID операции';
            case 101: return 'Неправильный ID заказа';
            case 102: return 'Неправильный ID сектора';
            case 103: return 'Операция не найдена';
            case 104: return 'Заказ не найден';
            case 105: return 'Сектор не найден';
            case 106: return 'Операция не принадлежит Заказу';
            case 107: return 'Операция не принадлежит сектору';
            case 108: return 'Заказ не принадлежит Сектору';
            case 109: return 'Неверная цифровая подпись';
            case 110: return 'Отсутствует параметр "reference"';
            case 111: return 'Отсутствует параметр "amount"';
            case 112: return 'Отсутствует параметр "currency"';
            case 113: return 'Валюта отличается от валюты Сектора';
            case 114: return 'Отсутствует электронная почта';
            case 115: return 'Неверная электронная почта';
            case 116: return 'Отсутствует телефон';
            case 117: return 'Неверный формат телефона';
            case 118: return 'Заказ не зарегистрирован в базе данных ПЦ';
            case 121: return 'ТСП не активирован в ПЦ';
            case 122: return 'Длина параметра "description" превышаетзаданное ограничение';
            case 123: return 'Длина параметра "email" превышает заданноеограничение';
            case 124: return 'Длина параметра "phone" превышает заданноеограничение';
            case 125: return 'Длина параметра "URL" превышает заданноеограничение';
            case 126: return 'Заказ уже находится в процессе оплаты';
            case 127: return 'Плательщик отказался от совершения операции';
            case 128: return 'Неправильная сумма операции';
            case 129: return 'Валюта отличается от валюты Заказа';
            case 130: return 'Внутренняя ошибка';
            case 131: return 'Заказ не авторизован';
            case 132: return 'Оригинальная операция не найдена';
            case 133: return 'Неправильный статус Заказа для указаннойОперации';
            case 134: return 'Сумма Операции превышает суммуоригинальной Операции';
            case 135: return 'Сумма Возврата не равна сумме оригинальнойОперации';
            case 136: return 'Неправильный код валюты';
            case 137: return 'Длина параметра "reference" превышаетзаданное ограничение';
            case 138: return 'Неверное значение параметра "mode"';
            case 139: return 'Неверное значение параметра';
            case 140: return 'Отсутствует параметр "cvc/cvv2"';
            case 141: return 'Неверное значение параметра "name"';
            case 142: return 'Неверное значение параметра "pan"';
            case 143: return 'Неверное значение параметра "month"';
            case 144: return 'Неверное значение параметра "year"';
            case 145: return 'ТСП не поддерживает операцию';
            case 146: return 'Крипто модуль неактивен';
            case 147: return 'ТСП не поддерживает регулярные платежи';
            case 148: return 'ТСП не поддерживает работу в режиме';
            case 149: return 'ТСП не поддерживает работу с токеном карты';
            case 150: return 'ТСП не поддерживает операцию';
            case 151: return 'ТСП не поддерживает операцию';
            case 152: return 'Токен 3DS не существует';
            case 153: return 'ТСП не поддерживает операцию';
            case 154: return 'Некорректный IP сервера';
            case 155: return 'Неверно задан период';
            case 156: return 'Неверно введена капча';
            case 157: return 'Неверно рассчитана комиссия';
            case 158: return 'Карта не соответствует выбранной платёжнойсистеме';
            case 159: return 'Неверный идентификатор кэша';
            case 160: return 'Токен не был создан';
            case 161: return 'ТСП не поддерживает операцию';
            case 162: return 'Запрет операции по ссылке для данного сектора';
            case 163: return 'Отключены cookies, для продолжения включитеcookies или воспользуйтесь другим браузером';
            case 164: return 'Не получается найти реквизиты для зачисления';
            case 165: return 'Карта отправителя совпадает с картойполучателя';
            case 166: return 'ТСП не поддерживает операцию';
            case 167: return 'Торговец не поддерживает операцию';
            case 168: return 'Оригинальная операция не валидна';
            case 169: return 'Превышено время, отведенное на проведениеоперации';
            case 170: return 'Достигнут лимит попыток совершения операции';
            case 171: return 'Ошибка';
            case 174: return 'Невозможно привязать номер телефона кпользователю';
            case 175: return 'Неправильный пароль';
            case 176: return 'Телефон не уникален для сектора';
            case 177: return 'Пользователь не найден';
            case 178: return 'Превышено количество попыток выполненияоперации';
            case 179: return 'Был введен неправильный код СМС';
            case 180: return 'Не найдено ни одной отправленной СМС скодом';
            case 181: return 'Превышено количество попыток ввода СМСкода. Попробуйте выполнить привязку телефона заново';
            case 182: return 'Достигнут лимит переотправки СМС';
            case 183: return 'Достигнут лимит регистрации телефона';
            case 184: return 'Ошибка протокола';
            case 185: return 'Пользователь B2P не активен';
            case 187: return 'Операция запрещена для сектора';
            case 190: return 'Не удается зарегистрировать карту';
            case 191: return 'Неверная длина элемента в параметре fiscal_positions';
            case 192: return 'Общая сумма элементов fiscal_positions несовпадает с суммой заказа';
            case 193: return 'Некорректный формат параметра в элементе fiscal_positions';
            case 194: return 'Ошибка идентификации пользователя';
            case 195: return 'Ошибка ограничения уникальности';
            case 196: return '3DS 2.0 Ошибка аутентификации';
            case 197: return 'Некорректное состояние пользователя';
            case 199: return 'Достигнут лимит ввода СМС-кода';
            case 200: return 'Запрещено для текущего статуса рекуррента';
            case 201: return 'Запрещено для текущей периодичностирекуррента';
            case 202: return 'Данные кредитной карты, привязанной ксектору, не найдены';
            case 203: return 'PAN не привязан к сектору';
            case 204: return 'Данные кредитной карты сектора не найдены';
            case 205: return 'Неправильный pan или';
            case 210: return 'Некорректный ID персоны';
            case 211: return 'Банк не поддерживает операцию Bank doesn';
            case 212: return 'Нет ключа для расшифровки';
            case 213: return 'У пользователя B2P не существует кошелька';
            case 214: return 'Не задан MerchantID для мобильных платежей';
            case 215: return 'Дублированные фискальные позиции';
            case 216: return 'Некорректный формат фискальных данных';
            case 217: return 'Не переданы фискальные данные для операциис частичной суммой';
            case 218: return 'Неизвестный БИК';
            case 219: return 'Некорректный номер расчетного счета';
            case 225: return 'Некорректные данные мультиплатежей';
            case 226: return 'Заказ не регулярный';
            case 227: return 'Параметр count вне допустимого диапазона';
            case 228: return 'Потеряна операция';
            case 229: return 'Потерянная транзакция';
            case 230: return 'Потерян результат гейта';
            case 231: return 'Не выбран гейт';
            case 232: return 'Token или clientRef не указаны';
            case 233: return 'Некорректный тип ответа';
            case 234: return 'Предотвращение излишних возвратов. В заказеесть reverse со статусом';
            case 235: return 'Неправильный';
            case 236: return 'Ошибка валидации';
            case 237: return 'Потеряна операция';
            case 238: return 'Пользователь B2P заблокирован';
            case 239: return 'Превышено количество запросов в минуту / час';
            case 240: return 'Операция оспаривается плательщиком';
            case 241: return 'Некорректная настройка сектора длязапрашиваемого действия';
            case 242: return 'Не удалось зарегистрировать отправителя в СБП';
            case 243: return 'Не удалось получить данные получателя из СБП';
            case 244: return 'Ошибка сочетания параметров запроса';
            case 245: return 'Недостаточно средств на балансе';
            case 246: return 'Невозможно поменять статус. Срок действиязаказа истёк';
            case 249: return 'Предотвращение излишних операций';
            case 250: return 'Команда не поддерживается';
            case 251: return 'Неизвестная команда';
            case 252: return 'Мерчант аккаунт не зарегистрирован';
            case 253: return 'Некорректный мерчант аккаунт';
            case 254: return 'Мерчант аккаунт не верифицирован';
            case 255: return 'Сектор не поддерживает telegram платежи';
            case 256: return 'Пользователь не является владельцем бота';
            case 257: return 'Уже существует аккаунт с незавершенной верификацией';
            case 258: return 'Мерчант аккаунт уже зарегистрирован';
            case 259: return 'Невозможно разобрать сообщение';
            case 260: return 'Несовпадение версий ФФД';
            case 261: return 'sd_ref не найден';
            
            default: return 'Неизвестный код ответа:  "' . $code . '"';
        }
    }
}