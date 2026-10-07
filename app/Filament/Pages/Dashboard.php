<?php

namespace App\Filament\Pages;

use App\Support\Fitur;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/** Dashboard = laporan dasar dengan filter periode (default 30 hari terakhir). */
class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Dashboard';

    /**
     * Laporan toko hanya untuk orang toko. Super Admin (tanpa izin laporan.lihat) yang membuka
     * /admin langsung diarahkan ke halaman Fitur & paket; widget penjualan tidak dimuat.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return self::bolehLihatLaporan();
    }

    private static function bolehLihatLaporan(): bool
    {
        return (bool) \Filament\Facades\Filament::auth()->user()?->hasPermissionTo('laporan.lihat');
    }

    public function mount(): void
    {
        if (! self::bolehLihatLaporan()) {
            $this->redirect(FiturPaket::canAccess() ? FiturPaket::getUrl() : '/', navigate: false);
        }
    }

    public function getWidgets(): array
    {
        return self::bolehLihatLaporan() ? parent::getWidgets() : [];
    }

    /**
     * Fitur "Laporan lengkap": PDF dibuka di tab baru (bisa diunduh/dicetak dari penampil PDF
     * browser) dan Excel diunduh. Periode diambil dari filter yang sedang dipakai saat diklik.
     */
    protected function getHeaderActions(): array
    {
        $tampil = fn () => self::bolehLihatLaporan() && Fitur::aktif('laporan_lengkap');
        $buka = fn (string $rute, bool $tabBaru) => sprintf(
            "const p = new URLSearchParams(); const f = \$wire.filters || {}; if (f.dari) p.set('dari', f.dari); if (f.sampai) p.set('sampai', f.sampai); %s('%s?' + p.toString()%s)",
            $tabBaru ? 'window.open' : 'window.location.assign',
            route($rute),
            $tabBaru ? ", '_blank'" : '',
        );

        return [
            Action::make('laporanPdf')
                ->label('Lihat PDF')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->visible($tampil)
                ->alpineClickHandler(fn () => $buka('filament.admin.laporan.pdf', true)),
            Action::make('laporanExcel')
                ->label('Unduh Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible($tampil)
                ->alpineClickHandler(fn () => $buka('filament.admin.laporan.excel', false)),
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    DatePicker::make('dari')->label('Dari')->default(now(config('toko.zona_waktu'))->subDays(29)->toDateString())->maxDate(fn ($get) => $get('sampai')),
                    DatePicker::make('sampai')->label('Sampai')->default(now(config('toko.zona_waktu'))->toDateString()),
                ]),
        ]);
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
