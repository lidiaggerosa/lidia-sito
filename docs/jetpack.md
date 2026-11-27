# Jetpack e connettore WordPress.com (solo produzione)

Serve solo a far pubblicare a Claude le modifiche piccole in produzione. Staging resta senza.

## Installazione (una volta)
1. **Account WordPress.com aziendale** (non personale): crealo su wordpress.com se non c’è.
2. **Plugin:** admin di lidiatech.ai → Plugin → Aggiungi nuovo → «Jetpack» → Installa → Attiva.
3. **Collegamento:** Jetpack → Configura → accedi con l’account aziendale → scegli il piano Free.
   Se il collegamento fallisce, di solito è XML-RPC bloccato: dimmelo e lo verifichiamo.
4. **Moduli spenti:** Jetpack → Impostazioni → spegni tutto (statistiche, Site Accelerator/CDN, condivisione, newsletter, articoli correlati). Restano solo collegamento e MCP.
5. **Permessi dell’agente:** Jetpack → Jetpack AI → «MCP and Connectors»
   - Read: Content Authoring, Site Editor Context
   - Write: solo Content Authoring (template, plugin, impostazioni spenti)
6. **Claude:** Impostazioni → Connettori → WordPress.com → Connetti → accedi con lo stesso account.

## Uso
- Claude legge la pagina, la aggiorna col contenuto del file in `pagine/`, poi segna `DISALLINEAMENTI.md` e `REGISTRO.md`.
- Ripristino: revisione precedente della pagina (admin → pagina → Revisioni).
- Registro attività dell’agente: Jetpack → Jetpack AI → MCP and Connectors.
- Spegnere tutto: stessa schermata → «Enable MCP access» off.

## Da fare dopo
- Privacy policy: Automattic tra i fornitori.
