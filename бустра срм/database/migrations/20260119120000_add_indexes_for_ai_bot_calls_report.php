<?php

use Phinx\Migration\AbstractMigration;

final class AddIndexesForAiBotCallsReport extends AbstractMigration
{
    public function up(): void
    {
        $commentsTable = $this->table('s_comments');
        if (!$commentsTable->hasIndex(['block', 'created', 'id'])) {
            $commentsTable->addIndex(['block', 'created', 'id'], ['name' => 'idx_block_created_id']);
        }
        $commentsTable->save();

        $smsMessagesTable = $this->table('s_sms_messages');
        if (!$smsMessagesTable->hasIndex(['user_id', 'type', 'send_status', 'created'])) {
            $smsMessagesTable->addIndex(['user_id', 'type', 'send_status', 'created'], ['name' => 'idx_user_type_status_created']);
        }
        $smsMessagesTable->save();

        $this->execute('ANALYZE TABLE s_comments');
        $this->execute('ANALYZE TABLE s_sms_messages');
    }

    public function down(): void
    {
        $commentsTable = $this->table('s_comments');
        if ($commentsTable->hasIndex(['block', 'created', 'id'])) {
            $commentsTable->removeIndex(['block', 'created', 'id']);
        }
        $commentsTable->save();

        $smsMessagesTable = $this->table('s_sms_messages');
        if ($smsMessagesTable->hasIndex(['user_id', 'type', 'send_status', 'created'])) {
            $smsMessagesTable->removeIndex(['user_id', 'type', 'send_status', 'created']);
        }
        $smsMessagesTable->save();
    }
}
