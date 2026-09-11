---
title: "Architettura — plugin formalife-core"
progetto: "Formalife, Sviluppo Web & Plugin"
tipo: "riferimento tecnico"
status: "vivo (aggiornare ad ogni modifica strutturale)"
versione_plugin_al: "1.1.0"
ultimo_aggiornamento: "2026-09-11"
---

# Architettura — plugin `formalife-core`

Plugin "fondamenta": stile e componenti condivisi tra tutti i plugin Formalife.
Non genera nessuna pagina pubblica da solo — esiste perché un plugin di prodotto
(libro, corso, quello che verrà) non riscriva colori/font/pulsanti/guscio del
popup ogni volta. Vedi la decisione che lo ha originato in `DECISIONI-TECNICHE.md`
(2026-09-10, "formalife-core invece di un plugin unico").

## 1. Cosa fornisce
| File | Cosa contiene |
|---|---|
| `assets/css/tokens.css` | Custom property `--fmls-*`: colori, font, spaziature, ombre, raggio angoli. Solo variabili — inerti finché non richiamate con `var()`, quindi sicure su `:root` globale (non "leakano" stili come farebbero classi non scoped). Dalla v1.1.0 include anche `--fmls-green-reassure`/`-dark`/`-light` (verde di rassicurazione per badge di garanzia) — **attualmente senza consumatori**: aggiunto per un tentativo di stile poi ripristinato su `formalife-homepage` (vedi §3bis), lasciato disponibile per un futuro riuso invece di rimuoverlo. |
| `assets/css/components.css` | Classi `.fmls-*`: pulsanti (`fmls-btn-primary`, `-onlight`, `-secondary`, `-lg`), contenitore pagina (`fmls-wrap`), guscio popup (`fmls-modal-overlay`, `fmls-modal`, `fmls-modal-close`), form (`fmls-form-row`, `fmls-form-message`), etichetta discreta (`fmls-micro`), comparsa (`fmls-reveal`). |
| `assets/css/fonts.css` + `assets/fonts/` | `@font-face` locali (Fredoka, Karla, Lora) — nessuna richiesta a Google Fonts. File WOFF2 non versionati (licenza): vedi `assets/fonts/README.txt`. |
| `includes/formalife-core-functions.php` | `formalife_core_enqueue()` (registra ed enqueua token+font+componenti, ritorna gli handle), `formalife_core_output_font_preloads( $font_files )` (preload solo se il file esiste su disco). |
| `includes/class-formalife-settings-field.php` | `Formalife_Settings_Field::text()/checkbox()/textarea()` — markup ripetitivo delle righe nei pannelli impostazioni in bacheca. |

## 2. Come lo usa un plugin di prodotto
1. Header: `Requires Plugins: formalife-core` (WordPress impedisce l'attivazione
   senza, da WP 6.5 — su versioni precedenti non imposto, per questo ogni punto
   di integrazione è comunque difensivo, vedi §3).
2. Nel proprio `wp_enqueue_scripts`: chiama `formalife_core_enqueue()`, poi
   enqueua il proprio CSS con l'handle `components` restituito come dipendenza.
3. Nel proprio CSS: o usa direttamente le classi `.fmls-*` (consigliato per un
   plugin nuovo), oppure alias delle proprie variabili, es.
   `--gaps-teal-deep: var(--fmls-teal-deep, #0B6560);` — **sempre con un
   fallback esplicito** (secondo argomento di `var()`), così il plugin
   continua a rendere correttamente anche se formalife-core non fosse attivo.

## 3. Integrazione con guida-antipanico-soffocamento (dalla v3.7.8)
Primo plugin migrato (costruito in v3.7.6, applicato al sorgente coordinato
in v3.7.8 — vedi `DECISIONI-TECNICHE.md`, 2026-09-10 e 2026-09-11). Scelta
deliberatamente conservativa, trattandosi di un plugin live che incassa
pagamenti reali:

