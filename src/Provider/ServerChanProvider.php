<?php

namespace LeoT\NotifyPush\Provider;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class ServerChanProvider implements ProviderInterface
{
    private const SETTING_ENABLED = 'leo-t-notify-push.serverchan_enabled';
    private const SETTING_SEND_KEY = 'leo-t-notify-push.serverchan_send_key';
    private const API_BASE_URL = 'https://sctapi.ftqq.com/';

    protected SettingsRepositoryInterface $settings;
    protected LoggerInterface $logger;

    public function __construct(SettingsRepositoryInterface $settings, LoggerInterface $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;
    }

    public function send(string $title, string $body, string $url): bool
    {
        $sendKey = $this->settings->get(self::SETTING_SEND_KEY, '');

        if (empty($sendKey)) {
            return false;
        }

        $desp = "{$body}\n\n[查看详情]({$url})";
        $apiUrl = self::API_BASE_URL . $sendKey . '.send';

        try {
            $client = new Client(['timeout' => 10]);
            $response = $client->post($apiUrl, [
                'json' => [
                    'title' => $title,
                    'desp' => $desp,
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Throwable $e) {
            $this->logger->error('[NotifyPush] ServerChan send failed: ' . $e->getMessage());
            return false;
        }
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get(self::SETTING_ENABLED, false);
    }

    public static function getKey(): string
    {
        return 'serverchan';
    }
}
