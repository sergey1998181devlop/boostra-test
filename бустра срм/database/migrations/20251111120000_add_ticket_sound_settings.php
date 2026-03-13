<?php

declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class AddTicketSoundSettings extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("INSERT INTO s_settings(site_id, name, value) VALUES (NULL, 'ticket_sound_settings', '{\"check_interval_sec\":10,\"remind_interval_min\":15}')");
    }

    public function down(): void
    {
        $this->execute("DELETE FROM s_settings WHERE name='ticket_sound_settings' AND site_id IS NULL");
    }
}

