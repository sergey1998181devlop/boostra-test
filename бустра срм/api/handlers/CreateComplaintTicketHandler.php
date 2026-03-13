<?php

namespace api\handlers;

use App\Enums\TicketPriority;
use Simpla;
use App\Service\FileStorageService;
use Carbon\Carbon;

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('memory_limit', '256M');

class CreateComplaintTicketHandler extends Simpla
{
    private const SYSTEM_INITIATOR_ID = 360;
    private const CHAT_CHANNEL_ID = 2;
    private const NEW_STATUS_ID = 1;
    private const DEFAULT_SUBJECT_ID = 9;

    private FileStorageService $s3Client;

    public function __construct()
    {
        parent::__construct();
        $this->s3Client = new FileStorageService(
            $this->config->s3['endpoint'],
            $this->config->s3['region'],
            $this->config->s3_tickets['key'],
            $this->config->s3_tickets['secret'],
            $this->config->s3_tickets['Bucket']
        );
    }

    /**
     * @param array $params Параметры для создания тикета
     * @return array
     */
    public function handle(array $params): array
    {
        $phone = $params['phone_mobile'] ?? null;
        $message = $params['message'] ?? null;
        $subject_id = isset($params['subject_id']) ? (int)$params['subject_id'] : null;
        $priority_id = isset($params['priority']) ? TicketPriority::getByName($params['priority']) : TicketPriority::HIGH;
        $chat_link = $params['link'] ?? null;

        if ($chat_link) {
            $chat_link = str_replace('usedesk.com', 'usedesk.ru', $chat_link);
        }
        
        $file_links_raw = $params['attachments'] ?? null;
        $file_links = is_string($file_links_raw) ? json_decode($file_links_raw, true) : $file_links_raw;
        $file_links = is_array($file_links) ? $file_links : [];

        if (empty($phone) || empty($message)) {
            $this->log('Не заполнены обязательные поля', compact('phone', 'message'));
            return ['success' => false, 'message' => 'Не заполнены обязательные поля'];
        }

        $phone = $this->normalizePhone($phone);

        $user = $this->tickets->getUserByPhone($phone);
        if (!$user) {
            $this->log('Клиент не найден', compact('phone'));
            return ['success' => false, 'message' => 'Клиент не найден'];
        }

        $orders = $this->orders->get_orders([
            'user_id' => $user->id,
            'status' => 10,
            '1c_status' => '5.Выдан',
            'sort' => 'id_desc'
        ]);

        if (empty($orders)) {
            $this->log('Активные договоры не найдены', ['user_id' => $user->id]);
            return ['success' => false, 'message' => 'Активные договоры не найдены'];
        }

        $created_tickets = [];
        $has_active_tickets = false;

        foreach ($orders as $order) {
            // проверяем наличие активного тикета для этого заказа
            if ($this->tickets->hasActiveTicketFromBot($order->order_id, $subject_id)) {
                $this->log('Пропущено создание тикета: уже существует активный тикет', [
                    'order_id' => $order->order_id,
                    'subject_id' => $subject_id
                ]);
                $has_active_tickets = true;
                continue;
            }

            $contract = $this->contracts->get_contract_by_params(['order_id' => $order->order_id]);
            if (!$contract) {
                $this->log('Контракт не найден', ['order_id' => $order->order_id]);
                continue;
            }

            $orderHistory = $this->users->getLoanFromHistory($order, $order->order_id);
            if ($orderHistory && !empty($orderHistory->plan_close_date)) {
                $paymentDate = Carbon::parse($orderHistory->plan_close_date);
                $daysOverdue = $paymentDate->isFuture() ? 0 : $paymentDate->diffInDays(Carbon::now());
                
                if ($daysOverdue >= 30) {
                    $this->log('Пропущено создание тикета из-за большой просрочки', [
                        'order_id' => $order->order_id,
                        'days_overdue' => $daysOverdue
                    ]);
                    continue;
                }
            }

            $company_id = $this->determineCompanyId($contract->number);
            $attachments = $this->processFiles($file_links, $order->order_id);

            $ticketData = [
                'created_at' => date('Y-m-d H:i:s'),
                'status_id' => self::NEW_STATUS_ID,
                'chanel_id' => self::CHAT_CHANNEL_ID,
                'priority_id' => $priority_id,
                'order_id' => $order->order_id,
                'initiator_id' => self::SYSTEM_INITIATOR_ID,
                'company_id' => $company_id,
                'subject_id' => $subject_id ?: self::DEFAULT_SUBJECT_ID,
                'client_id' => $user->id,
                'description' => $this->formatTicketDescription($message, $chat_link, $contract->number, $attachments),
                'data' => json_encode([
                    'overdue_days' => $daysOverdue ?? 0,
                    'files' => $attachments,
                    'chat_link' => $chat_link,
                    'created_from' => 'suvvy_bot',
                    'contract_number' => $contract->number
                ], JSON_UNESCAPED_UNICODE)
            ];

            $ticketId = $this->tickets->createNewTicket($ticketData);
            if ($ticketId) {
                $created_tickets[] = $ticketId;
            }
        }

        if (empty($created_tickets)) {
            if ($has_active_tickets) {
                $this->log('Тикеты не созданы: уже есть активные тикеты', ['user_id' => $user->id]);
                return [
                    'success' => true,
                    'message' => 'Ваше обращение уже зарегистрировано и находится в работе у претензионного отдела. Специалист свяжется с вами в ближайшее время.',
                    'ticket_ids' => [],
                    'has_active_tickets' => true
                ];
            }

            $this->log('Не удалось создать тикеты', ['user_id' => $user->id]);
            return ['success' => false, 'message' => 'Не удалось создать тикеты'];
        }

        return [
            'success' => true,
            'message' => 'Тикеты успешно созданы',
            'ticket_ids' => $created_tickets
        ];
    }

