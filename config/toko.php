<?php

return [
    /* Nama toko di storefront. */
    'nama' => env('TOKO_NAMA', 'Kangkung Apparel'),

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

    /* Nama negara (kode ISO alpha-2). Negara yang bisa dipilih = negara di zona ongkir aktif. */
    'negara' => [
        'ID' => 'Indonesia',
        'TW' => 'Taiwan',
        'SG' => 'Singapore',
        'MY' => 'Malaysia',
        'HK' => 'Hong Kong',
        'JP' => 'Japan',
        'US' => 'United States',
        'AU' => 'Australia',
    ],

    /* Zona waktu tampilan untuk pembeli (database tetap UTC). WITA = UTC+8, sama dengan Taiwan. */
    'zona_waktu' => env('TOKO_ZONA_WAKTU', 'Asia/Makassar'),

    'order' => [
        'prefix_nomor' => env('TOKO_PREFIX_ORDER', 'KA'),
        // Batas bayar sejak order dibuat; lewat dari ini order kadaluarsa dan stok dilepas.
        'batas_bayar_jam' => (int) env('TOKO_BATAS_BAYAR_JAM', 24),
        'maks_qty_per_item' => 20,
    ],
];
