<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;

/** Data order untuk halaman akun pembeli. Nominal ditampilkan dalam mata uang order (snapshot). */
class TampilanOrder
{
    public static function labelStatus(string $status): string
    {
        return match ($status) {
            Order::STATUS_MENUNGGU_PEMBAYARAN => __('Awaiting payment'),
            Order::STATUS_DIBAYAR => __('Paid'),
            Order::STATUS_DIPROSES => __('Being packed'),
            Order::STATUS_DIKIRIM => __('Shipped'),
            Order::STATUS_SELESAI => __('Completed'),
            Order::STATUS_KADALUARSA => __('Expired'),
            Order::STATUS_DIBATALKAN => __('Cancelled'),
            default => $status,
        };
    }

    private static function uang(Order $o, float $nilai): string
    {
        return app(Kurs::class)->formatNilai($nilai, $o->mata_uang);
    }

    public static function ringkas(Order $o): array
    {
        return [
            'nomor' => $o->nomor,
            'url' => route('akun.pesanan.show', $o),
            'tanggal' => $o->created_at->timezone(config('toko.zona_waktu'))->locale(str_replace('_', '-', app()->getLocale()))->isoFormat('D MMM YYYY'),
            'status' => $o->status,
            'label_status' => self::labelStatus($o->status),
            'total' => self::uang($o, (float) $o->total),
        ];
    }

    public static function detail(Order $o): array
    {
        $o->loadMissing('items.variant.product.images', 'statusHistories');
        $kurs = app(Kurs::class);
        $locale = app()->getLocale();

        return self::ringkas($o) + [
            'items' => $o->items->map(function (OrderItem $i) use ($o, $kurs, $locale) {
                $hargaSatuan = $kurs->konversiDenganRate((float) $i->harga_saat_itu, (float) $o->kurs_terpakai, $o->mata_uang);

                return [
                    'nama' => $i->variant?->product?->getTranslation('nama_terjemahan', $locale) ?? '—',
                    'opsi' => collect($i->variant?->opsi ?? [])->map(fn ($n, $k) => __($k).': '.__($n))->values()->implode(', '),
                    'foto' => $i->variant?->product?->images->first()?->thumbUrl(),
                    'qty' => $i->qty,
                    'harga' => self::uang($o, $hargaSatuan),
                    'total' => self::uang($o, $hargaSatuan * $i->qty),
                ];
            })->all(),
            'subtotal' => self::uang($o, (float) $o->subtotal),
            'ongkir' => self::uang($o, (float) $o->ongkir),
            'mata_uang' => $o->mata_uang,
            'alamat' => $o->alamat_snapshot ?? [],
            'nama_negara' => __(config('toko.negara.'.($o->alamat_snapshot['negara'] ?? ''), $o->alamat_snapshot['negara'] ?? '')),
            'berat_kg' => number_format($o->berat_gram / 1000, 2),
            'batas_bayar' => $o->kadaluarsa_pada?->timezone(config('toko.zona_waktu'))->locale(str_replace('_', '-', $locale))->isoFormat('D MMM YYYY, HH:mm').' '.$o->kadaluarsa_pada?->timezone(config('toko.zona_waktu'))->format('T'),
            'menunggu' => $o->status === Order::STATUS_MENUNGGU_PEMBAYARAN,
            'resi' => $o->resi,
            'riwayat' => $o->statusHistories->map(fn ($h) => [
                'label' => self::labelStatus($h->ke),
                'waktu' => $h->created_at->timezone(config('toko.zona_waktu'))->locale(str_replace('_', '-', $locale))->isoFormat('D MMM YYYY, HH:mm'),
            ])->all(),
            'bisa_retur' => TampilanRetur::bisaDiajukan($o),
            'batas_retur_hari' => config('toko.retur.batas_hari'),
            'retur' => ($r = $o->returnRequests()->latest()->first()) ? [
                'model' => $r,
                'label' => TampilanRetur::labelStatus($r->status),
                'status' => $r->status,
                'penjelasan' => TampilanRetur::penjelasan($r),
                'catatan_admin' => $r->catatan_admin,
                'resi_kembali' => $r->resi_kembali,
                'alamat_retur' => config('toko.retur.alamat'),
            ] : null,
        ];
    }
}
