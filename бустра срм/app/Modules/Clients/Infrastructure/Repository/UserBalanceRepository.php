<?php

namespace App\Modules\Clients\Infrastructure\Repository;

use Database;
use Carbon\Carbon;

class UserBalanceRepository
{
    private Database $db;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function updateFromOneCData(int $userId, array $balance1c): void
    {
        $normalized = $this->normalizeBalance($userId, $balance1c);
        $zaimNumber = $balance1c['НомерЗайма'] ?? null;
        
        $existing = $zaimNumber 
            ? $this->findByUserAndLoan($userId, $zaimNumber)
            : null;
        
        if (!$existing) {
            $existing = $this->findByUserId($userId);
        }
        
        if ($existing) {
            $this->update($existing->id, $normalized);
        } else {
            $this->insert($normalized);
        }
    }
    
    private function normalizeBalance(int $userId, array $balance1c): array
    {
        $restructurisation = '';
        if (!empty($balance1c['Реструктуризация'])) {
            $decoded = json_decode($balance1c['Реструктуризация']);
            if ($decoded !== null) {
                $restructurisation = serialize($decoded);
            }
        }
        
        return [
            'user_id' => $userId,
            'zaim_number' => $balance1c['НомерЗайма'] ?? '',
            'zaim_summ' => $balance1c['СуммаЗайма'] ?? 0,
            'percent' => $balance1c['ПроцентнаяСтавка'] ?? 0,
            'ostatok_od' => $balance1c['ОстатокОД'] ?? 0,
            'ostatok_percents' => $balance1c['ОстатокПроцентов'] ?? 0,
            'ostatok_peni' => $balance1c['ОстатокПени'] ?? 0,
            'client' => $balance1c['Клиент'] ?? '',
            'zaim_date' => $balance1c['ДатаЗайма'] ?? '',
            'zayavka' => $balance1c['Заявка'] ?? ($balance1c['НомерЗаявки'] ?? ''),
            'restructurisation' => $restructurisation,
            'sale_info' => $balance1c['ИнформацияОПродаже'] ?? '',
            'payment_date' => $balance1c['ПланДата'] ?? null,
            'prolongation_amount' => $balance1c['СуммаДляПролонгации'] ?? 0,
            'last_prolongation' => $balance1c['ПоследняяПролонгация'] ?? 0,
            'prolongation_summ_percents' => $balance1c['СуммаДляПролонгации_Проценты'] ?? 0,
            'prolongation_summ_insurance' => $balance1c['СуммаДляПролонгации_Страховка'] ?? 0,
            'prolongation_summ_sms' => $balance1c['СуммаДляПролонгации_СМС'] ?? 0,
            'prolongation_summ_cost' => $balance1c['СуммаДляПролонгации_Стоимость'] ?? 0,
            'prolongation_count' => $balance1c['КоличествоПролонгаций'] ?? 0,
            'allready_added' => $balance1c['УжеНачислено'] ?? 0,
            'penalty' => $balance1c['ШтрафнойКД'] ?? 0,
            'sum_with_grace' => $balance1c['СуммаСоСкидкой'] ?? null,
            'sum_od_with_grace' => $balance1c['СуммаСоСкидкойОД'] ?? null,
            'sum_percent_with_grace' => $balance1c['СуммаСоСкидкойПроцент'] ?? null,
            'inn' => $balance1c['ИНН'] ?? null,
            'current_inn' => $balance1c['ИННТекущейОрганизации'] ?? null,
            'loan_type' => (isset($balance1c['IL']) && $balance1c['IL'] == 1) ? 'IL' : 'PDL',
            'last_update' => Carbon::now('Europe/Moscow')->format('Y-m-d H:i:s'),
        ];
    }
    
    private function findByUserId(int $userId)
    {
        $query = "SELECT id FROM s_user_balance WHERE user_id = ? LIMIT 1";
        $this->db->query($query, $userId);
        return $this->db->result();
    }
    
    private function findByUserAndLoan(int $userId, string $zaimNumber)
    {
        $query = "SELECT id FROM s_user_balance WHERE user_id = ? AND zaim_number = ? LIMIT 1";
        $this->db->query($query, $userId, $zaimNumber);
        return $this->db->result();
    }
    
    private function update(int $balanceId, array $normalized): void
    {
        unset($normalized['user_id']);
        
        $this->db->query(
            "UPDATE s_user_balance SET ?% WHERE id = ?",
            $normalized,
            $balanceId
        );
    }
    
    private function insert(array $normalized): void
    {
        $this->db->query(
            "INSERT INTO s_user_balance SET ?%",
            $normalized
        );
    }
}

