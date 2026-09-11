=== Formalife — Homepage ===
Contributors: formalife
Tags: homepage, landing page, formalife
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 4.6.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Homepage istituzionale di Formalife: genera automaticamente la pagina pubblica "home", con hero, presentazione delle due soluzioni (libro e corso pratico), badge di rassicurazione, direzione scientifica, FAQ e contatti. Stesso stile grafico del plugin "Guida Anti-Panico al Soffocamento Pediatrico", ma con palette colori propria.

== Descrizione ==

Il plugin genera automaticamente una pagina pubblica:

* `home` — la homepage istituzionale di Formalife: hero con missione, sezione "La nostra filosofia" (il sistema libro + corso), "Le nostre soluzioni" con due card che rimandano rispettivamente alla landing del libro e alla pagina del corso pratico, badge di fiducia, direzione scientifica (Dott.ssa Camposarcone), FAQ, CTA finale, contatti e footer.

Dalla versione 4.0 il plugin gestisce la landing `genitori-pronti` del Corso Anti-Panico al Soffocamento Pediatrico, sessioni dinamiche, checkout singolo/coppia, ordini e dashboard operativa. Lo slug, il page ID e gli ordini storici restano compatibili. Il plugin del libro resta indipendente: vengono riusati pattern tecnici, non dipendenze runtime.

= Palette colori =

**Homepage (`home`)**: palette indipendente da quella del libro:

* Blu autorevolezza — #1E3A5F (scuro #142843, chiaro #EEF3F9): titoli e superfici ampie (hero, sezione finale, footer).
* Rosso accento/CTA — #C94A4A (scuro #A33535, chiaro #FAEAEA): usato sempre e solo per i pulsanti di azione.
* Verde sporadico — #2E7D5B (scuro #1F5C41, chiaro #E6F4EC): usato con parsimonia, alternato al blu, solo nei badge di rassicurazione della sezione "Perché fidarti di Formalife".
* Sfondo chiaro — #FBF8F4 (alternato #F4EDE3).

