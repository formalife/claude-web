<?php
/**
 * Contenuto testuale delle pagine legali (Condizioni di vendita, Privacy
 * Policy). Il "motore" grafico è in templates/template-legal.php: qui
 * vive solo il copy, personalizzato con le impostazioni del plugin
 * (prezzo, date, contatti, dati aziendali) tramite gaps_get_settings() e
 * gaps_legal_field() (che evidenzia i dati aziendali non ancora
 * compilati — vedi includes/gaps-settings-helpers.php).
 *
 * Nota generale sui contenuti: le presenti Condizioni di vendita e
 * Privacy Policy sono redatte sulla base del Codice del Consumo
 * (D.Lgs. 206/2005 e succ. mod.) e del Regolamento (UE) 2016/679 (GDPR),
 * personalizzate sul prodotto e sul flusso di acquisto reali di questo
 * plugin (preordine + reindirizzamento a Stripe). Non sostituiscono una
 * revisione legale: prima della pubblicazione definitiva è opportuno
 * farle rivedere da un legale o da un consulente privacy, in particolare
 * per i dati aziendali ancora evidenziati come segnaposto.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stampa un titolo di sezione numerato e con ancora, nello stesso formato
 * per Condizioni di vendita e Privacy Policy.
 *
 * @param string $number Numero della sezione (es. "1").
 * @param string $anchor ID HTML dell'ancora (deve combaciare con la voce corrispondente nel TOC).
 * @param string $title  Titolo della sezione, già tradotto.
 */
function gaps_legal_heading( $number, $anchor, $title ) {
	printf(
		'<h2 id="%1$s"><span class="gaps-legal-num">%2$s.</span> %3$s</h2>',
		esc_attr( $anchor ),
		esc_html( $number ),
		esc_html( $title )
	);
}

/**
 * Stampa il riquadro con i dati identificativi dell'azienda, riusato sia
 * nella sezione "Chi siamo" delle Condizioni di vendita sia nella sezione
 * "Titolare del trattamento" della Privacy.
 *
 * @param array $settings Impostazioni del plugin.
 */
