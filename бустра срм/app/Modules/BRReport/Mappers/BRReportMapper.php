<?php

namespace App\Modules\BRReport\Mappers;

use App\Modules\BRReport\Dto\BRReportItemDto;
use Carbon\Carbon;

/**
 * Маппер для преобразования данных отчета БР
 *
 * Содержит бизнес-логику маппинга внутренних значений в категории ЦБ РФ
 */
class BRReportMapper
{
    /**
     * Маппинг внутренних каналов на категории ЦБ РФ
     */
    private const CHANNEL_MAPPING = [
        'Телефония' => 'Посредством телефонной связи',
        'Чат' => 'Путем переписки в чате поддержки в личном кабинете/мобильном приложении',
        'Эл. Почта' => 'По электронной почте',
        'Почта' => 'По почте',
        'Форумы' => 'Форма обратной связи на официальном сайте организации в сети «Интернет»',
        'Банк России' => 'Банк России',
    ];

    /**
     * Маппинг внутренних тем жалоб на категории ЦБ РФ
     */
    private const SUBJECT_MAPPING = [
        // ФЗ № 230-ФЗ
        'Угрозы' => 'Вопросы, связанные с Федеральным законом Российской Федерации № 230-ФЗ',
        'Угрозы третьим лицам' => 'Вопросы, связанные с Федеральным законом Российской Федерации № 230-ФЗ',
        'Агенты' => 'Вопросы, связанные с Федеральным законом Российской Федерации № 230-ФЗ',
        'Бомбер' => 'Вопросы, связанные с Федеральным законом Российской Федерации № 230-ФЗ',
        'Робот' => 'Вопросы, связанные с Федеральным законом Российской Федерации № 230-ФЗ',
        'Взыскание' => 'Вопросы, связанные с Федеральным законом Российской Федерации № 230-ФЗ',

        // Несогласие с размером задолженности
        'Перерасчет' => 'Несогласие с размером задолженности',

        // Несогласие с условиями договора
        'Выдача без АСП в СМСЦ' => 'Несогласие с условиями договора',
        'Автовыдача' => 'Несогласие с условиями договора',

        // Мошеннические действия
        'Мошенничество' => 'Мошеннические действия',

        // Списание без согласия
        'Рекурренты' => 'Списание денежных средств без согласия клиента',

        // Проблемы с погашением
        'Тех. Вопросы' => 'Проблемы с погашением (кроме несогласия с размером задолженности)',

        // Кредитные каникулы
        'Кредитные каникулы' => 'Вопросы предоставления кредитных каникул',

        // Кредитные истории
        'БКИ' => 'Вопросы, связанные с кредитными историями',

        // Неудовлетворительное рассмотрение
        'ОС Почта' => 'Неудовлетворительное рассмотрение обращений (жалоб) клиента',

        // Возврат денежных средств за дополнительные услуги
        'Доп. Услуги' => 'Возврат денежных средств за дополнительные услуги',
        'Программное обеспечение' => 'Возврат денежных средств за дополнительные услуги',
        'Допы и прочее' => 'Возврат денежных средств за дополнительные услуги',
        'ШКД' => 'Возврат денежных средств за дополнительные услуги',
        'Возврат допа НЕ на карту клиента' => 'Возврат денежных средств за дополнительные услуги',

        // Иное
        'Иное' => 'Иное',
        'Закрытие договора' => 'Иное',
        'ОС' => 'Иное',
        'ОС ' => 'Иное',
        'Проблема при выдаче займа' => 'Иное',
        'Инстолмент (кроме закрытия)' => 'Иное',
        'Закрытие ИЛ' => 'Иное',
        'Закрытие по скидке от ОВЗ' => 'Иное',
        'Тех. поддержка' => 'Иное',
        'Подача тикета' => 'Иное',
    ];

