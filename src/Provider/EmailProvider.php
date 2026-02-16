<?php

namespace LeoT\NotifyPush\Provider;

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;
use Psr\Log\LoggerInterface;

class EmailProvider implements ProviderInterface
{
    private const SETTING_ENABLED = 'leo-t-notify-push.email_enabled';
    private const SETTING_RECIPIENTS = 'leo-t-notify-push.email_recipients';

    protected SettingsRepositoryInterface $settings;
    protected Mailer $mailer;
    protected LoggerInterface $logger;

    public function __construct(
        SettingsRepositoryInterface $settings,
        Mailer $mailer,
        LoggerInterface $logger
    ) {
        $this->settings = $settings;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function send(string $title, string $body, string $url): bool
    {
        $recipients = $this->getRecipients();

        if (empty($recipients)) {
            return false;
        }

        $textBody = $this->buildPlainText($title, $body, $url);

        try {
            $this->mailer->raw($textBody, function (Message $message) use ($title, $recipients) {
                $message->to($recipients);
                $message->subject('[Forum] ' . $title);
            });

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('[NotifyPush] Email send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @return string[]
     */
    private function getRecipients(): array
    {
        $raw = $this->settings->get(self::SETTING_RECIPIENTS, '');

        if (empty($raw)) {
            return [];
        }

        return array_filter(
            array_map('trim', explode(',', $raw))
        );
    }

    private function buildPlainText(string $title, string $body, string $url): string
    {
        return "{$title}\n\n{$body}\n\n查看详情: {$url}";
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get(self::SETTING_ENABLED, false);
    }

    public static function getKey(): string
    {
        return 'email';
    }
}
