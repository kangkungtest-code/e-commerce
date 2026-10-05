<?php

/*
 * Katalog awal Fast & Flux. Format sama dengan database/data/katalog-demo.php,
 * plus 'foto' => [kode motif => file di toko/foto/].
 * Isi contoh (nama, harga, stok) — ubah lewat panel admin setelah deploy.
 * Seeder hanya menambah produk yang belum ada, jadi perubahan di panel tidak tertimpa.
 *
 * Kurs, ongkir & FAQ ikut bawaan katalog demo.
 */

$bawaan = require __DIR__.'/../database/data/katalog-demo.php';

// Motif dipakai sebagai opsi "Warna": [nama id, nama en, nama zh_TW, hex cadangan].
$motif = [
    'tropical-sky' => ['Biru Langit Tropis', 'Tropical Sky', '熱帶天空藍', '#2aa7e0'],
    'hibiscus-royal' => ['Biru Royal Hibiscus', 'Royal Hibiscus', '寶藍扶桑花', '#1f3fa8'],
    'pastel-palm' => ['Pastel Daun Palem', 'Pastel Palm', '粉彩棕櫚', '#9fc3ec'],
    'island-night' => ['Navy Island', 'Island Night', '海島夜藍', '#1b2340'],
    'pineapple-breeze' => ['Biru Nanas', 'Pineapple Breeze', '鳳梨淺藍', '#5aaee0'],
    'red-lily' => ['Merah Lily', 'Red Lily', '紅百合', '#b3202a'],
    'charcoal' => ['Charcoal Washed', 'Washed Charcoal', '水洗炭灰', '#3a3a3a'],
];

$kemeja = fn (string $kode, string $m, array $nama, array $deskripsi, int $harga) => [
    'kode' => $kode,
    'kategori' => 'Kemeja Hawaii',
    'nama' => $nama,
    'deskripsi' => $deskripsi,
    'harga' => $harga, 'berat' => 220,
    'ukuran' => ['S', 'M', 'L', 'XL'],
    'warna' => [$m],
    'foto' => [$m => "{$m}.jpg"],
];

