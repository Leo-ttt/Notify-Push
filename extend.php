<?php

use Flarum\Extend;
use LeoT\NotifyPush\Listener\PushEventSubscriber;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\Event())
        ->subscribe(PushEventSubscriber::class),

    (new Extend\ServiceProvider())
        ->register(\LeoT\NotifyPush\PushServiceProvider::class),
];
