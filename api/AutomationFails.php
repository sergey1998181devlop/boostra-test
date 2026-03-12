<?php

class AutomationFails extends Simpla
{
    private const SOAP_ERROR_TYPE = 'soap_error';

    private bool $soapError;

    public function __construct()
    {
        parent::__construct();

        $this->soapError = $this->getSoapError();
    }

    public function setSoapError(bool $isActive): void
    {
        if ($this->soapError !== $isActive) {
            $this->updateSoapError($isActive);
        }

        $this->soapError = $isActive;
    }

    private function getSoapError(): bool
    {
        $query = "SELECT is_active FROM automation_fails WHERE type = ?";

        $this->db->query($query, self::SOAP_ERROR_TYPE);

        return (bool)$this->db->result('is_active') ?? false;
    }

    private function updateSoapError(bool $isActive): void
    {
        if ($isActive) {
            $query = $this->db->placehold(
                'UPDATE automation_fails SET is_active = ?, last_notification_at = ? WHERE type = ? AND is_active <> ?',
                true,
                date('Y-m-d H:i:s'),
                self::SOAP_ERROR_TYPE,
                true
            );
        } else {
            $query = $this->db->placehold(
                'UPDATE automation_fails SET is_active = ? WHERE type = ? AND is_active <> ?',
                false,
                self::SOAP_ERROR_TYPE,
                false
            );
        }

        $this->db->query($query);
    }
}