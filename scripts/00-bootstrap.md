# scripts/00-bootstrap — configurazione del sito "Lidia 2026"

> Questi comandi **sono** la configurazione del sito. Non si configura nulla dal pannello di
> WordPress: si aggiunge una riga qui e si riesegue.
>
> Vengono lanciati con lo strumento WP-CLI di WordPress Studio (`wp_cli`) sul sito `Lidia 2026`.
> Sono scritti per essere **rieseguibili**: rilanciarli su un sito già configurato non rompe nulla.
>
> Perché esistono: il database locale è SQLite e non è trasferibile su SiteGround
> (`docs/tecnico/ambienti-e-rilascio.md` §3bis). La ricostruzione del sito è la riesecuzione di questi script.

---

## 1. Lingua, identità, fuso

```
core language install it_IT --activate
option update blogname "Lidia"
option update blogdescription "Non risponde. Pensa con te."
option update timezone_string "Europe/Rome"
option update start_of_week 1
option update date_format "j F Y"
option update time_format "H:i"
```

## 2. Permalink

Struttura `/%postname%/`. Da rilanciare ogni volta che si registra un CPT o si cambia uno slug,
altrimenti i nuovi URL rispondono 404.

```
rewrite structure "/%postname%/" --hard
rewrite flush --hard
```

## 3. Commenti e discussione: spenti

Un sito B2B corporate non ha commenti. Lasciarli aperti significa moderazione e spam senza
alcun ritorno.

```
option update default_comment_status "closed"
option update default_ping_status "closed"
option update comment_registration 1
option update close_comments_for_old_posts 1
option patch update discussion_settings 2>/dev/null || true
```

## 4. Pulizia del contenuto di default

WordPress installa un post, una pagina di esempio e una privacy in bozza. Vanno via: se restano,
finiscono indicizzati o confondono l'inventario delle pagine.

```
post delete $(post list --post_type=post --post_status=publish,draft --name=hello-world --format=ids) --force
post delete $(post list --post_type=page --name=sample-page --format=ids) --force
post delete $(post list --post_type=page --name=privacy-policy --post_status=draft --format=ids) --force
comment delete $(comment list --format=ids) --force
```

Se un comando non trova nulla, restituisce un errore innocuo: il contenuto era già stato rimosso.

## 5. Tema

```
theme activate lidia
theme list --format=csv --fields=name,status,version
```

**Prerequisito:** la junction deve esistere (`docs/00-setup-progetto.md` §5.2), altrimenti il
tema non è visibile e `theme activate` fallisce.

Dopo l'attivazione, i temi di default non servono e sono superficie d'attacco in meno:

```
theme delete twentytwentyfive twentytwentyfour twentytwentythree
```

## 6. Indicizzazione: bloccata in locale

Il sito locale non è raggiungibile da fuori, ma la costante vale anche su staging: meglio
l'abitudine giusta.

```
option update blog_public 0
```

**Da riportare a `1` solo in produzione, al cutover.** È nella checklist di Fase 8.

## 7. Plugin

Nessun plugin viene installato a mano. Ogni riga qui corrisponde a una voce nel registro di
`docs/09-decision-log.md`.

```
plugin install wordpress-seo --activate
plugin delete akismet hello
plugin list --format=csv --fields=name,status,version
```

Decisi ma non ancora installati (si installano nella fase in cui servono, con la riga qui):

- **Polylang** — multilingua, Fase 4 (deciso l'11/09)
- **Complianz** — CMP e Consent Mode v2, Fase 5 (deciso il 16/09)
- **SG Optimizer** — solo su SiteGround, Fase 8 (deciso il 18/09)

## 8. Utenti

L'admin di Studio resta per lo sviluppo. In produzione il marketing avrà ruolo `editor`, non
`administrator` (`docs/tecnico/ambienti-e-rilascio.md` §8).

```
user list --format=csv --fields=ID,user_login,user_email,roles
option update admin_email "marketing@lidiatech.ai"
```

## 9. Verifica finale

```
option get blogname
option get permalink_structure
option get blog_public
theme list --status=active --format=csv
plugin list --status=active --format=csv
core version
```

---

## Cosa NON sta in questo script

| Cosa | Dove sta |
|---|---|
| Creazione delle pagine | `scripts/01-home.md` … `08-legale.md`, uno per pagina o gruppo di pagine, dai file di `scripts/blocchi/` |
| CPT, tassonomie, campi custom | nel tema (`inc/post-types.php`, `inc/meta.php`) — sono codice, non configurazione |
| Costanti di ambiente (`LIDIA_ENV`, `LIDIA_GTM_ID`, `LIDIA_DELERA_WEBHOOK`) | `wp-config.php` del sito, mai nel progetto |
| Menu di navigazione | Fase 3, come parte dell'header |
| Impostazioni di Yoast | `scripts/09-yoast.md` (Fase 7), per non configurarlo a mano |
