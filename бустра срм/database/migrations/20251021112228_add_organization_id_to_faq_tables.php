<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddOrganizationIdToFaqTables extends AbstractMigration
{
    /**
     * Добавление organization_id к FAQ таблицам
     */
    public function change(): void
    {
        // 1. Добавляем organization_id к s_faq_blocks
        $this->table('s_faq_blocks')
            ->addColumn('organization_id', 'integer', [
                'signed' => false,
                'default' => 1,
                'null' => false,
                'after' => 'id'
            ])
            ->addIndex('organization_id', ['name' => 'idx_faq_blocks_org'])
            ->addForeignKey('organization_id', 's_organizations', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
                'constraint' => 'fk_faq_blocks_organization'
            ])
            ->update();

        // 2. Добавляем organization_id и sequence к s_faq_sections
        $this->table('s_faq_sections')
            ->addColumn('organization_id', 'integer', [
                'signed' => false,
                'default' => 1,
                'null' => false,
                'after' => 'id'
            ])
            ->addColumn('sequence', 'integer', [
                'default' => 0,
                'null' => true,
            ])
            ->addIndex('organization_id', ['name' => 'idx_faq_sections_org'])
            ->addIndex(['organization_id', 'block_id'], ['name' => 'idx_org_block'])
            ->addForeignKey('organization_id', 's_organizations', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
                'constraint' => 'fk_faq_sections_organization'
            ])
            ->update();

        // 3. Добавляем organization_id к s_faq
        $this->table('s_faq')
            ->addColumn('organization_id', 'integer', [
                'signed' => false,
                'default' => 1,
                'null' => false,
                'after' => 'id'
            ])
            ->addIndex('organization_id', ['name' => 'idx_faq_org'])
            ->addIndex(['organization_id', 'section_id'], ['name' => 'idx_org_section'])
            ->addForeignKey('organization_id', 's_organizations', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
                'constraint' => 'fk_faq_organization'
            ])
            ->update();
    }
}
