<?php

namespace App\Http\Controllers;

use App\Actions\Pembayaran\KonfirmasiPembayaranAction;
use App\Models\Payment;
use App\Payments\MetodePembayaran;
use App\Payments\WebhookTidakSah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Notifikasi dari gateway. Endpoint terpisah per gateway, masing-masing
 * memverifikasi tanda tangan / token sendiri sebelum data dipakai.
 */
class WebhookPembayaranController extends Controller
{
    public function paypal(Request $request, KonfirmasiPembayaranAction $konfirmasi): JsonResponse
    {
        return $this->proses($request, Payment::GATEWAY_PAYPAL, $konfirmasi);
    }

    public function xendit(Request $request, KonfirmasiPembayaranAction $konfirmasi): JsonResponse
    {
        // QRIS & VA memakai endpoint dan token yang sama.
        return $this->proses($request, Payment::GATEWAY_QRIS, $konfirmasi);
    }

    private function proses(Request $request, string $kode, KonfirmasiPembayaranAction $konfirmasi): JsonResponse
    {
        $gateway = MetodePembayaran::gateway($kode) ?? abort(404);

        try {
            $hasil = $gateway->bacaWebhook($request);
        } catch (WebhookTidakSah $e) {
            Log::warning("Webhook {$kode} ditolak: ".$e->getMessage(), ['ip' => $request->ip()]);

            return response()->json(['ok' => false], 401);
        }

        $konfirmasi->execute($hasil);

        return response()->json(['ok' => true]);
    }
}
