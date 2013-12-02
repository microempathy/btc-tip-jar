<?php

class Btc_Tip_Jar_User_Summary {
	private $user;

	public function __construct( $user ) {
		$this->user = $user;
	}
	public function do_page() {

		echo '<div id="wrap">';
		screen_icon();
		echo '<h1>Bitcoin Tip Jar - Account Overview</h1>';

		echo '</div>';
	}
}

?>
