<?php

require_once('View.php');

class FaqView extends View {
    use \api\traits\JWTAuthTrait;

    public function fetch() {
        $action = $this->request->get('action');

        switch ($action) {
            case 'section':
                return $this->renderFaqSection(false);
            case 'user_section':
                return $this->renderFaqSection(true);
            case 'search':
                $this->faqSearchAction();
                return null;
            default:
                return $this->renderFaq();
        }
    }

    private function groupFaqBySection(array $items): array {
        $grouped = [];

        foreach ($items as $item) {
            $sectionId = $item->section_id;

            if (!isset($grouped[$sectionId])) {
                $grouped[$sectionId] = [
                    'section_id' => $sectionId,
                    'section_name' => $item->section_name,
                    'block_yandex_goal_id' => $item->parent_goal_id ?? null,
                    'faqs' => []
                ];
            }

            $grouped[$sectionId]['faqs'][] = $item;
        }

        return array_values($grouped);
    }

    private function renderFaqSection(bool $isUser = false) {
        if ($isUser) {
            $this->jwtAuthValidate();

            if (empty($this->user)) {
                header('Location: ' . $this->config->root_url . '/user/login');
                exit();
            }
        }

        $sectionId = $this->request->get('section_id', 'integer');
        $questionId = $this->request->get('q', 'integer');

        if (!$sectionId) return false;

        $faqItems = $this->faq->getFaqBySectionId($sectionId);
        if (empty($faqItems)) return false;

        $selected = $questionId ? array_filter($faqItems, fn($f) => $f->id == $questionId) : [$faqItems[0]];
        $selectedFaq = reset($selected);

        $this->design->assign('faqs', $faqItems);
        $this->design->assign('section_name', $faqItems[0]->section_name);
        $this->design->assign('selected_question', $selectedFaq->question ?? '');
        $this->design->assign('selected_answer', $selectedFaq->answer ?? '');
        $this->design->assign('is_user_faq', $isUser);

        return $this->design->fetch($isUser ? 'user_faq_section.tpl' : 'faq_section.tpl');
    }

    
    private function renderFaq() {
        $blockKey = $this->request->get('block');

        // Если параметр block не передан, считаем что используется /faq
        // Значит нужно показать блок "application_process"
        if (!$blockKey) {
            $blockKey = 'application_process';
        }

        // Обработка /user/faq
        if ($blockKey == 'user') {
            $this->jwtAuthValidate();

            if (empty($this->user)) {
                header('Location: ' . $this->config->root_url . '/user/login');
                exit();
            }

            $userBalance = $this->users->get_user_balance($this->user->id);
            $userLastLoan = $this->orders->get_last_order($this->user->id);

            $blockTypes = [];

            $zaimDate = $userBalance->zaim_date ?? '';
            $paymentDate = $userBalance->payment_date ?? '';
            $haveClosedLoans = !empty($userLastLoan->have_close_credits);
            $isComplete = !empty($userLastLoan->complete);

            $defaultZaimDate = '0001-01-01T00:00:00';
            $defaultPaymentDate = '0001-01-01 00:00:00';
            $todayDate = date('Y-m-d H:i:s');

            // 1. Закрытые займы
            if ($zaimDate === $defaultZaimDate && $isComplete && $haveClosedLoans) {
                $blockTypes[] = Faq::BLOCK_TYPES['closed_loans'];
            } // 2. Активный займ
            elseif ($zaimDate !== $defaultZaimDate && $paymentDate !== $defaultPaymentDate && $paymentDate > $todayDate) {
                $blockTypes[] = Faq::BLOCK_TYPES['active_loan'];
            } // 3. Просроченный займ
            elseif ($zaimDate !== $defaultZaimDate && $paymentDate !== $defaultPaymentDate && $paymentDate < $todayDate) {
                $blockTypes[] = Faq::BLOCK_TYPES['overdue_debt'];
            } // 4. Авторизованный пользователь без займов
            elseif ($zaimDate == $defaultZaimDate && !$haveClosedLoans) {
                $blockTypes[] = Faq::BLOCK_TYPES['authorized_no_loans'];
            }

            $faqItems = $this->faq->getFaqByType($blockTypes);
            $faqSections = $this->groupFaqBySection($faqItems);

            $blockGoalId = !empty($faqItems) ? ($faqItems[0]->parent_goal_id ?? null) : null;

            $this->design->assign('faq_sections', $faqSections);
            $this->design->assign('is_user_faq', true);
            $this->design->assign('block_goal_id', $blockGoalId);

            return $this->design->fetch('user_faq.tpl');
        }

        // Обработка /faq/main
        if ($blockKey === 'main') {
            $blockKey = 'public';
        }

        $blockType = Faq::BLOCK_TYPES[$blockKey] ?? Faq::BLOCK_TYPES['public'];
        $faqItems = $this->faq->getFaqByType($blockType);
        $faqSections = $this->groupFaqBySection($faqItems);

        $blockGoalId = !empty($faqItems) ? ($faqItems[0]->parent_goal_id ?? null) : null;

        $this->design->assign('faq_sections', $faqSections);
        $this->design->assign('is_user_faq', false);
        $this->design->assign('block_goal_id', $blockGoalId);

        return $this->design->fetch('faq.tpl');
    }

