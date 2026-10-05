<?php

/*
|--------------------------------------------------------------------------
| Profil toko
|--------------------------------------------------------------------------
| Semua yang membedakan satu toko dengan toko lain yang memakai kode yang
| sama ada di folder toko/ (dan public/toko/ untuk logo). Branch toko lain
| (mis. toko-fastandflux) hanya mengubah folder ini, jadi update dari
| claude-dev bisa di-merge tanpa bentrok.
|
| ATURAN: di branch claude-dev, file di folder toko/ jangan diubah lagi
| kecuali menambah kunci baru yang punya nilai bawaan di config/toko.php.
*/

return [
    // Nama toko di header, judul halaman, email, invoice Xendit, dll.
    'nama' => 'Kangkung Apparel',

    // Awalan nomor pesanan, mis. KA-20261005-0001.
    'prefix_order' => 'KA',

    // Logo di header & panel admin (path di dalam public/). null = tulisan nama toko saja.
    'logo' => null,

    // Warna storefront (variabel CSS di public/css/toko.css). Kosong = warna bawaan.
    'tema' => [],

    // Warna utama panel admin (hex). null = amber bawaan Filament.
    'warna_admin' => null,

    // Teks besar di beranda per bahasa. null = teks bawaan.
    'beranda' => null,

    // Data katalog awal (relatif ke folder toko/), null = katalog contoh bawaan
    // database/data/katalog-demo.php dengan foto siluet.
    'katalog' => null,
];
