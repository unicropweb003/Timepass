<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/admin/auth/google/callback'),
    ],

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    // Secret bearer token that protects the MCP server (set MCP_TOKEN in the
    // environment). AI clients must send: Authorization: Bearer <MCP_TOKEN>.
    'mcp' => [
        'token' => env('MCP_TOKEN'),
    ],

    // AI image generation for MCP tools. Google Gemini image models are used
    // when GEMINI_API_KEY is set (Imagen was retired by Google); otherwise the
    // free Pollinations service (about 1 image / 45 s, small watermark).
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-3.1-flash-lite-image'),
    ],

    'pollinations' => [
        'token' => env('POLLINATIONS_TOKEN'),
    ],

];
