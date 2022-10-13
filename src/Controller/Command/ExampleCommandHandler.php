<?php

namespace App\Controller\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'consumer', priority: 10)]
final class ExampleCommandHandler
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(ExampleCommand $message): void
    {
        $this->logger->info('Message consumed with subject ' . $message->getSubject() . ', generated at ' . $message->getCreatedAt()->format('c'));
    }
}