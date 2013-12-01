<?php

class Btc_Tip_Jar_User_Withdraw {
	private $user;

	public function __construct( $user ) {
		$this->user = $user;
	}
	public function do_page() {
		echo wp_kses( get_class() );
	}
}

?>
