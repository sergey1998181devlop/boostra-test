<?php

namespace App\Service;

use App\Contracts\FromtechIncomingCallServiceInterface;
use App\Core\Application\Response\Response;
use App\Dto\FromtechIncomingCallDto;
use App\Enums\CommentBlocks;
use App\Handlers\IncomingCallCommentHandler;
use App\Repositories\UserRepository;

final class FromtechIncomingCallService implements FromtechIncomingCallServiceInterface
{
    private UserRepository $userRepository;
    private IncomingCallCommentHandler $incomingCallHandler;

    public function __construct(
        UserRepository $userRepository,
        IncomingCallCommentHandler $incomingCallHandler
    ) {
        $this->userRepository = $userRepository;
        $this->incomingCallHandler = $incomingCallHandler;
    }

    public function handle(FromtechIncomingCallDto $dto): Response
    {
        $formattedPhone = formatPhoneNumber($dto->msisdn);
        if (!$formattedPhone) {
            return response()->json(['message' => 'Неверный формат номера телефона'], 422);
        }

        $user = $this->userRepository->getByPhone($formattedPhone);

        $userData = ['phone_mobile' => $formattedPhone];
        if ($user) {
            $userData = [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'patronymic' => $user->patronymic,
                'phone_mobile' => $user->phone_mobile
            ];
        }

        $callData = $dto->toArray();

        return $this->incomingCallHandler->handle(
            $userData,
            $callData,
            CommentBlocks::FROMTECH_INCOMING_CALL,
            $dto->manager_id
        );
    }
}