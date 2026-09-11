---
title: "Architettura — plugin formalife-homepage"
progetto: "Formalife, Sviluppo Web & Plugin"
tipo: "riferimento tecnico"
status: "vivo (aggiornare ad ogni modifica strutturale)"
versione_plugin_al: "4.6.8"
ultimo_aggiornamento: "2026-09-11"
---

# Architettura — plugin `formalife-homepage`

Plugin WordPress custom che genera la homepage istituzionale di Formalife e la
landing/checkout del corso pratico "Genitori Pronti". Registrato in `claude-web`
(rinominato `wordpress-web` l'11/09/2026, vedi `DECISIONI-TECNICHE.md`) il
2026-09-10, contestualmente alla migrazione a `formalife-core` (vedi
`DECISIONI-TECNICHE.md`, stessa data).

**Dipendenza (dalla v4.6.4):** questo plugin richiede `formalife-core` attivo
(header `Requires Plugins`). Font locali (Fredoka/Karla/Lora) e una parte dei
token di base (raggi, larghezza massima, ink/white, i tre colori "libro")
vengono da lì — vedi `docs/architettura-formalife-core.md`. **A differenza di
guida-antipanico-soffocamento**, questo plugin ha una palette colori
**propria e indipendente** (blu/rosso/verde, non teal/terracotta): quei
token restano scritti qui, non sono duplicazione da eliminare — vedi §4.

**Se stai per modificare qualcosa che non trovi descritto qui, il documento è
disallineato dal codice reale: fidati del codice, poi aggiorna questo file.**

---

## 1. Cos'è, in una riga

Un solo plugin che gestisce **due prodotti indipendenti sullo stesso sito**:
la homepage istituzionale (contenuti statici, nessun pagamento) e la
landing/checkout del corso pratico "Genitori Pronti" (sessioni dinamiche,
pagamento Stripe incorporato). Riusa pattern tecnici del plugin del libro
(`guida-antipanico-soffocamento`) ma non ne dipende a runtime.

## 2. Le 3 pagine generate automaticamente

| Slug | Contenuto | Template |
|---|---|---|
| `home` | Homepage istituzionale: hero, filosofia, le due soluzioni (libro/corso), fiducia, direzione scientifica, FAQ, contatti | `templates/template-home.php` |
| `genitori-pronti` | Landing + checkout del corso pratico (sessioni dinamiche, checkout singolo/coppia, codici riservati) | `templates/template-course.php` |
| `corso-confermato` | Pagina di conferma post-pagamento corso | `templates/template-course-thankyou.php` |

Gestione URL/slug/conflitti centralizzata in `class-fmh-page-manager.php`
(`FMH_Page_Manager::get_registry()`), stesso meccanismo (slug fisso + meta di
riconoscimento + opzione ID pagina + opzione conflitto) del plugin del libro.
Lo slug `genitori-pronti` è legacy: pre-esisteva a questo plugin, adottato
(`adopt_existing: true`) senza cancellare contenuto.

## 3. File/classi principali e responsabilità

| File | Responsabilità |
|---|---|
| `formalife-homepage.php` | Bootstrap, costanti (`FMH_VERSION`, `FMH_SLUG`, costanti pagina...), include dei file. Prefisso `FMH_`/`fmh_` per non collidere con `GAPS_*`/`gaps_*`. |
| `includes/fmh-settings-helpers.php` | `fmh_default_settings()`, `fmh_get_settings()` (unica fonte di verità, mai `get_option()` diretto), helper immagini/URL/social per la homepage. |
| `includes/fmh-course-helpers.php` | Impostazioni/sessioni/prezzo del corso: `fmh_course_get_settings()`, `fmh_course_get_sessions()`, `fmh_course_get_price_cents()` (**unica fonte di verità dell'importo**), gestione codici riservati monouso (`fmh_course_code_is_valid()`, `fmh_course_mark_code_used()`). |
| `includes/class-fmh-course-settings.php` | Pannello impostazioni corso in bacheca (menu `formalife-course`): sessioni, prezzi, codici, immagini, chiavi Stripe. |
| `includes/class-fmh-course-orders.php` | CPT `fmh_course_order`, submit AJAX (`fmh_submit_course_order`), creazione PaymentIntent, verifica codice riservato (AJAX separata), riconciliazione manuale ordine, colonne/filtri admin. |
| `includes/class-fmh-course-stripe-webhook.php` | Endpoint REST `fmh/v1/course-stripe-webhook`: verifica firma HMAC (`Stripe-Signature`, tolleranza 300s, `hash_equals`), aggiorna stato pagamento. |
| `includes/class-fmh-meta-capi.php` | Invio server-side dell'evento Purchase a Meta Conversions API dopo conferma pagamento, solo se `_fbp` presente; retry manuale dal dettaglio ordine (v4.6.2). |
| `includes/class-fmh-waitlist.php` | CPT `fmh_waitlist_lead`, form lista d'attesa (AJAX `fmh_join_waitlist`), nessun pagamento. |
| `includes/class-fmh-settings.php` | Pannello impostazioni homepage in bacheca (menu `formalife-homepage`, icona home). |
| `includes/class-fmh-page-manager.php` | Registro pagine, creazione/riconoscimento pagine, redirect template. |
| `includes/class-fmh-assets.php` | Enqueue CSS/JS frontend/admin, preload font, meta Open Graph, CSS dinamico hero (overlay/focal/blur da impostazioni). Dalla v4.6.4 delega token/font a `formalife-core` — vedi §4. |
| `includes/class-fmh-admin-notices.php` | Avviso conflitto slug pagina. **Non ha** (a differenza di GAPS_Admin_Notices) un avviso dedicato "webhook secret mancante" — vedi §7 decisioni aperte. |
| `templates/template-home.php`, `template-course.php`, `template-course-thankyou.php` | Markup delle 3 pagine. Copy lungo delle sezioni vive nel template (non in impostazioni) dalla v2.0.0. |
| `assets/js/course.js`, `assets/js/frontend.js`, `assets/js/fmh-stripe-loader.js` | Step del checkout corso, form lista d'attesa, caricamento lazy di Stripe.js (solo al primo click su un CTA, come nel plugin del libro). |

## 4. Integrazione con formalife-core — homepage generale (dalla v4.6.4)

**Vale solo per `assets/css/frontend.css` / `template-home.php` (pagina
`home`).** Per la landing e la conferma del corso, superata dalla migrazione
completa descritta in §4bis.

Secondo plugin migrato dopo `guida-antipanico-soffocamento` (v3.7.6). A
differenza del libro — i cui token colore coincidono 1:1 con quelli di
formalife-core, perché ne sono la fonte originale — questo plugin ha una
**palette propria e voluta**: blu autorevolezza, rosso CTA, verde
rassicurazione, crema. Solo il sottoinsieme di token *realmente identico* a
formalife-core è stato aliasato:

- **Aliasati** (stesso valore, ora con fallback esplicito
  `var(--fmls-x, <valore originale>)`): `--fmh-ink`, `--fmh-ink-soft`,
  `--fmh-white`, `--fmh-radius`, `--fmh-radius-sm`, `--fmh-max-w`,
  `--fmh-font-display`, `--fmh-font-body`, `--fmh-font-story`, e i tre colori
  usati solo nella card/sezione "Il libro" (`--fmh-book-teal`,
  `--fmh-book-teal-darker`, `--fmh-book-terracotta` → `--fmls-teal-deep`,
  `--fmls-teal-darker`, `--fmls-terracotta`).
- **Non toccati** (palette propria, deliberatamente indipendente — vedi
  commento originale in `assets/css/frontend.css`): `--fmh-cream`,
  `--fmh-cream-alt`, `--fmh-blue-*`, `--fmh-red-*`, `--fmh-green-*`,
  `--fmh-border-soft`, `--fmh-shadow-soft`, `--fmh-shadow-card`,
  `--fmh-shadow-lift` (questi ultimi tre sono calcolati sul blu proprio, non
  sul teal di formalife-core — aliasarli avrebbe cambiato colore alle ombre).
- **Migrati per intero**: font (`assets/css/fonts.css` e `assets/fonts/`
  rimossi dal plugin, ora vivono solo in formalife-core — file identici,
  stessi 8 pesi WOFF2/nomi).
- **Ogni punto di integrazione è difensivo**: `class-fmh-assets.php` verifica
  `function_exists('formalife_core_enqueue')` / `function_exists('formalife_core_output_font_preloads')`
  prima di chiamarle; se formalife-core non fosse attivo, il CSS ricade sui
  valori di fallback (secondo argomento di ogni `var()`) invece di un foglio
  di stile mancante. Verificato per lettura, non per test dal vivo (nessun
  accesso al sito in questa sessione) — la prima cosa da controllare dopo il
  deploy è che homepage e landing corso rendano identiche a prima.

## 4bis. Stile core esatto sul corso — sperimentato in v4.6.6, ripristinato in v4.6.7

Per una sola versione (v4.6.6, 2026-09-11), `course.css` è passato allo
"stile core esatto" (colori del libro — crema/teal/terracotta — al posto
della palette propria blu/rosso/verde, pulsante di acquisto terracotta). Su
richiesta esplicita del proprietario, **ripristinata la palette propria in
v4.6.7**, stessa sessione: preferenza visiva, non un problema tecnico o di
implementazione. `course.css` oggi segue esattamente lo stesso criterio di
`frontend.css` (§4): solo font/raggi/larghezza massima/ink/white/i tre
colori "libro" aliasati a formalife-core, il resto della palette
(blu/rosso/verde/crema/ombre/bordi) indipendente. Dettaglio della decisione
e della marcia indietro in `DECISIONI-TECNICHE.md` (entrambe le voci
2026-09-11).

**Eredità di questo esperimento:** il nuovo token condiviso
`--fmls-green-reassure`/`-dark`/`-light` in formalife-core (v1.1.0),
aggiunto per quel tentativo, **resta definito ma oggi non ha consumatori**
(nessun CSS di alcun plugin lo referenzia) — lasciato disponibile per un
futuro riuso invece di rimuoverlo, vedi `docs/architettura-formalife-core.md` §3bis.

**Audit di coerenza (v4.6.7):** durante il ripristino, trovato che
`frontend.css` e `course.css` dichiaravano lo stesso ruolo
(`--fmh-font-display`/`--fmh-font-body`) con **fallback diversi**
(`system-ui` vs `'Arial Rounded MT Bold', Arial`) — derivante dal fatto che
`course.css` non eredita i token di `frontend.css` (§ nota 1 in
`course.css`, due dichiarazioni separate che nel tempo erano divergenti).
Uniformato al fallback di formalife-core (quello già usato da `course.css`
e da `guida-antipanico-soffocamento`) in entrambi i file.

## 5. Sistema impostazioni

Due gruppi indipendenti, entrambi via `wp_parse_args( $saved, <defaults> )`,
mai `get_option()` diretto:
- Homepage: `fmh_get_settings()` / `fmh_default_settings()` (opzione
  `FMH_OPTION_KEY` = `fmh_settings`).
- Corso: `fmh_course_get_settings()` / `fmh_course_default_settings()`
  (opzione `FMH_COURSE_OPTION_KEY` = `fmh_course_settings`).

Prezzo corso: unica fonte di verità `fmh_course_get_price_cents( $session, $party_size )`,
con eventuale sconto via `fmh_course_resolve_price_cents()` (codici riservati
monouso, consumati solo a pagamento confermato).

## 6. Flusso di pagamento (corso)

Stesso modello del libro: PaymentIntent + webhook, nessuna conferma "pagato"
accettata dal solo browser.
1. Cliente sceglie sessione/quantità (+ eventuale codice riservato) → AJAX
   (`fmh_submit_course_order`) crea l'ordine (CPT `fmh_course_order`) e un
   **PaymentIntent** Stripe per l'importo da `fmh_course_resolve_price_cents()`.
2. Stripe Payment Element nel modale; `stripe.confirmPayment()` con
   `return_url` = pagina "Conferma corso".
3. **Conferma reale solo dal webhook** (`payment_intent.succeeded`, firma
   HMAC verificata con tolleranza 300s) — aggiorna stato ordine, consuma
   l'eventuale codice riservato, innesca l'invio Purchase a Meta CAPI.

## 6ter. Tracking Meta: InitiateCheckout (browser, dalla v4.6.8) + Purchase (server, dalla v4.6.1)

Questo plugin **non carica un Meta Pixel**: entrambi gli eventi dipendono da
un Pixel già attivo in pagina per altra via (verificato dal vivo l'11/09/2026:
`window.fbq` è una funzione sulla landing corso, iniettato da Google Tag
Manager — non da questo plugin). Ogni punto di integrazione controlla
`typeof window.fbq==='function'` prima di chiamare `fbq()`: se il Pixel non
c'è, il tracking viene saltato silenziosamente, mai un errore JS che blocchi
il checkout.

- **InitiateCheckout** (`assets/js/course.js`, solo browser, mai CAPI):
  sparato subito dopo che il Payment Element viene montato nel modale (il
  PaymentIntent esiste già lato server a quel punto). `value`/`currency` =
  `amount_cents` reale restituito dalla stessa risposta AJAX che crea
  l'ordine (`class-fmh-course-orders.php` lo calcolava già da
  `fmh_course_resolve_price_cents()` e lo restituiva nella risposta; prima
  della v4.6.8 il JS lo ignorava semplicemente) — non un valore
  ricalcolato lato client, quindi coerente anche con eventuali codici
  sconto. `content_ids: ['formalife-course']`, stesso identificatore usato
  da `FMH_Meta_CAPI::maybe_send_purchase()`. `event_id` =
  `fmh_initiate_checkout_<order_id>`: stabile per un eventuale futuro
  mirror server-side, ma oggi non c'è nulla con cui deduplicare (nessun
  InitiateCheckout via CAPI).
