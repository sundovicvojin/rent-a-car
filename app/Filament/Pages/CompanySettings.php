<?php

namespace App\Filament\Pages;

use App\Models\CompanySetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** Podešavanja firme: naziv, kontakt i radno vreme koji se prikazuju na sajtu i u mejlovima. */
class CompanySettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Podešavanja firme';

    protected static ?string $title = 'Podešavanja firme';

    protected static string|\UnitEnum|null $navigationGroup = 'Podešavanja';

    protected static ?string $slug = 'podesavanja-firme';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(CompanySetting::current()->only(['name', 'phone', 'email', 'address', 'working_hours', 'base_latitude', 'base_longitude', 'fee_per_km', 'max_delivery_km']));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Podaci o firmi')
                    ->description('Prikazuju se na javnom sajtu, u podnožju i u mejl porukama.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Naziv firme')->required()->maxLength(120),
                        TextInput::make('phone')->label('Telefon')->tel()->maxLength(40),
                        TextInput::make('email')->label('Mejl')->email()->maxLength(120),
                        TextInput::make('address')->label('Adresa')->maxLength(200),
                        Textarea::make('working_hours')->label('Radno vreme')->rows(4)->columnSpanFull()
                            ->helperText('Svaki red se prikazuje u posebnom redu.'),
                    ]),
                Section::make('Dostava na adresu (mapa)')
                    ->description('Kada klijent izabere tačku na mapi, doplata se računa po kilometru vazdušne linije od baze. Prazna polja koriste podrazumevane vrednosti.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('base_latitude')->label('Baza: geografska širina')->numeric()->minValue(-90)->maxValue(90)->placeholder((string) config('rentacar.delivery.base_lat')),
                        TextInput::make('base_longitude')->label('Baza: geografska dužina')->numeric()->minValue(-180)->maxValue(180)->placeholder((string) config('rentacar.delivery.base_lng')),
                        \App\Filament\Support\MoneyInput::make('fee_per_km')->label('Doplata po kilometru')->placeholder(number_format(config('rentacar.delivery.fee_per_km') / 100, 2)),
                        TextInput::make('max_delivery_km')->label('Najveća udaljenost dostave (km)')->numeric()->minValue(1)->maxValue(500)->placeholder((string) config('rentacar.delivery.max_km')),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([$this->getFormContentComponent()])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Action::make('save')->label('Sačuvaj')->submit('save')->keyBindings(['mod+s']),
                ]),
        ]);
    }

    public function getFormContentComponent(): \Filament\Schemas\Components\Component
    {
        return \Filament\Schemas\Components\EmbeddedSchema::make('form');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $setting = CompanySetting::query()->first() ?? new CompanySetting;
        $setting->fill($data)->save();

        Notification::make()->success()->title('Podešavanja su sačuvana.')->send();
    }
}
