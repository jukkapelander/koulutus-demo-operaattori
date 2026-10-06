# Operaattori-demo

> **VAROITUS: Koulutuskäyttöön tehty demosovellus, jossa on tarkoituksella puutteita.
> Älä ota tuotantokäyttöön äläkä käytä oikealla datalla.** Kaikki yritykset, tunnukset,
> tilinumerot ja avaimet ovat keksittyjä.

Laskutusoperaattorin välityspalvelu: vastaanottaa Finvoice 3.0 -sanomia, reitittää ne
vastaanottajan operaattorille välitystietojen perusteella ja tuo pankin viitemaksuaineistoja,
jotka kohdistetaan avoimiin laskuihin. PHP 8.4 + MariaDB. Harjoitusalusta agenttiseen
ohjelmistokehitykseen; tehtävät ovat tiedostossa `TEHTAVAT.md`.


## Pika-aloitus

Vaihtoehto A – MariaDB Dockerissa, PHP omalla koneella:

```bash
docker compose up -d db                               # MariaDB 11 + skeema + testiaineisto
sudo apt install php8.4-cli php8.4-xml php8.4-mysql    # Debian/Ubuntu/WSL2 (ks. "Työkalut" alla)
php tests/run.php                                      # testien pitäisi mennä läpi
php bin/migrate.php                                    # jos tietokanta on tyhjä
php -S 127.0.0.1:18090 -t public                       # kehityspalvelin
```

Vaihtoehto B – kaikki Dockerissa:

```bash
docker compose up --build
docker compose exec app php tests/run.php
```

Yhteysasetukset luetaan `.env`-tiedostosta (`.env.example` on malli).

## Finvoice ja reititys

Nämä ovat demon oma, yksinkertaistettu spesifikaatio. Ne eivät ole Finvoice-standardi
sellaisenaan; standardi on laajempi.

**Sanoman rakenne.** Finvoice 3.0 -sanoman välitystiedot ovat `MessageTransmissionDetails`-
lohkossa: lähettäjän OVT-tunnus (`FromIdentifier`) ja operaattori (`FromIntermediator`),
vastaanottajan OVT (`ToIdentifier`) ja operaattori (`ToIntermediator`), sekä `MessageIdentifier`.
Laskun summat ovat `InvoiceDetails`-lohkossa, maksutiedot `EpiDetails`-lohkossa.

**Reititys.** Sanoma reititetään vastaanottajan operaattorille. Oikea vastaanottava operaattori
on se, joka on merkitty vastaanottajan osapuolelle operaattorirekisteriin (`parties.operator_id`).
Sanoman otsakkeen `ToIntermediator` on lähettäjän ilmoitus, jota verrataan rekisteriin; ristiriita
ei saa johtaa sanoman ohjaamiseen väärälle operaattorille.

**Alkuperän todennus.** Vastaanotetun sanoman lähettäjän OVT:n on kuuluttava sille operaattorille,
joka sanoman välitti (`FromIntermediator`). Jos ei kuulu, sanoma on hylättävä: muuten kuka tahansa
voi lähettää laskuja toisen nimissä.

**Arvot ja tunnisteet.** Rahamäärät voivat tulla pisteellä tai pilkulla ja tuhaterottimena voi olla
välilyönti. IBAN tarkistetaan mod-97-säännöllä. Kotimainen viitenumero tarkistetaan 7-3-1-säännöllä;
kansainvälinen RF-viite (ISO 11649) mod-97-säännöllä.

**Idempotenssi.** Sama sanoma (sama MessageId samalta lähettäjältä) saa kirjautua ja reitittyä vain
kerran. Sama pankin arkistointitunnus kirjataan vain kerran.

**Viitemaksujen kohdistus.** Saapuva viitemaksu kohdistetaan laskuun, jonka viitenumero on täsmälleen
sama, ja vain saman osapuolen laskuihin. Summat vertaillaan sentin tarkkuudella.

## API

| Metodi | Reitti | Kuvaus |
| --- | --- | --- |
| GET | `/api/health` | Elossaolotarkistus |
| GET | `/api/messages` | Sanomat |
| GET | `/api/messages/{id}` | Yksittäinen sanoma |
| POST | `/api/messages` | Vastaanota Finvoice-sanoma (XML rungossa) |
| GET | `/api/invoices/{id}` | Jäsennetty lasku |

```bash
curl -X POST --data-binary @fixtures/messages/finvoice_valid.xml localhost:18090/api/messages
curl localhost:18090/api/messages
```

## Testit

```bash
php tests/run.php
```

Riippuvukseton ajuri: `tests/*Test.php` määrittelee `test_`-funktioita. Osa testeistä tarvitsee
käynnissä olevan tietokannan.

## Työkalut

Tarvitaan PHP 8.4 (laajennukset xml ja pdo_mysql), Docker Compose ja git. MariaDB ajetaan
Dockerissa.

## Rakenne

```
public/index.php           HTTP-rajapinta
bin/                       CLI-työkalut
src/                       logiikka
db/schema.sql, seed.sql    skeema ja testiaineisto
fixtures/messages/*.xml    Finvoice-esimerkit
fixtures/viitemaksut.csv   pankkiaineisto
tests/                     testit
```
