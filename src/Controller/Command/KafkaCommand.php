<?php

namespace App\Controller\Command;

use DateTime;

final class KafkaCommand
{
    public function __construct(private string $subject, private \DateTime $createdAt)
    {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