return [
    'warna' => $motif,
    'kategori' => [
        'Kemeja Hawaii' => ['id' => 'Kemeja Hawaii', 'en' => 'Hawaiian Shirts', 'zh_TW' => '夏威夷襯衫'],
        'Kaos' => ['id' => 'Kaos', 'en' => 'T-shirts', 'zh_TW' => 'T恤'],
    ],
    'produk' => [
        $kemeja('FF-TROPICAL', 'tropical-sky',
            ['id' => 'Kemeja Hawaii Tropical Sky', 'en' => 'Tropical Sky Hawaiian Shirt', 'zh_TW' => '熱帶天空夏威夷襯衫'],
            [
                'id' => 'Biru langit cerah dengan bunga dan daun tropis. Kerah camp, kancing depan, satu saku dada. Bahan ringan dan adem untuk hari panas.',
                'en' => 'Bright sky blue with tropical flowers and leaves. Camp collar, button front and one chest pocket. Light, airy fabric for hot days.',
                'zh_TW' => '明亮天空藍搭配熱帶花葉印花。古巴領、前開扣、單胸袋，布料輕盈透氣，適合炎熱天氣。',
            ], 199000),
        $kemeja('FF-HIBISCUS', 'hibiscus-royal',
            ['id' => 'Kemeja Hawaii Hibiscus Royal', 'en' => 'Royal Hibiscus Hawaiian Shirt', 'zh_TW' => '寶藍扶桑花夏威夷襯衫'],
            [
                'id' => 'Motif hibiscus putih klasik di atas biru royal. Potongan regular, nyaman dipakai santai maupun ke acara pantai.',
                'en' => 'Classic white hibiscus on royal blue. Regular fit that works for lazy weekends and beach parties alike.',
                'zh_TW' => '經典白色扶桑花印在寶藍底色上。標準版型，週末休閒或海灘派對都適合。',
            ], 189000),
        $kemeja('FF-PASTEL', 'pastel-palm',
            ['id' => 'Kemeja Hawaii Pastel Palm', 'en' => 'Pastel Palm Hawaiian Shirt', 'zh_TW' => '粉彩棕櫚夏威夷襯衫'],
            [
                'id' => 'Daun palem warna pastel biru dan pink. Kerah camp yang santai, cocok dipadukan dengan celana pendek atau chino.',
                'en' => 'Palm leaves in soft pastel blue and pink. Relaxed camp collar that pairs easily with shorts or chinos.',
                'zh_TW' => '粉藍與粉紅色調的棕櫚葉印花。輕鬆的古巴領，搭配短褲或卡其褲都好看。',
            ], 209000),
        $kemeja('FF-ISLAND', 'island-night',
            ['id' => 'Kemeja Hawaii Island Night', 'en' => 'Island Night Hawaiian Shirt', 'zh_TW' => '海島夜藍夏威夷襯衫'],
            [
                'id' => 'Navy gelap dengan ikon pulau: nanas, ukulele, penyu, dan bunga. Lebih kalem untuk dipakai malam hari.',
                'en' => 'Deep navy scattered with island icons — pineapples, ukuleles, turtles and flowers. A calmer pick for evenings out.',
                'zh_TW' => '深海軍藍底，點綴鳳梨、烏克麗麗、海龜與花朵等海島圖案，晚間外出更顯沉穩。',
            ], 219000),
        $kemeja('FF-PINEAPPLE', 'pineapple-breeze',
            ['id' => 'Kemeja Hawaii Pineapple Breeze', 'en' => 'Pineapple Breeze Hawaiian Shirt', 'zh_TW' => '鳳梨微風夏威夷襯衫'],
            [
                'id' => 'Motif nanas putih di atas biru muda. Ringan, tidak menerawang, dan gampang dipadukan.',
                'en' => 'White pineapples on light blue. Lightweight without being see-through, and easy to style.',
                'zh_TW' => '淺藍底白色鳳梨印花。輕薄但不透，好搭配。',
            ], 199000),
        $kemeja('FF-REDLILY', 'red-lily',
            ['id' => 'Kemeja Hawaii Red Lily', 'en' => 'Red Lily Hawaiian Shirt', 'zh_TW' => '紅百合夏威夷襯衫'],
            [
                'id' => 'Bunga lily merah besar dengan daun hijau tua di atas dasar gelap. Paling mencolok di koleksi ini.',
                'en' => 'Oversized red lilies and dark green leaves on a deep base. The boldest print in the collection.',
                'zh_TW' => '深色底上的大朵紅百合與墨綠葉片，是本系列最搶眼的印花。',
            ], 219000),
        [
            'kode' => 'FF-BULLY',
            'kategori' => 'Kaos',
            'nama' => ['id' => 'Kaos Bully Washed', 'en' => 'Bully Washed Tee', 'zh_TW' => 'Bully 水洗T恤'],
            'deskripsi' => [
                'id' => 'Kaos oversize warna charcoal dengan efek washed dan sablon karakter "BULLY" di depan. Katun tebal, bahu turun.',
                'en' => 'Oversized washed-charcoal tee with a "BULLY" character print on the front. Heavy cotton, dropped shoulders.',
                'zh_TW' => '水洗炭灰色寬鬆T恤，正面印有「BULLY」角色圖案。厚磅純棉，落肩剪裁。',
            ],
            'harga' => 149000, 'berat' => 240,
            'ukuran' => ['M', 'L', 'XL'],
            'warna' => ['charcoal'],
            'foto' => ['charcoal' => 'bully-tee.jpg'],
        ],
    ],
    'kurs' => $bawaan['kurs'],
    'faq' => $bawaan['faq'],
];
