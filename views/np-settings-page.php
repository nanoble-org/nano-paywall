<?php

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'You do not have sufficient permissions to access this page.', 'np_settings' ) );
}

?>
<div class="wrap">
	<div id="icon-themes" class="icon32"></div>
    <h2>Nano Paywall Settings</h2>
    <?php settings_errors(); ?>
    <hr />
	<form id="np-settings-form" action="options.php" method="POST">

		<?php

				settings_fields( 'np_general_settings' );
				do_settings_sections( 'np-settings-page-payment' );

				submit_button();

		?>

	</form>
</div><!-- end .wrap -->