    private function faqSearchAction(): void
    {
        $query = trim((string)$this->request->get('query', 'string'));
        $scope = (string)$this->request->get('scope', 'string'); // 'public' | 'user'
        $limit = 12;

        if (mb_strlen($query, 'UTF-8') < 3) {
            $this->request->json_output(['query' => $query, 'suggestions' => []]);
        }

        // Нормализация регистра и Ё->Е
        $qLower = mb_strtolower($query, 'UTF-8');
        $qLowerNormalized = str_replace(['ё','Ё'], ['е','е'], $qLower);
        $kw = $this->db->escape($qLower);
        $kwN = $this->db->escape($qLowerNormalized);

        // Типы блоков для поиска
        $allTypes = ['public','application_process','authorized_no_loans','active_loan','overdue_debt','closed_loans'];
        $types = $allTypes;

        if ($scope === 'user') {
            $userTypes = $this->getUserFaqBlockTypes();
            if (!empty($userTypes)) {
                $types = $userTypes;
            }
        }

        $typesSql = "('" . implode("','", array_map(function($t){ return $this->db->escape($t); }, $types)) . "')";

        $sql = "
            SELECT
                f.id,
                f.section_id,
                f.question,
                f.answer,
                s.name AS section_name
            FROM s_faq f
            INNER JOIN s_faq_sections s ON f.section_id = s.id
            INNER JOIN s_faq_blocks fb ON s.block_id = fb.id
            WHERE fb.site_id = '" . $this->db->escape($this->config->site_id) . "' AND fb.type IN $typesSql
              AND (
                    LOWER(f.question) LIKE '%$kw%'
                 OR LOWER(f.answer)   LIKE '%$kw%'
                 OR LOWER(REPLACE(f.question,'ё','е')) LIKE '%$kwN%'
                 OR LOWER(REPLACE(f.answer,'ё','е'))   LIKE '%$kwN%'
              )
            ORDER BY
              (CASE
                 WHEN LOWER(f.question) LIKE '$kw%' THEN 0
                 WHEN LOWER(f.question) LIKE '% $kw%' THEN 1
                 ELSE 2
               END), CHAR_LENGTH(f.question)
            LIMIT $limit
        ";

        $this->db->query($sql);
        $items = $this->db->results();

        $suggestions = [];
        foreach ($items as $it) {
            $url = ($scope === 'user')
                ? "/user/faq?action=user_section&section_id={$it->section_id}&q={$it->id}"
                : "/faq?action=section&section_id={$it->section_id}&q={$it->id}";

            // Текст ответа без HTML, с усечением
            $answerRaw = is_string($it->answer) ? $it->answer : '';
            $answerPlain = strip_tags(html_entity_decode($answerRaw, ENT_QUOTES, 'UTF-8'));
            $answerPlain = trim(preg_replace('/\s+/u', ' ', $answerPlain));
            if ($answerPlain === '') {
                $answerPlain = (string)$it->question;
            }
            if (mb_strlen($answerPlain, 'UTF-8') > 140) {
                $answerPlain = mb_substr($answerPlain, 0, 140, 'UTF-8') . '…';
            }

            $suggestions[] = [
                'value' => $answerPlain,
                'data'  => [
                    'id' => (int)$it->id,
                    'section_id' => (int)$it->section_id,
                    'section_name' => $it->section_name,
                    'url' => $url,
                ]
            ];
        }

        $this->request->json_output(['query' => $query, 'suggestions' => $suggestions]);
    }

    private function getUserFaqBlockTypes(): array
    {
        if (empty($this->user)) {
            return [];
        }

        $userBalance = $this->users->get_user_balance($this->user->id);
        $userLastLoan = $this->orders->get_last_order($this->user->id);

        $types = [];

        $zaimDate = $userBalance->zaim_date ?? '';
        $paymentDate = $userBalance->payment_date ?? '';
        $haveClosedLoans = !empty($userLastLoan->have_close_credits);
        $isComplete = !empty($userLastLoan->complete);

        $defaultZaimDate = '0001-01-01T00:00:00';
        $defaultPaymentDate = '0001-01-01 00:00:00';
        $todayDate = date('Y-m-d H:i:s');

        if ($zaimDate === $defaultZaimDate && $isComplete && $haveClosedLoans) {
            $types[] = Faq::BLOCK_TYPES['closed_loans'];
        } elseif ($zaimDate !== $defaultZaimDate && $paymentDate !== $defaultPaymentDate && $paymentDate > $todayDate) {
            $types[] = Faq::BLOCK_TYPES['active_loan'];
        } elseif ($zaimDate !== $defaultZaimDate && $paymentDate !== $defaultPaymentDate && $paymentDate < $todayDate) {
            $types[] = Faq::BLOCK_TYPES['overdue_debt'];
        } elseif ($zaimDate == $defaultZaimDate && !$haveClosedLoans) {
            $types[] = Faq::BLOCK_TYPES['authorized_no_loans'];
        }

        return $types;
    }
}