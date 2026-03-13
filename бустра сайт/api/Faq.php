<?php

require_once(__DIR__ . '/../api/Simpla.php');

class Faq extends Simpla {
    public const BLOCK_TYPES = [
        'public' => 'public',
        'application_process' => 'application_process',
        'authorized_no_loans' => 'authorized_no_loans',
        'active_loan' => 'active_loan',
        'overdue_debt' => 'overdue_debt',
        'closed_loans' => 'closed_loans'
    ];

    /**
     * @param string|array $blockTypes
     * @return array
     */
    public function getFaqByType($blockTypes) {
        if (!is_array($blockTypes)) {
            $blockTypes = [$blockTypes];
        }

        if (empty($blockTypes)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($blockTypes), '?'));

        // Если для вопроса не заполнен идентификатор цели Яндекс.Метрики, то используем идентификатор цели родительского блока
        $this->db->query("
            SELECT
                f.id,
                f.section_id,
                f.question,
                f.answer,
                CASE
                    WHEN f.yandex_goal_id IS NULL OR f.yandex_goal_id = '' THEN fb.yandex_goal_id
                    ELSE f.yandex_goal_id
                END AS yandex_goal_id,
                s.name AS section_name,
                s.sequence AS section_sequence,
                fb.type,
                fb.id AS block_id,
                fb.yandex_goal_id AS parent_goal_id
            FROM s_faq f
            INNER JOIN s_faq_sections s ON f.section_id = s.id
            INNER JOIN s_faq_blocks fb ON s.block_id = fb.id
            WHERE fb.site_id = ? AND fb.type IN ($placeholders)
            ORDER BY s.sequence ASC, f.id ASC
        ", $this->config->site_id, ...$blockTypes);

        return $this->db->results();
    }

    public function getFaqBySectionId(int $sectionId): array {
        $this->db->query("
            SELECT
                f.id,
                f.section_id,
                f.question,
                f.answer,
                f.yandex_goal_id,
                s.name AS section_name
            FROM s_faq f
            INNER JOIN s_faq_sections s ON f.section_id = s.id
            INNER JOIN s_faq_blocks fb ON s.block_id = fb.id
            WHERE fb.site_id = ? AND f.section_id = ?
            ORDER BY f.id ASC
        ", $this->config->site_id, $sectionId);

        return $this->db->results();
    }
}