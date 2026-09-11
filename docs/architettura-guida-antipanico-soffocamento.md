---
title: "Architettura — plugin guida-antipanico-soffocamento"
progetto: "Formalife, Sviluppo Web & Plugin"
tipo: "riferimento tecnico"
status: "vivo (aggiornare ad ogni modifica strutturale)"
versione_plugin_al: "3.7.8"
ultimo_aggiornamento: "2026-09-11"
---

# Architettura — plugin `guida-antipanico-soffocamento`

Mappa tecnica del plugin che gestisce landing, checkout Stripe e pagine legali del
libro "La Guida Anti-Panico al Soffocamento Pediatrico". Obiettivo: chi riprende il
lavoro (umano o Claude) capisce in due minuti dove intervenire, senza rileggere tutto
il codice da capo.

**Dipendenza (dalla v3.7.8):** questo plugin richiede `formalife-core` attivo
(header `Requires Plugins`). Token di colore/font/spaziatura e le
dichiarazioni `@font-face` vengono da lì — vedi
`docs/architettura-formalife-core.md` §3 per il dettaglio di cosa è migrato
e cosa no. La dipendenza era stata costruita in uno zip già alla v3.7.6
(2026-09-10) ma non applicata al sorgente coordinato fino a questa
versione, nell'ambito di un audit esplicito di coerenza dei token richiesto
dal proprietario su tutti i plugin — vedi `DECISIONI-TECNICHE.md`
(2026-09-11). La 3.7.7 (sicurezza uninstall) era partita apposta dalla 3.7.5
per non introdurre questa modifica in una sessione dedicata a un incidente
di sicurezza; applicata ora in 3.7.8.

**Se stai per modificare qualcosa che non trovi descritto qui, il documento è
disallineato dal codice reale allegato in conversazione: fidati del codice, poi
aggiorna questo file.**

---

## 1. Cos'è, in una riga
Plugin WordPress custom (nessun framework/page-builder per la logica) che genera 5
pagine pubbliche e gestisce un checkout con pagamento Stripe incorporato (Payment
Element), senza redirect esterni.

## 2. Le 5 pagine generate automaticamente
| Slug | Contenuto | Template |
|---|---|---|
| `guida-antipanico-soffocamento` | Landing del libro (12+ sezioni) + popup di acquisto | `templates/template-landing.php` |
| `condizioni-di-vendita` | Condizioni di vendita (Codice del Consumo) | `includes/gaps-legal-content.php` |
| `privacy` | Privacy Policy (GDPR) | `includes/gaps-legal-content.php` |
| `grazie-ordine-confermato` | Pagina di ringraziamento post-pagamento (redirect automatico via `return_url` di Stripe, non configurato su Stripe) | `templates/template-thankyou.php` |
| `i-miei-numeri` | Download scheda PDF "I tuoi numeri importanti" (QR nel libro, pp. 157-158) | `templates/template-numeri.php` |

Gestione URL/slug centralizzata in `class-gaps-page-manager.php` (`GAPS_Page_Manager`).

## 3. File/classi principali e responsabilità
| File | Responsabilità |
|---|---|
| `guida-antipanico-soffocamento.php` | Bootstrap plugin, costanti (`GAPS_VERSION`, `GAPS_SLUG`, `GAPS_OPTION_KEY`...), include dei file. |
| `includes/class-gaps-settings.php` | Pannello impostazioni in bacheca (Bacheca → Guida Anti-Panico): render dei campi, `sanitize()` di tutto ciò che viene salvato, avvisi di stato (chiavi Stripe/webhook mancanti). |
| `includes/gaps-settings-helpers.php` | `gaps_default_settings()`, `gaps_get_settings()` (merge salvato+default), helper di conversione prezzo→centesimi (`gaps_get_price_cents()`, `gaps_get_shipping_cents()`), `gaps_has_cta_destination()`. **Unica fonte di verità per ogni importo.** |
| `includes/class-gaps-preorder.php` | CPT `gaps_preorder` ("Preordini ricevuti" — la bacheca/dashboard degli ordini), gestione submit del form (AJAX `gaps_submit_preorder`), creazione del PaymentIntent Stripe, colonne/filtri della lista admin, meta box dettaglio ordine. |
| `includes/class-gaps-stripe-webhook.php` | Endpoint REST pubblico che riceve gli eventi Stripe, verifica la firma (`Stripe-Signature`, tolleranza 300s), aggiorna lo stato pagamento, invia le email (cliente + notifica interna). |
| `includes/class-gaps-assets.php` | Enqueue CSS/JS, localizzazione dell'oggetto JS `gapsFrontend` (prezzi in centesimi, URL, nonce). Dalla v3.7.8 delega font/token a `formalife_core_enqueue()` (guardia `function_exists()`) invece di caricare `assets/css/fonts.css` in proprio. |
| `includes/class-gaps-admin-notices.php` | Avvisi globali in bacheca (non solo nella pagina impostazioni): chiavi Stripe mancanti, **webhook secret mancante** (il check più importante, vedi §6). |
| `includes/gaps-legal-content.php` | Contenuto di Condizioni di vendita e Privacy Policy, generato dalle impostazioni (prezzo, spedizione, garanzia...). |
| `templates/template-landing.php` | Markup della landing + popup di acquisto (step 1-2-3). |
| `templates/template-thankyou.php` | Markup pagina "Grazie". |
| `assets/js/frontend.js` | Navigazione a step del popup, calcolo totale live, creazione PaymentIntent, `stripe.confirmPayment()`. |
| `assets/js/gaps-stripe-loader.js` | Caricamento lazy di Stripe.js (solo al primo click su un CTA). |