- **Purchase** (`includes/class-fmh-meta-capi.php`, solo server/CAPI, mai
  browser): invariato in questa versione. Resta l'unica fonte per
  "acquisto confermato" — vedi §5 sopra. Richiede dataset ID + access
  token configurati e il cookie `_fbp` catturato al submit del form
  (`appendAttribution()` in `course.js`); se manca uno dei tre, l'invio
  viene saltato e lo stato (`sent` / `skipped_no_dataset` /
  `skipped_no_token` / `skipped_no_fbp` / `skipped_no_amount` /
  `error_http_<code>` / `error_wp`) è visibile nel meta box dell'ordine in
  bacheca — vedi `class-fmh-course-orders.php::render_meta_box()`.

**Non ancora fatto:** nessun mirror server-side di InitiateCheckout (non
richiesto); verifica dal vivo in Meta Events Manager dell'evento browser
dopo il deploy di questa versione.

## 6bis. Sicurezza alla disinstallazione (dalla v4.6.5)

`uninstall.php` cancella impostazioni (`fmh_settings`, `fmh_course_settings`
— comprese le chiavi Stripe), tutti gli ordini corso (CPT `fmh_course_order`)
e le pagine generate, ma **solo se** `fmh_settings['allow_uninstall_wipe']`
è esplicitamente `true` (checkbox "Cancella i dati alla disinstallazione" in
impostazioni, falsa di default). Se la casella non è spuntata, `uninstall.php`
esce subito (`return;`) senza toccare nulla.

