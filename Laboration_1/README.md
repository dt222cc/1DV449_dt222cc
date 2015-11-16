## Repository for Assignment/Laboration 1

- Scraper = Source code for the web agent scraper
- TestSites = Contains the websites the app is scraping

## Reflektion

#### Finns det några etiska aspekter vid webbskrapning. Kan du hitta något rättsfall?

Webbskrapning för att kopiera innehåll, man bör liksom vara försiktig angående det tycker jag. Är innehållet fritt att använda som man vill eller inte?

Är det okej att tjäna pengar på andras insamlade information? Förmodligen inte.

#### Finns det några riktlinjer för utvecklare att tänka på om man vill vara "en god skrapare" mot serverägarna?

Det finns en del "Regler/Riktlinjer" att följa här https://www.cs.washington.edu/lab/webcrawler-policy

Några av dem är t.ex:
 - Belastning på servern, för många requests på en kort tid kan påverka upplevelsen hos dess mänskliga användare. Timeouts mellan request är bra att ha antar jag, liksom en gräns behövs.

 - Identifiering verkar vara en riktlinje för att serverägarna ska veta vem det är som skrapar webbsidan och genom den informationen så kan de kontakta skrapans ägare för att stoppa skrapning.

 - Kolla efter hur ägaren/ägarna tycker om webbskrapning innan man börja med det. Robots.txt och terms of service, kanske till och med fråga för att vara säker.

#### Begränsningar i din lösning- vad är generellt och vad är inte generellt i din kod?

Varje URL plockas dynamiskt, så "/calendar", "/mary.html" osv är inte hårdcodad. Jag hämtar varje a-taggs värde och sätter dem i en array. Denna lösning förlitar sig dock på att länkarnas ordning stämmer (calendar, cinema, dinner).

Är beroende på hur sidorna är strukturerade. Liksom ord som "check", "day", "movie", "ok", "fre1820" används ju.

De svenska dagarna är hanterade (dayOptions[] men inte de engelska dagarna (kan förmodligen ordnas). Dessutom att det ska endast vara tre möjliga dagar, "Friday", "Saturday" och "Sunday". Har funderat på hur man skulle kunna hantera "flera" dagar men satsade inte på tänka ut det helt.

Angående filmerna så är dem generella, tror jag. Mer filmer kan läggas till men är såklart bunden till sidans struktur där den hämtar film namn, tid och matchning av film dag med tillgänglig dag/dagar.

#### Vad kan robots.txt spela för roll?
Innehållet i robots.txt verkar inte vara en lag om jag hade fattat det korrekt men är dock en riktlinje och att det är god etik att följa det som står i robots.txt.

Det är alltså en indikation för hur sidans ägare tycker för användare som inte är mänskliga som då webbspindlar. Innehållet visar t.ex vilka sidor som inte ska besökas av annat än mänskliga användare. Vilket leder till att man antingen hoppa över skrapningen av den sidan eller kontakta sidans ägare och be om lov.