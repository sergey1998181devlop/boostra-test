<?php

namespace App\Dto;

class ExtraServiceVisibilityDto
{
    /**
     * @var bool
     */
    private bool $financialDoctorShow;

    /**
     * @var bool
     */
    private bool $starOracleShow;
    private bool $financialDoctorChecked;
    private bool $starOracleChecked;

    public function __construct(bool $financialDoctorShow, bool $starOracleShow, bool $financialDoctorChecked = null, bool $starOracleChecked = null)
    {
        $this->financialDoctorShow = $financialDoctorShow;
        $this->starOracleShow = $starOracleShow;
        $this->financialDoctorChecked = $financialDoctorChecked ?? !$financialDoctorShow;
        $this->starOracleChecked = $starOracleChecked ?? !$starOracleShow;
    }

    public function toArray(): array
    {
        return [
            'financial_doctor' => ['show' => $this->financialDoctorShow, 'enable' => $this->financialDoctorChecked],
            'star_oracle' => ['show' => $this->starOracleShow, 'enable' => $this->starOracleChecked]
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['financial_doctor'] ?? false,
            $data['star_oracle'] ?? false
        );
    }
} 