    /**
     * Маппинг статусов в решения ЦБ РФ
     */
    private const STATUS_TO_DECISION = [
        // Жалоба не рассмотрена
        'Новая' => 'Жалоба не рассмотрена',
        'Ожидание' => 'Жалоба не рассмотрена',
        'В работе' => 'Жалоба не рассмотрена',
        'Отозвана клиентом' => 'Жалоба не рассмотрена',

        // Жалоба не удовлетворена
        'Не урегулирован' => 'Жалоба не удовлетворена',
        'Спорная жалоба' => 'Жалоба не удовлетворена',

        // Жалоба удовлетворена
        'Урегулирован' => 'Жалоба удовлетворена',
        'Достигнуты договоренности' => 'Жалоба удовлетворена',
    ];

    /**
     * Маппинг статусов в основания решения (п.11 требований ЦБ РФ)
     */
    private const STATUS_TO_BASIS = [
        // Срок рассмотрения не наступил
        'Новая' => 'срок рассмотрения не наступил',
        'Ожидание' => 'срок рассмотрения не наступил',
        'В работе' => 'срок рассмотрения не наступил',

        // Отзыв жалобы её заявителем
        'Отозвана клиентом' => 'отзыв жалобы её заявителем',

        // Отсутствие оснований для удовлетворения
        'Не урегулирован' => 'отсутствие оснований для удовлетворения',
        'Спорная жалоба' => 'отсутствие оснований для удовлетворения',

        // Клиентоориентированная политика
        'Урегулирован' => 'клиентоориентированная политика',
        'Достигнуты договоренности' => 'клиентоориентированная политика',
    ];

    /**
     * Конфигурация колонок отчета
     */
    public const REPORT_COLUMNS = [
        ['key' => 'id', 'label' => 'Номер жалобы', 'sort_key' => 'id'],
        ['key' => 'created_at', 'label' => 'Дата регистрации жалобы', 'sort_key' => 'created'],
        ['key' => 'client_name', 'label' => 'ФИО клиента', 'sort_key' => 'client'],
        ['key' => 'company_name', 'label' => 'Компания', 'sort_key' => 'company'],
        ['key' => 'type_activity', 'label' => 'Вид деятельности'],
        ['key' => 'type_product', 'label' => 'Вид продукта'],
        ['key' => 'subject_name', 'label' => 'Тематика жалобы'],
        ['key' => 'event_period', 'label' => 'Период события'],
        ['key' => 'channel_name', 'label' => 'Канал поступления жалобы'],
        ['key' => 'take_decision', 'label' => 'Принятое решение по жалобе'],
        ['key' => 'basis_decision', 'label' => 'Основание принятия решения по жалобе'],
        ['key' => 'scope_consideration', 'label' => 'Жалоба относится к сфере рассмотрения финансовым уполномоченным'],
        ['key' => 'cbr_letter_number', 'label' => 'Номер письма Банка России'],
    ];

    /**
     * Преобразовать внутреннюю тему в категорию ЦБ РФ
     *
     * @param string $subjectName
     * @return string
     */
    public function mapSubjectToBRCategory(string $subjectName): string
    {
        return self::SUBJECT_MAPPING[$subjectName] ?? '';
    }

    /**
     * Преобразовать внутренний канал в категорию ЦБ РФ
     *
     * @param string $channelName
     * @return string
     */
    public function mapChannelToBRCategory(string $channelName): string
    {
        return self::CHANNEL_MAPPING[$channelName] ?? 'Иное';
    }

    /**
     * Преобразовать статус в решение ЦБ РФ
     *
     * @param string $statusName
     * @return string
     */
    public function mapStatusToBRDecision(string $statusName): string
    {
        return self::STATUS_TO_DECISION[$statusName] ?? '';
    }

    /**
     * Определить основание решения по статусу
     *
     * @param string $statusName
     * @return string
     */
    public function getBasisReason(string $statusName): string
    {
        return self::STATUS_TO_BASIS[$statusName] ?? 'иное';
    }

