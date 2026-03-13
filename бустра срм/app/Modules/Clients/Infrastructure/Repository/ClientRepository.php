<?php

namespace App\Modules\Clients\Infrastructure\Repository;

use App\Modules\Clients\Domain\Entity\Client;
use App\Modules\Clients\Domain\Repository\ClientRepositoryInterface;
use Database;

/**
 * Репозиторий для работы с клиентами.
 * 
 * Реализует доступ к данным клиентов в таблице s_users,
 * включая поиск по номеру телефона и получение истории займов.
 * 
 * @package App\Modules\Clients\Infrastructure\Repository
 */
class ClientRepository implements ClientRepositoryInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Находит клиента по номеру телефона.
     * 
     * @param string $phone Номер телефона клиента
     * @return Client|null Найденный клиент или null
     */
    public function findByPhone(string $phone): ?Client
    {
        return $this->fetchClient('u.phone_mobile = ?', [$phone]);
    }

    /**
     * Находит клиента по номеру телефона, у которого есть заказы в указанной организации.
     *
     * @param string $phone
     * @param int $organizationId
     * @return Client|null
     */
    public function findByPhoneAndOrganizationId(string $phone, int $organizationId): ?Client
    {
        return $this->fetchClient(
            'u.phone_mobile = ?',
            [$organizationId, $phone],
            'INNER JOIN s_orders o ON o.user_id = u.id AND o.organization_id = ?'
        );
    }

    /**
     * Находит клиента по номеру телефона, у которого есть заказы в одной из указанных организаций.
     *
     * @param string $phone
     * @param int[] $organizationIds
     * @return Client|null
     */
    public function findByPhoneAndOrganizationIds(string $phone, array $organizationIds): ?Client
    {
        $placeholders = implode(',', array_fill(0, count($organizationIds), '?'));
        $params = array_merge($organizationIds, [$phone]);

        return $this->fetchClient(
            'u.phone_mobile = ?',
            $params,
            'INNER JOIN s_orders o ON o.user_id = u.id AND o.organization_id IN (' . $placeholders . ')'
        );
    }

    private function fetchClient(?string $where, array $params = [], string $joins = ''): ?Client
    {
        $whereClause = $where !== null && $where !== '' ? "WHERE {$where}" : '';

        $sql = "SELECT u.id, u.UID, u.firstname, u.lastname, u.patronymic, u.phone_mobile, u.blocked, u.loan_history,
                       ub.sale_info, ub.buyer, ub.buyer_phone,
                       CASE WHEN EXISTS (
                           SELECT 1
                           FROM s_user_dnc dnc
                           WHERE dnc.user_id = u.id
                             AND dnc.date_start <= NOW()
                             AND dnc.date_end >= NOW()
                       ) THEN 0 ELSE 1 END AS auto_informer_enabled,
                       CASE WHEN EXISTS (
                           SELECT 1
                           FROM b2p_cards bc
                           WHERE bc.user_id = u.id
                             AND bc.deleted = 0
                             AND bc.deleted_by_client = 0
                             AND bc.autodebit = 0
                       ) THEN 1 ELSE 0 END AS recurrents_disabled
                FROM s_users u
                {$joins}
                LEFT JOIN s_user_balance ub ON u.id = ub.user_id
                {$whereClause}
                ORDER BY u.id DESC
                LIMIT 1";

        $this->db->query($sql, ...$params);
        $results = $this->db->results();

        if (empty($results)) {
            return null;
        }

        $userData = $results[0];
        if (!$userData) {
            return null;
        }

        return Client::fromArray((array)$userData);
    }
}
