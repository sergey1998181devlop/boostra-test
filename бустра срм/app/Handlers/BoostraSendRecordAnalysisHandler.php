<?php

namespace App\Handlers;

use App\Contracts\SendRecordAnalysisHandlerContract;
use App\Enums\CommentBlocks;
use App\Models\Comment;
use App\Models\User;
use App\Models\UserPhone;
use App\Models\VoxUser;
use App\Service\FileStorageService;
use App\Service\ObuchatService;
use App\Service\TinkoffTqmService;
use Exception;
use getID3;

class BoostraSendRecordAnalysisHandler implements SendRecordAnalysisHandlerContract
{
    private FileStorageService $storageService;
    private ObuchatService $obuchatService;
    private TinkoffTqmService $tinkoffTqmService;

    public function __construct()
    {
        $this->storageService = new FileStorageService(
            config('services.record_storage.url'),
            config('services.record_storage.region'),
            config('services.record_storage.access_key'),
            config('services.record_storage.secret_key'),
            config('services.record_storage.call_bucket')
        );

        $this->obuchatService = new ObuchatService();
        $this->tinkoffTqmService = new TinkoffTqmService();
    }

    public function handle(array $comment): bool
    {
        try {
            $call = json_decode($comment['text'], true);
            if (empty($call)) {
                logger('mango')->info('BoostraSendRecordAnalysisHandler::handle - sendDataToObuchat: '. "ID: " . $comment['id'] . " комментарий не содержит данные звонка");
                throw new Exception("ID: " . $comment['id'] . " комментарий не содержит данные звонка");
            }

            if ($this->shouldSkipCall($comment, $call)) {
                logger('mango')->info('BoostraSendRecordAnalysisHandler::handle - sendDataToObuchat: '. "Пропуск звонка для анализа. Комментарий ID: " . $comment['id']);
                error_log("Пропуск звонка для анализа. Комментарий ID: " . $comment['id']);
                return false;
            }

            $user = $this->getUser($comment['user_id']);
            if (empty($user)) {
                logger('mango')->info('BoostraSendRecordAnalysisHandler::handle - sendDataToObuchat: '. "Пользователь не найден. Комментарий ID: " . $comment['id']);
                error_log("Пользователь не найден. Комментарий ID: " . $comment['id']);
                return false;
            }

            $record = $this->downloadRecord($call['record_url']);
            if (empty($record)) {
                logger('mango')->info('BoostraSendRecordAnalysisHandler::handle - sendDataToObuchat: '. "Не удалось скачать запись звонка. Комментарий ID: " . $comment['id']);
                error_log("Не удалось скачать запись звонка. Комментарий ID: " . $comment['id']);
                return false;
            }

            $duration = $this->analyzeRecordDuration($record);
            $call['record_duration'] = $duration;

            $this->sendToObuchat($comment, $call, $user);

            // Отправляем в TQM только если оператор включен для анализа
            if ($this->isOperatorEnabled($call)) {
                $this->sendToTinkoff($comment, $call, $user);
            }

            $call['is_sent_analysis'] = true;

            $this->updateComment($comment['id'], $call);

            logger('mango')->info('BoostraSendRecordAnalysisHandler::handle - sendDataToObuchat: ' . "Запись отправлена на анализ. Комментарий ID: " . $comment['id']);

            return true;
        } catch (Exception $e) {
            logger('mango')->error('BoostraSendRecordAnalysisHandler::handle - sendDataToObuchat: '. "Ошибка при отправке записи на анализ ИИ: " . $e->getMessage());
            error_log("Ошибка при отправке записи на анализ ИИ: " . $e->getMessage());
            return false;
        }
    }

    private function shouldSkipCall(array $comment, array $call): bool
    {
        if (empty($call['record_url'])) {
            return true;
        }

        if ($call['is_sent_analysis'] === true) {
            return true;
        }

        if (empty($call['operator_tag']) && $comment['block'] === CommentBlocks::INCOMING_CALL) {
            $createdTimestamp = strtotime($comment['created']);
            if ((time() - $createdTimestamp) < 1800) {
                return true;
            }
        }

        return false;
    }

