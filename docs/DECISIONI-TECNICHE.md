---
title: "Decisioni tecniche — Sviluppo Web & Plugin Formalife"
progetto: "Formalife, Sviluppo Web & Plugin"
tipo: "changelog di progetto"
status: "vivo (da aggiornare a ogni decisione tecnica)"
---

# Decisioni tecniche — Sviluppo Web & Plugin Formalife

Registro delle decisioni tecniche e architetturali prese sui plugin/siti Formalife.
Stesso spirito del `DECISIONI-VALIDATE.md` usato per le decisioni editoriali del
manuale: tenere traccia di **cosa** è stato deciso, **perché**, e **cosa tocca**, così
non si rispiega da zero ogni volta e non si ripetono errori già risolti.

**Convenzioni**
- *Tipo:* bugfix · funzionalità · sicurezza · copy-tecnico (testo che vive nel codice,
  non nella strategia di marketing).
- Ogni voce indica i **file/classi toccati**, così l'aggiornamento si propaga anche ad
  `ARCHITETTURA-PLUGIN-GAPS.md`.

---

## A. Decisioni validate

### 2026-09-10 · Bug: pagamenti Stripe non registrati come "Pagato"
- **Decisione:** il pannello impostazioni ora avvisa esplicitamente (avviso dedicato,
  visibile in tutta la bacheca) quando le chiavi Stripe sono configurate ma manca la
  chiave segreta del webhook — causa più comune del sintomo "il cliente ha pagato ma
  in bacheca risulta ancora in attesa".
- **Tipo:** bugfix/sicurezza.
- **Toccati:** `class-gaps-settings.php`, `class-gaps-admin-notices.php`. Plugin
  `guida-antipanico-soffocamento`, v3.7.1.
- **Nota:** questo avviso non deve mai sparire in refactoring futuri — è la rete di
  sicurezza contro il bug più subdolo del flusso di pagamento (vedi
  `ARCHITETTURA-PLUGIN-GAPS.md` §6).

### 2026-09-10 · Copy: da "prevendita" a vendita normale
- **Decisione:** rimossi scadenza prenotazioni e riferimenti a "prevendita" da
  landing, Condizioni di vendita, Privacy Policy, readme del plugin. Nessuna nuova
  scadenza inventata al posto della vecchia.
- **Tipo:** copy-tecnico.
- **Toccati:** `template-landing.php`, `gaps-legal-content.php`, `readme.txt`. Plugin
  `guida-antipanico-soffocamento`, v3.7.1.

### 2026-09-10 · Spedizione a pagamento (2,90 €, flat per ordine)
- **Decisione:** la spedizione non è più gratuita. Nuovo campo impostazioni
  `shipping_price` (default 2,90 €), addebitato **una sola volta per ordine** (non per
  copia) insieme al prezzo del libro, nello stesso PaymentIntent Stripe.
- **Tipo:** funzionalità + copy-tecnico.
- **Razionale:** costo reale di spedizione non più assorbito da Formalife.
- **Toccati:** `gaps-settings-helpers.php` (`gaps_get_shipping_cents()`),
  `class-gaps-preorder.php` (`create_payment_intent()`), `class-gaps-assets.php`
  (localizzazione JS), `frontend.js` (`updateTotal()`), `template-landing.php` (nota
  soft nello step 3), `template-thankyou.php`, `class-gaps-stripe-webhook.php` (email),
  `gaps-legal-content.php` (artt. 3 e 6). Plugin `guida-antipanico-soffocamento`,
  v3.7.2.
