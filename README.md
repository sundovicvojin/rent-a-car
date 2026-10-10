# Rent a Car: sajt i admin panel

Sajt za iznajmljivanje vozila u Laravelu 13 sa Filament 5 admin panelom.
Jezik je srpski (latinica), valuta EUR, vremenska zona Europe/Belgrade, datumi dd.mm.yyyy.

**Stack:** Laravel 13, Filament 5, Livewire 4 (samo za tok rezervacije), Blade + Tailwind CSS 4 (Vite),
Leaflet + OpenStreetMap (mapa), SQLite za razvoj (migracije rade i na MySQL/PostgreSQL), Pest za testove.

## Pokretanje

Potrebno: PHP 8.3+ (sa `intl`, `mbstring`, `pdo_sqlite`, `gd` sa WebP podrškom, `zip`), Composer, Node 20+.
Na macOS-u: `brew install php composer node`.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite          # ako fajl ne postoji
php artisan migrate --seed              # tabele + podaci firme + demo podaci (samo u lokalnom okruženju)
php artisan storage:link                # javni pristup slikama
npm install && npm run build            # za razvoj: npm run dev
php artisan serve                       # http://localhost:8000
```

- Sajt: <http://localhost:8000>
- Admin panel: <http://localhost:8000/admin>

Mejlovi se u razvoju ne šalju, već upisuju u `storage/logs/laravel.log` (`MAIL_MAILER=log`).
Za produkciju podesite pravi SMTP u `.env` (`MAIL_*`) i `APP_URL`.

## Admin korisnik

Demo seeder (samo kad je `APP_ENV=local` ili `testing`) pravi korisnika `admin@example.com` sa lozinkom `password`.
**Ne koristite ga na produkciji.**

Pravi admin korisnik (preporučeno i lokalno):

```bash
php artisan make:filament-user
```

Svi korisnici iz tabele `users` imaju pristup panelu. Ako želite ograničenje, izmenite `canAccessPanel()` u `app/Models/User.php`.

## Demo podaci

```bash
php artisan migrate:fresh --seed
```

Pravi 8 vozila, 4 lokacije, dodatke, popuste za duže najmove, sezonska pravila i nekoliko rezervacija.
Slike su **generisane privremene ilustracije** (jasno označene tekstom „PRIVREMENA SLIKA“) i ne sadrže tuđe fotografije.
Na produkciji se pokreće samo `php artisan db:seed --class=CompanySettingSeeder` (podaci firme).

## Izmena naziva firme, kontakta i radnog vremena

Admin panel → **Podešavanja → Podešavanja firme**. Naziv, telefon, mejl, adresa i radno vreme čuvaju se u bazi
(tabela `company_settings`) i prikazuju se u zaglavlju, podnožju, mejlovima i pravnim stranicama.
Naziv nije upisan u šablone. Podrazumevane vrednosti su u `config/rentacar.php`.

U istim podešavanjima nalaze se i parametri dostave na adresu sa mape: koordinate baze, cena po kilometru i najveća udaljenost.

## Izmena slika vozila

Admin panel → **Vozni park → Vozila** → izmena vozila → odeljak **Slike**:

- dodajte slike, unesite alt tekst (opis za čitače ekrana) i označite glavnu sliku;
- redosled menjate prevlačenjem redova;
- pri čuvanju se automatski prave manje WebP verzije (480, 960 i 1600 px) u `storage/app/public/vehicles`.

Placeholder slike zamenite pravim fotografijama: obrišite red placeholder slike i dodajte novu.

## Cene i poslovna pravila

- Novac se čuva kao ceo broj centi. U panelu se unosi u evrima.
- Dani najma se računaju po datumima: 14. do 17. je 3 dana, najmanje 1 dan. Dan vraćanja je ponovo slobodan za novo preuzimanje.
- Cena = najam (sa sezonskim pravilima po danu) − popust za duže najmove + dostava na obe lokacije + dodaci.
- Sezonsko pravilo za jedno vozilo ima prednost nad pravilom za sva vozila.
- Vozilo je zauzeto ako se period preklapa sa rezervacijom koja nije otkazana ili sa blokadom za servis (**Rezervacije → Blokade**).
- Duplo rezervisanje je sprečeno transakcijom sa zaključavanjem reda vozila (`lockForUpdate`).
- Ručna rezervacija iz panela koristi isti servis, pa važe ista pravila. Termin i vozilo postojeće rezervacije se ne menjaju: otkažite je i napravite novu.

Sva logika je u `app/Services`: `AvailabilityService`, `PricingService`, `ReservationService`, `DeliveryService`, `ImageService`.
Kontroleri i Filament samo pozivaju servise, pa ih može koristiti i buduća mobilna aplikacija (`routes/api.php`, prefiks `v1`, za sada prazan).

## Testovi

```bash
vendor/bin/pest
```

Pokriveni su: dostupnost, računanje cene, sprečavanje duplog rezervisanja, tok rezervacije, mapa i dostava,
javne stranice (filteri, sortiranje, kontakt forma, sitemap) i osnovne stranice admin panela.

## Šta nije urađeno (faza 2) i na šta obratiti pažnju

- Online plaćanje i otključavanje vozila nisu implementirani. Rezervacija ima polja `payment_method` i `payment_status`,
  a u fazi 1 je jedino „plaćanje pri preuzimanju“. Podaci o karticama se ne obrađuju niti čuvaju.
- **Uslovi najma i politika privatnosti su placeholder tekst** i moraju ih pregledati pravnik. Isto važi za marketinške
  tekstove na početnoj stranici (označeni kao demo).
- Mapa učitava pločice sa OpenStreetMap servera (pominje se u politici privatnosti). Doplata se računa po vazdušnoj liniji.
- Fontovi (Bricolage Grotesque, DM Sans, JetBrains Mono) su preuzeti sa Google Fonts (OFL) i serviraju se sa sopstvenog servera,
  samo latin i latin-ext podskup (č, ć, š, đ, ž).
- `public/robots.txt` sadrži `localhost` u adresi sitemap-a: zamenite pravim domenom pri objavljivanju.
- Pre produkcije: `APP_ENV=production`, `APP_DEBUG=false`, pravi SMTP, queue worker ako se mejlovi budu stavljali u red.

## Vizuelni identitet

Boje, fontovi i razmaci su CSS promenljive (tokeni) u `resources/css/app.css`, sa tamnom temom preko `prefers-color-scheme`.
Ako se u korenu projekta pojavi `STILL.md`, on ima prednost: ažurirajte tokene na jednom mestu.
