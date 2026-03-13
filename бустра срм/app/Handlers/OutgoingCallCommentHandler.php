<?php

namespace App\Handlers;

use App\Contracts\OutgoingCallCommentHandlerContract;
use App\Core\Application\Response\Response;
use App\Enums\CommentBlocks;
use App\Models\Comment;
use App\Models\Manager;
use App\Models\User;
use App\Service\CommentService;
use App\Service\VoximplantService;

class OutgoingCallCommentHandler implements OutgoingCallCommentHandlerContract
{
    public function handle(array $calls): Response
    {
        $count = 0;

        if (!$calls) {
            return response()->json(['message' => 'Нет исходящих звонков для сохранения'], Response::HTTP_OK);
        }

        foreach ($calls as $call) {
            $callExist = (new Comment())->has([
                'block' => CommentBlocks::OUTGOING_CALL,
                'text[~]' => '%' . $call['id'] . '%'
            ])->getData();

            if ($callExist || ($call['duration'] ?? 0) < 10 || empty($call['record_url']) || empty($call['user_id'])) {
                continue;
            }

            $operator = (new VoximplantService())->searchUsers((int) $call['user_id'])[0] ?? [];

            if (empty($operator)) {
                continue;
            }

            if (!empty($call['is_incoming']) && $call['is_incoming'] === true) {
                continue;
            }

            $localNumber = null;
            $callSession = (new VoximplantService())->searchCalls((int) $call['id']);
            if (!empty($callSession[0]['calls'][0]['local_number'])) {
                $localNumber = $callSession[0]['calls'][0]['local_number'];
            }

            $user = (new User())->get(['id', 'uid', 'firstname', 'lastname', 'patronymic', 'phone_mobile'], [
                'phone_mobile' => $call['phone_b'] ?? null
            ])->getData();

            if (empty($user)) {
                continue;
            }

            $manager = (new Manager())->get(['id', 'name_1c'], [
                'id' => 50
            ])->getData();

            if (empty($manager)) {
                continue;
            }

            $data = [
                'call_id' => $call['id'],
                'operator_id' => $operator['id'] ?? null,
                'operator_name' => $operator['full_name'] ?? '',
                'record_url' => $call['record_url'],
                'is_sent_analysis' => false,
                'provider' => 'voximplant',
                'client_phone' => $user['phone_mobile'] ?? null,
                'local_number' => $localNumber,
                'phone_a' => $call['phone_a'] ?? null,
                'phone_b' => $call['phone_b'] ?? null,
                'is_incoming' => $callSession[0]['is_incoming'] ?? false,
            ];

            (new Comment())->insert([
                'manager_id' => $manager['id'],
                'user_id' => $user['id'],
                'block' => CommentBlocks::OUTGOING_CALL,
                'created' => date('Y-m-d H:i:s'),
                'text' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);

            (new CommentService())->sendCommentTo1C([
                'manager' => $manager['name_1c'],
                'data' => $data,
                'number' => '',
                'user_uid' => $user['uid']
            ]);

            $count++;
        }

        return response()->json(['message' => $count . ' исходящих звонков успешно сохранено'], Response::HTTP_OK);
    }
}
