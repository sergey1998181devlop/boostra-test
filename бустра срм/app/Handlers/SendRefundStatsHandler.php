<?php

namespace App\Handlers;

use App\Contracts\SendRefundStatsHandlerContract;
use App\Repositories\RefundStatsRepository;
use App\Service\TelegramService;

class SendRefundStatsHandler implements SendRefundStatsHandlerContract
{
    private RefundStatsRepository $refundStats;
    private TelegramService $telegramService;
    private string $telegramChatId;
    private int $telegramMessageThreadId;

    public function __construct()
    {
        $this->refundStats = new RefundStatsRepository();
        $this->telegramService = new TelegramService();
        $this->telegramChatId = config('services.telegram.notifications_chat_id');
        $this->telegramMessageThreadId = config('services.telegram.refund_stats_thread_id');
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $now = new \DateTime();
        $isEndOfDay = $now->format('H') === '00';

        if ($isEndOfDay) {
            $fromDate = (clone $now)->modify('-1 day')->format('Y-m-d 00:00:00');
            $toDate = (clone $now)->modify('-1 day')->format('Y-m-d 23:59:59');
            $periodText = "за прошедший день";
        } else {
            $fromDate = $now->format('Y-m-d 00:00:00');
            $toDate = $now->format('Y-m-d H:i:s');
            $periodText = "за текущий день";
        }

        $stats = $this->refundStats->getRefundStats($fromDate, $toDate);
        $message = $this->prepareMessage($stats, $periodText);

        $this->sendTelegramNotification($message);
    }

    /**
     * Метод для тестового запуска с конкретной датой
     */
    public function handleTest(string $date): void
    {
        $fromDate = $date . ' 00:00:00';
        $toDate = $date . ' 23:59:59';
        $periodText = "[ТЕСТ] за " . date('d.m.Y', strtotime($date));
        $stats = $this->refundStats->getRefundStats($fromDate, $toDate);
        $message = $this->prepareMessage($stats, $periodText);
print_r($message);
        $this->sendTelegramNotification($message);
    }

    /**
     * @param array $stats
     * @param string $periodText
     * @return string
     */
    private function prepareMessage(array $stats, string $periodText): string
    {
        $message = "<b>📊 Статистика возвратов {$periodText}</b>\n\n";

        $message .= sprintf(
            "Возвращено <b>%.2f</b> р., количество <b>%d</b> штук\n\n",
            $stats['total']['amount'],
            $stats['total']['count']
        );

        foreach ($stats['by_service'] as $service => $data) {
            $message .= sprintf(
                "Из них %s - <b>%.2f</b> р., <b>%d</b> штук\n",
                $service,
                $data['amount'],
                $data['count']
            );
        }

        $message .= "\nРазбивка по процентам возврата:\n";
        $message .= sprintf("Из них 25%% - <b>%d</b> шт\n", $stats['total']['by_percent']['25']);
        $message .= sprintf("Из них 50%% - <b>%d</b> шт\n", $stats['total']['by_percent']['50']);
        $message .= sprintf("Из них 75%% - <b>%d</b> шт\n", $stats['total']['by_percent']['75']);
        $message .= sprintf("Из них 100%% - <b>%d</b> шт\n", $stats['total']['by_percent']['100']);

        $message .= "\nИнформация о выдаче:\n";
        $message .= sprintf("Выдано <b>%.2f</b> р., штук <b>%d</b>\n", $stats['order_stat']['total_sum'], $stats['order_stat']['total_orders']);
        $message .= sprintf("Из них <b>%.2f</b> р., штук <b>%d</b> ПК\n", $stats['order_stat']['regular_clients_sum'], $stats['order_stat']['regular_clients_count']);
        $message .= sprintf("Из них <b>%.2f</b> р., штук <b>%d</b> НК\n", $stats['order_stat']['new_clients_sum'], $stats['order_stat']['new_clients_count']);

        $data = $stats['current_additions_data'];
        $message .= "\nПО:\n";
        $message .= sprintf("Общее <b>%.2f</b> р., <b>%d</b> штук\n", $data['total_sum'], $data['total_count']);
        $message .= sprintf("Из них ФД <b>%.2f</b> р., <b>%d</b> штук\n", $data['credit_doctor_to_user_sum'],$data['credit_doctor_to_user_count']);
        $message .= sprintf("Из них КС <b>%.2f</b> р., <b>%d</b> штук\n",  $data['tv_medical_payments_sum'],$data['tv_medical_payments_count']);
        $message .= sprintf("Из них ВМ <b>%.2f</b> р., <b>%d</b> штук\n",  $data['multipolis_sum'],$data['multipolis_count']);
        $message .= sprintf("Из них ЗО <b>%.2f</b> р., <b>%d</b> штук\n",  $data['star_oracle_sum'],$data['star_oracle_count']);

        $data = $stats['return_percentage'];
        $message .= "\nПроцент возврата:\n";
        $message .= sprintf("Общее <b>%.2f</b>%%\n", $data['total']);
        $message .= sprintf("Из них ФД <b>%.2f</b>%%\n", $data['credit_doctor_to_user']);
        $message .= sprintf("Из них КС <b>%.2f</b>%%\n",  $data['tv_medical_payments']);
        $message .= sprintf("Из них ВМ <b>%.2f</b>%%\n",  $data['multipolis']);
        $message .= sprintf("Из них ЗО <b>%.2f</b>%%\n",  $data['star_oracle']);

        return $message;
    }

    /**
     * @param string $message
     * @return void
     */
    private function sendTelegramNotification(string $message): void
    {
        $this->telegramService->sendMessage(
            $this->telegramChatId,
            $message,
            [
                'parse_mode' => 'HTML',
                'message_thread_id' => $this->telegramMessageThreadId
            ]
        );
    }
}