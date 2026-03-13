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
    private bool $tvMedicalShow;
    private bool $financialDoctorChecked;
    private bool $tvMedicalChecked;

    public function __construct(bool $financialDoctorShow, bool $tvMedicalShow, ?bool $financialDoctorChecked = null, ?bool $tvMedicalChecked = null)
    {
        $this->financialDoctorShow    = $financialDoctorShow;
        $this->tvMedicalShow          = $tvMedicalShow;
        $this->financialDoctorChecked = $financialDoctorChecked ?? $financialDoctorShow;
        $this->tvMedicalChecked       = $tvMedicalChecked ?? $tvMedicalShow;
    }

    public function toArray(): array
    {
        return [
            'financial_doctor' => ['show' => $this->financialDoctorShow, 'enable' => $this->financialDoctorChecked],
            'tv_medical' => ['show' => $this->tvMedicalShow, 'enable' => $this->tvMedicalChecked]
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['financial_doctor'] ?? false,
            $data['tv_medical'] ?? ($data['star_oracle'] ?? false)
        );
    }
} 
