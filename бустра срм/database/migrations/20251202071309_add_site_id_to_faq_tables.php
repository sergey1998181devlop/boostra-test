<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSiteIdToFaqTables extends AbstractMigration
{
    /**
     * Migrate Up - Replace organization_id with site_id in FAQ tables
     */
    public function up(): void
    {
        // ============================================
        // s_faq_blocks
        // ============================================
        $this->table('s_faq_blocks')
            ->addColumn('site_id', 'string', [
                'limit' => 50,
                'null' => true,
                'after' => 'id'
            ])
            ->update();

        $this->execute("UPDATE s_faq_blocks SET site_id = 'boostra' WHERE organization_id = 8");
        $this->execute("UPDATE s_faq_blocks SET site_id = 'soyaplace' WHERE organization_id = 17");

        $this->table('s_faq_blocks')
            ->changeColumn('site_id', 'string', [
                'limit' => 50,
                'null' => false
            ])
            ->addIndex(['site_id'], ['name' => 'idx_faq_blocks_site_id'])
            ->removeIndex(['organization_id'])
            ->update();

        $this->table('s_faq_blocks')
            ->dropForeignKey('organization_id')
            ->update();

        $this->table('s_faq_blocks')
            ->removeColumn('organization_id')
            ->update();

        // ============================================
        // s_faq_sections
        // ============================================
        $this->table('s_faq_sections')
            ->addColumn('site_id', 'string', [
                'limit' => 50,
                'null' => true,
                'after' => 'id'
            ])
            ->update();

        $this->execute("UPDATE s_faq_sections SET site_id = 'boostra' WHERE organization_id = 8");
        $this->execute("UPDATE s_faq_sections SET site_id = 'soyaplace' WHERE organization_id = 17");

        $this->table('s_faq_sections')
            ->changeColumn('site_id', 'string', [
                'limit' => 50,
                'null' => false
            ])
            ->addIndex(['site_id'], ['name' => 'idx_faq_sections_site_id'])
            ->removeIndex(['organization_id'])
            ->removeIndex(['organization_id', 'block_id'])
            ->update();

        $this->table('s_faq_sections')
            ->dropForeignKey('organization_id')
            ->update();

        $this->table('s_faq_sections')
            ->removeColumn('organization_id')
            ->update();

        // ============================================
        // s_faq
        // ============================================
        $this->table('s_faq')
            ->addColumn('site_id', 'string', [
                'limit' => 50,
                'null' => true,
                'after' => 'id'
            ])
            ->update();

        $this->execute("UPDATE s_faq SET site_id = 'boostra' WHERE organization_id = 8");
        $this->execute("UPDATE s_faq SET site_id = 'soyaplace' WHERE organization_id = 17");

        $this->table('s_faq')
            ->changeColumn('site_id', 'string', [
                'limit' => 50,
                'null' => false
            ])
            ->addIndex(['site_id'], ['name' => 'idx_faq_site_id'])
            ->removeIndex(['organization_id'])
            ->removeIndex(['organization_id', 'section_id'])
            ->update();

        $this->table('s_faq')
            ->dropForeignKey('organization_id')
            ->update();

        $this->table('s_faq')
            ->removeColumn('organization_id')
            ->update();
    }

    /**
     * Migrate Down - Restore organization_id from site_id in FAQ tables
     */
    public function down(): void
    {
        // ============================================
        // s_faq
        // ============================================
        $this->table('s_faq')
            ->addColumn('organization_id', 'integer', [
                'signed' => false,
                'null' => true,
                'after' => 'id'
            ])
            ->update();

        $this->execute("UPDATE s_faq SET organization_id = 8 WHERE site_id = 'boostra'");
        $this->execute("UPDATE s_faq SET organization_id = 17 WHERE site_id = 'soyaplace'");

        $this->table('s_faq')
            ->changeColumn('organization_id', 'integer', [
                'signed' => false,
                'null' => false,
                'default' => 1
            ])
            ->addIndex(['organization_id'], ['name' => 'idx_faq_org'])
            ->addIndex(['organization_id', 'section_id'], ['name' => 'idx_org_section'])
            ->addForeignKey('organization_id', 's_organizations', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
                'constraint' => 'fk_faq_organization'
            ])
            ->removeIndex(['site_id'])
            ->update();

        $this->table('s_faq')
            ->removeColumn('site_id')
            ->update();

        // ============================================
        // s_faq_sections
        // ============================================
        $this->table('s_faq_sections')
            ->addColumn('organization_id', 'integer', [
                'signed' => false,
                'null' => true,
                'after' => 'id'
            ])
            ->update();

        $this->execute("UPDATE s_faq_sections SET organization_id = 8 WHERE site_id = 'boostra'");
        $this->execute("UPDATE s_faq_sections SET organization_id = 17 WHERE site_id = 'soyaplace'");

        $this->table('s_faq_sections')
            ->changeColumn('organization_id', 'integer', [
                'signed' => false,
                'null' => false,
                'default' => 1
            ])
            ->addIndex(['organization_id'], ['name' => 'idx_faq_sections_org'])
            ->addIndex(['organization_id', 'block_id'], ['name' => 'idx_org_block'])
            ->addForeignKey('organization_id', 's_organizations', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
                'constraint' => 'fk_faq_sections_organization'
            ])
            ->removeIndex(['site_id'])
            ->update();

        $this->table('s_faq_sections')
            ->removeColumn('site_id')
            ->update();

        // ============================================
        // s_faq_blocks
        // ============================================
        $this->table('s_faq_blocks')
            ->addColumn('organization_id', 'integer', [
                'signed' => false,
                'null' => true,
                'after' => 'id'
            ])
            ->update();

        $this->execute("UPDATE s_faq_blocks SET organization_id = 8 WHERE site_id = 'boostra'");
        $this->execute("UPDATE s_faq_blocks SET organization_id = 17 WHERE site_id = 'soyaplace'");

        $this->table('s_faq_blocks')
            ->changeColumn('organization_id', 'integer', [
                'signed' => false,
                'null' => false,
                'default' => 1
            ])
            ->addIndex(['organization_id'], ['name' => 'idx_faq_blocks_org'])
            ->addForeignKey('organization_id', 's_organizations', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
                'constraint' => 'fk_faq_blocks_organization'
            ])
            ->removeIndex(['site_id'])
            ->update();

        $this->table('s_faq_blocks')
            ->removeColumn('site_id')
            ->update();
    }
}
