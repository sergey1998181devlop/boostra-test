<?php

namespace App\Dto;

class ReturnCoefficientDto
{
    public float $conversionOnIssuance;
    public float $conversionOnPayment;
    public float $overallReturnPct;

    /** @var array<string, float> ['nk' => 76.1, 'pk' => 88.9] */
    public array $clientType;

    /** @var array<string, float> ['pdl' => 74.3, 'il' => 91.0] */
    public array $loanType;

    /** @var array<string, float> ['site' => 80.5, 'android' => 83.2, 'ios' => 87.6] */
    public array $platform;

    /** @var array<string, float> ['male' => 81.8, 'female' => 84.2] */
    public array $gender;

    /** @var array<string, float> ['lt600' => 59.8, 'gte600' => 90.4] */
    public array $score;

    /** @var array<string, float> ['organic' => 78.9, 'auto_approve' => 70.2, 'cross_order' => 86.7, 'other' => 65.4] */
    public array $source;


    public function __construct(array $data)
    {
        $this->conversionOnIssuance = (float)($data['conversion_on_issuance'] ?? 0);
        $this->conversionOnPayment  = (float)($data['conversion_on_payment']  ?? 0);
        $this->overallReturnPct     = (float)($data['overall_return_pct']     ?? 0);

        $this->clientType  = $this->parseGroup($data, 'client_type',  ['nk', 'pk']);
        $this->loanType    = $this->parseGroup($data, 'loan_type',    ['pdl', 'il']);
        $this->platform    = $this->parseGroup($data, 'platform',     ['site', 'android', 'ios']);
        $this->gender      = $this->parseGroup($data, 'gender',       ['male', 'female']);
        $this->score       = $this->parseGroup($data, 'score',        ['lt600', 'gte600']);
        $this->source      = $this->parseGroup($data, 'source',       ['organic', 'auto_approve', 'cross_order', 'other']);
    }

    /**
     * @return array<string, array<string, float>>
     */
    public function getTraitGroups(): array
    {
        return [
            'client_type'  => $this->clientType,
            'loan_type'    => $this->loanType,
            'platform'     => $this->platform,
            'gender'       => $this->gender,
            'score'        => $this->score,
            'source'       => $this->source,
        ];
    }

    private function parseGroup(array $data, string $key, array $expectedKeys): array
    {
        $group  = $data[$key] ?? [];
        $result = [];

        foreach ($expectedKeys as $k) {
            $result[$k] = (float)($group[$k] ?? 0);
        }

        return $result;
    }
}
