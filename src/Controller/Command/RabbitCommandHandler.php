<?php
declare(strict_types=1);

namespace App\Controller\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
final class RabbitCommandHandler
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(RabbitCommand $message): void
    {
        $this->logger->info('Message subject ' . $message->getSubject() . ', generated at ' . $message->getCreatedAt()->format('c'));
    }
}