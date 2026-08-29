<?php

namespace LeoT\NotifyPush\Listener;

use Flarum\Discussion\Event\Started as DiscussionStarted;
use Flarum\Group\Group;
use Flarum\Post\Event\Posted as PostCreated;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Event\Registered as UserRegistered;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;
use LeoT\NotifyPush\Service\PushService;
use Symfony\Contracts\Translation\TranslatorInterface;

class PushEventSubscriber
{
    private const FIRST_POST_NUMBER = 1;
    private const SETTING_SKIP_ADMIN_MOD = 'leo-t-notify-push.skip_admin_mod';
    private const SETTING_PUSH_LOCALE = 'leo-t-notify-push.push_locale';
    private const SETTING_PUSH_TIMEZONE = 'leo-t-notify-push.push_timezone';
    private const BEIJING_TIMEZONE = 'Asia/Shanghai';
    private const DEFAULT_LOCALE = 'en';
    private const TIME_FORMAT = 'Y-m-d H:i:s';
    private const LINE_BREAK = "\n\n";

    protected PushService $pushService;
    protected TranslatorInterface $translator;
    protected SettingsRepositoryInterface $settings;

    public function __construct(
        PushService $pushService,
        TranslatorInterface $translator,
        SettingsRepositoryInterface $settings
    ) {
        $this->pushService = $pushService;
        $this->translator = $translator;
        $this->settings = $settings;
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(UserRegistered::class, [$this, 'handleUserRegistered']);
        $events->listen(DiscussionStarted::class, [$this, 'handleDiscussionStarted']);
        $events->listen(PostCreated::class, [$this, 'handlePostCreated']);
    }

    public function handleUserRegistered(UserRegistered $event): void
    {
        $user = $event->user;
        $username = $user->display_name ?? $user->username ?? 'unknown';
        $email = $user->email ?? '';
        $l = $this->getPushLocale();

        $title = $this->t('leo-t-notify-push.push.user_registered_title', $l);
        $body = $this->buildUserRegisteredBody($username, $email, $user->joined_at, $l);
        $url = $this->pushService->forumUrl();

        $this->pushService->push($title, $body, $url);
    }

    public function handleDiscussionStarted(DiscussionStarted $event): void
    {
        $actor = $event->actor;

        if ($this->shouldSkipPrivilegedUser($actor)) {
            return;
        }

        $discussion = $event->discussion;
        $username = $actor->display_name ?? $actor->username ?? 'unknown';
        $l = $this->getPushLocale();
        $content = $this->extractPostContent($discussion);

        $title = $this->t('leo-t-notify-push.push.discussion_started_title', $l);
        $body = $this->buildDiscussionBody($username, $discussion->title ?? '', $content, $discussion->created_at, $l);
        $url = $this->resolveModelUrl($discussion, 'd/' . $discussion->id);

        $this->pushService->push($title, $body, $url);
    }

    public function handlePostCreated(PostCreated $event): void
    {
        $post = $event->post;
        $actor = $event->actor;

        if ($post->number <= self::FIRST_POST_NUMBER) {
            return;
        }

        if ($this->shouldSkipPrivilegedUser($actor)) {
            return;
        }

        $discussion = $post->discussion;

        if ($discussion === null) {
            return;
        }

        $username = $actor !== null
            ? ($actor->display_name ?? $actor->username ?? 'unknown')
            : 'unknown';

        $l = $this->getPushLocale();
        $content = $this->stripToPlainText($post->content ?? '');

        $title = $this->t('leo-t-notify-push.push.post_created_title', $l);
        $body = $this->buildReplyBody($username, $discussion->title ?? '', $content, $post->created_at, $l);
        $url = $this->resolveModelUrl($post, 'd/' . $discussion->id . '/' . $post->number);

        $this->pushService->push($title, $body, $url);
    }

    // ==================== Body Builders ====================

    private function buildUserRegisteredBody(string $username, string $email, ?\DateTimeInterface $time, string $l): string
    {
        $lb = self::LINE_BREAK;

        return '**' . $this->t('leo-t-notify-push.push.label_username', $l) . '：** ' . $username . $lb
             . '**' . $this->t('leo-t-notify-push.push.label_email', $l) . '：** ' . $email . $lb
             . '**' . $this->t('leo-t-notify-push.push.label_registered_at', $l) . '：**' . $lb
             . $this->formatTimeLines($time, $l);
    }

