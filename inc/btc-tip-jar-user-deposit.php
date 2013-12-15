<?php

class Btc_Tip_Jar_User_Deposit extends Btc_Tip_Jar_User_Page {
	public function do_page_body() {
		global $current_user;
		get_currentuserinfo();

		echo 'Fill and refill your account by sending ';
		echo 'Bitcoins to the following address...<br />';

		$address = $this->get_address( $current_user->ID );

		$label = 'deposit-for-' . $current_user->login;
		$url   = $this->user->tip_jar->get_qr_url( $address, $label );

		echo wp_kses(
			"<img src=\"{$url}\" class=\"btc-tip-jar_deposit_qr\"><br />",
			array(
				'img' => array( 'src' => array(), 'class' => array(), ),
				'br' => array(),
			)
		);
		echo wp_kses( "Deposit Address: {$address}" );
	}
	public function get_address( $user_id ) {
		$address = $this->user->tip_jar->btc->get_user_address( $user_id );
		return $address['address'];
	}
}

?>