    /**
     * Форматировать период события как квартал
     *
     * @param string|null $createdAt Дата создания тикета
     * @return string Квартал в формате "I", "II", "III", "IV"
     */
    public function formatEventPeriod(?string $createdAt): string
    {
        if (empty($createdAt)) {
            return '';
        }

        $date = Carbon::parse($createdAt);
        $quarter = $date->quarter;

        $romanNumerals = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];

        return $romanNumerals[$quarter] ?? '';
    }

    /**
     * Преобразовать сырую строку из БД в DTO
     *
     * @param object $row
     * @return BRReportItemDto
     */
    public function mapRowToDto(object $row): BRReportItemDto
    {
        $channelName = $row->channel_name ?? '';
        $isBankOfRussia = $channelName === 'Банк России';

        return new BRReportItemDto(
            (int)($row->id ?? 0),
            $this->formatDate($row->created_at ?? ''),
            isset($row->client_id) ? (int)$row->client_id : null,
            $row->client_name ?? '',
            $row->company_name ?? '',
            $row->type_activity ?? '',
            $row->type_product ?? '',
            $this->mapSubjectToBRCategory($row->subject_name ?? ''),
            $this->formatEventPeriod($row->created_at ?? null),
            $this->mapChannelToBRCategory($channelName),
            $this->mapStatusToBRDecision($row->take_decision ?? ''),
            $this->getBasisReason($row->basis_decision ?? ''),
            $row->scope_consideration ?? '',
            $isBankOfRussia ? ($row->source ?? '') : ''
        );
    }

    /**
     * Преобразовать массив строк в массив DTO
     *
     * @param array $rows
     * @return BRReportItemDto[]
     */
    public function mapRowsToDtos(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRowToDto($row);
        }
        return $result;
    }

    /**
     * Получить значение колонки для отображения
     *
     * @param BRReportItemDto $item
     * @param string $key
     * @return string
     */
    public function getColumnValue(BRReportItemDto $item, string $key): string
    {
        switch ($key) {
            case 'id':
                return (string)$item->getId();
            case 'created_at':
                return $item->getCreatedAt();
            case 'client_name':
                return $item->getClientName();
            case 'company_name':
                return $item->getCompanyName();
            case 'type_activity':
                return $item->getTypeActivity();
            case 'type_product':
                return $item->getTypeProduct();
            case 'subject_name':
                return $item->getSubjectName();
            case 'event_period':
                return $item->getEventPeriod();
            case 'channel_name':
                return $item->getChannelName();
            case 'take_decision':
                return $item->getTakeDecision();
            case 'basis_decision':
                return $item->getBasisDecision();
            case 'scope_consideration':
                return $item->getScopeConsideration();
            case 'cbr_letter_number':
                return $item->getCbrLetterNumber();
            default:
                return '';
        }
    }

    /**
     * Подготовить строки отчета для отображения
     *
     * @param BRReportItemDto[] $items
     * @return array
     */
    public function prepareReportRows(array $items): array
    {
        $rows = [];
        foreach ($items as $item) {
            $row = [];
            foreach (self::REPORT_COLUMNS as $col) {
                $row[$col['key']] = $this->getColumnValue($item, $col['key']);
            }
            $row['client_id'] = $item->getClientId();
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Получить заголовки отчета
     *
     * @return array
     */
    public function getReportHeaders(): array
    {
        $headers = [];
        foreach (self::REPORT_COLUMNS as $col) {
            $headers[] = [
                'key' => $col['key'],
                'label' => $col['label'],
                'sort_key' => $col['sort_key'] ?? null,
            ];
        }
        return $headers;
    }

    /**
     * Форматировать дату
     *
     * @param string $date
     * @return string
     */
    private function formatDate(string $date): string
    {
        if (empty($date)) {
            return '';
        }
        return date('d.m.Y H:i', strtotime($date));
    }
}
