# Tehtävät

Jokainen tehtävä on kirjoitettu tiketin muotoon, kuten se tulisi tiimille oikeasti. Tehtävän
tulos on pull request, jonka kuvauksessa on: mitä muutettiin, miten se varmennettiin (testituloste
tai muu todiste) ja missä AI auttoi. Kouluttaja kertoo, mitkä tehtävät tehdään.

Aloita jokainen tehtävä lukemalla `README.md`:n osio "Finvoice ja reititys". Se on tämän
sovelluksen spesifikaatio.

## O1 – Laskun loppusumma tallentuu välillä vääräksi

Asiakas reklamoi, että osa vastaanotetuista laskuista näkyy järjestelmässä summalla 1,00 € tai
0,00 €, vaikka sanomassa on oikea summa. Esimerkit: `fixtures/messages/finvoice_locale_amount.xml`
ja `fixtures/messages/finvoice_wrong_version.xml`. Selvitä ja korjaa. Testien on oltava vihreät.

## O2 – Viitemaksuja kohdistuu väärään laskuun

Pankkiaineiston tuonnin (`php bin/import_bank.php fixtures/viitemaksut.csv`) jälkeen kirjanpito
ei täsmää: viitteellä `012506` maksettu suoritus näkyy laskulla, jonka viite on `12506`. Selvitä
ja korjaa. Tuonti pitää voida ajaa uudelleen ilman, että maksuja kirjautuu kahteen kertaan.

## O3 – RF-viitteitä ei tueta

Pankkiaineistossa on kansainvälisiä RF-viitteitä, jotka jäävät kohdistumatta. Lisää tuki.

## O4 – Sanoma ohjautui väärälle operaattorille

Nokian Nostot Oy ilmoittaa, ettei ole saanut laskua MSG-1004. Selvitä mihin sanoma meni ja miksi,
ja korjaa.

## O5 – Tietoturvakatselmointi ennen uutta kumppania

EDI-väylä avataan uudelle kumppanioperaattorille kahden viikon päästä. Tee tietoturvakatselmointi
ja kirjaa löydökset vakavuusjärjestyksessä. Korjaa vakavimmat; muista tee tiketit (lyhyt kuvaus
riittää). Käytä apuna OWASP Top 10 -listaa.

## O6 – Kumppani epäilee vääriä laskuja

Pohjolan Pakkaus Oy on saanut laskun, jota sen mukaan Kuusamon Kahvila Oy ei ole lähettänyt.
Selvitä, miten se on mahdollista, ja korjaa.

## O7 – Datan laatutarkistus ennen migraatiota

Osapuoli-, sanoma- ja laskutiedot migroidaan uuteen järjestelmään. Kirjoita skripti
`bin/data_quality.php`, joka raportoi kaikki poikkeamat, jotka estäisivät puhtaan migraation.
Perustele jokainen tarkistus yhdellä lauseella.
