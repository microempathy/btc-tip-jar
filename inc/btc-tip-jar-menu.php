<?php

class Btc_Tip_Jar_Menu {
	public $settings;

	public function __construct( $defaults = array() ) {

		$this->settings = get_option( get_class(), $defaults );

		add_action( 'admin_menu', array( &$this, 'menu' ) );
		add_action( 'admin_init', array( &$this, 'menu_settings' ) );

	}
public function menu() {

		add_options_page(
			'Bitcoin Tip Jar',
			'Bitcoin Tip Jar',
			'manage_options',
			__FILE__,
			array( &$this, 'menu_page' )
		);

	}
	public function menu_settings() {
		register_setting( get_class(), get_class() );
	}
	public function menu_page() {

		echo '<div class="wrap">';
		echo '<h2>Bitcoin Tip Jar Settings</h2>';
		echo '<form method="post" action="options.php">';
		settings_fields( get_class() );
		do_settings_fields( get_class(), get_class() );
		echo '<table class="form-table">';

		$this->menu_page_item( 'rpcssl', __( 'Secure socket' ) );
		$this->menu_page_item( 'rpcconnect', __( 'Address' ) );
		$this->menu_page_item( 'rpcport', __( 'Port' ) );
		$this->menu_page_item( 'rpcuser', __( 'Username' ) );
		$this->menu_page_item( 'rpcpassword', __( 'Password' ) );
		$this->menu_page_item( 'rpcwallet', __( 'Wallet Password' ) );
		$this->menu_page_item( 'fx', __( 'Conversion Currency' ) );
		$this->menu_page_item( 'decimals', __( 'Bitcoin Decimals' ) );

		echo '</table>';
		submit_button();
		echo '</form></div>';
	}
private function menu_page_item( $item, $label ) {

		echo '<tr valign="top">';
		echo '<th scope="row"><label for="' . get_class() . '[' . $item . ']">' . $label . '</label></th>';
		echo '<td>';

		if ( $item == 'rpcssl' ) {
			echo '<input type="checkbox" class="regular-text" ';
			echo 'name="' . get_class() . '[' . $item . ']" id="' . get_class() . '[' . $item . ']" ';
			if ( !empty( $this->settings[$item] ) ) {
				checked( $this->settings[$item] );
			}
			echo 'value="1" />';
		} else {
			echo '<input type="text" class="regular-text" ';
			echo 'name="' . get_class() . '[' . $item . ']" id="' . get_class() . '[' . $item . ']" ';
			echo 'value="' . $this->settings[$item] . '" />';
		}

		echo '</td>';
		echo '</tr>';

	}
}

?>