    private function sendToObuchat(array $comment, array $call, array $user): void
    {
        $data = $this->prepareObuchatPayload($comment, $call, $user);

        in_array($comment['block'], [CommentBlocks::INCOMING_CALL, CommentBlocks::FROMTECH_INCOMING_CALL])
            ? $this->obuchatService->sendIncomingRecordForRating($data)
            : $this->obuchatService->sendOutgoingRecordForRating($data);
    }

    private function sendToTinkoff(array $comment, array $call, array $user): void
    {
        $data = $this->prepareTqmPayload($comment, $call, $user);

        $this->tinkoffTqmService->sendCall($data);
    }

    private function prepareObuchatPayload(array $comment, array $call, array $user): array
    {
        // Определяем телефон клиента в зависимости от направления и доступных полей
        if (!empty($call['client_phone'])) {
            $clientPhone = $call['client_phone'];
        } elseif (!empty($call['is_incoming'])) {
            // Для входящих: клиент, как правило, в phone_a
            $clientPhone = $call['phone_a'] ?? ($user['phone_mobile'] ?? null);
        } else {
            // Для исходящих: клиент обычно в phone_b
            $clientPhone = $call['phone_b'] ?? ($user['phone_mobile'] ?? null);
        }

        return [
            'comment_id' => $comment['id'],
            'created_at' => $comment['created'],
            'record_url' => $call['record_url'],
            'operator_id' => $call['operator_id'] ?? null,
            'operator_name' => $call['operator_name'] ?? '',
            'operator_tag' => $call['operator_tag'] ?? '',
            'tag' => $call['tag'] ?? '',
            'user_id' => $user['id'],
            'user_name' => $user['lastname'] . ' ' . $user['firstname'] . ' ' . $user['patronymic'],
            'user_phone' => $clientPhone ? (string)$clientPhone : '',
            'assessment' => $call['assessment'] ?? '',
            'provider' => $call['provider'] ?? '',
        ];
    }

    private function getUser(int $id)
    {
        return (new User)->get(['id', 'firstname', 'lastname', 'patronymic', 'phone_mobile'], ['id' => $id])->getData();
    }

    private function downloadRecord(string $recordUrl): string
    {
        return $this->storageService->downloadFileByUrl($recordUrl);
    }

    private function updateComment(int $id, array $call): void
    {
        (new Comment)->update(['text' => json_encode($call, JSON_UNESCAPED_UNICODE)], ['id' => $id]);
    }

    private function analyzeRecordDuration(string $record): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
        file_put_contents($tempFile, $record);

        $getID3 = new getID3();
        $fileInfo = $getID3->analyze($tempFile);

        unlink($tempFile);

        if (!empty($fileInfo['playtime_seconds'])) {
            $seconds = (float)$fileInfo['playtime_seconds'];
            $minutes = floor($seconds / 60);
            $remainingSeconds = floor($seconds % 60);
            return sprintf("%d:%02d", $minutes, $remainingSeconds);
        }

