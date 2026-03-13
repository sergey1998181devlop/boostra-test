<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdatedVitamedAndStarOracleTariffs extends AbstractMigration
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
        // 1. Добавляем поле tariff в s_vita_med_conditions
        $this->table('s_vita_med_conditions')
            ->addColumn('tariff', 'string', [
                'limit' => 50,
                'null' => true,
                'default' => null
            ])
            ->update();

        // 2. Обновляем существующие записи
        $this->getQueryBuilder()
            ->update('s_vita_med_conditions')
            ->set('tariff', 'base')
            ->where(['tariff IS' => null])
            ->execute();

        // 3. Добавляем новые записи для prolongation
        $prolongationRecords = [
            [1, 7001, 17000, 600, null, 'prolongation'],
            [1, 17001, 25000, 1300, null, 'prolongation'],
            [1, 25001, 40000, 2200, null, 'prolongation'],
            [1, 40001, 50000, 2625, null, 'prolongation'],
            [1, 50001, 60000, 2975, null, 'prolongation'],
            [1, 60001, 70000, 3050, null, 'prolongation']
        ];

        foreach ($prolongationRecords as $record) {
            $this->table('s_vita_med_conditions')->insert([
                'is_new' => $record[0],
                'from_amount' => $record[1],
                'to_amount' => $record[2],
                'price' => $record[3],
                'license_key_days' => $record[4],
                'tariff' => $record[5]
            ])->saveData();
        }

        // 4. Добавляем поле tariff в s_star_oracle_conditions
        $this->table('s_star_oracle_conditions')
            ->addColumn('tariff', 'string', [
                'limit' => 50,
                'null' => true,
                'default' => null
            ])
            ->update();

        // 5. Обновляем существующие записи
        $this->getQueryBuilder()
            ->update('s_star_oracle_conditions')
            ->set('tariff', 'base')
            ->where(['tariff IS' => null])
            ->execute();

        // 6. Добавляем записи для prolongation
        $starOracleRecords = [
            [1, 1, 7000, 300, 'prolongation'],
            [1, 13001, 17000, 300, 'prolongation']
        ];

        foreach ($starOracleRecords as $record) {
            $this->table('s_star_oracle_conditions')->insert([
                'is_new' => $record[0],
                'from_amount' => $record[1],
                'to_amount' => $record[2],
                'price' => $record[3],
                'tariff' => $record[4]
            ])->saveData();
        }

        // 7. Добавляем записи в s_tv_medical
        $prices = range(1900, 5100, 200);
        foreach ($prices as $price) {
            $this->table('s_tv_medical')->insert([
                'name' => 'Премиум',
                'price' => $price,
                'days' => null,
                'api_doc_id' => null,
                'description' => null
            ])->saveData();
        }
    }

    public function down(): void
    {
        // Удаляем добавленные записи из s_tv_medical
        $prices = range(1900, 5100, 200);
        foreach ($prices as $price) {
            $this->getQueryBuilder()
                ->delete('s_tv_medical')
                ->where(['name' => 'Премиум', 'price' => $price])
                ->execute();
        }

        // Удаляем записи prolongation из s_star_oracle_conditions
        $starOracleRecords = [
            [1, 1, 7000, 300, 'prolongation'],
            [1, 13001, 17000, 300, 'prolongation']
        ];

        foreach ($starOracleRecords as $record) {
            $this->getQueryBuilder()
                ->delete('s_star_oracle_conditions')
                ->where([
                    'is_new' => $record[0],
                    'from_amount' => $record[1],
                    'to_amount' => $record[2],
                    'price' => $record[3],
                    'tariff' => $record[4]
                ])
                ->execute();
        }

        // Удаляем поле tariff из s_star_oracle_conditions
        $this->table('s_star_oracle_conditions')
            ->removeColumn('tariff')
            ->update();

        // Удаляем записи prolongation из s_vita_med_conditions
        $prolongationRecords = [
            [1, 7001, 17000, 600, null, 'prolongation'],
            [1, 17001, 25000, 1300, null, 'prolongation'],
            [1, 25001, 40000, 2200, null, 'prolongation'],
            [1, 40001, 50000, 2625, null, 'prolongation'],
            [1, 50001, 60000, 2975, null, 'prolongation'],
            [1, 60001, 70000, 3050, null, 'prolongation']
        ];

        foreach ($prolongationRecords as $record) {
            $this->getQueryBuilder()
                ->delete('s_vita_med_conditions')
                ->where([
                    'is_new' => $record[0],
                    'from_amount' => $record[1],
                    'to_amount' => $record[2],
                    'price' => $record[3],
                    'license_key_days' => $record[4],
                    'tariff' => $record[5]
                ])
                ->execute();
        }

        // Удаляем поле tariff из s_vita_med_conditions
        $this->table('s_vita_med_conditions')
            ->removeColumn('tariff')
            ->update();
    }
}
