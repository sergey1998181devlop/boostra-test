<?php

use App\Enums\CommentBlocks;
use App\Models\Comment;
use App\Service\CommentService;

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../api/Simpla.php';

class FetchResponsibleForCalls extends Simpla
{
    private const CHUNK_SIZE_DB   = 1000; // чанки БД
    private const CHUNK_SIZE_1C   = 100;  // пачка запроса в 1С
    private const DATE_FORMAT_1C  = 'c'; // согласно ТЗ: dateOfCall в формате ISO
    private CommentService $commentService;

    public function __construct()
    {
        parent::__construct();

        $this->commentService = new CommentService();
    }

    /**
     * @param string|null $from Начало периода (d-m-Y или Y-m-d). Если null и $to null — последние 40 минут.
     * @param string|null $to   Конец периода (d-m-Y или Y-m-d).
     */
    public function run($from = null, $to = null): void
    {
        if ($from === null || $to === null) {
            [$from, $to] = $this->makePeriod40min();
        } else {
            [$from, $to] = $this->normalizePeriod($from, $to);
        }

        logger('comment')->info('FetchResponsibleForCalls: period', ['from' => $from, 'to' => $to]);

        // Берём только те комментарии, у которых есть user_id и JSON валиден
        // и внутри JSON есть поле dateOfCall или created используем как fallback.
        $join = [];
        $columns = [
            's_comments.id',
            's_comments.user_id',
            's_comments.created',
            's_comments.text',
        ];
        $where = [
            's_comments.created[<>]' => [$from, $to],
            "s_comments.block" => [CommentBlocks::OUTGOING_CALL],
        ];

        $commentModel = new Comment();

        $processed = 0;
        // Собираем пачку на 1С по 100 записей
        $batch = [];
        $indexByUid = [];
        $commentsChunk = $commentModel->eachChunk(self::CHUNK_SIZE_DB, $columns, $where, $join);
        foreach ($commentsChunk as $row) {
            $row = (array)$row;

            if (!$row) {
                continue;
            }

            $commentId = (int)$row['id'];
            $userId    = (int)$row['user_id'];
            $created   = $row['created'];
            $text      = $row['text'] ?? '';

            if (empty($text)) {
                logger('comment')->info('Skip: empty comment text', ['comment_id' => $commentId]);
                continue;
            }

            try {
                $payload = json_decode($text, true);
                if (!is_array($payload) || empty($payload)) {
                    logger('comment')->info('Skip: invalid JSON in comment', [
                        'comment_id' => $commentId,
                        'json' => $text
                    ]);
                    continue;
                }
            } catch (Throwable $e) {
                logger('comment')->error('Skip: invalid JSON', [
                    'comment_id' => $commentId,
                    'error' => $e->getMessage(),
                    'json' => $text
                ]);
                continue;
            }

            // uid клиента — из связанной s_users. Чтобы не городить репозитории —
            // короткий запрос через старую модель.
            $uid = $this->fetchUserUid($userId);
            if (!$uid) {
                logger('comment')->info('Skip: user UID not found', ['comment_id' => $commentId, 'user_id' => $userId]);
                continue;
            }

            // дата звонка — если в JSON нет, берём created (ТЗ допускает).
            $dateOfCall = date(self::DATE_FORMAT_1C, strtotime($created));

            $batch[] = [
                'uid'        => $uid,
                'dateOfCall' => $dateOfCall,
            ];
            // Индексация для быстрого маппинга ответа 1С -> комментарий
            $indexByUid[$uid][] = [
                'comment_id' => $commentId,
                'payload'    => $payload,
            ];
        }

        $chunkFor1C = array_chunk($batch, self::CHUNK_SIZE_1C);
        foreach ($chunkFor1C as $chunkIndex => $chunk) {
            logger('comment')->info('FetchResponsibleForCalls: processing chunk', [
                'chunk_index' => $chunkIndex + 1,
                'chunk_size' => count($chunk)
            ]);

            try {
                $response = $this->commentService->getResponsibleFrom1C($chunk);

                if (!empty($response['error'])) {
                    logger('comment')->error('1C response error', [
                        'error' => $response['message'] ?? 'unknown',
                        'chunk' => $chunk
                    ]);
                    continue;
                }

                logger('comment')->info('1C response received', ['count' => count($response) ?? 0]);
            } catch (Throwable $e) {
                logger('comment')->error('1C request failed: ' . $e->getMessage(), [
                    'chunk' => $chunk
                ]);
                continue;
            }

            // Ответ ожидается массивом массивов формата { uid, dateOfCall, employee }
            if (!is_array($response)) {
                logger('comment')->error('1C response invalid type', ['type' => gettype($response)]);
                continue;
            }

            // Обновляем operator_name по uid
            foreach ($response as $item) {
                $uid      = $item['uid']        ?? ($item->uid        ?? null);
                $employee = $item['employee']   ?? ($item->employee   ?? null);

                if (!$uid || !$employee || empty($indexByUid[$uid])) {
                    logger('comment')->debug('FetchResponsibleForCalls: skipping item', [
                        'uid' => $uid ?? 'null',
                        'employee' => $employee ?? 'null',
                        'has_uid_in_index' => !empty($indexByUid[$uid]) ? 'yes' : 'no',
                    ]);
                    continue;
                }

                foreach ($indexByUid[$uid] as $bind) {
                    $commentId = $bind['comment_id'];
                    $payload   = $bind['payload'];

                    // Всегда обновляем operator_name данными из 1С (приоритет у 1С)
                    $payload['operator_name'] = $employee;

                    try {
                        // Обновляем JSON целиком
                        (new Comment())->update([
                            'text' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        ], ['id' => $commentId]);

                        logger('comment')->info('Comment updated with operator_name', [
                            'comment_id' => $commentId,
                            'operator_name' => $employee,
                        ]);
                    } catch (Throwable $e) {
                        logger('comment')->error('DB update failed: ' . $e->getMessage(), [
                            'comment_id' => $commentId,
                        ]);
                    }
                }
            }

            $processed += count($chunk);

            logger('comment')->info('Chunk processed', [
                'chunk_index' => $chunkIndex + 1,
                'processed_total' => $processed
            ]);
        }
    }

