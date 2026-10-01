<?php

namespace App\Support;

use App\Models\Pengaturan;

/** Tautan WhatsApp & LINE admin yang diisi di panel (Pengaturan → Chatbot & kontak). */
class KontakAdmin
{
    /** Nomor WA disimpan hanya angka dengan kode negara, mis. 6281234567890. */
    public static function normalisasiWa(?string $nomor): ?string
    {
        $angka = preg_replace('/\D+/', '', (string) $nomor);
        if ($angka === '') {
            return null;
        }

        // 08xx (format Indonesia) -> 628xx
        return str_starts_with($angka, '0') ? '62'.substr($angka, 1) : $angka;
    }

    public static function urlWa(?string $pesan = null): ?string
    {
        $nomor = Pengaturan::ambil('kontak.wa');

        return $nomor ? 'https://wa.me/'.$nomor.($pesan ? '?text='.rawurlencode($pesan) : '') : null;
    }

    /** ID LINE resmi diawali @ (akun bisnis); selain itu dianggap ID pribadi. */
    public static function urlLine(): ?string
    {
        $id = trim((string) Pengaturan::ambil('kontak.line'));
        if ($id === '') {
            return null;
        }

        return str_starts_with($id, '@')
            ? 'https://line.me/R/ti/p/'.rawurlencode($id)
            : 'https://line.me/R/ti/p/~'.rawurlencode($id);
    }

    /** @return array<int, array{label: string, url: string, jenis: string}> */
    public static function tautan(?string $pesan = null): array
    {
        return array_values(array_filter([
            ($u = self::urlWa($pesan)) ? ['jenis' => 'wa', 'label' => __('Chat on WhatsApp'), 'url' => $u] : null,
            ($u = self::urlLine()) ? ['jenis' => 'line', 'label' => __('Chat on LINE'), 'url' => $u] : null,
        ]));
    }
}
