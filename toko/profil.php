<?php

/*
|--------------------------------------------------------------------------
| Profil toko — Fast & Flux (branch toko-fastandflux)
|--------------------------------------------------------------------------
| Branch ini = kode claude-dev + folder toko/ & public/toko/ saja.
| Ambil update: git merge claude-dev (folder ini tidak disentuh claude-dev).
*/

return [
    'nama' => 'Fast & Flux',

    'prefix_order' => 'FF',

    'logo' => 'toko/logo.png',

    // Hitam-merah sesuai logo.
    'tema' => [
        '--ink' => '#141414',
        '--ink-soft' => '#555555',
        '--paper' => '#f7f6f4',
        '--kangkung' => '#c8102e',
        '--kangkung-dark' => '#9a0c23',
        '--sage' => '#f6e3e5',
        '--line' => '#d8d4d0',
    ],

    'warna_admin' => '#c8102e',

    'beranda' => [
        'judul' => [
            'en' => 'Loud prints. Light fabric. Made for the heat.',
            'id' => 'Motif berani, bahan ringan, siap untuk cuaca panas.',
            'zh_TW' => '大膽印花，輕盈布料，為炎夏而生。',
        ],
        'teks' => [
            'en' => 'Hawaiian shirts and graphic tees by Fast & Flux. Ships from Indonesia to Indonesia and Taiwan.',
            'id' => 'Kemeja Hawaii dan kaos grafis dari Fast & Flux. Dikirim dari Indonesia ke seluruh Indonesia dan Taiwan.',
            'zh_TW' => 'Fast & Flux 的夏威夷襯衫與圖案T恤。自印尼出貨，寄送印尼與台灣。',
        ],
    ],

    'katalog' => 'katalog.php',
];
