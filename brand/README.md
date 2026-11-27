# brand/ — asset di marca

Materia prima, non build. Il tema non legge da qui: in Fase 2 i `woff2` vengono copiati in
`theme/lidia/assets/fonts/` e il logo in `theme/lidia/assets/images/`. Questa cartella resta
la fonte, il tema la copia.

| Cartella | Cosa ci va |
|---|---|
| `logo/` | logo Lidia vettoriale (`.svg`, e `.ai`/`.eps` se ci sono): versione principale, monocromatica, negativa per fondo scuro, favicon |
| `font/` | Fraunces e Inter in `.woff2`. Entrambi **SIL Open Font License**: gratuiti, self-hosting permesso, nessun file di licenza da procurare. Si tiene solo copia del testo OFL accanto ai font |
| `loghi-clienti/` | loghi dei clienti da esporre. Tutti autorizzati; si raccolgono quando esiste la pagina che li usa |

Nota: `.gitignore` esclude `*.zip`. Scompatta gli archivi dei font, non committarli zippati.
