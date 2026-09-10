=== Guida Anti-Panico al Soffocamento Pediatrico — Vendita libro ===
Contributors: formalife
Tags: landing page, ecommerce, stripe, pediatria
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 3.7.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Landing page di vendita per "La Guida Anti-Panico al Soffocamento Pediatrico" (Formalife), con pagine "Condizioni di vendita", "Privacy", "Grazie — Ordine confermato" e "I tuoi numeri importanti" generate automaticamente nello stesso stile, pannello impostazioni per immagini, colori, prezzo, date, dato statistico, dati legali/aziendali, link al corso pratico e popup d'acquisto con pagamento Stripe integrato.

== Descrizione ==

Il plugin genera automaticamente cinque pagine pubbliche, tutte con lo stesso design coerente:

* `guida-antipanico-soffocamento` — la landing del libro con le sue 12+ sezioni.
* `condizioni-di-vendita` — Condizioni di vendita complete (Codice del Consumo: recesso, garanzia legale di conformità, garanzia commerciale, pagamento, spedizione, responsabilità).
* `privacy` — Privacy Policy conforme al GDPR (Regolamento UE 2016/679), personalizzata sui dati realmente raccolti dal modulo d'acquisto e sul flusso di pagamento Stripe.
* `grazie-ordine-confermato` — pagina di ringraziamento post-pagamento: riepiloga l'ordine, spiega cosa succede adesso e propone, senza pressione, il passo successivo facoltativo verso il corso pratico "Genitori Pronti". Il cliente vi arriva in automatico dopo il pagamento (Stripe Payment Element, nessuna configurazione da fare su Stripe per questo passaggio).
* `i-miei-numeri` — pagina raggiunta dal QR code stampato nelle ultime pagine del libro (pp. 157-158): permette il download gratuito, con un solo click e senza alcun modulo, della scheda PDF "I tuoi numeri importanti"; chiude, in una sezione separata e non invasiva, con una richiesta di recensione Google.

Tutti gli elementi variabili (copertina, foto autrice, colori del brand, prezzo, date, dato statistico, sfondo sezione ammissione, immagine garanzia, anteprime del libro, PDF e anteprima della scheda "I tuoi numeri importanti", link recensione Google, contatti finali, link al corso pratico, dati legali/aziendali e link legali) sono configurabili dal pannello "Guida Anti-Panico" nel menu principale della bacheca. I dati legali non ancora compilati (ragione sociale, P.IVA, sede, PEC, ecc.) compaiono evidenziati in giallo direttamente sulle pagine pubbliche, come promemoria prima della pubblicazione definitiva.

= Dove trovare le impostazioni =

Bacheca WordPress → menu laterale "Guida Anti-Panico" (icona scudo). Gli ordini ricevuti si trovano nel sottomenu "Preordini ricevuti". In cima alla pagina impostazioni trovi gli URL di tutte e cinque le pagine pubbliche generate dal plugin, insieme a un promemoria a completare la configurazione del webhook Stripe (sezione "Notifiche e conferma di pagamento" più sotto): senza quella, i pagamenti riusciti non vengono mai segnati come "Pagato".

= Flusso di acquisto =

I pulsanti "Prenota ora" non puntano mai a un link diretto: aprono sempre un popup con un modulo (nome, cognome, telefono, email, indirizzo di spedizione, città, provincia e presa visione della privacy). Chi desidera la fattura può selezionare l'apposita casella e compilare intestatario, indirizzo di fatturazione, P.IVA e codice univoco o PEC. All'invio:

