<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateComplaintTopicsTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('s_complaint_topics', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => 'Complaint topics dictionary',
        ]);

        $table->addColumn('id', 'integer', [
            'identity' => true,
        ])
        ->addColumn('organization_id', 'integer', [
            'null' => true,
            'comment' => 'Organization ID for multi-tenancy, NULL for global topics',
        ])
        ->addColumn('name', 'string', [
            'limit' => 255,
            'null' => false,
            'comment' => 'Topic name in Russian',
        ])
        ->addColumn('yandex_goal_id', 'string', [
            'limit' => 100,
            'null' => false,
            'comment' => 'Yandex Metrika goal identifier',
        ])
        ->addColumn('sort_order', 'integer', [
            'null' => false,
            'default' => 0,
            'comment' => 'Display order',
        ])
        ->addColumn('is_active', 'boolean', [
            'null' => false,
            'default' => 1,
            'comment' => 'Active status',
        ])
        ->addColumn('created_at', 'datetime', [
            'null' => false,
            'default' => 'CURRENT_TIMESTAMP',
        ])
        ->addColumn('updated_at', 'datetime', [
            'null' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'update' => 'CURRENT_TIMESTAMP',
        ])
        ->addIndex(['organization_id', 'is_active'], ['name' => 'idx_organization_active'])
        ->addIndex(['sort_order'], ['name' => 'idx_sort_order'])
        ->create();

        $this->table('s_complaint_topics')->insert([
            [
                'id' => 1,
                'organization_id' => null,
                'name' => 'получение займа',
                'yandex_goal_id' => 'complaint_reason_goal1',
                'sort_order' => 1,
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'organization_id' => null,
                'name' => 'начисления по займу',
                'yandex_goal_id' => 'complaint_reason_goal2',
                'sort_order' => 2,
                'is_active' => 1,
            ],
            [
                'id' => 3,
                'organization_id' => null,
                'name' => 'программное обеспечение',
                'yandex_goal_id' => 'complaint_reason_goal3',
                'sort_order' => 3,
                'is_active' => 1,
            ],
            [
                'id' => 4,
                'organization_id' => null,
                'name' => 'процедура взыскания',
                'yandex_goal_id' => 'complaint_reason_goal4',
                'sort_order' => 4,
                'is_active' => 1,
            ],
            [
                'id' => 5,
                'organization_id' => null,
                'name' => 'оспаривание КИ / мошенничество',
                'yandex_goal_id' => 'complaint_reason_goal5',
                'sort_order' => 5,
                'is_active' => 1,
            ],
            [
                'id' => 6,
                'organization_id' => null,
                'name' => 'иное',
                'yandex_goal_id' => 'complaint_reason_goal6',
                'sort_order' => 6,
                'is_active' => 1,
            ],
        ])->save();

        // Alter s_complaint table - add new columns
        $complaintTable = $this->table('s_complaint');

        // Add organization_id column after id
        if (!$complaintTable->hasColumn('organization_id')) {
            $complaintTable->addColumn('organization_id', 'integer', [
                'null' => true,
                'comment' => 'Organization ID for multi-tenancy',
                'after' => 'id',
            ])->update();
        }

        // Add topic_id column after birth
        if (!$complaintTable->hasColumn('topic_id')) {
            $complaintTable->addColumn('topic_id', 'integer', [
                'null' => true,
                'comment' => 'Reference to complaint_topics table',
                'after' => 'birth',
            ])->update();
        }

        // Add indexes
        if (!$complaintTable->hasIndex(['organization_id'])) {
            $complaintTable->addIndex(['organization_id'], ['name' => 'idx_organization'])->update();
        }

        if (!$complaintTable->hasIndex(['topic_id'])) {
            $complaintTable->addIndex(['topic_id'], ['name' => 'idx_topic'])->update();
        }

        // Add foreign key
        if (!$complaintTable->hasForeignKey('topic_id')) {
            $complaintTable->addForeignKey('topic_id', 's_complaint_topics', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
                'constraint' => 'fk_complaint_topic',
            ])->update();
        }

        // Migrate existing data: update topic_id based on topic name
        $this->execute("
            UPDATE s_complaint c
            INNER JOIN s_complaint_topics ct ON c.topic = ct.name
            SET c.topic_id = ct.id
            WHERE c.topic_id IS NULL AND c.topic IS NOT NULL
        ");
    }

    public function down(): void
    {
        // Remove foreign key and indexes from s_complaint
        $complaintTable = $this->table('s_complaint');

        if ($complaintTable->hasForeignKey('topic_id')) {
            $complaintTable->dropForeignKey('topic_id')->save();
        }

        if ($complaintTable->hasIndex(['topic_id'])) {
            $complaintTable->removeIndex(['topic_id'])->save();
        }

        if ($complaintTable->hasIndex(['organization_id'])) {
            $complaintTable->removeIndex(['organization_id'])->save();
        }

        // Remove columns
        if ($complaintTable->hasColumn('topic_id')) {
            $complaintTable->removeColumn('topic_id')->save();
        }

        if ($complaintTable->hasColumn('organization_id')) {
            $complaintTable->removeColumn('organization_id')->save();
        }

        // Drop s_complaint_topics table
        $this->table('s_complaint_topics')->drop()->save();
    }
}
