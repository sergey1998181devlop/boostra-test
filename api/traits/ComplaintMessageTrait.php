<?php

/**
 * Trait для работы с сообщениями о жалобах
 * Переиспользуется в ComplaintView
 */
trait ComplaintMessageTrait
{
    /**
     * Формирует сообщение о жалобе
     *
     * @param string $name ФИО клиента
     * @param string $phone Телефон
     * @param string $email Email
     * @param string $birth Дата рождения
     * @param string $topic Тема обращения
     * @param string $text Текст жалобы
     * @param null $usedesk_ticket_id ID тикета в Usedesk
     * @param bool $isHTML Форматировать для HTML (email)
     * @param null $profileUrl Ссылка на профиль клиента в CRM (для Telegram уведомлений)
     * @return string
     * @throws Exception
     */
    private function setMessage($name, $phone, $email, $birth, $topic, $text, $usedesk_ticket_id = null, $isHTML = false, $profileUrl = null): string
    {
        $eol = PHP_EOL;
        $quoteStart = '<blockquote>';
        $quoteEnd = '</blockquote>';
        if ($isHTML) {
            $eol = '<br>';
        }

        $loanStatus = $this->getLoanStatusMessage();

        $clientLink = '';
        if ($profileUrl) {
            $clientLink = $eol . "<b>Профиль в CRM</b>: " . $profileUrl;
        }

        $usedeskLink = '';
        if ($usedesk_ticket_id) {
            $link = "https://secure.usedesk.ru/tickets/" . $usedesk_ticket_id;
            $usedeskLink = $eol . "<b>Чат в Usedesk</b>: " . $link;
        }

        $clientName = $name;

        return sprintf(
            "Клиент отправил форму жалобы" . $eol . $eol .
            "<b>Клиент</b>: %s" . $eol .
            "<b>Телефон</b>: %s" . $eol .
            "<b>Email</b>: %s" . $eol .
            "<b>Дата рождения</b>: %s" . $eol .
            (!empty($loanStatus) ? "<b>Статус займов</b>: " . $loanStatus . $eol : "") .
            "<b>Тема обращения</b>: %s" . $eol . $clientLink . $usedeskLink . $eol . $eol .
            $quoteStart . "<b>%s</b>" . $quoteEnd,
            $clientName,
            $phone,
            $email,
            $birth,
            $topic,
            $text
        );
    }

    /**
     * Получает статус займов клиента
     *
     * @return string Информация о статусе займов
     * @throws Exception
     */
    private function getLoanStatusMessage(): string
    {
        if (empty($this->user)) {
            return '';
        }

        $user = $this->users->get_user(intval($this->user->id));
        if (empty($user->loan_history)) {
            return 'Нет активных договоров';
        }

        $loanHistory = $user->loan_history;

        $activeLoans = array_filter($loanHistory, function($loan) {
            return empty($loan->close_date);
        });

        if (empty($activeLoans)) {
            return 'Нет активных договоров';
        }

        $now = new DateTime();
        $maxOverdueDays = 0;

        foreach ($activeLoans as $loan) {
            if (!empty($loan->plan_close_date)) {
                $paymentDate = new DateTime($loan->plan_close_date);

                if ($paymentDate > $now) {
                    continue;
                }

                $interval = $now->diff($paymentDate);
                $daysOverdue = $interval->days;

                if ($daysOverdue > $maxOverdueDays) {
                    $maxOverdueDays = $daysOverdue;
                }
            }
        }

        if ($maxOverdueDays > 0) {
            return sprintf(
                'Договор%s просрочен%s %d %s',
                count($activeLoans) > 1 ? 'ы' : '',
                count($activeLoans) > 1 ? 'ы' : '',
                $maxOverdueDays,
                $this->declensionDays($maxOverdueDays)
            );
        }

        return 'Нет просроченных договоров';
    }

    /**
     * Склонение слова "день" в зависимости от числа
     *
     * @param int $number Количество дней
     * @return string Склоненное слово
     */
    private function declensionDays(int $number): string
    {
        $cases = [2, 0, 1, 1, 1, 2];
        $titles = ['день', 'дня', 'дней'];
        return $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }
}

