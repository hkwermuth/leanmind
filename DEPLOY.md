# Deploy fra GitHub til Simply.com

Denne guide beskriver, hvordan du sætter **automatisk deploy** op, så hver gang du pusher til `main` på GitHub, uploades filerne til dit webhotel hos Simply.

---

## 1. Find FTP-oplysninger hos Simply

1. Log ind på [Simply.com](https://www.simply.com) og åbn din **Kontrolpanel**.
2. Find **FTP / fil-adgang** (eller "Login-oplysninger" / "Administration").
3. Notér:
   - **FTP-servernavn (host)**  
     Ofte er det dit domæne, fx `ftp.leanmind.dk` eller `leanmind.dk`.  
     Hvis det ikke virker, står der ofte en server som `linux1.simply.com` – brug den.
   - **Brugernavn**  
     Typisk dit domænenavn (fx `leanmind.dk`) eller det brugernavn Simply har givet dig.
   - **Adgangskode**  
     Den du bruger til FTP (kan være anderledes end din Simply-login).

Filer skal ligge i mappen **`public_html`** (eller den mappe Simply angiver som "webroot"). Workflow-filen er sat til `public_html` – hvis Simply bruger et andet mappenavn, skal du rette det i `.github/workflows/deploy-simply.yml` under `server-dir`.

---

## 2. Opret hemmeligheder (secrets) i GitHub

1. Gå til dit **repository** på GitHub.
2. Klik **Settings** → **Secrets and variables** → **Actions**.
3. Klik **New repository secret** og opret disse tre (med de værdier du fandt hos Simply):

| Navn             | Eksempel / beskrivelse                          |
|------------------|--------------------------------------------------|
| `FTP_SERVER`     | `ftp.leanmind.dk` eller `leanmind.dk` (uden `ftp://`) |
| `FTP_USERNAME`   | Dit FTP-brugernavn fra Simply                    |
| `FTP_PASSWORD`   | Din FTP-adgangskode                              |

**Vigtigt:** Brug kun **FTP-servernavn** i `FTP_SERVER` – uden `ftp://` eller `https://`.

---

## 3. Sådan virker deploy nu

- Når du **pusher til `main`** (fx `git push origin main`), kører workflow-filen `.github/workflows/deploy-simply.yml`.
- Den **uploader alle relevante filer** fra repoet til `public_html` på Simply (`.git`, `.github`, README og nogle dev-filer udelades).
- Du kan følge med under **Actions**-fanen på GitHub: hver push vises som et workflow-run, og du kan se log for evt. fejl.

**Første gang:** Push en ændring til `main` og tjek under **Actions**, at jobbet "Deploy to Simply" kører grønt. Hvis det fejler, er det ofte forkert server/brugernavn/adgangskode eller forkert `server-dir`.

---

## 4. Kontaktformular og PHP

- **Kontaktformularen** (da og en) sender nu til **`send-kontakt.php`**, som sender mail til `hannah@leanmind.dk`.
- Filen `send-kontakt.php` skal ligge i **roden** af det, der deployes (sammen med `index.html`, `kontakt.html` osv.), så den kommer automatisk med ved deploy til `public_html`.
- Sørg for, at **PHP er slået til** på dit Simply-host (det er normalt standard). Hvis mails ikke ankommer, tjek Simply-dokumentation om `mail()` eller spørg deres support.

---

## 5. Sådan tester du, at mail virker

1. **Sørg for, at sitet er deployet**  
   Push til `main`, så filerne (inkl. `send-kontakt.php`) ligger på Simply.

2. **Åbn den rigtige side på nettet**  
   Gå til **https://www.leanmind.dk/kontakt.html** (ikke en fil åbnet lokalt i browseren – PHP kører kun på serveren).

3. **Udfyld formularen**  
   Brug fx dit eget navn og **din egen mailadresse**, så du nemt kan genkende testen. Skriv et kort emne og besked (fx "Test fra kontaktformular").

4. **Klik "Send besked"**  
   - Du bør komme til en **tak-side** ("Tak for din besked" / "Thank you for your message").  
   - Tjek **indbakken** for `hannah@leanmind.dk`: der bør ligge en mail med det emne og den besked, du skrev.

5. **Test også den engelske side**  
   Gå til **https://www.leanmind.dk/en/contact.html**, udfyld og send. Tak-siden bør være på engelsk, og mailen ankommer som før.

**Hvis mailen ikke kommer:**

- Tjek **spam/junk** hos den konto, der modtager (hannah@leanmind.dk).
- På Simply bruges ofte PHP’s `mail()`. Nogle hosting-planer kræver, at afsender-domænet er godkendt, eller at du bruger deres SMTP. Se Simply’s dokumentation under "Mail" / "PHP mail" eller skriv til deres support.
- Tjek at **tak-siden vises**: hvis den gør, er formularen og PHP-scriptet i gang – så er problemet kun selve afsendelsen fra serveren.

---

## 6. Kort oversigt

| Du gør dette              | Resultat                          |
|---------------------------|-----------------------------------|
| Redigerer i Cursor        | Ændringer er kun lokalt/GitHub    |
| Committer og pusher til `main` | GitHub Actions deployer til Simply |
| Besøger www.leanmind.dk   | Du ser den nyeste version + PHP   |

Hvis din Simply-konto bruger en anden mappe end `public_html`, ret `server-dir` i `.github/workflows/deploy-simply.yml` til det mappenavn Simply angiver.
