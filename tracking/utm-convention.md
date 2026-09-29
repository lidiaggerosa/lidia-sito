# Convenzione UTM — Lidia

Da rispettare su **ogni** link di campagna, senza eccezioni: l'attribuzione in GA4 e in Delera
dipende interamente da questa coerenza.

| Parametro | Valori ammessi | Esempio |
|---|---|---|
| `utm_source` | `linkedin` · `google` · `meta` · `mailchimp` · `dem` · `qr` | `linkedin` |
| `utm_medium` | `cpc` · `paid-social` · `email` · `organic-social` · `referral` · `offline` | `paid-social` |
| `utm_campaign` | `{anno}-{trimestre}-{verticale}-{tema}` | `2026-q4-studi-legali-word-addin` |
| `utm_content` | variante creativa, in kebab-case | `carousel-a` |
| `utm_term` | keyword — solo campagne search | `software+gestione+contratti` |

Regole:

- tutto in **minuscolo**, separatori `-` (non `_`, non spazi);
- `utm_campaign` identifica la campagna, **non** l'annuncio: la variante sta in `utm_content`;
- i verticali usano gli stessi slug del sito: `studi-legali`, `direzioni-legali`,
  `assicurazioni`, `atenei`;
- niente UTM sui link **interni** al sito (azzerano la sessione originale in GA4);
- i click ID (`gclid`, `fbclid`, `li_fat_id`, `msclkid`) arrivano dalle piattaforme: non aggiungerli a mano;
- ogni campagna registra i suoi URL completi in un foglio condiviso prima del lancio.

Costruttore consigliato: un foglio con formula di concatenazione, non a mano.
