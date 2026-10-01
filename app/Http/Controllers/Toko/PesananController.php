<?php

namespace App\Http\Controllers\Toko;

use App\Actions\Order\BatalkanOrderAction;
use App\Exceptions\TokoException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\TampilanOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->latest()->paginate(10);

        return view('toko.akun.pesanan', [
            'orders' => $orders,
            'pesanan' => $orders->getCollection()->map(fn (Order $o) => TampilanOrder::ringkas($o)),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->pastikanMilik($request, $order);

        return view('toko.akun.pesanan-detail', ['o' => TampilanOrder::detail($order)]);
    }

    public function batal(Request $request, Order $order, BatalkanOrderAction $batalkan): RedirectResponse
    {
        $this->pastikanMilik($request, $order);

        try {
            $batalkan->execute($order);
        } catch (TokoException $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }

        return back()->with('status', __('Order cancelled.'));
    }

    private function pastikanMilik(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 404);
    }
}