**Perché esiste questa guardia:** WordPress non permette di caricare uno zip
con lo stesso slug di un plugin già installato — per sostituirlo bisogna
prima "Disattiva" + **"Elimina"**, e "Elimina" è esattamente ciò che innesca
`uninstall.php`. Prima di questa versione, un aggiornamento fatto in questo
modo cancellava silenziosamente impostazioni e ordini reali (successo il
2026-09-11, vedi `DECISIONI-TECNICHE.md`). Le immagini della Libreria Media
non erano/non sono mai state cancellate da questo file: solo i riferimenti
(ID) salvati nelle impostazioni.

**Implicazione operativa:** per un normale aggiornamento di versione, lascia
la casella deselezionata (default) — "Elimina" seguito da un nuovo upload
non cancellerà più nulla. Spuntala solo quando l'intento è davvero smontare
il plugin e ripulire il sito.

## 7. Changelog (sintesi — dettaglio completo in `readme.txt` del plugin)
- **4.6.8** — Aggiunto `InitiateCheckout` (Meta Pixel, browser) alla
  mostrazione del Payment Element nel checkout corso — vedi §6ter. Nessuna
  modifica a UX, prezzi, checkout Stripe o Purchase CAPI.
- **4.6.7** — Ripristinata la palette propria sulla landing/conferma corso
  (richiesta esplicita del proprietario, preferenza visiva — vedi §4bis).
  Audit di coerenza token: uniformato il fallback font tra `frontend.css` e
  `course.css`.
