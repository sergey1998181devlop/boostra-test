<?php

namespace api\traits;

/**
 * Проверка валидности отображения кнопок авторизации
 */
trait AuthButtonsTrait
{
    /**
     * Проверка кнопки ТБанк ID, и установка флага для отображения
     *
     * @param bool $init Инициализировать ли переменные для шаблона
     * @return bool
     */
    private function checkTBankShowButton(bool $init = true): bool
    {
        $utm_source = trim($_COOKIE['utm_source'] ?? $this->request->get( 'utm_source') ?: '');
        if (in_array($utm_source, array_map('trim', $this->settings->t_bank_button_registration['utm_sources'] ?? []))) {
            $utm_source_valid = true;
        }

        $is_access = !empty($this->settings->t_bank_button_registration['status']) && (!empty($utm_source_valid) || $this->users->is_organic($utm_source));

        if ($is_access && $init) {
            $this->design->assign('t_id_state', $this->TBankIdService->setState());
            $this->design->assign('t_id_redirect_url', $this->config->root_url . '/t-bank-id/auth');
            $this->design->assign('t_id_auth_url', $this->TBankIdService->getAuthUrl());
            $this->design->assign('t_bank_button_registration_access', true);
            $this->TBankIdService->setCookie();
            $this->design->assign('t_id_error', $_SESSION['t_id_error'] ?? null);
        }

        return $is_access;
    }

    /**
     * Проверка кнопки ГосУслуг, и установка флага для отображения
     *
     * @param bool $init Инициализировать ли переменные для шаблона
     * @return bool
     */
    private function checkEsiaShowButton(bool $init = true): bool
    {
        $utm_source = trim($_COOKIE['utm_source'] ?? $this->request->get( 'utm_source') ?: '');
        if (in_array($utm_source, array_map('trim', $this->settings->esia_button_registration['utm_sources'] ?? []))) {
            $utm_source_valid = true;
        }

        $is_access = !empty($this->settings->esia_button_registration['status']) && (!empty($utm_source_valid) || $this->users->is_organic($utm_source));

        if ($is_access && $init) {
            $this->design->assign('esia_redirect_url', $this->config->root_url . '/esia/init');
            $this->design->assign('esia_button_registration_access', true);
            $this->esia_service->setState();
            $this->esia_service->setCookie();
            $this->design->assign('esia_id_error',  $_SESSION['esia_id_error'] ?? null);
        }

        return $is_access;
    }

    /**
     * Инициализирует все кнопки с переменными шаблона
     *
     * @return void
     */
    private function initAuthAllButtons()
    {
        $this->checkTBankShowButton();
        $this->checkEsiaShowButton();
    }
}
