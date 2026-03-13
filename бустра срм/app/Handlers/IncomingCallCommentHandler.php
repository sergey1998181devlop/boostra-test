<?php

namespace App\Handlers;

use App\Contracts\IncomingCallCommentHandlerContract;
use App\Core\Application\Response\Response;
use App\Enums\CommentBlocks;
use App\Repositories\UserRepository;
use App\Repositories\ManagerRepository;
use App\Repositories\CommentRepository;
use App\Service\CommentService;
use App\Service\VoximplantService;

class IncomingCallCommentHandler implements IncomingCallCommentHandlerContract
{
    private UserRepository $users;
    private ManagerRepository $managers;
    private CommentRepository $comments;
    private CommentService $commentService;
    private VoximplantService $voximplantService;

    public function __construct(
        UserRepository $users,
        ManagerRepository $managers,
        CommentRepository $comments,
        CommentService $commentService,
        VoximplantService $voximplantService
    ) {
        $this->users = $users;
        $this->managers = $managers;
        $this->comments = $comments;
        $this->commentService = $commentService;
        $this->voximplantService = $voximplantService;
    }

    /**
     * @param array $userData
     * @param array $callData
     * @param string $blockType
     * @param int|null $managerId
     * @return Response
     */
    public function handle(array $userData, array $callData, string $blockType, ?int $managerId = null): Response
    {
        $user = isset($userData['id']) 
            ? $this->users->getById((int)$userData['id']) 
            : $this->users->getByPhone((string)$userData['phone_mobile']);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        if ($managerId === null) {
            $managerId = $blockType === CommentBlocks::FROMTECH_INCOMING_CALL ? 612 : 50;
        }
        
        $manager = $this->managers->getById($managerId);

        if (!$manager) {
            return response()->json(['message' => 'Менеджер не найден'], 500);
        }

        if (!empty($callData['call_id'])) {
            $response = $this->voximplantService->searchCalls((int) $callData['call_id']);

            if (!empty($response)) {
                $call = $response[0];

                $callData['scenario_id'] = $call['scenario_id'] ?? null;
                $callData['operator_id'] = $call['user_id'] ?? null;
                $callData['phone_a'] = $call['phone_a'] ?? null;
                $callData['phone_b'] = $call['phone_b'] ?? null;
                $callData['is_incoming'] = $call['is_incoming'] ?? null;
                $callData['duration'] = $call['duration'] ?? null;
                $callData['completion_code'] = $call['completion_code'] ?? null;

                if (!empty($call['calls'][0]['local_number'])) {
                    $callData['local_number'] = $call['calls'][0]['local_number'];
                }

                if (!empty($call['datetime_start'])) {
                    $startTs = strtotime($call['datetime_start']);
                    if ($startTs) {
                        $callData['tqm_start_date'] = date('Y-m-d\TH:i:s', $startTs);
                        if (!empty($callData['duration'])) {
                            $callData['tqm_end_date'] = date('Y-m-d\TH:i:s', $startTs + (int)$callData['duration']);
                        }
                    }
                }
            }
        }

        $this->comments->insert([
            'manager_id' => $manager->id,
            'user_id' => $user->id,
            'block' => $blockType,
            'created' => date('Y-m-d H:i:s'),
            'text' => json_encode($callData, JSON_UNESCAPED_UNICODE),
        ]);

        $this->commentService->sendCommentTo1C([
            'manager' => $manager->name_1c,
            'data' => $callData,
            'number' => '',
            'user_uid' => $user->uid
        ]);

        return response()->json(['message' => 'Звонок успешно сохранен']);
    }
}