- **Migrati per intero**: font (`fonts.css` + `assets/fonts/` rimossi dal
  plugin del libro, ora vivono solo in formalife-core) e i valori dei token
  colore/font/spaziatura (aliasati, non più hardcoded).
- **Non migrate le classi**: `guida-antipanico-soffocamento` continua a usare
  le proprie classi `gaps-btn-primary`, `gaps-modal`, `gaps-wrap`, ecc. — non
  rinominate in `fmls-*`. Migrarle avrebbe richiesto toccare ogni template
  HTML (~800 righe tra landing/thankyou/legal/numeri) senza modo di testarlo
  dal vivo in questa sessione: rischio giudicato superiore al beneficio per
  ora. La vera duplicazione (i *valori* hardcoded) è comunque eliminata dagli
  alias — resta solo una piccola duplicazione cosmetica delle *regole* CSS
  (es. `.fmls-btn-primary { ... }` e `.gaps-btn-primary { ... }` hanno lo
  stesso corpo, scritto due volte). Migrazione delle classi: task futuro
  esplicito, non un compromesso silenzioso — vedi `DECISIONI-TECNICHE.md`.
- **Ogni punto di integrazione è difensivo**: se formalife-core non fosse
  attivo, `class-gaps-assets.php` non enqueua nulla di rotto (semplicemente
  salta l'enqueue condiviso) e ogni `var(--fmls-*, <valore originale>)` nel
  CSS ricade sul valore che c'era prima di questa modifica. Verificato per
  lettura, non per test dal vivo (nessun accesso al sito in questa sessione)
  — la prima cosa da controllare dopo il deploy è che la landing renda
  identica a prima.

## 3bis. Integrazione con formalife-homepage (dalla v4.6.4, uniformata in v4.6.7)

Stesso criterio ovunque nel plugin — homepage generale (`frontend.css`) e
landing/conferma del corso (`course.css`), due dichiarazioni separate per
motivi strutturali (§4 di `docs/architettura-formalife-homepage.md`) ma
tenute deliberatamente identiche: solo il sottoinsieme di token realmente
identico a formalife-core (font, raggi, larghezza massima, ink/white, i tre
colori "libro" della sola sezione dedicata) è aliasato con fallback; la
palette propria (blu/rosso/verde/crema) resta scritta lì, deliberatamente
indipendente — come guida-antipanico-soffocamento §3, ma con una palette
propria invece che coincidente con quella del libro.

**Nota storica:** per una versione (v4.6.6, 2026-09-11) `course.css` era
passato allo "stile core esatto" (colori del libro al posto della palette
propria, incluso il nuovo token `--fmls-green-reassure*` per il verde di
rassicurazione — vedi §1). Ripristinata la palette propria in v4.6.7 su
richiesta esplicita del proprietario (preferenza visiva) — dettaglio in
`DECISIONI-TECNICHE.md`.

## 4. Distribuzione
Non ha (ancora) un repository di distribuzione dedicato in stile Plugin
Update Checker: a differenza di `guida-antipanico-soffocamento`, per ora
vive solo in `claude-web/plugins/formalife-core/` e si installa copiandolo a
mano su WordPress. Decisione deliberata per non aggiungere infrastruttura
(un terzo repository, un'altra Release da mantenere) finché non serve
davvero — se in futuro cambia spesso indipendentemente dai plugin che lo
usano, vale la pena riconsiderare.

## 5. Decisioni aperte
- Se/quando migrare le classi di `guida-antipanico-soffocamento` da `gaps-*`
  a `fmls-*` per intero (vedi §3).
- Se dare a formalife-core un proprio repository di distribuzione quando
  cambierà abbastanza spesso da giustificarlo — per ora entrambi i plugin di
  prodotto lo consumano solo per un sottoinsieme di token (font/raggi/ink),
  non c'è più un caso "dipendenza piena" dopo il ripristino della palette
  propria del corso in v4.6.7 (vedi §3bis).
- Se/quando trovare un consumatore per `--fmls-green-reassure*` (vedi §1) o
  rimuoverlo come token inutilizzato.
