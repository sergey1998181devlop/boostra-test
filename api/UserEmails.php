<?php

require_once 'Simpla.php';

/**
 * Класс для работы с доп. почтами пользователя.
 */
class UserEmails extends Simpla
{
    const SOURCE_COMPLAINT_EMAIL = 'COMPLAINT_EMAIL';
    const SOURCE_FEEDBACK_EMAIL = 'FEEDBACK_EMAIL';
    const SOURCE_USER_TICKET_EMAIL = 'USER_TICKET_EMAIL';
    const SOURCE_NBKI_EMAIL = 'NBKI_EMAIL';

    /**
     * Синхронизирует почты с CRM и 1C из формы жалобы ЛК
     * @param object $user
     * @param string $email
     * @param string $source
     * @return void
     */
    public function syncEmail(object $user, string $email, string $source): void
    {
        $this->syncCrm($user, $email, $source);
        $this->sync1c($user, $email);
    }

    /**
     * Отправляет почту в бд CRM
     * @param object $user
     * @param string $email
     * @param string $source
     * @return void
     */
    private function syncCrm(object $user, string $email, string $source): void
    {
        if (empty($user->email)) {
            $this->users->update_user($user->id, ['email' => $email]);
            return;
        }

        if ($user->email === $email) {
            // Это основной email
            return;
        }

        $usersWithSameEmail = $this->getUsersWithSameEmail($email);
        foreach ($usersWithSameEmail as $otherUserId) {
            if ((int)$otherUserId === (int)$user->id) {
                // Этот доп.email уже добавлен
                return;
            }
        }

        $this->add([
            'user_id' => $user->id,
            'email' => $email,
            'source' => $source
        ]);
    }

    /**
     * Отправляет email в 1С и помечает его как синхронизированный.
     *
     * @param object $user Пользователь
     * @param string $email Email-адрес
     * @return void
     */
    private function sync1c(object $user, string $email): void
    {
        try {
            $response = $this->soap->sendAdditionalEmail($user->uid, [$email]);
            $payload = $response['response'] ?? $response;

            $isSuccess = !array_key_exists('КонтрагентНайден', $payload) || $payload['КонтрагентНайден'] === true;

            if ($isSuccess) {
                $this->markSynced((int) $user->id, $email);
                return;
            }

            $this->logging(
                __METHOD__,
                'SendAdditionalEmail failed',
                ['user_id' => $user->id, 'uid' => $user->uid, 'email' => $email],
                ['response' => $payload],
                'sync_user_emails.txt'
            );
        } catch (\Throwable $e) {
            $this->logging(
                __METHOD__,
                'SendAdditionalEmail exception',
                ['user_id' => $user->id, 'uid' => $user->uid, 'email' => $email],
                ['error' => $e->getMessage()],
                'sync_user_emails.txt'
            );
        }
    }

    /**
     * Поиск пользователей с указанной доп. почтой
     * @param string $email
     * @return array
     */
    public function getUsersWithSameEmail(string $email): array
    {
        $this->db->query($this->db->placehold('SELECT user_id FROM __user_emails WHERE email = ?', $email));

        return $this->db->results('user_id') ?? [];
    }

    /**
     * Добавление доп. почты в бд CRM
     * @param array $row
     * @return int
     */
    public function add(array $row): int
    {
        $this->db->query($this->db->placehold('INSERT INTO __user_emails SET ?%', $row));

        return $this->db->insert_id();
    }

    /**
     * Помечает email как синхронизированный с 1С.
     *
     * @param int $userId ID пользователя
     * @param string $email Email-адрес
     *
     * @return void
     */
    public function markSynced(int $userId, string $email): void
    {
        $this->db->query(
            $this->db->placehold(
                'UPDATE __user_emails SET synced_at = NOW(), updated_at = NOW() WHERE user_id = ? AND email = ?',
                $userId,
                $email
            )
        );
    }
}