# claude-web

Workspace di coordinamento per tutto lo sviluppo web di Formalife — più plugin,
documentazione tecnica condivisa, un solo posto dove Claude (in chat o in Claude
Code) trova il contesto senza bisogno di rispiegazioni.

## Perché due livelli di repository

Un plugin WordPress ha bisogno, per aggiornarsi da solo (Plugin Update Checker), di
un repository GitHub con `readme.txt` **alla radice** — la libreria non supporta
sottocartelle per questo (verificato leggendo il codice, non un'ipotesi). Con più
plugin coordinati in un unico repository, solo uno potrebbe stare alla radice.

Per questo:
- **`claude-web` (qui)** — dove si sviluppa, si discute, si coordina. Ogni plugin ha
  la sua sottocartella in `plugins/`.
- **Un repository dedicato per ogni plugin che si aggiorna via GitHub** — es.
  `formalife/plugin-guida-al-soffocamento` — che contiene *solo* quel plugin, alla
  radice. Si sincronizza da qui con `scripts/sync-plugin-release.sh` al momento del
  rilascio, non ad ogni commit.

Un plugin che non usa (ancora) l'aggiornamento automatico via GitHub resta solo in
`plugins/<slug>/`, senza repository dedicato — non è un costo pagato in anticipo.

## Cosa c'è qui

- **`plugins/<slug>/`** — sorgente completo di ogni plugin. Attualmente:
  `guida-antipanico-soffocamento` (libro).
- **`docs/`** — documentazione viva:
  - `DECISIONI-TECNICHE.md` — registro **unico**, trasversale a tutti i plugin: cosa
    è stato deciso, quando, perché.
  - `architettura-<slug>.md` — un file per plugin, mappa tecnica (file, classi,
    impostazioni, flussi).
- **`scripts/sync-plugin-release.sh`** — copia `plugins/<slug>/` in un clone locale
  del repository di distribuzione dedicato, pronto per commit/tag/push.
- **`CLAUDE.md`** — istruzioni per Claude Code quando lavora in questo repository.

## Come si rilascia una nuova versione di un plugin

1. Lavora dentro `plugins/<slug>/`. `php -l` su ogni file toccato.
2. Bump versione in tre punti coerenti (header `Version:`, costante tipo
   `GAPS_VERSION`, `Stable tag` in `readme.txt` del plugin) + voce di changelog.
3. Se il plugin ha un repository di distribuzione dedicato:
   `scripts/sync-plugin-release.sh <slug> <percorso-clone-locale>`, poi dentro quel
   repository: commit, tag `vX.X.X`, push, Release GitHub con quel tag → il workflow
   lì dentro genera e allega lo zip da solo.
4. Se la modifica è stata strutturale, aggiorna anche `docs/architettura-<slug>.md` e
   `docs/DECISIONI-TECNICHE.md` **qui**, in claude-web.

## Aggiungere un nuovo plugin

1. Crea `plugins/<nuovo-slug>/` con il codice (esistente o nuovo).
2. Crea `docs/architettura-<nuovo-slug>.md` — anche solo uno scheletro iniziale.
3. Decidi se/quando gli serve un repository di distribuzione dedicato (dipende dal
   meccanismo di aggiornamento che sceglierai per quel plugin — non è detto sia lo
   stesso di `guida-antipanico-soffocamento`).
4. Aggiungi una voce in `docs/DECISIONI-TECNICHE.md`.
