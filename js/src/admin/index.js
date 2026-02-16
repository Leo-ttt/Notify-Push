import app from 'flarum/admin/app';

app.initializers.add('leo-t-notify-push', () => {
  app.extensionData
    .for('leo-t-notify-push')

    // ========== General Settings ==========
    .registerSetting({
      setting: 'leo-t-notify-push.push_locale',
      label: app.translator.trans('leo-t-notify-push.admin.push_locale_label'),
      type: 'select',
      options: {
        'en': 'English',
        'zh-hans': '中文',
      },
      default: 'en',
    }, 200)
    .registerSetting({
      setting: 'leo-t-notify-push.push_timezone',
      label: app.translator.trans('leo-t-notify-push.admin.push_timezone_label'),
      help: app.translator.trans('leo-t-notify-push.admin.push_timezone_help'),
      type: 'text',
      placeholder: 'America/New_York',
    }, 199)
    .registerSetting({
      setting: 'leo-t-notify-push.skip_admin_mod',
      label: app.translator.trans('leo-t-notify-push.admin.skip_admin_mod_label'),
      help: app.translator.trans('leo-t-notify-push.admin.skip_admin_mod_help'),
      type: 'boolean',
    }, 198)

    // ========== WeCom ==========
    .registerSetting({
      setting: 'leo-t-notify-push.wecom_enabled',
      label: app.translator.trans('leo-t-notify-push.admin.wecom_enabled_label'),
      type: 'boolean',
    }, 100)
    .registerSetting({
      setting: 'leo-t-notify-push.wecom_webhook_url',
      label: app.translator.trans('leo-t-notify-push.admin.wecom_webhook_url_label'),
      help: app.translator.trans('leo-t-notify-push.admin.wecom_webhook_url_help'),
      type: 'url',
      placeholder: 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=xxx',
    }, 99)

    // ========== DingTalk ==========
    .registerSetting({
      setting: 'leo-t-notify-push.dingtalk_enabled',
      label: app.translator.trans('leo-t-notify-push.admin.dingtalk_enabled_label'),
      type: 'boolean',
    }, 90)
    .registerSetting({
      setting: 'leo-t-notify-push.dingtalk_webhook_url',
      label: app.translator.trans('leo-t-notify-push.admin.dingtalk_webhook_url_label'),
      help: app.translator.trans('leo-t-notify-push.admin.dingtalk_webhook_url_help'),
      type: 'url',
      placeholder: 'https://oapi.dingtalk.com/robot/send?access_token=xxx',
    }, 89)
    .registerSetting({
      setting: 'leo-t-notify-push.dingtalk_secret',
      label: app.translator.trans('leo-t-notify-push.admin.dingtalk_secret_label'),
      help: app.translator.trans('leo-t-notify-push.admin.dingtalk_secret_help'),
      type: 'text',
      placeholder: 'SECxxx',
    }, 88)

    // ========== ServerChan ==========
    .registerSetting({
      setting: 'leo-t-notify-push.serverchan_enabled',
      label: app.translator.trans('leo-t-notify-push.admin.serverchan_enabled_label'),
      type: 'boolean',
    }, 80)
    .registerSetting({
      setting: 'leo-t-notify-push.serverchan_send_key',
      label: app.translator.trans('leo-t-notify-push.admin.serverchan_send_key_label'),
      help: app.translator.trans('leo-t-notify-push.admin.serverchan_send_key_help'),
      type: 'text',
      placeholder: 'SCTxxx',
    }, 79)

    // ========== Email ==========
    .registerSetting({
      setting: 'leo-t-notify-push.email_enabled',
      label: app.translator.trans('leo-t-notify-push.admin.email_enabled_label'),
      type: 'boolean',
    }, 70)
    .registerSetting({
      setting: 'leo-t-notify-push.email_recipients',
      label: app.translator.trans('leo-t-notify-push.admin.email_recipients_label'),
      help: app.translator.trans('leo-t-notify-push.admin.email_recipients_help'),
      type: 'text',
      placeholder: 'admin@example.com, manager@example.com',
    }, 69)

    // ========== Webhook ==========
    .registerSetting({
      setting: 'leo-t-notify-push.webhook_enabled',
      label: app.translator.trans('leo-t-notify-push.admin.webhook_enabled_label'),
      type: 'boolean',
    }, 60)
    .registerSetting({
      setting: 'leo-t-notify-push.webhook_url',
      label: app.translator.trans('leo-t-notify-push.admin.webhook_url_label'),
      help: app.translator.trans('leo-t-notify-push.admin.webhook_url_help'),
      type: 'url',
      placeholder: 'https://your-server.com/webhook',
    }, 59)
    .registerSetting({
      setting: 'leo-t-notify-push.webhook_method',
      label: app.translator.trans('leo-t-notify-push.admin.webhook_method_label'),
      type: 'select',
      options: {
        'POST': 'POST',
        'PUT': 'PUT',
      },
      default: 'POST',
    }, 58)
    .registerSetting({
      setting: 'leo-t-notify-push.webhook_headers',
      label: app.translator.trans('leo-t-notify-push.admin.webhook_headers_label'),
      help: app.translator.trans('leo-t-notify-push.admin.webhook_headers_help'),
      type: 'text',
      placeholder: 'Authorization: Bearer xxx',
    }, 57);
});
