<?php defined( 'ABSPATH' ) or die; ?>
<div class="keysenderec-welcome">
	<div class="keysenderec-flex">
		<div class="keysenderec-logo">
			<img src="<?php esc_attr_e( plugins_url( 'images/logo.svg', KEYSENDER_ECOMMERCE_FILE ) ); ?>">
		</div>
		<div class="keysenderec-buttons-green">
			<p><?php _e( 'Your Keysender plug-in has been installed, set up your account to begin distributing digital products instantly, affordably, and safely.', 'keysender' ); ?></p>
			<div>
				<a href="<?php esc_attr_e( admin_url( 'admin.php?page=wc-settings&tab=keysenderec' ) ); ?>" class="btn-grey"><?php _e( 'Setup', 'keysender' );?></a>
				<a href="https://support.keysender.com/en-US/integrating-woocommerce-274265?plugin" target="_blank"><?php _e( 'Help Center', 'keysender' );?></a>
			</div>
		</div>
	</div>
</div>