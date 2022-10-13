<?php

namespace App\Controller\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'kafka_consumer', priority: 10)]
final class KafkaCommandHandler
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(KafkaCommand $message): void
    {
        $this->logger->info('Message consumed with subject ' . $message->getSubject() . ', generated at ' . $message->getCreatedAt()->format('c'));
    }
}