<?php

use App\Actions\Order\KadaluarsakanOrderAction;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('toko:kadaluarsakan-order', function (KadaluarsakanOrderAction $action) {
    $jumlah = $action->execute();
    $this->info("{$jumlah} order kadaluarsa, stok dilepas.");
})->purpose('Tandai order yang lewat batas bayar sebagai kadaluarsa dan lepas stoknya');

// Server menjalankan `php artisan schedule:run` tiap menit lewat cron (dipasang oleh deploy.yml).
Schedule::command('toko:kadaluarsakan-order')->everyFiveMinutes()->withoutOverlapping();
