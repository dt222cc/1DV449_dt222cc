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