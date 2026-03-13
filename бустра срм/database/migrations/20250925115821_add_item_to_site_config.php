<?php

declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class AddItemToSiteConfig extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("INSERT INTO s_settings(name, value) VALUES  ('voximplant_ai_enabled', 0)");
    }

    public function down(): void
    {
        $this->execute("DELETE FROM s_settings WHERE name='voximplant_ai_enabled'");
    }
}
