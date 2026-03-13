<?php
use Phinx\Migration\AbstractMigration;


final class FullSchema extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');

        if (!$this->hasTable('application_tokens')) {
            $table = $this->table('application_tokens', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('token', 'text', ['null' => false, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('abilities', 'json', ['null' => true, 'default' => null]);
            $table->addColumn('expired_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('enabled', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '(1)', 'signed' => false]);
            $table->addColumn('app', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'encoding' => 'latin1', 'collation' => 'latin1_swedish_ci']);
            $table->create();
        }

        if (!$this->hasTable('approved_to_vox')) {
            $table = $this->table('approved_to_vox', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'biginteger', ['null' => false]);
            $table->addColumn('send_time', 'datetime', ['null' => false]);
            $table->addIndex(['order_id'], ['name' => 'approved_to_vox_s_orders_null_fk']);
            $table->addForeignKey(['order_id'], 's_orders', ['id'], ['constraint' => 'approved_to_vox_s_orders_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('automation_fails')) {
            $table = $this->table('automation_fails', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('is_active', 'boolean', ['null' => false]);
            $table->addColumn('is_auto_active', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '1', 'comment' => 'Активируется автоматически по триггеру']);
            $table->addColumn('show_at', 'string', ['limit' => 255, 'null' => false, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('type', 'string', ['limit' => 255, 'null' => false, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('text', 'text', ['null' => false, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('last_notification_at', 'datetime', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('axi_token')) {
            $table = $this->table('axi_token', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('token', 'text', ['null' => false]);
            $table->addColumn('login', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('b2p_bank_list')) {
            $table = $this->table('b2p_bank_list', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('latinTitle', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('title', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('b2p_cards')) {
            $table = $this->table('b2p_cards', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('base_card', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('pan', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('expdate', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('approval_code', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('token', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('operation_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('operation', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('register_id', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('transaction_id', 'integer', ['null' => false]);
            $table->addColumn('file_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('deleted', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('autodebit', 'boolean', ['null' => false, 'default' => '1']);
            $table->addColumn('autodebit_save', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('bin_issuer', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('verification_status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('organization_id', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '1']);
            $table->addColumn('checked', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('next_recurrent_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('deleted_by_client', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('deleted_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('deleted_by_client_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['pan'], ['name' => 'pan']);
            $table->addIndex(['file_id'], ['name' => 'file_id']);
            $table->addIndex(['transaction_id'], ['name' => 'transaction_id']);
            $table->addIndex(['deleted'], ['name' => 'deleted']);
            $table->addIndex(['autodebit'], ['name' => 'autodebit']);
            $table->addIndex(['organization_id'], ['name' => 'organization_id']);
            $table->addIndex(['checked'], ['name' => 'checked']);
            $table->create();
        }

        if (!$this->hasTable('b2p_insures')) {
            $table = $this->table('b2p_insures', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('insurance_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('p2pcredit_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('transaction_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('register_id', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('operation_id', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('body', 'text', ['null' => false]);
            $table->addColumn('response', 'text', ['null' => false]);
            $table->addColumn('status', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('complete_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false]);
            $table->addColumn('return_status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('return_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['order_id'], ['name' => 'contract_id']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['transaction_id'], ['name' => 'transaction_id']);
            $table->addIndex(['p2pcredit_id'], ['name' => 'p2pcredit_id']);
            $table->addIndex(['insurance_id'], ['name' => 'insurance_id']);
            $table->create();
        }

        if (!$this->hasTable('b2p_p2pcredits')) {
            $table = $this->table('b2p_p2pcredits', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('register_id', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('operation_id', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('body', 'text', ['null' => false]);
            $table->addColumn('response', 'text', ['null' => false]);
            $table->addColumn('status', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('complete_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('sent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('send_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('nbki_ready', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('likezaim_enabled', 'boolean', ['null' => false, 'default' => '0']);
            $table->addIndex(['order_id'], ['name' => 'contract_id']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['sent'], ['name' => 'sent']);
            $table->addIndex(['status'], ['name' => 'status']);
            $table->addIndex(['nbki_ready'], ['name' => 'nbki_ready']);
            $table->addIndex(['likezaim_enabled'], ['name' => 'likezaim_enabled']);
            $table->addIndex(['complete_date'], ['name' => 'complete_date']);
            $table->addIndex(['register_id'], ['name' => 'register_id']);
            $table->create();
        }

        if (!$this->hasTable('b2p_payments')) {
            $table = $this->table('b2p_payments', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('order_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('contract_number', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('card_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false]);
            $table->addColumn('split_data', 'string', ['limit' => 512, 'null' => true, 'default' => '']);
            $table->addColumn('insure', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('fee', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('body_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('percents_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('prolongation', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('calc_percents', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Нужно ли начислить проценты по беспроцентому займу по этой оплате']);
            $table->addColumn('asp', 'string', ['limit' => 10, 'null' => false, 'default' => '']);
            $table->addColumn('created', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('payment_type', 'enum', ['values' => ['credit_rating', 'debt', 'credit_rating_for_nk', 'credit_rating_after_rejection', 'tv_medical', 'multipolis', 'credit_doctor', 'penalty_credit_doctor', 'refuser', 'recurring'], 'null' => true, 'default' => null]);
            $table->addColumn('organization_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('sector', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true, 'default' => null]);
            $table->addColumn('register_id', 'biginteger', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('operation_id', 'biginteger', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('reference', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('description', 'string', ['limit' => 512, 'null' => false, 'default' => '']);
            $table->addColumn('payment_link', 'string', ['limit' => 1024, 'null' => false, 'default' => '']);
            $table->addColumn('reason_code', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0']);
            $table->addColumn('body', 'text', ['null' => true]);
            $table->addColumn('callback_response', 'text', ['null' => true]);
            $table->addColumn('sent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('send_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('recurrent_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('nbki_ready', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('grace_payment', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('card_pan', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('operation_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('dop1c_sent', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Отправлена ли доп.услуга в 1с']);
            $table->addColumn('chdp', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Частичное досрочное погашение - используется для IL займов']);
            $table->addColumn('pdp', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Полное досрочное погашение - используется для IL займов']);
            $table->addColumn('contract_payment', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('is_sbp', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('refinance', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('create_from', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('discount_amount', 'integer', ['null' => true, 'default' => '0', 'comment' => 'Сумма скидки']);
            $table->addIndex(['sent'], ['name' => 'sent']);
            $table->addIndex(['sector'], ['name' => 'sector']);
            $table->addIndex(['contract_number'], ['name' => 'contract_number']);
            $table->addIndex(['register_id'], ['name' => 'register_id']);
            $table->addIndex(['reason_code'], ['name' => 'reason_code']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['card_id'], ['name' => 'card_id']);
            $table->addIndex(['payment_type'], ['name' => 'b2p_payments_payment_type_index']);
            $table->addIndex(['recurrent_id'], ['name' => 'recurrent_id']);
            $table->addIndex(['nbki_ready'], ['name' => 'nbki_ready']);
            $table->addIndex(['dop1c_sent'], ['name' => 'dop1c_sent']);
            $table->addIndex(['payment_type', 'reason_code', 'sent'], ['name' => 'payment_type']);
            $table->addIndex(['organization_id'], ['name' => 'organization_id']);
            $table->addIndex(['operation_id'], ['name' => 'operation_id']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->create();
        }

        if (!$this->hasTable('b2p_sbp_accounts')) {
            $table = $this->table('b2p_sbp_accounts', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'biginteger', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'biginteger', ['null' => false]);
            $table->addColumn('qrcId', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('subscription_state', 'string', ['limit' => 64, 'null' => false]);
            $table->addColumn('token', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('member_id', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('signature', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => false]);
            $table->addColumn('deleted', 'boolean', ['null' => false]);
            $table->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('b2p_sbp_accounts_logs')) {
            $table = $this->table('b2p_sbp_accounts_logs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('card_id', 'integer', ['null' => false]);
            $table->addColumn('action', 'enum', ['values' => ['binding', 'success_payment_card', 'error_payment_card', 'delete_card_client', 'delete_card_manager', 'autodebit_on', 'autodebit_off', 'success_attach_sbp', 'error_attach_sbp', 'recurring_on_sbp', 'recurring_off_sbp', 'success_payment_sbp', 'success_payment_sbp_recurring', 'error_payment_sbp', 'error_payment_sbp_recurring'], 'null' => false]);
            $table->addColumn('date', 'datetime', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('b2p_sbp_issuance_log')) {
            $table = $this->table('b2p_sbp_issuance_log', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Таблица проверки СБП счетов клиентов для выплаты']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'biginteger', ['null' => false]);
            $table->addColumn('order_id', 'biginteger', ['null' => false]);
            $table->addColumn('member_id', 'string', ['limit' => 100, 'null' => false, 'comment' => 'ID банка', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('phone', 'string', ['limit' => 30, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('description', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('status', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('sbp_account_id', 'biginteger', ['null' => true, 'default' => null]);
            $table->addColumn('b2p_order_id', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('precheck_id', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'ID зарегистрированного заказа в b2p', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('pam', 'string', ['limit' => 200, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('request', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('response', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['user_id'], ['name' => 'b2p_sbp_issuance_accounts_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('b2p_transactions')) {
            $table = $this->table('b2p_transactions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('type', 'enum', ['values' => ['refund_credit_doctor', 'refund_multipolis', 'refund_tv_medical', 'recompense_credit_doctor', 'recompense_multipolis', 'recompense_tv_medical', 'recompense_star_oracle', 'refund_star_oracle'], 'null' => true, 'default' => null]);
            $table->addColumn('amount', 'integer', ['null' => false]);
            $table->addColumn('sector', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => false]);
            $table->addColumn('register_id', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('contract_number', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('reference', 'string', ['limit' => 40, 'null' => false]);
            $table->addColumn('description', 'text', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('operation', 'biginteger', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('reason_code', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('state', 'string', ['limit' => 15, 'null' => true, 'default' => null, 'comment' => '']);
            $table->addColumn('body', 'text', ['null' => false]);
            $table->addColumn('callback_response', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM, 'null' => true]);
            $table->addColumn('sms', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('prolongation', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('insurance_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('loan_body_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('loan_percents_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('loan_charge_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('loan_peni_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('commision_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('card_pan', 'string', ['limit' => 20, 'null' => false, 'default' => '']);
            $table->addColumn('operation_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['sector'], ['name' => 'sector']);
            $table->addIndex(['register_id'], ['name' => 'register_id']);
            $table->addIndex(['contract_number'], ['name' => 'b2p_transactions_contract_number_index']);
            $table->addIndex(['order_id'], ['name' => 'b2p_transactions_order_id_index']);
            $table->addIndex(['reference'], ['name' => 'reference']);
            $table->addIndex(['operation'], ['name' => 'operation']);
            $table->addIndex(['reason_code'], ['name' => 'reason_code']);
            $table->addIndex(['type'], ['name' => 'type']);
            $table->addIndex(['user_id', 'order_id'], ['name' => 'user_id_2']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->create();
        }

        if (!$this->hasTable('calls_blacklist')) {
            $table = $this->table('calls_blacklist', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('created', 'date', ['null' => false]);
            $table->addColumn('days', 'integer', ['null' => false]);
            $table->addColumn('unblock_day', 'date', ['null' => false]);
            $table->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id', 'deleted_at'], ['name' => 'user_id']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 'calls_blacklist_s_users_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('cession_requests')) {
            $table = $this->table('cession_requests', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('request_date', 'date', ['null' => false]);
            $table->addColumn('full_name_with_birth', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('contract_number', 'string', ['limit' => 100, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('shkd_number', 'string', ['limit' => 100, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('contract_date', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('contract_form', 'enum', ['values' => ['Займ', 'Доп.услуга'], 'null' => false, 'default' => 'Займ', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('cedent', 'enum', ['values' => ['Алфавит', 'Аквариус', 'Бустра', 'Акадо', 'ФР', 'Дивэлопмэнт'], 'null' => false, 'default' => 'Алфавит', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('counterparty', 'string', ['null' => true, 'default' => 'Сириус', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('transfer_date', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('importance', 'string', ['null' => false, 'default' => 'Не', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('execution_status', 'string', ['null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('client_replace_status', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('comments', 'text', ['null' => true, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('extra_actions', 'text', ['null' => true, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('email', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('source', 'enum', ['values' => ['manual', 'auto'], 'null' => true, 'default' => 'auto', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('collector_calls')) {
            $table = $this->table('collector_calls', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('collector_id', 'integer', ['null' => false]);
            $table->addColumn('date_start', 'datetime', ['null' => false]);
            $table->addColumn('date_end', 'datetime', ['null' => false]);
            $table->addColumn('calls_count', 'integer', ['null' => false]);
            $table->addColumn('success_calls_count', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('income_app_rosstatregion')) {
            $table = $this->table('income_app_rosstatregion', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false]);
            $table->addColumn('name', 'string', ['limit' => 200, 'null' => true, 'default' => null, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->create();
        }

        if (!$this->hasTable('manager_company')) {
            $table = $this->table('manager_company', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('company', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('dnc_number', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('plus', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('minus', 'string', ['limit' => 10, 'null' => true, 'default' => null]);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addForeignKey(['manager_id'], 's_managers', ['id'], ['constraint' => 'manager_id', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('managers_schedule')) {
            $table = $this->table('managers_schedule', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('date', 'string', ['limit' => 100, 'null' => false, 'default' => '0']);
            $table->addColumn('plus', 'boolean', ['null' => true, 'default' => '0']);
            $table->addIndex(['manager_id'], ['name' => 'managers_schedule_s_managers_null_fk']);
            $table->addForeignKey(['manager_id'], 's_managers', ['id'], ['constraint' => 'managers_schedule_s_managers_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('messenger_chats')) {
            $table = $this->table('messenger_chats', ['id' => false, 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('uid', 'string', ['limit' => 40, 'null' => true, 'default' => null]);
            $table->addColumn('chat_id', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('is_client', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('sender_id', 'string', ['limit' => 40, 'null' => true, 'default' => null]);
            $table->addColumn('body', 'binary', ['null' => true]);
            $table->addColumn('message_id', 'string', ['limit' => 40, 'null' => true, 'default' => null]);
            $table->addColumn('client_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('status', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('messenger_type', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('date_create', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_update', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['id'], ['name' => 'id', 'unique' => true]);
            $table->addIndex(['uid'], ['name' => 'uid_ux', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('missed_calls')) {
            $table = $this->table('missed_calls', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('attempts_count', 'integer', ['null' => false]);
            $table->addColumn('interval_time', 'integer', ['null' => false]);
            $table->addColumn('last_send', 'datetime', ['null' => false]);
            $table->addColumn('attempts_made', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('created', 'date', ['null' => false]);
            $table->addColumn('robo_number', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('old_s_asp_to_zaim')) {
            $table = $this->table('old_s_asp_to_zaim', ['id' => false, 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Тут хранятся подписи займов на частоту взаимодействия']);
            $table->addColumn('zaim_number', 'string', ['limit' => 64, 'null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('sms_code', 'integer', ['null' => false]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('file_name', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addIndex(['zaim_number'], ['name' => 's_asp_to_zaim_zaim_number_1_uindex', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('payment_resource_log')) {
            $table = $this->table('payment_resource_log', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Таблица хранения логов для аналитики коммуникаций с должниками']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'biginteger', ['null' => false]);
            $table->addColumn('event', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('source', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('params', 'json', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'datetime', ['null' => false]);
            $table->addIndex(['event'], ['name' => 'event']);
            $table->addIndex(['source'], ['name' => 'source']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->create();
        }

        if (!$this->hasTable('pdn_calculation')) {
            $table = $this->table('pdn_calculation', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Заявки, для которых произвели расчет ПДН']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_uid', 'string', ['limit' => 40, 'null' => false, 'default' => '', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('contract_number', 'string', ['limit' => 20, 'null' => false, 'default' => '', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('date_create', 'date', ['null' => false]);
            $table->addColumn('success', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('smp', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('smp1', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('smp2', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('smd', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('income_base', 'float', ['null' => true, 'default' => null, 'comment' => 'Анкетный доход']);
            $table->addColumn('income_rosstat', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('pdn', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('pdn_calculation_type', 'integer', ['null' => true, 'default' => null, 'comment' => 'Тип расчета ПДН']);
            $table->addColumn('fakt_address', 'string', ['limit' => 500, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('amount', 'integer', ['null' => true, 'default' => null, 'comment' => 'Сумма займа']);
            $table->addColumn('issuance_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('amp_report_link', 'string', ['limit' => 500, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('credit_history_link', 'string', ['limit' => 500, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['order_id'], ['name' => 's_pdn_calculation_order_id_uindex']);
            $table->addIndex(['order_uid'], ['name' => 's_pdn_calculation_order_uid_uindex']);
            $table->addIndex(['contract_number'], ['name' => 'contract_number']);
            $table->create();
        }

        if (!$this->hasTable('pdn_calculation_finlab')) {
            $table = $this->table('pdn_calculation_finlab', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Заявки, для которых произвели расчет ПДН']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_uid', 'string', ['limit' => 40, 'null' => false, 'default' => '', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('contract_number', 'string', ['limit' => 20, 'null' => false, 'default' => '', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('date_create', 'date', ['null' => false]);
            $table->addColumn('success', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('smp', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('smp1', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('smp2', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('smd', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('income_base', 'float', ['null' => true, 'default' => null, 'comment' => 'Анкетный доход']);
            $table->addColumn('income_rosstat', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('pdn', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('pdn_calculation_type', 'integer', ['null' => true, 'default' => null, 'comment' => 'Тип расчета ПДН']);
            $table->addColumn('fakt_address', 'string', ['limit' => 500, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('amount', 'integer', ['null' => true, 'default' => null, 'comment' => 'Сумма займа']);
            $table->addColumn('issuance_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('amp_report_link', 'string', ['limit' => 500, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('credit_history_link', 'string', ['limit' => 500, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['contract_number'], ['name' => 'contract_number']);
            $table->addIndex(['order_id'], ['name' => 's_pdn_calculation_order_id_uindex']);
            $table->addIndex(['order_uid'], ['name' => 's_pdn_calculation_order_uid_uindex']);
            $table->create();
        }

        if (!$this->hasTable('prolong_sms_log')) {
            $table = $this->table('prolong_sms_log', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 99, 'null' => false]);
            $table->addColumn('status', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('dates', 'datetime', ['null' => false]);
            $table->addColumn('sms_id', 'string', ['limit' => 99, 'null' => false]);
            $table->create();
        }

        if (!$this->hasTable('promocodes')) {
            $table = $this->table('promocodes', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('code', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('days_to_expired', 'integer', ['null' => true, 'default' => '1']);
            $table->addColumn('deleted_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addIndex(['deleted_at'], ['name' => 'promocodes_deleted_at_index']);
            $table->create();
        }

        if (!$this->hasTable('questionsForCustomers')) {
            $table = $this->table('questionsForCustomers', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('idSurvey', 'integer', ['null' => false, 'default' => '0', 'comment' => 'id варианта ответа']);
            $table->addColumn('Text', 'text', ['null' => false, 'comment' => ' Текст вопроса', 'encoding' => 'utf8mb3']);
            $table->addColumn('typeTask', 'string', ['limit' => 50, 'null' => false, 'comment' => 'Тип задачи', 'encoding' => 'utf8mb3']);
            $table->create();
        }

        if (!$this->hasTable('regions')) {
            $table = $this->table('regions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('code', 'string', ['limit' => 2, 'null' => false, 'comment' => 'Код региона по списку МВД', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('district', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['code'], ['name' => 'regions_code_uindex', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_about_as_articles')) {
            $table = $this->table('s_about_as_articles', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Статьи о нас на главной']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('title', 'string', ['limit' => 512, 'null' => true, 'default' => null]);
            $table->addColumn('description', 'text', ['null' => true]);
            $table->addColumn('logo', 'text', ['null' => true]);
            $table->addColumn('total_like', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('status', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('url', 'text', ['null' => true]);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_additional_service_returns')) {
            $table = $this->table('s_additional_service_returns', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('service_type', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('return_status', 'integer', ['null' => false]);
            $table->addColumn('return_date', 'datetime', ['null' => false]);
            $table->addColumn('return_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false]);
            $table->addColumn('return_transaction_id', 'integer', ['null' => false]);
            $table->addColumn('return_sent', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false]);
            $table->addColumn('return_by_manager_id', 'integer', ['null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_amo_tokens')) {
            $table = $this->table('s_amo_tokens', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'токены AmoCrm']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('access_token', 'text', ['null' => true]);
            $table->addColumn('token_type', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('refresh_token', 'text', ['null' => true]);
            $table->addColumn('expires_in', 'integer', ['null' => true, 'default' => null, 'comment' => 'первоначальное время жизни (сек)']);
            $table->addColumn('date_update', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_appeals')) {
            $table = $this->table('s_appeals', ['id' => false, 'primary_key' => ['Id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('Id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('AppealDate', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('Text', 'text', ['null' => false]);
            $table->addColumn('Them', 'string', ['limit' => 250, 'null' => false]);
            $table->addColumn('Phone', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('Email', 'string', ['limit' => 250, 'null' => true, 'default' => null]);
            $table->addColumn('ToEmail', 'string', ['limit' => 250, 'null' => false]);
            $table->addColumn('TicketId', 'integer', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_approve_amount_settings')) {
            $table = $this->table('s_approve_amount_settings', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'BOOSTRARU-3303']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('utm_source', 'string', ['limit' => 30, 'null' => false, 'comment' => 'utm_source заявки из s_orders', 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('utm_medium', 'string', ['limit' => 30, 'null' => false, 'comment' => 'utm_medium заявки из s_orders', 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('have_close_credits', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0', 'comment' => '0 - НК, 1 - ПК', 'signed' => false]);
            $table->addColumn('min_ball', 'integer', ['null' => false, 'comment' => 'Минимальный балл скористы', 'signed' => false]);
            $table->addColumn('amount', 'integer', ['null' => false, 'comment' => 'Рекомендуемая сумма', 'signed' => false]);
            $table->addIndex(['utm_source', 'utm_medium', 'have_close_credits'], ['name' => 'utm_source_have_close_credits', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_approve_amount_settings_logs')) {
            $table = $this->table('s_approve_amount_settings_logs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'BOOSTRARU-3303']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('created', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('type', 'string', ['limit' => 100, 'null' => false, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('old_values', 'text', ['null' => false, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('new_values', 'text', ['null' => false, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('setting_id', 'integer', ['null' => false]);
            $table->addColumn('utm_source', 'string', ['limit' => 30, 'null' => false, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('utm_medium', 'string', ['limit' => 30, 'null' => false, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['type'], ['name' => 'type']);
            $table->addIndex(['setting_id'], ['name' => 'order_id']);
            $table->create();
        }

        if (!$this->hasTable('s_articles')) {
            $table = $this->table('s_articles', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('slug', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('title', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('content', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('description', 'text', ['null' => true, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('keywords', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('author', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('published', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_asp_to_user')) {
            $table = $this->table('s_asp_to_user', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Хранение различных АСП пользователя']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('phone_mobile', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('asp', 'string', ['limit' => 16, 'null' => false]);
            $table->addColumn('asp_type', 'string', ['limit' => 32, 'null' => false]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['date_added'], ['name' => 's_asp_to_user_date_added_index']);
            $table->addIndex(['asp_type', 'phone_mobile'], ['name' => 's_asp_to_user_asp_type_phone_mobile_index']);
            $table->create();
        }

        if (!$this->hasTable('s_asp_to_zaim')) {
            $table = $this->table('s_asp_to_zaim', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('zaim_number', 'string', ['limit' => 64, 'null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('sms_code', 'integer', ['null' => false]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('file_name', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addIndex(['zaim_number'], ['name' => 's_asp_to_zaim_zaim_number_1_uindex', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_audits')) {
            $table = $this->table('s_audits', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('status', 'string', ['limit' => 20, 'null' => false, 'comment' => 'new, process, completed']);
            $table->addColumn('types', 'text', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->create();
        }

        if (!$this->hasTable('s_auth_service_errors')) {
            $table = $this->table('s_auth_service_errors', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Лог ошибок авторизации по сервисам']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('auth_type', 'string', ['limit' => 32, 'null' => false, 'comment' => 'Тип сервиса']);
            $table->addColumn('request_uid', 'string', ['limit' => 128, 'null' => false, 'comment' => 'UID запроса, для поиска по логам']);
            $table->addColumn('session_uid', 'string', ['limit' => 128, 'null' => true, 'default' => null, 'comment' => 'Ключ для Тид, по которому связываем запросы по одному пользователю']);
            $table->addColumn('main', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'comment' => 'Общий массив данных']);
            $table->addColumn('fio', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('phone', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('birth_place', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('birth_date', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('gender', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('passport', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('registration_address', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['created_at', 'auth_type'], ['name' => 's_auth_service_errors_created_at_auth_type_index']);
            $table->addIndex(['session_uid'], ['name' => 's_auth_service_errors_session_uid_index']);
            $table->create();
        }

        if (!$this->hasTable('s_auth_users')) {
            $table = $this->table('s_auth_users', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Фиксируем входы из сервисов']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('type', 'string', ['limit' => 128, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['user_id'], ['name' => 's_auth_users_s_users_id_fk']);
            $table->addIndex(['created_at', 'type'], ['name' => 's_auth_users_created_at_type_index']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_auth_users_s_users_id_fk', 'delete' => 'CASCADE']);
            $table->create();
        }

        if (!$this->hasTable('s_authcodes')) {
            $table = $this->table('s_authcodes', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('code', 'string', ['limit' => 10, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('chanell', 'string', ['limit' => 5, 'null' => true, 'default' => '', 'collation' => 'utf8mb4_general_ci']);
            $table->addIndex(['phone'], ['name' => 'phone']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->create();
        }

        if (!$this->hasTable('s_auto_approve_nk')) {
            $table = $this->table('s_auto_approve_nk', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('status', 'string', ['limit' => 32, 'null' => true, 'default' => 'NEW']);
            $table->addColumn('validate_scoring', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'comment' => 'Нужна ли проверка на скоринге при выполнении крона']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_cron', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('date_edit', 'datetime', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['date_cron'], ['name' => 's_auto_approve_nk_date_cron_index']);
            $table->addIndex(['status'], ['name' => 's_auto_approve_nk_status_index']);
            $table->addIndex(['user_id'], ['name' => 's_auto_approve_nk_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_average_psk')) {
            $table = $this->table('s_average_psk', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Среднерыночные значения полной стоимости потребительского кредита (займа)']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('period', 'date', ['null' => false]);
            $table->addColumn('type', 'enum', ['values' => ['ko', 'mfo'], 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('code', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('description', 'string', ['limit' => 150, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('psk', 'decimal', ['precision' => 20, 'scale' => 6, 'null' => false]);
            $table->addIndex(['type', 'code'], ['name' => 'type_code']);
            $table->addIndex(['period'], ['name' => 'period']);
            $table->create();
        }

        if (!$this->hasTable('s_axi_ltv')) {
            $table = $this->table('s_axi_ltv', ['id' => false, 'primary_key' => ['order_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('order_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('final_decision', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('stop_factors', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('countClosedLoans', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('initial_limit', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('age', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('axi_comment', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('ProductCategory', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('DeliveryOptionCode', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('ApplicationDate', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('sc_new01', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('sc_new02', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('sc_new03', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('sc_rpt01', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('sc_rpt02', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('sc_rpt03', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('final_limit', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('final_maturity', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('initial_maturity', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('created', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Дата создания записи в этой таблице']);
            $table->addColumn('updated', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Дата обновления записи в этой таблице']);
            $table->create();
        }

        if (!$this->hasTable('s_axilink')) {
            $table = $this->table('s_axilink', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('app_id', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('order_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('xml', 'text', ['null' => false]);
            $table->addColumn('created_date', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['app_id'], ['name' => 'app_id', 'unique' => true]);
            $table->addIndex(['order_id'], ['name' => 's_axilink_order_id_index_2']);
            $table->create();
        }

        if (!$this->hasTable('s_bki_questions')) {
            $table = $this->table('s_bki_questions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'biginteger', ['null' => false]);
            $table->addColumn('contract_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('attachment', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('description', 'text', ['null' => true, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('status', 'enum', ['values' => ['new', 'cancelled', 'approved'], 'null' => false, 'default' => 'new', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_blacklist')) {
            $table = $this->table('s_blacklist', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('reason', 'string', ['limit' => 300, 'null' => true, 'default' => null, 'collation' => 'utf8mb3_unicode_ci']);
            $table->addColumn('comment', 'string', ['limit' => 300, 'null' => true, 'default' => null, 'collation' => 'utf8mb3_unicode_ci']);
            $table->addColumn('created_date', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('sent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addForeignKey(['manager_id'], 's_managers', ['id'], ['constraint' => 'FK_s_managers']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 'FK_s_users', 'delete' => 'SET_NULL']);
            $table->create();
        }

        if (!$this->hasTable('s_block_sms_adv')) {
            $table = $this->table('s_block_sms_adv', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Содержит пользователей у которых заблокирована отправка рекламных смс']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('sms_type', 'string', ['limit' => 24, 'null' => true, 'default' => null, 'comment' => 'Тип смс']);
            $table->addColumn('phone', 'string', ['limit' => 16, 'null' => false]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('blocked_until', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id', 'sms_type'], ['name' => 's_block_sms_adv_pk_2', 'unique' => true]);
            $table->addIndex(['phone'], ['name' => 's_block_sms_adv_phone_index']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_block_sms_adv_s_users_id_fk', 'delete' => 'CASCADE', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_car_deposit_applications')) {
            $table = $this->table('s_car_deposit_applications', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('phone', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('email', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('car_number', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_cards_autodebit')) {
            $table = $this->table('s_cards_autodebit', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('card_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('contract_number', 'string', ['limit' => 20, 'null' => false, 'default' => '']);
            $table->addColumn('autodebit', 'boolean', ['null' => false]);
            $table->addIndex(['card_id', 'contract_number'], ['name' => 'card_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['card_id', 'order_id'], ['name' => 'card_id_2']);
            $table->addIndex(['contract_number'], ['name' => 'contract_number']);
            $table->create();
        }

        if (!$this->hasTable('s_ccprolongations_send_vox')) {
            $table = $this->table('s_ccprolongations_send_vox', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('date_send', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('user_id', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('company_id', 'string', ['limit' => 45, 'null' => true, 'default' => null]);
            $table->addColumn('type', 'string', ['limit' => 2, 'null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_cd_payments')) {
            $table = $this->table('s_cd_payments', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Платежи по Кредитному Доктору']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('full_amount', 'decimal', ['precision' => 10, 'scale' => 0, 'null' => true, 'default' => null, 'comment' => 'полная стоимость услуги']);
            $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 0, 'null' => true, 'default' => null, 'comment' => 'сумма продукта']);
            $table->addColumn('payment_id', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('save_payment_method', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0', 'comment' => 'флаг главного платежа (пост оплата)']);
            $table->addColumn('kkt_uid', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('status', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_modified', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('income_amount', 'decimal', ['precision' => 10, 'scale' => 0, 'null' => true, 'default' => null, 'comment' => 'выручка за минусом коммисии']);
            $table->addColumn('order_type_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'id тарифного плана 1-3 или 0 бесплатный']);
            $table->addColumn('filled', 'decimal', ['precision' => 10, 'scale' => 0, 'null' => true, 'default' => null, 'comment' => 'заполненность платежа, если он разбит на части']);
            $table->addColumn('sms_code', 'integer', ['null' => true, 'default' => null]);
            $table->addIndex(['payment_id'], ['name' => 's_cd_payments_payment_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_cd_save_payments')) {
            $table = $this->table('s_cd_save_payments', ['id' => false, 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'связка пост платежей после привязки']);
            $table->addColumn('payment_method_id', 'string', ['limit' => 128, 'null' => true, 'default' => null, 'comment' => 'id главного платежа']);
            $table->addColumn('payment_id', 'string', ['limit' => 128, 'null' => true, 'default' => null, 'comment' => 'id привязанного платежа']);
            $table->addIndex(['payment_method_id', 'payment_id'], ['name' => 's_cd_save_payments_payment_method_id_payment_id_uindex', 'unique' => true]);
            $table->addIndex(['payment_method_id'], ['name' => 's_cd_save_payments_payment_method_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_cdoctors')) {
            $table = $this->table('s_cdoctors', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('cdoctor_id', 'integer', ['null' => false]);
            $table->addColumn('cdoctor_status', 'string', ['limit' => 15, 'null' => false]);
            $table->addColumn('url', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('amount', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('payout', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('pdf', 'string', ['limit' => 255, 'null' => true, 'default' => '']);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addIndex(['order_id'], ['name' => 'order_id', 'unique' => true]);
            $table->addIndex(['cdoctor_status'], ['name' => 'cdoctor_status']);
            $table->addIndex(['cdoctor_id'], ['name' => 'cdoctor_id']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->create();
        }

        if (!$this->hasTable('s_changelogs')) {
            $table = $this->table('s_changelogs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('created', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('type', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('old_values', 'text', ['null' => false]);
            $table->addColumn('new_values', 'text', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('file_id', 'integer', ['null' => true, 'default' => null]);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['file_id'], ['name' => 'file_id']);
            $table->addIndex(['type'], ['name' => 'type']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->addIndex(['type', 'created'], ['name' => 'type_2']);
            $table->create();
        }

        if (!$this->hasTable('s_chats')) {
            $table = $this->table('s_chats', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('chat_type', 'string', ['limit' => 250, 'null' => true, 'default' => null]);
            $table->addColumn('user_id_in_chat', 'string', ['limit' => 250, 'null' => true, 'default' => null]);
            $table->addColumn('chat_id', 'string', ['limit' => 250, 'null' => true, 'default' => null]);
            $table->addColumn('update_id', 'string', ['limit' => 250, 'null' => true, 'default' => null]);
            $table->addColumn('status', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('message_status', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('message_id', 'string', ['limit' => 250, 'null' => true, 'default' => null]);
            $table->addColumn('text', 'text', ['null' => true]);
            $table->addColumn('user_id', 'string', ['limit' => 250, 'null' => true, 'default' => null]);
            $table->addColumn('date', 'string', ['limit' => 250, 'null' => true, 'default' => null]);
            $table->addColumn('phone', 'string', ['limit' => 15, 'null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['date'], ['name' => 'date']);
            $table->create();
        }

        if (!$this->hasTable('s_checker_clients')) {
            $table = $this->table('s_checker_clients', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('title', 'string', ['limit' => 100, 'null' => false, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('requests_limit', 'integer', ['null' => false, 'signed' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_checker_clients_ip')) {
            $table = $this->table('s_checker_clients_ip', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('client_id', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('ip', 'string', ['limit' => 100, 'null' => false, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['ip'], ['name' => 'ip', 'unique' => true]);
            $table->addIndex(['client_id'], ['name' => 'FK__s_checker_clients']);
            $table->addForeignKey(['client_id'], 's_checker_clients', ['id'], ['constraint' => 'FK__s_checker_clients', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_checker_requests')) {
            $table = $this->table('s_checker_requests', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('client_ip_id', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('request_time', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('request_body', 'json', ['null' => false]);
            $table->addIndex(['request_time', 'client_ip_id'], ['name' => 'client_ip_id_request_time']);
            $table->addIndex(['client_ip_id'], ['name' => 'FK__s_checker_clients_ip']);
            $table->addForeignKey(['client_ip_id'], 's_checker_clients_ip', ['id'], ['constraint' => 'FK__s_checker_clients_ip', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_close_tasks')) {
            $table = $this->table('s_close_tasks', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('number', 'string', ['limit' => 20, 'null' => false, 'encoding' => 'utf8mb3']);
            $table->addColumn('zayavka', 'string', ['limit' => 10, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3']);
            $table->addColumn('uid', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3']);
            $table->addColumn('client', 'string', ['limit' => 255, 'null' => true, 'default' => '', 'encoding' => 'utf8mb3']);
            $table->addColumn('open_date', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('close_date', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('timezone', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('year_closed', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('pk', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('last_update', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('perspective_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('recall_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['uid'], ['name' => 'uid']);
            $table->addIndex(['timezone'], ['name' => 'timezone']);
            $table->addIndex(['number'], ['name' => 'number']);
            $table->addIndex(['pk'], ['name' => 'pk']);
            $table->addIndex(['client'], ['name' => 'client']);
            $table->addIndex(['status'], ['name' => 'status']);
            $table->create();
        }

        if (!$this->hasTable('s_co_credit_targets')) {
            $table = $this->table('s_co_credit_targets', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci', 'comment' => 'Цели крелитования для заявок ИП и ООО']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'text', ['null' => true, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_comment_record_analysis')) {
            $table = $this->table('s_comment_record_analysis', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('created', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('comment_id', 'biginteger', ['null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('analysis', 'text', ['null' => true, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addIndex(['created'], ['name' => 'idx_created']);
            $table->addIndex(['comment_id'], ['name' => 'idx_comment_id']);
            $table->addIndex(['user_id'], ['name' => 'idx_user_id']);
            $table->addForeignKey(['comment_id'], 's_comments', ['id'], ['constraint' => 's_comment_record_analysis_ibfk_1']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_comment_record_analysis_ibfk_2']);
            $table->create();
        }

        if (!$this->hasTable('s_comments')) {
            $table = $this->table('s_comments', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('block', 'string', ['limit' => 24, 'null' => false]);
            $table->addColumn('text', 'text', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 'product_id']);
            $table->addIndex(['block'], ['name' => 'type']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->create();
        }

        if (!$this->hasTable('s_company_order_data')) {
            $table = $this->table('s_company_order_data', ['id' => false, 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('company_order_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('key', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('value', 'string', ['limit' => 1000, 'null' => true, 'default' => null]);
            $table->addIndex(['company_order_id', 'key'], ['name' => 'company_order_id_key', 'unique' => true]);
            $table->addIndex(['company_order_id'], ['name' => 'company_order_id']);
            $table->create();
        }

        if (!$this->hasTable('s_company_orders')) {
            $table = $this->table('s_company_orders', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Заявки с формы /company_form для ООО и ИП']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('status', 'integer', ['null' => true, 'default' => '1']);
            $table->addColumn('amount', 'integer', ['null' => false, 'comment' => 'Сумма заявки']);
            $table->addColumn('ip', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('bank_name', 'string', ['limit' => 512, 'null' => true, 'default' => null]);
            $table->addColumn('bank_place', 'text', ['null' => true]);
            $table->addColumn('bank_cor_wallet', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('bank_bik', 'string', ['limit' => 16, 'null' => true, 'default' => null]);
            $table->addColumn('bank_user_wallet', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('tax', 'string', ['limit' => 12, 'null' => true, 'default' => null]);
            $table->addColumn('okved', 'string', ['limit' => 8, 'null' => true, 'default' => null]);
            $table->addColumn('co_credit_target_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'цель кредитования']);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['created_at'], ['name' => 's_company_orders_created_at_index']);
            $table->addIndex(['status'], ['name' => 's_company_orders_status_index']);
            $table->addIndex(['user_id'], ['name' => 's_company_orders_s_users_id_fk']);
            $table->addIndex(['co_credit_target_id'], ['name' => 's_company_orders_s_co_credit_targets_id_fk']);
            $table->addForeignKey(['co_credit_target_id'], 's_co_credit_targets', ['id'], ['constraint' => 's_company_orders_s_co_credit_targets_id_fk']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_company_orders_s_users_id_fk']);
            $table->create();
        }

        if (!$this->hasTable('s_complain_form')) {
            $table = $this->table('s_complain_form', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Форма с жалобами']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('user_name', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('user_email', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('user_message', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM, 'null' => true]);
            $table->addColumn('user_contract', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('files', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM, 'null' => true]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_complaint')) {
            $table = $this->table('s_complaint', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('fio', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('phone', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('email', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('birth', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('topic', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('message', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('files', 'json', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->create();
        }

        if (!$this->hasTable('s_connection_by_coockie')) {
            $table = $this->table('s_connection_by_coockie', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('hash', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('coockie', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('count', 'integer', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addIndex(['hash'], ['name' => 's_connection_by_coockie_hash_IDX', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_connection_by_ip')) {
            $table = $this->table('s_connection_by_ip', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('hash', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('ip', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('count', 'integer', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addIndex(['hash'], ['name' => 's_connection_by_ip_hash_IDX', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_connection_by_phone')) {
            $table = $this->table('s_connection_by_phone', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('hash', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('phone_decimal', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('phone', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('count', 'integer', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addIndex(['hash'], ['name' => 's_connection_by_phone_hash_IDX', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_contactpersons')) {
            $table = $this->table('s_contactpersons', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('relation', 'string', ['limit' => 100, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('phone', 'string', ['limit' => 20, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('comment', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->create();
        }

        if (!$this->hasTable('s_contracts')) {
            $table = $this->table('s_contracts', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('user_uid', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('number', 'string', ['limit' => 20, 'null' => false, 'default' => '']);
            $table->addColumn('amount', 'float', ['null' => false, 'default' => '29550', 'comment' => 'сумма выдачи займа']);
            $table->addColumn('period', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => false, 'default' => '0', 'comment' => 'срок выдачи займа']);
            $table->addColumn('payment_method', 'enum', ['values' => ['tinkoff', 'b2p', 'import', ''], 'null' => false, 'default' => 'B2P']);
            $table->addColumn('card_id', 'integer', ['null' => false, 'default' => '0', 'comment' => 'карта на которую выдан займ']);
            $table->addColumn('card_type', 'string', ['limit' => 20, 'null' => true, 'default' => 'card']);
            $table->addColumn('status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0', 'comment' => 'Статус договора']);
            $table->addColumn('base_percent', 'decimal', ['precision' => 10, 'scale' => 3, 'null' => false, 'default' => '0.000', 'comment' => 'базовая процентная ставка по договору, %/день']);
            $table->addColumn('charge_percent', 'decimal', ['precision' => 10, 'scale' => 3, 'null' => false, 'default' => '0.000', 'comment' => 'дополнительная процентная ставка по договору, %/день']);
            $table->addColumn('peni_percent', 'decimal', ['precision' => 10, 'scale' => 3, 'null' => false, 'default' => '0.000', 'comment' => 'пени, %/год']);
            $table->addColumn('uid', 'string', ['limit' => 40, 'null' => false, 'default' => '', 'comment' => 'УИД договора для БКИ']);
            $table->addColumn('loan_body_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Текущая задолженность по основному долгу']);
            $table->addColumn('loan_percents_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Текущая задолженность по начисленным процентам']);
            $table->addColumn('loan_charge_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Текущая задолженность по начисленным дополнительным процентам']);
            $table->addColumn('loan_peni_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Текущая задолженность по начисленным пеням']);
            $table->addColumn('loan_penalty_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Текущая задолженность по начисленным штрафам']);
            $table->addColumn('profit_border', 'decimal', ['precision' => 3, 'scale' => 1, 'null' => false, 'default' => '1.3']);
            $table->addColumn('create_date', 'datetime', ['null' => false, 'comment' => 'Дата создания договора']);
            $table->addColumn('confirm_date', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата подписи договора']);
            $table->addColumn('issuance_date', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата выдачи по договору']);
            $table->addColumn('grace_date', 'date', ['null' => true, 'default' => null, 'comment' => 'дата льготного периода']);
            $table->addColumn('return_date', 'date', ['null' => true, 'default' => null, 'comment' => 'Текущая дата возврата займа по договору (дата платежа)']);
            $table->addColumn('close_date', 'date', ['null' => true, 'default' => null, 'comment' => 'Дата закрытия договора']);
            $table->addColumn('prolongation_count', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('stop_profit', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'порог']);
            $table->addColumn('organization_id', 'integer', ['null' => false, 'default' => '0', 'comment' => 'Организация на которой находится договор, либо на какую продан займ']);
            $table->addColumn('asp', 'string', ['limit' => 10, 'null' => false, 'default' => '', 'comment' => 'Код АСП подписания договора']);
            $table->addColumn('psk', 'decimal', ['precision' => 10, 'scale' => 3, 'null' => false, 'default' => '0.000']);
            $table->addColumn('pdn', 'decimal', ['precision' => 10, 'scale' => 3, 'null' => false, 'default' => '0.000']);
            $table->addColumn('onec_sent', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Результат отправки в 1с договора']);
            $table->addColumn('onec_sent_date', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата отправки в 1с договора']);
            $table->addColumn('is_true', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'временное поле для переноса']);
            $table->addColumn('responsible_person_id', 'integer', ['null' => true, 'default' => null]);
            $table->addIndex(['order_id'], ['name' => 'order_id_2', 'unique' => true]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['number'], ['name' => 'number']);
            $table->addIndex(['payment_method'], ['name' => 'payment_method']);
            $table->addIndex(['status'], ['name' => 'status']);
            $table->addIndex(['card_id'], ['name' => 'card_id']);
            $table->addIndex(['close_date'], ['name' => 'close_date']);
            $table->addIndex(['issuance_date'], ['name' => 'issuance_date']);
            $table->addIndex(['return_date'], ['name' => 'return_date']);
            $table->addIndex(['organization_id'], ['name' => 'organization_id']);
            $table->addIndex(['onec_sent'], ['name' => 'onec_sent']);
            $table->addIndex(['grace_date'], ['name' => 'grace_date']);
            $table->addIndex(['stop_profit'], ['name' => 'stop_profit']);
            $table->addIndex(['user_uid'], ['name' => 'user_uid']);
            $table->create();
        }

        if (!$this->hasTable('s_contracts_for_auto_approve_orders')) {
            $table = $this->table('s_contracts_for_auto_approve_orders', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Номера договоров клиентов, для которых нужно сгенерировать авто-одобренные заявки']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('contract_number', 'string', ['limit' => 20, 'null' => false, 'default' => '', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('status', 'string', ['limit' => 20, 'null' => false, 'default' => 'NEW', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('auto_approve_nk_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'ID добавленной записи в s_auto_approve_nk']);
            $table->addColumn('date_create', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_update', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['contract_number'], ['name' => 's_contracts_for_auto_approve_orders_contact_number_uindex']);
            $table->addIndex(['status'], ['name' => 's_contracts_for_auto_approve_orders_status_uindex']);
            $table->create();
        }

        if (!$this->hasTable('s_coupons')) {
            $table = $this->table('s_coupons', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('code', 'string', ['limit' => 256, 'null' => false]);
            $table->addColumn('expire', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('type', 'enum', ['values' => ['absolute', 'percentage'], 'null' => false, 'default' => 'absolute']);
            $table->addColumn('value', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('min_order_price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('single', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('usages', 'integer', ['null' => false, 'default' => '0']);
            $table->create();
        }

        if (!$this->hasTable('s_credit_doctor_condition_to_lessons')) {
            $table = $this->table('s_credit_doctor_condition_to_lessons', ['id' => false, 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('condition_id', 'integer', ['null' => false]);
            $table->addColumn('lesson_id', 'integer', ['null' => false]);
            $table->addIndex(['condition_id', 'lesson_id'], ['name' => 's_credit_doctor_condition_to_lessons_pk', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_credit_doctor_conditions')) {
            $table = $this->table('s_credit_doctor_conditions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('is_new', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('from_amount', 'integer', ['null' => false]);
            $table->addColumn('to_amount', 'integer', ['null' => false]);
            $table->addColumn('price', 'integer', ['null' => false]);
            $table->addColumn('penalty_price', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('price_group', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('license_key_days', 'integer', ['null' => true, 'default' => null, 'comment' => 'Срок действия лицензионного ключа в днях']);
            $table->create();
        }

        if (!$this->hasTable('s_credit_doctor_form')) {
            $table = $this->table('s_credit_doctor_form', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Страница с опросами']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('survey_amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('has_credit', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('count_take_money', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('count_calls', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date_created', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_modified', 'datetime', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('email', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 's_credit_doctor_form_user_id_uindex', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_credit_doctor_lessons')) {
            $table = $this->table('s_credit_doctor_lessons', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Уроки по КД']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('level_id', 'integer', ['null' => false]);
            $table->addColumn('title', 'text', ['null' => true]);
            $table->addColumn('description', 'text', ['null' => true]);
            $table->addColumn('url', 'string', ['limit' => 150, 'null' => false]);
            $table->addColumn('cover', 'string', ['limit' => 150, 'null' => false]);
            $table->addColumn('type', 'enum', ['values' => ['video', 'pdf'], 'null' => false]);
            $table->addColumn('ordering', 'integer', ['null' => false, 'default' => '0']);
            $table->addIndex(['level_id'], ['name' => 'FK_s_credit_doctor_lessons_s_credit_doctor_levels']);
            $table->addForeignKey(['level_id'], 's_credit_doctor_levels', ['id'], ['constraint' => 'FK_s_credit_doctor_lessons_s_credit_doctor_levels']);
            $table->create();
        }

        if (!$this->hasTable('s_credit_doctor_levels')) {
            $table = $this->table('s_credit_doctor_levels', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('title', 'string', ['limit' => 200, 'null' => false]);
            $table->addColumn('ordering', 'integer', ['null' => false, 'default' => '0']);
            $table->create();
        }

        if (!$this->hasTable('s_credit_doctor_to_user')) {
            $table = $this->table('s_credit_doctor_to_user', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Список купленных КД']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('credit_doctor_condition_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('amount', 'integer', ['null' => true, 'default' => null, 'comment' => 'Стоимость КД']);
            $table->addColumn('amount_total_returned', 'integer', ['null' => false, 'default' => '0', 'comment' => 'total returned amount']);
            $table->addColumn('payment_method', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('transaction_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('status', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('organization_id', 'integer', ['null' => false, 'default' => '1']);
            $table->addColumn('return_sent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('return_transaction_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('return_status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('return_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('return_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('return_by_user', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('return_by_manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_edit', 'datetime', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('dop1c_sent', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'отправлена ли доп услуга в доповую 1с']);
            $table->addColumn('dop1c_sent_return', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'отправлен ли возврат доп услуги в доповую 1с']);
            $table->addColumn('is_penalty', 'boolean', ['null' => true, 'default' => '0']);
            $table->addIndex(['status'], ['name' => 's_credit_doctor_to_user_status_index']);
            $table->addIndex(['user_id'], ['name' => 's_credit_doctor_to_user_user_id_index']);
            $table->addIndex(['order_id'], ['name' => 's_credit_doctor_to_user_order_id_index']);
            $table->addIndex(['payment_method'], ['name' => 's_credit_doctor_to_user_payment_method_index']);
            $table->addIndex(['transaction_id'], ['name' => 's_credit_doctor_to_user_transaction_id_index']);
            $table->addIndex(['return_date'], ['name' => 'return_date']);
            $table->addIndex(['return_transaction_id'], ['name' => 'return_transaction_id']);
            $table->addIndex(['return_sent'], ['name' => 'return_sent']);
            $table->addIndex(['credit_doctor_condition_id'], ['name' => 's_credit_doctor_to_user_credit_doctor_id_index']);
            $table->addIndex(['dop1c_sent_return'], ['name' => 'dop1c_sent_return']);
            $table->addIndex(['dop1c_sent'], ['name' => 'dop1c_sent']);
            $table->addIndex(['is_penalty'], ['name' => 'is_penalty']);
            $table->addForeignKey(['credit_doctor_condition_id'], 's_credit_doctor_conditions', ['id'], ['constraint' => 'FK_s_credit_doctor_to_user_s_credit_doctor_conditions', 'delete' => 'RESTRICT', 'update' => 'CASCADE']);
            $table->create();
        }

        if (!$this->hasTable('s_credit_histories')) {
            $table = $this->table('s_credit_histories', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Отчеты из НБКИ']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('type', 'string', ['limit' => 50, 'null' => false, 'comment' => 'Тип отчета', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('file_name', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'Название файла', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('s3_name', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'Путь к файлу в s3', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('date_create', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['order_id'], ['name' => 's_credit_histories_order_id_index']);
            $table->addIndex(['user_id'], ['name' => 's_credit_histories_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_cron_fixed_divide_amount')) {
            $table = $this->table('s_cron_fixed_divide_amount', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Хранилилище проверенных авто - деленных заявок по скористе']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['order_id'], ['name' => 's_cron_fixed_divide_amount_order_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_cron_task_scorista')) {
            $table = $this->table('s_cron_task_scorista', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Задания для скористы']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => false, 'comment' => 'Id заявки s_orders.id']);
            $table->addColumn('foreign_key_value', 'integer', ['null' => true, 'default' => null, 'comment' => 'идентификатор для внешней таблицы']);
            $table->addColumn('foreign_key_name', 'string', ['limit' => 64, 'null' => true, 'default' => null, 'comment' => 'имя таблицы и колонки, для foreign_key_value']);
            $table->addColumn('status', 'enum', ['values' => ['new', 'progress', 'success', 'error'], 'null' => true, 'default' => null, 'comment' => 'Статус задания']);
            $table->addColumn('type', 'string', ['limit' => 32, 'null' => true, 'default' => null, 'comment' => 'Тип события']);
            $table->addColumn('data', 'text', ['null' => true, 'comment' => 'Данные для отправки']);
            $table->addColumn('description', 'text', ['null' => true, 'comment' => 'Дополнительное описание задания']);
            $table->addColumn('date_added', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_edit', 'timestamp', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['status'], ['name' => 's_cron_task_scorista_status_index']);
            $table->addIndex(['type'], ['name' => 's_cron_task_scorista_type_index']);
            $table->addIndex(['order_id'], ['name' => 's_cron_task_scorista_order_id_index']);
            $table->addIndex(['foreign_key_value'], ['name' => 's_cron_task_scorista_foreign_key_value_index']);
            $table->addIndex(['foreign_key_name'], ['name' => 's_cron_task_scorista_foreign_key_name_index']);
            $table->create();
        }

        if (!$this->hasTable('s_cron_validate_blacklist')) {
            $table = $this->table('s_cron_validate_blacklist', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Автоделенные заявки для которых проводился скоринг на ЧС']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['order_id'], ['name' => 's_cron_validate_blacklist_order_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_customerSurveys')) {
            $table = $this->table('s_customerSurveys', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('idQuestion', 'integer', ['null' => false, 'default' => '0', 'comment' => 'id вопроса']);
            $table->addColumn('variantSurveys', 'text', ['null' => false, 'comment' => 'Варианты ответов на вопрос', 'encoding' => 'utf8mb3']);
            $table->addColumn('typeTask', 'string', ['limit' => 250, 'null' => false, 'comment' => 'к какому типу задач относится опрос', 'encoding' => 'utf8mb3']);
            $table->addColumn('typeSurvey', 'string', ['limit' => 20, 'null' => false, 'default' => 'button', 'comment' => 'тип варианта ответа (text, button, dateText)', 'encoding' => 'utf8mb3']);
            $table->addColumn('buttonColor', 'string', ['limit' => 7, 'null' => false, 'default' => '#55CE63', 'comment' => 'Цвет кнопки варианта ответа', 'encoding' => 'utf8mb3']);
            $table->addColumn('action', 'string', ['limit' => 250, 'null' => true, 'default' => null, 'comment' => 'Действие при нажатии варианта ответа', 'encoding' => 'utf8mb3']);
            $table->create();
        }

        if (!$this->hasTable('s_cyberity_verifications')) {
            $table = $this->table('s_cyberity_verifications', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Антифрод верификация пользователей в cyberity']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('applicant_id', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'ID пользователя в cyberity', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('inspection_id', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'ID заявления в cyberity', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('status', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('date_create', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_update', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_start_verification', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата отправки фотографий для верификации в cyberity']);
            $table->addColumn('date_end_verification', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата получения результатов верификации из cyberity']);
            $table->addColumn('verification_result', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('verification_result_comment', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('phone', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['applicant_id'], ['name' => 's_cyberity_verifications_applicant_id_index']);
            $table->addIndex(['user_id'], ['name' => 's_cyberity_verifications_user_id_index']);
            $table->addIndex(['phone'], ['name' => 's_cyberity_verifications_phone_IDX']);
            $table->create();
        }

        if (!$this->hasTable('s_dbrain')) {
            $table = $this->table('s_dbrain', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('file_id', 'integer', ['null' => false]);
            $table->addColumn('method', 'string', ['limit' => 50, 'null' => false, 'encoding' => 'utf8mb3']);
            $table->addColumn('task_id', 'string', ['limit' => 50, 'null' => false, 'encoding' => 'utf8mb3']);
            $table->addColumn('status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('result', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG, 'null' => true, 'encoding' => 'utf8mb3']);
            $table->addColumn('created_date', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['task_id'], ['name' => 'task_id', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_dbrain_axi_logs')) {
            $table = $this->table('s_dbrain_axi_logs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('1c_id', 'string', ['limit' => 15, 'null' => false]);
            $table->addColumn('end_date', 'datetime', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_dbrain_statistics')) {
            $table = $this->table('s_dbrain_statistics', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('decision', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('reason', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('created', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->create();
        }

        if (!$this->hasTable('s_device_tokens')) {
            $table = $this->table('s_device_tokens', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Tokens for mobile push notifications']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('token', 'string', ['limit' => 250, 'null' => false]);
            $table->addColumn('device', 'string', ['limit' => 200, 'null' => false, 'default' => 'android']);
            $table->addColumn('created', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('modified', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['user_id'], ['name' => 'FK_s_device_tokens_s_users']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 'FK_s_device_tokens_s_users', 'delete' => 'CASCADE']);
            $table->create();
        }

        if (!$this->hasTable('s_discount_insure')) {
            $table = $this->table('s_discount_insure', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Скидки на страховку']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('date_start', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('date_end', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('prices', 'text', ['null' => true]);
            $table->addColumn('status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '1']);
            $table->create();
        }

        if (!$this->hasTable('s_discount_insure_phones')) {
            $table = $this->table('s_discount_insure_phones', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Список телефонов для акций по страховке']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('discount_insurer_id', 'integer', ['null' => true, 'default' => null]);
            $table->addIndex(['phone', 'discount_insurer_id'], ['name' => 's_discount_insure_phones_phone_discount_insurer_id_uindex', 'unique' => true]);
            $table->addIndex(['phone'], ['name' => 's_discount_insure_phones_phone_index']);
            $table->create();
        }

        if (!$this->hasTable('s_discounts')) {
            $table = $this->table('s_discounts', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'biginteger', ['null' => false, 'comment' => 'телефон клиента']);
            $table->addColumn('end_date', 'date', ['null' => false, 'comment' => 'дата когда акция заканчивается']);
            $table->addColumn('percent', 'decimal', ['precision' => 4, 'scale' => 2, 'null' => false, 'comment' => 'акционный процент']);
            $table->addColumn('max_period', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '16', 'comment' => 'максимальный период на который можно взять займ по акции']);
            $table->addIndex(['phone'], ['name' => 'phone']);
            $table->addIndex(['end_date'], ['name' => 'end_date']);
            $table->create();
        }

        if (!$this->hasTable('s_divide_order')) {
            $table = $this->table('s_divide_order', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Для разделения заявок']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('main_order_id', 'integer', ['null' => false, 'comment' => 'Заявка от которой делалось разделение']);
            $table->addColumn('divide_order_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'Заявки которые созданы при разделении']);
            $table->addColumn('auto_generate', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'comment' => 'Авто-сгенерированная заявка']);
            $table->addColumn('status', 'string', ['limit' => 16, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['main_order_id', 'user_id'], ['name' => 's_divide_order_pk', 'unique' => true]);
            $table->addIndex(['divide_order_id'], ['name' => 's_divide_order_divide_order_id_index']);
            $table->addIndex(['main_order_id'], ['name' => 's_divide_order_main_order_id_index']);
            $table->addIndex(['status'], ['name' => 's_divide_order_status_index']);
            $table->addIndex(['user_id'], ['name' => 's_divide_order_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_divide_order_status_log')) {
            $table = $this->table('s_divide_order_status_log', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Лог истории разделенных займов']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('divide_order_id', 'integer', ['null' => true, 'default' => null, 'comment' => 's_divide_order.id']);
            $table->addColumn('status', 'string', ['limit' => 16, 'null' => false, 'comment' => 'Статус заявки']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['divide_order_id'], ['name' => 's_divide_order_status_log_s_divide_order_null_fk']);
            $table->addIndex(['status'], ['name' => 's_divide_order_status_log_status_index']);
            $table->addForeignKey(['divide_order_id'], 's_divide_order', ['id'], ['constraint' => 's_divide_order_status_log_s_divide_order_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_divide_pre_orders')) {
            $table = $this->table('s_divide_pre_orders', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Информация для создания будущей заявки']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('amount', 'integer', ['null' => true, 'default' => null, 'comment' => 'Сумма будущей заявки']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['order_id'], ['name' => 's_divide_pre_orders_pk', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_docs')) {
            $table = $this->table('s_docs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 500, 'null' => false]);
            $table->addColumn('filename', 'text', ['null' => false]);
            $table->addColumn('description', 'text', ['null' => true]);
            $table->addColumn('created', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('in_info', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('in_register', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('visible', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('position', 'integer', ['null' => false]);
            $table->addColumn('version', 'integer', ['null' => false, 'default' => '0']);
            $table->create();
        }

        if (!$this->hasTable('s_docs_logs')) {
            $table = $this->table('s_docs_logs', ['id' => false, 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('doc_id', 'integer', ['null' => false, 'comment' => 'DOCUMENT ID']);
            $table->addColumn('manager_id', 'integer', ['null' => false, 'comment' => 'MANAGER ID']);
            $table->addColumn('action_id', 'integer', ['null' => false, 'comment' => 'ACTION ID']);
            $table->addColumn('created', 'datetime', ['null' => false, 'comment' => 'CREATE DATE & TIME']);
            $table->addIndex(['doc_id'], ['name' => 'doc_id_index']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id_index']);
            $table->addIndex(['action_id'], ['name' => 'action_id_index']);
            $table->addForeignKey(['manager_id'], 's_managers', ['id'], ['constraint' => 's_docs_logs_ibfk_1', 'delete' => 'CASCADE', 'update' => 'RESTRICT']);
            $table->addForeignKey(['action_id'], 's_docs_logs_actions', ['id'], ['constraint' => 's_docs_logs_ibfk_2', 'delete' => 'CASCADE', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_docs_logs_actions')) {
            $table = $this->table('s_docs_logs_actions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->create();
        }

        if (!$this->hasTable('s_document_types')) {
            $table = $this->table('s_document_types', ['id' => false, 'primary_key' => ['type'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('type', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('template', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('name', 'string', ['limit' => 512, 'null' => false]);
            $table->addColumn('client_visible', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('organization_id', 'integer', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_documents')) {
            $table = $this->table('s_documents', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('contract_number', 'string', ['limit' => 30, 'null' => true, 'default' => '']);
            $table->addColumn('type', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('template', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('content', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM, 'null' => false]);
            $table->addColumn('client_visible', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('params', 'text', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('sent_1c', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('sent_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('ready', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('filestorage_uid', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => 'УИД файла в хранилише']);
            $table->addColumn('replaced', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('organization_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('s3_key', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addIndex(['contract_number'], ['name' => 'contract_id']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['client_visible'], ['name' => 'client_visible']);
            $table->addIndex(['type'], ['name' => 'type']);
            $table->addIndex(['filestorage_uid'], ['name' => 'filestorage_uid']);
            $table->addIndex(['organization_id'], ['name' => 's_documents_organization_id_index']);
            $table->addIndex(['type', 'user_id'], ['name' => 'type_2']);
            $table->addIndex(['ready'], ['name' => 'ready']);
            $table->addIndex(['ready'], ['name' => 'ready_2']);
            $table->create();
        }

        if (!$this->hasTable('s_dop_licenses')) {
            $table = $this->table('s_dop_licenses', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Лицензии дополнительных сервисов']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('service_type', 'enum', ['values' => ['financial_doctor', 'concierge', 'vitamed', 'star_oracle'], 'null' => false, 'comment' => 'Тип сервиса', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('service_id', 'integer', ['null' => false, 'comment' => 'ИД ДОП']);
            $table->addColumn('license_key', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'Ключ лицензии из API', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('tariff', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'Тариф из API', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('amount', 'integer', ['null' => false, 'comment' => 'Сумма ДОПа']);
            $table->addColumn('status', 'enum', ['values' => ['new', 'success', 'error'], 'null' => false, 'default' => 'NEW', 'comment' => 'Статус обработки', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user_id', 'integer', ['null' => false, 'comment' => 'ID пользователя']);
            $table->addColumn('order_id', 'integer', ['null' => false, 'comment' => 'ID заказа']);
            $table->addColumn('organization_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'ID организации']);
            $table->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Дата создания в системе']);
            $table->addColumn('ending', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Timestamp окончания из API']);
            $table->addColumn('updated_at', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'comment' => 'Дата обновления']);
            $table->addColumn('attempts', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0', 'comment' => 'Кол-во попыток обработки', 'signed' => false]);
            $table->addColumn('api_body', 'text', ['null' => true, 'comment' => 'Данные для API (JSON)', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('api_response', 'text', ['null' => true, 'comment' => 'Полный ответ API (JSON)', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['service_id'], ['name' => 'service_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['status', 'service_type'], ['name' => 'status_service_type']);
            $table->create();
        }

        if (!$this->hasTable('s_eventlogs')) {
            $table = $this->table('s_eventlogs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('event_id', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('changelog_id', 'integer', ['null' => true, 'default' => null]);
            $table->addIndex(['event_id'], ['name' => 'event_id']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['changelog_id'], ['name' => 'changelog_id']);
            $table->create();
        }

        if (!$this->hasTable('s_events')) {
            $table = $this->table('s_events', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('event', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['event'], ['name' => 'event']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->create();
        }

        if (!$this->hasTable('s_exitpools')) {
            $table = $this->table('s_exitpools', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('question_id', 'integer', ['null' => false]);
            $table->addColumn('question', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('response', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('date', 'datetime', ['null' => false]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->create();
        }

        if (!$this->hasTable('s_external_api_queue')) {
            $table = $this->table('s_external_api_queue', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('api', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('order_id', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('executed_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['api', 'order_id', 'executed_date'], ['name' => 'api_order_id_executed_date']);
            $table->create();
        }

        if (!$this->hasTable('s_extra_services_informs')) {
            $table = $this->table('s_extra_services_informs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('contract', 'string', ['limit' => 31, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('service_name', 'string', ['limit' => 63, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('sms_phone', 'string', ['limit' => 15, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('sms_template_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('sms_type', 'string', ['limit' => 15, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('license_key', 'string', ['limit' => 31, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->create();
        }

        if (!$this->hasTable('s_faq')) {
            $table = $this->table('s_faq', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('section_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('question', 'text', ['null' => false]);
            $table->addColumn('answer', 'text', ['null' => false]);
            $table->addColumn('yandex_goal_id', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addIndex(['section_id'], ['name' => 'section_id']);
            $table->addForeignKey(['section_id'], 's_faq_sections', ['id'], ['constraint' => 's_faq_ibfk_1', 'delete' => 'CASCADE']);
            $table->create();
        }

        if (!$this->hasTable('s_faq_blocks')) {
            $table = $this->table('s_faq_blocks', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('type', 'enum', ['values' => ['public', 'authorized_no_loans', 'active_loan', 'overdue_debt', 'application_process', 'closed_loans'], 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('yandex_goal_id', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->create();
        }

        if (!$this->hasTable('s_faq_sections')) {
            $table = $this->table('s_faq_sections', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('block_id', 'integer', ['null' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('sequence', 'integer', ['null' => true, 'default' => '0']);
            $table->addIndex(['block_id'], ['name' => 'block_id']);
            $table->addForeignKey(['block_id'], 's_faq_blocks', ['id'], ['constraint' => 's_faq_sections_ibfk_1', 'delete' => 'CASCADE']);
            $table->create();
        }

        if (!$this->hasTable('s_feedbacks')) {
            $table = $this->table('s_feedbacks', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('date', 'datetime', ['null' => false]);
            $table->addColumn('ip', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('email', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('message', 'text', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_files')) {
            $table = $this->table('s_files', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('type', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0', 'comment' => '0 - updated (файл залит, но не отправлен); 1 - sended (файл отправлен на проверку, но не обработан); 2 - accept (файл обработан и принят); 3 - dismiss(файл обработан и отклонен);']);
            $table->addColumn('created', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('storage_uid', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addColumn('s3_name', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addColumn('visible', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('not_found', 'boolean', ['null' => false, 'default' => '0']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['storage_uid'], ['name' => 'storage_uid']);
            $table->addIndex(['status'], ['name' => 'status']);
            $table->addIndex(['visible'], ['name' => 'visible']);
            $table->addIndex(['not_found'], ['name' => 'not_found']);
            $table->addIndex(['name'], ['name' => 'name']);
            $table->addIndex(['s3_name'], ['name' => 's3_name']);
            $table->create();
        }

        if (!$this->hasTable('s_finansdoctor_license_keys')) {
            $table = $this->table('s_finansdoctor_license_keys', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 15, 'null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('license_key', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('active', 'boolean', ['null' => false, 'default' => '1']);
            $table->addColumn('tariff', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('days', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('tokens', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('ending', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('rejected', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('organization_id', 'integer', ['null' => true, 'default' => null]);
            $table->addIndex(['phone'], ['name' => 'phone']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->create();
        }

        if (!$this->hasTable('s_fssp_basis')) {
            $table = $this->table('s_fssp_basis', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('title', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_fssp_reasons')) {
            $table = $this->table('s_fssp_reasons', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('title', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_fssp_to_orders')) {
            $table = $this->table('s_fssp_to_orders', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('reason_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('basis_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date_end', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['basis_id'], ['name' => 's_fssp_to_orders_basis_id_index']);
            $table->addIndex(['order_id'], ['name' => 's_fssp_to_orders_order_id_index']);
            $table->addIndex(['reason_id'], ['name' => 's_fssp_to_orders_reason_id_index']);
            $table->addIndex(['user_id'], ['name' => 's_fssp_to_orders_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_groups')) {
            $table = $this->table('s_groups', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('discount', 'decimal', ['precision' => 5, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->create();
        }

        if (!$this->hasTable('s_hide_service')) {
            $table = $this->table('s_hide_service', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Пользователи для которых скрыты допы']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['user_id'], ['name' => 's_hide_service_pk', 'unique' => true]);
            $table->addIndex(['user_id'], ['name' => 's_hide_service_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_hyper_c')) {
            $table = $this->table('s_hyper_c', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Результаты скоринга Hyper-C']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('success', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('decision', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('approve_amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('result', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('start_date', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('end_date', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['order_id'], ['name' => 'order_id', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_images')) {
            $table = $this->table('s_images', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('product_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('filename', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('position', 'integer', ['null' => false]);
            $table->addIndex(['filename'], ['name' => 'filename']);
            $table->addIndex(['product_id'], ['name' => 'product_id']);
            $table->addIndex(['position'], ['name' => 'position']);
            $table->create();
        }

        if (!$this->hasTable('s_incoming_calls_blacklist')) {
            $table = $this->table('s_incoming_calls_blacklist', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone_number', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('reason', 'text', ['null' => true]);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('last_call_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('created_by', 'integer', ['null' => false]);
            $table->addColumn('is_active', 'boolean', ['null' => false, 'default' => '1']);
            $table->addIndex(['phone_number'], ['name' => 'unique_phone', 'unique' => true]);
            $table->addIndex(['created_by'], ['name' => 'created_by']);
            $table->addForeignKey(['created_by'], 's_managers', ['id'], ['constraint' => 's_incoming_calls_blacklist_ibfk_1']);
            $table->create();
        }

        if (!$this->hasTable('s_individuals')) {
            $table = $this->table('s_individuals', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('paid', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('manager_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['paid'], ['name' => 'paid']);
            $table->addIndex(['status'], ['name' => 'status']);
            $table->create();
        }

        if (!$this->hasTable('s_init_user_phones')) {
            $table = $this->table('s_init_user_phones', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'используется для нового флоу авторизации']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 12, 'null' => false]);
            $table->addColumn('sms_code', 'string', ['limit' => 12, 'null' => true, 'default' => null, 'comment' => 'Код подтверждения']);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['phone'], ['name' => 's_init_user_phones_phone_index']);
            $table->create();
        }

        if (!$this->hasTable('s_installment_segments')) {
            $table = $this->table('s_installment_segments', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('min_close_count', 'boolean', ['null' => true, 'default' => '0', 'comment' => 'минимальное кол-во закрытых займов']);
            $table->addColumn('history_max_expired', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0', 'comment' => 'максимальная просрочка  от последней даты платежа по всем займам ранее']);
            $table->addColumn('history_avg_days', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0', 'comment' => 'минимальное среднее кол-во дней пользования займами']);
            $table->addColumn('min_axi_score', 'decimal', ['precision' => 4, 'scale' => 2, 'null' => true, 'default' => '0.00', 'comment' => 'вероятность дефолта по модели акси
            минимальный порог']);
            $table->addColumn('max_axi_score', 'decimal', ['precision' => 4, 'scale' => 2, 'null' => true, 'default' => '0.00', 'comment' => 'вероятность дефолта по модели акси
            максимальный порог']);
            $table->addColumn('max_approve_amount', 'integer', ['null' => true, 'default' => '0', 'comment' => 'максимальная сумма одобрения']);
            $table->addColumn('min_approve_period', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0', 'comment' => 'кол-во недель минимум
            ']);
            $table->addColumn('max_approve_period', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0', 'comment' => 'кол-во недель максимум']);
            $table->create();
        }

        if (!$this->hasTable('s_insurances')) {
            $table = $this->table('s_insurances', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('number', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('create_date', 'datetime', ['null' => false]);
            $table->addColumn('start_date', 'datetime', ['null' => false]);
            $table->addColumn('end_date', 'datetime', ['null' => false]);
            $table->addColumn('transaction_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('contract_number', 'string', ['limit' => 30, 'null' => false, 'default' => '']);
            $table->addColumn('sent_status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('send_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('insurer', 'string', ['limit' => 20, 'null' => false, 'default' => '']);
            $table->addColumn('return_application_date', 'string', ['limit' => 30, 'null' => false, 'default' => '']);
            $table->addColumn('return_status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('return_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('return_response', 'text', ['null' => true]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['transaction_id'], ['name' => 'operation_id']);
            $table->addIndex(['contract_number'], ['name' => 'contract_number']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['return_status'], ['name' => 'return_status']);
            $table->create();
        }

        if (!$this->hasTable('s_juicescore_criteria')) {
            $table = $this->table('s_juicescore_criteria', ['id' => false, 'primary_key' => ['name'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('name', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('required_ball', 'float', ['null' => false, 'default' => '0']);
            $table->create();
        }

        if (!$this->hasTable('s_labels')) {
            $table = $this->table('s_labels', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('color', 'string', ['limit' => 6, 'null' => false]);
            $table->addColumn('position', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_lead_price')) {
            $table = $this->table('s_lead_price', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('utm_source', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('webmaster_id', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('price', 'float', ['null' => false, 'default' => '0']);
            $table->addColumn('updated_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['utm_source', 'webmaster_id'], ['name' => 'utm_source_webmaster_id', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_lead_price_logs')) {
            $table = $this->table('s_lead_price_logs', ['id' => false, 'primary_key' => ['order_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('order_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('utm_source', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('webmaster_id', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('price', 'float', ['null' => false]);
            $table->addIndex(['utm_source'], ['name' => 'utm_source']);
            $table->addIndex(['webmaster_id'], ['name' => 'webmaster_id']);
            $table->create();
        }

        if (!$this->hasTable('s_leadgid_scorista')) {
            $table = $this->table('s_leadgid_scorista', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'BOOSTRARU-2459']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('type', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0', 'comment' => '0 - Для отказной скористы, 1 - Для одобренной', 'signed' => false]);
            $table->addColumn('utm_source', 'string', ['limit' => 30, 'null' => false, 'comment' => 'utm_source заявки из s_orders']);
            $table->addColumn('utm_medium', 'string', ['limit' => 30, 'null' => false, 'default' => '*', 'comment' => 'utm_medium заявки из s_orders']);
            $table->addColumn('have_close_credits', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0', 'comment' => '0 - НК, 1 - ПК', 'signed' => false]);
            $table->addColumn('min_ball', 'integer', ['null' => false, 'comment' => 'Минимальный балл скористы']);
            $table->addColumn('amount', 'integer', ['null' => false, 'comment' => 'Рекомендуемая сумма', 'signed' => false]);
            $table->addIndex(['utm_source', 'utm_medium', 'have_close_credits', 'type'], ['name' => 'utm_source_have_close_credits', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_leadgid_scorista_factors')) {
            $table = $this->table('s_leadgid_scorista_factors', ['id' => false, 'primary_key' => ['factor'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci', 'comment' => 'Список стоп-факторов относящихся к настройкам из таблицы s_leadgid_scorista']);
            $table->addColumn('factor', 'string', ['limit' => 50, 'null' => false, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('comment', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->create();
        }

        if (!$this->hasTable('s_leadgid_scorista_logs')) {
            $table = $this->table('s_leadgid_scorista_logs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('created', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('type', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('old_values', 'text', ['null' => false]);
            $table->addColumn('new_values', 'text', ['null' => false]);
            $table->addColumn('leadgid_id', 'integer', ['null' => false]);
            $table->addColumn('utm_source', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('utm_medium', 'string', ['limit' => 30, 'null' => false]);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['type'], ['name' => 'type']);
            $table->addIndex(['leadgid_id'], ['name' => 'order_id']);
            $table->create();
        }

        if (!$this->hasTable('s_leadgid_sms_log')) {
            $table = $this->table('s_leadgid_sms_log', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('message', 'text', ['null' => false]);
            $table->addColumn('number_of', 'integer', ['null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('firstname', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_likezaim')) {
            $table = $this->table('s_likezaim', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('response', 'text', ['null' => true]);
            $table->addColumn('client_cloned', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('link', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addColumn('has_contract', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('sms_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('postback_getted', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('postback_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('postback_state', 'string', ['limit' => 10, 'null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['client_cloned'], ['name' => 'status']);
            $table->addIndex(['sms_id'], ['name' => 'sms_sent']);
            $table->addIndex(['has_contract'], ['name' => 'has_contract']);
            $table->addIndex(['postback_getted'], ['name' => 'postback_getted']);
            $table->create();
        }

        if (!$this->hasTable('s_link_stats')) {
            $table = $this->table('s_link_stats', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('user_id_for_analytics', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('link', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('previous_utm_source', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('clicks_count', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('applications_count', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('loans_count', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('expiration_date', 'datetime', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_loan_funnel_report')) {
            $table = $this->table('s_loan_funnel_report', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('time', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('user_ip', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('login', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('order_request', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('approved', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('issued', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'biginteger', ['null' => true, 'default' => null]);
            $table->addColumn('link_date', 'date', ['null' => false]);
            $table->addColumn('login_date', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('order_date', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('approved_date', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('issue_date', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('webmaster_id', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addIndex(['order_id'], ['name' => 'loan_funnel_report_s_orders_null_fk']);
            $table->addIndex(['user_id'], ['name' => 'loan_funnel_report_s_users_null_fk']);
            $table->addForeignKey(['order_id'], 's_orders', ['id'], ['constraint' => 'loan_funnel_report_s_orders_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 'loan_funnel_report_s_users_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_local_storage')) {
            $table = $this->table('s_local_storage', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Описания файлов на сервере']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('type', 'enum', ['values' => ['pdn_remains', 'pdn_quarterly'], 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('name', 'string', ['limit' => 250, 'null' => false, 'default' => '0', 'comment' => 'Описание файла', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('path', 'string', ['limit' => 250, 'null' => false, 'default' => '0', 'comment' => 'Путь к файлу относительно коневой папки сайта', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['type'], ['name' => 'type']);
            $table->create();
        }

        if (!$this->hasTable('s_log_methods')) {
            $table = $this->table('s_log_methods', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Логирование методов сайта']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'string', ['limit' => 128, 'null' => false]);
            $table->addColumn('method', 'string', ['limit' => 256, 'null' => false]);
            $table->addColumn('url', 'text', ['null' => true]);
            $table->addColumn('request', 'text', ['null' => true]);
            $table->addColumn('response', 'text', ['null' => true]);
            $table->addColumn('additional', 'text', ['null' => true]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['date_added'], ['name' => 's_log_methods_date_added_index']);
            $table->addIndex(['user_id'], ['name' => 's_log_methods_user_id_index']);
            $table->addIndex(['method'], ['name' => 's_log_methods_method_index']);
            $table->create();
        }

        if (!$this->hasTable('s_lpt')) {
            $table = $this->table('s_lpt', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('lpt_id', 'integer', ['null' => false]);
            $table->addColumn('user_balance_id', 'integer', ['null' => false]);
            $table->addColumn('json', 'text', ['null' => true]);
            $table->addColumn('status', 'string', ['limit' => 55, 'null' => true, 'default' => null]);
            $table->addColumn('tag', 'string', ['limit' => 55, 'null' => true, 'default' => null]);
            $table->addColumn('custom_array', 'text', ['null' => true]);
            $table->addColumn('comment', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addColumn('updated_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('status_before', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_manager_visits')) {
            $table = $this->table('s_manager_visits', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('ip', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('date_create', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('last_visit', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['ip'], ['name' => 'ip']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['manager_id', 'ip'], ['name' => 's_manager_visits_manager_id_ip_index']);
            $table->addIndex(['manager_id', 'last_visit'], ['name' => 'idx_manager_last_visit']);
            $table->create();
        }

        if (!$this->hasTable('s_managers')) {
            $table = $this->table('s_managers', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('login', 'string', ['limit' => 32, 'null' => false]);
            $table->addColumn('password', 'string', ['limit' => 32, 'null' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('name_1c', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('role', 'string', ['limit' => 25, 'null' => false]);
            $table->addColumn('last_ip', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('last_visit', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('salt', 'string', ['limit' => 32, 'null' => false]);
            $table->addColumn('mango_number', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('avatar', 'string', ['limit' => 50, 'null' => false, 'default' => '5.jpg']);
            $table->addColumn('blocked', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('vox_deleted', 'boolean', ['null' => true, 'default' => null]);
            $table->addIndex(['name'], ['name' => 'name']);
            $table->addIndex(['blocked'], ['name' => 'blocked']);
            $table->addIndex(['last_visit'], ['name' => 'idx_managers_last_visit']);
            $table->create();
        }

        if (!$this->hasTable('s_managers_notifications')) {
            $table = $this->table('s_managers_notifications', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('from_user', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('to_user', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('message', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('is_read', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('subject', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_mangoAnswersToTheQuestionsForTheQuestionnaire')) {
            $table = $this->table('s_mangoAnswersToTheQuestionsForTheQuestionnaire', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('parent', 'integer', ['null' => false]);
            $table->addColumn('text', 'text', ['null' => false]);
            $table->addColumn('type', 'string', ['limit' => 25, 'null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_mangoQuestion')) {
            $table = $this->table('s_mangoQuestion', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('Answer', 'text', ['null' => false]);
            $table->addColumn('Survey', 'text', ['null' => false]);
            $table->addColumn('Action', 'string', ['limit' => 250, 'null' => false]);
            $table->addColumn('Date', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('TicketId', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_mangoQuestionsForTheQuestionnaire')) {
            $table = $this->table('s_mangoQuestionsForTheQuestionnaire', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('text', 'text', ['null' => false]);
            $table->addColumn('type', 'string', ['limit' => 50, 'null' => false, 'default' => 'button']);
            $table->addColumn('action', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('actionParams', 'string', ['limit' => 250, 'null' => false, 'default' => '']);
            $table->addColumn('questionId', 'integer', ['null' => false]);
            $table->addColumn('buttonColor', 'string', ['limit' => 50, 'null' => false, 'default' => '#55CE63']);
            $table->create();
        }

        if (!$this->hasTable('s_mangocalls')) {
            $table = $this->table('s_mangocalls', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('manager_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('entry_id', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('call_id', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('from_extension', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'mango_number из таблицы s_mangocalls']);
            $table->addColumn('from_number', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('to_extension', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'mango_number из таблицы s_mangocalls']);
            $table->addColumn('to_number', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('call_direction', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('disconnect_reason', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addColumn('entry_result', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('create_time', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('forward_time', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('talk_time', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('end_time', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('recording_id', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('record_file', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('created', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('result_code', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('duration', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('rating', 'integer', ['null' => true, 'default' => null]);
            $table->addIndex(['entry_id'], ['name' => 'entry_id']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['duration'], ['name' => 'duration']);
            $table->addIndex(['created'], ['name' => 's_mangocalls_created_index']);
            $table->addIndex(['to_number'], ['name' => 's_mangocalls_to_number_index']);
            $table->addIndex(['from_number'], ['name' => 'from_number']);
            $table->addIndex(['from_number', 'to_number'], ['name' => 'from_number_2']);
            $table->create();
        }

        if (!$this->hasTable('s_maratoriums')) {
            $table = $this->table('s_maratoriums', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('period', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_menu')) {
            $table = $this->table('s_menu', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('position', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_metric_actions')) {
            $table = $this->table('s_metric_actions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'События метрики']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('metric_goal_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('visitor_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('session_unique', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '1']);
            $table->addColumn('user_unique', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true]);
            $table->addColumn('client_type', 'integer', ['null' => true, 'default' => null, 'comment' => 'НК - 0, ПК - 1 null - неопределено']);
            $table->addColumn('referer', 'text', ['null' => true]);
            $table->addColumn('from_backend', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['metric_goal_id'], ['name' => 's_metric_actions_metric_goal_id_index']);
            $table->addIndex(['visitor_id'], ['name' => 's_metric_actions_visitor_id_index']);
            $table->addIndex(['client_type'], ['name' => 's_metric_actions_client_type_index']);
            $table->addIndex(['user_id'], ['name' => 's_metric_actions_user_id_index']);
            $table->addIndex(['session_unique'], ['name' => 's_metric_actions_session_unique_index']);
            $table->addIndex(['user_unique'], ['name' => 's_metric_actions_user_unique_index']);
            $table->addIndex(['date_added'], ['name' => 's_metric_actions_date_added_index']);
            $table->create();
        }

        if (!$this->hasTable('s_metric_game')) {
            $table = $this->table('s_metric_game', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('score', 'integer', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('start_time', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('end_time', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('is_mobile', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0', 'signed' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_metric_goals')) {
            $table = $this->table('s_metric_goals', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Список целей']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('title', 'string', ['limit' => 128, 'null' => false]);
            $table->addColumn('description', 'text', ['null' => true]);
            $table->addColumn('type', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('validate_client_type', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'comment' => '1 - проверяет и обновляет цели при авторизации']);
            $table->addColumn('status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['type'], ['name' => 's_metric_goals_type_index']);
            $table->create();
        }

        if (!$this->hasTable('s_migrations')) {
            $table = $this->table('s_migrations', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('migration', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('batch', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_missing_activity')) {
            $table = $this->table('s_missing_activity', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('phone_mobile', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('UTC', 'string', ['limit' => 45, 'null' => true, 'default' => null]);
            $table->addColumn('missing_real_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('continue_order', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true, 'default' => null]);
            $table->addColumn('address_data_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('accept_data_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('card_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('files_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('additional_data_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('missing_manager_update_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('hour_date_add', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('personal_data_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('failed_status', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('send', 'integer', ['null' => true, 'default' => '0']);
            $table->create();
        }

        if (!$this->hasTable('s_mobileid')) {
            $table = $this->table('s_mobileid', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Для взаимодействия с mobileid']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 11, 'null' => true, 'default' => null]);
            $table->addColumn('request_payload', 'json', ['null' => true, 'default' => null]);
            $table->addColumn('response_payload', 'json', ['null' => true, 'default' => null]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('webhook_payload', 'json', ['null' => true, 'default' => null]);
            $table->addColumn('transaction_id', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('is_push_denied', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('is_sms', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('otp_validate_url', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('is_session_finished', 'boolean', ['null' => true, 'default' => '0', 'comment' => 'Была ли завершена сессия на стороне мегафона']);
            $table->addIndex(['id'], ['name' => 's_mobileid_pk2', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_moratorium_rating')) {
            $table = $this->table('s_moratorium_rating', ['id' => false, 'primary_key' => ['user_id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'таблица для проверки покупки кредитного рейтинга, и открытия доступа отправки фейковой заявки займа']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('date_order_added', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата фейковой заявки']);
            $table->create();
        }

        if (!$this->hasTable('s_multipolis')) {
            $table = $this->table('s_multipolis', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Мультиполисы купленные пользователем']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('number', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('payment_method', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('payment_id', 'integer', ['null' => false, 'comment' => 'id транзакции в Б2П или Тинькоф']);
            $table->addColumn('amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('amount_total_returned', 'integer', ['null' => false, 'default' => '0', 'comment' => 'total returned amount']);
            $table->addColumn('status', 'string', ['limit' => 16, 'null' => false]);
            $table->addColumn('organization_id', 'integer', ['null' => false, 'default' => '1']);
            $table->addColumn('is_sent', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('return_status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('return_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('return_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('return_transaction_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('return_sent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('return_by_user', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('return_by_manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('dop1c_sent', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Отправлена ли доп услуга в доповую 1с']);
            $table->addColumn('dop1c_sent_return', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Отправлен ли возврат доп услуги в доповую 1с']);
            $table->addIndex(['date_added'], ['name' => 's_multipolis_date_added_index']);
            $table->addIndex(['order_id'], ['name' => 's_multipolis_order_id_index']);
            $table->addIndex(['payment_method'], ['name' => 's_multipolis_payment_method_index']);
            $table->addIndex(['status'], ['name' => 's_multipolis_status_index']);
            $table->addIndex(['user_id'], ['name' => 's_multipolis_user_id_index']);
            $table->addIndex(['is_sent'], ['name' => 's_multipolis_is_sent_index']);
            $table->addIndex(['payment_id'], ['name' => 's_multipolis_payment_id_index']);
            $table->addIndex(['return_status'], ['name' => 'return_status']);
            $table->addIndex(['return_date'], ['name' => 'return_date']);
            $table->addIndex(['return_transaction_id'], ['name' => 'return_transaction_id']);
            $table->addIndex(['return_sent'], ['name' => 'return_sent']);
            $table->addIndex(['dop1c_sent_return'], ['name' => 'dop1c_sent_return']);
            $table->addIndex(['dop1c_sent'], ['name' => 'dop1c_sent']);
            $table->addIndex(['order_id', 'status'], ['name' => 'order_id']);
            $table->create();
        }

        if (!$this->hasTable('s_multipolis_form')) {
            $table = $this->table('s_multipolis_form', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Заявки с формы мультиполиса']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('multipolis_number', 'string', ['limit' => 64, 'null' => false]);
            $table->addColumn('firstname', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('lastname', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('patronymic', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('phone_mobile', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('multipolis_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date_created', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['multipolis_id'], ['name' => 's_multipolis_form_s_multipolis_null_fk']);
            $table->addForeignKey(['multipolis_id'], 's_multipolis', ['id'], ['constraint' => 's_multipolis_form_s_multipolis_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets')) {
            $table = $this->table('s_mytickets', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('client_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('chanel_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('department_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('subject_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('status_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('description', 'string', ['limit' => 1000, 'null' => true, 'default' => null, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('data', 'json', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('priority_id', 'integer', ['null' => true, 'default' => '1']);
            $table->addColumn('client_status', 'string', ['limit' => 10, 'null' => false, 'default' => 'new', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('is_repeat', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'biginteger', ['null' => true, 'default' => '0']);
            $table->addColumn('initiator_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('company_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('accepted_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('closed_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('working_time', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('responsible_person_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('is_duplicate', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('main_ticket_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('duplicates_count', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('feedback_received', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('notify_user', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0']);
            $table->addColumn('final_comment', 'text', ['null' => true, 'comment' => 'Последний комментарий при закрытии тикета по взысканию', 'collation' => 'utf8mb4_general_ci']);
            $table->addIndex(['chanel_id'], ['name' => 'idx_chanel_id']);
            $table->addIndex(['manager_id'], ['name' => 'idx_manager_id']);
            $table->addIndex(['subject_id'], ['name' => 'idx_subject_id']);
            $table->addIndex(['status_id'], ['name' => 'idx_status_id']);
            $table->addIndex(['client_id'], ['name' => 'client_id']);
            $table->addIndex(['responsible_person_id'], ['name' => 'fk_responsible_person']);
            $table->addIndex(['is_duplicate', 'main_ticket_id'], ['name' => 'idx_duplicate_main']);
            $table->addIndex(['manager_id', 'status_id', 'closed_at', 'created_at'], ['name' => 'idx_manager_status_closed_created']);
            $table->addForeignKey(['responsible_person_id'], 's_responsible_persons', ['id'], ['constraint' => 'fk_responsible_person', 'delete' => 'SET_NULL']);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_channels')) {
            $table = $this->table('s_mytickets_channels', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('is_active', 'boolean', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_comments')) {
            $table = $this->table('s_mytickets_comments', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('ticket_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('text', 'text', ['null' => true, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('is_show', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_departments')) {
            $table = $this->table('s_mytickets_departments', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_messages')) {
            $table = $this->table('s_mytickets_messages', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('ticket_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('body', 'text', ['null' => true]);
            $table->addColumn('files', 'json', ['null' => true, 'default' => null]);
            $table->addColumn('is_manager', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('email', 'text', ['null' => true]);
            $table->addColumn('unique_hash', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addIndex(['unique_hash'], ['name' => 'unique_hash', 'unique' => true]);
            $table->addIndex(['is_manager'], ['name' => 'is_manager']);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_priority')) {
            $table = $this->table('s_mytickets_priority', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('color', 'string', ['limit' => 10, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_report_templates')) {
            $table = $this->table('s_mytickets_report_templates', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'comment' => 'Название шаблона', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('data', 'json', ['null' => false, 'comment' => 'JSON с настройками фильтров и полей']);
            $table->addIndex(['name'], ['name' => 'idx_name', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_results')) {
            $table = $this->table('s_mytickets_results', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('subject_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_statuses')) {
            $table = $this->table('s_mytickets_statuses', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('color', 'string', ['limit' => 10, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_subjects')) {
            $table = $this->table('s_mytickets_subjects', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('parent_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('yandex_goal_id', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('uid', 'string', ['limit' => 36, 'null' => true, 'default' => null, 'comment' => 'UID темы в 1С', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('responsible_person_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('is_active', 'boolean', ['null' => true, 'default' => '1']);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_templates')) {
            $table = $this->table('s_mytickets_templates', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('description', 'text', ['null' => false]);
            $table->addColumn('name', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_mytickets_topics')) {
            $table = $this->table('s_mytickets_topics', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('subject', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('description', 'text', ['null' => true, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_nbki_items')) {
            $table = $this->table('s_nbki_items', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('report_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('contract_number', 'string', ['limit' => 20, 'null' => false, 'default' => '']);
            $table->addColumn('type', 'enum', ['values' => ['pay', 'p2p', 'cession', 'recompense', ''], 'null' => false]);
            $table->addColumn('external_id', 'integer', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('onec_data', 'text', ['null' => true]);
            $table->addColumn('operation_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['external_id', 'type'], ['name' => 'external_id', 'unique' => true]);
            $table->addIndex(['report_id'], ['name' => 'report_id']);
            $table->addIndex(['type'], ['name' => 'type']);
            $table->addIndex(['operation_date'], ['name' => 'operation_date']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->create();
        }

        if (!$this->hasTable('s_nbki_reports')) {
            $table = $this->table('s_nbki_reports', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('filename', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('sent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->create();
        }

        if (!$this->hasTable('s_operation_types')) {
            $table = $this->table('s_operation_types', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'identity' => true]);
            $table->addColumn('type', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('title', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addIndex(['type'], ['name' => 'type', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_operations')) {
            $table = $this->table('s_operations', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('contract_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('operation_type_id', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false]);
            $table->addColumn('transaction_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'comment' => 'сумма операции']);
            $table->addColumn('operation_date', 'date', ['null' => true, 'default' => null, 'comment' => 'Дата начисления']);
            $table->addColumn('payment_date', 'date', ['null' => true, 'default' => null, 'comment' => 'Дата платежа']);
            $table->addColumn('loan_body_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Остаток ОД после операции']);
            $table->addColumn('loan_percents_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Остаток процентов после операции']);
            $table->addColumn('loan_charge_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Остаток доп процентов после операции']);
            $table->addColumn('loan_peni_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Остаток пеней после операции']);
            $table->addColumn('loan_penalty_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Остаток штрафа после операции']);
            $table->addColumn('create_date', 'datetime', ['null' => false, 'comment' => 'Дата создания']);
            $table->addColumn('day_expired', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => false, 'default' => '0']);
            $table->addColumn('from_onec', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('onec_sent', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Результат отправки в 1с операции']);
            $table->addColumn('onec_sent_date', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата отправки в 1с операции']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['contract_id'], ['name' => 'contract_id']);
            $table->addIndex(['transaction_id'], ['name' => 'transaction_id']);
            $table->addIndex(['create_date'], ['name' => 'create_date']);
            $table->addIndex(['onec_sent'], ['name' => 'onec_sent']);
            $table->addIndex(['payment_date'], ['name' => 'payment_date']);
            $table->addIndex(['operation_date'], ['name' => 'operation_date']);
            $table->addIndex(['operation_type_id'], ['name' => 'operation_type_id']);
            $table->addIndex(['operation_type_id', 'operation_date'], ['name' => 'type_2']);
            $table->addIndex(['day_expired'], ['name' => 'day_expired']);
            $table->addIndex(['from_onec'], ['name' => 'from_1c']);
            $table->create();
        }

        if (!$this->hasTable('s_order_data')) {
            $table = $this->table('s_order_data', ['id' => false, 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('order_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('key', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('value', 'string', ['limit' => 1000, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('updated', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['order_id', 'key'], ['name' => 'order_id_key', 'unique' => true]);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['key'], ['name' => 'key']);
            $table->addIndex(['updated'], ['name' => 's_order_data_updated_index']);
            $table->create();
        }

        if (!$this->hasTable('s_order_payout_grade')) {
            $table = $this->table('s_order_payout_grade', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Выплаты по постбекам']);
            $table->addColumn('id', 'integer', ['null' => false, 'comment' => 'id', 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('utm_source', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['date_added'], ['name' => 's_order_payout_grade_date_added_index']);
            $table->addIndex(['order_id'], ['name' => 's_order_payout_grade_order_id_index']);
            $table->addIndex(['utm_source'], ['name' => 's_order_payout_grade_utm_source_index']);
            $table->create();
        }

        if (!$this->hasTable('s_order_status_logs')) {
            $table = $this->table('s_order_status_logs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('old_status', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('status', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('old_status_1c', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('status_1c', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('front', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Изменения пришли с сайта, не с CRM']);
            $table->addIndex(['date_added'], ['name' => 's_order_status_logs_date_added_index']);
            $table->addIndex(['order_id'], ['name' => 's_order_status_logs_order_id_index']);
            $table->addIndex(['status_1c'], ['name' => 's_order_status_logs_status_1c_index']);
            $table->addIndex(['status'], ['name' => 's_order_status_logs_status_index']);
            $table->create();
        }

        if (!$this->hasTable('s_orders')) {
            $table = $this->table('s_orders', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('contract_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('cdoctor_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('accept_sms', 'string', ['limit' => 12, 'null' => true, 'default' => null]);
            $table->addColumn('accept_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('accept_try', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('manager_change_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('call_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('confirm_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('approve_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('reject_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('card_id', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('card_type', 'string', ['limit' => 20, 'null' => true, 'default' => 'card']);
            $table->addColumn('sbp_account_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'ID СБП счета для выплаты в b2p_sbp_accounts', 'signed' => false]);
            $table->addColumn('delivery_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('delivery_price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('payment_method_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('paid', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('payment_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('closed', 'boolean', ['null' => false]);
            $table->addColumn('date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('local_time', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('uid', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'UID пользователя в 1С']);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('address', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('phone', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('email', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('comment', 'text', ['null' => false]);
            $table->addColumn('status', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('url', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('payment_details', 'text', ['null' => false]);
            $table->addColumn('ip', 'string', ['limit' => 15, 'null' => false]);
            $table->addColumn('total_price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('note', 'string', ['limit' => 1024, 'null' => false]);
            $table->addColumn('discount', 'decimal', ['precision' => 5, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('coupon_discount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('coupon_code', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('separate_delivery', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('modified', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('amount', 'integer', ['null' => false]);
            $table->addColumn('approve_amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('period', 'integer', ['null' => false]);
            $table->addColumn('selected_period', 'integer', ['null' => true, 'default' => null, 'comment' => 'Необходимо для бекапа периода, при изменении периода через Ajax']);
            $table->addColumn('percent', 'decimal', ['precision' => 6, 'scale' => 2, 'null' => true, 'default' => '0.80']);
            $table->addColumn('first_loan', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('sent_1c', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('sms', 'string', ['limit' => 6, 'null' => false]);
            $table->addColumn('1c_id', 'string', ['limit' => 15, 'null' => false]);
            $table->addColumn('1c_status', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('official_response', 'text', ['null' => true]);
            $table->addColumn('reason_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('crm_response', 'text', ['null' => true]);
            $table->addColumn('utm_source', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('utm_medium', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('utm_campaign', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('utm_content', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('utm_term', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('webmaster_id', 'string', ['limit' => 70, 'null' => true, 'default' => null]);
            $table->addColumn('click_hash', 'string', ['limit' => 70, 'null' => false]);
            $table->addColumn('juicescore_session_id', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('scorista_sms_sent', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('have_close_credits', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('pay_result', 'string', ['limit' => 1024, 'null' => true, 'default' => null]);
            $table->addColumn('razgon', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('max_amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('min_period', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true, 'default' => null]);
            $table->addColumn('max_period', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true, 'default' => null]);
            $table->addColumn('loan_type', 'enum', ['values' => ['pdl', 'il', ''], 'null' => false, 'default' => 'PDL']);
            $table->addColumn('payment_period', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => false, 'default' => '1']);
            $table->addColumn('stage1', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('stage1_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('stage2', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('stage2_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('stage3', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('stage3_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('stage4', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('stage4_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('stage5', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('stage5_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('call_variants', 'text', ['null' => false]);
            $table->addColumn('leadgid_postback_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('credit_getted', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('b2p', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('autoretry', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('number_of_signing_errors', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('insurer', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('insure_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => '0.00']);
            $table->addColumn('insure_percent', 'decimal', ['precision' => 4, 'scale' => 2, 'null' => true, 'default' => '0.00']);
            $table->addColumn('scorista_ball', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('is_credit_doctor', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('is_default_way', 'integer', ['null' => true, 'default' => null, 'comment' => 'new flow for nk']);
            $table->addColumn('is_discount_way', 'integer', ['null' => true, 'default' => null, 'comment' => 'new flow for nk']);
            $table->addColumn('payout_grade', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('leadgen_postback', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('send_user_info_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('order_uid', 'string', ['limit' => 40, 'null' => false, 'default' => '']);
            $table->addColumn('complete', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('promocode', 'biginteger', ['null' => true, 'default' => null, 'comment' => 'Promocode ID', 'signed' => false]);
            $table->addColumn('is_user_credit_doctor', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('not_received_loan_manager_id', 'integer', ['null' => false, 'default' => '0', 'comment' => 'Менеджер, закрепленный за займом в \"Неполученных займах\"']);
            $table->addColumn('not_received_loan_manager_update_date', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата добавления отвественного по \"Неполученному займу\"']);
            $table->addColumn('will_client_receive_loan', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true, 'default' => null, 'comment' => 'Получит ли, по его словам, клиент займ. Данные для листинга \"Неполученные займы\"', 'signed' => false]);
            $table->addColumn('pti_loan', 'float', ['null' => true, 'default' => null, 'comment' => 'ПДН по Росстату']);
            $table->addColumn('pti_order', 'float', ['null' => true, 'default' => null, 'comment' => 'ПДН по доходу из заявки', 'signed' => false]);
            $table->addColumn('pdn_notification_shown', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0', 'comment' => 'Показано уведомление о превышении ПДН', 'signed' => false]);
            $table->addColumn('additional_service', 'boolean', ['null' => false, 'default' => '1']);
            $table->addColumn('additional_service_repayment', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('additional_service_partial_repayment', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('deleteKD', 'boolean', ['null' => true, 'default' => null, 'comment' => 'для ШКД ']);
            $table->addColumn('organization_id', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '1']);
            $table->addColumn('pdn_nkbi_loan', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('pdn_nkbi_order', 'float', ['null' => true, 'default' => null]);
            $table->addColumn('cancellation_additional_services_by_phone', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Cancellation of additional services by phone']);
            $table->addColumn('salary_for_pti_3', 'float', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 'login']);
            $table->addIndex(['closed'], ['name' => 'written_off']);
            $table->addIndex(['status'], ['name' => 'status']);
            $table->addIndex(['url'], ['name' => 'code']);
            $table->addIndex(['paid'], ['name' => 'payment_status']);
            $table->addIndex(['uid'], ['name' => 'uid']);
            $table->addIndex(['click_hash'], ['name' => 'click_hash']);
            $table->addIndex(['1c_status'], ['name' => '1c_status']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['amount'], ['name' => 'amount']);
            $table->addIndex(['period'], ['name' => 'period']);
            $table->addIndex(['scorista_sms_sent'], ['name' => 'scorista_sms_sent']);
            $table->addIndex(['reason_id'], ['name' => 'reason_id']);
            $table->addIndex(['accept_date'], ['name' => 'accept_date']);
            $table->addIndex(['confirm_date'], ['name' => 'confirm_date']);
            $table->addIndex(['approve_date'], ['name' => 'approve_date']);
            $table->addIndex(['reject_date'], ['name' => 'reject_date']);
            $table->addIndex(['call_date'], ['name' => 'call_date']);
            $table->addIndex(['cdoctor_id'], ['name' => 'cdoctor_id']);
            $table->addIndex(['1c_id'], ['name' => '1c_id']);
            $table->addIndex(['utm_source'], ['name' => 'utm_source']);
            $table->addIndex(['have_close_credits'], ['name' => 'have_close_credits']);
            $table->addIndex(['leadgid_postback_date'], ['name' => 'leadgid_postback_date']);
            $table->addIndex(['credit_getted'], ['name' => 'credit_getted']);
            $table->addIndex(['autoretry'], ['name' => 'autoretry']);
            $table->addIndex(['is_credit_doctor'], ['name' => 'is_credit_doctor']);
            $table->addIndex(['promocode'], ['name' => 'FK_s_orders_s_promocodes']);
            $table->addIndex(['is_user_credit_doctor'], ['name' => 's_orders_is_user_credit_doctor_index']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['date'], ['name' => 'date']);
            $table->addIndex(['card_id'], ['name' => 'card_id']);
            $table->addIndex(['contract_id'], ['name' => 'contract_id']);
            $table->addIndex(['loan_type'], ['name' => 'loan_type']);
            $table->addIndex(['organization_id'], ['name' => 'organization_id']);
            $table->addIndex(['complete'], ['name' => 'complete']);
            $table->addIndex(['order_uid'], ['name' => 's_orders_order_uid_index']);
            $table->addIndex(['modified'], ['name' => 'modified']);
            $table->addIndex(['ip'], ['name' => 'ip']);
            $table->addIndex(['utm_term'], ['name' => 'utm_term']);
            $table->addIndex(['utm_medium'], ['name' => 'utm_medium']);
            $table->addForeignKey(['promocode'], 's_promocodes', ['id'], ['constraint' => 'FK_s_orders_s_promocodes']);
            $table->create();
        }

        if (!$this->hasTable('s_orders_auto_approve')) {
            $table = $this->table('s_orders_auto_approve', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('pk_type', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('status', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_end', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['order_id'], ['name' => 's_orders_auto_approve_order_id_index']);
            $table->addIndex(['user_id'], ['name' => 's_orders_auto_approve_user_id_index']);
            $table->addIndex(['status'], ['name' => 's_orders_auto_approve_status_index']);
            $table->create();
        }

        if (!$this->hasTable('s_orders_labels')) {
            $table = $this->table('s_orders_labels', ['id' => false, 'primary_key' => ['order_id', 'label_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('label_id', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_organizations')) {
            $table = $this->table('s_organizations', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('short_name', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('email', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('phone', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('phone2', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('inn', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('kpp', 'string', ['limit' => 45, 'null' => true, 'default' => null]);
            $table->addColumn('bank', 'string', ['limit' => 200, 'null' => true, 'default' => null]);
            $table->addColumn('address', 'text', ['null' => true]);
            $table->addColumn('site', 'string', ['limit' => 45, 'null' => true, 'default' => null]);
            $table->addColumn('b2p_split_code', 'string', ['limit' => 10, 'null' => false, 'default' => '']);
            $table->addColumn('onec_code', 'string', ['limit' => 10, 'null' => false, 'default' => '']);
            $table->addColumn('agent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('ogrn', 'string', ['limit' => 32, 'null' => true, 'default' => null, 'comment' => 'ОГРН']);
            $table->addColumn('registry_number', 'string', ['limit' => 32, 'null' => true, 'default' => null, 'comment' => 'Номер в государственном реестре микрофинансовых организаций ']);
            $table->addColumn('registry_date', 'date', ['null' => true, 'default' => null, 'comment' => 'Дата регистрации номера в реестре']);
            $table->addColumn('director', 'string', ['limit' => 128, 'null' => true, 'default' => null, 'comment' => 'ФИО Директора']);
            $table->addColumn('params', 'json', ['null' => true, 'default' => null, 'comment' => 'Дополнительные параметры']);
            $table->addColumn('contract_prefix', 'string', ['limit' => 6, 'null' => true, 'default' => null]);
            $table->addColumn('b2p_prefix', 'string', ['limit' => 20, 'null' => false, 'default' => '']);
            $table->addColumn('cross_orders', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->create();
        }

        if (!$this->hasTable('s_overdue_hide_service')) {
            $table = $this->table('s_overdue_hide_service', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Пользователи для которых надо скрывать покупку доп услуг при просрочке']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['phone'], ['name' => 's_overdue_hide_service_pk_2', 'unique' => true]);
            $table->addIndex(['phone'], ['name' => 's_overdue_hide_service_phone_index']);
            $table->create();
        }

        if (!$this->hasTable('s_pages')) {
            $table = $this->table('s_pages', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('url', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('template', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('meta_title', 'string', ['limit' => 500, 'null' => false]);
            $table->addColumn('meta_description', 'string', ['limit' => 500, 'null' => false]);
            $table->addColumn('meta_keywords', 'string', ['limit' => 500, 'null' => false]);
            $table->addColumn('body', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG, 'null' => false]);
            $table->addColumn('menu_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('position', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('visible', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('header', 'string', ['limit' => 1024, 'null' => false]);
            $table->addColumn('new_field', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('new_field2', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addIndex(['position'], ['name' => 'order_num']);
            $table->addIndex(['url'], ['name' => 'url']);
            $table->create();
        }

        if (!$this->hasTable('s_participant_codes')) {
            $table = $this->table('s_participant_codes', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('code', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['user_id'], ['name' => 'user_id', 'unique' => true]);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_participant_codes_ibfk_1', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_partner_href')) {
            $table = $this->table('s_partner_href', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'ссылки партнеров']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('href', 'text', ['null' => false]);
            $table->addColumn('link_type', 'string', ['limit' => 128, 'null' => true, 'default' => '', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('client_type', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addIndex(['link_type', 'client_type'], ['name' => 'link_type_client_type']);
            $table->create();
        }

        if (!$this->hasTable('s_partner_href_statistics')) {
            $table = $this->table('s_partner_href_statistics', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'клики, показы партнерских ссылок']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('href_id', 'integer', ['null' => false]);
            $table->addColumn('type_action', 'string', ['limit' => 12, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['href_id'], ['name' => 's_partner_href_statistics_href_id_index']);
            $table->addIndex(['user_id'], ['name' => 's_partner_href_statistics_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_passport_transactions')) {
            $table = $this->table('s_passport_transactions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'транзакции пользователей вошедших по паспорту']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('payment_id', 'integer', ['null' => false]);
            $table->addColumn('status', 'string', ['limit' => 10, 'null' => false]);
            $table->addColumn('amount', 'integer', ['null' => false]);
            $table->addColumn('user_uid', 'string', ['limit' => 64, 'null' => false]);
            $table->addColumn('loan_uid', 'string', ['limit' => 64, 'null' => false]);
            $table->addColumn('mfo_agreement', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_password')) {
            $table = $this->table('s_password', ['id' => false, 'primary_key' => ['user_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('salt', 'string', ['limit' => 16, 'null' => false]);
            $table->addColumn('hash', 'string', ['limit' => 64, 'null' => false]);
            $table->addColumn('incorrect_total', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_edit', 'datetime', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_payment_exitpool_variants')) {
            $table = $this->table('s_payment_exitpool_variants', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('variant', 'text', ['null' => false]);
            $table->addColumn('enabled', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('position', 'integer', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_payment_exitpools')) {
            $table = $this->table('s_payment_exitpools', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('response', 'text', ['null' => false]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->create();
        }

        if (!$this->hasTable('s_payment_methods')) {
            $table = $this->table('s_payment_methods', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('module', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('description', 'text', ['null' => false]);
            $table->addColumn('currency_id', 'float', ['null' => false]);
            $table->addColumn('settings', 'text', ['null' => false]);
            $table->addColumn('enabled', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('position', 'integer', ['null' => false]);
            $table->addIndex(['position'], ['name' => 'position']);
            $table->create();
        }

        if (!$this->hasTable('s_payments')) {
            $table = $this->table('s_payments', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Таблица с оплатами']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('summ', 'decimal', ['precision' => 10, 'scale' => 0, 'null' => false]);
            $table->addColumn('payment_method_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('payment_date', 'datetime', ['null' => false]);
            $table->addColumn('payment_details', 'text', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('status', 'boolean', ['null' => false, 'default' => '0']);
            $table->create();
        }

        if (!$this->hasTable('s_payments_rs')) {
            $table = $this->table('s_payments_rs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'biginteger', ['null' => false]);
            $table->addColumn('contract_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('attachment', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('status', 'enum', ['values' => ['new', 'cancelled', 'approved'], 'null' => false, 'default' => 'new', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['contract_id'], ['name' => 's_rs_payment_files_s_contracts_id_fk']);
            $table->addIndex(['order_id'], ['name' => 's_rs_payment_files_s_orders_id_fk']);
            $table->addIndex(['user_id'], ['name' => 's_rs_payment_files_s_users_id_fk']);
            $table->addForeignKey(['contract_id'], 's_contracts', ['id'], ['constraint' => 's_rs_payment_files_s_contracts_id_fk']);
            $table->addForeignKey(['order_id'], 's_orders', ['id'], ['constraint' => 's_rs_payment_files_s_orders_id_fk']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_rs_payment_files_s_users_id_fk']);
            $table->create();
        }

        if (!$this->hasTable('s_payout_grade')) {
            $table = $this->table('s_payout_grade', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Вознаграждения для постбеков']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('utm_source', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('conditions', 'text', ['null' => true, 'comment' => 'условия для формирования вознаграждения']);
            $table->addColumn('field', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['utm_source'], ['name' => 's_payout_grade_pk', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_pdn_calculation')) {
            $table = $this->table('s_pdn_calculation', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Заявки, для которых произвели расчет ПДН']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_uid', 'string', ['limit' => 40, 'null' => false, 'default' => '', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('contract_number', 'string', ['limit' => 20, 'null' => false, 'default' => '', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('date_create', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('success', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('processed_order_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'ID заявки клиента, по которому удалось рассчитать ПДН']);
            $table->addColumn('request', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('result', 'text', ['null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('debts_document_added', 'string', ['limit' => 1024, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['order_id'], ['name' => 's_pdn_calculation_order_id_uindex']);
            $table->addIndex(['order_uid'], ['name' => 's_pdn_calculation_order_uid_uindex']);
            $table->create();
        }

        if (!$this->hasTable('s_personal_access_tokens')) {
            $table = $this->table('s_personal_access_tokens', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('tokenable_type', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('tokenable_id', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('token', 'string', ['limit' => 64, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('abilities', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('last_used_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addIndex(['token'], ['name' => 's_personal_access_tokens_token_unique', 'unique' => true]);
            $table->addIndex(['tokenable_type', 'tokenable_id'], ['name' => 's_personal_access_tokens_tokenable_type_tokenable_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_postback')) {
            $table = $this->table('s_postback', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Для хранения отправленных постбеков']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('url', 'text', ['null' => true]);
            $table->addColumn('type', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('method', 'string', ['limit' => 16, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('response', 'text', ['null' => true]);
            $table->addColumn('request', 'text', ['null' => true]);
            $table->addIndex(['order_id'], ['name' => 's_postback_order_id_index']);
            $table->addIndex(['type'], ['name' => 's_postback_type_index']);
            $table->create();
        }

        if (!$this->hasTable('s_pr_tasks')) {
            $table = $this->table('s_pr_tasks', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('number', 'string', ['limit' => 15, 'null' => true, 'default' => null]);
            $table->addColumn('ticketId', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('task_date', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('user_balance_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('close', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('prolongation', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('od_start', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => '0.00']);
            $table->addColumn('percents_start', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => '0.00']);
            $table->addColumn('period', 'enum', ['values' => ['minus2', 'zero', 'plus3', 'plus1', 'period_one_two', 'minus1', 'minus3', '-1', '-2', '-3', '-4', '-5'], 'null' => false]);
            $table->addColumn('paid', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('timezone', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false]);
            $table->addColumn('recall_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('perspective_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('sent_task', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('marked', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('vox_call', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('sms_send', 'boolean', ['null' => true, 'default' => '0']);
            $table->addIndex(['number'], ['name' => 'number']);
            $table->addIndex(['user_balance_id'], ['name' => 'user_balance_id']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['task_date'], ['name' => 'task_date']);
            $table->addIndex(['status'], ['name' => 'status']);
            $table->addIndex(['period'], ['name' => 'period']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['timezone'], ['name' => 'timezone']);
            $table->addIndex(['close'], ['name' => 'close']);
            $table->addIndex(['prolongation'], ['name' => 'prolongation']);
            $table->create();
        }

        if (!$this->hasTable('s_pr_tasks_sms')) {
            $table = $this->table('s_pr_tasks_sms', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('period', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('date', 'date', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_pr_tasks_sms_daily')) {
            $table = $this->table('s_pr_tasks_sms_daily', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('period', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('date', 'date', ['null' => false]);
            $table->addColumn('sent', 'boolean', ['null' => false]);
            $table->addIndex(['user_id'], ['name' => 's_pr_tasks_sms_s_users_fk']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_pr_tasks_sms_s_users_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_promo_banners')) {
            $table = $this->table('s_promo_banners', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('banner_text', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('banner_img_bg', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('banner_level', 'integer', ['null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('banner_img_sm', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('additional_text', 'text', ['null' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_promocodes')) {
            $table = $this->table('s_promocodes', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Client']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('title', 'string', ['limit' => 250, 'null' => true, 'default' => '0', 'comment' => 'Promocode']);
            $table->addColumn('promocode', 'string', ['limit' => 6, 'null' => false, 'default' => '0', 'comment' => 'Promocode']);
            $table->addColumn('date_start', 'date', ['null' => false, 'comment' => 'Promocode']);
            $table->addColumn('date_end', 'date', ['null' => false, 'comment' => 'Promocode']);
            $table->addColumn('rate', 'decimal', ['precision' => 5, 'scale' => 2, 'null' => false, 'default' => '0.00', 'comment' => 'Promocode', 'signed' => false]);
            $table->addColumn('quantity', 'integer', ['null' => false, 'default' => '0', 'comment' => 'Total number of uses', 'signed' => false]);
            $table->addColumn('phone', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'comment' => 'Phone number for personal promocodes']);
            $table->addColumn('limit_sum', 'integer', ['null' => false, 'default' => '0', 'comment' => 'Loan', 'signed' => false]);
            $table->addColumn('limit_term', 'integer', ['null' => false, 'default' => '0', 'comment' => 'Loan', 'signed' => false]);
            $table->addColumn('manager_id', 'integer', ['null' => false, 'comment' => 'Promocode']);
            $table->addColumn('disable_additional_services', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('is_mandatory_issue', 'boolean', ['null' => true, 'default' => '0']);
            $table->addIndex(['promocode'], ['name' => 'promocode', 'unique' => true]);
            $table->addIndex(['manager_id'], ['name' => 'FK_s_promocodes_s_managers']);
            $table->addForeignKey(['manager_id'], 's_managers', ['id'], ['constraint' => 'FK_s_promocodes_s_managers']);
            $table->create();
        }

        if (!$this->hasTable('s_purchases')) {
            $table = $this->table('s_purchases', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('product_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('variant_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('product_name', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('variant_name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('amount', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('sku', 'string', ['limit' => 255, 'null' => false]);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['product_id'], ['name' => 'product_id']);
            $table->addIndex(['variant_id'], ['name' => 'variant_id']);
            $table->create();
        }

        if (!$this->hasTable('s_queue')) {
            $table = $this->table('s_queue', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('method', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('url', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('call', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('data', 'json', ['null' => false]);
            $table->addColumn('sent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('timer', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('created_date', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('tag', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => false, 'default' => '0']);
            $table->create();
        }

        if (!$this->hasTable('s_reasons')) {
            $table = $this->table('s_reasons', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('admin_name', 'text', ['null' => false]);
            $table->addColumn('client_name', 'text', ['null' => false]);
            $table->addColumn('type', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('maratory', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('refusal_note', 'text', ['null' => true]);
            $table->addIndex(['type'], ['name' => 'type']);
            $table->create();
        }

        if (!$this->hasTable('s_receipts')) {
            $table = $this->table('s_receipts', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Чеки']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('payment_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'id платежа b2p_payments.id']);
            $table->addColumn('transaction_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'Используется при выдаче, это b2p_transactions.id (КД)
            Для какой транзакии использовался возврат']);
            $table->addColumn('payment_method', 'string', ['limit' => 16, 'null' => true, 'default' => null, 'comment' => 'Тип оплаты']);
            $table->addColumn('description', 'text', ['null' => true, 'comment' => 'Описание услуги']);
            $table->addColumn('payment_type', 'string', ['limit' => 32, 'null' => true, 'default' => null, 'comment' => 'Название услуги']);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'id пользователя s_users.id']);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'id заявки s_orders.id']);
            $table->addColumn('organization_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'id организации s_organizations.id']);
            $table->addColumn('is_sent', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0', 'comment' => 'Отправлен или нет (0,1)']);
            $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('success', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'comment' => 'Успешно ли отправлен чек']);
            $table->addColumn('receipt_id', 'string', ['limit' => 32, 'null' => true, 'default' => null, 'comment' => 'id чека в системе Клаудкассир']);
            $table->addColumn('receipt_url', 'string', ['limit' => 64, 'null' => true, 'default' => null, 'comment' => 'Url чека в системе Клаудкассир']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'дата создания задания']);
            $table->addColumn('modified_date', 'datetime', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP', 'comment' => 'дата модификации данных']);
            $table->addIndex(['order_id'], ['name' => 's_receipts_order_id_index']);
            $table->addIndex(['payment_id'], ['name' => 's_receipts_payment_id_index']);
            $table->addIndex(['user_id'], ['name' => 's_receipts_user_id_index']);
            $table->addIndex(['organization_id'], ['name' => 's_receipts_organization_id_index']);
            $table->addIndex(['success'], ['name' => 's_receipts_success_index']);
            $table->addIndex(['is_sent', 'date_added'], ['name' => 's_receipts_is_sent_date_added_index']);
            $table->addIndex(['payment_type'], ['name' => 's_receipts_payment_type_index']);
            $table->addIndex(['transaction_id'], ['name' => 's_receipts_transaction_id_index']);
            $table->addIndex(['receipt_id'], ['name' => 's_receipts_receipt_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_recurrents')) {
            $table = $this->table('s_recurrents', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('list_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('number', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('od', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('percents', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('payment_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('expired', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('client', 'string', ['limit' => 512, 'null' => false]);
            $table->addColumn('client_uid', 'string', ['limit' => 40, 'null' => true, 'default' => null]);
            $table->addColumn('status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('getted_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('getted_percents', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('string_result', 'text', ['null' => false]);
            $table->addColumn('created', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('checked', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('payment_type', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addIndex(['number'], ['name' => 'zaym']);
            $table->addIndex(['client'], ['name' => 'client']);
            $table->addIndex(['status'], ['name' => 'checked']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->addIndex(['client_uid'], ['name' => 'client_uid']);
            $table->addIndex(['list_id'], ['name' => 'list_id']);
            $table->addIndex(['list_id', 'number'], ['name' => 'list_id_2']);
            $table->create();
        }

        if (!$this->hasTable('s_recurrents_list')) {
            $table = $this->table('s_recurrents_list', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('list_uid', 'string', ['limit' => 40, 'null' => false, 'encoding' => 'utf8mb3']);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('loaded', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('sent_1c', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('sent_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['list_uid'], ['name' => 'uid']);
            $table->addIndex(['sent_1c'], ['name' => 'sent_1c']);
            $table->addIndex(['loaded'], ['name' => 'loaded']);
            $table->create();
        }

        if (!$this->hasTable('s_referrals')) {
            $table = $this->table('s_referrals', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('utm_source', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('utm_medium', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('utm_campaign', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('utm_content', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('utm_term', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('webmaster_id', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('click_hash', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('link', 'text', ['null' => true]);
            $table->addColumn('created', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('ip', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('user_agent', 'text', ['null' => true]);
            $table->addColumn('referer', 'text', ['null' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addIndex(['utm_source'], ['name' => 'utm_source']);
            $table->addIndex(['webmaster_id'], ['name' => 'webmaster_id']);
            $table->addIndex(['ip'], ['name' => 'ip']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['click_hash'], ['name' => 'click_hash']);
            $table->create();
        }

        if (!$this->hasTable('s_reject_queue')) {
            $table = $this->table('s_reject_queue', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Очередь отклоненных заявок для лидстеха']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('order_id', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('reject_date', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('response', 'text', ['null' => true]);
            $table->addIndex(['order_id'], ['name' => 'order_id', 'unique' => true]);
            $table->addIndex(['reject_date'], ['name' => 's_reject_queue_reject_date_index']);
            $table->create();
        }

        if (!$this->hasTable('s_related_products')) {
            $table = $this->table('s_related_products', ['id' => false, 'primary_key' => ['product_id', 'related_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('product_id', 'integer', ['null' => false]);
            $table->addColumn('related_id', 'integer', ['null' => false]);
            $table->addColumn('position', 'integer', ['null' => false]);
            $table->addIndex(['position'], ['name' => 'position']);
            $table->create();
        }

        if (!$this->hasTable('s_responsible_persons')) {
            $table = $this->table('s_responsible_persons', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('code', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('is_sync_available', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('uid', 'string', ['limit' => 36, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('role', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('group_uid', 'string', ['limit' => 36, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('group_name', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['uid'], ['name' => 'uk_uid', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_rosstat_incomes')) {
            $table = $this->table('s_rosstat_incomes', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Данные Росстата по среднедушевым доходам в разрезе регионов']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('start_date', 'date', ['null' => false]);
            $table->addColumn('include_date', 'date', ['null' => false]);
            $table->addColumn('income', 'integer', ['null' => false, 'default' => '0', 'signed' => false]);
            $table->addColumn('region_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addIndex(['region_id'], ['name' => 'region_id']);
            $table->addIndex(['start_date'], ['name' => 'period']);
            $table->addIndex(['include_date'], ['name' => 'include_date']);
            $table->addForeignKey(['region_id'], 's_rosstat_regions', ['id'], ['constraint' => 'FK_s_rosstat_incomes_s_rosstat_regions']);
            $table->create();
        }

        if (!$this->hasTable('s_rosstat_regions')) {
            $table = $this->table('s_rosstat_regions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Список регионов для зарплат по Росстату']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('region', 'string', ['limit' => 200, 'null' => false, 'default' => '0', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['region'], ['name' => 'region', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_rosstat_salaries')) {
            $table = $this->table('s_rosstat_salaries', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Данные Росстата по зарплатам в разрезе регионов']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('year', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => false, 'default' => '0', 'signed' => false]);
            $table->addColumn('month', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0', 'signed' => false]);
            $table->addColumn('salary', 'integer', ['null' => false, 'default' => '0', 'signed' => false]);
            $table->addColumn('region_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addIndex(['region_id'], ['name' => 'region_id']);
            $table->addForeignKey(['region_id'], 's_rosstat_regions', ['id'], ['constraint' => 'FK_s_rosstat_salaries_s_rosstat_regions']);
            $table->create();
        }

        if (!$this->hasTable('s_scoring_body')) {
            $table = $this->table('s_scoring_body', ['id' => false, 'primary_key' => ['scoring_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('scoring_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('body', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG, 'null' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_scoring_efrsb')) {
            $table = $this->table('s_scoring_efrsb', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('inn', 'string', ['limit' => 12, 'null' => true, 'default' => null]);
            $table->addColumn('status', 'enum', ['values' => ['new', 'process', 'stopped', 'completed', 'error', 'import', 'wait'], 'null' => false, 'comment' => 'new, process, stopped ,completed, error, import']);
            $table->addColumn('body', 'string', ['limit' => 4096, 'null' => true, 'default' => null]);
            $table->addColumn('success', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('created', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('string_result', 'string', ['limit' => 2048, 'null' => true, 'default' => null]);
            $table->addColumn('start_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('end_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('bankruptcy_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id', 'order_id'], ['name' => 'entry', 'unique' => true]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['status'], ['name' => 'status']);
            $table->addIndex(['start_date'], ['name' => 'start_date']);
            $table->addIndex(['success'], ['name' => 'success']);
            $table->create();
        }

        if (!$this->hasTable('s_scoring_manager')) {
            $table = $this->table('s_scoring_manager', ['id' => false, 'primary_key' => ['scoring_id', 'manager_id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('scoring_id', 'integer', ['null' => false]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_scoring_types')) {
            $table = $this->table('s_scoring_types', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('title', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('short_title', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('type', 'string', ['limit' => 10, 'null' => false, 'default' => 'first']);
            $table->addColumn('negative_action', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('params', 'text', ['null' => true]);
            $table->addColumn('toll', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('active', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('position', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_scorings')) {
            $table = $this->table('s_scorings', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('audit_id', 'integer', ['null' => true, 'default' => '0', 'signed' => false]);
            $table->addColumn('type', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0', 'comment' => 's_scoring_types', 'signed' => false]);
            $table->addColumn('status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0', 'comment' => 'Список статусов в api/Scorings.php', 'signed' => false]);
            $table->addColumn('success', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('created', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('scorista_id', 'string', ['limit' => 72, 'null' => true, 'default' => null]);
            $table->addColumn('scorista_status', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('scorista_ball', 'string', ['limit' => 10, 'null' => true, 'default' => null]);
            $table->addColumn('string_result', 'text', ['null' => true]);
            $table->addColumn('start_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('end_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('manual', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('next_run_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['scorista_id'], ['name' => 'scorista_id']);
            $table->addIndex(['type'], ['name' => 'type']);
            $table->addIndex(['status'], ['name' => 'status']);
            $table->addIndex(['start_date'], ['name' => 'start_date']);
            $table->addIndex(['success'], ['name' => 'success']);
            $table->addIndex(['manual'], ['name' => 'manual']);
            $table->addIndex(['type', 'status'], ['name' => 'type_2']);
            $table->addIndex(['order_id', 'status', 'type'], ['name' => 'order_id_2']);
            $table->addIndex(['manual', 'status'], ['name' => 'manual_2']);
            $table->addIndex(['next_run_at'], ['name' => 'idx_scorings_next_run_at']);
            $table->create();
        }

        if (!$this->hasTable('s_service_recovery_exclusions')) {
            $table = $this->table('s_service_recovery_exclusions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Список клиентов и услуг, исключенных из автоматического возобновления']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('service_key', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('reason', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('expires_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('deleted_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id', 'order_id', 'service_key', 'deleted_at'], ['name' => 'idx_user_order_service_active', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_service_recovery_process_logs')) {
            $table = $this->table('s_service_recovery_process_logs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Логи выполнения процесса автоматического возобновления услуг']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('started_at', 'datetime', ['null' => false]);
            $table->addColumn('finished_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('run_type', 'string', ['limit' => 20, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('rule_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('processed_candidates', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('reenabled_count', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('message', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('status', 'string', ['limit' => 20, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('error_details', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->create();
        }

        if (!$this->hasTable('s_service_recovery_revenue')) {
            $table = $this->table('s_service_recovery_revenue', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Трекинг доходов от восстановленных дополнительных услуг']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('process_log_id', 'integer', ['null' => false]);
            $table->addColumn('rule_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('service_key', 'string', ['limit' => 100, 'null' => false, 'comment' => 'Ключ восстановленной услуги', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('reenabled_at', 'datetime', ['null' => false]);
            $table->addColumn('payment_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'ID платежа из соответствующей таблицы']);
            $table->addColumn('payment_table', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'comment' => 'Таблица, из которой пришла оплата (напр. s_tv_medical_payments)', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('payment_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('payment_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('is_refunded', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('refund_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('refund_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['order_id', 'service_key'], ['name' => 'idx_order_service']);
            $table->addIndex(['process_log_id'], ['name' => 'idx_process_log_id']);
            $table->create();
        }

        if (!$this->hasTable('s_service_recovery_rules')) {
            $table = $this->table('s_service_recovery_rules', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Правила автоматического возобновления дополнительных услуг']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('days_since_disable', 'integer', ['null' => false]);
            $table->addColumn('disabled_from', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('disabled_to', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('manager_ids', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('manager_role_ids', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('service_keys', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('is_active', 'boolean', ['null' => false, 'default' => '1']);
            $table->addColumn('min_loan_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('max_loan_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('repayment_stage', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('priority', 'integer', ['null' => false, 'default' => '100']);
            $table->addColumn('auto_run_enabled', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('cron_schedule', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('last_auto_run_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('created_by', 'integer', ['null' => false]);
            $table->addColumn('updated_by', 'integer', ['null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_settings')) {
            $table = $this->table('s_settings', ['id' => false, 'primary_key' => ['setting_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('setting_id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('value', 'text', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_short_link')) {
            $table = $this->table('s_short_link', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('link', 'string', ['limit' => 100, 'null' => false, 'collation' => 'utf8mb3_unicode_ci']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('phone', 'string', ['limit' => 100, 'null' => false, 'collation' => 'utf8mb3_unicode_ci']);
            $table->addColumn('zaim_number', 'string', ['limit' => 100, 'null' => false, 'collation' => 'utf8mb3_unicode_ci']);
            $table->addColumn('active', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('type', 'enum', ['values' => ['sms-1k', 'sms-prolongation', 'sms-payment', 'sms-payment-sbp', 'lk'], 'null' => true, 'default' => null, 'collation' => 'utf8mb3_unicode_ci']);
            $table->addColumn('order_id', 'biginteger', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 's_short_link_s_users_null_fk']);
            $table->addIndex(['order_id'], ['name' => 's_short_link_s_orders_null_fk']);
            $table->addForeignKey(['order_id'], 's_orders', ['id'], ['constraint' => 's_short_link_s_orders_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_short_link_s_users_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_short_url_visits')) {
            $table = $this->table('s_short_url_visits', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('short_url_id', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('ip_address', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('operating_system', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('operating_system_version', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('browser', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('browser_version', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('referer_url', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('device_type', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('visited_at', 'timestamp', ['null' => false]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addIndex(['short_url_id'], ['name' => 's_short_url_visits_short_url_id_foreign']);
            $table->create();
        }

        if (!$this->hasTable('s_short_urls')) {
            $table = $this->table('s_short_urls', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('destination_url', 'text', ['null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('url_key', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('default_short_url', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('single_use', 'boolean', ['null' => false]);
            $table->addColumn('forward_query_params', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('track_visits', 'boolean', ['null' => false]);
            $table->addColumn('redirect_status_code', 'integer', ['null' => false, 'default' => '301']);
            $table->addColumn('track_ip_address', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('track_operating_system', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('track_operating_system_version', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('track_browser', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('track_browser_version', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('track_referer_url', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('track_device_type', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('activated_at', 'timestamp', ['null' => true, 'default' => '2022-01-13']);
            $table->addColumn('deactivated_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null]);
            $table->addIndex(['url_key'], ['name' => 's_short_urls_url_key_unique', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_sms_auth_validate')) {
            $table = $this->table('s_sms_auth_validate', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Валидация смс при входе']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 32, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('type', 'string', ['limit' => 32, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('repeats', 'integer', ['null' => false, 'default' => '0', 'comment' => 'Кол-во попыток']);
            $table->addColumn('last_validate_at', 'datetime', ['null' => false, 'comment' => 'Дата последней ошибки']);
            $table->addIndex(['phone'], ['name' => 's_sms_auth_validate_phone_index']);
            $table->addIndex(['type'], ['name' => 's_sms_auth_validate_type_index']);
            $table->create();
        }

        if (!$this->hasTable('s_sms_cc')) {
            $table = $this->table('s_sms_cc', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Исходящие смс для КЦ']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('mango_call_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'id звонка в манго']);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('phone', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('sms_id', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('sms_template_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('sms_type', 'string', ['limit' => 12, 'null' => true, 'default' => null, 'comment' => 'Тип смс, служит для связки входящих сообщений']);
            $table->addIndex(['sms_id'], ['name' => 's_sms_cc_sms_id_index']);
            $table->addIndex(['user_id'], ['name' => 's_sms_cc_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_sms_cc_answer')) {
            $table = $this->table('s_sms_cc_answer', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Ответные смс от пользователей']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('sms_id', 'string', ['limit' => 64, 'null' => false]);
            $table->addColumn('sms_from', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('phone', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('message', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['sms_id'], ['name' => 's_sms_cc_answer_sms_id_uindex', 'unique' => true]);
            $table->addIndex(['sms_from'], ['name' => 's_sms_cc_answer_sms_from_index']);
            $table->create();
        }

        if (!$this->hasTable('s_sms_messages')) {
            $table = $this->table('s_sms_messages', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('phone', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('message', 'text', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('send_status', 'string', ['limit' => 255, 'null' => true, 'default' => '']);
            $table->addColumn('delivery_status', 'string', ['limit' => 255, 'null' => true, 'default' => '']);
            $table->addColumn('send_id', 'string', ['limit' => 63, 'null' => true, 'default' => '']);
            $table->addColumn('type', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('validated', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '1', 'comment' => 'Признак, стоит ли валидировать в лимитах']);
            $table->addColumn('is_last_sms', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('code', 'string', ['limit' => 6, 'null' => true, 'default' => null, 'comment' => 'АСП код']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['send_id'], ['name' => 'send_id']);
            $table->addIndex(['phone'], ['name' => 'phone']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->addIndex(['type'], ['name' => 's_sms_messages_type_index']);
            $table->addIndex(['send_status'], ['name' => 's_sms_messages_send_status_index']);
            $table->addIndex(['validated'], ['name' => 's_sms_messages_validated_index']);
            $table->addIndex(['order_id', 'type'], ['name' => 'order_id_2']);
            $table->addIndex(['type', 'is_last_sms'], ['name' => 's_sms_messages_type_is_last_sms_index']);
            $table->create();
        }

        if (!$this->hasTable('s_sms_templates')) {
            $table = $this->table('s_sms_templates', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('template', 'text', ['null' => false]);
            $table->addColumn('name', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('type', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('check_limit', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('order', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('delay_days', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('status', 'boolean', ['null' => true, 'default' => null]);
            $table->addIndex(['order'], ['name' => 's_sms_templates_order_index']);
            $table->addIndex(['type'], ['name' => 's_sms_templates_type_index']);
            $table->create();
        }

        if (!$this->hasTable('s_sms_validate')) {
            $table = $this->table('s_sms_validate', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('ip', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('sms_time', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date_edit', 'datetime', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('total', 'integer', ['null' => true, 'default' => '1']);
            $table->addColumn('total_unique', 'integer', ['null' => true, 'default' => '1']);
            $table->addIndex(['phone'], ['name' => 's_sms_validate_phone_pk', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_spr_versions')) {
            $table = $this->table('s_spr_versions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('description', 'text', ['null' => false, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('manager_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('created', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_star_oracle')) {
            $table = $this->table('s_star_oracle', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Список купленных Звездных Оракул']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('amount', 'integer', ['null' => true, 'default' => null, 'comment' => 'Стоимость ЗО0']);
            $table->addColumn('amount_total_returned', 'integer', ['null' => false, 'default' => '0', 'comment' => 'total returned amount']);
            $table->addColumn('payment_method', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('transaction_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('status', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('organization_id', 'integer', ['null' => false, 'default' => '1']);
            $table->addColumn('return_sent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('return_transaction_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('return_status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('return_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('return_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('return_by_user', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('return_by_manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_edit', 'datetime', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('dop1c_sent', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'отправлена ли доп услуга в доповую 1с']);
            $table->addColumn('dop1c_sent_return', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'отправлен ли возврат доп услуги в доповую 1с']);
            $table->addColumn('action_type', 'enum', ['values' => ['issuance', 'prolongation', 'partial_payment', 'full_payment', 'recurring_partial_payment', 'recurring_full_payment'], 'null' => true, 'default' => null]);
            $table->addIndex(['dop1c_sent'], ['name' => 'dop1c_sent']);
            $table->addIndex(['dop1c_sent_return'], ['name' => 'dop1c_sent_return']);
            $table->addIndex(['return_status'], ['name' => 'return_status']);
            $table->addIndex(['return_date'], ['name' => 'return_date']);
            $table->addIndex(['return_sent'], ['name' => 'return_sent']);
            $table->addIndex(['return_transaction_id'], ['name' => 'return_transaction_id']);
            $table->addIndex(['order_id'], ['name' => 's_star_oracle_order_id_index']);
            $table->addIndex(['payment_method'], ['name' => 's_star_oracle_payment_method_index']);
            $table->addIndex(['status'], ['name' => 's_star_oracle_status_index']);
            $table->addIndex(['transaction_id'], ['name' => 's_star_oracle_transaction_id_index']);
            $table->addIndex(['user_id'], ['name' => 's_star_oracle_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_star_oracle_conditions')) {
            $table = $this->table('s_star_oracle_conditions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('is_new', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('from_amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('to_amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('price', 'integer', ['null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('s_stop_list_web_id')) {
            $table = $this->table('s_stop_list_web_id', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Стоп лист для трафика']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('utm_source', 'string', ['limit' => 64, 'null' => false]);
            $table->addColumn('web_master_id', 'string', ['limit' => 64, 'null' => false]);
            $table->addIndex(['web_master_id', 'utm_source'], ['name' => 's_stop_list_web_id_pk', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_sync_organization')) {
            $table = $this->table('s_sync_organization', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Организации для синхронизации']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 128, 'null' => true, 'default' => null, 'comment' => 'Имя организации']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_tbank_id')) {
            $table = $this->table('s_tbank_id', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1', 'comment' => 'Логи TBank для регистрации клиентов']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('sub', 'string', ['limit' => 256, 'null' => true, 'default' => null, 'comment' => 'идентификатор авторизированного пользователя TBankId']);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['sub'], ['name' => 's_t_bank_id_sub_index']);
            $table->addIndex(['user_id'], ['name' => 's_t_bank_id_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_theme_view')) {
            $table = $this->table('s_theme_view', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('session_id', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addIndex(['session_id'], ['name' => 's_theme_view_pk', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_ticket_companies')) {
            $table = $this->table('s_ticket_companies', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('is_active', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '1']);
            $table->create();
        }

        if (!$this->hasTable('s_tickets_history')) {
            $table = $this->table('s_tickets_history', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('ticket_id', 'integer', ['null' => false]);
            $table->addColumn('field_name', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('old_value', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('new_value', 'text', ['null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('changed_by', 'integer', ['null' => false]);
            $table->addColumn('changed_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('comment', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['id'], ['name' => 'id', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_time_zones')) {
            $table = $this->table('s_time_zones', ['id' => false, 'primary_key' => ['time_zone_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('time_zone_id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name_zone', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addColumn('time', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('timezone', 'string', ['limit' => 100, 'null' => false]);
            $table->addIndex(['name_zone'], ['name' => 's_time_zones_name_zone_index']);
            $table->create();
        }

        if (!$this->hasTable('s_tinkoff_cards')) {
            $table = $this->table('s_tinkoff_cards', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Карты Тинька']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('card_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('pan', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('status', 'string', ['limit' => 16, 'null' => true, 'default' => null]);
            $table->addColumn('rebill_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('card_type', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('exp_date', 'string', ['limit' => 8, 'null' => true, 'default' => null]);
            $table->addColumn('auto_debiting', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['card_id'], ['name' => 's_tinkoff_cards_card_id_index']);
            $table->addIndex(['user_id'], ['name' => 's_tinkoff_cards_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_transactions')) {
            $table = $this->table('s_transactions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('individual_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('uid', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('order_id', 'string', ['limit' => 24, 'null' => true, 'default' => null, 'comment' => 'Id от Тинькова']);
            $table->addColumn('crm_order_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'Id заявки из CRM']);
            $table->addColumn('card_id', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('amount', 'integer', ['null' => false]);
            $table->addColumn('payment_id', 'string', ['limit' => 200, 'null' => false]);
            $table->addColumn('terminal_type', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('payment_link', 'string', ['limit' => 200, 'null' => true, 'default' => null]);
            $table->addColumn('created', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('sended', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('send_result', 'text', ['null' => true]);
            $table->addColumn('status', 'string', ['limit' => 100, 'null' => true, 'default' => '']);
            $table->addColumn('error_code', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('error_message', 'text', ['null' => true]);
            $table->addColumn('prolongation', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('code_sms', 'string', ['limit' => 10, 'null' => true, 'default' => '']);
            $table->addColumn('insurer', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('insure_amount', 'decimal', ['precision' => 8, 'scale' => 2, 'null' => true, 'default' => '0.00']);
            $table->addColumn('loan_id', 'string', ['limit' => 20, 'null' => true, 'default' => null, 'comment' => 'айди займа/заявки из 1с']);
            $table->addColumn('contract_number', 'string', ['limit' => 20, 'null' => true, 'default' => null, 'comment' => 'zaim_number - номер договора']);
            $table->addColumn('payment_type', 'enum', ['values' => ['credit_rating', 'credit_rating_for_nk', 'credit_rating_after_rejection', 'debt'], 'null' => true, 'default' => 'debt']);
            $table->addColumn('loan_uid', 'string', ['limit' => 64, 'null' => true, 'default' => null, 'comment' => 'UID займа из 1С, нужен для связки транзаций,
            когда пользователь залогинен через паспорт РФ']);
            $table->addColumn('referer', 'string', ['limit' => 256, 'null' => true, 'default' => null]);
            $table->addColumn('cron_import_completed', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0', 'comment' => 'Обработан ли импортом из 1С, по уплаченным процентам']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['payment_id'], ['name' => 'payment_id']);
            $table->addIndex(['individual_id'], ['name' => 'individual_id']);
            $table->addIndex(['referer'], ['name' => 's_transactions_referer_index']);
            $table->addIndex(['status'], ['name' => 's_transactions_status_index']);
            $table->addIndex(['cron_import_completed'], ['name' => 's_transactions_cron_import_completed_index']);
            $table->addIndex(['created'], ['name' => 's_transactions_created_index']);
            $table->addIndex(['payment_type'], ['name' => 's_transactions_payment_type_index']);
            $table->addIndex(['crm_order_id'], ['name' => 's_transactions_crm_order_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_tv_medical')) {
            $table = $this->table('s_tv_medical', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Список тарифов по телемедицине']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 128, 'null' => false]);
            $table->addColumn('price', 'integer', ['null' => false]);
            $table->addColumn('days', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('api_doc_id', 'string', ['limit' => 64, 'null' => true, 'default' => null, 'comment' => 'id документа из API']);
            $table->addColumn('description', 'text', ['null' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_tv_medical_payments')) {
            $table = $this->table('s_tv_medical_payments', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Информация по оплатам телемедецины']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('tv_medical_id', 'integer', ['null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('payment_method', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('payment_id', 'integer', ['null' => false, 'comment' => 'id транзакции в Б2П или Тинькоф']);
            $table->addColumn('amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('amount_total_returned', 'integer', ['null' => false, 'default' => '0', 'comment' => 'total returned amount']);
            $table->addColumn('status', 'string', ['limit' => 16, 'null' => false]);
            $table->addColumn('organization_id', 'integer', ['null' => false, 'default' => '1']);
            $table->addColumn('return_sent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('return_transaction_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('return_status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('return_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('return_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('return_by_user', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('return_by_manager_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_modified', 'datetime', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('sent_to_api', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'comment' => 'Отправлен в API медецины']);
            $table->addColumn('return', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'comment' => 'Был ли возврат средств']);
            $table->addColumn('dop1c_sent', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Отправлена ли доп услуга в доповую 1с']);
            $table->addColumn('dop1c_sent_return', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'Отправлен ли возврат доп услуги в доповую 1с']);
            $table->addIndex(['amount'], ['name' => 's_tv_medical_payments_amount_index']);
            $table->addIndex(['date_added'], ['name' => 's_tv_medical_payments_date_added_index']);
            $table->addIndex(['order_id'], ['name' => 's_tv_medical_payments_order_id_index']);
            $table->addIndex(['payment_id'], ['name' => 's_tv_medical_payments_payment_id_index']);
            $table->addIndex(['payment_method'], ['name' => 's_tv_medical_payments_payment_method_index']);
            $table->addIndex(['status'], ['name' => 's_tv_medical_payments_status_index']);
            $table->addIndex(['user_id'], ['name' => 's_tv_medical_payments_user_id_index']);
            $table->addIndex(['tv_medical_id'], ['name' => 's_tv_medical_payments_s_tv_medical_null_fk']);
            $table->addIndex(['return'], ['name' => 's_tv_medical_payments_return_index']);
            $table->addIndex(['sent_to_api'], ['name' => 's_tv_medical_payments_sent_to_api_index']);
            $table->addIndex(['return_status'], ['name' => 'return_status']);
            $table->addIndex(['return_date'], ['name' => 'return_date']);
            $table->addIndex(['return_transaction_id'], ['name' => 'return_transaction_id']);
            $table->addIndex(['return_sent'], ['name' => 'return_sent']);
            $table->addIndex(['dop1c_sent'], ['name' => 'dop1c_sent']);
            $table->addIndex(['dop1c_sent_return'], ['name' => 'dop1c_sent_return']);
            $table->addForeignKey(['tv_medical_id'], 's_tv_medical', ['id'], ['constraint' => 's_tv_medical_payments_s_tv_medical_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_unibell_sms')) {
            $table = $this->table('s_unibell_sms', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('request_id', 'string', ['limit' => 128, 'null' => true, 'default' => null]);
            $table->addColumn('phone', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('code', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('status', 'string', ['limit' => 32, 'null' => true, 'default' => null]);
            $table->addColumn('error_code', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_edit', 'datetime', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['phone'], ['name' => 's_unibell_sms_phone_index']);
            $table->addIndex(['status'], ['name' => 's_unibell_sms_status_index']);
            $table->addIndex(['request_id'], ['name' => 's_unibell_sms_request_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_uploaded_documents')) {
            $table = $this->table('s_uploaded_documents', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'biginteger', ['null' => false]);
            $table->addIndex(['order_id'], ['name' => 's_uploaded_documents_s_orders_null_fk']);
            $table->addIndex(['user_id'], ['name' => 's_uploaded_documents_s_users_null_fk']);
            $table->addForeignKey(['order_id'], 's_orders', ['id'], ['constraint' => 's_uploaded_documents_s_orders_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_uploaded_documents_s_users_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_usedesk_ticket_analysis')) {
            $table = $this->table('s_usedesk_ticket_analysis', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('created', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('ticket_id', 'biginteger', ['null' => false, 'comment' => 'Идентификатор тикета в usedesk', 'signed' => false]);
            $table->addColumn('analysis', 'json', ['null' => true, 'default' => null]);
            $table->addIndex(['created'], ['name' => 's_usedesk_ticket_analysis_created_index']);
            $table->addIndex(['ticket_id'], ['name' => 's_usedesk_ticket_analysis_ticket_id_index']);
            $table->addIndex(['user_id'], ['name' => 's_usedesk_ticket_analysis_user_id_index']);
            $table->create();
        }

        if (!$this->hasTable('s_user_agreement')) {
            $table = $this->table('s_user_agreement', ['id' => false, 'primary_key' => ['user_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('lastname', 'string', ['limit' => 120, 'null' => true, 'default' => null]);
            $table->addColumn('firstname', 'string', ['limit' => 120, 'null' => true, 'default' => null]);
            $table->addColumn('patronymic', 'string', ['limit' => 120, 'null' => true, 'default' => null]);
            $table->addColumn('gender', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('birth', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('birth_place', 'text', ['null' => true]);
            $table->addColumn('phone_mobile', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('email', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('choose_insure', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => null]);
            $table->addColumn('passport_serial', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('passport_date', 'string', ['limit' => 15, 'null' => true, 'default' => null]);
            $table->addColumn('subdivision_code', 'string', ['limit' => 7, 'null' => true, 'default' => null]);
            $table->addColumn('passport_issued', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addIndex(['lastname'], ['name' => 'lastname']);
            $table->addIndex(['firstname'], ['name' => 'firstname']);
            $table->addIndex(['patronymic'], ['name' => 'patronymic']);
            $table->addIndex(['birth'], ['name' => 'birth']);
            $table->addIndex(['email'], ['name' => 'email']);
            $table->create();
        }

        if (!$this->hasTable('s_user_balance')) {
            $table = $this->table('s_user_balance', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('zaim_number', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('zaim_summ', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('percent', 'string', ['limit' => 10, 'null' => false]);
            $table->addColumn('ostatok_od', 'string', ['limit' => 20, 'null' => false, 'comment' => '// Остаток основного долга']);
            $table->addColumn('ostatok_percents', 'string', ['limit' => 20, 'null' => false, 'comment' => '// Остаток процентов']);
            $table->addColumn('ostatok_peni', 'string', ['limit' => 20, 'null' => false, 'comment' => '// Остаток пени']);
            $table->addColumn('client', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('zaim_date', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('zayavka', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('last_update', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('restructurisation', 'text', ['null' => true]);
            $table->addColumn('sale_info', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('payment_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('prolongation_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => '0.00']);
            $table->addColumn('prolongation_summ_percents', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('prolongation_summ_insurance', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('prolongation_summ_sms', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('prolongation_summ_cost', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('prolongation_count', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('allready_added', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('last_prolongation', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('cc_status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('lpt_lead', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('expired_days', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true, 'default' => '0']);
            $table->addColumn('buyer', 'string', ['limit' => 255, 'null' => true, 'default' => '']);
            $table->addColumn('buyer_phone', 'string', ['limit' => 15, 'null' => true, 'default' => null]);
            $table->addColumn('pr_status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('pr_manager', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('is_cession_shown', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('penalty', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('overdue_sms_count', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('overdue_sms_day', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('sum_with_grace', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('sum_od_with_grace', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('sum_percent_with_grace', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('inn', 'string', ['limit' => 12, 'null' => false, 'default' => '']);
            $table->addColumn('current_inn', 'string', ['limit' => 12, 'null' => false, 'default' => '']);
            $table->addColumn('loan_type', 'enum', ['values' => ['il', 'pdl', ''], 'null' => false, 'default' => 'PDL']);
            $table->addColumn('overdue_debt_od_IL', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('overdue_debt_percent_IL', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('next_payment_od', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('next_payment_percent', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('discount_amount', 'integer', ['null' => true, 'default' => '0', 'comment' => 'Сумма скидки ']);
            $table->addColumn('discount_date', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата действия скидки']);
            $table->addIndex(['zaim_number'], ['name' => 'zaim_number']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['last_update'], ['name' => 'last_update']);
            $table->addIndex(['payment_date'], ['name' => 'payment_date']);
            $table->addIndex(['zaim_date'], ['name' => 'zaim_date']);
            $table->addIndex(['zayavka'], ['name' => 'zayavka']);
            $table->addIndex(['pr_status'], ['name' => 'pr_status']);
            $table->create();
        }

        if (!$this->hasTable('s_user_balance_new')) {
            $table = $this->table('s_user_balance_new', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('zaim_number', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('percent', 'string', ['limit' => 10, 'null' => false]);
            $table->addColumn('ostatok_od', 'string', ['limit' => 20, 'null' => false, 'comment' => '// Остаток основного долга']);
            $table->addColumn('ostatok_percents', 'string', ['limit' => 20, 'null' => false, 'comment' => '// Остаток процентов']);
            $table->addColumn('ostatok_peni', 'string', ['limit' => 20, 'null' => false, 'comment' => '// Остаток пени']);
            $table->addColumn('client', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('zaim_date', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('zayavka', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('last_update', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_user_banners')) {
            $table = $this->table('s_user_banners', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Баннера для других МФК, после синхронизации']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('organization_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'id организации для синхронизации']);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'id заявки, используется при синхронизации']);
            $table->addColumn('url', 'text', ['null' => true, 'comment' => 'Хэш ссылки для баннера']);
            $table->addColumn('status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '1']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_end', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 's_user_banners_s_users_id_fk']);
            $table->addIndex(['status'], ['name' => 's_user_banners_status_index']);
            $table->addIndex(['organization_id'], ['name' => 's_user_banners_s_sync_organization_id_fk']);
            $table->addIndex(['order_id'], ['name' => 's_user_banners_order_id_index']);
            $table->addIndex(['date_end'], ['name' => 's_user_banners_date_end_index']);
            $table->addForeignKey(['organization_id'], 's_sync_organization', ['id'], ['constraint' => 's_user_banners_s_sync_organization_id_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_user_banners_s_users_id_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_user_basic_cards')) {
            $table = $this->table('s_user_basic_cards', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('card_id', 'integer', ['null' => false]);
            $table->addIndex(['user_id'], ['name' => 's_user_basic_cards_s_users_null_fk']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_user_basic_cards_s_users_null_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_user_data')) {
            $table = $this->table('s_user_data', ['id' => false, 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'Дополнительные данные пользователей']);
            $table->addColumn('user_id', 'integer', ['null' => false, 'signed' => false]);
            $table->addColumn('key', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('value', 'string', ['limit' => 1024, 'null' => true, 'default' => null, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('updated', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['user_id', 'key'], ['name' => 'unique_user_key', 'unique' => true]);
            $table->addIndex(['user_id'], ['name' => 'user_id_key']);
            $table->create();
        }

        if (!$this->hasTable('s_user_dnc')) {
            $table = $this->table('s_user_dnc', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('phones', 'json', ['null' => false]);
            $table->addColumn('days', 'integer', ['null' => false]);
            $table->addColumn('date_start', 'datetime', ['null' => false]);
            $table->addColumn('date_end', 'datetime', ['null' => false]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('dnc_contact_ids', 'json', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_user_emails')) {
            $table = $this->table('s_user_emails', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('email', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('source', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('is_active', 'boolean', ['null' => false, 'default' => '1']);
            $table->addIndex(['user_id', 'email'], ['name' => 'user_id_email', 'unique' => true]);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 'user_emails_user_id_users_id', 'delete' => 'CASCADE', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('s_user_feedbacks')) {
            $table = $this->table('s_user_feedbacks', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('order_id', 'biginteger', ['null' => false]);
            $table->addColumn('data', 'json', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->create();
        }

        if (!$this->hasTable('s_user_offers')) {
            $table = $this->table('s_user_offers', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('close_task_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addColumn('type', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'encoding' => 'utf8mb3']);
            $table->addColumn('value', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'encoding' => 'utf8mb3']);
            $table->addColumn('end_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('used', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['close_task_id'], ['name' => 'close_task_id']);
            $table->create();
        }

        if (!$this->hasTable('s_user_payments_1c')) {
            $table = $this->table('s_user_payments_1c', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'Платежи из 1С']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('uid', 'string', ['limit' => 64, 'null' => false, 'comment' => 'UID пользователя из 1С']);
            $table->addColumn('percent_amount', 'integer', ['null' => true, 'default' => '0', 'comment' => 'Уплаченные проценты']);
            $table->addColumn('full_amount', 'integer', ['null' => true, 'default' => '0', 'comment' => 'Долг + проценты, без страховки']);
            $table->addColumn('insurer_amount', 'integer', ['null' => true, 'default' => '0', 'comment' => 'Сумма страховки']);
            $table->addColumn('id_1c', 'string', ['limit' => 32, 'null' => false, 'comment' => 'Номер заявки 1C']);
            $table->addColumn('loan_id', 'string', ['limit' => 16, 'null' => true, 'default' => null, 'comment' => 'Номер займа']);
            $table->addColumn('payment_date', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата оплаты']);
            $table->addColumn('date_added', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['id_1c'], ['name' => 's_user_payments_1c_id_1c_index']);
            $table->addIndex(['payment_date'], ['name' => 's_user_payments_1c_payment_date_index']);
            $table->addIndex(['uid'], ['name' => 's_user_payments_1c_uid_index']);
            $table->addIndex(['percent_amount'], ['name' => 's_user_payments_1c_percent_amount_index']);
            $table->addIndex(['loan_id', 'uid', 'payment_date'], ['name' => 's_user_payments_1c_loan_id_uid_payment_date_index']);
            $table->create();
        }

        if (!$this->hasTable('s_user_phones')) {
            $table = $this->table('s_user_phones', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('phone', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('source', 'string', ['limit' => 15, 'null' => false, 'default' => '']);
            $table->addColumn('modified_date', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('added_date', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('is_active', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '1']);
            $table->addIndex(['user_id', 'phone'], ['name' => 'user_id_phone', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('s_user_ticket_comments')) {
            $table = $this->table('s_user_ticket_comments', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('usedesk_id', 'biginteger', ['null' => true, 'default' => null]);
            $table->addColumn('ticket_id', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('sender_type', 'enum', ['values' => ['user', 'operator'], 'null' => false, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('message', 'text', ['null' => false, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('attachments', 'json', ['null' => true, 'default' => null]);
            $table->addColumn('is_read', 'boolean', ['null' => false, 'default' => '0']);
            $table->addIndex(['ticket_id'], ['name' => 's_user_ticket_comments_s_user_tickets_id_fk']);
            $table->addForeignKey(['ticket_id'], 's_user_tickets', ['id'], ['constraint' => 's_user_ticket_comments_s_user_tickets_id_fk']);
            $table->create();
        }

        if (!$this->hasTable('s_user_ticket_contracts')) {
            $table = $this->table('s_user_ticket_contracts', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('ticket_id', 'biginteger', ['null' => false, 'signed' => false]);
            $table->addColumn('contract_id', 'integer', ['null' => false]);
            $table->addIndex(['ticket_id'], ['name' => 'ticket_id']);
            $table->addForeignKey(['ticket_id'], 's_user_tickets', ['id'], ['constraint' => 's_user_ticket_contracts_ibfk_1']);
            $table->create();
        }

        if (!$this->hasTable('s_user_tickets')) {
            $table = $this->table('s_user_tickets', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('usedesk_id', 'biginteger', ['null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('subject', 'string', ['limit' => 255, 'null' => false, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('status', 'string', ['limit' => 50, 'null' => false, 'default' => 'Новое', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->create();
        }

        if (!$this->hasTable('s_user_usedesk')) {
            $table = $this->table('s_user_usedesk', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('usedesk_id', 'integer', ['null' => false]);
            $table->addIndex(['user_id'], ['name' => 's_user_usedesk_s_users_id_fk']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 's_user_usedesk_s_users_id_fk']);
            $table->create();
        }

        if (!$this->hasTable('s_user_utm')) {
            $table = $this->table('s_user_utm', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('utm_medium', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('utm_content', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('utm_campaign', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('utm_term', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('utm_source', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('create_date', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('s_user_vk')) {
            $table = $this->table('s_user_vk', ['id' => false, 'primary_key' => ['user_id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('vk_user_id', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('access_token_id', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('access_token', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('phone', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('email', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addIndex(['vk_user_id'], ['name' => 'vk_user_id']);
            $table->addIndex(['phone'], ['name' => 'phone']);
            $table->create();
        }

        if (!$this->hasTable('s_users')) {
            $table = $this->table('s_users', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('maratorium_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('maratorium_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('first_loan', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('first_loan_amount', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('first_loan_period', 'integer', ['null' => true, 'default' => '0']);
            $table->addColumn('service_recurent', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('service_sms', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('service_insurance', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('service_reason', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('service_doctor', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('email', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('password', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'default' => '']);
            $table->addColumn('group_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('enabled', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('last_ip', 'string', ['limit' => 15, 'null' => true, 'default' => null]);
            $table->addColumn('reg_ip', 'string', ['limit' => 15, 'null' => true, 'default' => null]);
            $table->addColumn('created', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('personal_data_added', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('personal_data_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('card_req_data_added', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('card_req_data_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('address_data_added', 'boolean', ['null' => false, 'default' => '1']);
            $table->addColumn('address_data_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('accept_data_added', 'boolean', ['null' => false, 'default' => '1']);
            $table->addColumn('accept_data_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('additional_data_added', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('additional_data_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('files_added', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('files_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('card_added', 'boolean', ['null' => true, 'default' => '1']);
            $table->addColumn('card_added_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('stage_sms_sended', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('lastname', 'string', ['limit' => 120, 'null' => false]);
            $table->addColumn('firstname', 'string', ['limit' => 120, 'null' => false]);
            $table->addColumn('patronymic', 'string', ['limit' => 120, 'null' => false]);
            $table->addColumn('gender', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('birth', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('birth_place', 'text', ['null' => true]);
            $table->addColumn('phone_mobile', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('landline_phone', 'string', ['limit' => 30, 'null' => true, 'default' => null]);
            $table->addColumn('marital', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('passport_serial', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('subdivision_code', 'string', ['limit' => 7, 'null' => false]);
            $table->addColumn('passport_date', 'string', ['limit' => 15, 'null' => false]);
            $table->addColumn('passport_issued', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('Snils', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('inn', 'string', ['limit' => 20, 'null' => false]);
            $table->addColumn('registration_address_id', 'integer', ['null' => false, 'default' => '0', 'comment' => 'ID адреса регистрации', 'signed' => false]);
            $table->addColumn('factual_address_id', 'integer', ['null' => false, 'default' => '0', 'comment' => 'ID адреса проживания', 'signed' => false]);
            $table->addColumn('bplace', 'text', ['null' => false]);
            $table->addColumn('Regindex', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Regregion', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Regdistrict', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Regcity', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Reglocality', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Regstreet', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('Regbuilding', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Reghousing', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Regroom', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Regregion_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Regcity_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Regstreet_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Faktindex', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Faktregion', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Faktdistrict', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Faktcity', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Faktlocality', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Faktstreet', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('Faktbuilding', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Fakthousing', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Faktroom', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('Faktregion_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Faktcity_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Faktstreet_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('contact_person_name', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('contact_person_phone', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('contact_person_relation', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('contact_person2_name', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('contact_person2_phone', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('contact_person2_relation', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('contact_person3_name', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('contact_person3_phone', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('contact_person3_relation', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('employment', 'string', ['limit' => 100, 'null' => true, 'default' => '']);
            $table->addColumn('profession', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addColumn('workplace', 'string', ['limit' => 100, 'null' => true, 'default' => '']);
            $table->addColumn('experience', 'string', ['limit' => 100, 'null' => true, 'default' => '']);
            $table->addColumn('work_address', 'text', ['null' => true]);
            $table->addColumn('work_scope', 'string', ['limit' => 100, 'null' => true, 'default' => '']);
            $table->addColumn('work_staff', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('work_phone', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('workdirector_name', 'string', ['limit' => 200, 'null' => true, 'default' => '']);
            $table->addColumn('Workindex', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Workregion', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Workcity', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Workstreet', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('Workhousing', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Workbuilding', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Workroom', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Workregion_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Workcity_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('Workstreet_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('income_base', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('income_additional', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('income_family', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('obligation', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('other_loan_month', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('other_loan_count', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('credit_history', 'string', ['limit' => 100, 'null' => true, 'default' => '']);
            $table->addColumn('other_max_amount', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('other_last_amount', 'string', ['limit' => 20, 'null' => true, 'default' => '']);
            $table->addColumn('bankrupt', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('education', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('marital_status', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('childs_count', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('have_car', 'string', ['limit' => 50, 'null' => true, 'default' => '']);
            $table->addColumn('has_estate', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('social_inst', 'string', ['limit' => 255, 'null' => true, 'default' => '']);
            $table->addColumn('social_fb', 'string', ['limit' => 255, 'null' => true, 'default' => '']);
            $table->addColumn('social_vk', 'string', ['limit' => 255, 'null' => true, 'default' => '']);
            $table->addColumn('social_ok', 'string', ['limit' => 255, 'null' => true, 'default' => '']);
            $table->addColumn('site_id', 'string', ['limit' => 20, 'null' => false, 'default' => 'boostra']);
            $table->addColumn('partner_id', 'string', ['limit' => 15, 'null' => true, 'default' => null]);
            $table->addColumn('partner_name', 'string', ['limit' => 15, 'null' => true, 'default' => null]);
            $table->addColumn('utm_source', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('utm_medium', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('utm_campaign', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('utm_content', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('utm_term', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('webmaster_id', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('click_hash', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('sms', 'string', ['limit' => 6, 'null' => false]);
            $table->addColumn('tinkoff_id', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addColumn('UID', 'string', ['limit' => 50, 'null' => false, 'comment' => 'Айди пользователя из 1с']);
            $table->addColumn('UID_status', 'string', ['limit' => 255, 'null' => false, 'comment' => 'Статус синхронизации 1с и пользователя движка']);
            $table->addColumn('rebillId', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('file_uploaded', 'boolean', ['null' => false, 'default' => '0', 'comment' => 'флаг доступности загрузки фото, 1 - загрузка недоступна, 0 - загрузка доступна']);
            $table->addColumn('need_remove', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('loan_history', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM, 'null' => true]);
            $table->addColumn('fake_order_error', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('choose_insure', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('cdoctor_level', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true, 'default' => '0']);
            $table->addColumn('cdoctor_pdf', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('identified_phone', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('scorista_history_loaded', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('use_b2p', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('missing_manager_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('missing_status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('missing_status_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('missing_real_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('sentData', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('files_checked', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('last_lk_visit_time', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('skip_credit_rating', 'string', ['limit' => 12, 'null' => true, 'default' => null]);
            $table->addColumn('date_skip_cr_visit', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('restructurisation', 'text', ['null' => true]);
            $table->addColumn('quantity_loans', 'text', ['null' => true]);
            $table->addColumn('blocked', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('timezone_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('call_status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true, 'default' => null, 'comment' => 'Статус по звонку', 'signed' => false]);
            $table->addColumn('continue_order', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true, 'default' => null, 'comment' => 'Клиент продолжит оформлять', 'signed' => false]);
            $table->addColumn('missing_manager_update_date', 'datetime', ['null' => true, 'default' => null, 'comment' => 'дата добавления/обновления ответственного']);
            $table->addColumn('stage_in_contact', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true, 'default' => null, 'comment' => 'Этап во время контакта', 'signed' => false]);
            $table->addColumn('cdoctor_last_graph_update_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('cdoctor_last_graph_display_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('agree_claim_value', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('last_mark', 'date', ['null' => true, 'default' => null]);
            $table->addColumn('generated_codes_count', 'integer', ['null' => true, 'default' => '0']);
            $table->addIndex(['email'], ['name' => 'email']);
            $table->addIndex(['UID'], ['name' => 'UID']);
            $table->addIndex(['service_insurance'], ['name' => 'service_insurance']);
            $table->addIndex(['personal_data_added'], ['name' => 'personal_data_added']);
            $table->addIndex(['files_added'], ['name' => 'files_added']);
            $table->addIndex(['card_added'], ['name' => 'card_added']);
            $table->addIndex(['lastname'], ['name' => 'lastname']);
            $table->addIndex(['firstname'], ['name' => 'firstname']);
            $table->addIndex(['patronymic'], ['name' => 'patronymic']);
            $table->addIndex(['Regregion'], ['name' => 'Regregion']);
            $table->addIndex(['phone_mobile'], ['name' => 'phone_mobile']);
            $table->addIndex(['birth'], ['name' => 'birth']);
            $table->addIndex(['missing_manager_id'], ['name' => 'missing_manager_id']);
            $table->addIndex(['missing_status'], ['name' => 'missing_status']);
            $table->addIndex(['files_checked'], ['name' => 'files_checked']);
            $table->addIndex(['missing_status_date'], ['name' => 'missing_status_date']);
            $table->addIndex(['additional_data_added'], ['name' => 'additional_data_added']);
            $table->addIndex(['timezone_id'], ['name' => 'timezone_id']);
            $table->addIndex(['passport_serial'], ['name' => 'passport_serial']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->create();
        }

        if (!$this->hasTable('s_variants')) {
            $table = $this->table('s_variants', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('product_id', 'integer', ['null' => false]);
            $table->addColumn('sku', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('price', 'decimal', ['precision' => 14, 'scale' => 2, 'null' => false, 'default' => '0.00']);
            $table->addColumn('compare_price', 'decimal', ['precision' => 14, 'scale' => 2, 'null' => true, 'default' => null]);
            $table->addColumn('stock', 'text', ['null' => true, 'default' => null]);
            $table->addColumn('position', 'integer', ['null' => false]);
            $table->addColumn('attachment', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('external_id', 'string', ['limit' => 36, 'null' => false]);
            $table->addIndex(['product_id'], ['name' => 'product_id']);
            $table->addIndex(['sku'], ['name' => 'sku']);
            $table->addIndex(['price'], ['name' => 'price']);
            $table->addIndex(['stock'], ['name' => 'stock']);
            $table->addIndex(['position'], ['name' => 'position']);
            $table->addIndex(['external_id'], ['name' => 'external_id']);
            $table->create();
        }

        if (!$this->hasTable('s_verification_cards')) {
            $table = $this->table('s_verification_cards', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('name', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('number', 'string', ['limit' => 21, 'null' => false]);
            $table->addColumn('expired_date', 'string', ['limit' => 10, 'null' => false, 'default' => '']);
            $table->addColumn('created', 'datetime', ['null' => false]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->create();
        }

        if (!$this->hasTable('s_verify_messangers')) {
            $table = $this->table('s_verify_messangers', ['id' => false, 'primary_key' => ['Id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('Id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('phone', 'string', ['limit' => 25, 'null' => false]);
            $table->addColumn('typeMessanger', 'string', ['limit' => 250, 'null' => false]);
            $table->addColumn('userIdInMessanger', 'string', ['limit' => 250, 'null' => false]);
            $table->addColumn('chatId', 'string', ['limit' => 250, 'null' => false]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('s_visitors')) {
            $table = $this->table('s_visitors', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('ip', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('user_agent', 'text', ['null' => true]);
            $table->addColumn('referer', 'text', ['null' => true]);
            $table->addColumn('link', 'text', ['null' => true]);
            $table->addColumn('created', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('last_active', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('utm_source', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addColumn('webmaster_id', 'string', ['limit' => 100, 'null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['utm_source'], ['name' => 'utm_source']);
            $table->addIndex(['webmaster_id'], ['name' => 'webmaster_id']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->addIndex(['ip'], ['name' => 'ip']);
            $table->create();
        }

        if (!$this->hasTable('s_vita_med_conditions')) {
            $table = $this->table('s_vita_med_conditions', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('is_new', 'boolean', ['null' => true, 'default' => null]);
            $table->addColumn('from_amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('to_amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('price', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('license_key_days', 'integer', ['null' => true, 'default' => null, 'comment' => 'Срок действия лицензионного ключа в днях']);
            $table->create();
        }

        if (!$this->hasTable('s_vk_message_settings')) {
            $table = $this->table('s_vk_message_settings', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('send_hour', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '12', 'signed' => false]);
            $table->addColumn('day_from', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '-1']);
            $table->addColumn('day_to', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '0']);
            $table->addColumn('age_from', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '18', 'signed' => false]);
            $table->addColumn('age_to', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '100', 'signed' => false]);
            $table->addColumn('gender', 'enum', ['values' => ['any', 'male', 'female'], 'null' => false, 'default' => 'any', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('scorista_ball_from', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => false, 'default' => '0', 'signed' => false]);
            $table->addColumn('scorista_ball_to', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => false, 'default' => '5000', 'signed' => false]);
            $table->addColumn('scorista_decision', 'enum', ['values' => ['any', 'approve', 'decline'], 'null' => false, 'default' => 'any', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('utm_source', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('organization_id', 'integer', ['null' => true, 'default' => null, 'signed' => false]);
            $table->addColumn('message', 'string', ['limit' => 600, 'null' => false, 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_0900_ai_ci']);
            $table->addColumn('enabled', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'default' => '1', 'signed' => false]);
            $table->addIndex(['send_hour'], ['name' => 'send_hour']);
            $table->addIndex(['enabled'], ['name' => 'enabled']);
            $table->create();
        }

        if (!$this->hasTable('s_vox_calls')) {
            $table = $this->table('s_vox_calls', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('cost', 'float', ['null' => true, 'default' => null, 'comment' => 'стоимость звонка']);
            $table->addColumn('call_result_code', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('datetime_start', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('duration', 'integer', ['null' => true, 'default' => null, 'comment' => 'длительность звонка']);
            $table->addColumn('vox_call_id', 'biginteger', ['null' => true, 'default' => null, 'comment' => 'id звонка в vox', 'signed' => false]);
            $table->addColumn('is_incoming', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true, 'default' => null, 'comment' => 'входящий или исходящий звонок', 'signed' => false]);
            $table->addColumn('phone_a', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'От кого звонок']);
            $table->addColumn('phone_b', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'кому звонок']);
            $table->addColumn('scenario_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('tags', 'json', ['null' => true, 'default' => null]);
            $table->addColumn('created', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null, 'comment' => 'id юзера в нашей системе', 'signed' => false]);
            $table->create();
        }

        if (!$this->hasTable('sms_log')) {
            $table = $this->table('sms_log', ['id' => false, 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false]);
            $table->addColumn('phone', 'string', ['limit' => 99, 'null' => false]);
            $table->addColumn('status', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('dates', 'datetime', ['null' => false]);
            $table->addColumn('sms_id', 'string', ['limit' => 99, 'null' => false]);
            $table->addIndex(['phone'], ['name' => 'phone']);
            $table->create();
        }

        if (!$this->hasTable('soglasie_bki_hash_code')) {
            $table = $this->table('soglasie_bki_hash_code', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('hash_code', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('patch', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->create();
        }

        if (!$this->hasTable('ssp_nbki_request_log')) {
            $table = $this->table('ssp_nbki_request_log', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('app_id', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
            $table->addColumn('order_id', 'biginteger', ['null' => false]);
            $table->addColumn('request_type', 'enum', ['values' => ['ssp_nbki', 'nbki'], 'null' => false]);
            $table->addColumn('data', 'text', ['null' => false]);
            $table->addColumn('s3_name', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('created_at', 'datetime', ['null' => false]);
            $table->addIndex(['order_id'], ['name' => 'ssp_nbki_request_log_s_orders_id_fk']);
            $table->addForeignKey(['order_id'], 's_orders', ['id'], ['constraint' => 'ssp_nbki_request_log_s_orders_id_fk', 'delete' => 'RESTRICT', 'update' => 'RESTRICT']);
            $table->create();
        }

        if (!$this->hasTable('t_sbp_accounts')) {
            $table = $this->table('t_sbp_accounts', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'biginteger', ['null' => false]);
            $table->addColumn('order_id', 'biginteger', ['null' => true, 'default' => '0']);
            $table->addColumn('request_key', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('token', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('status', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('error_code', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('message', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('deleted', 'boolean', ['null' => true, 'default' => '0']);
            $table->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['user_id'], ['name' => 't_sbp_accounts_user_id_index']);
            $table->addIndex(['order_id'], ['name' => 't_sbp_accounts_order_id_index']);
            $table->addIndex(['request_key'], ['name' => 't_sbp_accounts_request_key_index']);
            $table->addIndex(['status'], ['name' => 't_sbp_accounts_status_index']);
            $table->addIndex(['deleted'], ['name' => 't_sbp_accounts_deleted_index']);
            $table->create();
        }

        if (!$this->hasTable('temp_contracts')) {
            $table = $this->table('temp_contracts', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3', 'comment' => 'временная таблица для переноса']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('uid', 'string', ['limit' => 60, 'null' => false, 'default' => '']);
            $table->addColumn('lastname', 'string', ['limit' => 100, 'null' => false, 'default' => '']);
            $table->addColumn('firstname', 'string', ['limit' => 100, 'null' => false, 'default' => '']);
            $table->addColumn('patronymic', 'string', ['limit' => 100, 'null' => false, 'default' => '']);
            $table->addColumn('birth', 'string', ['limit' => 20, 'null' => false, 'default' => '']);
            $table->addColumn('passport', 'string', ['limit' => 12, 'null' => true, 'default' => null]);
            $table->addColumn('phone', 'biginteger', ['null' => true, 'default' => null]);
            $table->addColumn('loan_number', 'string', ['limit' => 20, 'null' => true, 'default' => null]);
            $table->addColumn('loan_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('loan_amount', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('loan_order', 'string', ['limit' => 10, 'null' => true, 'default' => null]);
            $table->addColumn('loan_sold', 'boolean', ['null' => false, 'default' => '0', 'comment' => '0 - не продан, 2 - правза, 1 - бикеш']);
            $table->addColumn('found', 'boolean', ['null' => false, 'default' => '0']);
            $table->addIndex(['uid'], ['name' => 'uid']);
            $table->addIndex(['phone'], ['name' => 'phone']);
            $table->addIndex(['passport'], ['name' => 'passport']);
            $table->addIndex(['found'], ['name' => 'found']);
            $table->create();
        }

        if (!$this->hasTable('tg_auth_hash')) {
            $table = $this->table('tg_auth_hash', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('hash', 'string', ['limit' => 32, 'null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => false]);
            $table->addIndex(['user_id'], ['name' => 'fk_user_id']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 'fk_user_id', 'delete' => 'SET_NULL', 'update' => 'CASCADE']);
            $table->create();
        }

        if (!$this->hasTable('tg_nicknames')) {
            $table = $this->table('tg_nicknames', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true]);
            $table->addColumn('phone_number', 'string', ['limit' => 64, 'null' => false]);
            $table->addColumn('nickname', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('chat_id', 'string', ['limit' => 255, 'null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => false]);
            $table->addColumn('source', 'string', ['limit' => 64, 'null' => true, 'default' => null]);
            $table->addIndex(['phone_number'], ['name' => 'phone_number']);
            $table->addIndex(['phone_number', 'nickname'], ['name' => 'phone_number_nickname']);
            $table->addIndex(['phone_number', 'chat_id'], ['name' => 'phone_number_chat_id']);
            $table->addIndex(['phone_number', 'nickname', 'chat_id'], ['name' => 'phone_number_nickname_chat_id']);
            $table->create();
        }

        if (!$this->hasTable('tinkoff_insures')) {
            $table = $this->table('tinkoff_insures', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('p2pcredit_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('transaction_id', 'integer', ['null' => false, 'default' => '0']);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('register_id', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('operation_id', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('body', 'text', ['null' => false]);
            $table->addColumn('response', 'text', ['null' => false]);
            $table->addColumn('status', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('complete_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false]);
            $table->addColumn('insurer', 'string', ['limit' => 25, 'null' => true, 'default' => null]);
            $table->addIndex(['order_id'], ['name' => 'contract_id']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['transaction_id'], ['name' => 'transaction_id']);
            $table->addIndex(['p2pcredit_id'], ['name' => 'p2pcredit_id']);
            $table->create();
        }

        if (!$this->hasTable('tinkoff_p2pcredits')) {
            $table = $this->table('tinkoff_p2pcredits', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('order_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('register_id', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('operation_id', 'string', ['limit' => 50, 'null' => false]);
            $table->addColumn('body', 'text', ['null' => false]);
            $table->addColumn('response', 'text', ['null' => false]);
            $table->addColumn('status', 'string', ['limit' => 50, 'null' => false, 'default' => '']);
            $table->addColumn('complete_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('sent', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('send_date', 'datetime', ['null' => true, 'default' => null]);
            $table->addIndex(['order_id'], ['name' => 'contract_id']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['sent'], ['name' => 'sent']);
            $table->addIndex(['status'], ['name' => 'status']);
            $table->create();
        }

        if (!$this->hasTable('user_order_gifts')) {
            $table = $this->table('user_order_gifts', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('user_id', 'integer', ['null' => false]);
            $table->addColumn('contract_number', 'string', ['limit' => 100, 'null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('status', 'boolean', ['null' => false, 'default' => '0']);
            $table->addColumn('order_id', 'biginteger', ['null' => false]);
            $table->addColumn('gift_activation_expired_at', 'datetime', ['null' => true, 'default' => '2025-02-28']);
            $table->addColumn('activated_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('gift_expired_at', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('promocode', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addColumn('provider', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->addIndex(['order_id'], ['name' => 'fk_order']);
            $table->addIndex(['user_id'], ['name' => 'fk_user']);
            $table->addIndex(['status'], ['name' => 'user_order_gifts_status_index']);
            $table->addIndex(['promocode'], ['name' => 'user_order_gifts_promocode_index']);
            $table->addIndex(['gift_expired_at'], ['name' => 'user_order_gifts_gift_expired_at_index']);
            $table->addForeignKey(['order_id'], 's_orders', ['id'], ['constraint' => 'fk_order']);
            $table->addForeignKey(['user_id'], 's_users', ['id'], ['constraint' => 'fk_user']);
            $table->create();
        }

        if (!$this->hasTable('users_addresses')) {
            $table = $this->table('users_addresses', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('address_index', 'string', ['limit' => 50, 'null' => true, 'default' => '', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('region', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('region_code', 'string', ['limit' => 2, 'null' => true, 'default' => null, 'comment' => 'Код региона из regions', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('district', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('city', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('locality', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('street', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('building', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('housing', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('room', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('region_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('city_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('street_shorttype', 'string', ['limit' => 50, 'null' => true, 'default' => '', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('fias_id', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_unicode_ci']);
            $table->addIndex(['region_code'], ['name' => 'users_addresses_regions_code_fk']);
            $table->create();
        }

        if (!$this->hasTable('verification_stats')) {
            $table = $this->table('verification_stats', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('order_id', 'integer', ['null' => false]);
            $table->addColumn('dates', 'datetime', ['null' => false]);
            $table->addColumn('manager_id', 'integer', ['null' => false]);
            $table->addColumn('start_status', 'integer', ['null' => false]);
            $table->addIndex(['order_id'], ['name' => 'order_id']);
            $table->addIndex(['manager_id'], ['name' => 'manager_id']);
            $table->addIndex(['start_status'], ['name' => 'start_status']);
            $table->addIndex(['dates'], ['name' => 'dates']);
            $table->create();
        }

        if (!$this->hasTable('verify_user_in_messengers')) {
            $table = $this->table('verify_user_in_messengers', ['id' => false, 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('uid', 'string', ['limit' => 40, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('sender_id', 'string', ['limit' => 40, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('messenger_type', 'string', ['limit' => 20, 'null' => true, 'default' => null, 'collation' => 'utf8mb4_general_ci']);
            $table->addColumn('client_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('verify_code', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('verify_status', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('verify_step', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('date_create', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('date_update', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addIndex(['id'], ['name' => 'id', 'unique' => true]);
            $table->addIndex(['sender_id'], ['name' => 'sender_id_ux', 'unique' => true]);
            $table->addIndex(['uid'], ['name' => 'uid_ux', 'unique' => true]);
            $table->create();
        }

        if (!$this->hasTable('vox_call_result')) {
            $table = $this->table('vox_call_result', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('client_phone', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('company_phone', 'string', ['limit' => 30, 'null' => false]);
            $table->addColumn('call_result', 'integer', ['null' => false]);
            $table->addColumn('created_at', 'datetime', ['null' => false]);
            $table->create();
        }

        if (!$this->hasTable('vox_tickets')) {
            $table = $this->table('vox_tickets', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'latin1']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false]);
            $table->addColumn('ticket_id', 'integer', ['null' => false]);
            $table->addColumn('call_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('email', 'string', ['limit' => 255, 'null' => true, 'default' => null]);
            $table->create();
        }

        if (!$this->hasTable('yametric_logs')) {
            $table = $this->table('yametric_logs', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb3']);
            $table->addColumn('id', 'integer', ['null' => false, 'identity' => true]);
            $table->addColumn('created', 'datetime', ['null' => true, 'default' => null]);
            $table->addColumn('user_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('visit_id', 'integer', ['null' => true, 'default' => null]);
            $table->addColumn('ip', 'string', ['limit' => 20, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('ya_type', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addColumn('ya_action', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'encoding' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci']);
            $table->addIndex(['created'], ['name' => 'created']);
            $table->addIndex(['user_id'], ['name' => 'user_id']);
            $table->addIndex(['ip'], ['name' => 'ip']);
            $table->addIndex(['ya_type'], ['name' => 'ya_type']);
            $table->addIndex(['ya_action'], ['name' => 'ya_action']);
            $table->addIndex(['ya_action', 'created'], ['name' => 'ya_action_2']);
            $table->addIndex(['visit_id'], ['name' => 'yametric_logs_visit_id_index']);
            $table->create();
        }

        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');
    }

}
