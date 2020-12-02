<?php

namespace Maximaster\BitrixSymfonyMailer;

use DateTime;
use Maximaster\SelfRegistry\SelfRegistryTrait;
use PhpMimeMailParser\Parser;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\Headers;

class TransportMailer implements BitrixMailerInterface
{
    use SelfRegistryTrait;

    /** @var TransportInterface */
    private $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param string $additionalHeaders
     * @param string $additionalParameters
     *
     * @return bool
     *
     * @throws TransportExceptionInterface
     */
    public function mail(
        string $to,
        string $subject,
        string $message,
        string $additionalHeaders = '',
        string $additionalParameters = ''
    ): bool {
        $parser = new Parser();
        $parser->setText($additionalHeaders."\n\n".$message);

        $headers = new Headers();

        foreach ($parser->getHeaders() as $headerName => $headerValue) {
            $lcHeader = strtolower($headerName);

            switch ($lcHeader) {
                case 'date':
                    $headers->addDateHeader($headerName, DateTime::createFromFormat('D, d M Y H:i:s O', $headerValue));
                    break;

                case 'sender':
                    $headers->addMailboxHeader($headerName, $headerValue);
                    break;

                case 'from':
                case 'reply-to':
                case 'to':
                case 'cc':
                case 'bc':
                case 'bcc':
                    if (!filter_var($headerValue, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }
                    $headers->addMailboxListHeader($headerName, [ $headerValue ]);
                    break;

                default:
                    // Некоторые заголовки будут устанавливаться при компиляции письма автоматически, или путём вызовов
                    // соответствующих методов на объекте письма. Поэтому добавление их отсюда создаст дублирование
                    if (in_array($lcHeader, ['content-type', 'subject', 'to'])) {
                        continue;
                    }

                    $headers->addTextHeader($headerName, $headerValue);
            }
        }

        $email = new Email();
        $email
            ->setHeaders($headers)
            ->to($to)
            ->subject($subject)
            ->text($parser->getMessageBody('text'))
            ->html($parser->getMessageBody('html'));

        return !!$this->transport->send($email);
    }
}
