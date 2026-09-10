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

### 2026-09-10 · Repository di distribuzione: pubblico, non privato
- **Decisione:** `formalife/plugin-guida-al-soffocamento` va reso **pubblico**, non
  privato. Il codice del plugin stesso lo presupponeva già ("Le release devono
  essere pubbliche", commento originale in `guida-antipanico-soffocamento.php`) —
  creato privato per default, questo avrebbe reso invisibile il repository a Plugin
  Update Checker (nessuna autenticazione configurata), con il sito WordPress
  convinto di essere sempre aggiornato anche quando non lo è, senza errori visibili.
- **Tipo:** sicurezza/bugfix.
- **Razionale:** nessun segreto nel repository (chiavi Stripe e webhook secret
  vivono solo nelle impostazioni WordPress, mai nel codice) — non c'è motivo di
  pagare la complessità di un token di lettura per un repository che non protegge
  nulla di sensibile.
- **Stato:** decisione presa, esecuzione (Settings → Danger Zone → Change
  visibility, azione di sola amministrazione repo) lasciata al proprietario — non
  richiede un token con scope "Administration" solo per questo.
- **`claude-web` resta privato**: contiene documentazione interna/strategica, non
  solo codice destinato a WordPress.

### 2026-09-10 · Lezione operativa: scope "Workflows" ≠ scope "Actions"
- **Trovato:** un token con permesso "Workflows: Read and write" basta per pushare
  file dentro `.github/workflows/`, ma **non** per interrogare via API lo stato delle
  esecuzioni (serve il permesso separato "Actions"). Verificato indirettamente il
  successo del primo rilascio controllando l'elenco assets della Release invece dei
  log della Action.
- **Tipo:** operativo — utile per la prossima volta che si genera un token.

### 2026-09-10 · Modifiche future al codice: Claude Code, non token in chat
- **Decisione:** le prossime modifiche a `claude-web` (e ai repository di
  distribuzione collegati) passeranno da **Claude Code**, autenticato una volta sola
  in locale (`gh auth login` o chiave SSH) su una copia clonata del repository — non
  da token incollati in questa chat ogni volta. Claude (in questa chat) non ha
  memoria persistente di credenziali tra conversazioni: ogni intervento diretto sulla
  repo da qui richiederebbe comunque un nuovo token, ad ogni sessione.
- **Tipo:** infrastruttura/metodo di lavoro.
- **Implicazione pratica:** `CLAUDE.md` alla radice di `claude-web` è il documento
  che guiderà quelle sessioni — tenerlo aggiornato è più importante ora che prima.

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

### 2026-09-10 · formalife-core invece di un plugin unico
- **Decisione:** invece di un plugin unico che contenga tutte le pagine di
  tutti i prodotti Formalife, creato `formalife-core`: un plugin
  "fondamenta" con token di design, componenti CSS generici e un helper per
  i pannelli impostazioni, di cui ogni plugin di prodotto (libro, corso, il
  prossimo) dichiara la dipendenza (`Requires Plugins`) invece di
  duplicarne il contenuto.
- **Tipo:** funzionalità/infrastruttura.
- **Razionale:** un plugin unico avrebbe accoppiato codice non correlato
  (bug nel pannello di un prodotto rischia di rompere il checkout Stripe di
  un altro, nello stesso file), reso i rilasci meno chiari (una versione,
  un changelog, per prodotti indipendenti), e reso più difficile lo
  scorporo futuro verso un'interfaccia/database diversi (es. Cloudflare),
  già previsto dal proprietario come possibile evoluzione.
- **Cosa migra e cosa no, nell'integrazione con guida-antipanico-soffocamento
  (v3.7.6):** vedi `docs/architettura-formalife-core.md` §3 per il
  dettaglio — riassunto: font e valori dei token migrati per intero, con
  fallback espliciti; le classi CSS del libro (`gaps-*`) NON rinominate in
  questa sessione, scelta deliberatamente conservativa data la natura live
  del plugin (paga con Stripe) e l'impossibilità di testare dal vivo in
  questa sessione.
- **Toccati:** nuovo plugin `plugins/formalife-core/` (v1.0.0).
  `guida-antipanico-soffocamento`: header `Requires Plugins`,
  `class-gaps-assets.php`, `assets/css/frontend.css` (solo il blocco token),
  rimossi `assets/css/fonts.css` e `assets/fonts/` — v3.7.6.
- **Non ancora fatto:** nessuna verifica dal vivo su un sito WordPress reale
  (nessun accesso in questa sessione) — prima cosa da controllare dopo il
  deploy: la landing del libro deve rendere identica a prima.
- **Distribuzione:** `formalife-core` non ha (ancora) un repository dedicato
  in stile Plugin Update Checker — per ora si installa a mano, come
  `claude-web` stesso. Deciso di non aggiungere infrastruttura finché non
  serve davvero (vedi `docs/architettura-formalife-core.md` §4).

### 2026-09-10 · formalife-homepage: secondo plugin registrato e migrato a formalife-core
- **Contesto:** ricevuto in chat lo zip `formalife-homepage-v4.6.3-meta-retry-visible.zip`
  (homepage istituzionale + landing/checkout del corso pratico "Genitori
  Pronti"), non ancora presente in `claude-web`. Prima registrazione completa
  in `plugins/formalife-homepage/` con relativo
  `docs/architettura-formalife-homepage.md`.
- **Decisione:** applicato lo stesso modello di integrazione con
  `formalife-core` già usato per `guida-antipanico-soffocamento` v3.7.6
  (header `Requires Plugins`, `formalife_core_enqueue()`/
  `formalife_core_output_font_preloads()` con guardia `function_exists()`,
  font locali rimossi in favore di quelli condivisi). **Differenza rispetto
  al libro:** questo plugin ha una palette colori propria e indipendente
  (blu/rosso/verde, non teal/terracotta) — aliasati ai token `--fmls-*`
  solo i valori realmente identici (font, raggi, larghezza massima,
  ink/white, i tre colori usati nella sola card "Il libro"), non l'intera
  palette. Dettaglio completo in `docs/architettura-formalife-homepage.md` §4.
- **Tipo:** funzionalità/infrastruttura.
- **Toccati:** `plugins/formalife-homepage/formalife-homepage.php` (header
  `Requires Plugins`, `FMH_VERSION`), `includes/class-fmh-assets.php`,
  `assets/css/frontend.css`, `assets/css/course.css` (rimossi
  `assets/css/fonts.css` e `assets/fonts/`), `readme.txt`. Plugin
  `formalife-homepage`, v4.6.3 → v4.6.4.
- **Non fatto deliberatamente (fuori perimetro di questa richiesta):**
  nessun Plugin Update Checker/repository di distribuzione dedicato aggiunto
  (creare un nuovo repository GitHub è un'azione esterna che richiede
  conferma esplicita — il plugin resta a installazione manuale, come
  formalife-core); nessun avviso "webhook secret mancante" aggiunto (il
  plugin non lo aveva già, a differenza del libro — non era nel perimetro
  della richiesta). Entrambi elencati come decisioni aperte in
  `docs/architettura-formalife-homepage.md` §8.
- **Discrepanza trovata e non toccata:** il codice sorgente spacchettato di
  `guida-antipanico-soffocamento` in `plugins/guida-antipanico-soffocamento/`
  risulta ancora alla v3.7.5 (senza l'integrazione formalife-core), mentre
  `guida-antipanico-soffocamento_v3_7_6.zip` nella stessa cartella contiene
  già la v3.7.6 migrata (usata come riferimento per capire il "modello" da
  replicare qui). Non risincronizzato in questa sessione: è un plugin che
  incassa pagamenti reali, fuori dal perimetro della richiesta — vedi già
  "Prossimi passi tecnici prioritari" punto 1 più sotto, tuttora valido.
- **Non ancora fatto:** nessuna verifica dal vivo su un sito WordPress reale
  (nessun accesso in questa sessione) — prima cosa da controllare dopo il
  deploy: homepage e landing corso devono rendere identiche a prima.

## B. Decisioni proposte, in attesa di conferma

### Migrazione completa delle classi CSS del libro a fmls-*?
- **Contesto:** oggi il libro usa ancora `gaps-btn-primary` ecc., non
  `fmls-btn-primary` — solo i *valori* dei token sono condivisi, non le
  *classi*. Migrare le classi eliminerebbe l'ultima duplicazione (le regole
  CSS dei componenti esistono scritte due volte, una per prefisso), ma
  richiede toccare ogni template HTML del plugin.
- **Stato:** non programmata, da fare quando c'è modo di verificarla dal
  vivo (Claude Code + un ambiente di staging, idealmente) invece che alla
  cieca in chat.

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

1. **Caricare `formalife-core`, `guida-antipanico-soffocamento` v3.7.6 e
   `formalife-homepage` v4.6.4 su WordPress** (attivare formalife-core PRIMA
   dei plugin che ne dipendono) e verificare che homepage, landing corso e
   landing libro rendano identiche a prima — nessuna modifica visiva è
   prevista, ma non è stata verificata dal vivo in questa sessione.
   **Nota:** il sorgente spacchettato in
   `plugins/guida-antipanico-soffocamento/` è ancora alla v3.7.5 (solo lo
   zip `guida-antipanico-soffocamento_v3_7_6.zip` nella stessa cartella è
   alla v3.7.6) — da risincronizzare prima o durante questo passaggio, dato
   che è il plugin che incassa i pagamenti reali.
2. **Rendere pubblico `formalife/plugin-guida-al-soffocamento`** (Settings → Danger
   Zone → Change visibility) — senza questo, l'aggiornamento automatico resta rotto
   in silenzio. Azione del proprietario, non richiede Claude.
3. **Collegare il sito WordPress**: verificare la versione live, caricare una volta
   sola lo zip più recente, forzare un controllo aggiornamenti, verificare il popup
   changelog. Vedi i passi dati in chat il 2026-09-10.
4. **Installare Claude Code**, autenticarlo con GitHub (`gh auth login` o SSH) e
   clonare `claude-web` in locale — da questo punto in avanti, le modifiche al
   codice passano di lì, non da token incollati in chat.
5. Registrare gli altri plugin Formalife ancora mancanti (nome/codice noti:
   mix di esistenti e da costruire) in `plugins/` appena disponibili, con
   relativo `docs/architettura-<slug>.md` — e decidere, per ciascuno, se/come
   useranno formalife-core. `formalife-homepage` registrato e migrato in
   questa sessione (2026-09-10).
6. Confermare il punto B sulla spedizione (flat vs per copia) e quello sulla
   migrazione delle classi CSS del libro.
