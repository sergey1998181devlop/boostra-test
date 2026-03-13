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
 * @property int    $code        109
 * @property bool   $error
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
        $this->amount      = isset( $this->amount )      ? (int) $this->amount      : 0;
        $this->currency    = isset( $this->currency )    ? (int) $this->currency    : 0;
        $this->state       = $this->state ?? 'ERROR';
        if( isset( $this->reason_code ) ){
            $this->reason_code = (int) $this->reason_code;
        }
    }
    
    /**
     * Проверяет есть ли ошибки в ответе и формирует сообщение об ошибке в свойство $this->message
     *
     * @param $method
     * @param $expected_state
     *
     * @return bool
     */
    public function isError( $method, $expected_state ): bool
    {
        // Статуса нет, выставляем ошибку
        if( $this->state === 'ERROR' ){
            $this->message = $this->getErrorDescriptionByReasonCode( $this->code );
            $this->error   = true;
            
        // Статус не тот, выставляем ошибку
        }elseif( $this->state !== $expected_state ){
            $this->message = $this->getErrorDescriptionByReasonCode( $this->reason_code, "Не верный статус. Получен: $this->state. Ожидался: $expected_state" );
            $this->error   = true;
        
        /**
        * Если reason code есть и !== 1, тогда ошибка лежит в message, выставляем ошибку
        * Так как примеров всех ответов нет, то можно использовать API для перевода текста ошибок, обязательно выводить при этом код.
        */
        }elseif( isset( $this->reason_code ) && $this->reason_code !== 1 ){
            $this->message = $this->getErrorDescriptionByReasonCode( $this->reason_code );
            $this->error   = true;
        }
        
        // Проверка наличия ошибки
        if( $this->error ){
            $this->message = "Возврат не выполнен. Операция: '$method'. Ошибка: $this->message. Код: " . ( $this->code ?? $this->reason_code );
            return true;
        }
        
        return false;
    }
    
    /**
     * Получает сообщение на русском языке по коду ответа
     *
     * @param $code
     * @param $additional_error_string
     *
     * @return string
     */
    private function getErrorDescriptionByReasonCode( $code, $additional_error_string = '' )
    {
        switch( $code ){
            
            // Response codes
            case 0:  $message = 'Операция отклонена по другим причинам. Требуется уточнение у ПЦ.'; break;
            case 1:  $message = 'Успешно'; break;
            case 2:  $message = 'Неверный срок действия Банковской карты'; break;
            case 3:  $message = 'Неверный статус Банковской карты на стороне Эмитента'; break;
            case 4:  $message = 'Операция отклонена Эмитентом'; break;
            case 5:  $message = 'Операция недопустима для Эмитента'; break;
            case 6:  $message = 'Недостаточно средств на счёте Банковской карты'; break;
            case 7:  $message = 'Превышен установленный для ТСП лимит на сумму операций (дневной, недельный, месячный) или сумма операции выходит за пределы установленных границ'; break;
            case 8:  $message = 'Операция отклонена по причине срабатывания системы предотвращения мошенничества'; break;
            case 9:  $message = 'Заказ уже находится в процессе оплаты. Операция, возможно, задублировалась'; break;
            case 10: $message = 'Системная ошибка'; break;
            case 11: $message = 'Ошибка 3DS аутентификации'; break;
            case 12: $message = 'Указано неверное значение секретного кода карты'; break;
            case 13: $message = 'Операция отклонена по причине недоступности Эмитента и/или Банкаэквайрера'; break;
            // No 14th in documentation
            case 15: $message = 'BIN платёжной карты присутствует в черных списках'; break;
            case 16: $message = 'BIN 2 платёжной карты присутствует в черных списках'; break;
            case 17: $message = 'Заказ просрочен'; break;
            case 18: $message = 'Неверно задан параметр "month"/"reference" '; break;
            case 19: $message = 'Операция оспаривается плательщиком'; break;
            
            // Error codes
            case 100: $message = 'Неправильный ID операции'; break;
            case 101: $message = 'Неправильный ID заказа'; break;
            case 102: $message = 'Неправильный ID сектора'; break;
            case 103: $message = 'Операция не найдена'; break;
            case 104: $message = 'Заказ не найден'; break;
            case 105: $message = 'Сектор не найден'; break;
            case 106: $message = 'Операция не принадлежит Заказу'; break;
            case 107: $message = 'Операция не принадлежит сектору'; break;
            case 108: $message = 'Заказ не принадлежит Сектору'; break;
            case 109: $message = 'Неверная цифровая подпись'; break;
            case 110: $message = 'Отсутствует параметр "reference"'; break;
            case 111: $message = 'Отсутствует параметр "amount"'; break;
            case 112: $message = 'Отсутствует параметр "currency"'; break;
            case 113: $message = 'Валюта отличается от валюты Сектора'; break;
            case 114: $message = 'Отсутствует электронная почта'; break;
            case 115: $message = 'Неверная электронная почта'; break;
            case 116: $message = 'Отсутствует телефон'; break;
            case 117: $message = 'Неверный формат телефона'; break;
            case 118: $message = 'Заказ не зарегистрирован в базе данных ПЦ'; break;
            case 121: $message = 'ТСП не активирован в ПЦ'; break;
            case 122: $message = 'Длина параметра "description" превышаетзаданное ограничение'; break;
            case 123: $message = 'Длина параметра "email" превышает заданноеограничение'; break;
            case 124: $message = 'Длина параметра "phone" превышает заданноеограничение'; break;
            case 125: $message = 'Длина параметра "URL" превышает заданноеограничение'; break;
            case 126: $message = 'Заказ уже находится в процессе оплаты'; break;
            case 127: $message = 'Плательщик отказался от совершения операции'; break;
            case 128: $message = 'Неправильная сумма операции'; break;
            case 129: $message = 'Валюта отличается от валюты Заказа'; break;
            case 130: $message = 'Внутренняя ошибка'; break;
            case 131: $message = 'Заказ не авторизован'; break;
            case 132: $message = 'Оригинальная операция не найдена'; break;
            case 133: $message = 'Неправильный статус Заказа для указаннойОперации'; break;
            case 134: $message = 'Сумма Операции превышает суммуоригинальной Операции'; break;
            case 135: $message = 'Сумма Возврата не равна сумме оригинальнойОперации'; break;
            case 136: $message = 'Неправильный код валюты'; break;
            case 137: $message = 'Длина параметра "reference" превышаетзаданное ограничение'; break;
            case 138: $message = 'Неверное значение параметра "mode"'; break;
            case 139: $message = 'Неверное значение параметра'; break;
            case 140: $message = 'Отсутствует параметр "cvc/cvv2"'; break;
            case 141: $message = 'Неверное значение параметра "name"'; break;
            case 142: $message = 'Неверное значение параметра "pan"'; break;
            case 143: $message = 'Неверное значение параметра "month"'; break;
            case 144: $message = 'Неверное значение параметра "year"'; break;
            case 145: $message = 'ТСП не поддерживает операцию'; break;
            case 146: $message = 'Крипто модуль неактивен'; break;
            case 147: $message = 'ТСП не поддерживает регулярные платежи'; break;
            case 148: $message = 'ТСП не поддерживает работу в режиме'; break;
            case 149: $message = 'ТСП не поддерживает работу с токеном карты'; break;
            case 150: $message = 'ТСП не поддерживает операцию'; break;
            case 151: $message = 'ТСП не поддерживает операцию'; break;
            case 152: $message = 'Токен 3DS не существует'; break;
            case 153: $message = 'ТСП не поддерживает операцию'; break;
            case 154: $message = 'Некорректный IP сервера'; break;
            case 155: $message = 'Неверно задан период'; break;
            case 156: $message = 'Неверно введена капча'; break;
            case 157: $message = 'Неверно рассчитана комиссия'; break;
            case 158: $message = 'Карта не соответствует выбранной платёжнойсистеме'; break;
            case 159: $message = 'Неверный идентификатор кэша'; break;
            case 160: $message = 'Токен не был создан'; break;
            case 161: $message = 'ТСП не поддерживает операцию'; break;
            case 162: $message = 'Запрет операции по ссылке для данного сектора'; break;
            case 163: $message = 'Отключены cookies, для продолжения включитеcookies или воспользуйтесь другим браузером'; break;
            case 164: $message = 'Не получается найти реквизиты для зачисления'; break;
            case 165: $message = 'Карта отправителя совпадает с картойполучателя'; break;
            case 166: $message = 'ТСП не поддерживает операцию'; break;
            case 167: $message = 'Торговец не поддерживает операцию'; break;
            case 168: $message = 'Оригинальная операция не валидна'; break;
            case 169: $message = 'Превышено время, отведенное на проведениеоперации'; break;
            case 170: $message = 'Достигнут лимит попыток совершения операции'; break;
            case 171: $message = 'Ошибка'; break;
            case 174: $message = 'Невозможно привязать номер телефона кпользователю'; break;
            case 175: $message = 'Неправильный пароль'; break;
            case 176: $message = 'Телефон не уникален для сектора'; break;
            case 177: $message = 'Пользователь не найден'; break;
            case 178: $message = 'Превышено количество попыток выполненияоперации'; break;
            case 179: $message = 'Был введен неправильный код СМС'; break;
            case 180: $message = 'Не найдено ни одной отправленной СМС скодом'; break;
            case 181: $message = 'Превышено количество попыток ввода СМСкода. Попробуйте выполнить привязку телефона заново'; break;
            case 182: $message = 'Достигнут лимит переотправки СМС'; break;
            case 183: $message = 'Достигнут лимит регистрации телефона'; break;
            case 184: $message = 'Ошибка протокола'; break;
            case 185: $message = 'Пользователь B2P не активен'; break;
            case 187: $message = 'Операция запрещена для сектора'; break;
            case 190: $message = 'Не удается зарегистрировать карту'; break;
            case 191: $message = 'Неверная длина элемента в параметре fiscal_positions'; break;
            case 192: $message = 'Общая сумма элементов fiscal_positions несовпадает с суммой заказа'; break;
            case 193: $message = 'Некорректный формат параметра в элементе fiscal_positions'; break;
            case 194: $message = 'Ошибка идентификации пользователя'; break;
            case 195: $message = 'Ошибка ограничения уникальности'; break;
            case 196: $message = '3DS 2.0 Ошибка аутентификации'; break;
            case 197: $message = 'Некорректное состояние пользователя'; break;
            case 199: $message = 'Достигнут лимит ввода СМС-кода'; break;
            case 200: $message = 'Запрещено для текущего статуса рекуррента'; break;
            case 201: $message = 'Запрещено для текущей периодичностирекуррента'; break;
            case 202: $message = 'Данные кредитной карты, привязанной ксектору, не найдены'; break;
            case 203: $message = 'PAN не привязан к сектору'; break;
            case 204: $message = 'Данные кредитной карты сектора не найдены'; break;
            case 205: $message = 'Неправильный pan или'; break;
            case 210: $message = 'Некорректный ID персоны'; break;
            case 211: $message = 'Банк не поддерживает операцию Bank doesn'; break;
            case 212: $message = 'Нет ключа для расшифровки'; break;
            case 213: $message = 'У пользователя B2P не существует кошелька'; break;
            case 214: $message = 'Не задан MerchantID для мобильных платежей'; break;
            case 215: $message = 'Дублированные фискальные позиции'; break;
            case 216: $message = 'Некорректный формат фискальных данных'; break;
            case 217: $message = 'Не переданы фискальные данные для операциис частичной суммой'; break;
            case 218: $message = 'Неизвестный БИК'; break;
            case 219: $message = 'Некорректный номер расчетного счета'; break;
            case 225: $message = 'Некорректные данные мультиплатежей'; break;
            case 226: $message = 'Заказ не регулярный'; break;
            case 227: $message = 'Параметр count вне допустимого диапазона'; break;
            case 228: $message = 'Потеряна операция'; break;
            case 229: $message = 'Потерянная транзакция'; break;
            case 230: $message = 'Потерян результат гейта'; break;
            case 231: $message = 'Не выбран гейт'; break;
            case 232: $message = 'Token или clientRef не указаны'; break;
            case 233: $message = 'Некорректный тип ответа'; break;
            case 234: $message = 'Предотвращение излишних возвратов. В заказеесть reverse со статусом'; break;
            case 235: $message = 'Неправильный'; break;
            case 236: $message = 'Ошибка валидации'; break;
            case 237: $message = 'Потеряна операция'; break;
            case 238: $message = 'Пользователь B2P заблокирован'; break;
            case 239: $message = 'Превышено количество запросов в минуту / час'; break;
            case 240: $message = 'Операция оспаривается плательщиком'; break;
            case 241: $message = 'Некорректная настройка сектора длязапрашиваемого действия'; break;
            case 242: $message = 'Не удалось зарегистрировать отправителя в СБП'; break;
            case 243: $message = 'Не удалось получить данные получателя из СБП'; break;
            case 244: $message = 'Ошибка сочетания параметров запроса'; break;
            case 245: $message = 'Недостаточно средств на балансе'; break;
            case 246: $message = 'Невозможно поменять статус. Срок действиязаказа истёк'; break;
            case 249: $message = 'Предотвращение излишних операций'; break;
            case 250: $message = 'Команда не поддерживается'; break;
            case 251: $message = 'Неизвестная команда'; break;
            case 252: $message = 'Мерчант аккаунт не зарегистрирован'; break;
            case 253: $message = 'Некорректный мерчант аккаунт'; break;
            case 254: $message = 'Мерчант аккаунт не верифицирован'; break;
            case 255: $message = 'Сектор не поддерживает telegram платежи'; break;
            case 256: $message = 'Пользователь не является владельцем бота'; break;
            case 257: $message = 'Уже существует аккаунт с незавершенной верификацией'; break;
            case 258: $message = 'Мерчант аккаунт уже зарегистрирован'; break;
            case 259: $message = 'Невозможно разобрать сообщение'; break;
            case 260: $message = 'Несовпадение версий ФФД'; break;
            case 261: $message = 'sd_ref не найден'; break;
            
            default: return 'Неизвестный код ответа:  "' . $code . '"';
        }
        
        $additional_error_string = $additional_error_string ? (' ' . $additional_error_string) : '';
        
        return $message . $additional_error_string;
    }
    
    public static function getTest($method, $data, $error = '')
    {
        if( $error ){
            return $error;
        }
        
        switch( $method ){
            
            case 'webapi/Reverse':
                throw new \Exception( "Метод не поддерживается: $method" );
                
            case 'webapi/Register':
                return '<?xml version="1.0" encoding="UTF-8"?>
                        <order>
                         <id>123</id>
                         <state>REGISTERED</state>
                         <inprogress>0</inprogress>
                         <date>'.date('Y.m.d H:i:s').'</date>
                         <amount>' . $data['amount'] . '</amount>
                         <currency>643</currency>
                         <email>mail@somesite.com</email>
                         <phone>79123456789</phone>
                         <reference>12345ABC</reference>
                         <description>notebook</description>
                         <url>http://www.somesite.com/Accept.jsp</url>
                         <parameters number="1">
                         <parameter>
                         <name>mode</name>
                         <value>1</value>
                         </parameter>
                         </parameters>
                         <signature>NzA4MjllNTczMTZlMGZjN2RlNGNlMmQ2Njc5ZDJhMjU=</signature>
                        </order>';
                
            case 'gateweb/P2PCredit':
                return '<?xml version="1.0" encoding="UTF-8"?><operation>
                        <order_id>882463681</order_id>
                        <order_state>COMPLETED</order_state>
                        <reference>d2e59cba-6f3b-470f-b048-c8a25eea237e</reference>
                        <id>1314306664</id>
                        <date>'.date('Y.m.d H:i:s').'</date>
                        <type>P2PCREDIT</type>
                        <state>APPROVED</state>
                        <reason_code>1</reason_code>
                        <message>Successful financial transaction</message>
                        <pan2>111111******1111</pan2>
                        <amount>16500</amount>
                        <fee>0</fee>
                        <currency>643</currency>
                        <approval_code>027234</approval_code>
                        <signature>Y2U3NTFhNmUwOTJiYjU4YjUxNDRlMDJkNzdkM2MwNWM=</signature>
                        </operation>';
                
            default:
                throw new \Exception( "Неизвестный метод: $method" );
        }
    }
}

