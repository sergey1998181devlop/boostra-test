<?php
/**
 * @author Jewish Programmer
 */

/**
 * @property Leadgid $leadgid
 */
class Webmaster extends Simpla
{
    /**
     * @var stdClass $order
     */
    private stdClass $order;

    /**
     * @var stdClass $manager
     */
    private stdClass $manager;

    /**
     * @var array $updateOrder
     */
    private array $updateOrder;

    /**
     * system manager id
     * @var int MANAGER_ID
     */
    const MANAGER_ID = 50;

    /**
     * Cancel status order
     * @var int ORDER_CANCEL_STATUS
     */
    const ORDER_CANCEL_STATUS = 3;

    /**
     * Webmaster reason id
     * @var int REASON_ID
     */
    const REASON_ID = 37;

    /**
     * Text for cancel status order
     * @var string ORDER_CANCEL_STATUS_TEXT
     */
    const ORDER_CANCEL_STATUS_TEXT = 'Отказано';

    /**
     * @deprecated
     * method for auto run scorings @NOT_USED
     * @param int $scoringId
     * @return void
     */
    public function run_scoring($scoringId): void
    {
        if ($scoring = $this->scorings->get_scoring($scoringId)) {
            if ($this->order = $this->orders->get_order((int) $scoring->order_id)) {
                $update = array(
                    'status' => $this->scorings::STATUS_COMPLETED,
                    'success' => 1,
                    'body' => 'Одобрена',
                    'string_result' => ''
                );
                if ($this->isBlocked()) {
                    try {
                        $this->runDeclineProcess();
                        $update['success'] = 0;
                        $update['body'] = 'Не одобрена';
                        $update['string_result'] = 'Webmaster заблокирован!';
                    } catch (Exception $e) {
                        $update = [
                            'status' => $this->scorings::STATUS_ERROR,
                            'success' => 0,
                            'body' => $e->getMessage(),
                            'string_result' => 'Не до конца проверено из за проблем с 1С!',
                        ];
                    }
                } elseif (empty($order->webmaster_id)) {
                    $update['string_result'] = 'Webmaster не установлен!';
                }
            } else {
                $update = array(
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'не найдена заявка'
                );
            }

            if ($update['status'] == $this->scorings::STATUS_COMPLETED) {
                $update['end_date'] = date('Y-m-d H:i:s');
            }
            $this->scorings->update_scoring($scoringId, $update);
        }
    }

    /**
     * Set order from outside
     * @param object $order
     * @return void
     */
    public function setOrder(object $order): void
    {
        if ($order) {
            $this->order = $order;
        }
    }

    /**
     * Run decline methods
     * @return void
     */
    public function runDeclineProcess(): void
    {
        $this->manager = $this->managers->get_manager(self::MANAGER_ID);
        $this->updateOrder = array(
            'status' => self::ORDER_CANCEL_STATUS,
            'manager_id' => $this->manager->id,
            'reason_id' => self::REASON_ID,
            'reject_date' => date('Y-m-d H:i:s'),
        );
        $this->orders->update_order($this->order->order_id, $this->updateOrder);
        $this->leadgid->reject_actions($this->order->order_id);
        $this->addChangeLog();
        $reason = $this->reasons->get_reason(self::REASON_ID);
        $this->soap->update_status_1c($this->order->id_1c, self::ORDER_CANCEL_STATUS_TEXT, $this->manager->name_1c, 0, 1, $reason->admin_name);
        $this->soap->send_order_manager($this->order->id_1c, $this->manager->name_1c);
    }

    /**
     * Check webmaster_id for blocked or not
     * @return bool
     */
    public function isBlocked(): bool
    {
        $this->order->webmaster_id = (int) trim($this->order->webmaster_id);
        if (empty($this->order->webmaster_id)) {
            return false;
        }
        $query = $this->db->placehold("SELECT EXISTS (SELECT id FROM __stop_list_web_id WHERE utm_source = ? AND web_master_id = ?) block", $this->order->utm_source, $this->order->webmaster_id);
        $this->db->query($query);
        $res = $this->db->result();
        return ($res && isset($res->block)) ? $res->block : true;
    }

    /**
     * Add data to change_logs
     * @return void
     */
    private function addChangeLog(): void
    {
        $logOldValues = [];
        foreach ($this->updateOrder as $key => $val) {
            if ($this->order->$key != $this->updateOrder[$key]) {
                $logOldValues[$key] = $this->order->$key;
            }
        }
        $logNewValues = array_diff($this->updateOrder, $logOldValues);
        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($logOldValues),
            'new_values' => serialize($logNewValues),
            'order_id' => $this->order->order_id,
            'user_id' => $this->order->user_id,
        ));
    }
}
