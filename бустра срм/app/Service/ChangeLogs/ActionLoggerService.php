<?php

namespace App\Service\ChangeLogs;

use App\Enums\LogAction;
use App\Repositories\ChangelogRepository;
use App\Repositories\CommentRepository;
use App\Repositories\UserRepository;
use App\Repositories\ManagerRepository;
use App\Service\CommentService;

class ActionLoggerService
{
    private ChangelogRepository $changelogRepo;
    private CommentRepository $commentRepo;
    private UserRepository $userRepo;
    private ManagerRepository $managerRepo;
    private CommentService $commentService;

    public function __construct(
        ChangelogRepository $changelogRepo,
        CommentRepository   $commentRepo,
        UserRepository      $userRepo,
        ManagerRepository   $managerRepo,
        CommentService      $commentService
    )
    {
        $this->changelogRepo = $changelogRepo;
        $this->commentRepo = $commentRepo;
        $this->userRepo = $userRepo;
        $this->managerRepo = $managerRepo;
        $this->commentService = $commentService;
    }

    public function log(LogAction $action, int $userId, array $input): void
    {
        if ($action->needsChangelog()) {
            $this->changelogRepo->addLog(
                $input['manager_id'],
                $action->getValue(),
                $input['old_values'] ?? '',
                $input['new_values'] ?? '',
                $input['order_id'] ?? null,
                $userId
            );
        }

        if ($action->getCommentBlock()) {
            $this->commentRepo->insert([
                'manager_id' => $input['manager_id'],
                'user_id' => $userId,
                'block' => $action->getCommentBlock(),
                'order_id' => $input['order_id'] ?? null,
                'created' => date('Y-m-d H:i:s'),
                'text' => $action->getMessage(),
            ]);
        }

        if ($action->needsSendTo1C()) {
            $user = $this->userRepo->getById($userId);
            if ($user) {
                $manager = $this->managerRepo->getById($input['manager_id']);
                $this->commentService->sendCommentTo1C([
                    'manager' => $manager ? $manager->name_1c : '',
                    'data' => [
                        'text' => $action->getMessage(),
                        'order_id' => $input['order_id'] ?? null,
                    ],
                    'user_uid' => $user->uid
                ]);
            }
        }
    }
}