<?php

namespace App\Service;

use App\Core\Application\Application;

class AIBotNotificationService
{
    private TelegramService $telegramService;
    private string $chatId;
    private string $threadId;
    private string $backUrl;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
        $this->chatId = config('services.telegram.notifications_chat_id');
        $this->threadId = config('services.telegram.ai_bot_thread_id');
        $this->backUrl = config('services.app.back_url');
    }

    /**
     * Отправка уведомления о переводе звонка на оператора
     */
    public function sendTransferNotification(array $userData, array $callData): void
    {
        $message = $this->prepareMessage($userData, $callData);

        $options = [
            'parse_mode' => 'HTML',
        ];
        
        if (!empty($this->threadId)) {
            $options['message_thread_id'] = $this->threadId;
        }

        $this->telegramService->sendMessage($this->chatId, $message, $options);

        $audioUrl = $this->resolveAudioUrl($callData);
        if ($audioUrl) {
            $audioOptions = [];
            if (!empty($this->threadId)) {
                $audioOptions['message_thread_id'] = $this->threadId;
            }
            $this->telegramService->sendAudio($this->chatId, $audioUrl, $audioOptions);
        }
    }

    /**
     * Подготовка сообщения для отправки
     */
    private function prepareMessage(array $userData, array $callData): string
    {
        $fullName = $this->getUserFullName($userData);
        $phone = $callData['msisdn'] ?? 'Не указан';
        $duration = $this->formatDuration($callData['bot_duration'] ?? 0);
        $transcript = $this->truncateTranscript($callData['call_transcript'] ?? '');
        $clientLink = $this->backUrl . '/client/' . ($userData['id'] ?? '');

        return sprintf(
            "🤖 <b>ИИ бот не смог обработать звонок</b>\n\n" .
            "<b>Клиент:</b> <a href='%s'>%s</a>\n" .
            "<b>Телефон:</b> %s\n" .
            "<b>Длительность с ботом:</b> %s\n" .
            "<b>Транскрипция:</b> %s\n\n" .
            "<b>Переведен на оператора</b>",
            $clientLink,
            $fullName,
            $phone,
            $duration,
            $transcript
        );
    }

    private function resolveAudioUrl(array $callData): ?string
    {
        $key = (string)($callData['call_record'] ?? '');
        if ($key === '') return null;
        try {
            $app = Application::getInstance();
            /** @var FileStorageFactory $fsFactory */
            $fsFactory = $app->make(FileStorageFactory::class);
            $storage = $fsFactory->make('call_records');
            return $storage->getPublicUrl($key);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Получение полного имени пользователя
     */
    private function getUserFullName(array $userData): string
    {
        if (isset($userData['firstname']) || isset($userData['lastname']) || isset($userData['patronymic'])) {
            return trim(
                ($userData['lastname'] ?? '') . ' ' . 
                ($userData['firstname'] ?? '') . ' ' . 
                ($userData['patronymic'] ?? '')
            );
        }
        
        return 'Не указано';
    }

    /**
     * Форматирование длительности
     */
    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' сек';
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        return $minutes . ' мин ' . $remainingSeconds . ' сек';
    }

    /**
     * Обрезка транскрипции для уведомления
     */
    private function truncateTranscript(string $transcript): string
    {
        if (empty($transcript)) {
            return 'Не указана';
        }
        
        // Ограничиваем длину транскрипции
        if (mb_strlen($transcript) > 200) {
            return mb_substr($transcript, 0, 200) . '...';
        }
        
        return $transcript;
    }
}
