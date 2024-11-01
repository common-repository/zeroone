<?php defined( 'ABSPATH' ) or die; ?>
<?php
	$is_authenticated = $this->is_authenticated();
?>
<div class="keysenderec-setup">
	<p><?php _e( 'Welcome to Keysender, an order management system for digital products. To get started, please complete the setup process then manage your orders on your Keysender panel.', 'keysender' ); ?></p>
	<p><?php echo sprintf( __( 'Visit our %sHelp Center%s for a setup tutorial or advice and answers from the Keysender Team.', 'keysender' ), '<a href="http://support.keysender.com/" target="_blank">', '</a>' ); ?>
	<div class="keysenderec-buttons">
		<div class="keysenderec-step">
			<div><?php _e( 'Step 1: Create your Keysender account', 'keysender' );?></div>
			<a href="https://panel.keysender.co.uk/register" target="_blank"><?php _e( 'Create Keysender Account', 'keysender' ); ?></a>
		</div>
		<div class="keysenderec-step">
			<div><?php _e( 'Step 2: Connect WooCommerce', 'keysender' );?></div>
			<a href="https://panel.keysender.co.uk/account/settings " target="_blank"><?php _e( 'Authenticate', 'keysender' );?></a>
		</div>
	</div>
	<div class="keysenderec-fields">
		<div class="keysenderec-step">
			<div><?php _e( 'Step 3: Insert your API key and secret from Keysender', 'keysender' );?>
				<label for="<?php esc_attr_e( $this->options_name ); ?>[api_key]"><?php _e( 'API Key', 'keysender' ); ?> <?php echo wc_help_tip( __( 'Get your API key on your Keysender account > Account Settings > API Key', 'keysender' ) ); ?></label>
				<input type="<?php echo $is_authenticated ? 'password' : 'text'; ?>" class="api-key" name="<?php esc_attr_e( $this->options_name ); ?>[api_key]" value="<?php esc_attr_e( $this->get_option( 'api_key' ) ); ?>" required <?php echo $is_authenticated ? 'disabled' : 'text'; ?>>
			</div>
			<div>
				<label for="<?php esc_attr_e( $this->options_name ); ?>[api_secret]"><?php _e( 'API Secret', 'keysender' ); ?> <?php echo wc_help_tip( __( 'Get your API secret on your Keysender account > Account Settings > API Secret', 'keysender' ) ); ?></label>
				<input type="<?php echo $is_authenticated ? 'password' : 'text'; ?>" class="api-secret" name="<?php esc_attr_e( $this->options_name ); ?>[api_secret]" value="<?php esc_attr_e( $this->get_option( 'api_secret' ) ); ?>" required <?php echo $is_authenticated ? 'disabled' : ''; ?>>
			</div>
		</div>
	</div>
	<div class="keysenderec-info">
		<?php echo wp_kses( $this->get_status(), 'data' ); ?></span>
	</div>
	<div class="ajax-loader">
		<img src="<?php esc_attr_e( $this->ajax_loader_url ); ?>">
	</div>
</div>
<p class="keysenderec-submit">
	<?php submit_button( __( 'Save', 'keysender' ), 'primary', 'submit', false ); ?>
</p>