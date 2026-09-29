# 11 — I moduli verso Delera

Form nativi del tema che scrivono su Delera via webhook. Nessun iframe, nessuno script di terza
parte, nessun cookie: il modulo si vede sempre, anche con tutti i consensi rifiutati.

Sostituisce il blocco `lidia/form-delera` del 16/09, che è ritirato.

---

## 1. I due moduli

**Prova gratuita** — home, `/prova-gratuita/` (in cima e in fondo).
**Team commerciale** — `/contatti/`: chi vuole l'offerta Enterprise (personalizzazione e incontro).
Ci arriva la CTA «Parla con il team» del box Enterprise di `/prezzi/` (25/09).
**Download whitepaper** — le cinque pagine whitepaper, al posto del paragrafo «FORM DI DOWNLOAD».

Un solo blocco, `lidia/modulo`, con attributo `tipo`: `prova` o `whitepaper`. Per il tipo `prova`
l'attributo `intento` (`prova` · `commerciale`) lo fissa chi compone la pagina: nessuna scelta per
il visitatore. Con `commerciale` il pulsante è «Scrivi al team commerciale», l'aiuto sotto l'email
sparisce, la conferma è quella del team commerciale. Stesso webhook della prova: **in Delera il
workflow si divide su `intento`.**

### Campi

| Campo | `prova` | `whitepaper` | Tipo |
|---|---|---|---|
| Intento | nascosto | — | dall'attributo del blocco: `prova` · `commerciale` (23/09: niente radio) |
| Nome | obbligatorio | obbligatorio | testo |
| Cognome | obbligatorio | obbligatorio | testo |
| Email di lavoro | obbligatorio | obbligatorio | email |
| Telefono | facoltativo | — | tel |
| Studio o azienda | obbligatorio | obbligatorio | testo |
| Professione | obbligatorio | obbligatorio | elenco |
| Consenso privacy | obbligatorio | obbligatorio | checkbox |
| Consenso marketing | facoltativo | facoltativo | checkbox |

Professione: Avvocato · Socio o titolare · Direzione legale d'impresa · Assicurazioni · Ateneo ·
Altro. Elenco chiuso, non testo libero: in Delera si segmenta.

Nome e cognome separati perché Delera li vuole distinti.

Il pulsante segue l'intento: **Richiedi la prova gratuita** / **Scrivi al team commerciale**.

---

## 2. Come viaggia

Senza JavaScript: POST a `admin-post.php`, validazione, consegna, redirect alla pagina di partenza.
Con JavaScript: `fetch` verso `POST /wp-json/lidia/v1/modulo`, conferma in pagina senza ricaricare.
Se la rete cade a metà, il browser **non reinvia da solo**: la richiesta potrebbe essere già arrivata
e farebbe un lead doppio. Mostra un avviso e lascia riprovare alla persona.
Stesso codice di validazione per entrambe le strade: il browser non decide cosa è valido.

La conferma **sostituisce** il modulo. Nessuna pagina `/grazie/`.

Errore: banner sopra il modulo, messaggio sotto il campo sbagliato, focus sul primo errore, **dati
conservati**. Senza JavaScript i valori tornano da un transient a scadenza breve, indicizzato da un
token nella query string — non da un cookie, così il modulo resta a zero cookie.

### Evento di tracciamento

Alla conferma il modulo fa push su `dataLayer`:

```
{ event: 'lidia_lead', tipo: 'prova'|'whitepaper', intento: 'prova'|'commerciale',
  documento: '<slug del whitepaper, vuoto altrimenti>', posizione: 'hero'|'footer'|'inline'|'' }
```

`posizione` è un attributo del blocco, scelto nell'editor: distingue il modulo in cima da quello in
fondo alla stessa pagina. Senza JavaScript l'evento lo stampa il server nella pagina di conferma.
Scritto ora perché in Fase 5 non ci sia niente da ritoccare nel tema: GTM si aggancia all'evento.

