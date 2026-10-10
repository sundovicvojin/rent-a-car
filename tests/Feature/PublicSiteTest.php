<?php

use App\Mail\ContactMessage;
use App\Models\CompanySetting;
use App\Models\Reservation;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('otvara sve javne stranice sa lang="sr" i title-om', function (string $url) {
    $this->get($url)->assertOk()
        ->assertSee('<html lang="sr">', false)
        ->assertSee('<meta name="description"', false)
        ->assertSee('property="og:title"', false);
})->with(['/', '/vozila', '/kontakt', '/uslovi-najma', '/politika-privatnosti', '/rezervacija']);

it('naziv firme dolazi iz podešavanja, ne iz šablona', function () {
    CompanySetting::first()->update(['name' => 'Moja Nova Firma', 'phone' => '+381 11 999 888']);
    $this->get('/')->assertSee('Moja Nova Firma')->assertDontSee('Rent a Car Novi Sad');
});

it('prikazuje detalj vozila sa kalendarom i podacima', function () {
    $v = App\Models\Vehicle::first();
    $this->get(route('fleet.show', $v))->assertOk()->assertSee($v->name)->assertSee('Kalendar zauzetosti')->assertSee('Depozit');
});

it('ne prikazuje neaktivno vozilo', function () {
    $v = App\Models\Vehicle::first();
    $v->update(['status' => 'inactive']);
    $this->get(route('fleet.show', $v))->assertNotFound();
    $this->get('/vozila')->assertDontSee($v->name);
});

it('filtrira vozni park po menjaču, gorivu i sedištima', function () {
    $this->get('/vozila?menjac=automatic')->assertOk()->assertSee('Toyota Corolla Hybrid')->assertDontSee('Škoda Fabia');
    $this->get('/vozila?gorivo=electric')->assertSee('Peugeot e-208')->assertDontSee('Dacia Duster');
    $this->get('/vozila?sedista=9')->assertSee('Opel Vivaro')->assertDontSee('Škoda Fabia');
});

it('sortira po ceni', function () {
    $this->get('/vozila?sort=cena_opadajuce')->assertOk()->assertSeeInOrder(['Opel Vivaro', 'Škoda Fabia']);
    $this->get('/vozila?sort=cena_rastuce')->assertSeeInOrder(['Škoda Fabia', 'Opel Vivaro']);
});

it('sa datumima prikazuje samo slobodna vozila i ukupnu cenu', function () {
    $r = Reservation::where('status', 'confirmed')->first(); // Toyota Corolla, u budućnosti
    $busyVehicle = $r->vehicle;
    $from = $r->starts_on->toDateString();
    $to = $r->ends_on->toDateString();

    $this->get("/vozila?od=$from&do=$to")->assertOk()->assertDontSee($busyVehicle->name)->assertSee('Ukupno za');
});

it('proverava neispravne datume u pretrazi', function () {
    $this->get('/vozila?od=2030-05-10&do=2030-05-01')->assertRedirect('/vozila')->assertSessionHasErrors('do');
});

it('šalje poruku sa kontakt forme', function () {
    Mail::fake();
    $this->post('/kontakt', ['name' => 'Ivan Ivić', 'email' => 'ivan@example.com', 'message' => 'Zanima me ponuda za vikend.', 'consent' => '1'])
        ->assertRedirect('/kontakt');
    Mail::assertSent(ContactMessage::class);
});

it('kontakt forma traži saglasnost i odbija honeypot', function () {
    Mail::fake();
    $this->post('/kontakt', ['name' => 'Ivan', 'email' => 'ivan@example.com', 'message' => 'Zanima me ponuda.'])
        ->assertSessionHasErrors(['consent']);
    $this->post('/kontakt', ['name' => 'Bot', 'email' => 'b@example.com', 'message' => 'Spam spam spam spam', 'consent' => '1', 'website' => 'x'])
        ->assertSessionHasErrors(['website']);
    Mail::assertNothingSent();
});

it('kontakt forma ima throttle', function () {
    foreach (range(1, 5) as $i) {
        $this->post('/kontakt', []);
    }
    $this->post('/kontakt', [])->assertStatus(429);
});

it('sitemap sadrži stranice i vozila', function () {
    $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')
        ->assertSee('/vozila/skoda-fabia-2022', false)->assertSee('/kontakt', false);
});

it('stranica potvrde i rezervacije nisu za indeksiranje', function () {
    $this->get('/rezervacija')->assertSee('noindex', false);
});

it('pravne stranice su jasno označene kao placeholder', function () {
    $this->get('/uslovi-najma')->assertSee('Placeholder tekst');
    $this->get('/politika-privatnosti')->assertSee('Placeholder tekst');
});
