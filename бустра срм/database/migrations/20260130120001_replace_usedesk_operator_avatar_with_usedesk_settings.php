<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Замена настройки usedesk_operator_avatar на единый JSON usedesk_settings.
 * usedesk_settings: { "operator_avatar": "...", "custom_icon_enabled": true }
 */
final class ReplaceUsedeskOperatorAvatarWithUsedeskSettings extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO s_settings (site_id, name, value)
            SELECT NULL, 'usedesk_settings',
              JSON_UNQUOTE(JSON_OBJECT(
                'operator_avatar', IFNULL((SELECT value FROM s_settings WHERE name = 'usedesk_operator_avatar' AND site_id IS NULL LIMIT 1), ''),
                'custom_icon_enabled', true
              ))
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM s_settings WHERE name = 'usedesk_settings' AND site_id IS NULL)
        ");
        $this->execute("DELETE FROM s_settings WHERE name = 'usedesk_operator_avatar' AND site_id IS NULL");
    }

    public function down(): void
    {
        $row = $this->fetchRow("SELECT value FROM s_settings WHERE name = 'usedesk_settings' AND site_id IS NULL LIMIT 1");
        if ($row) {
            $data = json_decode($row['value'], true);
            $avatar = isset($data['operator_avatar']) ? $data['operator_avatar'] : '';
            $escaped = $this->getAdapter()->getConnection()->quote($avatar);
            $this->execute("INSERT INTO s_settings (site_id, name, value) VALUES (NULL, 'usedesk_operator_avatar', {$escaped})");
        }
        $this->execute("DELETE FROM s_settings WHERE name = 'usedesk_settings' AND site_id IS NULL");
    }
}
