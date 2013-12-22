<?php

class Btc_Tip_Jar_User_Withdraw extends Btc_Tip_Jar_User_Page {
	public function __construct( $user, $title ) {
		parent::__construct( $user, $title );

		add_action( 'admin_head', array( &$this, 'get_withdrawal_form' ) );
	}
	public function get_withdrawal_form() {

		global $_POST;

		if (
			!empty( $_POST['btc-tip-jar_withdraw'] )
			&&
			check_admin_referer( 'btc-tip-jar_withdraw' )
		) {
			global $current_user;
			get_currentuserinfo();

			$status = $this->user->tip_jar->btc->do_withdrawal(
				$current_user->ID,
				$_POST['btc-tip-jar_withdraw']['address'],
				$_POST['btc-tip-jar_withdraw']['amount']
			);

			print_r( $status );

			switch ( $status ) {
			case 'OVERDRAFT':
				printf( __( 'Insufficient Funds for Withdrawal.', 'btc-tip-jar' ) );
				break;
			}
		}

	}
	public function do_page_body() {

		$this->get_balance();

		echo '<form name="btc-tip-jar_withdraw" method="post">';
		wp_nonce_field( 'btc-tip-jar_withdraw' );
		echo '<table class="form-table">';

		echo '<tr valign="top">';
		echo '<th scope="row">';
		echo '<label for="btc-tip-jar_withdraw[address]">Send To Address:</label>';
		echo '</th>';
		echo '<td>';

		echo '<input type="text" class="regular-text" ';
		echo 'name="btc-tip-jar_withdraw[address]" id="btc-tip-jar_withdraw[address]" ';
		echo 'value="" />';

		echo '</td>';
		echo '</tr>';

		echo '<tr valign="top">';
		echo '<th scope="row">';
		echo '<label for="btc-tip-jar_withdraw[amount]">Withdraw Amount:</label>';
		echo '</th>';
		echo '<td>';

		echo '<input type="text" class="regular-text" ';
		echo 'name="btc-tip-jar_withdraw[amount]" id="btc-tip-jar_withdraw[amount]" ';
		echo 'value="" />';

		echo '</td>';
		echo '</tr>';

		echo '</table>';
		submit_button( __( 'Send To Address', 'btc-tip-jar' ) );
		echo '</form>';
	}
}

?>