## 4. Sistema impostazioni
Tutte le impostazioni passano da `gaps_get_settings()` (mai `get_option()` diretto),
che fa `wp_parse_args( $saved, gaps_default_settings() )`. Aggiungere un nuovo campo:
1. Aggiungilo a `gaps_default_settings()` in `gaps-settings-helpers.php`.
2. Aggiungilo al ciclo/blocco `sanitize()` in `class-gaps-settings.php`.
3. Aggiungi il campo nel form (`render_page()`, stessa classe).
4. Se è un importo: crea/riusa un `gaps_get_..._cents()` come unica fonte di verità.

Campi economici attuali: `price` (19,90 €), `shipping_price` (2,90 €, fissa per
ordine, non moltiplicata per copie), `date_delivery` ("in 4-5 giorni lavorativi",
testo libero mostrato ovunque si parli di tempi di consegna).

## 5. Flusso di pagamento (l'unico che conta davvero)
1. Cliente compila step 1-2, arriva allo step 3 → JS chiama l'AJAX che crea un
   **PaymentIntent** Stripe per `gaps_get_price_cents() × quantità + gaps_get_shipping_cents()`,
   con `metadata[wp_preorder_id]` = ID del post CPT.
2. Stripe Payment Element si monta nel popup; `stripe.confirmPayment()` con
   `return_url` = pagina "Grazie" (redirect automatico, **niente da configurare sul
   lato Stripe per questo passaggio**).
3. **La conferma "pagato" non arriva mai dal browser**: arriva solo dal webhook
   (`payment_intent.succeeded`), che legge `metadata.wp_preorder_id`, verifica di non
   aver già segnato l'ordine come pagato, aggiorna lo stato e invia le email.

## 6. Il checkpoint critico: webhook secret
**Se un pagamento va a buon fine su Stripe ma in "Preordini ricevuti" resta su "In
attesa di pagamento" per sempre**, la causa è quasi sempre una sola: la chiave segreta
del webhook (`stripe_webhook_secret`) non è impostata o il webhook non è registrato
lato Stripe (Sviluppatori → Webhook → endpoint + eventi
`payment_intent.succeeded`/`payment_intent.payment_failed`). Le chiavi API (publishable
+ secret) bastano per **far pagare**, non per **far sapere a WordPress che si è
pagato** — sono due configurazioni distinte. Dalla v3.7.1 c'è un avviso dedicato in
bacheca proprio per questo scenario (`class-gaps-admin-notices.php`); se in futuro
sparisce o smette di comparire, è una regressione da correggere subito.

## 6bis. Sicurezza alla disinstallazione (dalla v3.7.7)

`uninstall.php` cancella impostazioni (`gaps_settings` — comprese le chiavi
Stripe) e le pagine generate, ma **solo se** `gaps_settings['allow_uninstall_wipe']`
è esplicitamente `true` (checkbox "Cancella i dati alla disinstallazione" in
impostazioni, falsa di default). Se non è spuntata, `uninstall.php` esce
subito (`return;`) senza toccare nulla.

**Motivo:** vedi lo stesso meccanismo su `formalife-homepage` §6bis del suo
documento di architettura, e `DECISIONI-TECNICHE.md` (2026-09-11) — un
aggiornamento fatto eliminando il plugin attivo per caricarne uno più
recente (necessario perché WordPress non sovrascrive un plugin già
installato via upload) ha innescato `uninstall.php` su `formalife-homepage`
e cancellato dati reali. Questo plugin aveva lo stesso identico pattern
(nessuna guardia), corretto qui per prevenzione anche se l'incidente non lo
ha coinvolto direttamente. **Nota:** a differenza di `formalife-homepage`,
questo `uninstall.php` non cancella CPT di ordini (`gaps_preorder` non è
tra le cose rimosse) — solo impostazioni e pagine.

## 7. Changelog (sintesi — dettaglio completo in `readme.txt` del plugin)
- **3.7.8** — Applicata al sorgente coordinato la dipendenza `formalife-core`
  costruita in 3.7.6 (token colore/font/spaziatura e font @font-face
  migrati lì, con fallback espliciti — zero cambiamento visivo previsto;
  classi CSS proprie `gaps-*` non toccate, vedi
  `docs/architettura-formalife-core.md` §3). Audit di coerenza token: il
  giallo hardcoded di `mark.gaps-legal-todo` in `legal.css` ora referenzia
  `--gaps-yellow-light` invece di un valore scritto a mano identico.