        return '0:00';
    }

    private function prepareTqmPayload(array $comment, array $call, array $user): array
    {
        $createdTimestamp = strtotime($comment['created'] ?? '') ?: time();
        $defaultStart = date('Y-m-d\TH:i:s', $createdTimestamp);

        $startDate = $call['tqm_start_date'] ?? $defaultStart;
        $endDate = $call['tqm_end_date'] ?? $startDate;

        $direction = 'outbound';
        if (!empty($call['is_incoming'])) {
            $direction = 'inbound';
        }

        // Определяем телефон клиента в зависимости от направления и доступных полей
        if (!empty($call['client_phone'])) {
            $clientPhone = $call['client_phone'];
        } elseif (!empty($call['is_incoming'])) {
            // Для входящих: клиент, как правило, в phone_a
            $clientPhone = $call['phone_a'] ?? ($user['phone_mobile'] ?? null);
        } else {
            // Для исходящих: клиент обычно в phone_b
            $clientPhone = $call['phone_b'] ?? ($user['phone_mobile'] ?? null);
        }

        $payload = [
            'fileLink' => $call['record_url'] ?? '',
            'id' => (string)($call['call_id'] ?? $comment['id']),
            'callDirection' => $direction,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'operatorId' => (string)($call['operator_id'] ?? ''),
            'clientPhoneNumber' => $clientPhone ? (string)$clientPhone : '',
            'languageCode' => $call['language_code'] ?? 'ru',
            'taskType' => $call['operator_tag'] ?? ($call['task_type'] ?? 'boostra_call'),
        ];

        if (!empty($call['hangup_party'])) {
            $payload['hangupParty'] = $call['hangup_party'];
        } else {
            $payload['hangupParty'] = $this->resolveHangupParty($call);
        }

        if (!empty($call['operator_channel'])) {
            $payload['operatorChannel'] = $call['operator_channel'];
        } else {
            $payload['operatorChannel'] = $this->resolveOperatorChannel($call);
        }

        // reason: ссылка на ЛК клиента
        $payload['reason'] = config('services.app.back_url') . '/client/' . $comment['user_id'];

        $stage = $call['stage'] ?? '';
        $assessment = $call['assessment'] ?? '';
        $result = trim($stage . ' ' . $assessment);
        if ($result !== '') {
            $payload['result'] = $result;
        }

        return $payload;
    }

    private function resolveHangupParty(array $call): string
    {
        $code = $call['completion_code'] ?? '';

        if ($code === 'Call_Answered') {
            return 'client';
        }

        if (in_array($code, ['No_Answer', 'Busy', 'Rejected'], true)) {
            return 'timeout';
        }

        if (!empty($call['is_incoming'])) {
            return 'operator';
        }

        return 'server';
    }

    /**
     * Проверяет, включен ли оператор для отправки звонков на анализ в TQM
     *
     * @param array $call Массив данных звонка
     * @return bool
     */
    private function isOperatorEnabled(array $call): bool
    {
        $voxUserId = $call['operator_id'] ?? null;

        if (empty($voxUserId)) {
            return false;
        }

        $voxUser = new VoxUser();
        return $voxUser->isEnabledForCallAnalysis((int)$voxUserId);
    }

    /**
     * Определяет аудиоканал оператора (left=phone_a, right=phone_b).
     *
     * Приоритет:
     * 1. Сопоставление local_number с phone_a/phone_b
     * 2. Поиск клиентского номера в БД (s_users, s_user_phones)
     * 3. Эвристика по направлению звонка
     *
     * @param array $call
     * @return string 'left' или 'right'
     */
    private function resolveOperatorChannel(array $call): string
    {
        $phoneA = $call['phone_a'] ?? null;
        $phoneB = $call['phone_b'] ?? null;
        $localNumber = $call['local_number'] ?? null;

        // Шаг 1: сопоставление local_number (номер оператора) с phone_a / phone_b
        if ($localNumber !== null && ($phoneA !== null || $phoneB !== null)) {
            $normalizedLocal = $this->normalizePhoneForComparison($localNumber);

            if ($phoneA !== null && $normalizedLocal === $this->normalizePhoneForComparison($phoneA)) {
                return 'left';
            }
            if ($phoneB !== null && $normalizedLocal === $this->normalizePhoneForComparison($phoneB)) {
                return 'right';
            }
        }

        // Шаг 2: поиск клиентского номера в БД
        if ($phoneA !== null && $phoneB !== null) {
            $phoneAIsClient = $this->isClientPhone($phoneA);
            $phoneBIsClient = $this->isClientPhone($phoneB);

            if ($phoneAIsClient && !$phoneBIsClient) {
                return 'right';
            }
            if ($phoneBIsClient && !$phoneAIsClient) {
                return 'left';
            }
        }

        // Шаг 3: fallback — эвристика по направлению звонка
        $isIncoming = !empty($call['is_incoming']);
        return $isIncoming ? 'left' : 'right';
    }

    /**
     * Нормализует номер телефона до цифр для сравнения.
     *
     * @param string $phone
     * @return string
     */
    private function normalizePhoneForComparison(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Проверяет, принадлежит ли номер клиенту (s_users.phone_mobile или s_user_phones.phone).
     *
     * @param string $phone
     * @return bool
     */
    private function isClientPhone(string $phone): bool
    {
        $normalized = formatPhoneNumber($phone);
        if ($normalized === false) {
            return false;
        }

        $userExists = (new User())->has(['phone_mobile' => $normalized])->getData();
        if ($userExists) {
            return true;
        }

        $userPhoneExists = (new UserPhone())->has([
            'phone' => $normalized,
            'is_active' => 1,
        ])->getData();

        return (bool)$userPhoneExists;
    }
}
