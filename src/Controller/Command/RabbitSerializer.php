<?php

namespace App\Controller\Command;

use DateTime;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Envelope;

final class RabbitSerializer implements SerializerInterface
{
    public function decode(array $encodedEnvelope): Envelope
    {
        $record = json_decode($encodedEnvelope['body'], true);

        return new Envelope(new RabbitCommand(
            $record['subject'],
            new DateTime($record['createdAt']['date']),
        ));
    }

    public function encode(Envelope $envelope): array
    {
        /** @var RabbitCommand $event */
        $event = $envelope->getMessage();

        return [
            'key' => md5($event->getSubject() . $event->getCreatedAt()->format('c')),
            'headers' => [],
            'body' => json_encode([
                'subject' => $event->getSubject(),
                'createdAt' => $event->getCreatedAt(),
            ])
        ];
    }
}