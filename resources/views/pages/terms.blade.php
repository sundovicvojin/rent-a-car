<x-layouts.app title="Uslovi najma" description="Opšti uslovi iznajmljivanja vozila: starosna granica, depozit, gorivo, otkazivanje i odgovornost.">
    <div class="wrap max-w-3xl pb-10 pt-10">
        <p class="eyebrow">Pravno</p>
        <h1 class="h2 mt-2" style="font-size: clamp(2rem, 4.5vw, 3.2rem)">Uslovi najma</h1>
        <p class="badge badge-signal mt-5 w-full justify-start px-4 py-3 text-sm" role="note">Placeholder tekst. Ovaj sadržaj mora da pregleda i odobri pravnik pre objavljivanja sajta.</p>

        <div class="prose-sr mt-6">
            <h2>1. Predmet i zaključenje ugovora</h2>
            <p>Rezervacijom preko sajta klijent šalje zahtev za najam vozila. Ugovor o najmu se smatra zaključenim nakon potvrde rezervacije od strane {{ $company->name }} i potpisivanja ugovora pri preuzimanju vozila.</p>
            <h2>2. Uslovi za vozača</h2>
            <p>Vozač mora imati važeću vozačku dozvolu i minimalni broj godina i godina vozačkog staža koje odredi firma. [Uneti stvarne uslove.]</p>
            <h2>3. Cena, depozit i plaćanje</h2>
            <p>Cena se računa po danu najma, uz sezonska pravila, popuste za duže najmove, doplate za dostavu i izabrane dodatke. Depozit se ostavlja pri preuzimanju. Plaćanje se vrši pri preuzimanju vozila. [Uneti stvarne načine plaćanja.]</p>
            <h2>4. Računanje dana</h2>
            <p>Dani najma se računaju po datumima: najam od 14. do 17. u mesecu traje 3 dana. Najkraći najam je 1 dan.</p>
            <h2>5. Gorivo, kilometraža i vraćanje</h2>
            <p>[Uneti pravila o gorivu, ograničenju kilometraže, kašnjenju pri vraćanju i čistoći vozila.]</p>
            <h2>6. Otkazivanje i izmene</h2>
            <p>[Uneti rokove i uslove otkazivanja i izmene rezervacije.]</p>
            <h2>7. Odgovornost i osiguranje</h2>
            <p>[Uneti obim osiguranja, učešće u šteti i obaveze klijenta u slučaju nezgode.]</p>
            <h2>8. Kontakt</h2>
            <p>Za pitanja o uslovima najma obratite nam se na {{ $company->email }} ili {{ $company->phone }}.</p>
        </div>
    </div>
</x-layouts.app>
