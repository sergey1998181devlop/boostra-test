<?php

namespace App\Core\Messenger\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Psr\Log\LoggerInterface;

class LoggingMiddleware implements MiddlewareInterface
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $messageClass = get_class($message);

        if ($envelope->last(ReceivedStamp::class)) {
            $this->log('info', 'Received message {messageClass}', ['messageClass' => $messageClass]);
        } else {
            $this->log('info', 'Dispatching message {messageClass}', ['messageClass' => $messageClass]);
        }

        try {
            $envelope = $stack->next()->handle($envelope, $stack);
            $this->log('info', 'Message {messageClass} handled successfully', ['messageClass' => $messageClass]);
            
            return $envelope;
        } catch (\Throwable $e) {
            $this->log('error', 'Message {messageClass} failed: {error}', [
                'messageClass' => $messageClass,
                'error' => $e->getMessage(),
                'exception' => $e
            ]);
            throw $e;
        }
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}