    /**
     * @param $contractNumber
     * @return int
     */
    private function determineCompanyId($contractNumber): int
    {
        // Маппинг contract_prefix на id из s_organizations
        $map = [
            'A'    => 6,  // Аквариус
            'FL'   => 11,  // Финлаб
            'VZ'   => 12,  // Випзайм
            'RZS'  => 13, // РЗС
            'FR'   => 14, // Форинт
            'LD'   => 15,  // Лорд
            'BLAJ' => 16,  // Блай
        ];

        foreach ($map as $prefix => $companyId) {
            if (strpos($contractNumber, $prefix) !== false) {
                return $companyId;
            }
        }
    }

    /**
     * Обработка вложений
     * @param array $file_links
     * @param int $order_id
     * @return array
     */
    private function processFiles(array $file_links, int $order_id): array
    {
        $attachments = [];

        if (!empty($file_links)) {
            foreach ($file_links as $fileUrl) {
                $tmpFile = tempnam(sys_get_temp_dir(), 'suvvy_');
                $fileContents = @file_get_contents($fileUrl);

                if ($fileContents !== false) {
                    file_put_contents($tmpFile, $fileContents);
                    $fileName = basename(parse_url($fileUrl, PHP_URL_PATH));

                    try {
                        // загружаем файл в S3
                        $uploadKey = $this->s3Client->uploadFile([
                            'tmp_name' => $tmpFile,
                            'name' => $fileName
                        ], "tickets/{$order_id}/");

                        if ($uploadKey) {
                            $attachments[] = $uploadKey;
                        }

                    } catch (\Exception $e) {
                        file_put_contents(
                            __DIR__ . '/../../logs/debug_attachments.log',
                            date('Y-m-d H:i:s') . " ERROR: Ошибка сохранения вложения {$fileUrl} для заказа {$order_id}: " . $e->getMessage() . "\n",
                            FILE_APPEND
                        );
                    }

                    @unlink($tmpFile);
                }
            }
        }

        return $attachments;
    }

    /**
     * Форматирование описания тикета
     * @param string $message
     * @param string|null $chat_link
     * @param string $contract_number
     * @param array $attachments
     * @return string
     */
    private function formatTicketDescription(string $message, ?string $chat_link, string $contract_number, array $attachments = []): string
    {
        $description = $message;

        if ($chat_link) {
            $description .= " ссылка на чат: " . $chat_link;
        }

        $description .= " номер договора: " . $contract_number;

        if (!empty($attachments)) {
            $description .= " прикрепленные файлы:\n";
            foreach ($attachments as $attachment) {
                $description .= "- " . basename($attachment) . "\n";
            }
        }

        return $description;
    }

    /**
     * Универсальный метод логирования
     * @param string $message Сообщение для лога
     * @param array $context Дополнительные параметры
     */
    private function log(string $message, array $context = []): void
    {
        $this->logging(
            'create_complaint_ticket_error',
            'ajax/create_complaint_ticket.php',
            $context,
            ['error' => $message],
            'complaint_tickets.txt'
        );
    }

    /**
     * Преобразуем номер в формат 7XXXXXXXXXX
     * @param $phone
     * @return array|string|string[]|null
     */
    private function normalizePhone($phone)
    {
        $p = preg_replace('/\D+/', '', $phone);
        return (strlen($p) === 11 && $p[0] === '8') ? '7' . substr($p, 1) : $p;
    }
}