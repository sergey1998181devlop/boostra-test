<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Models\User;
use App\Modules\VoxCallsArchive\Application\Service\VoxCallsArchiveService;

class CallController
{
    /** @var VoxCallsArchiveService|null */
    private $archiveService = null;

    /**
     * Получить сервис записи в архив
     *
     * @return VoxCallsArchiveService
     */
    private function getArchiveService(): VoxCallsArchiveService
    {
        if ($this->archiveService === null) {
            $this->archiveService = new VoxCallsArchiveService();
        }
        return $this->archiveService;
    }

    /**
     * Логирование звонка
     *
     * @param Request $request
     * @return Response
     */
    public function logCall(Request $request): Response
    {
        $formattedPhoneNumber = formatPhoneNumber($request->input('phone'));

        if (!$formattedPhoneNumber) {
            return response()->json(['message' => 'Неверный формат телефона'], 422);
        }

        $user = (new User)->get(['id', 'missing_manager_id'], [
            'phone_mobile' => $formattedPhoneNumber
        ])->getData();

        try {
            if ($request->input('missing_manager_id') && $user['missing_manager_id'] !== $request->input('missing_manager_id')) {
                (new User())->update([
                    'missing_manager_id' => $request->input('missing_manager_id')
                ], ['id' => $user['id']]);
            }

            // Запись ТОЛЬКО в архивную БД
            $archiveService = $this->getArchiveService();
            $archiveService->saveFromArray([
                'cost' => 0,
                'call_result_code' => $request->input('result_code'),
                'datetime_start' => date('Y-m-d H:i:s'),
                'duration' => $request->input('duration'),
                'vox_call_id' => $request->input('vox_call_id'),
                'is_incoming' => false,
                'phone_a' => $request->input('phone_a'),
                'phone_b' => $request->input('phone_b'),
                'scenario_id' => $request->input('scenario_id'),
                'tags' => $request->input('tags', '[]'),
                'created' => date('Y-m-d H:i:s'),
                'user_id' => $user['id'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка при логирование звонка'], 500);
        }

        return response()->json(['message' => 'Звонок записан']);
    }
}
