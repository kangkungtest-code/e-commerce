<?php

namespace App\Actions\Order;

use App\Exceptions\TokoException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/** Pembeli membatalkan order yang belum dibayar; stok yang di-reserve dilepas. */
class BatalkanOrderAction
{
    public function __construct(private LepasReservasiAction $lepas) {}

    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($order->status !== Order::STATUS_MENUNGGU_PEMBAYARAN) {
                throw new TokoException(__('This order can no longer be cancelled.'));
            }

            $this->lepas->execute($order);
            $order->update(['status' => Order::STATUS_DIBATALKAN]);

            return $order;
        });
    }
}
