<?php

use NotificationChannels\WebPush\PushSubscription;

return [

    // Clés VAPID qui signent les notifications push : « php artisan webpush:vapid » les écrit dans .env.
    // Sans clés, les notifications restent dans l'appli (cloche) et le push est désactivé.
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', env('APP_URL')),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'pem_file' => env('VAPID_PEM_FILE'),
    ],

    'model' => PushSubscription::class,

    'table_name' => 'push_subscriptions',

    'database_connection' => env('DB_CONNECTION', 'pgsql'),

    'client_options' => ['timeout' => 5],

    'automatic_padding' => true,

];
