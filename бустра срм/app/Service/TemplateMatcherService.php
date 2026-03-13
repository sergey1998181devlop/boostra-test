<?php

namespace App\Service;

use App\Repositories\SmsTemplateRepository;

class TemplateMatcherService
{
    private SmsTemplateRepository $templateRepository;
    private ?array $templateCache = null;

    public function __construct(SmsTemplateRepository $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * @param string $message
     * @return object|null
     */
    public function findTemplateByMessage(string $message): ?object
    {
        if ($this->templateCache === null) {
            $this->templateCache = $this->templateRepository->findAllByType('from_tech');
        }

        if (empty($this->templateCache)) {
            return null;
        }

        $messageTrimmed = trim($message);

        // сначала пробуем точное совпадение
        foreach ($this->templateCache as $template) {
            if (trim($template->template) === $messageTrimmed) {
                return $template;
            }
        }

        // потом нормализованное совпадение
        $normalized = $this->normalizeMessage($message);

        foreach ($this->templateCache as $template) {
            $templateNormalized = $this->normalizeTemplate($template->template);

            if ($this->matchTemplate($normalized, $templateNormalized)) {
                return $template;
            }
        }

        return null;
    }

    private function normalizeMessage(string $message): string
    {
        $message = preg_replace('/\d+/', '{NUM}', $message);
        $message = preg_replace('/https?:\/\/[^\s]+/', '{URL}', $message);
        $message = preg_replace('/clck\.ru\/[^\s]+/', '{URL}', $message);
        $message = preg_replace('/boostra\.ru\/[^\s]+/', '{URL}', $message);
        $message = preg_replace('/[a-z0-9\-]+\.[a-z]{2,}\/[^\s]+/i', '{URL}', $message);
        return trim($message);
    }

    private function normalizeTemplate(string $template): string
    {
        $template = preg_replace('/\{\{[^}]+\}\}/', '{NUM}', $template);
        $template = preg_replace('/\{[^}]+\}/', '{NUM}', $template);
        $template = preg_replace('/https?:\/\/[^\s]+/', '{URL}', $template);
        $template = preg_replace('/clck\.ru\/[^\s]+/', '{URL}', $template);
        $template = preg_replace('/boostra\.ru\/[^\s]+/', '{URL}', $template);
        $template = preg_replace('/[a-z0-9\-]+\.[a-z]{2,}\/[^\s]+/i', '{URL}', $template);
        return trim($template);
    }

    private function matchTemplate(string $message, string $template): bool
    {
        $similarity = 0;
        similar_text($message, $template, $similarity);
        return $similarity > 80;
    }
}

