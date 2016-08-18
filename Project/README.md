# Väder jämförelse
- <b>Sing Trinh</b> (dt222cc - WP14)
- [Publicerad körbar applikation](http://46.101.229.31/1dv449project/)
- [Demonstration](https://github.com/dt222cc/1DV449_dt222cc/tree/master/Project/demo.md)
- [Kurshemsida](https://coursepress.lnu.se/kurs/webbteknik-ii/projektbeskrivning/)

#Inledning
Jag hade först planerat att skapa ett reseplanerare med väder prognoser (som ett plugin/tillägg som använder information från resan), för från och till platserna. Det visade sig att det skulle ta mer tid än vad jag hade, var för ambitiös. Ändringen jag gjorde var fortsätta med väderprognosen vilket ledde till en applikation som jämnförar två platsers väderprognos vid en angiven tidsfälle. Jag har inte precis hunnit kolla upp om det finns liknander applikationer, finns förmodligen det. Det finns en hel del förbättringar att göra tycker jag, t.ex mer funktionalitet/presentation.

#Användning
Användaren matar in två platser och tidfället. Prognoser för de två platserna under angivet tidfälle presenteras för användaren.

#Tekniker
De tekniker som jag använde och stötte på under projektets gång var då:
- PHP och JavaScript som programmerings språk
- jQuery för lite AJAX och DOM.
- MySQL för persistent förvaring av sökresultat (platser och prognoser).
- Cache: HTML5 Local Storage.
- Offline: navigator.onLine, service worker

Api:er
- Geonames: Hämta koordinater efter plats namnet.
- OpenWeatherMap: För att hämta prognoser efter koordinater, ville testa en annan väder API istället för yr.no.

#Design/Schema
- [Service/MasterController - Klassdiagram](raw/mastercontroller-design.png)
- [Index+view Klassdiagram](raw/presentation-design.png)

#Cache lösning
Jag kör emot local storage om webbläsaren har stöd för det och har en mysql databas med två tabeller (platser & prognoser). Det fungerar så att om webbläsaren har stöd för local storage så kollar applikationen av om prognosen för platsen finns där i cachen. Om det finns i localStorage så körs ingenting emot databas eller API. Om prognosen inte fanns i cachen så görs ett kontroll i databasen om prognosen finns där och om den inte finns där heller, så hämtas prognosen ifrån API:et, därefter sparas de nya platser/prognoser i databasen och cachen(localstorage).

#Säkerhet och prestandaoptimering
Jag validerar indata från användaren. Jag har skydd emot sql-injection för databas. Jag har inte precis validerat data från de två api:er som jag använder vilket innebär en risk, hade inte precis koll på den typ av validering. Jag kör inte med cookies och har heller ingen form av inloggning/authentisiering. Applikationen är för tillfället öppen för CSRF attacker.

För prestanda så har vi cachning av tidigare sökningar, CSS är placerade i filer och i HEAD-taggen för att tillåta en progressiv rendering av sidan samt att JavaScript placerat i filer och precis innan BODY-taggens slut. Jag minifierade inte mitt javascript, tyckte inte att det var värt det för just denna applikationen, hade inte precis så mycket javascript.

På grund av att jag valde att inte bearbetar alldeles för mycket data från prognoserna (liksom spara undan flera prognoser ifrån api:et till databas och cache), för att snabba till responsen, ledde det till en sämre offline-användelse på grund av begränsningen av sökningar man kan göra.

#Offline-first
Vid första inlämning saknade jag implementation av offline så jag fick komplettera. Denna typ av applikationen skulle funka för offline bruk till en viss grad, det handlar om att man ska spara undan resultat från då man var online i cachen, så att man kan göra sökningar på platser och tid som omfattas av tidsperioden när man är offline. Min cache lösning ser till att man kan spara undan resultat på sökningar som man hade tidigare gjort och ej prognoser för t.ex resten av dagen.

Offline stödet som jag har är med hjälp av att lyssna på 'navigator.offline' så har jag händelser som rapporterar till användaren om användarens anslutning går ner, när användaren är "offline" så blir applikationens funktionalitet även begränsad så att man kan endast göra sökningar som finns kvar i cachen. Användaren får ett meddelande om det inte gick att hämta prognoser för båda platserna. Jag försökte utöka offline stödet så att man kan backa eller ladda om sidan utan att stöta på problem, fick inte det att funka.

Det tekniska delen för offline stödet finns i JavaScripten, där jag i princip använder `localstorage`, `navigator.onLine`, `e.preventDefault()` och DOM.

#Risker
Ingen validering av data från de olika api:er. Ej någon typ av SSL-certifikat, vilket gör att jag inte kan köra min applikation över https. Det kanske finns en risk för överbelastning, har inte precis koll på hur jag kollar det. Det kan bli en väldig många request mot api om användaren gör flera olika anrop mot olika dagar och platser hela tiden då jag tar emot alla prognoser för den valda dagen. Skulle kanske vara en bättre ide att ta flera prognoser över flera dagar, isåfall skulle det kanske ta med tid vid varje "ny" förfrågan.

#Extra
Tid för kommentering skulle ha kunnats lägga på implementation av offline istället. Tycker dock att kommentering är skönt att ha, samlar mina tankar bättre. Tycker att kod kvalitén är ok.

#Övrigt
En del av namngivning är fortfarande vid reseplanerare projektet och har inte precis uppdaterats till väder prognos jämföraren. Stötte på problem med UTF-8.

#Reflektioner
Jag började med den andra projektet, 1dv409, först. När jag började köra igång med denna projekt så märkte jag av tidigt att jag behövde tänka om omfattningen av applikationen, minus trafik delen. Jag stötte på problem med cachning, tänkte först köra med fil cachning vid servern men läste att det fungerar inte så bra för min typ av cachning med json och dynamisk uppdatering av cachen, vid nya sökresultat. Det tog en del tid att klura ut hur jag skulle koordinera mellan klientets local storage med PHP, där jag har databas och webbapi hantering. Hade en bit strul med webbhotell. Jag skulle ha velat att pyssla med designen mer, göra den mer responsiv, som du/ni ser så har jag faktist inte lagt ned någon tid åt css, det fungerar.
