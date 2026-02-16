<?php

namespace LeoT\NotifyPush\Service;

use Flarum\Settings\SettingsRepositoryInterface;
use LeoT\NotifyPush\Provider\ProviderInterface;
use Psr\Log\LoggerInterface;

class PushService
{
    /** @var ProviderInterface[] */
    protected array $providers;
    protected SettingsRepositoryInterface $settings;
    protected LoggerInterface $logger;

    /**
     * @param ProviderInterface[] $providers
     */
    public function __construct(
        array $providers,
        SettingsRepositoryInterface $settings,
        LoggerInterface $logger
    ) {
        $this->providers = $providers;
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * Dispatch notification to all enabled providers.
     */
    public function push(string $title, string $body, string $url): void
    {
        foreach ($this->providers as $provider) {
            if (!$provider->isEnabled()) {
                continue;
            }

            $key = $provider::getKey();

            try {
                $success = $provider->send($title, $body, $url);

                if (!$success) {
                    $this->logger->warning("[NotifyPush] Provider [{$key}] returned false.");
                }
            } catch (\Throwable $e) {
                $this->logger->error("[NotifyPush] Provider [{$key}] exception: " . $e->getMessage());
            }
        }
    }

    /**
     * Build the full forum URL for a given path.
     */
    public function forumUrl(string $path = ''): string
    {
        $baseUrl = rtrim($this->settings->get('url', ''), '/');

        if (empty($path)) {
            return $baseUrl;
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }
}