### Cache di pagina

La pagina può arrivare dalla cache di SiteGround anche dopo ore. Per questo tutto ciò che cambia
da un visitatore all'altro lo scrive **il browser**, non il server: i parametri di campagna (UTM e
click id, letti dall'indirizzo e tenuti in `sessionStorage` per tutta la visita, così valgono anche
se il modulo si compila su una pagina diversa da quella di arrivo), l'indirizzo della pagina di
partenza, e una marca temporale fresca chiesta a `GET /wp-json/lidia/v1/marca` al primo tocco.
Il server non legge mai gli UTM dall'URL. La pagina con `?lidia=` (stato dopo un invio senza
JavaScript) e il link `?lidia_dl=` sono marcati `DONOTCACHEPAGE` e `no-store`.

---

## 3. Antispam

**Niente CAPTCHA.** Quattro difese di prima parte, zero servizi esterni, zero cookie:

1. **Esca** — campo fuori schermo (`position:absolute`, non `display:none`, che i bot leggono nel
   CSS), `tabindex="-1"`, `aria-hidden`. Compilato ⟶ scartato in silenzio.
2. **Tempo** — marca temporale firmata (HMAC con il salt del sito). Sotto i 3 secondi ⟶ scartata.
   La marca stampata nell'HTML vale 7 giorni, perché può restare in cache; con JavaScript viene
   rinnovata al primo tocco sul modulo, e lì il limite dei 3 secondi vale davvero.
3. **Frequenza** — massimo 5 tentativi all'ora per connessione, contati tutti (anche quelli
   respinti dalla validazione). La chiave è un hash dell'IP con il salt del sito: l'IP in chiaro non
   si scrive da nessuna parte. Dietro proxy o CDN va messo `LIDIA_DIETRO_PROXY` a true in
   `wp-config.php`, altrimenti tutti i visitatori contano come una sola connessione.
4. **Origine** — l'intestazione `Origin` o `Referer` deve essere il nostro host.

Ferma i bot generici. Non ferma quelli scritti per questo sito: se succede, si aggiunge Cloudflare
Turnstile, che il codice prevede come passaggio innestabile. Non prima, e non senza una valutazione
di legittimo interesse scritta: Turnstile tratta IP, user-agent e fingerprint TLS, Cloudflare per
parte di quei dati agisce da titolare, e non è conforme per default.

**Nessun nonce, per scelta.** Il nonce di WordPress scade e si fossilizza nella cache di pagina: su
un modulo pubblico romperebbe gli invii veri senza fermare quelli falsi. Non c'è niente da falsificare
— non esiste un'azione utente da forgiare. Le quattro difese sopra sono il perimetro.

---

## 4. Verso Delera

Un webhook per modulo. Le URL stanno in `wp-config.php`, mai nei file di progetto:

```php
define( 'LIDIA_DELERA_WEBHOOK_PROVA',      'https://…' );
define( 'LIDIA_DELERA_WEBHOOK_WHITEPAPER', 'https://…' );
define( 'LIDIA_FORM_ALERT_EMAIL',          'lidia@lidiatech.ai' );
define( 'LIDIA_DIETRO_PROXY',              true );  // su SiteGround; in locale non serve
define( 'LIDIA_WHITEPAPER_MAIL_DELERA',    false ); // true solo se l'automazione Delera che spedisce il PDF esiste
```

Su staging i due webhook puntano a un flusso **di prova** in Delera, non a quello vero.

### Payload