- **Vincolo di presentazione:** il costo di spedizione **non** compare mai nella
  landing page (né come "gratuita" né con l'importo) — solo nello step di pagamento
  del popup, con tono discreto, insieme ai tempi di consegna.

### 2026-09-10 · Tempi di consegna: "in 4-5 giorni lavorativi"
- **Decisione/conferma:** aggiornato il default del campo `date_delivery` da una data
  di prevendita ormai passata a un testo stabile e riutilizzabile.
- **Tipo:** copy-tecnico.
- **Toccati:** `gaps-settings-helpers.php`. Plugin `guida-antipanico-soffocamento`,
  v3.7.2.

---

### 2026-09-10 · Repository GitHub degli aggiornamenti: nome corretto e verificato
- **Decisione:** la costante `GAPS_UPDATE_REPOSITORY` puntava a
  `github.com/formalife/plugin-guida-al-soffocamento`; verificato direttamente (fetch
  del repo pubblico) che vive su `github.com/formalife-personal/plugin-guida-al-soffocamento`.
  Costante e header "Update URI" aggiornati di conseguenza.
- **Tipo:** bugfix.
- **Scoperta aggiuntiva, non ancora risolta:** il repository è fermo alla v3.5.1 — le
  versioni 3.6.0→3.7.2 (bug webhook, copy vendita, spedizione) esistono solo negli
  zip scambiati in chat, mai sincronizzate col repository. Nessuna cartella
  `.github/workflows`: nessuna automazione genera lo zip per le Release. Workflow
  `build-release.yml` scritto e consegnato, ma non ancora inserito nel repository né
  testato su una Release reale.
- **Toccati:** `guida-antipanico-soffocamento.php`. Plugin
  `guida-antipanico-soffocamento`, v3.7.3.

### 2026-09-10 · Repository ricreato da zero su formalife/claude-web
- **Decisione:** invece di sincronizzare/riparare il vecchio repository
  (`formalife-personal/plugin-guida-al-soffocamento`, fermo alla v3.5.1), se ne crea
  uno nuovo, `github.com/formalife/claude-web`, pensato come monorepo di
  coordinamento per tutto lo sviluppo web Formalife (non solo questo plugin).
- **Tipo:** funzionalità/infrastruttura.
- **Struttura:** il plugin resta alla **radice** del repository (non in una
  sottocartella `plugins/<nome>/`): verificato leggendo la libreria Plugin Update
  Checker vendorizzata che il fetch del changelog (`readme.txt`) cerca sempre la
  radice del repo, senza supporto per sottocartelle. `docs/` e
  `.github/workflows/` convivono alla radice accanto al codice del plugin.
  Dettaglio tecnico in `docs/architettura-guida-antipanico-soffocamento.md`, §9.
- **Superata dalla decisione sopra:** la voce precedente "Repository GitHub degli
  aggiornamenti: nome corretto e verificato" (2026-09-10, mattina) — quel repository
  non è più quello in uso.
- **Toccati:** `guida-antipanico-soffocamento.php` (`Update URI`,
  `GAPS_UPDATE_REPOSITORY`). Plugin `guida-antipanico-soffocamento`, v3.7.4. Nuovi
  file di repository: `README.md`, `CLAUDE.md`, `.github/workflows/build-release.yml`.

### 2026-09-10 · Modello a due livelli: coordinamento vs distribuzione
- **Contesto:** la decisione precedente (stesso giorno, poche ore prima) presumeva un
  solo plugin per ora, e rimandava la scelta "sottocartella vs repository separato" a
  quando un secondo plugin fosse esistito davvero. Chiesto al proprietario, è emerso
  che **esistono già altri plugin Formalife** (alcuni già scritti altrove, altri da
  costruire, meccanismo di aggiornamento non ancora deciso per gli altri, tutti sullo
  stesso sito WordPress) — la premessa "per ora uno solo" era sbagliata.
- **Decisione:** `claude-web` resta il workspace di coordinamento, con ogni plugin
  nella sua sottocartella `plugins/<slug>/`. Per l'aggiornamento automatico via
  GitHub, ogni plugin che lo adotta ha invece un **repository di distribuzione
  dedicato** (solo quel plugin, alla radice — dove Plugin Update Checker riesce a
  leggere il changelog), sincronizzato da `claude-web` con
  `scripts/sync-plugin-release.sh` al momento del rilascio.
- **Tipo:** funzionalità/infrastruttura.
- **Razionale:** evita di dover scegliere, per ogni singolo plugin, se sacrificare il
  coordinamento (repository separato, isolato) o il changelog (sottocartella in un
  monorepo puro) — non serve più scegliere, si tengono entrambi.
- **Toccati:** `guida-antipanico-soffocamento.php` (`Update URI` →
  `github.com/formalife/plugin-guida-al-soffocamento`, `GAPS_UPDATE_REPOSITORY`).
  Plugin `guida-antipanico-soffocamento`, v3.7.5. Repository: `claude-web` (README,
  CLAUDE.md, `scripts/sync-plugin-release.sh`); nuovo repository di distribuzione
  `formalife/plugin-guida-al-soffocamento` (contiene `.github/workflows/build-release.yml`).
- **Superata dalla decisione sopra:** la voce "Repository ricreato da zero su
  formalife/claude-web" (stesso giorno, mattina) nella parte relativa
  all'aggiornamento automatico — il resto (coordinamento in claude-web) resta valido.

## B. Decisioni proposte, in attesa di conferma

### Nome e contenuto degli altri plugin Formalife
- **Contesto:** confermato che esistono già altri plugin (mix: alcuni scritti,
  altri no), tutti sullo stesso sito WordPress, meccanismo di aggiornamento non
  ancora deciso per quelli mancanti.
- **Stato:** in attesa di nome/codice/descrizione per registrarli in
  `claude-web/plugins/` e decidere, uno per uno, se gli serve un repository di
  distribuzione dedicato.

### La spedizione scala con la quantità di copie?
- **Assunzione applicata:** no, è flat per ordine (un unico pacco anche con più copie).
- **Stato:** non confermato esplicitamente dal proprietario — verificare.
- **Toccati se cambia:** `class-gaps-preorder.php` (`create_payment_intent()`), la
  riga `$shipping_cents = gaps_get_shipping_cents();` andrebbe moltiplicata per
  `$quantita`.

---

## C. Prossimi passi tecnici prioritari

1. **Popolare `github.com/formalife/claude-web`**: `plugins/guida-antipanico-soffocamento/`
   (contenuto v3.7.5), `docs/`, `CLAUDE.md`, `README.md`, `scripts/sync-plugin-release.sh`
   — file già pronti, da inserire.
2. **Creare `github.com/formalife/plugin-guida-al-soffocamento`** (repository di
   distribuzione dedicato, vuoto per ora), inserire lo stesso contenuto v3.7.5 alla
   radice + `.github/workflows/build-release.yml`, pubblicare la prima Release
   (`v3.7.5`) e verificare che il workflow generi e alleghi correttamente lo zip.
3. **Registrare gli altri plugin Formalife** appena nome/codice sono disponibili:
   cartella in `plugins/`, scheletro di `docs/architettura-<slug>.md`, voce in questo
   registro.
4. Confermare il punto B sulla spedizione (flat vs per copia).
5. Installare Claude Code puntato sul clone locale di `claude-web`, per le prossime
   sessioni di editing vero e proprio.
