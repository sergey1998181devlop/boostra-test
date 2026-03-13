<?php

use Phinx\Migration\AbstractMigration;

final class CreateOrganizationsData extends AbstractMigration
{
    private const TABLE_NAME = 's_organizations_data';

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table(self::TABLE_NAME, [
            'id' => 'id',
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'signed' => false,
        ]);

        $table->addColumn('organization_id', 'integer', [
            'null' => false,
        ]);

        $table->addColumn('key', 'string', [
            'null' => false,
            'comment' => 'Key',
        ]);

        $table->addColumn('value', 'text', [
            'null' => false,
            'comment' => 'Value',
        ]);

        $table->addIndex(['organization_id', 'key'], [
            'unique' => true,
            'name' => 'idx_org_key_unique'
        ]);

        $table->addColumn('created_at', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false
        ]);

        $table->create();

        $table->addForeignKey('organization_id', 's_organizations', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION']);

        $table->update();
    }

    public function down(): void
    {
        if ($this->hasTable(self::TABLE_NAME)) {
            $table = $this->table(self::TABLE_NAME);
            $table->drop();
        }
    }
}
