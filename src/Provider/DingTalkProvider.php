<?php

namespace LeoT\NotifyPush\Provider;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class DingTalkProvider implements ProviderInterface
{
    private const SETTING_ENABLED = 'leo-t-notify-push.dingtalk_enabled';
    private const SETTING_WEBHOOK_URL = 'leo-t-notify-push.dingtalk_webhook_url';
    private const SETTING_SECRET = 'leo-t-notify-push.dingtalk_secret';

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

        $finalUrl = $this->buildSignedUrl($webhookUrl);

        $content = "## {$title}\n\n{$body}\n\n[查看详情]({$url})";

        try {
            $client = new Client(['timeout' => 10]);
            $response = $client->post($finalUrl, [
                'json' => [
                    'msgtype' => 'markdown',
                    'markdown' => [
                        'title' => $title,
                        'text' => $content,
                    ],
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Throwable $e) {
            $this->logger->error('[NotifyPush] DingTalk send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Build webhook URL with HMAC-SHA256 signature if secret is configured.
     */
    private function buildSignedUrl(string $webhookUrl): string
    {
        $secret = $this->settings->get(self::SETTING_SECRET, '');

        if (empty($secret)) {
            return $webhookUrl;
        }

        $timestamp = (string) round(microtime(true) * 1000);
        $stringToSign = $timestamp . "\n" . $secret;
        $sign = urlencode(base64_encode(hash_hmac('sha256', $stringToSign, $secret, true)));

        $separator = str_contains($webhookUrl, '?') ? '&' : '?';

        return $webhookUrl . $separator . 'timestamp=' . $timestamp . '&sign=' . $sign;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get(self::SETTING_ENABLED, false);
    }

    public static function getKey(): string
    {
        return 'dingtalk';
    }
}
