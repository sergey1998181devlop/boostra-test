<?php

require_once('Simpla.php');

class AbTestService extends Simpla
{
    public const PARTNER_BANNER_AB = 'partner_banner_ab_v1';

    public const BANNER_GROUP_CONTROL = 'control';

    public const BANNER_EVENT_SHOWN = 'shown';

    public const BANNER_EVENT_SUPPRESSED = 'suppressed';

    public const BANNER_EVENT_CLICKED = 'click';

    public const BANNER_EVENT_PAID = 'paid';

    public const BANNER_EVENT_LK_OPEN = 'lk_open';

    protected const BANNER_GROUP_TEST = 'test';

    public function checkUserInAbTest($user_id): bool
    {
        return (bool) $this->user_data->read($user_id, self::PARTNER_BANNER_AB);
    }

    public function getGroup($user_id): string
    {
        $group = $this->user_data->read($user_id, self::PARTNER_BANNER_AB);

        if ($group) return $group;

        $hash = crc32($user_id . self::PARTNER_BANNER_AB);
        $group = ($hash % 2 == 0) ? self::BANNER_GROUP_CONTROL : self::BANNER_GROUP_TEST;

        $this->user_data->set($user_id, self::PARTNER_BANNER_AB, $group);
        $this->user_data->set($user_id, self::PARTNER_BANNER_AB.':assigned_at', date('Y-m-d H:i:s'));

        $this->log($user_id, $group, $group === self::BANNER_GROUP_CONTROL ? self::BANNER_EVENT_SUPPRESSED : self::BANNER_EVENT_SHOWN);

        return $group;
    }

    public function logLkOpen($user_id, array $meta = [])
    {
        if (!$this->checkUserInAbTest($user_id)) return;
        $this->log($user_id, $this->getGroup($user_id), self::BANNER_EVENT_LK_OPEN, $meta);
    }

    public function logShowed($user_id, array $meta = [])
    {
        if (!$this->checkUserInAbTest($user_id)) return;
        $this->log($user_id, $this->getGroup($user_id), self::BANNER_EVENT_SHOWN, $meta);
    }

    public function logSuppressed($user_id, array $meta = [])
    {
        if (!$this->checkUserInAbTest($user_id)) return;
        $this->log($user_id, $this->getGroup($user_id), self::BANNER_EVENT_SUPPRESSED, $meta);
    }

    public function logClick($user_id, array $meta = [])
    {
        if (!$this->checkUserInAbTest($user_id)) return;
        $this->log($user_id, $this->getGroup($user_id), self::BANNER_EVENT_CLICKED, $meta);
    }

    public function logPaid($user_id, array $meta = [])
    {
        if (!$this->checkUserInAbTest($user_id)) return;

        $assignedAt = $this->user_data->read($user_id, self::PARTNER_BANNER_AB.':assigned_at');
        $days_between = date_diff(
            new DateTime($assignedAt),
            new DateTime(date('Y-m-d H:i:s'))
        );

        $this->logging(__METHOD__, 'logPaid', ['user_id' => $user_id], ['days_between' => $days_between->days], 'ab_test.txt');

        // Логируем оплату только если пользователь был в А/Б тесте не более 2 дней
        if ($days_between->days > 2) return;

        // Логируем оплату, если пользователь в тестовой группе и кликнул по баннеру
        if ($this->isTestGroup($user_id) && $this->hasClickedBanner($user_id)) {
            $this->log($user_id, self::BANNER_GROUP_TEST, self::BANNER_EVENT_PAID, $meta);
        }

        // Логируем оплату, если пользователь в контрольной группе (баннер не показывался)
        if ($this->isControlGroup($user_id)) {
            $this->log($user_id, self::BANNER_GROUP_CONTROL, self::BANNER_EVENT_PAID, $meta);
        }
    }

    private function log($user_id, $ab_group, $event, $meta = [])
    {
        try {
            $query = $this->db->placehold("INSERT INTO s_ab_events SET ab_key = ?, user_id = ?, ab_group = ?, event = ?, meta = ?, created_at = NOW()",
                self::PARTNER_BANNER_AB,
                $user_id,
                $ab_group,
                $event,
                json_encode($meta));

            $this->db->query($query);
        } catch (\Throwable $th) {
            $this->logging(__METHOD__, 'log ab test event error', [
                'user_id' => $user_id,
                'ab_group' => $ab_group,
                'event' => $event,
                'meta' => $meta,
            ], ['error' => $th->getMessage()], 'ab_test.txt' );
        }
    }

    private function isTestGroup($user_id): bool
    {
        return $this->user_data->read($user_id, self::PARTNER_BANNER_AB) === self::BANNER_GROUP_TEST;
    }

    private function isControlGroup($user_id): bool
    {
        return $this->user_data->read($user_id, self::PARTNER_BANNER_AB) === self::BANNER_GROUP_CONTROL;
    }

    private function hasClickedBanner($user_id): bool
    {
        $query = $this->db->placehold("SELECT 1 has_clicked FROM s_ab_events WHERE user_id = ? AND ab_key = ? AND event = ? LIMIT 1",
            $user_id,
            self::PARTNER_BANNER_AB,
            self::BANNER_EVENT_CLICKED
        );

        $this->db->query($query);

        return (bool)$this->db->result('has_clicked');
    }
}