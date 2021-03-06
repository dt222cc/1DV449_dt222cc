## Rapport - Messy Labbage
- Sing Trinh - dt222cc
- [Laboration 2](https://coursepress.lnu.se/kurs/webbteknik-ii/laborationer/laboration-02/)

## Innehållsförteckning
- [Säkerhetsproblem](#s%C3%A4kerhetsproblem)
	- [Injektion](#injektion)
	- [Trasiga autentisering och sessionshantering](#trasiga-autentisering-och-sessionshantering)
	- [Cross-Site Scripting (XSS)](#cross-site-scripting-xss)
	- [Osäkra direkta objektreferenser](#os%C3%A4kra-direkta-objektreferenser)
	- [Exponering av känslig data](#exponering-av-k%C3%A4nslig-data)
	- [Saknad åtkomstkontroll på funktionsnivå](#saknad-%C3%A5tkomstkontroll-p%C3%A5-funktionsniv%C3%A5)
	- [Cross-Site Request Forgery (CSRF)](#cross-site-request-forgery-csrf)
- [Prestandaproblem](#prestandaproblem)
	- [Cachning](#cachning)
	- [Skript vid slutet](#skript-vid-slutet)
	- [Inline kod](#inline-kod)
	- [Minifiera JavaScript](#minifiera-javascript)
- [Egna övergripande reflektioner](#egna-%C3%B6vergripande-reflektioner)
- [Referenser](#referenser)

## Säkerhetsproblem

### #Injektion
<b>Problem:</b><br>
Öppen för SQL Injection...

SQL satser/frågor använder inte sig av "parameterized questions". Alltså att fält som "username" och "password" från användarinmatningen inte ska användas direkt i SQL kommandon/frågan genom konkatenering utan någon form av injektion validering.

<b>Följder:</b><br>
Risker för detta är att de elaka användarna kan förändra SQL satsens uppbyggnad och skapa rejäla problem som bl.a förlust eller korruption av data eller till och med fullständig värd övertagande ör möjligt. T.ex så kan man logga in som admin genom att skriva `NoNo' OR '1'='1`. Detta fungerar eftersom detta tolkas som `SELECT * FROM user WHERE password = 'NoNo' OR '1'='1'`.

<b>Åtgärder:</b><br>
För att förhindra injektionen ska man separera opålitliga uppgifter från kommandon och frågor.

<b>Referenser:</b><br>
[[1] OWASP, "Top 10 2013 - A1 Injection"](#s%C3%A4kerhet)


### #Trasiga autentisering och sessionshantering
<b>Problem:</b><br>
Det verkar som att man är fortfarande inloggad även om man klickar på "log out" knappen. Exempel: Är inloggad och logga ut och navigera sedan tillbaka till `/message` adressen så kommer sessionen att authentisera användaren igen (utan att behöva ange inloggninsuppgifter igen).

<b>Åtgärder:</b><br>
Se till att login/logout sessioner är verkligen satta korrekt när man loggar in och ut eller att de förstörs när man loggar ut.

<b>Referenser:</b><br>
[[2] OWASP, "Top 10 2013 - A2 Broken Authentication and Session Management"](#s%C3%A4kerhet)


### #Cross-Site Scripting (XSS)
<b>Problem:</b><br>
Det verkar som att indatan inte valideras/filtreras emot XSS. Alltså att elaka användare kan lägga in script kod i meddelande, som kan orsaka olika typer av problem.

<b>Följder:</b><br>
Följder kan variera från en småaktig obehag till en betydande säkerhetsrisk, beroende på hur känsliga de data som hanteras av den utsatta platsen och den typ av skydd som genomförst av webbplatsens ägare.

<b>Åtgärder:</b><br>
Validera indata (finns flera olika sätt, t.ex bara tillåta vissa tecken) och filtrera utdata. En “whitelist” av tillåtna tecken rekommenderas att ha eftersom det hjälper till att skydda mot XSS, dock är det inte ett helt fullständigt skydd (Läs mer i [3])

<b>Referenser:</b><br>
[[3] OWASP, "Top 10 2013 - A3 Cross-Site Scripting (XSS)"](#s%C3%A4kerhet)


### #Osäkra direkta objektreferenser
<b>Problem:</b><br>
Genom att besöka adressen `http://localhost:3000/static/message.db` så laddas den filen ned till hårddisken.

<b>Följder:</b><br>
Detta utgör en säkerhetsrisk då att vem som helst som ladda ned databasen och komma åt all data från databas filen.

<b>Åtgärder:</b><br>
Placera `.db` filer så att man inte kan komma åt dem via URLn, t.ex i PHP så kunde man sätta filen i en mapp på rootnivå.

<b>Referenser:</b><br>
[[4] OWASP, "Top 10 2013 - A4 Insecure Direct Object References"](#s%C3%A4kerhet)


### #Exponering av känslig data
<b>Problem:</b><br>
Känslig data som lösenord visas och skickas som klar text.

<b>Följder:</b><br>
Attackerarna kan komma åt känslig data som användarinformation (lösenord) och meddelanden.

<b>Åtgärder:</b><br>
Kryptera uppgifter istället för att ha dem i klartext. Hasha lösenord, SALT kan användas för att ge mer säkerhet åt krypteringen.

<b>Referenser:</b><br>
[[5] OWASP, "Top 10 2013 - A6 Sensitive Data Exposure"](#s%C3%A4kerhet)


### #Saknad åtkomstkontroll på funktionsnivå

<b>Problem:</b><br>
Man kan komma åt alla meddelanden utan att vara inloggade genom att lägga till `/message/data` på applikationsens URL.

<b>Följder:</b><br>
Som jag nämde tidigare så kunde man komma åt `message.db` filen genom URLn, inte helt säker på hur man kan komma åt innehållet men kanske öppna det med en applikation som kan hantera db filer.

<b>Åtgärder:</b><br>
Det är inte tillräckligt att bara inte visa "länkar" eller "knappar" till skyddade funktioner som att visa meddelanden, som tanken vara att bara användarna kan läsa.
Det behövs åtkomstkontroller också.

<b>Referenser:</b><br>
[[6] OWASP, "Top 10 2013 - A7 Missing Function Level Access Control"](#s%C3%A4kerhet)

### #Cross-Site Request Forgery (CSRF)
<b>Problem:</b><br>
Applikationen verkar inte ha tillräckligt skydd mot CSRF-attacker. Hittar inte denna "Unpredictable CSRF token" i formuläret eller någon form av kontroll att användaren "ville skicka en POST".

<b>Följder:</b><br>
Detta är en typ av attack där användarens webbläsare tvingas att skicka ovälkomna requests mot en sida som användaren litar på. T.ex så kan offret tvingas att skicka meddelanden.

<b>Åtgärder:</b><br>
Implementera CSRF skydd, t.ex "Synchronizer token pattern". OWASP har en cheat sheet för CSRF-attacker.

<b>Referenser:</b><br>
[[7] OWASP, "Top 10 2013 - A8 Cross-Site Request Forgery (CSRF)"](#s%C3%A4kerhet)<br>
[[8] OWASP, "Cross-Site Request Forgery (CSRF) Prevention Cheat Sheet"](#s%C3%A4kerhet)

***

## Prestandaproblem

### #Cachning
<b>Problem:</b><br>
Applikationen cachar inte resurser.

Två huvudskäl till att webbcacheminnen används är för att minska latens och för att minska nätverkstrafik. Mindre latens = bättre användarupplevelse = ökad prestanda.

Genom att hålla kvar data i minnet minskas antalet HTTP förfrågninar vilket innebär att prestandan ökas. 

<b>Åtgärder:</b><br>
Många resurser kan cachas, t.ex css och bilder, dock ej meddelanden. Se till att behålla cachen i ett par minuter eller timmar eller ett rimligt tid som verkar passa bra.

<b>Referenser:</b><br>
[[10] Steve Sounders, "High Performance Web Sites" - Rule 3: Add an Expires Header](#prestanda)<br>
[[11] Mark Nottingham, "Caching Tutorial"](#prestanda)<br>
[[12] Kyle Young, "A Beginner's Guide to HTTP Cache Headers"](#prestanda)


### #Skript vid slutet
<b>Problem:</b><br>
JavaScript filerna `Message.js` och `MessageBoard.js` länkas in i headern. Detta kan blockera renderingen av innehållet under skriptet och blockera komponenter från att hämtas.

<b>Åtgärder:</b><br>
Placera dem vid slutet istället, innan `</body>`.

<b>Referenser:</b><br>
[[13] Steve Sounders, "High Performance Web Sites" - Rule 6: Put Scripts at the Bottom](#prestanda)


### #Inline kod
<b>Problem:</b><br>
Filerna `admin.html` och `index.html` har css i inline.

<b>Åtgärder:</b><br>
Enligt Steve Sounders ska man ska lägga dem i externa filer. Det sättet som vi har lärt oss att göra.

<b>Referenser:</b><br>
[[14] Steve Sounders, "High Performance Web Sites" - Rule 8: Make JavaScript and CSS External](#prestanda)


### #Minifiera JavaScript
<b>Problem:</b><br>
JavaScript är inte minifierad. "Minification" är en process där man ta bort tecken från kod som inte påverkar koden för att minska kodens storlek, därigenom förbättrar nedladdningstid.

<b>Åtgärder:</b><br>
Det finns flera [verktyg](https://www.google.se/search?q=tools+for+minifying+javascript) för minifiering av JavaScript.

Liksam ha en minifierad version av dina JavaScript filer. Man kan ersätta jquery.js med deras minifierad version med "min" i fil namnet. Samma sak gäller med CSS filer.

<b>Annat:</b><br>
Man kan även minifiera CSS, men man tjänar oftast mindre än det man tjänar ifrån minifiering av JavaScript. Det man gör då är att kombinera identiska klasser, ta bort oanvända klasser, etc. Oftast ett svårt ämne att optimera pga hur CSS är strukturerat. Steve Sounders nämner att det bästa lösningen här är att ta bort kommentarer och "whitespace" och göra enkla optimeringar som att göra förkortningar som `#606` istället för `#660066` och ta bort onödiga stränger(`0` istället för `0px`).

<b>Referenser:</b><br>
[[15] Steve Sounders, "High Performance Web Sites" - Rule 10: Minify JavaScript](#prestanda)


### #Annat
Jag får 404 errors med Chrome DevTools för filerna `static/js/materialize.js`, `/assets/js/ie10-viewport-bug-workaround.js` och `/static/css/materialize.min.css`.

Ett par millisekunder går ju åt för detta och bör hanteras tycker jag.

***

## Egna övergripande reflektioner

Jag tycker att det saknas rätt- och felmeddelanden vid eventuellt fel vid inloggning, räcker inte bara med feedback för inloggningsuppgifter. Man undrar liksom varför inloggningen inte gick igenom, skrev man in fel uppgifter?

Laddnings ikoner kanske?

Logga ut knappen vid högra hörnet syns även om man är utloggad.

Det fungerar även inte för mig att ta bort meddelanden som admin, ta bort meddelanden verkar inte vara klar.

`MessageBoard.js` verkar inte vara helt klart/redo (förmodligen: beta), finns kommenterad kod, funktionen logout är kopplat till en addRemoveMarker och används inte och en del funktionalitet verkar vara påbörjad men inte fullständigt ännu.

Första inloggninsuppgiter: `user@user.se` : `pass1user!`, fungerar inte för mig.

***

## Referenser

#### #Säkerhet
[1] OWASP, "Top 10 2013 - A1 Injection", *OWASP*, juni 2013 [Online] Tillgänglig: https://www.owasp.org/index.php/Top_10_2013-A1-Injection [Hämtad: 25 november, 2015].

[2] OWASP, "Top 10 2013 - A2 Broken Authentication and Session Management", *OWASP*, juni 2013 [Online] Tillgänglig: https://www.owasp.org/index.php/Top_10_2013-A2-Broken_Authentication_and_Session_Management [Hämtad: 26 november, 2015].

[3] OWASP, "Top 10 2013 - A3 Cross-Site Scripting (XSS)", *OWASP*, juni 2013 [Online] Tillgänglig: https://www.owasp.org/index.php/Top_10_2013-A3-Cross-Site_Scripting_(XSS) [Hämtad: 27 november, 2015].

[4] OWASP, "Top 10 2013 - A4 Insecure Direct Object References", *OWASP*, juni 2013 [Online] Tillgänglig: https://www.owasp.org/index.php/Top_10_2013-A4-Insecure_Direct_Object_References [Hämtad: 30 november, 2015].

[5] OWASP, "Top 10 2013 - A6 Sensitive Data Exposure", *OWASP*, juni 2013 [Online] Tillgänglig: https://www.owasp.org/index.php/Top_10_2013-A6-Sensitive_Data_Exposure [Hämtad: 25 november, 2015].

[6] OWASP, "Top 10 2013 - A7 Missing Function Level Access Control", *OWASP*, juni 2013 [Online] Tillgänglig: https://www.owasp.org/index.php/Top_10_2013-A7-Missing_Function_Level_Access_Control [Hämtad: 30 november, 2015].

[7] OWASP, "Top 10 2013 - A8 Cross-Site Request Forgery (CSRF)", *OWASP*, juni 2013 [Online] Tillgänglig: https://www.owasp.org/index.php/Top_10_2013-A8-Cross-Site_Request_Forgery_(CSRF) [Hämtad: 30 november, 2015]

[8] OWASP "Cross-Site Request Forgery (CSRF) Prevention Cheat Sheet", *OWASP*, november 2015 [Online] Tillgänglig: https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)_Prevention_Cheat_Sheet [Hämtad: 30 november, 2015]

#### #Prestanda
[10] Steve Sounders, "High Performance Web Sites - Rule 3: Add an Expires Header", *O'Reilly*, september 2007

[11] Mark Nottingham, "Caching Tutorial", *MNOT*, maj 2013 [Online] Tillgänglig: https://www.mnot.net/cache_docs/ [Hämtad: 30 november, 2015]

[12] Kyle Young, "A Beginner's Guide to HTTP Cache Headers", *Mobify*, april 2013 [Online] Tillgänglig: http://www.mobify.com/blog/beginners-guide-to-http-cache-headers/ [Hämtad: 30 november, 2015]

[13] Steve Sounders, "High Performance Web Sites - Rule 6: Put Scripts at the Bottom", *O'Reilly*, september 2007

[14] Steve Sounders, "High Performance Web Sites - Rule 8: Make JavaScript and CSS External", *O'Reilly*, september 2007

[15] Steve Sounders, "High Performance Web Sites - Rule 10: Minify JavaScript", *O'Reilly*, september 2007