1. Il lead viene salvato in bacheca (sottomenu "Preordini ricevuti"), con stato di pagamento iniziale "in attesa". Nessuna email parte a questo punto, né al cliente né all'admin.
2. Il pagamento (carta, Link, PayPal, Apple Pay) avviene direttamente nello step 3 del popup, senza uscire dal sito (Stripe Payment Element).
3. Solo quando Stripe conferma — via webhook — che il pagamento è realmente andato a buon fine, il preordine passa allo stato "Pagato" e partono due email distinte, nello stesso momento: al cliente (ringraziamento, riepilogo dell'ordine e tempi di consegna) e all'indirizzo di notifica interno configurato (impostazioni → "Notifiche e conferma di pagamento"), che è il segnale per procedere con la spedizione.
4. Dopo il pagamento, il cliente viene reindirizzato alla pagina "Grazie — Ordine confermato" generata dal plugin: lì trova il riepilogo dell'ordine, i prossimi passi e — se configurato — il rimando facoltativo al corso pratico.

Se le chiavi Stripe non sono ancora configurate, il popup funziona comunque (il lead viene salvato) ma lo step di pagamento non può completarsi. Se la chiave segreta del webhook non è configurata, il plugin ignora per sicurezza ogni notifica di pagamento in arrivo (nessuna conferma automatica, ma nessun rischio di notifiche false).

== Installazione ==

1. Bacheca → Plugin → Aggiungi nuovo → Carica plugin.
2. Seleziona il file .zip del plugin e clicca "Installa ora".
3. Attiva il plugin: alla prima attivazione (o dopo ogni aggiornamento che introduce nuove pagine) vengono create automaticamente le pagine pubbliche mancanti.
4. Vai su Bacheca → Guida Anti-Panico per caricare le immagini, impostare i colori, il prezzo, le date, i contatti finali, il link al corso pratico, i link legali e le chiavi Stripe (pubblicabile e segreta).
5. Sempre nel pannello impostazioni, sezione "Notifiche e conferma di pagamento": copia l'URL dell'endpoint webhook mostrato lì, incollalo in Stripe → Sviluppatori → Webhook → Aggiungi endpoint (eventi: payment_intent.succeeded, payment_intent.payment_failed), poi incolla nel plugin la chiave segreta ("Signing secret") che Stripe ti mostra. Senza questo passaggio i pagamenti vanno comunque a buon fine, ma il plugin non può segnarli come "Pagato" né inviare le email di conferma.

== Changelog ==

= 3.7.5 =
* Corretto il modello di repository: `github.com/formalife/claude-web` (introdotto in 3.7.4) resta il workspace di coordinamento per tutti i plugin Formalife, ma la sorgente degli aggiornamenti automatici di questo plugin punta ora a un repository dedicato, `github.com/formalife/plugin-guida-al-soffocamento`, sincronizzato da `claude-web` ad ogni rilascio. Necessario perché Plugin Update Checker legge il changelog solo dalla radice del repository, e più plugin coordinati nello stesso repo di sviluppo ne avrebbero uno solo alla radice.

= 3.7.4 =
* Repository ricreato da zero su `github.com/formalife/claude-web` (monorepo di coordinamento per il web design Formalife). Aggiornata di conseguenza la sorgente degli aggiornamenti automatici (`Update URI` e `GAPS_UPDATE_REPOSITORY`).

= 3.7.3 =
* Corretto l'URL del repository nell'aggiornatore automatico (Plugin Update Checker): puntava a `github.com/formalife/...`, verificato che il repository vive davvero su `github.com/formalife-personal/...`. Senza questa correzione, l'aggiornamento automatico in bacheca poteva non essere affidabile.

= 3.7.2 =
* La spedizione non è più gratuita: nuovo campo impostazioni "Costo di spedizione" (default 2,90 €), addebitato una sola volta per ordine (non per copia) insieme al prezzo del libro, nello stesso pagamento Stripe. Aggiornato di conseguenza il calcolo dell'importo lato server (`gaps_get_shipping_cents()`), le Condizioni di vendita (artt. 3 e 6), l'email di conferma e la pagina "Grazie".
* Nella landing page non viene più indicato alcun costo di spedizione (né "gratuita", né l'importo): il costo compare, con discrezione, solo nello step di pagamento del popup, sotto il totale, insieme ai tempi di consegna.
* Aggiornato il default del campo "Data spedizione" da una data di prevendita ormai passata a "in 4-5 giorni lavorativi".
* Corrette tre affermazioni di "spedizione/consegna gratuita" rimaste sbagliate dopo l'introduzione del costo di spedizione (badge nella pagina "Grazie", email di conferma, Privacy/Condizioni di vendita).
* Il riquadro "Dettagli preordine" in bacheca mostra ora la scomposizione dell'importo (libro + spedizione) per ogni ordine.

= 3.7.1 =
* Corretto un bug per cui un pagamento Stripe andato a buon fine poteva restare bloccato su "In attesa di pagamento" in "Preordini ricevuti", senza inviare le email di conferma: il pannello impostazioni ora avverte esplicitamente (avviso nella pagina impostazioni + avviso in tutta la bacheca) quando le chiavi Stripe sono configurate ma manca ancora la chiave segreta del webhook, che è la causa più comune di questo sintomo.
* Rimosso un promemoria obsoleto che invitava a impostare la pagina "Grazie" come destinazione post-pagamento in un "Payment Link" o "Checkout" di Stripe: quel meccanismo non esiste più dalla v3.6.0 (il redirect è automatico, via Payment Element). Il promemoria ora punta correttamente al passaggio realmente necessario: la registrazione del webhook.
* Copy aggiornato dalla fase di prevendita alla vendita normale: rimossi la scadenza di prenotazione e i richiami a "prevendita" dalla landing page (badge, offerta, CTA, riga di urgenza), dalle Condizioni di vendita (articolo 4) e dalla Privacy Policy, senza toccare prezzo, garanzia o altri contenuti.
* Aggiornate le istruzioni di installazione nel readme e alcune etichette solo-admin per riflettere il flusso di pagamento attuale.

= 3.7.0 =
* Prestazioni mobile: rimossi gli enqueue di Google Fonts (fonts.googleapis.com / fonts.gstatic.com). Fredoka, Karla e Lora vengono ora dichiarati con @font-face locali (`assets/css/fonts.css`), con preload solo dei pesi realmente usati sopra la piega e `font-display: optional` per evitare cambi tardivi di font nella Hero. I file WOFF2 vanno copiati manualmente in `assets/fonts/` (vedi il README in quella cartella): finché non sono presenti, il CSS usa i font di fallback di sistema, senza errori 404.
* Stripe.js non viene più caricato staticamente su nessuna pagina: nuovo loader condiviso (`assets/js/gaps-stripe-loader.js`) che scarica lo script ufficiale (sempre da js.stripe.com) solo al primo click su un CTA che apre il popup di preordine, con Promise condivise per evitare caricamenti duplicati su click ripetuti o CTA diverse nella stessa pagina.
* Popup di preordine: il pulsante "Continua al pagamento" mostra ora uno stato di caricamento esplicito (spinner + testo, `aria-busy`) mentre Stripe e il Payment Intent vengono preparati in parallelo, con gestione degli errori e nuovo tentativo senza duplicare lo script Stripe.
* Nuovo supporto opzionale per Meta Pixel: campo "Meta Pixel ID" nel pannello impostazioni (vuoto di default, nessuno script caricato finché non viene compilato). Se configurato, il Pixel viene caricato solo dopo le risorse critiche della pagina (font, immagine di copertina, popup) e solo con consenso ai cookie di marketing valido.
* Nuovi campi "Anteprima libro — desktop" e "Anteprima libro — mobile" nel pannello impostazioni: immagine responsive dedicata (via `<picture>`) per il pulsante "Sfoglia un'anteprima" della sezione Prova, con fallback automatico sul vecchio campo singolo per le landing già pubblicate.
* Nuovo renderer condiviso delle immagini (`gaps_render_attachment_image()`), usato ora da tutte le immagini della Libreria Media stampate dal plugin: width/height reali, srcset, sizes, `loading="lazy"` e `decoding="async"` automatici, senza più tag `<img>` con solo `src`.
* La copertina della Hero è ora l'unica immagine con `fetchpriority="high"` e `loading="eager"` della pagina (nuova funzione dedicata `gaps_render_hero_cover_image()`), per migliorare il Largest Contentful Paint.
* Nuove dimensioni immagine registrate (`add_image_size()`): copertina Hero e le due varianti dell'anteprima libro, senza crop distruttivo.
* CSS e JS del plugin (dove compatibile con la versione di WordPress) caricati con strategia "defer" tramite `wp_script_add_data()`, per non bloccare il rendering iniziale della pagina.

= 3.6.1 =
* Rimossa l'email admin inviata alla semplice compilazione del modulo di preordine: ora nessuna email parte finché il pagamento non è confermato da Stripe.
* Aggiunta una nuova email automatica al cliente, inviata insieme alla notifica interna solo a pagamento confermato: ringraziamento, riepilogo dell'ordine (copie, totale pagato, indirizzo di spedizione, eventuale fattura) e tempi di consegna, ripresi dallo stesso campo "Data spedizione" già mostrato in landing e nella pagina "Grazie" (nessuna data diversa promessa al cliente).
* Aggiornati i testi del pannello "Notifiche e conferma di pagamento" per riflettere il nuovo flusso a doppia email solo post-pagamento.

= 3.5.3 =
* Nuova pagina pubblica generata automaticamente, nello stesso stile delle altre: "I tuoi numeri importanti" (`i-miei-numeri`), raggiunta dal QR code stampato nelle ultime pagine del libro (pp. 157-158). Permette il download gratuito della scheda PDF con un solo click, senza alcun modulo da compilare; il PDF e l'anteprima visiva sono inclusi di serie nel plugin (funzionano subito dopo l'aggiornamento, senza configurazione).
* Sezione finale di richiesta recensione Google, separata dal download e non invasiva: propone la recensione solo a chi ha trovato valore nel libro, e invita chi ha dubbi a scrivere prima ai contatti Formalife invece di lasciare una recensione a metà.
* Nuova sezione impostazioni "Scheda 'I tuoi numeri importanti'": sovrascrittura opzionale del PDF e dell'immagine di anteprima dalla Libreria Media, campo per il link alla recensione Google (lasciare vuoto per nascondere la sezione).
* Nuovo foglio di stile dedicato (`assets/css/numeri.css`), caricato solo su questa pagina, che riusa le stesse variabili colore/font e gli stessi componenti già condivisi dalle altre pagine del plugin.
* Nuovo uploader generico per campi file (non immagine) nel pannello impostazioni, usato per la sovrascrittura opzionale del PDF.

= 3.5.2 =
* Rimosso il codice fiscale obbligatorio dal popup di preordine.
* Aggiunta la scelta facoltativa “Voglio la fattura”: quando selezionata mostra, uno per riga, intestatario, indirizzo di fatturazione, P.IVA e codice univoco o PEC; i dati vengono validati, salvati nel preordine e inclusi nella notifica amministrativa.
* Aggiornati Condizioni di vendita e Privacy Policy per descrivere correttamente la raccolta facoltativa dei dati fiscali.
* Ridisegnata integralmente la pagina post-acquisto con maggiore gerarchia visiva, riepilogo dell'ordine, checklist interattiva salvata solo sul dispositivo, suggerimenti utili nell'attesa, timeline, FAQ espandibili, assistenza più visibile e passaggio facoltativo al corso pratico.
* Aggiunto uno script dedicato alla pagina “Grazie” per animazioni accessibili e checklist, con pieno supporto a `prefers-reduced-motion`.

= 3.5.1 =
* Integrato Plugin Update Checker 5.7 per ricevere in WordPress le notifiche e gli aggiornamenti con un clic dalle release pubbliche del repository GitHub ufficiale.
* Aggiunto l'header `Update URI` per identificare in modo univoco la sorgente degli aggiornamenti e prevenire collisioni con plugin omonimi.
* Configurato l'uso dello ZIP installabile allegato automaticamente a ogni GitHub Release.

= 3.5.0 =
* Nuova pagina pubblica generata automaticamente, nello stesso stile delle altre: "Grazie — Ordine confermato" (`grazie-ordine-confermato`), pensata come pagina di conferma da impostare in Stripe dopo il pagamento. Riepiloga cosa è stato acquistato (copertina, prezzo, spedizione gratuita, scheda numeri d'emergenza in omaggio, garanzia), spiega in tre passi cosa succede da qui in avanti e chiude con un rimando facoltativo, senza alcuna pressione, al corso pratico "Genitori Pronti".
* Nuovo campo impostazioni "URL corso pratico (cross-sell nella pagina 'Grazie')": se lasciato vuoto, la sezione di rimando al corso semplicemente non compare.
* Nuovo foglio di stile dedicato (`assets/css/thankyou.css`), caricato solo su questa pagina, che riusa le stesse variabili colore/font e gli stessi componenti (pulsanti, badge, passi della garanzia, contatti, footer) già condivisi da landing e pagine legali.
* Il pannello impostazioni mostra ora l'URL di tutte e quattro le pagine pubbliche generate dal plugin, con un promemoria a impostare la pagina "Grazie" come destinazione post-pagamento nel Payment Link Stripe.

= 3.4.0 =
* Rifiniture riservate esclusivamente alla versione mobile (≤860px): la versione desktop non è stata toccata.
* Nuova barra CTA fissa in basso, trasparente, con il solo pulsante "Prenota ora": compare dopo aver superato la hero, scompare mentre è visibile il box P.S. della sezione finale, ricompare risalendo sopra quel box.
* Testi centrati invece che allineati a sinistra in hero, storia, sezione autrice, box e card della sezione "Il sistema", citazione della sezione "Prova", box P.S. (le liste puntate con icona — offerta, FAQ, passi della garanzia — restano volutamente allineate a sinistra per leggibilità).
* Immagine del box verde "Il sistema" rimpicciolita e mostrata per intero (object-fit: contain) invece di essere ritagliata.
* Pulsante "Sfoglia un'anteprima del libro" ingrandito ed evidenziato (etichetta a pillola colorata, leggera animazione), per invitare più chiaramente al click.
* Lightbox di anteprima: frecce precedente/successiva nascoste su mobile, sostituite dallo scorrimento a trascinamento (swipe touch); restano invariate su desktop.
* Più rientro tra il bordo superiore della sezione "Un libro non può allenare le tue mani" e l'inizio del testo.
* CTA della sezione "Tra sei mesi..." più marcata (peso tipografico maggiore) e con font più grande.
* Più distanza tra il testo e il pulsante CTA nel box P.S.

= 3.3.0 =
* Nuovo endpoint webhook Stripe (`/wp-json/gaps/v1/stripe-webhook`, vedi `class-gaps-stripe-webhook.php`) che riceve e verifica crittograficamente (firma HMAC-SHA256 con la chiave segreta del webhook) gli eventi `checkout.session.completed`, `checkout.session.async_payment_succeeded`, `checkout.session.async_payment_failed` e `checkout.session.expired`.
* Ogni preordine viene ora collegato alla relativa sessione di pagamento Stripe tramite `client_reference_id` (vedi `GAPS_CTA::get_url_for_order()`), così da poter riconoscere con certezza, dal webhook, a quale richiesta corrisponde un pagamento realmente ricevuto.
* Due email distinte: una alla compilazione del modulo ("richiesta in attesa di pagamento", non è un ordine confermato) e una separata, con oggetto "Pagamento confermato", inviata solo quando Stripe conferma l'avvenuto pagamento — è quest'ultima il segnale corretto per procedere con la spedizione.
* Nuova sezione impostazioni "Notifiche e conferma di pagamento": indirizzo email di notifica (default `info@formalife.it`), URL dell'endpoint webhook da incollare su Stripe, chiave segreta del webhook.
* Elenco "Preordini ricevuti" in bacheca: nuova colonna "Pagamento" con badge colorato (in attesa / in verifica / pagato / fallito / scaduto) e filtro a tendina per isolare rapidamente i preordini realmente pagati; il riquadro di dettaglio del singolo preordine mostra anche data di pagamento e ID sessione Stripe.

= 3.2.0 =
* Nuove pagine pubbliche generate automaticamente, con lo stesso identico design della landing (stessi colori, font, componenti, footer): "Condizioni di vendita" (`condizioni-di-vendita`) e "Privacy" (`privacy`).
* Nuovo motore di gestione pagine multiplo (`GAPS_Page_Manager::get_registry()`): landing, condizioni, privacy condividono lo stesso meccanismo di creazione/riconoscimento/gestione conflitti di slug, pronto per ospitare ulteriori pagine in futuro.
* Nuova sezione "Dati legali e azienda" nel pannello impostazioni: ragione sociale, P.IVA/C.F., sede legale, PEC, REA e capitale sociale (opzionali), foro competente, corriere di spedizione, durata della garanzia commerciale, data di ultimo aggiornamento dei testi legali. I campi non ancora compilati sono evidenziati in giallo sulle pagine pubbliche.
* Contenuto legale completo e personalizzato: Condizioni di vendita (oggetto e venditore, prezzo e prevendita, pagamento via Stripe, spedizione, diritto di recesso di 14 giorni ex Codice del Consumo, garanzia legale di conformità, garanzia commerciale "soddisfatti o rimborsati", limitazione di responsabilità sul contenuto informativo/non sostitutivo della formazione pratica, proprietà intellettuale, foro competente e piattaforma ODR); Privacy Policy conforme al GDPR (titolare, dati raccolti dal modulo di preordine, finalità e basi giuridiche, conservazione, destinatari inclusa Stripe, trasferimenti extra-UE, diritti dell'interessato, reclamo al Garante, cookie).
* I link "Condizioni di vendita" e "Privacy" nel footer della landing e nel popup di preordine ora puntano automaticamente alle nuove pagine generate dal plugin (i campi "Link legali" nel pannello restano disponibili solo come sovrascrittura manuale opzionale).
* Assets frontend (font, variabili colore, pulsanti) condivisi tra landing e pagine legali per garanzia di coerenza visiva; nuovo foglio di stile dedicato alla tipografia dei testi legali lunghi (indice, richiami, tabelle).

= 3.1.3 =
* Sezione "Il sistema": le cinque card ora sono disposte una sotto l'altra (non più affiancate), con l'icona a sbalzo sull'angolo superiore sinistro della card invece che al centro.
* Sezione "Un libro non può allenare le tue mani": aumentato ulteriormente il padding superiore.
* Sezione Offerta: copertina ingrandita e spostata in sovrapposizione sul bordo sinistro della card bianca; rimossa la menzione degli autori dal primo punto elenco e la frase "la stampi, la attacchi vicino al telefono..." dal punto sulla scheda numeri d'emergenza; il testo dell'elenco ora sfrutta lo spazio liberato dall'immagine, mentre tutto il blocco dal prezzo in giù (prezzo, badge data, testo esplicativo, pulsante) è centrato rispetto all'intera card, a prescindere dalla copertina sovrapposta.
* Sezione "Tra sei mesi...": le tre immagini fluttuanti sono state spostate più verso il centro (erano troppo ai lati).
* Box P.S.: rimosso l'effetto "carta strappata" introdotto nella revisione precedente — box tornato a uno stile pulito e semplice, con un accento superiore colorato.

= 3.1.2 =
* Corretto un bug per cui, in alcune installazioni, il menu laterale "Guida Anti-Panico" mostrava solo il sottomenu "Preordini ricevuti" e non la vera pagina impostazioni (immagini, colori, contenuti). Causa: il custom post type "Preordini" usa lo stesso slug del plugin come voce padre nel menu; senza un sottomenu esplicito per la pagina impostazioni, WordPress poteva far "sparire" il suo link nella barra laterale. Ora la pagina impostazioni viene registrata sia come voce di primo livello sia come sottomenu esplicito "Impostazioni", con priorità più alta rispetto alla registrazione del sottomenu del custom post type: dopo l'aggiornamento, in Bacheca → Guida Anti-Panico dovrebbero comparire distintamente sia "Impostazioni" sia "Preordini ricevuti".

= 3.1.1 =
* Ripristinata la versione 3.0.0 stabile (con pannello impostazioni completo) e riapplicate sopra di essa, con verifica riga per riga, tutte le richieste della revisione precedente: barra annuncio/hero (badge riformulato, copertina più contenuta, CTA meno "larga", più respiro tra colonne), storia (effetto carta più marcato, badge "A voce nostra" rimosso, frase chiave scorporata, badge finale riscritto e più stretto), problema (corpo diviso frase per frase con "Non è colpa tua" in grassetto, tre card ridisegnate), sistema (icone semi-sovrapposte al bordo delle card, "Ambiente domestico"), prova (titolo aggiornato, spazi non discendenti su Ferma/Valuta/Agisci, anteprima cliccabile al posto del pulsante), ammissione (bordo a freccia singola, più padding, spazio non discendente dopo il titolo del libro), dati (badge alla larghezza del contenuto, icone ingrandite e sovrapposte, chiusura su tre righe con l'ultima in grassetto), offerta (layout a due colonne con il mockup del libro, icona per ogni voce, evidenza sulla spedizione gratuita), FAQ (più margine tra domanda e risposta), garanzia (immagine libera più grande senza cerchio, grassetto sulla frase finale), CTA finale (tre immagini fluttuanti configurabili, citazione isolata e in grassetto, box P.S. con effetto carta strappata e CTA centrata), contatti (titolo aggiornato).
* Tre nuovi campi immagine opzionali nel pannello impostazioni: "Immagine fluttuante 1/2/3" per la sezione "Tra sei mesi...". Verificato che il pannello impostazioni resti completo e funzionante con tutti i campi immagine/colore/testo esistenti.

= 3.0.0 =
* Revisione grafica completa: nuova barra annuncio superiore, hero molto più impattante (CTA con pulsazione, badge urgenza, titolo a due colori), transizione ondulata tra hero e sezione storia.
* Sezione "Una storia vera" con texture carta impercettibile, bordi "strappati", frase chiave evidenziata e box di commento autoriale ridisegnato con forte contrasto visivo.
* Sezione "Il problema" interamente ricentrata, contenuti a griglia invece di card piatte, chiusura evidenziata in un box con bordo.
* Sezione autrice: foto più contenuta, effetto hover.
* Sezione "Il sistema" ridisegnata: box chiave con immagine opzionale in sovrapposizione (1/3 immagine, 2/3 testo), ombra invece di bordo colorato, cinque card con badge icona più grandi.
* Sezione "Prova": eliminate le miniature in pagina, anteprima consultabile solo dal popup dedicato tramite un pulsante più impattante; card finali con effetti più marcati.
* Sezione "Un libro non può allenare le tue mani": più margine interno, sfocatura dello sfondo regolabile dal pannello impostazioni, bordi a "triangolo" in alto e in basso.
* Sezione "I dati, quelli veri" portata su sfondo chiaro con card statistiche professionali (era illeggibile su sfondo scuro).
* Sezione Offerta: schema colori rivisto (sfondo teal pieno + card bianca), checklist ampliata a 5 voci, copy sul prezzo e sulla prevendita riscritto per essere più persuasivo senza svelare la logica interna delle tirature.
* FAQ animate (fade-in, hover, numerazione).
* Sezione Garanzia: stesso schema colori chiaro dell'offerta, badge a scudo/immagine semi-sovrapposto al bordo del box, passaggi disposti verticalmente.
* CTA finale: testi più grandi, P.S. con lo stesso effetto carta della sezione storia e più distanza dal blocco precedente.
* Nuove icone a colori (stile Gmail, WhatsApp, Instagram) nella sezione contatti, tutte della stessa dimensione.
* Modulo di preordine ampliato: Città, Provincia e Codice Fiscale (con tooltip informativo), layout a due colonne; testo dei pulsanti CTA sempre in grassetto.
* Consolidamento di ogni "&nbsp;" attorno a grassetti/corsivi in un'unica stringa `wp_kses()`, per eliminare la dipendenza da spazi letterali tra tag PHP separati.

= 2.0.0 =
* Redesign completo del frontend (nuovi componenti, animazioni on-scroll, contrasto colori rivisto).
* Popup di preordine (nome, cognome, telefono, email, indirizzo) con reindirizzamento a Stripe dopo l'invio, al posto del link diretto.
* Nuovo custom post type privato "Preordini" per archiviare i lead in bacheca.
* Sezione "Costo dell'inazione" e box "Il sistema"/"Prova" ridisegnati con icone e card interattive.
* Sfondo con opacità regolabile per la sezione "Un libro non può allenare le tue mani".
* Sezione Garanzia ridisegnata con immagine configurabile e passi numerati.
* Nuova sezione contatti finale (email, WhatsApp, Instagram, logo) e link legali nel footer.
* Popup "Sfoglia un'anteprima del libro" con fino a 3 immagini configurabili.
* FAQ riformulate come domande; risposta sul prezzo corretta.

= 1.0.0 =
* Prima versione pubblica.
