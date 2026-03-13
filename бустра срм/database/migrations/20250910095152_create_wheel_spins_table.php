<?php
declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateWheelSpinsTable extends AbstractMigration
{
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
        $table = $this->table('s_wheel_spins', [
            'id'          => 'id',
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'encoding'    => 'utf8mb4',
            'collation'   => 'utf8mb4_unicode_ci',
            'signed'      => false,
        ]);

        $table->addColumn('user_id', 'integer', [
                'signed' => false,
                'null'   => false,
                'comment'=> 'ID пользователя',
            ])
            ->addColumn('started_at', 'datetime', [
                'null' => false,
            ])
            ->addColumn('finished_at', 'datetime', [
                'null' => true,
            ])
            ->addColumn('status', 'enum', [
                'values'  => ['started', 'finished', 'error'],
                'default' => 'started',
                'null'    => false,
            ])
            ->addColumn('sector_index', 'integer', [
                'limit'  => MysqlAdapter::INT_TINY, // 0..255
                'signed' => false,
                'null'   => true,
                'comment'=> 'Индекс сектора 0..N-1',
            ])
            ->addColumn('result_type', 'enum', [
                'values' => ['nothing','discount','gift','jackpot','multiplier','bonus'],
                'null'   => true,
            ])
            ->addColumn('result_value', 'integer', [
                'null'   => true,
                'comment'=> 'Сумма скидки/гифта и т.п.',
            ])
            ->addColumn('bonus_spin', 'boolean', [
                'default' => 0,
                'null'    => true,
                'comment' => 'Это бонусный спин?',
            ])
            ->addColumn('multiplier_pending', 'boolean', [
                'default' => 0,
                'null'    => true,
                'comment' => 'Выдан ли x2 и ждём применения',
            ])
            ->addColumn('multiplier_applied', 'boolean', [
                'default' => 0,
                'null'    => true,
            ])
            ->addColumn('price', 'integer', [
                'default' => 100,
                'null'    => true,
                'comment' => 'Цена спина (₽)',
            ])
            ->addColumn('rand_u', 'decimal', [
                'precision' => 10,
                'scale'     => 8,
                'null'      => true,
                'comment'   => 'Случайное U(0,1) для воспроизводимости',
            ])
            ->addColumn('weights_json', 'json', [
                'null'    => true,
                'comment' => 'Слепок весов при розыгрыше',
            ])
            ->addColumn('ip', 'string', [
                'limit' => 45,   // IPv6 ок
                'null'  => true,
            ])
            ->addColumn('ua', 'string', [
                'limit' => 255,
                'null'  => true,
            ])
            ->addColumn('meta_json', 'json', [
                'null' => true,
            ])
            ->addIndex(['user_id', 'started_at'], [
                'name' => 'user_id_started',
            ])
            ->create();
    }
}