function gaps_legal_company_box( $settings ) {
	?>
	<div class="gaps-legal-company-box">
		<p><strong><?php esc_html_e( 'Ragione sociale:', 'guida-antipanico-soffocamento' ); ?></strong> <?php gaps_legal_field( $settings['legal_company_name'] ); ?></p>
		<p><strong><?php esc_html_e( 'Partita IVA / Codice Fiscale:', 'guida-antipanico-soffocamento' ); ?></strong> <?php gaps_legal_field( $settings['legal_vat_number'] ); ?></p>
		<p><strong><?php esc_html_e( 'Sede legale:', 'guida-antipanico-soffocamento' ); ?></strong> <?php gaps_legal_field( $settings['legal_address'] ); ?></p>
		<?php if ( '' !== trim( $settings['legal_rea'] ) ) : ?>
			<p><strong><?php esc_html_e( 'Numero REA:', 'guida-antipanico-soffocamento' ); ?></strong> <?php echo esc_html( $settings['legal_rea'] ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== trim( $settings['legal_share_capital'] ) ) : ?>
			<p><strong><?php esc_html_e( 'Capitale sociale:', 'guida-antipanico-soffocamento' ); ?></strong> <?php echo esc_html( $settings['legal_share_capital'] ); ?></p>
		<?php endif; ?>
		<p><strong><?php esc_html_e( 'PEC:', 'guida-antipanico-soffocamento' ); ?></strong> <?php gaps_legal_field( $settings['legal_pec'] ); ?></p>
		<?php if ( $settings['contact_email'] ) : ?>
			<p><strong><?php esc_html_e( 'Email di contatto:', 'guida-antipanico-soffocamento' ); ?></strong> <a href="mailto:<?php echo esc_attr( $settings['contact_email'] ); ?>"><?php echo esc_html( $settings['contact_email'] ); ?></a></p>
		<?php endif; ?>
		<?php if ( '' !== trim( $settings['legal_dpo_email'] ) ) : ?>
			<p><strong><?php esc_html_e( 'Referente protezione dati:', 'guida-antipanico-soffocamento' ); ?></strong> <?php echo esc_html( $settings['legal_dpo_email'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/* =====================================================================
 * CONDIZIONI DI VENDITA
 * ===================================================================*/

/**
 * Indice della pagina "Condizioni di vendita". Le chiavi (ancore) devono
 * combaciare esattamente con gli id usati in gaps_render_terms_content().
 *
 * @return array<string, string>
 */
function gaps_get_terms_toc() {
	return array(
		'oggetto'                => __( 'Oggetto e accettazione', 'guida-antipanico-soffocamento' ),
		'venditore'               => __( 'Chi siamo — il venditore', 'guida-antipanico-soffocamento' ),
		'prodotto-prezzo'         => __( 'Il prodotto e il prezzo', 'guida-antipanico-soffocamento' ),
		'conclusione-contratto'   => __( 'Come si conclude il contratto', 'guida-antipanico-soffocamento' ),
		'pagamento'               => __( 'Pagamento', 'guida-antipanico-soffocamento' ),
		'spedizione'              => __( 'Spedizione e consegna', 'guida-antipanico-soffocamento' ),
		'recesso'                 => __( 'Diritto di recesso', 'guida-antipanico-soffocamento' ),
		'garanzia-legale'         => __( 'Garanzia legale di conformità', 'guida-antipanico-soffocamento' ),
		'garanzia-commerciale'    => __( 'Garanzia commerciale "Soddisfatti o rimborsati"', 'guida-antipanico-soffocamento' ),
		'responsabilita'          => __( 'Limiti del contenuto e responsabilità', 'guida-antipanico-soffocamento' ),
		'proprieta-intellettuale' => __( 'Proprietà intellettuale', 'guida-antipanico-soffocamento' ),
		'dati-personali'          => __( 'Dati personali', 'guida-antipanico-soffocamento' ),
		'legge-foro'              => __( 'Legge applicabile e foro competente', 'guida-antipanico-soffocamento' ),
		'controversie'            => __( 'Risoluzione delle controversie', 'guida-antipanico-soffocamento' ),
		'modifiche'               => __( 'Modifiche alle condizioni', 'guida-antipanico-soffocamento' ),
		'contatti'                => __( 'Contatti', 'guida-antipanico-soffocamento' ),
	);
}

/**
 * Stampa il contenuto completo della pagina "Condizioni di vendita".
 *
 * @param array $settings Impostazioni del plugin.
 */
function gaps_render_terms_content( $settings ) {
	$price       = $settings['price'];
	$shipping_price = $settings['shipping_price'];
	$delivery    = $settings['date_delivery'];
	$carrier     = '' !== trim( $settings['shipping_carrier'] ) ? $settings['shipping_carrier'] : __( 'corriere espresso incaricato da Formalife', 'guida-antipanico-soffocamento' );
	$guarantee_d = $settings['guarantee_period_days'];
	$privacy_url = gaps_get_privacy_url();
	?>

	<p class="gaps-legal-intro">
		<?php
		printf(
			/* translators: %s: nome del libro */
			esc_html__( 'Le presenti Condizioni di vendita regolano l\'acquisto di "%s", il libro pubblicato da Formalife, prenotato attraverso questo sito. Leggile con attenzione prima di completare la prenotazione: contengono informazioni importanti sui tuoi diritti come consumatore, incluso il diritto di recesso e le garanzie applicabili.', 'guida-antipanico-soffocamento' ),
			esc_html__( 'La Guida Anti-Panico al Soffocamento Pediatrico', 'guida-antipanico-soffocamento' )
		);
		?>
	</p>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '1', 'oggetto', __( 'Oggetto e accettazione', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Le presenti Condizioni di vendita disciplinano la vendita a distanza del prodotto descritto all\'articolo 3, effettuata tramite questo sito (di seguito anche "il Sito"), tra il Venditore indicato all\'articolo 2 (di seguito anche "Formalife", "noi" o "il Venditore") e la persona fisica che effettua l\'acquisto per scopi estranei alla propria eventuale attività imprenditoriale, commerciale, artigianale o professionale (di seguito "il Cliente" o "tu").', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Completando il modulo di prenotazione e procedendo al pagamento, dichiari di aver letto, compreso e accettato integralmente le presenti Condizioni di vendita, nonché l\'Informativa sulla Privacy richiamata all\'articolo 12.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Le presenti Condizioni si applicano ai rapporti con consumatori ai sensi dell\'art. 3, comma 1, lett. a) del Codice del Consumo (D.Lgs. 6 settembre 2005, n. 206, come successivamente modificato). Se acquisti nell\'esercizio di un\'attività imprenditoriale, commerciale, artigianale o professionale, alcune tutele descritte in questo documento — in particolare il diritto di recesso di cui all\'articolo 7 — potrebbero non essere a te applicabili: in tal caso ti invitiamo a contattarci prima di procedere.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '2', 'venditore', __( 'Chi siamo — il venditore', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Il venditore del prodotto descritto in queste Condizioni è:', 'guida-antipanico-soffocamento' ); ?></p>
		<?php gaps_legal_company_box( $settings ); ?>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '3', 'prodotto-prezzo', __( 'Il prodotto e il prezzo', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( '"La Guida Anti-Panico al Soffocamento Pediatrico" è un libro fisico di 160 pagine, copertina rigida, scritto dalla Dott.ssa Mafalda Camposarcone (pediatra, Direttrice Scientifica di Formalife) insieme a Raffaele La Torre (CEO e istruttore di Formalife), e verificato sulle linee guida ERC 2025 e IRC disponibili al momento della pubblicazione.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'L\'offerta attuale comprende:', 'guida-antipanico-soffocamento' ); ?></p>
		<ul>
			<li><?php esc_html_e( 'il libro descritto sopra;', 'guida-antipanico-soffocamento' ); ?></li>
			<li><?php esc_html_e( 'la spedizione in tutta Italia, al costo indicato all\'articolo 6;', 'guida-antipanico-soffocamento' ); ?></li>
			<li><?php esc_html_e( 'la scheda "I tuoi numeri importanti", stampabile, in omaggio;', 'guida-antipanico-soffocamento' ); ?></li>
			<li><?php esc_html_e( 'l\'accesso prioritario alle informazioni sui corsi pratici Formalife, a numero chiuso.', 'guida-antipanico-soffocamento' ); ?></li>
		</ul>
		<p>
			<?php
			printf(
				/* translators: 1: prezzo del libro, 2: costo di spedizione */
				esc_html__( 'Il prezzo del libro è %1$s, comprensivo di IVA. Le spese di spedizione (%2$s, vedi articolo 6) si aggiungono al momento dell\'ordine: il totale addebitato, comprensivo di spedizione, è sempre mostrato nel riepilogo prima di confermare il pagamento, salvo diversa indicazione mostrata al momento dell\'ordine.', 'guida-antipanico-soffocamento' ),
				esc_html( $price ),
				esc_html( $shipping_price )
			);
			?>
		</p>
		<p><?php esc_html_e( 'Le immagini del prodotto pubblicate sul Sito hanno valore indicativo: trattandosi di un prodotto editoriale, eventuali minime variazioni cromatiche o di rilegatura rispetto alle immagini non incidono sulla validità dell\'acquisto. Formalife si riserva il diritto di modificare prezzo e composizione dell\'offerta per gli acquisti futuri, senza che ciò incida in alcun modo sugli ordini già confermati.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '4', 'conclusione-contratto', __( 'Come si conclude il contratto', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'L\'acquisto avviene in due passaggi: prima compili il modulo di prenotazione (nome, cognome, telefono, email, indirizzo di spedizione, città e provincia) aperto dal pulsante "Acquista ora"; se desideri la fattura puoi selezionare l\'apposita casella e inserire i dati di fatturazione richiesti. Subito dopo vieni reindirizzato alla pagina di pagamento sicura di Stripe, dove inserisci i dati del metodo di pagamento scelto e completi l\'ordine.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Il contratto si considera concluso nel momento in cui il pagamento viene confermato dal sistema di pagamento. Riceverai un\'email di conferma all\'indirizzo indicato nel modulo: conservala come prova d\'acquisto, insieme alla ricevuta o fattura che ti verrà inviata.', 'guida-antipanico-soffocamento' ); ?></p>
		<div class="gaps-legal-note">
			<p>
				<?php
				printf(
					/* translators: %s: data di spedizione */
					esc_html__( 'La spedizione è prevista %s. Formalife si impegna a rispettare questa tempistica; eventuali variazioni verranno comunicate tempestivamente via email. In caso di ritardo significativo rispetto alla data comunicata, resta comunque a tua disposizione il diritto di recesso descritto all\'articolo 7.', 'guida-antipanico-soffocamento' ),
					esc_html( $delivery )
				);
				?>
			</p>
		</div>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '5', 'pagamento', __( 'Pagamento', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'I pagamenti sono gestiti interamente da Stripe, fornitore di servizi di pagamento certificato secondo lo standard di sicurezza PCI-DSS. Formalife non riceve né conserva sui propri sistemi i dati completi della tua carta o del metodo di pagamento scelto: l\'intero processo avviene sui sistemi sicuri di Stripe, secondo i termini e l\'informativa privacy pubblicati su stripe.com.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'La fattura viene emessa soltanto se richiesta tramite l\'apposita casella nel modulo. In quel caso utilizziamo intestatario, indirizzo di fatturazione, partita IVA e codice univoco o PEC esclusivamente per gli adempimenti fiscali e amministrativi relativi all\'acquisto.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '6', 'spedizione', __( 'Spedizione e consegna', 'guida-antipanico-soffocamento' ) ); ?>
		<p>
			<?php
			printf(
				/* translators: 1: costo di spedizione, 2: corriere, 3: data di spedizione */
				esc_html__( 'La spedizione in tutto il territorio italiano ha un costo di %1$s, addebitato insieme al prezzo del libro nello stesso pagamento, e viene affidata a un %2$s. La consegna è prevista %3$s: eventuali variazioni ti verranno comunicate via email all\'indirizzo indicato in fase di prenotazione.', 'guida-antipanico-soffocamento' ),
				esc_html( $shipping_price ),
				esc_html( $carrier ),
				esc_html( $delivery )
			);
			?>
		</p>
		<p><?php esc_html_e( 'Il rischio di perdita o danneggiamento del libro passa in capo a te nel momento in cui tu, o un terzo da te designato e diverso dal corriere, acquisisci il possesso fisico del bene, in conformità con l\'art. 63 del Codice del Consumo.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Verifica con attenzione i dati di spedizione inseriti nel modulo prima di confermare l\'ordine: eventuali costi aggiuntivi dovuti a un indirizzo errato o incompleto fornito da te potranno essere addebitati per una nuova spedizione.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '7', 'recesso', __( 'Diritto di recesso', 'guida-antipanico-soffocamento' ) ); ?>
		<div class="gaps-legal-callout">
			<p><?php esc_html_e( 'Se acquisti come consumatore, hai diritto di recedere dal contratto entro 14 giorni, senza dover fornire alcuna motivazione e senza alcuna penalità, ai sensi degli articoli 52-59 del Codice del Consumo.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
		<h3><?php esc_html_e( 'Decorrenza del termine', 'guida-antipanico-soffocamento' ); ?></h3>
		<p><?php esc_html_e( 'Il termine di 14 giorni decorre dal giorno in cui tu, o un terzo da te designato e diverso dal corriere, acquisisci il possesso fisico del libro.', 'guida-antipanico-soffocamento' ); ?></p>
		<h3><?php esc_html_e( 'Come esercitare il recesso', 'guida-antipanico-soffocamento' ); ?></h3>
		<p><?php esc_html_e( 'Per esercitare il diritto di recesso devi informarci della tua decisione con una dichiarazione esplicita, ad esempio tramite una email all\'indirizzo di contatto indicato all\'articolo 16, oppure via PEC. Puoi utilizzare, se lo desideri, il seguente modello, adattato dal modello standard di cui all\'Allegato I, parte B, del Codice del Consumo:', 'guida-antipanico-soffocamento' ); ?></p>
		<div class="gaps-legal-note">
			<p><em><?php esc_html_e( '"Con la presente comunico il recesso dal contratto di vendita del seguente bene: La Guida Anti-Panico al Soffocamento Pediatrico — ordinato il [data] — nome del consumatore — indirizzo del consumatore — data."', 'guida-antipanico-soffocamento' ); ?></em></p>
		</div>
		<h3><?php esc_html_e( 'Restituzione del bene', 'guida-antipanico-soffocamento' ); ?></h3>
		<p><?php esc_html_e( 'Dopo aver comunicato il recesso, devi rispedire il libro senza ritardo e comunque entro 14 giorni dalla comunicazione. Le spese dirette di restituzione sono a tuo carico, salvo diverso accordo scritto con Formalife.', 'guida-antipanico-soffocamento' ); ?></p>
		<h3><?php esc_html_e( 'Effetti del recesso', 'guida-antipanico-soffocamento' ); ?></h3>
		<p><?php esc_html_e( 'In caso di recesso, ti rimborsiamo tutti i pagamenti ricevuti, incluse le spese di consegna standard, senza ingiustificato ritardo e in ogni caso entro 14 giorni dal giorno in cui siamo informati della tua decisione di recedere. Il rimborso avviene con lo stesso mezzo di pagamento usato per la transazione iniziale, salvo tuo diverso accordo espresso, e comunque senza costi aggiuntivi per te. Possiamo trattenere il rimborso finché non abbiamo ricevuto indietro il bene, o finché non dimostri di averlo rispedito, se anteriore.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Trattandosi di un libro fisico e non di un contenuto digitale sigillato, nessuna delle esclusioni dal diritto di recesso previste dall\'art. 59 del Codice del Consumo è applicabile a questo prodotto.', 'guida-antipanico-soffocamento' ); ?></p>
		<p>
			<?php
			printf(
				/* translators: %s: numero di giorni della garanzia commerciale */
				esc_html__( 'Oltre a questo diritto di legge, all\'articolo 9 trovi una garanzia commerciale volontaria di Formalife che, entro %s giorni dalla ricezione, ti consente di ottenere il rimborso senza dover restituire il libro: puoi scegliere liberamente quale delle due opzioni utilizzare.', 'guida-antipanico-soffocamento' ),
				esc_html( $guarantee_d )
			);
			?>
		</p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '8', 'garanzia-legale', __( 'Garanzia legale di conformità', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'In quanto consumatore, hai diritto alla garanzia legale di conformità di 24 mesi dalla consegna, prevista dagli articoli 128-135 del Codice del Consumo. Se il libro presenta un difetto di conformità esistente al momento della consegna (ad esempio pagine mancanti o danneggiate, difetti di stampa o di rilegatura), hai diritto, a tua scelta e nei limiti previsti dalla legge, alla sua riparazione o sostituzione senza spese, oppure, ricorrendone i presupposti, a una riduzione del prezzo o alla risoluzione del contratto.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Per attivare la garanzia legale, scrivici entro un termine ragionevole dalla scoperta del difetto, indicando gli estremi dell\'ordine e allegando, se possibile, una foto del difetto riscontrato: i contatti sono all\'articolo 16.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '9', 'garanzia-commerciale', __( 'Garanzia commerciale "Soddisfatti o rimborsati"', 'guida-antipanico-soffocamento' ) ); ?>
		<div class="gaps-legal-callout">
			<p>
				<?php
				printf(
					/* translators: %s: numero di giorni della garanzia commerciale */
					esc_html__( 'Oltre ai diritti di legge descritti sopra, Formalife offre volontariamente una garanzia commerciale più ampia: se il libro non ti convince, per qualsiasi motivo, entro %s giorni dalla ricezione scrivici e ti rimborsiamo interamente il prezzo pagato — senza doverci restituire il libro, senza doverci fornire spiegazioni — e in aggiunta ricevi uno sconto del 20%% su un corso pratico Formalife.', 'guida-antipanico-soffocamento' ),
					esc_html( $guarantee_d )
				);
				?>
			</p>
		</div>
		<p><?php esc_html_e( 'Questa è una garanzia commerciale ai sensi dell\'art. 128, comma 2, lettera e) del Codice del Consumo: si aggiunge — e non sostituisce in alcun modo — i diritti inderogabili di legge descritti agli articoli 7 e 8, che restano sempre a tua disposizione in alternativa, alle condizioni ivi previste.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Per attivarla, scrivici all\'indirizzo indicato all\'articolo 16 indicando nome, cognome e riferimento dell\'ordine. Il rimborso viene elaborato con lo stesso mezzo di pagamento usato per l\'acquisto, entro un termine ragionevole.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '10', 'responsabilita', __( 'Limiti del contenuto e responsabilità', 'guida-antipanico-soffocamento' ) ); ?>
		<div class="gaps-legal-note">
			<p><strong><?php esc_html_e( 'Il libro ha finalità informativa ed educativa.', 'guida-antipanico-soffocamento' ); ?></strong> <?php esc_html_e( 'È stato scritto e verificato dalla Dott.ssa Mafalda Camposarcone, pediatra, sulla base delle linee guida ERC 2025 e IRC disponibili al momento della pubblicazione.', 'guida-antipanico-soffocamento' ); ?></p>
			<p><?php esc_html_e( 'Il libro non costituisce un parere medico personalizzato, non sostituisce una consulenza pediatrica individuale né una certificazione di primo soccorso rilasciata da un ente formativo abilitato. Le manovre di disostruzione pediatrica si apprendono in modo efficace solo con una formazione pratica supervisionata da un istruttore qualificato, come nei corsi pratici Formalife a numero chiuso.', 'guida-antipanico-soffocamento' ); ?></p>
			<p><strong><?php esc_html_e( 'In un\'emergenza reale, chiama sempre e per primo il 112 (Numero Unico per le Emergenze) o il 118', 'guida-antipanico-soffocamento' ); ?></strong> <?php esc_html_e( ', indipendentemente da qualsiasi manovra descritta nel libro.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
		<p><?php esc_html_e( 'Nei limiti massimi consentiti dalla legge applicabile, Formalife non potrà essere ritenuta responsabile per un uso improprio, un\'interpretazione errata o un\'applicazione scorretta delle informazioni contenute nel libro.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Resta fermo che nulla nelle presenti Condizioni esclude o limita la responsabilità di Formalife per dolo o colpa grave, né per morte o lesioni personali causate da negligenza, nei casi in cui la legge italiana non ne consenta l\'esclusione (art. 1229 del Codice Civile), né qualsiasi altra responsabilità che non può essere esclusa o limitata ai sensi di legge.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '11', 'proprieta-intellettuale', __( 'Proprietà intellettuale', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Tutti i contenuti del libro (testi, illustrazioni, schede) e del Sito (grafica, testi, marchio "Formalife") sono protetti dal diritto d\'autore e da altri diritti di proprietà intellettuale, di titolarità di Formalife o dei rispettivi autori o licenzianti. È vietata la riproduzione, distribuzione, comunicazione al pubblico o rielaborazione, anche parziale, senza autorizzazione scritta, salve le eccezioni previste dalla legge — ad esempio la citazione per finalità di critica o discussione, purché accompagnata dall\'indicazione della fonte.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '12', 'dati-personali', __( 'Dati personali', 'guida-antipanico-soffocamento' ) ); ?>
		<p>
			<?php
			if ( $privacy_url ) {
				// Costruiamo il link direttamente (URL già sanificato con esc_url()),
				// invece di passare un href con placeholder "%s" a wp_kses(): quel
				// pattern rischierebbe di far normalizzare/alterare il segnaposto
				// prima ancora che printf() lo sostituisca con l'URL reale.
				echo wp_kses(
					sprintf(
						/* translators: %s: URL della pagina Privacy, già sanificato */
						__( 'Il trattamento dei dati personali raccolti in fase di prenotazione e acquisto è disciplinato dalla nostra <a href="%s">Informativa sulla Privacy</a>, che ti invitiamo a leggere prima di procedere.', 'guida-antipanico-soffocamento' ),
						esc_url( $privacy_url )
					),
					array( 'a' => array( 'href' => array() ) )
				);
			} else {
				esc_html_e( 'Il trattamento dei dati personali raccolti in fase di prenotazione e acquisto è disciplinato dalla nostra Informativa sulla Privacy, che ti invitiamo a leggere prima di procedere.', 'guida-antipanico-soffocamento' );
			}
			?>
		</p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '13', 'legge-foro', __( 'Legge applicabile e foro competente', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Le presenti Condizioni sono regolate dalla legge italiana. Se acquisti come consumatore e risiedi abitualmente in un altro Paese dell\'Unione Europea, potresti comunque beneficiare delle eventuali disposizioni inderogabili più favorevoli previste dalla legge del Paese in cui risiedi.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Se sei un consumatore, per qualsiasi controversia relativa alle presenti Condizioni è competente in via esclusiva il giudice del luogo in cui risiedi o hai eletto domicilio (il cosiddetto "foro del consumatore"), ai sensi dell\'art. 66-bis del Codice del Consumo: si tratta di una tutela inderogabile, che queste Condizioni non possono modificare a tuo danno.', 'guida-antipanico-soffocamento' ); ?></p>
		<p>
			<?php esc_html_e( 'Per i rapporti con soggetti che non rivestono la qualifica di consumatore, è competente in via esclusiva il Foro di', 'guida-antipanico-soffocamento' ); ?>
			<?php gaps_legal_field( $settings['legal_court'] ); ?>.
		</p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '14', 'controversie', __( 'Risoluzione delle controversie', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Se hai un problema con il tuo ordine, scrivici prima di tutto direttamente: cerchiamo sempre una soluzione diretta e rapida, ai contatti indicati all\'articolo 16.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Ti informiamo che, in attuazione del Regolamento (UE) 2024/3228, dal 20 luglio 2025 la piattaforma europea ODR (Online Dispute Resolution) della Commissione Europea non è più operativa. Per la risoluzione extragiudiziale delle controversie di consumo puoi rivolgerti agli organismi di composizione delle controversie (ADR) iscritti nell\'elenco tenuto dal Ministero delle Imprese e del Made in Italy, alla Camera di Commercio competente per territorio, oppure al Centro Europeo Consumatori Italia (ecc-netitalia.it), che offre assistenza gratuita ai consumatori nelle controversie transfrontaliere all\'interno dell\'Unione Europea.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '15', 'modifiche', __( 'Modifiche alle condizioni', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Formalife può aggiornare le presenti Condizioni di vendita, ad esempio per adeguamenti normativi o cambiamenti nell\'offerta. Le condizioni applicabili al tuo acquisto sono sempre quelle in vigore al momento in cui invii il modulo di prenotazione. La data di ultimo aggiornamento di questa pagina è indicata in cima.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '16', 'contatti', __( 'Contatti', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Per qualunque domanda su un ordine, sul diritto di recesso o sulle garanzie descritte in questa pagina, puoi contattarci così:', 'guida-antipanico-soffocamento' ); ?></p>
		<?php gaps_legal_company_box( $settings ); ?>
	</div>
	<?php
}

/* =====================================================================
 * PRIVACY POLICY
 * ===================================================================*/

/**
 * Indice della pagina "Privacy". Le chiavi (ancore) devono combaciare
 * esattamente con gli id usati in gaps_render_privacy_content().
 *
 * @return array<string, string>
 */
function gaps_get_privacy_toc() {
	return array(
		'titolare'         => __( 'Titolare del trattamento', 'guida-antipanico-soffocamento' ),
		'dati-raccolti'     => __( 'Dati raccolti e come li raccogliamo', 'guida-antipanico-soffocamento' ),
		'finalita'          => __( 'Finalità del trattamento e basi giuridiche', 'guida-antipanico-soffocamento' ),
		'conferimento'      => __( 'Natura del conferimento dei dati', 'guida-antipanico-soffocamento' ),
		'sicurezza'         => __( 'Modalità del trattamento e sicurezza', 'guida-antipanico-soffocamento' ),
		'conservazione'     => __( 'Periodo di conservazione', 'guida-antipanico-soffocamento' ),
		'destinatari'       => __( 'Comunicazione e destinatari dei dati', 'guida-antipanico-soffocamento' ),
		'trasferimento'     => __( 'Trasferimento dei dati extra-UE', 'guida-antipanico-soffocamento' ),
		'diritti'           => __( 'Diritti dell\'interessato', 'guida-antipanico-soffocamento' ),
		'reclamo'           => __( 'Reclamo al Garante Privacy', 'guida-antipanico-soffocamento' ),
		'cookie'            => __( 'Cookie e tecnologie simili', 'guida-antipanico-soffocamento' ),
		'minori'            => __( 'Minori', 'guida-antipanico-soffocamento' ),
		'modifiche-privacy' => __( 'Modifiche alla presente informativa', 'guida-antipanico-soffocamento' ),
		'contatti-privacy'  => __( 'Contatti', 'guida-antipanico-soffocamento' ),
	);
}

/**
 * Stampa il contenuto completo della pagina "Privacy".
 *
 * @param array $settings Impostazioni del plugin.
 */
function gaps_render_privacy_content( $settings ) {
	$carrier = '' !== trim( $settings['shipping_carrier'] ) ? $settings['shipping_carrier'] : __( 'il corriere espresso incaricato da Formalife', 'guida-antipanico-soffocamento' );
	?>

	<p class="gaps-legal-intro">
		<?php esc_html_e( 'Questa informativa descrive come Formalife raccoglie, utilizza e protegge i dati personali di chi visita questo sito e di chi prenota una copia de "La Guida Anti-Panico al Soffocamento Pediatrico", in conformità al Regolamento (UE) 2016/679 ("GDPR") e al Codice in materia di protezione dei dati personali (D.Lgs. 196/2003, come modificato dal D.Lgs. 101/2018).', 'guida-antipanico-soffocamento' ); ?>
	</p>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '1', 'titolare', __( 'Titolare del trattamento', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Il Titolare del trattamento dei tuoi dati personali è:', 'guida-antipanico-soffocamento' ); ?></p>
		<?php gaps_legal_company_box( $settings ); ?>
		<p><?php esc_html_e( 'Per qualunque richiesta relativa al trattamento dei tuoi dati personali puoi scrivere all\'email di contatto indicata sopra, oppure, per comunicazioni con valore legale, alla PEC indicata.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '2', 'dati-raccolti', __( 'Dati raccolti e come li raccogliamo', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Raccogliamo solo i dati necessari a gestire la tua richiesta di prenotazione e il tuo acquisto:', 'guida-antipanico-soffocamento' ); ?></p>
		<div class="gaps-legal-table-wrap">
			<table class="gaps-legal-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Categoria di dati', 'guida-antipanico-soffocamento' ); ?></th>
						<th><?php esc_html_e( 'Esempi', 'guida-antipanico-soffocamento' ); ?></th>
						<th><?php esc_html_e( 'Come li raccogliamo', 'guida-antipanico-soffocamento' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Dati identificativi e di contatto', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Nome, cognome, telefono, email', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Modulo d\'acquisto ("Acquista ora")', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Dati di spedizione', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Indirizzo, città, provincia', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Modulo di prenotazione', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Dati fiscali', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Se richiedi la fattura: intestatario, indirizzo di fatturazione, partita IVA, codice univoco o PEC', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Sezione facoltativa del modulo di prenotazione, mostrata soltanto dopo la selezione di “Voglio la fattura”', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Dati di pagamento', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Dati della carta o del metodo di pagamento scelto', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Inseriti direttamente sulla pagina di pagamento sicura di Stripe: Formalife non li riceve né li conserva', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Dati di navigazione', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Indirizzo IP, tipo di browser, pagine visitate, cookie tecnici', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Raccolti automaticamente durante la navigazione (vedi sezione Cookie)', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<p><?php esc_html_e( 'Il modulo di prenotazione prevede una casella per la presa visione della Privacy Policy. La casella “Voglio la fattura” non è un consenso di marketing: serve esclusivamente a mostrare i campi fiscali necessari. Il modulo non iscrive automaticamente ad alcuna newsletter o comunicazione promozionale.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '3', 'finalita', __( 'Finalità del trattamento e basi giuridiche', 'guida-antipanico-soffocamento' ) ); ?>
		<div class="gaps-legal-table-wrap">
			<table class="gaps-legal-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Finalità', 'guida-antipanico-soffocamento' ); ?></th>
						<th><?php esc_html_e( 'Base giuridica', 'guida-antipanico-soffocamento' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Gestire la richiesta di prenotazione e concludere/eseguire il contratto di vendita (raccolta dell\'ordine, spedizione, fatturazione)', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Art. 6.1.b GDPR — misure precontrattuali ed esecuzione del contratto', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Adempiere a obblighi fiscali, contabili e amministrativi', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Art. 6.1.c GDPR — obbligo legale', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Rispondere a richieste, gestire reclami, garanzie e recessi, esercitare o difendere un diritto in sede giudiziale', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Art. 6.1.f GDPR — legittimo interesse del Titolare a gestire correttamente il rapporto commerciale', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Eventuali comunicazioni promozionali o newsletter (al momento non attive)', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Art. 6.1.a GDPR — consenso specifico, richiesto separatamente e in aggiunta rispetto a quello necessario per l\'acquisto', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '4', 'conferimento', __( 'Natura del conferimento dei dati', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'I dati di contatto e spedizione contrassegnati come obbligatori sono necessari per concludere ed eseguire il contratto di acquisto. I dati fiscali diventano obbligatori soltanto se selezioni “Voglio la fattura”; senza tali dati non potremo emettere la fattura richiesta, mentre non sono necessari quando la fattura non viene richiesta.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '5', 'sicurezza', __( 'Modalità del trattamento e sicurezza', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Trattiamo i tuoi dati con strumenti informatici e, in misura residuale, cartacei, adottando misure tecniche e organizzative adeguate a garantire un livello di sicurezza proporzionato al rischio: accesso riservato al solo personale autorizzato, connessioni protette e aggiornamenti di sicurezza regolari del sito.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'I dati di pagamento non transitano né vengono conservati sui sistemi di Formalife: l\'intero processo di pagamento avviene sui sistemi di Stripe, fornitore certificato secondo lo standard di sicurezza PCI-DSS.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '6', 'conservazione', __( 'Periodo di conservazione', 'guida-antipanico-soffocamento' ) ); ?>
		<div class="gaps-legal-table-wrap">
			<table class="gaps-legal-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Tipo di dato', 'guida-antipanico-soffocamento' ); ?></th>
						<th><?php esc_html_e( 'Periodo di conservazione', 'guida-antipanico-soffocamento' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Dati legati a un ordine concluso (fatturazione, contabilità)', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Per il tempo necessario a evadere l\'ordine e, successivamente, per il periodo previsto dalla normativa fiscale e civilistica (10 anni ai sensi dell\'art. 2220 del Codice Civile), poi cancellati o anonimizzati', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Dati di richieste o prenotazioni non finalizzate all\'acquisto', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Fino a 24 mesi dall\'ultimo contatto, salvo cancellazione anticipata su tua richiesta', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Dati di navigazione e cookie tecnici', 'guida-antipanico-soffocamento' ); ?></td>
						<td><?php esc_html_e( 'Per la durata della sessione o comunque non oltre il tempo tecnicamente necessario', 'guida-antipanico-soffocamento' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '7', 'destinatari', __( 'Comunicazione e destinatari dei dati', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'I tuoi dati possono essere comunicati, nei limiti strettamente necessari alle finalità descritte, a:', 'guida-antipanico-soffocamento' ); ?></p>
		<ul>
			<li><?php echo wp_kses( __( '<strong>Stripe</strong> (fornitore del servizio di pagamento), che tratta i dati di pagamento in autonomia, quale titolare autonomo del trattamento per le finalità connesse al pagamento — informativa disponibile su stripe.com/it-it/privacy;', 'guida-antipanico-soffocamento' ), array( 'strong' => array() ) ); ?></li>
			<li>
				<?php
				printf(
					/* translators: %s: nome del corriere di spedizione */
					esc_html__( '%s, limitatamente ai dati necessari alla consegna (nome, indirizzo, telefono);', 'guida-antipanico-soffocamento' ),
					esc_html( $carrier )
				);
				?>
			</li>
			<li><?php esc_html_e( 'il fornitore del servizio di hosting e del sito web, in qualità di responsabile del trattamento ai sensi dell\'art. 28 GDPR;', 'guida-antipanico-soffocamento' ); ?></li>
			<li><?php esc_html_e( 'eventuali consulenti fiscali o contabili, per gli adempimenti amministrativi;', 'guida-antipanico-soffocamento' ); ?></li>
			<li><?php esc_html_e( 'le autorità pubbliche competenti, quando richiesto dalla legge.', 'guida-antipanico-soffocamento' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'Formalife non vende né cede a terzi i tuoi dati personali per finalità di marketing di terze parti.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '8', 'trasferimento', __( 'Trasferimento dei dati extra-UE', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Alcuni fornitori che trattano i tuoi dati per nostro conto o come titolari autonomi — in particolare Stripe — possono trattare dati anche al di fuori dello Spazio Economico Europeo, ad esempio negli Stati Uniti. In questi casi il trasferimento avviene sulla base di garanzie adeguate riconosciute dal GDPR, tra cui le Clausole Contrattuali Standard approvate dalla Commissione Europea. Puoi richiedere maggiori informazioni su queste garanzie scrivendoci ai contatti indicati all\'articolo 14.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '9', 'diritti', __( 'Diritti dell\'interessato', 'guida-antipanico-soffocamento' ) ); ?>
		<div class="gaps-legal-callout">
			<p><?php esc_html_e( 'In qualsiasi momento puoi esercitare, scrivendoci ai contatti indicati all\'articolo 14, i diritti previsti dagli articoli 15-22 del GDPR:', 'guida-antipanico-soffocamento' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'accesso ai tuoi dati personali (art. 15);', 'guida-antipanico-soffocamento' ); ?></li>
				<li><?php esc_html_e( 'rettifica dei dati inesatti o incompleti (art. 16);', 'guida-antipanico-soffocamento' ); ?></li>
				<li><?php esc_html_e( 'cancellazione, nei casi previsti dalla legge (art. 17);', 'guida-antipanico-soffocamento' ); ?></li>
				<li><?php esc_html_e( 'limitazione del trattamento (art. 18);', 'guida-antipanico-soffocamento' ); ?></li>
				<li><?php esc_html_e( 'portabilità dei dati, dove tecnicamente applicabile (art. 20);', 'guida-antipanico-soffocamento' ); ?></li>
				<li><?php esc_html_e( 'opposizione al trattamento basato sul legittimo interesse (art. 21);', 'guida-antipanico-soffocamento' ); ?></li>
				<li><?php esc_html_e( 'revoca del consenso in qualsiasi momento, senza pregiudicare la liceità del trattamento svolto prima della revoca (art. 7.3).', 'guida-antipanico-soffocamento' ); ?></li>
			</ul>
		</div>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '10', 'reclamo', __( 'Reclamo al Garante Privacy', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Se ritieni che il trattamento dei tuoi dati violi la normativa applicabile, hai diritto di proporre reclamo al Garante per la protezione dei dati personali (Piazza Venezia 11, 00187 Roma — www.garanteprivacy.it), oppure di adire l\'autorità giudiziaria competente.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '11', 'cookie', __( 'Cookie e tecnologie simili', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Questo sito utilizza esclusivamente cookie tecnici necessari al suo funzionamento (ad esempio per la gestione della sessione di WordPress). Al momento non sono attivi cookie di profilazione né strumenti di marketing o analisi di terze parti.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Qualora in futuro venissero attivati strumenti di analisi statistica o marketing che comportano l\'uso di cookie non tecnici, il sito richiederà preventivamente il tuo consenso tramite un apposito banner, in conformità alla normativa ePrivacy, e questa informativa verrà aggiornata di conseguenza.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '12', 'minori', __( 'Minori', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Il sito e l\'acquisto del libro si rivolgono a genitori e adulti maggiorenni. Non raccogliamo consapevolmente dati personali di minori attraverso il modulo di prenotazione, che richiede dati anagrafici e fiscali riferibili a un acquirente maggiorenne.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '13', 'modifiche-privacy', __( 'Modifiche alla presente informativa', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Formalife può aggiornare periodicamente questa informativa, ad esempio in caso di modifiche normative o di nuovi strumenti adottati sul sito. La data di ultimo aggiornamento è indicata in cima a questa pagina: ti invitiamo a consultarla periodicamente.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>

	<div class="gaps-legal-section">
		<?php gaps_legal_heading( '14', 'contatti-privacy', __( 'Contatti', 'guida-antipanico-soffocamento' ) ); ?>
		<p><?php esc_html_e( 'Per qualunque domanda su questa informativa o per esercitare i tuoi diritti, puoi contattarci così:', 'guida-antipanico-soffocamento' ); ?></p>
		<?php gaps_legal_company_box( $settings ); ?>
	</div>
	<?php
}
