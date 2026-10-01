<?php

return [
    /*
    | Bahasa yang didukung untuk konten (produk, FAQ) dan UI.
    | Urutan = urutan tab di panel admin. Locale pertama = default storefront.
    */
    'locales' => [
        'en' => 'English',
        'id' => 'Bahasa Indonesia',
        'zh_TW' => '繁體中文',
    ],

    /* Locale yang wajib diisi untuk nama produk. */
    'required_locales' => ['en', 'id'],

    'currencies' => ['USD', 'IDR', 'TWD'],
    'base_currency' => 'IDR',

    'product_images' => [
        'disk' => 'public',
        'directory' => 'products',
        'max_per_product' => 8,
        'max_upload_kb' => 10240,
        'max_width' => 1600,
        'thumb_width' => 400,
        'quality' => 80,
    ],
];
