<?php

class Btc_Tip_Jar_Menu {
	public $settings;

	public function __construct( $defaults = array() ) {

		$this->settings = get_option( get_class(), $defaults );

	}
}

?>
