<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$home = fmh_get_settings();
$course_url = fmh_get_course_page_url();
$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
$order = $order_id && FMH_Course_Orders::CPT === get_post_type( $order_id ) ? get_post( $order_id ) : null;
// Conferma il pagamento interrogando Stripe, senza dipendere dal webhook:
// il metodo è idempotente, quindi se il webhook è già arrivato non fa nulla.
$payment_status = $order ? FMH_Course_Orders::reconcile_order( $order_id ) : '';
$session = $order ? fmh_course_get_session( get_post_meta( $order_id, '_fmh_course_session', true ) ) : null;
?><!DOCTYPE html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Iscrizione ricevuta — Formalife</title><?php wp_head(); ?></head>
<body <?php body_class( 'fmh-course-thankyou-body' ); ?>><?php wp_body_open(); ?>
<main class="fmh-home fmh-course-thankyou"><div class="fmh-course-thankyou-card"><span class="fmh-course-check">✓</span><h1>Grazie. La tua iscrizione è stata ricevuta.</h1><?php if ( 'paid' === $payment_status ) : ?><p>Il pagamento è stato confermato. Trovi il riepilogo anche nella email che ti abbiamo appena inviato.</p><?php else : ?><p>Stiamo verificando il pagamento con il circuito bancario. Riceverai la conferma via email entro pochi minuti.</p><?php endif; ?><?php if ( $order && $session ) : ?><div class="fmh-checkout-summary"><strong>Ordine #<?php echo esc_html( $order_id ); ?></strong><span><?php echo esc_html( fmh_course_format_date( $session['date'] ) . ' · ' . $session['time'] ); ?></span><span><?php echo esc_html( $session['city'] . ' · ' . $session['venue'] ); ?></span><span>Partecipanti: <?php echo esc_html( max( 1, absint( get_post_meta( $order_id, '_fmh_party_size', true ) ?: get_post_meta( $order_id, '_fmh_course_seats', true ) ) ) ); ?></span><span>Importo: <?php echo esc_html( fmh_course_format_price( get_post_meta( $order_id, '_fmh_amount_cents', true ) ) ); ?></span><span>Libro incluso: una copia per partecipante</span></div><?php endif; ?><p>La prenotazione è protetta dalla Garanzia Serenità Formalife e dalle condizioni di vendita applicabili.</p><?php if ( ! empty( $home['contact_email'] ) ) : ?><p>Per assistenza: <a href="mailto:<?php echo esc_attr( $home['contact_email'] ); ?>"><?php echo esc_html( $home['contact_email'] ); ?></a></p><?php endif; ?><a class="fmh-btn-primary" href="<?php echo esc_url( $course_url ); ?>">Torna alla pagina del corso</a></div></main>
<?php wp_footer(); ?></body></html>
