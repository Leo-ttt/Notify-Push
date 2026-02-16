# Notify Push for Flarum

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

A [Flarum](https://flarum.org) extension that pushes real-time notifications to external channels when forum events occur.

## Features

- **5 Push Channels**: WeCom (企业微信), DingTalk (钉钉), ServerChan (Server酱), Email, Generic Webhook
- **3 Event Types**: User Registration, New Discussion, New Reply
- **Multi-language Push**: Choose push message language (English / 中文) in admin panel
- **Dual Timezone**: Display both Beijing time and a configurable local timezone
- **Admin/Mod Filter**: Optionally skip notifications for admin and moderator posts
- **Markdown Formatting**: Rich message formatting for channels that support Markdown

## Requirements

- Flarum `^1.0.0`
- PHP `^8.0`

## Installation

```bash
composer require leo-t/flarum-notify-push:*
```

## Configuration

After installation, enable the extension in the Flarum admin panel, then configure:

### General Settings

| Setting | Description |
|---------|-------------|
| Push Message Language | `en` (English) or `zh-hans` (中文) |
| Local Timezone | IANA timezone name (e.g. `America/New_York`). Leave empty for Beijing time only |
| Skip Admin/Mod | When enabled, posts by admins and moderators won't trigger notifications |

### WeCom (企业微信)

| Setting | Description |
|---------|-------------|
| Enable | Toggle WeCom push on/off |
| Webhook URL | WeCom group robot webhook URL |

### DingTalk (钉钉)

| Setting | Description |
|---------|-------------|
| Enable | Toggle DingTalk push on/off |
| Webhook URL | DingTalk group robot webhook URL |
| Secret | Optional HMAC-SHA256 signing secret (starts with `SEC`) |

### ServerChan (Server酱)

| Setting | Description |
|---------|-------------|
| Enable | Toggle ServerChan push on/off |
| SendKey | Get from [sct.ftqq.com](https://sct.ftqq.com/) |

### Email

| Setting | Description |
|---------|-------------|
| Enable | Toggle email push on/off |
| Recipients | Comma-separated email addresses |

> Email uses Flarum's built-in mail driver. Make sure your forum mail settings are configured correctly.

### Generic Webhook

| Setting | Description |
|---------|-------------|
| Enable | Toggle webhook push on/off |
| URL | Target endpoint URL |
| HTTP Method | `POST` or `PUT` |
| Custom Headers | One per line, format: `Key: Value` |

Webhook JSON payload format:

```json
{
  "title": "📝 New Discussion",
  "body": "**Author:** username\n\n**Title:** ...",
  "url": "https://your-forum.com/d/123",
  "timestamp": "2026-02-16T11:00:00+08:00"
}
```

## Push Message Examples

### New User Registration

```
📢 New User Registered

Username:  john_doe
Email:  john@example.com
Registered At:
🕐 Beijing Time: 2026-02-16 11:00:00
🕐 Local Time: 2026-02-15 22:00:00
```

### New Discussion

```
📝 New Discussion

Author:  john_doe
Title:  How to set up shipping?
Content:  I'm trying to configure shipping rates for...
Posted At:
🕐 Beijing Time: 2026-02-16 11:05:00
🕐 Local Time: 2026-02-15 22:05:00
```

### New Reply

```
💬 New Reply

Author:  jane_smith
Topic:  How to set up shipping?
Content:  You can go to Settings > Shipping and...
Replied At:
🕐 Beijing Time: 2026-02-16 11:10:00
🕐 Local Time: 2026-02-15 22:10:00
```

## Updating

```bash
composer update leo-t/flarum-notify-push
php flarum cache:clear
```

## Uninstall

```bash
composer remove leo-t/flarum-notify-push
php flarum cache:clear
```

## License

MIT

## Author

Leo (Leo.lty0511@gmail.com)
