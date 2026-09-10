# CLAUDE.md — istruzioni per Claude Code in questo repository

Workspace di coordinamento per lo sviluppo web di Formalife. **Più plugin vivono
qui**, ognuno nella sua sottocartella sotto `plugins/`. Prima di modificare codice:
1. Leggi `docs/architettura-<slug>.md` del plugin che stai per toccare, e
   `docs/DECISIONI-TECNICHE.md` (registro unico, trasversale a tutti i plugin).
2. Se qualcosa non torna tra quei documenti e il codice reale, fidati del codice, poi
   segnala e correggi la discrepanza — non ipotizzare architetture non lette.
3. Se stai per toccare un plugin che non ha ancora `docs/architettura-<slug>.md`,
   creane uno scheletro prima di procedere: è la prima cosa da aggiornare, non
   l'ultima.

## Struttura del repository
- `plugins/<slug>/` — sorgente completo di ogni plugin, autonomo.
- `docs/` — documentazione viva (vedi sopra).
- `scripts/sync-plugin-release.sh` — porta `plugins/<slug>/` in un repository di
  distribuzione dedicato (solo per i plugin che ce l'hanno), al momento del rilascio.

## Perché alcuni plugin hanno un secondo repository (di "distribuzione")
Plugin Update Checker (usato per l'aggiornamento automatico via GitHub) legge il
changelog sempre da `readme.txt` alla radice del repository configurato, senza
supporto per sottocartelle — verificato leggendo la libreria vendorizzata, non
un'ipotesi. Un plugin coordinato qui in `plugins/<slug>/` che vuole l'aggiornamento
automatico ha quindi bisogno di un repository dedicato, minimale, dove quella
sottocartella diventa la radice. Non tutti i plugin ne hanno bisogno: solo quelli che
adottano questo specifico meccanismo di aggiornamento.

## Stack e convenzioni (valide per tutti i plugin, salvo eccezioni indicate nel loro architettura-<slug>.md)
- WordPress, PHP 7.4+, nessun framework: plugin custom scritti a mano.
- Pagamenti (dove presenti): Stripe Payment Element incorporato via REST + webhook
  firmato. Mai Payment Link o Checkout Session — se ne vedi, è codice legacy da
  correggere.
- Naming: prefisso `<SIGLA>_` per classi/costanti, `<sigla>_` per funzioni helper
  (es. `GAPS_`/`gaps_` per guida-antipanico-soffocamento). Impostazioni sempre
  tramite una funzione tipo `<sigla>_get_settings()`, mai `get_option()` diretto.
  Ogni importo mostrato all'utente ha una funzione "sorgente di verità" in centesimi,
  usata sia per il testo sia per l'addebito reale.

## Regole di sicurezza permanenti
- Non modificare prezzo, garanzie, tempi di consegna, claim clinici o dati di
  compliance senza conferma esplicita e circostanziata del proprietario. Se manca un
  dato, chiedilo — non inventarlo.
- Ogni pagamento Stripe passa da verifica di firma sul webhook. Nessuna eccezione.
- `php -l` su ogni file PHP toccato, sempre, prima di considerare finito il lavoro.
- Ogni release: versione bumpata in tre punti coerenti (header plugin, costante
  versione, `Stable tag` in `readme.txt`) + voce di changelog.

## Dopo ogni modifica strutturale
Aggiorna `docs/architettura-<slug>.md` del plugin toccato (cosa esiste ora) e
`docs/DECISIONI-TECNICHE.md` (perché è stato deciso così, con data). Non è
opzionale: è il meccanismo che evita di dover rispiegare tutto da zero alla sessione
successiva, con un plugin o con dieci.
