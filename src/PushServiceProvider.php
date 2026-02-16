<?php

namespace LeoT\NotifyPush;

use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Mail\Mailer;
use LeoT\NotifyPush\Provider\DingTalkProvider;
use LeoT\NotifyPush\Provider\EmailProvider;
use LeoT\NotifyPush\Provider\ServerChanProvider;
use LeoT\NotifyPush\Provider\WebhookProvider;
use LeoT\NotifyPush\Provider\WeComProvider;
use LeoT\NotifyPush\Service\PushService;
use Psr\Log\LoggerInterface;

class PushServiceProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(PushService::class, function ($app) {
            $settings = $app->make(SettingsRepositoryInterface::class);
            $logger = $app->make(LoggerInterface::class);
            $mailer = $app->make(Mailer::class);

            $providers = [
                new WeComProvider($settings, $logger),
                new DingTalkProvider($settings, $logger),
                new ServerChanProvider($settings, $logger),
                new EmailProvider($settings, $mailer, $logger),
                new WebhookProvider($settings, $logger),
            ];

            return new PushService($providers, $settings, $logger);
        });
    }
}
