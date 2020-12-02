<?php

namespace Maximaster\BitrixSymfonyMailer;

interface BitrixMailerInterface
{
    public function mail(
        string $to,
        string $subject,
        string $message,
        string $additionalHeaders = '',
        string $additionalParameters = ''
    ): bool;
}
