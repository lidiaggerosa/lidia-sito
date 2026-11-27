# Disallineamenti staging ↔ produzione

Per riallineare lo staging: `.\strumenti\pubblica.ps1 -Riallinea`. Porta su staging tutte le voci della prima tabella e la svuota.

## In produzione, non su staging
Le righe le scrive `pubblica.ps1`; per i rilasci col connettore le scrive Claude.
Tipo: `page`, `landing`, `webinar`, `tema`, `yoast`. File: blocchi in `pagine/`.

| Data | Tipo | Voce | File | Nota |
|---|---|---|---|---|

## Solo su staging (lavori non pubblicati)

| Voce | Nota |
|---|---|
| Offerte: legal-ai-os, ai-managed-service, transfer-pricing | in lavorazione |

## Altre differenze (configurazione, plugin)

| Voce | Staging | Produzione |
|---|---|---|
| Iscrizione automatica Teams | costanti Teams presenti | spenta (`LIDIA_TEAMS_TENANT_ID` tolta il 26/11) |
| Versioni plugin | più indietro | più avanti (autoupdate SiteGround) |
| Jetpack (connettore WordPress.com) | assente | da installare |