I colori del plugin del libro (teal #0B6560 / terracotta #C24E3A) compaiono solo nella card "Il libro" della sezione "Le nostre soluzioni", come unica eccezione prevista: sono valori fissi definiti in questo stesso plugin (nessuna dipendenza diretta dall'altro), usati solo quando la pagina fa esplicito riferimento al libro.

**Landing e conferma del corso (`genitori-pronti`, `corso-confermato`)**: dalla v4.6.6, **stile core esatto** — non più questa palette propria, ma gli stessi colori di formalife-core (crema/teal/terracotta, l'identità del libro), ereditati via token `--fmls-*` con fallback esplicito. Decisione esplicita del proprietario: coerenza visiva totale col libro sull'intero funnel del corso, pulsante di acquisto incluso (ora terracotta, non più rosso). Il verde di rassicurazione dei badge di garanzia è stato promosso a token condiviso (`--fmls-green-reassure`) in formalife-core v1.1.0. Vedi `docs/architettura-formalife-homepage.md` §4bis nel repository di coordinamento.

Non sono presenti campi colore nel pannello impostazioni: la palette è intenzionalmente fissa via CSS, per garantire che venga sempre rispettata.

= Dove trovare le impostazioni =

Bacheca WordPress → menu laterale "Formalife Home" (icona casa). In cima alla pagina impostazioni trovi l'URL della homepage generata e un promemoria per impostarla come pagina iniziale del sito (Impostazioni → Lettura), passaggio che il plugin non esegue automaticamente per non modificare impostazioni globali del sito senza conferma esplicita dell'amministratore.

== Installazione ==

1. Bacheca → Plugin → Aggiungi nuovo → Carica plugin.
2. Seleziona il file .zip del plugin e clicca "Installa ora".
3. Attiva il plugin: alla prima attivazione viene creata automaticamente la pagina pubblica "home" (se lo slug è libero; in caso contrario vedi l'avviso di conflitto in bacheca).
4. Vai su Bacheca → Formalife Home per caricare le immagini, personalizzare i testi di hero/libro/corso, i contatti e i link legali.
5. Vai su Bacheca → Impostazioni → Lettura e imposta "Home" come pagina iniziale del sito, se non lo è già.

== Changelog ==

= 4.6.6 =
* La landing e la pagina di conferma del corso pratico adottano lo stile core esatto di formalife-core (crema/teal/terracotta) al posto della palette propria blu/rosso/verde — decisione esplicita del proprietario. Il pulsante di acquisto diventa terracotta (era rosso). Homepage generale non toccata: mantiene la propria palette.
* Nuovi token condivisi in formalife-core v1.1.0 (`--fmls-green-reassure`/`-dark`/`-light`) per il verde dei badge di garanzia, migrati 1:1 dal valore già in uso qui.
* Nessuna modifica a checkout, prezzi, PaymentIntent, webhook Stripe, email, logica di capienza o contenuti testuali.

= 4.6.5 =
* Sicurezza: disinstallare il plugin (Bacheca → Plugin → Elimina) non cancella più automaticamente impostazioni (comprese le chiavi Stripe), iscrizioni al corso e pagine generate. Serve prima spuntare "Cancella i dati alla disinstallazione" in impostazioni (falsa di default). Prima di questa versione, eliminare il plugin per sostituirlo con uno zip più recente cancellava questi dati senza preavviso.

= 4.6.4 =
* Nuova dipendenza: formalife-core (plugin condiviso di stile/token/componenti tra i plugin Formalife, stesso modello adottato da guida-antipanico-soffocamento in v3.7.6 — va attivato insieme a questo). Font locali (Fredoka/Karla/Lora) spostati in formalife-core: rimossi da questo plugin assets/css/fonts.css e assets/fonts/.
* I token realmente condivisi (font, raggi, larghezza massima, ink/white, i tre colori "libro" di teal/terracotta) ora vengono da formalife-core, con fallback esplicito se non fosse attivo. La palette propria di Formalife Home (blu/rosso/verde/crema) resta invariata e indipendente, come da scelta di design originale.
* Nessuna modifica a checkout, prezzi, PaymentIntent, webhook Stripe, email, logica di capienza o contenuti.

= 4.6.2 =
* Diagnostica Meta CAPI nel dettaglio ordine: stato, motivo di skip/errore, presenza _fbp/_fbc, configurazione dataset/token ed event_id.
* Retry manuale del Purchase Meta per ordini già pagati, senza creare nuovi addebiti e riusando il PaymentIntent/event_id esistente.
* Nessuna modifica a checkout, prezzi, PaymentIntent, webhook Stripe, email o logica di capienza.

= 4.6.1 =
* Tracking acquisti corso: il webhook PaymentIntent invia Purchase server-side a Meta Conversions API solo dopo pagamento confermato, con importo reale e event_id stabile per ordine.
* Persistenza delle UTM (source, medium, campaign, content, term) nell'ordine corso e nei metadata Stripe.
* Il CAPI viene inviato solo quando il checkout conserva il cookie Meta _fbp; Dataset ID e access token sono configurabili dalla dashboard corso e non sono hardcodati nel plugin.

= 4.6.0 =
* I codici riservati diventano individuali: al posto del codice unico c'è un elenco di codici autorizzati, precaricato con 10 codici nel formato pediastudioXYZ.
* Ogni codice è monouso e viene consumato solo a pagamento confermato, così un tentativo fallito non lo brucia. Il comportamento è disattivabile dalla dashboard.
* Tabella di stato in Corso Anti-Panico: per ogni codice si vede se è libero o quale iscrizione l'ha usato, con link per liberarlo in caso di rimborso o cambio.
* Se al momento del pagamento il codice non è più valido, il checkout si ferma con un messaggio invece di addebitare silenziosamente il prezzo pieno.
* Messaggio distinto fra codice inesistente e codice già utilizzato.

= 4.5.0 =
* Codice riservato nel checkout: spunta opzionale che apre un campo "Codice". Con un codice valido il prezzo diventa quello concordato (di serie 65 € singolo e 110 € coppia).
* Il codice, i due prezzi ridotti, l'etichetta della spunta e l'attivazione sono configurabili in Corso Anti-Panico, senza toccare il codice del plugin.
* Il codice non viene mai stampato nella pagina né incluso negli script: la verifica avviene con una chiamata al server, e l'importo addebitato viene comunque ricalcolato lato server al momento del checkout, quindi non è manipolabile dal browser.
* Ogni iscrizione registra il codice usato e il prezzo di listino, sia nei dati dell'ordine sia nei metadata Stripe, per riconciliare gli incassi ridotti.

= 4.4.0 =
* Popup di prenotazione: aggiunti codice fiscale, indirizzo di residenza e città sotto email e telefono, con validazione lato server e salvataggio nei dati dell'iscrizione.
* Riepilogo finale del popup ridisegnato e centrato, con etichetta, separatore e totale in evidenza; rimossa la riga "posti disponibili su" (tolta anche dal JS che la popolava, per non lasciare riferimenti a un elemento inesistente).
* CORREZIONE: le iscrizioni pagate restavano "pending" in bacheca. Lo stato dipendeva unicamente dall'arrivo del webhook Stripe: se il webhook non veniva consegnato (endpoint irraggiungibile, firma non valida per orologio del server sfasato, evento non sottoscritto) l'ordine non veniva mai confermato. Ora la logica di conferma è condivisa e idempotente, e viene applicata anche interrogando direttamente Stripe: automaticamente all'apertura della pagina di ringraziamento e, per gli ordini già rimasti indietro, con il pulsante "Verifica pagamento su Stripe" nel dettaglio dell'iscrizione.
* Nuova email con tutti i dati raccolti inviata a formalife.it@gmail.com nel momento in cui il modulo viene compilato, prima dell'esito del pagamento. Le notifiche di pagamento confermato restano invariate.
* Spazi non separabili al confine col grassetto anche nel popup; testo della pagina di ringraziamento differenziato in base allo stato reale del pagamento.

= 4.3.0 =
Ancora solo estetica, layout e copy della landing del corso.

* Hero: colonne 66%/34% (immagine più piccola), stessa immagine riusata come sfondo di sezione al 10% di opacità, prezzo con corpo ridotto, bonus "Nuovo Libro sul Soffocamento incluso!" evidenziato, box coppia con cifre tonde e nuovo testo.
* Corretto il testo scuro sulle CTA rosse: ".fmh-course-page a { color: inherit }" aveva specificità maggiore del colore dichiarato dai pulsanti e vinceva su ogni CTA resa come <a>. Sistemato anche il colore di .fmh-text-link.
* Spazi non separabili (&nbsp;) al confine fra testo normale e testo in grassetto in tutte le sezioni con copy fisso.
* Sezione "La pratica": immagini in cover centrate.
* Sezione "Non ci conosci?": aggiunta Sara Bertoli (istruttrice); foto e bio arrivano dai campi già esistenti del pannello Formalife Home, senza nuovi campi in dashboard.
* Card "Superbonus incluso": "Prezzo di vendita", testo esteso e corpo leggermente maggiore.
* Rimossa la sezione "Dalla prenotazione a dopo il corso".
* Sezione date: più spazio sotto il titolo, contenuto delle card centrato, sede mostrata solo se scelta in bacheca, prezzi affiancati "120€ coppia" / "80€ singolo" con gerarchia visiva, riga bonus sotto il prezzo.
* Card "Offerta coppia" ridisegnata come confronto fra due riquadri con prezzo barrato e badge di risparmio; cifre senza decimali.
* Rimossa la FAQ sull'attestato; testo di chiusura centrato e offerta finale riscritta.

= 4.2.0 =
Solo estetica e layout della landing del corso: nessuna modifica a funzioni, link, dashboard, popup di checkout o flusso di pagamento.

* Ripristinato il reset CSS (margin/padding a zero) su .fmh-course-page, che era presente per la home ma non per il corso: era la causa delle spaziature raddoppiate e delle griglie <ol>/<ul> rientrate di 40px.
* Ridichiarati dentro course.css i componenti condivisi di frontend.css (.fmh-wrap, .fmh-eyebrow, .fmh-btn-primary, .fmh-date-badge), che il reset azzerava per pari specificità.
* Stilizzati i placeholder degli slot immagine vuoti: course.css puntava a ".fmh-course-placeholder", classe mai generata, mentre fmh_render_image() emette ".fmh-placeholder".
* Risolto il conflitto su .fmh-book-list, che ereditava display:flex e margini pensati per la home.
* Token e reset estesi a modale di checkout, CTA sticky e pagina di ringraziamento, che stanno fuori da .fmh-course-page e prima usavano solo valori di fallback.
* Fallback dei font allineati a quelli del plugin del libro ('Arial Rounded MT Bold' per Fredoka, Arial per Karla, Georgia per Lora) invece di system-ui.
* CTA sticky mobile ripristinata: l'attributo hidden nel markup ne impediva sempre la comparsa, perché il JS aggiunge solo la classe .is-visible.
* Rifiniti hero, timeline del metodo, card programma, sezione date, garanzia, FAQ e chiusura; offset di ancoraggio corretto sotto la barra sticky; nessun overflow orizzontale a 360, 390, 768, 1024 e 1440 px.

= 4.1.0 =
* Restyling completo della landing del Corso Anti-Panico: la pagina ora eredita gli stessi token di colore/tipografia/ombre/raggi della Home (erano ridichiarati con una palette diversa e incompleta, che tra l'altro lasciava i pulsanti .fmh-btn-primary e il contenitore .fmh-wrap privi delle variabili CSS necessarie — bottoni invisibili e contenuto non più centrato/limitato in larghezza).
* Corretto il bottone di chiusura del checkout, rimasto senza stile per una classe CSS non corrispondente al markup.
* Rivista sezione per sezione: hero, test interattivo, lettera, timeline del metodo, pratica, 6 card programma, bio/fonti, libro superbonus (mantiene l'eccezione cromatica teal/terracotta), journey, sessioni/coppia, garanzia, FAQ (accordion numerato), chiusura — con gerarchia tipografica, spaziature, ombre e stati hover coerenti con Home e libro.
* Corretti due bug di specificità CSS che rendevano illeggibile testo bianco su sfondo scuro (prezzo nella sezione finale, badge "Garanzia commerciale Formalife").
* Nessuna modifica a logica PHP, checkout, Stripe, sessioni dinamiche o readiness: solo markup/CSS della landing.

= 4.0.0 =
* Nuova landing in 14 sezioni per il Corso Anti-Panico al Soffocamento Pediatrico; slug pubblico `genitori-pronti` preservato.
* Sessioni dinamiche con ID stabile, attivazione, vendite aperte/chiuse, data, orario, città, sedi, capienza, nota, override prezzi e ordinamento.
* Migrazione non distruttiva delle sessioni legacy `a` e `b`; opzioni, ordini e post meta storici non vengono cancellati.
* Pricing server-authoritative: singolo 80 € e coppia 120 € di default, con una copia del libro e un posto per partecipante.
* Hold pending di 30 minuti, controllo capienza per persone e rifiuto backend della coppia quando resta un solo posto.
* PaymentIntent con metadata ordine/corso/sessione/data/partecipanti/tier; webhook firmato e idempotente.
* Stripe.js caricato soltanto al primo click checkout tramite loader FMH con Promise condivisa e retry.
* Google Fonts eliminato: `fonts.css` locale, `font-display: optional`, fallback di sistema e preload solo dei WOFF2 critici realmente presenti.
* Dashboard per copy, FAQ, Garanzia Serenità, bio, media, timeline, programma, libro, immagini finali e Course Control singoli/coppie/incassi.
* Vendite LIVE bloccate finché Privacy, Termini, garanzia, conferma policy, chiavi coerenti e webhook non sono pronti. La release non abilita vendite autonomamente.

= Test Stripe =
1. Configurare chiavi `pk_test_` / `sk_test_` coerenti e webhook test.
2. Collegare Privacy e Termini, mantenendo spento il flag LIVE.
3. Aprire le vendite su una sessione e abilitare il checkout generale.
4. Provare ordine singolo e coppia, verificando hold, webhook, email e thank-you.
5. Passare a LIVE soltanto dopo revisione delle policy e attivazione esplicita del flag amministrativo.

= 3.0.0 =
* Aggiunta landing gestita `genitori-pronti` nello stesso stile della homepage.
* Adozione sicura della pagina corso esistente senza cancellarne il contenuto WordPress.
* Dashboard "Corso Genitori Pronti" per date, prezzo, capienza, sede, immagini, policy e Stripe.
* Course Control con posti pagati/pending per sessione.
* Checkout embedded Stripe Payment Element con PaymentIntent calcolato lato server e webhook firmato.
* Iscrizioni corso archiviate in WordPress con data, posti, cliente, fatturazione opzionale e stato pagamento.
* Pagina di conferma dedicata e notifiche email dopo `payment_intent.succeeded`.
* Controllo disponibilità posti con hold temporaneo delle iscrizioni pending.


= 2.2.0 =
* Menu superiore sticky bianco (logo a sinistra, Home/Il libro/I corsi a destra), non invasivo.
* Corretto lo strato dell'immagine di sfondo della hero (era invertito rispetto al colore di sovrapposizione ed era invisibile); badge hero ora della stessa larghezza esatta del testo sopra; micro-testo sotto la CTA centrato; link di scroll con rientro laterale e maggiore interattività.
* Sezione "Perché esiste Formalife" ricolorata (sfondo blu navy scuro, titolo cream, testo bianco) e completata con la riga conclusiva mancante.
* Restyling leggero della sezione "C'è una differenza tra osservare e saper agire"; immagine di sfondo in trasparenza regolabile nella sezione "Osserva. Valuta. Agisci.".
* Sezione "Qual è il prossimo passo?": bordo su entrambi i box, CTA del corso blu navy, contenuti centrati, immagine 16:9 in ogni box.
* Seconda immagine nella sezione "Il libro", centrata verticalmente insieme alla copertina.
* Campo "Nome" del modulo lista d'attesa rinominato in "Nome e cognome" e reso obbligatorio; immagine di sfondo al 10% di opacità nella sezione.
* Sezione B2B con immagine quadrata a sinistra, CTA WhatsApp verde e CTA email in stile Gmail.
* Sezione garanzia completamente ridisegnata con icone ed elenco.
* Immagine di sfondo in trasparenza nella sezione team; carosello corsi riprogettato con due immagini sempre visibili e scorrimento continuo verso sinistra.
* Sezione "Verifica tu stesso" sostituita da "Qual è il costo di non agire?" (portata dalla landing del libro, ricolorata in navy).
* Pulsante CTA di chiusura reso visivamente identico agli altri pulsanti (stesso font/dimensioni, solo colori diversi) — corretto un conflitto di specificità CSS che lo rimpiccioliva rispetto al pulsante adiacente; box P.S. reso molto più risaltato.
* Sezione contatti finale allineata 1:1 alla sezione contatti della landing del libro (stessa struttura/icone), cambiando solo titolo e colore (navy invece di verde).

= 1.0.0 =
* Prima versione pubblica: pagina "Home" generata automaticamente con lo stesso motore di gestione pagine del plugin del libro (creazione automatica, riconoscimento tramite meta dedicato, avviso in caso di conflitto di slug, template dedicato che bypassa header/footer del tema).
* Sezioni: barra annuncio, hero con doppia CTA (corso pratico / libro), "La nostra filosofia" (il sistema in due passi), "Le nostre soluzioni" (card libro e card corso, con rimando alle rispettive pagine), "Perché fidarti di Formalife" (badge di rassicurazione blu/verde alternati), "Direzione scientifica" (Dott.ssa Camposarcone), FAQ, CTA finale su sfondo blu scuro, contatti (email/WhatsApp/Instagram), footer con link legali.
* Pannello impostazioni: immagini (hero, copertina libro, immagine corso, foto direzione scientifica, logo, immagine social), testi di hero/libro/corso, URL di libro e corso, contatti, URL delle pagine legali. Nessun campo colore: palette fissa via CSS per rispettare rigorosamente i colori approvati.
* Stesso stile grafico (font Fredoka/Karla/Lora, spaziature, raggi, ombre, animazioni "reveal" on-scroll) del plugin "Guida Anti-Panico al Soffocamento Pediatrico", con palette blu/rosso/verde propria e indipendente; i colori del libro vengono usati solo nella card dedicata al libro.
