## Laboration 3 - Mashup HT15

- Sing Trinh - dt222cc - WP14
- Demo: http://46.101.229.31/1dv449/mashup/
- Kurshemsida: https://coursepress.lnu.se/kurs/webbteknik-ii/laborationer/laboration-03/

## Reklektion

#### Vad finns det för krav du måste anpassa dig efter i de olika API:erna?

"Materialet som tillhandahålls via API får inte användas på ett sådant sätt att det skulle kunna skada Sveriges Radios oberoende eller trovärdighet."

För OpenStreetMap så räckte det med att man skulle ge credit för dem, att ha "© OpenStreetMap contributors" någonstans.

Det var inte mycket man behövde anpassa sig efter, bara tänka på att inte göra för många request.

#### Hur och hur länga cachar du ditt data för att slippa anropa API:erna i onödan?

Jag körde på 10 minuter. Ett kortare tid som 5 minuter skulle kanske vara mer optimalt för att få mer "aktuellt" trafik information.

#### Vad finns det för risker kring säkerhet och stabilitet i din applikation?

Användaren har ingen form av inmatning, så jag bör vara OK angående det. Däremot så har jag ingen validering från Sveriges Radio's API.

Applikationen är lite beroende på URLen där jag får fram bilderna för kartan. Fungerar URLen inte, slutar kartan att vara en karta, den blir tom.

Dock så fungera fortfarande applikationen, trafik listan visas fortafarande och det gör även alla "markers".

#### Hur har du tänkt kring säkerheten i din applikation?

Som jag nämde tidigare, inga fälter där elaka användaren kan skicka in skadlig kod. Liksom det är ju inget som lagras.

#### Hur har du tänkt kring optimeringen i din applikation?

CSS där uppe och JS där nere och minifierade.

Inga sidomladningar krävs förutom när användaren vill uppdatera traffiken, isåfall krävs ett uppdatering.

Jag cachar bara data från Sveriges Radio. Cachning av .css, .js och bilder skulle var en optimering men jag satsade inte på det. Jag tar det på projektet istället.


## Komplettering

#### CSRF (Cross-site request forgery)

Även kallad "one-click attack", "session riding" eller "sea-surf" (lol).

Till skillnad från XSS som utnyttjar det förtroende en användare har för en viss webbplats, utnyttjar CSRF det förtroende som en webbplats har i en användares webbläsare.

En attackerare lurar en offer att göra procedurer som offret är auktoriserad att göra, t.ex att offret besöker en sida som är "sårbar" och där attackeraren har skadlig kod inlagd (vilket gör att offret tvingas göra eventuella procedurer som attackeraren ville utföra).

Detta kan vara uppdatering av kontouppgifter, göra inköp, utloggning/inloggning.

Användaren ville inte göra denna ändring, eller vad det nu var. Webbplatsen där ändringen skedde ser det som att det var denna person som gjorde denna ändring. Saker och ting sker utan att användaren har någon aning om vad det är som har hänt (beroende på vad det är för attack), usch.

#### Hashning vs. kryptering

När man krypterar en sträng som t.ex ett lösenord, tillämpar man någon form av algoritm som förvränger den. **Tillämpnar man en nyckeln, avkodas den**. En simpel exempel är då att med en simpel algoritm byter ut alla bokstäver/siffror med ett steg upp, att alla "a" blir "b" och alla "3" blir "4" osv. Nycklen blir då, att gå ned ett steg för att avkoda den.

Poängen med kryptering är att krypterade data är **reversibel**. Använd kryptering när man vill **ha tillbaka indatat**, ett exempel kan vara ett meddelande som man inte vill ha i klar text, applicera en kryptering och förvara nycklen så säkert som möjligt och använd den för att avkoda den, krypterade meddelanden.  

***

Hashning skiljer sig från kryptering i att när data kodas, **är det extremt svårt att avkoda det**.

Man använd hashning när man vill kontrollera giltigheten av indata, **när man vill jämföra ett värde men kan inte lagra det i klar text** (som t.ex **lösenord**, finns andra former av tillämpningar).

Liksom man jämför inte sitt lösenord med ett sparat lösenord, utan man jämför resultatet av hashningen av sitt lösenord med ett sparat resultat av samma lösenord.

Som jag nämde tidigare så är det extremt svårt att avkoda en hashning, men om man använder vanliga ord i t.ex lösenordet så blir det lättare eller "mindre svårare" att avkoda det, för att undvika detta finns det något som kallas för **Salting**. Detta är något som man lägger till på hashningen för att ge extra "ummpf" (skydd) för dem med vanliga lösenord.