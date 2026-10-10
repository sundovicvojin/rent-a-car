<x-layouts.app title="Politika privatnosti" description="Kako obrađujemo podatke o ličnosti prikupljene pri rezervaciji i kontaktu.">
    <div class="wrap max-w-3xl pb-10 pt-10">
        <p class="eyebrow">Pravno</p>
        <h1 class="h2 mt-2" style="font-size: clamp(2rem, 4.5vw, 3.2rem)">Politika privatnosti</h1>
        <p class="badge badge-signal mt-5 w-full justify-start px-4 py-3 text-sm" role="note">Placeholder tekst. Ovaj sadržaj mora da pregleda i odobri pravnik pre objavljivanja sajta.</p>

        <div class="prose-sr mt-6">
            <h2>1. Rukovalac podacima</h2>
            <p>{{ $company->name }}, {{ $company->address }}. Kontakt: {{ $company->email }}, {{ $company->phone }}. [Dopuniti pravnim podacima: matični broj, PIB.]</p>
            <h2>2. Koje podatke prikupljamo</h2>
            <p>Ime i prezime, telefon, mejl adresa, napomena uz rezervaciju i podaci o rezervaciji (vozilo, termin, lokacije). Ne prikupljamo i ne čuvamo podatke o platnim karticama.</p>
            <h2>3. Svrha i pravni osnov</h2>
            <p>Podatke koristimo za obradu rezervacije i komunikaciju sa klijentom, na osnovu saglasnosti i radi izvršenja ugovora. [Proveriti sa pravnikom.]</p>
            <h2>4. Rok čuvanja</h2>
            <p>[Uneti rok čuvanja podataka.]</p>
            <h2>5. Prava lica na koje se podaci odnose</h2>
            <p>Imate pravo na pristup, ispravku, brisanje i prigovor, kao i na povlačenje saglasnosti. Zahtev možete poslati na {{ $company->email }}.</p>
            <h2>6. Kolačići i spoljne usluge</h2>
            <p>Sajt ne koristi alate za praćenje niti reklamne skripte. Fontovi se serviraju sa našeg servera. Sesijski kolačić se koristi isključivo za rad forme.</p>
        </div>
    </div>
</x-layouts.app>
