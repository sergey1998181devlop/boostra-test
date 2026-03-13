<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSiteIdForPagesTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('s_pages');
        $table->addColumn('site_id', 'string', [
            'limit' => 16,
            'null' => true,
            'default' => null,
            'after' => 'id'
        ])
        ->addIndex(['name'], ['unique' => true])
        ->update();

        $this->execute("UPDATE s_pages SET site_id = 'boostra'");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('s_pages');
        $table->removeIndex(['name'])
              ->removeColumn('site_id')
              ->update();
    }
}
