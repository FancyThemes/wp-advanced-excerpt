<div id="advanced-excerpt-sidebar">
<div class="inside">

	<div class="author">
		<img src="http://www.gravatar.com/avatar/e538ca4cb34839d4e5e3ccf20c37c67b?s=128&amp;d" width="64" height="64" />
		<div class="desc">
			<h3><?php _e( 'Maintained by', 'advanced-excerpt' ); ?></h3>
			<h2>Brad Touesnard</h2>
			<p>
				<a href="http://profiles.wordpress.org/bradt/" target="_blank"><?php _e( 'Profile', 'advanced-excerpt' ); ?></a>
				&nbsp;&nbsp;
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=5VPMGLLK94XJC" target="_blank"><?php _e( 'Donate', 'advanced-excerpt' ); ?></a>
			</p>
		</div>
	</div>

	<form method="post" action="http://deliciousbrains.createsend.com/t/t/s/tdiull/" target="_blank" class="subscribe">
		<h2><?php _e( 'WordPress Development and Deployment Strategy', 'advanced-excerpt' ); ?></h2>

		<?php $user = wp_get_current_user(); ?>

		<p class="interesting">
			<?php _e( '<strong>Free pro tips</strong> on advanced WordPress development techniques and deployment strategies.', 'advanced-excerpt' ); ?>
		</p>

		<div class="field notify-name">
			<input type="text" name="cm-name" value="<?php echo trim( esc_attr( $user->first_name ) . ' ' . esc_attr( $user->last_name ) ); ?>" placeholder="<?php _e( 'Your Name', 'advanced-excerpt' ); ?>" />
		</div>

		<div class="field notify-email">
			<input type="email" name="cm-tdiull-tdiull" value="<?php echo esc_attr( $user->user_email ); ?>" placeholder="<?php _e( 'Your Email', 'advanced-excerpt' ); ?>" />
		</div>

		<div class="field submit-button">
			<input type="submit" class="button" value="<?php _e( 'Subscribe', 'advanced-excerpt' ); ?>" />
		</div>

		<p class="promise">
			<?php _e( 'I promise I will not use your email for anything else and you can unsubscribe with <span style="white-space: nowrap;">1-click anytime</span>.', 'advanced-excerpt' ); ?>
		</p>
	</form>

</div>

<a class="wpmdb-banner" target="_blank" href="http://deliciousbrains.com/wp-migrate-db-pro/?utm_source=advanced-excerpt&utm_medium=plugin&utm_campaign=advanced-excerpt"><img src="<?php echo plugins_url( 'asset/img/wp-migrate-db-pro.jpg', $this->plugin_file_path ); ?>" width="292" height="292" alt="<?php _e( 'WP Migrate DB Pro &mdash; Push and pull your database from one WordPress install to another in 1-click.', 'advanced-excerpt' ); ?>" /></a>

</div>