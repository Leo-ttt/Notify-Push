<?php

namespace LeoT\NotifyPush\Provider;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class WeComProvider implements ProviderInterface
{
    private const SETTING_ENABLED = 'leo-t-notify-push.wecom_enabled';
    private const SETTING_WEBHOOK_URL = 'leo-t-notify-push.wecom_webhook_url';

    protected SettingsRepositoryInterface $settings;
    protected LoggerInterface $logger;

    public function __construct(SettingsRepositoryInterface $settings, LoggerInterface $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;
    }

    public function send(string $title, string $body, string $url): bool
    {
        $webhookUrl = $this->settings->get(self::SETTING_WEBHOOK_URL, '');

        if (empty($webhookUrl)) {
            return false;
        }

        $content = "## {$title}\n\n{$body}\n\n[查看详情]({$url})";

        try {
            $client = new Client(['timeout' => 10]);
            $response = $client->post($webhookUrl, [
                'json' => [
                    'msgtype' => 'markdown',
                    'markdown' => [
                        'content' => $content,
                    ],
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Throwable $e) {
            $this->logger->error('[NotifyPush] WeCom send failed: ' . $e->getMessage());
            return false;
        }
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get(self::SETTING_ENABLED, false);
    }

    public static function getKey(): string
    {
        return 'wecom';
    }
}
