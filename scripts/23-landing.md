# 23 — Landing di campagna (/lp/)

Landing isolate per Google Ads: tipo di contenuto `landing` (`inc/landing.php`), template
`single-landing`, testata e footer ridotti, `noindex, nofollow`, fuori sitemap. Rieseguibile:
se la landing esiste si aggiorna, non se ne crea una seconda.

| Landing | File di blocchi | Titolo | Slug | Gruppi Ads |
|---|---|---|---|---|
| Studi e avvocati | `lp-ai-legale-studi.html` | L’AI legale che lavora con te. | `ai-legale-studi` | 1, 6 |
| Ricerca giuridica | `lp-ricerca-giuridica.html` | La ricerca giuridica che ragiona come un professionista. | `ricerca-giuridica` | 2 |
| Attività legali | `lp-attivita-legali.html` | Dalla ricerca alla bozza, un unico processo. | `attivita-legali` | 3, 4 |

Copy approvato: `content/it/lp-*.md`.

## Crea

Dopo aver caricato il tema (il tipo `landing` deve esistere):

```
wp rewrite flush
wp post create "…\scripts\blocchi\lp-ai-legale-studi.html" --post_type=landing --post_status=publish --post_title="L’AI legale che lavora con te." --post_name=ai-legale-studi --porcelain
wp post create "…\scripts\blocchi\lp-ricerca-giuridica.html" --post_type=landing --post_status=publish --post_title="La ricerca giuridica che ragiona come un professionista." --post_name=ricerca-giuridica --porcelain
wp post create "…\scripts\blocchi\lp-attivita-legali.html" --post_type=landing --post_status=publish --post_title="Dalla ricerca alla bozza, un unico processo." --post_name=attivita-legali --porcelain
```

Su staging e produzione, dopo lo `scp` del file in `~/scripts/blocchi/`: stesso comando con il
percorso remoto, poi `wp sg purge`.

## Aggiorna

```
wp post list --post_type=landing --fields=ID,post_name --format=csv
wp post update <ID> "…\scripts\blocchi\lp-attivita-legali.html"
```

## Verifica

- `/lp/attivita-legali/` risponde 200, `/lp/` 404;
- nel sorgente `noindex, nofollow`; la landing non compare in `/sitemap_index.xml`;
- testata con solo logo e bottone, footer con la sola riga legale;
- invio di prova con `?utm_source=test&utm_campaign=test&utm_content=gruppo-3`: lead in Delera con
  gli UTM, `lidia_lead` / `generate_lead` in Tag Assistant.

## URL finale degli annunci

```
https://lidiatech.ai/lp/<slug>/?utm_source=google&utm_medium=cpc&utm_campaign=<campagna>&utm_content=gruppo-<n>&utm_term={keyword}
```

Gruppi 1 e 6 → `ai-legale-studi` · gruppo 2 → `ricerca-giuridica` · gruppi 3 e 4 → `attivita-legali` · gruppo 5 (Brand) → home.
