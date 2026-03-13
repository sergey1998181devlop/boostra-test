<?php


use Phinx\Seed\AbstractSeed;

class TestTableSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Тестовая запись 1',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Тестовая запись 2',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Тестовая запись 3',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Тестовая запись 4',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Тестовая запись 5',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $table = $this->table('test_table_simple');
        $table->insert($data)->save();
    }
}
