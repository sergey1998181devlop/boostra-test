<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSiteIdForBonon extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('application_tokens');
        if (!$table->hasColumn('site_id')) {
            $table->addColumn('site_id', 'string', [
                'limit'   => 20,
                'null'    => true,
                'comment' => 'Token site id',
                'collation' => 'utf8mb4_unicode_ci',
            ])->addForeignKey('site_id', 's_sites', 'site_id', [
                'delete'=> 'RESTRICT',
                'update'=> 'RESTRICT',
                'constraint' => 'FK_application_tokens_s_sites',
            ])->save();
        }

        $table = $this->table('s_partner_href');
        if (!$table->hasColumn('site_id')) {
            $table->addColumn('site_id', 'string', [
                'limit'   => 20,
                'null'    => true,
                'comment' => 'Token site id',
                'collation' => 'utf8mb4_unicode_ci',
            ])->addIndex(['site_id', 'link_type', 'client_type'], [
                'unique' => true,
                'name' => 'link_type_client_type_site_id',
            ])->addForeignKey('site_id', 's_sites', 'site_id', [
                'delete'=> 'RESTRICT',
                'update'=> 'RESTRICT',
                'constraint' => 'FK_s_partner_href_s_sites',
            ])->save();
        }
    }

    public function down(): void
    {
        $table = $this->table('s_partner_href');
        $needsSave = false;

        if ($table->hasIndex('link_type_client_type_site_id')) {
            $table->removeIndexByName('link_type_client_type_site_id');
            $needsSave = true;
        }

        if ($table->hasForeignKey('FK_s_partner_href_s_sites')) {
            $table->dropForeignKey('FK_s_partner_href_s_sites');
            $needsSave = true;
        }

        if ($table->hasColumn('site_id')) {
            $table->removeColumn('site_id');
            $needsSave = true;
        }

        if ($needsSave) {
            $table->save();
        }

        $table = $this->table('application_tokens');
        $needsSave = false;

        if ($table->hasForeignKey('FK_application_tokens_s_sites')) {
            $table->dropForeignKey('FK_application_tokens_s_sites');
            $needsSave = true;
        }

        if ($table->hasColumn('site_id')) {
            $table->removeColumn('site_id');
            $needsSave = true;
        }

        if ($needsSave) {
            $table->save();
        }
    }
}