```json
{
  "tipo": "prova",
  "intento": "prova",
  "nome": "", "cognome": "", "email": "", "telefono": "",
  "azienda": "", "professione": "",
  "consenso_privacy":   { "dato": true,  "quando": "ISO-8601", "testo": "v1" },
  "consenso_marketing": { "dato": false, "quando": "ISO-8601", "testo": "v1" },
  "origine": "https://lidiatech.ai/prova-gratuita/",
  "posizione": "hero",
  "campagna": { "utm_source": "", "utm_medium": "", "utm_campaign": "", "utm_term": "", "utm_content": "",
                "gclid": "", "wbraid": "", "gbraid": "", "fbclid": "", "li_fat_id": "", "msclkid": "" },
  "documento": { "id": 0, "slug": "", "titolo": "", "tag": "" }
}
```

`documento` solo per `tipo: whitepaper`. `testo` è la versione del testo di consenso accettato: se
il testo cambia, cambia la versione, e resta scritto a cosa ha detto sì ciascun contatto.

### Se Delera non risponde

Il lead non si perde. Tre tentativi con attesa crescente — subito, 5 minuti, 30 minuti, 3 ore —
tramite `wp_cron`. Dopo il terzo fallimento parte una mail a `LIDIA_FORM_ALERT_EMAIL` con i dati, e
la richiesta esce dalla coda. La coda vive in un'opzione, tiene al massimo 200 richieste, e si svuota
alla consegna o dopo sette giorni.

**Il sito non è un archivio di lead.** Niente CPT dei contatti, niente tabella: solo la coda, che
per definizione si svuota.

---

## 5. Il download del whitepaper

Alla conferma il modulo mostra un link firmato — `?lidia_dl=<id>&s=<scadenza>&k=<hmac>`, valido
24 ore — che serve il PDF da `lidia_file`. Il download parte da solo. Il link vale solo per un
whitepaper **pubblicato**: l'ID arriva dal browser e si verifica.

**Il PDF sta in una cartella chiusa.** Un file in `uploads/` è pubblico per costruzione. I file
caricati dalla pagina di un whitepaper (Aggiungi media dentro l'editor del whitepaper) finiscono in
`uploads/riservati/`, che il server non serve: l'unica strada è il link firmato. Un PDF caricato
dalla Libreria media generica resta pubblico, e l'editor lo segnala con un avviso sulla pagina del
whitepaper. `lidia_file` non è esposto nella REST API.

**`lidia_gated`** decide se il modulo c'è: spento, al posto del modulo compare il solo pulsante di
download.

**La mail con il documento la manda Delera**, non WordPress: è il CRM, ha le automazioni, e il sito
non deve occuparsi di recapito email. Serve quindi un'automazione Delera agganciata al tag
`whitepaper-<slug>` (il valore di `lidia_delera_tag` viaggia nel payload come `documento.tag`).
La riga «Ne abbiamo mandato copia anche al vostro indirizzo» compare **solo** se
`LIDIA_WHITEPAPER_MAIL_DELERA` è true in `wp-config.php`: finché l'automazione non c'è, resta spenta.

---

## 6. Forma

Etichetta sempre visibile sopra il campo. Mai il placeholder al posto dell'etichetta. Obbligatorietà
scritta a parole. Filetto e raggio dai token; anello di focus quello del tema.

Una colonna su mobile; due colonne da 640px per nome/cognome, email/telefono, azienda/professione.
Consensi e pulsante a piena larghezza.

Durante l'invio il pulsante si disabilita e dice «Invio in corso…».

Errori con `aria-describedby`, banner con `role="alert"`, focus programmatico sul primo campo
sbagliato.

---

## 7. File

| File | Cosa |
|---|---|
| `inc/forms.php` | blocco, markup, validazione, antispam, endpoint |
| `inc/delera.php` | consegna, riprove, coda, mail di allarme, download firmato |
| `assets/js/modulo.js` | invio senza ricarica — circa 3 KB, nessuna libreria |
| `assets/js/modulo-editor.js` | il blocco nell'editor |
| `assets/css/sezioni/modulo.css` | forma del modulo |

`lidia/form-delera`, `form-delera-editor.js` e le costanti `LIDIA_DELERA_*` del 16/09 sono ritirati.
