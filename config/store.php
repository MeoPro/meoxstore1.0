<?php

return [
    'admin_phone' => env('STORE_ADMIN_PHONE', '+6285933875292'),
    'admin_email' => env('STORE_ADMIN_EMAIL', 'devanoivan@gmail.com'),
    'seed_admin' => [
        'name' => env('STORE_SEED_ADMIN_NAME', 'Meoxstore Admin'),
        'email' => env('STORE_SEED_ADMIN_EMAIL'),
        'password' => env('STORE_SEED_ADMIN_PASSWORD'),
    ],
    'address' => env('STORE_ADDRESS'),
    'social_links' => [
        'facebook' => env('STORE_SOCIAL_FACEBOOK'),
        'instagram' => env('STORE_SOCIAL_INSTAGRAM'),
        'tiktok' => env('STORE_SOCIAL_TIKTOK'),
        'youtube' => env('STORE_SOCIAL_YOUTUBE'),
        'x' => env('STORE_SOCIAL_X'),
        'threads' => env('STORE_SOCIAL_THREADS'),
    ],
    'about_testimonials' => [
        'telegram_url' => env('STORE_TESTIMONIAL_TELEGRAM_URL', 'https://t.me/meofeedback'),
        'whatsapp_url' => env('STORE_TESTIMONIAL_WHATSAPP_URL', 'https://whatsapp.com/channel/0029VayXeYP6BIEf1OAxs63A'),
        'instagram_post_url' => env('STORE_TESTIMONIAL_INSTAGRAM_POST_URL', 'https://www.instagram.com/p/C9ZykuFpUfy/?cplk=eXo1bGt6aGRxeXMx'),
    ],
];
