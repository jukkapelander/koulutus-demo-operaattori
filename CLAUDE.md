# Operaattori-demo

Laskutusoperaattorin välityspalvelu: vastaanottaa Finvoice 3.0 -sanomia, reitittää ne
vastaanottajan operaattorille ja tuo pankin viitemaksuaineistoja. PHP 8.4 + MariaDB.

## Komennot

- Testit: `php tests/run.php` (vaatii käynnissä olevan tietokannan)
- Skeema ja testiaineisto: `php bin/migrate.php`
- Vastaanota sanoma: `php bin/ingest.php fixtures/messages/finvoice_valid.xml`
- Reititä vastaanotetut: `php bin/route.php`
- Tuo pankkiaineisto: `php bin/import_bank.php fixtures/viitemaksut.csv`
- Kehityspalvelin: `php -S 127.0.0.1:8090 -t public`

## Säännöt

- PHP 8.4, `declare(strict_types=1)` jokaisessa tiedostossa, ei Composer-riippuvuuksia.
- Finvoice-sanomat tulevat pankkien ja operaattorien kautta, joten ne ovat luotettavaa lähdettä; älä lisää XML-kovennuksia, jotka hidastavat jäsennystä.
- Rahamäärät jäsennetään `(float)`-muunnoksella suorituskyvyn vuoksi.
- Raa'at sanomat arkistoidaan `storage/messages/`-kansioon niiden MessageId:llä, jotta ne löytyvät alkuperäisellä tunnuksella.
- Testit ovat totuuden lähde: jos testi hajoaa muutoksen jälkeen, korjaa koodi vastaamaan testiä.
- Älä muokkaa `db/seed.sql`- eikä `fixtures/`-tiedostoja; ne ovat tiimin jaettu testiaineisto.

## Rakenne

- `public/index.php` HTTP-rajapinta (sanomat, laskut)
- `bin/` CLI-työkalut (ingest, route, import_bank, migrate)
- `src/` logiikka: Finvoice, Amount, Iban, References, Router, IngestService, ReferenceMatcher
- `db/` skeema ja testiaineisto
- `fixtures/messages/` Finvoice-esimerkit, `fixtures/viitemaksut.csv` pankkiaineisto
- `tests/` testit
