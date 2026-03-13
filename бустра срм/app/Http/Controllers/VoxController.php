<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Core\Database\BaseDatabase;
use App\Dto\VoxRobotCallsDto;
use App\Modules\VoxCallsArchive\Application\Service\VoxCallsArchiveService;
use App\Repositories\VoximplantRepository;
use App\Service\CheckFailService;
use App\Service\VoximplantService;

class VoxController
{
    private VoximplantService $voxImplantService;

    public function __construct()
    {
        $this->voximplantRepository = new VoximplantRepository();
    }
    public function checkFailServices(): Response
    {
        $checkFailService = new CheckFailService();
        $checkFailResultDto = $checkFailService->check();

        return response()->json([
            'has_error' => $checkFailResultDto->hasError,
            'message' => $checkFailResultDto->message,
            'show_at' => $checkFailResultDto->showAt,
            'is_active' => $checkFailResultDto->isActive
        ]);
    }

    public function updateRobotCall(Request $request): Response
    {
        $dto = VoxRobotCallsDto::fromRequest($request->input());

        $affected_rows = $this->voximplantRepository->updateVoxRobotCalls($dto);

        if ((int)$affected_rows == 0) {
            return response()->json([
                'error' => 'No records updated for phone: ' . $dto->phone,
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'phone' => $dto->phone,
            'status' => $dto->status,
            'is_redirected_manager' => $dto->is_redirected_manager,
            'type' => $dto->type
        ], Response::HTTP_OK);
    }

    /**
     * Ручной импорт звонков операторов за указанный период
     * Повторяет логику importCalls() из cron/vox_sync_calls_operators_report.php
     *
     * @param Request $request date_from, date_to (формат Y-m-d H:i:s или Y-m-d)
     * @return Response
     */
    public function importCallsReport(Request $request): Response
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (empty($dateFrom) || empty($dateTo)) {
            return response()->json([
                'error' => 'Параметры date_from и date_to обязательны',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Дополняем время если указана только дата
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $dateFrom .= ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $dateTo .= ' 00:00:00';
        }

        try {
            // Получаем ID очередей, включённых для отчёта
            $db = BaseDatabase::singleton()->db();
            $queueIds = $db->select('s_vox_queues', 'vox_queue_id', ['enabled_for_report' => 1]);
            $queueIds = array_map('intval', $queueIds);

            if (empty($queueIds)) {
                return response()->json([
                    'error' => 'Нет очередей с enabled_for_report = 1',
                ], Response::HTTP_BAD_REQUEST);
            }

            $voxService = new VoximplantService();
            $archiveService = new VoxCallsArchiveService();

            $page = 1;
            $perPage = 50;
            $pageCount = 1;
            $created = 0;
            $updated = 0;
            $skippedNoUser = 0;
            $totalProcessed = 0;
            $failedRequests = 0;

            do {
                $response = $voxService->searchCallsPaginated($dateFrom, $dateTo, $page, $perPage, [
                    'with_tags' => true,
                    'queue_ids' => json_encode($queueIds),
                    'sort' => 'id',
                ]);

                if (empty($response['success']) || empty($response['result']) || !is_array($response['result'])) {
                    if ($page === 1 && empty($response['result'])) {
                        break;
                    }
                    $failedRequests++;
                    break;
                }

                $pageCount = isset($response['_meta']['pageCount']) ? (int)$response['_meta']['pageCount'] : 1;

                foreach ($response['result'] as $call) {
                    if (!is_array($call)) {
                        continue;
                    }

                    if (empty($call['user_id'])) {
                        $skippedNoUser++;
                        continue;
                    }

                    $voxCallId = (int)$call['id'];

                    if ($archiveService->existsByVoxCallId($voxCallId)) {
                        $callData = json_decode($call['call_data'] ?? '{}', true);
                        $metaData = $call;
                        if (isset($callData['assessment'])) {
                            $metaData['assessment'] = $callData['assessment'];
                        }
                        $archiveService->updateReportMeta($metaData);
                        $updated++;
                    } else {
                        $archiveService->saveFromArray($this->mapVoxCallToArray($call));
                        $created++;
                    }

                    $totalProcessed++;
                }

                $page++;
            } while ($page <= $pageCount);

            return response()->json([
                'success' => true,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'queues_count' => count($queueIds),
                'total_processed' => $totalProcessed,
                'created' => $created,
                'updated' => $updated,
                'skipped_no_user' => $skippedNoUser,
                'failed_requests' => $failedRequests,
            ]);
        } catch (\Exception $e) {
            log_exception($e, 'Ошибка импорта звонков');
            return response()->json([
                'error' => 'Ошибка импорта: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Преобразует звонок из Vox API в формат для VoxCallDTO::fromArray()
     *
     * @param array $call
     * @return array
     */
    private function mapVoxCallToArray(array $call): array
    {
        $callData = json_decode($call['call_data'] ?? '{}', true);

        $phoneA = formatPhoneNumber($call['phone_a'] ?? '');
        $phoneB = formatPhoneNumber($call['phone_b'] ?? '');

        return [
            'cost' => $call['call_cost'] ?? null,
            'call_result_code' => $call['call_result_code'] ?? null,
            'datetime_start' => $call['datetime_start'] ?? null,
            'duration' => $call['duration'] ?? null,
            'vox_call_id' => $call['id'] ?? null,
            'is_incoming' => $call['is_incoming'] ?? null,
            'phone_a' => $phoneA ?: ($call['phone_a'] ?? null),
            'phone_b' => $phoneB ?: ($call['phone_b'] ?? null),
            'scenario_id' => $call['scenario_id'] ?? null,
            'tags' => isset($call['tags']) ? json_encode($call['tags']) : null,
            'created' => date('Y-m-d H:i:s'),
            'queue_id' => $call['queue_id'] ?? null,
            'vox_user_id' => $call['user_id'] ?? null,
            'record_url' => $call['record_url'] ?? null,
            'assessment' => $callData['assessment'] ?? null,
        ];
    }
}
