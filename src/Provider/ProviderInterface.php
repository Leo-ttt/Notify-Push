<?php

namespace LeoT\NotifyPush\Provider;

interface ProviderInterface
{
    /**
     * Send a push notification.
     *
     * @param string $title   Notification title
     * @param string $body    Notification body (markdown supported)
     * @param string $url     Link to the relevant forum page
     * @return bool           Whether the push was successful
     */
    public function send(string $title, string $body, string $url): bool;

    /**
     * Check if this provider is enabled and properly configured.
     */
    public function isEnabled(): bool;

    /**
     * Get the provider identifier.
     */
    public static function getKey(): string;
}