    private function buildDiscussionBody(string $username, string $discussionTitle, string $content, ?\DateTimeInterface $time, string $l): string
    {
        $lb = self::LINE_BREAK;

        return '**' . $this->t('leo-t-notify-push.push.label_author', $l) . '：** ' . $username . $lb
             . '**' . $this->t('leo-t-notify-push.push.label_topic', $l) . '：** ' . $discussionTitle . $lb
             . '**' . $this->t('leo-t-notify-push.push.label_content', $l) . '：** ' . $content . $lb
             . '**' . $this->t('leo-t-notify-push.push.label_posted_at', $l) . '：**' . $lb
             . $this->formatTimeLines($time, $l);
    }

    private function buildReplyBody(string $username, string $discussionTitle, string $content, ?\DateTimeInterface $time, string $l): string
    {
        $lb = self::LINE_BREAK;

        return '**' . $this->t('leo-t-notify-push.push.label_author', $l) . '：** ' . $username . $lb
             . '**' . $this->t('leo-t-notify-push.push.label_topic', $l) . '：** ' . $discussionTitle . $lb
             . '**' . $this->t('leo-t-notify-push.push.label_content', $l) . '：** ' . $content . $lb
             . '**' . $this->t('leo-t-notify-push.push.label_replied_at', $l) . '：**' . $lb
             . $this->formatTimeLines($time, $l);
    }

    // ==================== Helpers ====================

    private function shouldSkipPrivilegedUser(?User $actor): bool
    {
        if ($actor === null) {
            return false;
        }

        $skipEnabled = (bool) $this->settings->get(self::SETTING_SKIP_ADMIN_MOD, false);

        if (!$skipEnabled) {
            return false;
        }

        $groupIds = $actor->groups->pluck('id')->all();

        return in_array(Group::ADMINISTRATOR_ID, $groupIds, true)
            || in_array(Group::MODERATOR_ID, $groupIds, true);
    }

    private function extractPostContent($discussion): string
    {
        $firstPost = $discussion->firstPost;

        if ($firstPost === null) {
            return '';
        }

        return $this->stripToPlainText($firstPost->content ?? '');
    }

    private function stripToPlainText(string $raw): string
    {
        $text = strip_tags($raw);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($text);
    }

    private function formatTimeLines(?\DateTimeInterface $dateTime, string $locale): string
    {
        if ($dateTime === null) {
            return '';
        }

        $carbon = \Carbon\Carbon::instance($dateTime);
        $beijingLabel = $this->t('leo-t-notify-push.push.time_beijing', $locale);
        $beijingTime = $carbon->copy()->setTimezone(self::BEIJING_TIMEZONE)->format(self::TIME_FORMAT);
        $lines = '🕐 ' . $beijingLabel . '：' . $beijingTime;

        $userTimezone = trim($this->settings->get(self::SETTING_PUSH_TIMEZONE, ''));

        if (!empty($userTimezone)) {
            try {
                $localLabel = $this->t('leo-t-notify-push.push.time_local', $locale);
                $localTime = $carbon->copy()->setTimezone($userTimezone)->format(self::TIME_FORMAT);
                $lines .= self::LINE_BREAK . '🕐 ' . $localLabel . '：' . $localTime;
            } catch (\Throwable $e) {
                // Invalid timezone, skip
            }
        }

        return $lines;
    }

    private function getPushLocale(): string
    {
        return $this->settings->get(self::SETTING_PUSH_LOCALE, self::DEFAULT_LOCALE) ?: self::DEFAULT_LOCALE;
    }

    private function t(string $id, string $locale): string
    {
        return $this->translator->trans($id, [], 'messages', $locale);
    }

    /**
     * Use Flarum's canonical model URL so links include the discussion slug
     * and the correct post anchor. Keep the legacy path as a fallback for
     * older model implementations.
     */
    private function resolveModelUrl(object $model, string $fallbackPath): string
    {
        if (method_exists($model, 'getUrl')) {
            $url = trim((string) $model->getUrl());

            if ($url !== '') {
                if (preg_match('#^https?://#i', $url) === 1) {
                    return $url;
                }

                return $this->pushService->forumUrl($url);
            }
        }

        return $this->pushService->forumUrl($fallbackPath);
    }
}
