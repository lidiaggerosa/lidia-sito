# Importare il container GTM

File: `tracking/gtm-container.json` (generato il 25/09/2026). Gli ID reali non stanno qui: si inseriscono in GTM.

## Importazione

1. GTM → container di lidiatech.ai → **Amministrazione → Importa container**.
2. File: `gtm-container.json`. Area di lavoro: **Nuova** («Sito nuovo – 25/09»).
3. Opzione: **Unisci** → **Rinomina tag, trigger e variabili in conflitto**. Mai «Sovrascrivi».
4. **Variabili → Const – ***: sostituire i segnaposto con gli ID veri (GA4, Pixel Meta, LinkedIn Partner e Conversion ID, Google Ads ID ed etichette).
5. Tag del sito Joomla: sospenderli (non cancellarli) se duplicano GA4, Meta, LinkedIn o Ads. Doppioni = conversioni contate due volte.
6. Non pubblicare prima del cutover: si pubblica quando il sito nuovo è in produzione.

## Cosa contiene

| Tag | Trigger | Consenso |
|---|---|---|
| GA4 – Google tag – Configurazione | Initialization – All Pages | integrato (Consent Mode) |
| GA4 – Event – generate_lead | CE – lidia_lead | integrato |
| GA4 – Event – eventi del sito | CE – eventi del sito | integrato |
| Meta – Base Pixel | CE – lidia_consent_update – marketing | ad_storage, ad_user_data |
| Meta – Lead | CE – lidia_lead | ad_storage, ad_user_data |
| LinkedIn – Insight Tag | CE – lidia_consent_update – marketing | ad_storage, ad_user_data |
| LinkedIn – Conversion Lead | CE – lidia_lead | ad_storage, ad_user_data |
| Ads – Conversion Linker | Initialization – All Pages | integrato |
| Ads – Conversion – Lead prova | CE – lidia_lead – prova | integrato |
| Ads – Conversion – Download whitepaper | CE – lidia_download | integrato (secondaria) |

## Da fare fuori da GTM

- **GA4:** dimensioni personalizzate `page_language`, `page_type`, `tipo`, `intento`, `documento`; `generate_lead` come evento chiave.
- **LinkedIn:** conversione «Lead» con metodo «evento», il suo ID in `Const – LinkedIn Conversion ID`.
- **Google Ads:** due conversioni (Lead prova primaria, Download secondaria), ID ed etichette nelle costanti.

## Dopo ogni pubblicazione

Esportare il container e sostituire questo file: la versione in repo è quella in GTM.
