<?php

namespace App\Filament\Pages;

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