- **4.6.6** — Landing e conferma del corso passano allo **stile core esatto**
  di formalife-core (crema/teal/terracotta) al posto della palette propria —
  vedi §4bis. Pulsante di acquisto ora terracotta (era rosso). Homepage
  generale non toccata.
- **4.6.5** — Sicurezza: `uninstall.php` non cancella più automaticamente
  impostazioni (chiavi Stripe comprese), ordini corso e pagine generate al
  primo "Elimina" da bacheca. Serve prima spuntare "Cancella i dati alla
  disinstallazione" nel pannello impostazioni (`allow_uninstall_wipe`,
  falso di default). **Motivo:** in questa stessa sessione, un
  aggiornamento del plugin fatto eliminando la versione attiva per
  caricarne una più recente (necessario perché WordPress non sovrascrive
  un plugin già installato via upload) ha attivato `uninstall.php` e
  cancellato impostazioni, chiavi Stripe e ordini reali — vedi
  `DECISIONI-TECNICHE.md`, voce 2026-09-11.
- **4.6.4** — Nuova dipendenza `formalife-core`: font e sottoinsieme di
  token realmente condivisi migrati lì (fallback espliciti, zero cambiamento
  visivo previsto per la palette propria). Vedi §4.
- **4.6.3** — Versione presente nello zip caricato in questa sessione;
  **nessuna voce di changelog trovata in `readme.txt`** per questa versione
  (readme fermo a "Stable tag: 4.6.2" prima di questa sessione) — probabile
  disallineamento pregresso, non risolto qui per non inventare un changelog
  non verificato. Il nome dello zip (`meta-retry-visible`) suggerisce un
  intervento sulla visibilità del retry manuale Meta CAPI introdotto in 4.6.2.
