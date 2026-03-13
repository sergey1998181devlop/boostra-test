<?php

require_once __DIR__ . '/Simpla.php';
require_once __DIR__ . '/interfaces/NotifierInterface.php';

class NotificationCenter extends Simpla
{
    private array $channels = [];

    public function register_channel(string $name, NotifierInterface $notifier): self
    {
        $this->channels[$name] = $notifier;
        return $this;
    }

    public function notify($channels, string $message, array $params = []): void
    {
        if (!is_array($channels)) {
            $channels = [$channels];
        }

        foreach ($channels as $ch) {
            if (!isset($this->channels[$ch])) {
                trigger_error("Notification channel '{$ch}' is not registered", E_USER_WARNING);
                continue;
            }

            $this->channels[$ch]->sendMessage($message, $params);
        }
    }

    public function notifyTemplate($channels, string $template, array $vars = [], array $params = []): void
    {
        if (!is_array($channels)) {
            $channels = [$channels];
        }

        foreach ($channels as $ch) {
            $message = $this->renderTemplateForChannel($ch, $template, $vars);

            $this->notify($ch, $message, $params);
        }
    }

    private function renderTemplateForChannel(string $channel, string $template, array $vars): string
    {
        $design = $this->design;

        $template_path = "notifications/{$channel}/{$template}";

        foreach ($vars as $key => $value) {
            $design->assign($key, $value);
        }

        return $design->fetch($template_path);
    }
}
