<div id="advanced-excerpt-sidebar">

	<div class="author">
		<img src="//www.gravatar.com/avatar/e538ca4cb34839d4e5e3ccf20c37c67b?s=128&amp;d" width="64" height="64" />
		<div class="desc">
			<h3><?php _e( 'Created &amp; maintained by', 'advanced-excerpt' ); ?></h3>
			<h2>Brad Touesnard</h2>
			<p>
				<a href="http://profiles.wordpress.org/bradt/" target="_blank"><?php _e( 'Profile', 'advanced-excerpt' ); ?></a>
				&nbsp;&nbsp;
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=5VPMGLLK94XJC" target="_blank"><?php _e( 'Donate', 'advanced-excerpt' ); ?></a>
			</p>
		</div>
	</div>

	<form method="post" action="http://deliciousbrains.createsend.com/t/t/s/virn/" target="_blank" class="subscribe">
		<h2><?php _e( 'Pro Version Has Arrived!', 'advanced-excerpt' ); ?></h2>

		<a class="video" target="_blank" href="http://deliciousbrains.com/wp-migrate-db-pro/?utm_source=insideplugin&utm_medium=web&utm_campaign=freeplugin#play-intro"><img src="<?php echo plugins_url( 'asset/img/video@2x.jpg', $this->plugin_file_path ); ?>" width="250" height="164" alt="" /></a>

		<p class="links">
			<a href="http://deliciousbrains.com/wp-migrate-db-pro/?utm_source=insideplugin&utm_medium=web&utm_campaign=freeplugin" target="_blank"><?php _e( 'View Features &rarr;', 'advanced-excerpt' ); ?></a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://deliciousbrains.com/wp-migrate-db-pro/pricing/?utm_source=insideplugin&utm_medium=web&utm_campaign=freeplugin" target="_blank"><?php _e( 'View Pricing &rarr;', 'advanced-excerpt' ); ?></a>
		</p>

		<?php $user = wp_get_current_user(); ?>

		<h3><em><?php _e( 'Get 20% Off!', 'c' ); ?></em></h3>

		<p class="interesting">
			<?php _e( 'Subscribe to receive news &amp; updates below and we\'ll instantly send you a coupon code to get 20% off any WP Migrate DB Pro license.', 'advanced-excerpt' ); ?>
		</p>

		<div class="field notify-name">
			<p><?php _e( 'Your Name', 'advanced-excerpt' ); ?></p>
			<input type="text" name="cm-name" value="<?php echo trim( esc_attr( $user->first_name ) . ' ' . esc_attr( $user->last_name ) ); ?>" />
		</div>

		<div class="field notify-email">
			<p><?php _e( 'Your Email', 'advanced-excerpt' ); ?></p>
			<input type="email" name="cm-virn-virn" value="<?php echo esc_attr( $user->user_email ); ?>" />
		</div>

		<div class="field submit-button">
			<input type="submit" class="button" value="<?php _e( 'Subscribe', 'advanced-excerpt' ); ?>" />
		</div>

		<p class="promise">
			<?php _e( 'I promise I will not use your email for anything else and you can unsubscribe with <span style="white-space: nowrap;">1-click anytime</span>.', 'advanced-excerpt' ); ?>
		</p>
	</form>

</div>