- **4.6.2** — Diagnostica Meta CAPI nel dettaglio ordine + retry manuale del
  Purchase per ordini già pagati.
- **4.6.1** — Tracking acquisti corso via Meta Conversions API server-side
  (solo dopo pagamento confermato) + persistenza UTM.
- **4.6.0** — Codici riservati individuali monouso (in sostituzione del
  codice unico condiviso).

## 8. Decisioni aperte / da confermare col proprietario

- **Nessun avviso "webhook secret mancante"**: `guida-antipanico-soffocamento`
  ha un avviso dedicato in bacheca per questo scenario (il bug più comune del
  flusso di pagamento, vedi `docs/architettura-guida-antipanico-soffocamento.md`
  §6); `formalife-homepage` no. Non aggiunto in questa sessione (fuori
  perimetro della richiesta, che era l'integrazione con formalife-core) — da
  valutare come miglioramento futuro esplicito.
- **Distribuzione**: nessun Plugin Update Checker / repository dedicato in
  questo plugin (si installa a mano, come `formalife-core` stesso). Non
  aggiunto in questa sessione: introdurre PUC richiederebbe creare un nuovo
  repository GitHub di distribuzione dedicato (azione esterna, da confermare
  esplicitamente col proprietario) — vedi la sezione "Modello a due livelli"
  in `DECISIONI-TECNICHE.md`. Se il corso comincia a richiedere aggiornamenti
  automatici frequenti, vale la pena riconsiderare.
- **Gap di changelog pregresso** (v4.6.3, vedi §7): da colmare quando chi ha
  fatto quella modifica può confermare cosa è cambiato esattamente.