- **3.7.7** — Sicurezza: `uninstall.php` richiede consenso esplicito
  (`allow_uninstall_wipe`, falso di default) prima di cancellare
  impostazioni/pagine — vedi §6bis. Partita direttamente dalla 3.7.5 (la
  dipendenza formalife-core, già costruita in 3.7.6, applicata poi in 3.7.8).
- **3.7.6** — Costruita come zip, non applicata al sorgente coordinato fino
  alla 3.7.8 (vedi sopra). Nuova dipendenza `formalife-core`: token colore/font/spaziatura
  e font @font-face migrati lì (con fallback espliciti, zero cambiamento
  visivo previsto). Classi CSS proprie (`gaps-*`) non toccate — vedi
  `docs/architettura-formalife-core.md` §3.
- **3.7.5** — Corretto il modello di repository: coordinamento in `claude-web`
  (§9), aggiornamento automatico spostato su un repository di distribuzione
  dedicato (`formalife/plugin-guida-al-soffocamento`), a seguito della scoperta che
  esistono già altri plugin Formalife da coordinare nello stesso workspace.
- **3.7.4** — Repository ricreato da zero su `github.com/formalife/claude-web`,
  come monorepo di coordinamento (docs/ + workflow di release accanto al plugin,
  che resta alla radice — vedi §9). Aggiornata la sorgente degli aggiornamenti
  automatici di conseguenza.
- **3.7.3** — Corretto l'URL del repository GitHub nell'aggiornatore automatico (era `formalife/...`, corretto `formalife-personal/...`, verificato via fetch diretto).
- **3.7.2** — Spedizione a pagamento (2,90 €, flat per ordine); rimossa ogni dicitura
  "spedizione gratuita"; nota discreta nello step 3 del popup (nessuna menzione di
  prezzo spedizione in landing, per scelta esplicita).
- **3.7.1** — Corretto il bug del §6 (avviso webhook mancante); copy da prevendita a
  vendita normale (landing, Condizioni di vendita, Privacy); rimossa scadenza
  prenotazioni ormai passata.

## 8. Decisioni aperte / da confermare col proprietario
- La spedizione scala con la quantità di copie, o resta sempre flat per ordine? Al
  momento: **flat**, assunzione non ancora confermata esplicitamente.
- Nome/contenuto degli altri plugin Formalife da registrare in `claude-web` — da
  aggiungere non appena disponibili (codice e/o descrizione).
- Se/quando migrare le classi CSS proprie (`gaps-*`) a `fmls-*` per intero
  (vedi `docs/architettura-formalife-core.md` §3) — non fatto nell'audit di
  coerenza del 2026-09-11: avrebbe richiesto toccare ogni template HTML
  senza modo di testarlo dal vivo in questa sessione.

## 9. Distribuzione e aggiornamenti
Il plugin include **Plugin Update Checker** (libreria di YahnisElsts, vendorizzata
nel pacchetto: nessuna dipendenza esterna sul sito) e si aggiorna leggendo le
**Release** di un repository GitHub, non tramite caricamento manuale di zip in
bacheca.

**Modello a due livelli** (deciso il 2026-09-10, quando è emerso che esistono già
altri plugin Formalife oltre a questo — vedi `DECISIONI-TECNICHE.md`):
- Il codice sorgente di questo plugin vive in `claude-web/plugins/guida-antipanico-soffocamento/`
  (workspace di coordinamento, condiviso con gli altri plugin Formalife).
- L'aggiornamento automatico via GitHub punta invece a un repository **dedicato**,
  solo per questo plugin: `github.com/formalife/plugin-guida-al-soffocamento`,
  sincronizzato da `claude-web` con `scripts/sync-plugin-release.sh` al momento del
  rilascio (non ad ogni commit).
- **Motivo verificato leggendo la libreria vendorizzata (non un'ipotesi):**
  `getRemoteFile()` in `Vcs/GitHubApi.php` cerca sempre `readme.txt` alla radice del
  repository configurato, senza supporto per sottocartelle. Con più plugin nello
  stesso repository di coordinamento, solo uno potrebbe avere il changelog letto
  correttamente da WordPress; con repository di distribuzione dedicati, ognuno ce
  l'ha.

Configurazione:
- Costante: `GAPS_UPDATE_REPOSITORY` in `guida-antipanico-soffocamento.php`.
- Valore attuale: `https://github.com/formalife/plugin-guida-al-soffocamento/`.
- Branch monitorato: `main`.
- Meccanismo: il PUC cerca le Release del repo di distribuzione e richiede che
  ognuna abbia un **release asset** (zip allegato) il cui nome rispetti
  `guida-antipanico-soffocamento(-vX.X.X)?.zip`. Il workflow
  `.github/workflows/build-release.yml`, che vive nel repository di distribuzione
  (non in claude-web), lo genera e allega da solo a ogni Release pubblicata.
- Per ogni release: versione bumpata in tre punti coerenti (header `Version:`,
  `GAPS_VERSION`, `Stable tag` in `readme.txt`) + voce di changelog — vedi §7.

*(Aggiorna le sezioni 3, 4, 7, 8 ad ogni modifica strutturale. Il resto del documento
cambia raramente.)*
