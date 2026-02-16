<?php

namespace LeoT\NotifyPush\Provider;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class WebhookProvider implements ProviderInterface
{
    private const SETTING_ENABLED = 'leo-t-notify-push.webhook_enabled';
    private const SETTING_URL = 'leo-t-notify-push.webhook_url';
    private const SETTING_METHOD = 'leo-t-notify-push.webhook_method';
    private const SETTING_HEADERS = 'leo-t-notify-push.webhook_headers';

    protected SettingsRepositoryInterface $settings;
    protected LoggerInterface $logger;

    public function __construct(SettingsRepositoryInterface $settings, LoggerInterface $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;
    }

    public function send(string $title, string $body, string $url): bool
    {
        $webhookUrl = $this->settings->get(self::SETTING_URL, '');

        if (empty($webhookUrl)) {
            return false;
        }

        $method = strtoupper($this->settings->get(self::SETTING_METHOD, 'POST'));
        $headers = $this->parseHeaders();

        $payload = [
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'timestamp' => date('c'),
        ];

        try {
            $client = new Client(['timeout' => 10]);
            $response = $client->request($method, $webhookUrl, [
                'headers' => $headers,
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();

            return $statusCode >= 200 && $statusCode < 300;
        } catch (\Throwable $e) {
            $this->logger->error('[NotifyPush] Webhook send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse custom headers from settings (one per line, format: Key: Value).
     *
     * @return array<string, string>
     */
    private function parseHeaders(): array
    {
        $raw = $this->settings->get(self::SETTING_HEADERS, '');

        if (empty($raw)) {
            return ['Content-Type' => 'application/json'];
        }

        $headers = ['Content-Type' => 'application/json'];
        $lines = array_filter(array_map('trim', explode("\n", $raw)));

        foreach ($lines as $line) {
            $colonPos = strpos($line, ':');

            if ($colonPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $colonPos));
            $value = trim(substr($line, $colonPos + 1));

            if (!empty($key)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get(self::SETTING_ENABLED, false);
    }

    public static function getKey(): string
    {
        return 'webhook';
    }
}
