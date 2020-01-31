<div class="wtb-setting-wrapper">
	<div class="heading-wrapper">
		<h2 class="heading"><?php _e('Shopistas Feed Export settings', 'shopistas-feed-export');  ?></h2>
	</div>	
	<form action="" method="POST" name="wtb-settings">
		<?php wp_nonce_field( 'save_WOO_FEED_settings', '_WOO_FEED_settings' ); ?>
		<?php echo $this->prepare_fields(); ?>
	</form>
</div>