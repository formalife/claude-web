=== Formalife Core — Stile e componenti condivisi ===
Contributors: formalife
Tags: design system, componenti condivisi
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fondamenta condivise per i plugin Formalife: token di design, componenti CSS di base e un piccolo helper per i pannelli impostazioni. Non genera nessuna pagina pubblica da solo.

== Descrizione ==

`formalife-core` non fa nulla di visibile da solo: esiste perché più plugin
Formalife (a partire da `guida-antipanico-soffocamento`) condividano lo
stesso stile — colori, font, pulsanti, guscio del popup — senza riscriverlo
ogni volta.

**Cosa fornisce:**

* `assets/css/tokens.css` — custom property `--fmls-*` (colori, font,
  spaziature, ombre, raggio degli angoli). Solo variabili: non applicano
  nessuno stile finché qualcosa non le richiama con `var(...)`.
* `assets/css/components.css` — classi `.fmls-*` pronte all'uso: pulsanti
  (`fmls-btn-primary`, `fmls-btn-onlight`, `fmls-btn-secondary`,
  `fmls-btn-lg`), contenitore di pagina (`fmls-wrap`), guscio di
  popup/modal (`fmls-modal-overlay`, `fmls-modal`, `fmls-modal-close`),
  primitive di form (`fmls-form-row`, `fmls-form-message`), etichetta
  discreta (`fmls-micro`), animazione di comparsa (`fmls-reveal`).
* `assets/css/fonts.css` + `assets/fonts/` — dichiarazioni `@font-face`
  locali (Fredoka, Karla, Lora): nessuna richiesta verso Google Fonts. I
  file WOFF2 vanno copiati manualmente, vedi `assets/fonts/README.txt`.
* `formalife_core_enqueue()` — funzione PHP che un plugin consumatore
  richiama nel proprio `wp_enqueue_scripts` per caricare token, font e
  componenti nell'ordine corretto.
* `formalife_core_output_font_preloads( $font_files )` — preload dei pesi
  critici indicati, solo se il file esiste già su disco.
* `Formalife_Settings_Field` — helper statico per il markup ripetitivo dei
  pannelli impostazioni (`::text()`, `::checkbox()`, `::textarea()`).

**Come lo usa un plugin consumatore:**

1. Header del plugin consumatore: `Requires Plugins: formalife-core`.
2. Nel proprio `wp_enqueue_scripts`: `formalife_core_enqueue()`, poi enqueue
   del proprio CSS con `formalife-core-components` come dipendenza.
3. Nel proprio CSS: usare direttamente le classi `.fmls-*`, oppure — per un
   plugin già esistente con le proprie classi — alias del tipo
   `--gaps-teal-deep: var(--fmls-teal-deep, #0B6560);` (fallback esplicito
   incluso, per non dipendere in modo fragile dal caricamento di questo
   plugin).

Dettaglio completo, incluso perché guida-antipanico-soffocamento non è
stato migrato per intero alle classi `.fmls-*` (solo ai token), in
`docs/architettura-formalife-core.md` nel repository `claude-web`.

== Changelog ==

= 1.1.0 =
* Nuovi token `--fmls-green-reassure` / `-dark` / `-light` (verde di
  rassicurazione, da usare con parsimonia nei badge di garanzia/fiducia).
  Valori migrati 1:1 da `formalife-homepage`, promossi qui perché la
  landing del corso pratico adotta lo stile core esatto invece di una
  palette propria — vedi `docs/DECISIONI-TECNICHE.md` nel repository
  `claude-web`, 2026-09-11.

= 1.0.0 =
* Prima versione: estratti da guida-antipanico-soffocamento v3.7.5 i token di
  design, i componenti CSS generici (pulsanti, guscio del popup, primitive
  di form) e le dichiarazioni @font-face — nessun valore reinventato in
  questa estrazione.
