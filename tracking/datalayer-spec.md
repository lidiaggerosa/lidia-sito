# Spec dataLayer — sito lidiatech.ai

Fonte di verità del dataLayer. Chi lavora in GTM legge questo file e non ha bisogno del repo del
tema. Supera la tabella di `docs/tecnico/tracciamento.md` §4.

Container Web: account «Lidia». GA4, Meta, LinkedIn e Google Ads vivono **dentro** GTM. Nessun tag
nel tema, nessuno snippet nell'admin. Gli ID stanno in `wp-config.php`, mai nei file di progetto.

---

## 1. Convenzioni

- Nomi degli eventi in `snake_case`, prefisso `lidia_`.
- I parametri sono in italiano, come il resto del progetto.
- Ogni evento è implementato **una volta sola**, nel file indicato.
- Nessun dato personale nel dataLayer: mai email, telefono, nome. Nemmeno in hash.
- Il tracking non parte per admin e redattori, né fuori da `LIDIA_ENV=production`.

---

## 2. Contesto di pagina

Spinto in `<head>` su ogni pagina da `mu-plugins/lidia-tracking.php`, **dopo** il consent default e
**prima** dello snippet GTM.

```
{ event: 'lidia_page_context', page_type, page_id, page_language, page_template, is_logged_in }
```

| Parametro | Valori | Note |
|---|---|---|
| `page_type` | `home` · `page` · `post` · `risorsa` · `archive` | il post type per i singoli, altrimenti `home` o `archive` |
| `page_id` | intero | `0` sugli archivi |
| `page_language` | `it` · `en` | da Polylang quando c'è, altrimenti dal locale |
| `page_template` | `site` · `landing` | `landing` solo dalla Fase 6 |
| `is_logged_in` | booleano | serve a escludere il traffico interno |

Su `post` e `risorsa` si aggiungono `content_title` e `content_type`.

**Non ci sono, per scelta:** `funnel_step` (il loop è ritirato dall'11/09) e `campaign_slug` (il CPT
`landing` e la tassonomia `campagna` arrivano in Fase 6 — il parametro si aggiunge allora, insieme
alla dimensione personalizzata in GA4).

---

## 3. Eventi

| Evento | Quando | Parametri | Dove è implementato |
|---|---|---|---|
| `lidia_page_context` | ogni pagina, in head | vedi §2 | `mu-plugins/lidia-tracking.php` |
| `lidia_lead` | conferma di invio del modulo | `tipo`, `intento`, `documento` | `assets/js/modulo.js` — **già fatto** |
| `lidia_form_view` | il modulo entra nel viewport | `tipo` | `assets/js/tracking.js` |
| `lidia_form_start` | primo campo compilato | `tipo` | `assets/js/tracking.js` |
| `lidia_cta_click` | click su CTA primaria | `cta_testo`, `cta_destinazione`, `page_type` | `assets/js/tracking.js` |
| `lidia_download` | click sul link firmato del whitepaper | `documento` | `assets/js/tracking.js` |
| `lidia_outbound_click` | click su link esterno | `link_url`, `link_dominio` | `assets/js/tracking.js` |
| `lidia_contact_click` | click su `mailto:` o `tel:` | `contatto_tipo` | `assets/js/tracking.js` |
| `lidia_scroll_deep` | 75% di scroll sugli articoli e sui whitepaper | `page_type`, `content_title` | `assets/js/tracking.js` |

### Valori di `lidia_lead`

| Parametro | Valori |
|---|---|
| `tipo` | `prova` · `whitepaper` |
| `intento` | `prova` · `commerciale` · vuoto su `tipo: whitepaper` |
| `documento` | slug del whitepaper · vuoto su `tipo: prova` |

`lidia_lead` è **l'unico evento di conversione**. GA4, Meta, LinkedIn e Google Ads si agganciano
tutti a lui e distinguono con `tipo` e `intento`. Nessun pixel di conversione separato per canale.

> **GA4 sottostima sempre.** Consenso marketing rifiutato, ad blocker e JavaScript spento fanno sì
> che una parte dei lead arrivi in Delera senza generare una conversione misurata. Non è un difetto
> da correggere: la fonte di verità dei lead è il CRM. Si confrontano i due numeri una volta al mese
> e si usa il rapporto per leggere il costo per lead reale.

---

## 4. Markup: come si dichiara un evento

`tracking.js` non ha handler per pagina. Delega sugli attributi `data-track-*`, letti da `document`.

```html
<a href="/prova-gratuita/" data-track="cta" data-track-testo="Richiedi una prova gratuita">…</a>
```

| Attributo | A cosa serve |
|---|---|
| `data-track="cta"` | marca una CTA primaria |
| `data-track-testo` | etichetta leggibile, se diversa dal testo del link |
| `data-track="download"` | link firmato del whitepaper |

Link esterni, `mailto:` e `tel:` non richiedono attributi: `tracking.js` li riconosce dall'`href`.

**CTA automatiche (25/09/2026):** ogni bottone del tema (`.wp-block-button__link`) conta come CTA
senza attributi, così il team non deve ricordarsi di marcarle. `data-track="cta"` serve solo per un
link testuale che si vuole contare come CTA. Ordine di precedenza al click: download → contatto →
link esterno → CTA (il «Log in» verso l'applicazione è quindi un `lidia_outbound_click`). I link
firmati dei whitepaper portano già `data-track="download"` e `data-track-documento` (slug), dal
modulo in `inc/forms.php`.

---

## 5. Mappatura in GTM

| Evento dataLayer | GA4 | Meta | LinkedIn | Google Ads |
|---|---|---|---|---|
| `lidia_lead` | `generate_lead` | `Lead` | conversione, per `tipo` | conversione primaria su `intento: prova` |
| `lidia_cta_click` | stesso nome | — | — | — |
| `lidia_download` | stesso nome | — | — | conversione secondaria |
| gli altri | stesso nome | — | — | — |

Dimensioni personalizzate da creare in GA4 Admin, altrimenti i parametri arrivano e si perdono:
`page_language`, `page_type`, `tipo`, `intento`.

Naming in GTM: `[Canale] – [Tipo] – [Scopo]`. Trigger: `CE – lidia_lead`. Variabili: `DL – tipo`.

---

## 6. Consenso

| Categoria | Segnali Consent Mode | Tag |
|---|---|---|
| Necessari | `functionality_storage`, `security_storage` | nessun tag di marketing |
| Statistiche | `analytics_storage` | GA4 |
| Marketing | `ad_storage`, `ad_user_data`, `ad_personalization` | Meta, LinkedIn, Google Ads |

Ogni tag di marketing ha le sue **Additional consent checks** impostate in GTM. Nessun tag sempre
attivo tranne quelli tecnici.

`lidia_lead` viene spinto nel dataLayer **sempre**, anche a consenso rifiutato: il consenso governa i
pixel, non il funzionamento del modulo. Il lead arriva a Delera in ogni caso — è esecuzione
contrattuale, non profilazione.

---

## 7. Cosa non si fa

- Niente cookie scritti dal sito per catturare gli UTM: viaggiano in campi nascosti del modulo.
- Niente evento video: non ci sono video.
- Niente `event_id` per la deduplica pixel/CAPI finché non si decide dove gira la Conversions API.
- Niente GA4 installato anche via plugin: solo GTM.