    private function makePeriod40min(): array
    {
        try {
            $to   = new DateTimeImmutable('now');
            $from = $to->sub(new DateInterval('PT40M'));
            return [
                $from->format('Y-m-d H:i:s'),
                $to->format('Y-m-d H:i:s'),
            ];
        } catch (Throwable $e) {
            logger('comment')->error('makePeriod40min failed: ' . $e->getMessage());
            $to = date('Y-m-d H:i:s');
            $from = date('Y-m-d H:i:s', strtotime('-40 minutes'));
            return [$from, $to];
        }
    }

    /**
     * Нормализует переданные даты в Y-m-d H:i:s (from — начало дня, to — конец дня).
     * Поддерживает форматы d-m-Y (28-01-2026) и Y-m-d.
     *
     * @param string $from
     * @param string $to
     * @return array{0: string, 1: string}
     */
    private function normalizePeriod($from, $to): array
    {
        $formats = ['d-m-Y', 'Y-m-d', 'd.m.Y', 'Y-m-d H:i:s'];
        $parse = function ($dateStr) use ($formats) {
            $dateStr = trim($dateStr);
            foreach ($formats as $fmt) {
                $dt = \DateTimeImmutable::createFromFormat($fmt, $dateStr);
                if ($dt !== false) {
                    return $dt;
                }
            }
            $ts = strtotime($dateStr);
            if ($ts !== false) {
                return (new \DateTimeImmutable())->setTimestamp($ts);
            }
            throw new \InvalidArgumentException('Не правльная дата: ' . $dateStr);
        };

        $fromDt = $parse($from)->setTime(0, 0, 0);
        $toDt   = $parse($to)->setTime(23, 59, 59);
        if ($fromDt > $toDt) {
            throw new \InvalidArgumentException('Дата "from" не может быть позже "to".');
        }

        return [
            $fromDt->format('Y-m-d H:i:s'),
            $toDt->format('Y-m-d H:i:s'),
        ];
    }

    private function fetchUserUid(int $userId): ?string
    {
        logger('comment')->debug('fetchUserUid: start', ['user_id' => $userId]);

        try {
            $users = new \Users();
            $uid = $users->getUserUidById($userId);

            if (empty($uid)) {
                logger('comment')->info('UID not found for user', [
                    'user_id' => $userId,
                ]);
                return null;
            }

            logger('comment')->debug('fetchUserUid: success', [
                'user_id' => $userId,
                'uid' => $uid
            ]);

            return (string)$uid;
        } catch (\Throwable $e) {
            logger('comment')->error('fetchUserUid failed: ' . $e->getMessage(), [
                'user_id' => $userId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}

$from = isset($argv[1]) ? $argv[1] : null;
$to   = isset($argv[2]) ? $argv[2] : null;
try {
    (new FetchResponsibleForCalls())->run($from, $to);
} catch (\InvalidArgumentException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}