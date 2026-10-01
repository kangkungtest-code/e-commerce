<?php

namespace App\Http\Controllers\Toko;

use App\Actions\Pembayaran\KonfirmasiPembayaranAction;
use App\Actions\Pembayaran\MulaiPembayaranAction;
use App\Exceptions\TokoException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Payments\GatewayException;
use App\Payments\PayPalGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PembayaranController extends Controller
{
    public function bayar(Request $request, Order $order, MulaiPembayaranAction $mulai): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'metode' => ['required', Rule::in([Payment::GATEWAY_PAYPAL, Payment::GATEWAY_QRIS, Payment::GATEWAY_VA])],
            'bank' => ['required_if:metode,'.Payment::GATEWAY_VA, 'nullable', Rule::in(config('services.xendit.va_banks'))],
        ]);

        try {
            $payment = $mulai->execute($order, $data['metode'], ['bank' => $data['bank'] ?? null]);
        } catch (TokoException $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }

        return $payment->url_bayar
            ? redirect()->away($payment->url_bayar)
            : redirect()->route('akun.pesanan.show', $order);
    }

    /** Pembeli kembali dari PayPal setelah menyetujui pembayaran. */
    public function paypalKembali(Request $request, Payment $payment, PayPalGateway $paypal, KonfirmasiPembayaranAction $konfirmasi): RedirectResponse
    {
        $order = $payment->order;
        abort_unless($order->user_id === $request->user()->id, 404);

        if ($payment->status === Payment::PENDING) {
            try {
                $konfirmasi->execute($paypal->capture($payment));
            } catch (GatewayException $e) {
                Log::error("Capture PayPal {$payment->id} gagal: ".$e->getMessage());

                return redirect()->route('akun.pesanan.show', $order)
                    ->withErrors(['order' => __('PayPal could not complete the payment. You have not been charged; please try again.')]);
            }
        }

        $payment->refresh();

        $kembali = redirect()->route('akun.pesanan.show', $order);

        return $payment->status === Payment::BERHASIL
            ? $kembali->with('status', __('Payment received. Thank you!'))
            : $kembali->withErrors(['order' => __('The payment was not completed.')]);
    }

    public function paypalBatal(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->order->user_id === $request->user()->id, 404);

        return redirect()->route('akun.pesanan.show', $payment->order)
            ->with('status', __('Payment cancelled. You can choose a payment method again.'));
    }

    /** Dipakai halaman order untuk mengecek apakah pembayaran QRIS/VA sudah masuk. */
    public function status(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return response()->json(['status' => $order->status]);
    }
}
