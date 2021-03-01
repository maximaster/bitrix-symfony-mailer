# maximaster/bitrix-symfony-mailer

Позволяет использовать symfony/mailer транспорты для отправки почты в Битрикс.

## Использование

```bash
composer require maximaster/bitrix-symfony-mailer
```

Создайте `\Maximaster\BitrixSymfonyMailer\TransportMailer` с нужным транспортом и вызовите из него метод `mail` в
`custom_mail` 
