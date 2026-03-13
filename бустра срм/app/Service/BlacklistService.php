<?php

namespace App\Service;

use App\Repositories\CommentRepository;
use App\Repositories\IncomingCallBlacklistRepository;
use App\Repositories\UserRepository;
use App\Repositories\ChangelogRepository;
use App\Core\Application\Session\Session;

class BlacklistService
{
    private IncomingCallBlacklistRepository $blacklistRepository;
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;
    private ChangelogRepository $changelog;
    private Session $session;
    
    public function __construct(IncomingCallBlacklistRepository $blacklistRepository, CommentRepository $commentRepository, UserRepository $userRepository, ChangelogRepository $changelog, Session $session)
    {
        $this->blacklistRepository = $blacklistRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->changelog = $changelog;
        $this->session = $session;
    }

    /**
     * @param string $phone
     * @return string|null
     */
    public function isBlocked(string $phone): ?string
    {
        $phone = $this->normalizePhone($phone);
        $record = $this->blacklistRepository->findByPhone($phone);

        if ($record && $record->is_active) {
            $this->blacklistRepository->updateLastCallDate($record->id);
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return int
     */
    public function addToBlacklist(array $data): int
    {
        $data['phone_number'] = $this->normalizePhone($data['phone_number']);

        $exists = $this->blacklistRepository->findByPhone($data['phone_number']);
        if ($exists) {
            throw new \InvalidArgumentException('Этот номер уже находится в черном списке');
        }

        $id = $this->blacklistRepository->create(
            $data['phone_number'],
            $data['reason'] ?? '',
            $data['created_by']
        );

        $userId = $this->userRepository->getByPhone($data['phone_number']);
        if ($userId) {
            $this->commentRepository->insert([
                'manager_id' => $data['created_by'],
                'user_id' => $userId,
                'block' => 'blacklist',
                'text' => "Номер добавлен в черный список на 24 часа. Причина: " . $data['reason'],
                'created' => date('Y-m-d H:i:s')
            ]);
        }

        return $id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function deleteFromBlacklist(int $id): void
    {
        $this->blacklistRepository->delete($id);
    }

    /**
     * @param int $id
     * @param bool $status
     * @return void
     */
    public function toggleStatus(int $id, bool $status): void
    {
        $before = $this->blacklistRepository->findById($id);

        $this->blacklistRepository->updateStatus($id, $status);

        $managerId = (int)($this->session->get('manager_id') ?? 0);

        $userId = null;
        $phone = $before->phone_number ?? null;
        if ($phone) {
            $user = $this->userRepository->getByPhone($phone);
            $userId = $user->id ?? null;
        }

        $this->changelog->addIncomingBlacklistToggle(
            $managerId,
            $id,
            (bool)($before->is_active ?? 0),
            $status,
            $phone,
            $userId
        );
    }
    
    private function normalizePhone(string $phone): string
    {
        // Удаляем все кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Проверяем длину номера
        if (strlen($phone) !== 11) {
            throw new \InvalidArgumentException('Неверный формат номера телефона');
        }
        
        // Если номер начинается с 8, заменяем на 7
        if ($phone[0] === '8') {
            $phone = '7' . substr($phone, 1);
        }
        
        return $phone;
    }
} 