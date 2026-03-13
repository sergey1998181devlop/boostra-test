<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSoyaSiteSettings extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("INSERT INTO s_settings (site_id, name, value) VALUES ('soyaplace', 'addresses_is_dadata', '0');");
        $this->execute("INSERT INTO s_settings (site_id, name, value) VALUES ('soyaplace', 'send_complaint', '1');");
        $this->execute("INSERT INTO s_settings (site_id, name, value) VALUES ('soyaplace', 'header_email_block', '1');");
        $this->execute("INSERT INTO s_settings (site_id, name, value) VALUES ('soyaplace', 'header_email', 'info@soyaplace.ru');");
        $this->execute("INSERT INTO s_settings (site_id, name, value) VALUES ('soyaplace', 'additional_work_scope', '1');");
        $this->execute("INSERT INTO s_settings (site_id, name, value) VALUES ('soyaplace', 'prolongation_disable_timeout_minutes', '60');");
        $this->execute("INSERT INTO s_settings (site_id, name, value) VALUES ('soyaplace', 'faq_highlight_enabled', '1');");
        $this->execute("INSERT INTO s_settings (site_id, name, value) VALUES ('soyaplace', 'faq_highlight_delay', '1');");
    }

    public function down(): void
    {
        $this->execute("DELETE FROM s_settings WHERE site_id = 'soyaplace' AND name = 'addresses_is_dadata';");
        $this->execute("DELETE FROM s_settings WHERE site_id = 'soyaplace' AND name = 'send_complaint';");
        $this->execute("DELETE FROM s_settings WHERE site_id = 'soyaplace' AND name = 'header_email_block';");
        $this->execute("DELETE FROM s_settings WHERE site_id = 'soyaplace' AND name = 'header_email';");
        $this->execute("DELETE FROM s_settings WHERE site_id = 'soyaplace' AND name = 'additional_work_scope';");
        $this->execute("DELETE FROM s_settings WHERE site_id = 'soyaplace' AND name = 'prolongation_disable_timeout_minutes';");
        $this->execute("DELETE FROM s_settings WHERE site_id = 'soyaplace' AND name = 'faq_highlight_enabled';");
        $this->execute("DELETE FROM s_settings WHERE site_id = 'soyaplace' AND name = 'faq_highlight_delay';");
    }
}