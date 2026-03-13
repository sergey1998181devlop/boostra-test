<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateOfferArbitrationDocumentType extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('s_document_types')) {
            return;
        }

        // Ensure idempotency: skip if the type already exists
        $exists = $this->fetchRow("SELECT 1 AS one FROM s_document_types WHERE type = 'OFFER_ARBITRATION' LIMIT 1");
        if ($exists) {
            return;
        }

        $this->execute("
            INSERT INTO s_document_types (type, template, name, client_visible)
            VALUES ('OFFER_ARBITRATION', 'offer_arbitration.tpl', 'Соглашение о подписании молчанием', 1)
        ");
    }

    public function down(): void
    {
        if (!$this->hasTable('s_document_types')) {
            return;
        }

        $this->execute("DELETE FROM s_document_types WHERE type = 'OFFER_ARBITRATION'");
    }
}
