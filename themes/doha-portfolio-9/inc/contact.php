<?php
/**
 * Contact form: sent to the site email with wp_mail. Spam protection: hidden field + minimum fill time + rate limit.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function dp_contact_form() {
	$sent  = isset( $_GET['dp_sent'] ) ? sanitize_key( $_GET['dp_sent'] ) : ''; // phpcs:ignore
	$email = dp_opt( 'email' );
	?>
	<form class="contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>#contact">
		<?php if ( 'ok' === $sent ) : ?>
			<p class="form-note form-ok" role="status"><?php echo esc_html( dp_x( 'Thank you. Your message is on its way, and I usually reply within one working day.' ) ); ?></p>
		<?php elseif ( 'err' === $sent ) : ?>
			<p class="form-note form-err" role="alert"><?php echo esc_html( dp_x( 'The message could not be sent. Please email me directly:' ) ); ?> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
		<?php endif; ?>
		<input type="hidden" name="action" value="dp_contact">
		<input type="hidden" name="dp_t" value="<?php echo esc_attr( time() ); ?>">
		<input type="hidden" name="dp_back" value="<?php echo esc_url( remove_query_arg( 'dp_sent' ) ); ?>">
		<?php wp_nonce_field( 'dp_contact', 'dp_nonce' ); ?>
		<p class="hp" aria-hidden="true"><label>Website <input type="text" name="dp_website" tabindex="-1" autocomplete="off"></label></p>
		<div class="form-row">
			<label><span><?php echo esc_html( dp_x( 'Name' ) ); ?></span><input type="text" name="dp_name" required maxlength="100" autocomplete="name"></label>
			<label><span><?php echo esc_html( dp_x( 'Email' ) ); ?></span><input type="email" name="dp_email" required maxlength="150" autocomplete="email"></label>
		</div>
		<label><span><?php echo esc_html( dp_x( 'What do you need?' ) ); ?></span>
			<select name="dp_topic">
				<?php foreach ( array( 'New website', 'WooCommerce store', 'Speed optimisation', 'Hacked site recovery', 'Migration', 'Care and support', 'Something else' ) as $o ) : ?>
					<option value="<?php echo esc_attr( $o ); ?>"><?php echo esc_html( dp_x( $o ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label><span><?php echo esc_html( dp_x( 'Tell me about the project' ) ); ?></span><textarea name="dp_message" rows="5" required maxlength="5000"></textarea></label>
		<button class="btn btn-light magnetic" type="submit"><span><?php echo esc_html( dp_x( 'Send message' ) ); ?></span><?php echo dp_btn_icon( 'paper-plane-tilt' ); // phpcs:ignore ?></button>
		<p class="form-after"><?php echo esc_html( dp_x( 'I reply within one working day with questions or a short plan and estimate.' ) ); ?></p>
	</form>
	<?php
}

function dp_contact_handle() {
	$back = isset( $_POST['dp_back'] ) ? esc_url_raw( wp_unslash( $_POST['dp_back'] ) ) : home_url( '/' );
	$back = wp_validate_redirect( $back, home_url( '/' ) );
	$go   = function ( $r ) use ( $back ) { wp_safe_redirect( add_query_arg( 'dp_sent', $r, $back ) . '#contact' ); exit; };
	if ( ! isset( $_POST['dp_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dp_nonce'] ), 'dp_contact' ) ) { $go( 'err' ); }
	// Bots: filled the hidden field, or submitted in under 3 seconds. Pretend success.
	if ( ! empty( $_POST['dp_website'] ) || ( time() - (int) ( $_POST['dp_t'] ?? 0 ) ) < 3 ) { $go( 'ok' ); }
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'dp_cf_' . md5( $ip );
	if ( (int) get_transient( $key ) >= 5 ) { $go( 'err' ); }
	set_transient( $key, (int) get_transient( $key ) + 1, HOUR_IN_SECONDS );
	$name  = sanitize_text_field( wp_unslash( $_POST['dp_name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['dp_email'] ?? '' ) );
	$topic = sanitize_text_field( wp_unslash( $_POST['dp_topic'] ?? '' ) );
	$msg   = sanitize_textarea_field( wp_unslash( $_POST['dp_message'] ?? '' ) );
	if ( ! $name || ! is_email( $email ) || ! $msg ) { $go( 'err' ); }
	$to   = dp_opt( 'email' ) ? dp_opt( 'email' ) : get_option( 'admin_email' );
	$body = "Name: $name\nEmail: $email\nTopic: $topic\n\n$msg\n\n--\nSent from " . home_url( '/' );
	$ok   = wp_mail( $to, '[Portfolio] ' . $topic . ' from ' . $name, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
	$go( $ok ? 'ok' : 'err' );
}
add_action( 'admin_post_dp_contact', 'dp_contact_handle' );
add_action( 'admin_post_nopriv_dp_contact', 'dp_contact_handle' );
