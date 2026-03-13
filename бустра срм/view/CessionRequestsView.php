<?php

require_once('View.php');

class CessionRequestsView extends View
{
    public function fetch()
    {
        $conditions = [];
        $params = [];

        $limit = 30;
        $page = max(1, (int)($this->request->get('page') ?? 1));
        $offset = ($page - 1) * $limit;

        $searchFields = [
            'full_name_with_birth',
            'contract_number',
            'shkd_number',
            'contract_date',
            'request_date',
            'email',
            'contract_form',
            'cedent',
            'counterparty',
            'transfer_date',
            'importance',
            'execution_status',
            'comments',
            'extra_actions',
            'client_replace_status'
        ];

        $daterange = trim($this->request->get('daterange'));

        if ($daterange) {
            $dates = explode(' - ', $daterange);
            if (count($dates) === 2) {
                $startDate = DateTime::createFromFormat('d.m.Y', trim($dates[0]));
                $endDate = DateTime::createFromFormat('d.m.Y', trim($dates[1]));
                if ($startDate && $endDate) {
                    $endDate->modify('+1 day');
                    $conditions[] = "request_date >= ? AND request_date < ?";
                    $params[] = $startDate->format('Y-m-d');
                    $params[] = $endDate->format('Y-m-d');
                    $this->design->assign('daterange', $daterange);
                }
            }
        }

        foreach ($searchFields as $field) {
            $value = preg_replace('/\s+/u', ' ', trim($this->request->get($field)));
            if ($value === '') {
                continue;
            }

            if (in_array($field, ['contract_date', 'request_date', 'transfer_date'], true)) {
                if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
                    [$d, $m, $y] = explode('.', $value);
                    $value = "$y-$m-$d";
                }
                $conditions[] = "$field = ?";
                $params[] = $value;
            } elseif (in_array($field, ['execution_status', 'importance', 'cedent', 'contract_form'], true)) {
                $conditions[] = "$field = ?";
                $params[] = $value;
            } else {
                $conditions[] = "LOWER(`$field`) LIKE LOWER(?)";
                $params[] = '%' . $value . '%';
            }

            $this->design->assign($field, $value);
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $countQuery = $this->db->placehold("SELECT COUNT(*) FROM cession_requests $where", ...$params);
        $this->db->query($countQuery);
        $total = $this->db->result('COUNT(*)');

        $this->design->assign('pages_count', ceil($total / $limit));
        $this->design->assign('current_page', $page);

        $paramsWithLimit = array_merge($params, [$offset, $limit]);
        $selectQuery = $this->db->placehold("
            SELECT *
            FROM cession_requests
            $where
            ORDER BY id DESC
            LIMIT ?, ?
        ", ...$paramsWithLimit);

        $this->db->query($selectQuery);
        $requests = $this->db->results();

        // Подгружаем списки enum-значений из s_settings (JSON, единый ключ).
        $enumFields = ['execution_status', 'importance', 'contract_form', 'cedent', 'counterparty'];
        $enumValues = [];

        // Получаем все значения из одного ключа cession_settings
        $raw = $this->settings->{'cession_settings'};
        if ($raw === null) {
            $raw = $this->settings->__get('cession_settings');
        }
        $settings = is_array($raw) ? $raw : json_decode((string)$raw, true);

        foreach ($enumFields as $field) {
            $list = [];
            if (isset($settings[$field]) && is_array($settings[$field])) {
                foreach ($settings[$field] as $v) {
                    if (is_scalar($v)) {
                        $t = trim((string)$v);
                        if ($t !== '') {
                            $list[] = $t;
                        }
                    }
                }
            }

            $enumValues[$field] = $list;
        }

        $this->design->assign('enumValues', $enumValues);
        $this->design->assign('requests', $requests);
        $this->design->assign('meta_title', 'Цессия');
        $this->design->assign('searchFields', $searchFields);

        return $this->design->fetch('cession_requests.tpl');
    }
}