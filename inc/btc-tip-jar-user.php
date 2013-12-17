<?php

class Btc_Tip_Jar_User {
	public $tip_jar;

	private $overview;
	private $deposit;
	private $withdraw;
	private $transfer;

	public function __construct( $tip_jar ) {
		$this->tip_jar = $tip_jar;

		require_once( 'btc-tip-jar-user-overview.php' );
		$this->overview = new Btc_Tip_Jar_User_Overview( $this, 'Overview' );

		require_once( 'btc-tip-jar-user-deposit.php' );
		$this->deposit = new Btc_Tip_Jar_User_Deposit( $this, 'Deposit' );

		require_once( 'btc-tip-jar-user-withdraw.php' );
		$this->withdraw = new Btc_Tip_Jar_User_Withdraw( $this, 'Withdraw' );

		require_once( 'btc-tip-jar-user-transfer.php' );
		$this->transfer = new Btc_Tip_Jar_User_Transfer( $this, 'Transfer' );

		add_action( 'admin_menu', array( &$this, 'do_menu' ) );

		add_action( 'admin_init', array( &$this, 'do_scripts_and_styles' ) );
	}
	public function do_menu() {

		add_menu_page(
			__( 'Bitcoin Tip Jar', 'btc-tip-jar' ),
			__( 'Tip Jar', 'btc-tip-jar' ),
			'read',
			'btc-tip-jar',
			array( &$this->overview, 'do_page' ),
			plugins_url( '../images/btc-tip-jar-16.png', __FILE__ ),
			71
		);

		$this->do_page(
			__( 'Bitcoin Tip Jar - Overview', 'btc-tip-jar' ),
			__( 'Overview', 'btc-tip-jar' ),
			'',
			$this->overview
		);

		$this->do_page(
			__( 'Bitcoin Tip Jar - Deposit Bitcoins', 'btc-tip-jar' ),
			__( 'Deposit', 'btc-tip-jar' ),
			'_deposit',
			$this->deposit
		);

		$this->do_page(
			__( 'Bitcoin Tip Jar - Withdraw Bitcoins', 'btc-tip-jar' ),
			__( 'Withdraw', 'btc-tip-jar' ),
			'_withdraw',
			$this->withdraw
		);

		$this->do_page(
			__( 'Bitcoin Tip Jar - Transfer Bitcoins', 'btc-tip-jar' ),
			__( 'Transfer', 'btc-tip-jar' ),
			'_transfer',
			$this->transfer
		);
	}
	private function do_page( $title, $menu_title, $page_slug, $print ) {
		$page = add_submenu_page(
			'btc-tip-jar',
			$title,
			$menu_title,
			'read',
			'btc-tip-jar' . $page_slug,
			array( &$print, 'do_page' )
		);

		add_action(
			'admin_print_scripts-' . $page,
			array( &$this, 'print_script' )
		);

		add_action(
			'admin_print_styles-' . $page,
			array( &$this, 'print_style' )
		);
	}
	public function do_scripts_and_styles() {
		wp_register_style(
			'btc_tip_jar',
			plugins_url( '/../styles/btc-tip-jar-admin.css', __FILE__ )
		);

		wp_register_script(
			'btc_tip_jar',
			plugins_url( '/../scripts/btc-tip-jar-admin.js', __FILE__ )
		);

		wp_register_script(
			'btc_tip_jar_fx',
			plugins_url( '/../scripts/btc-tip-jar-fx.js', __FILE__ )
		);

		wp_register_script(
			'btc-tip-jar_formatCurrency',
			plugins_url( '/../scripts/jquery-formatcurrency/jquery.formatCurrency.js', __FILE__ ),
			array(
				'jquery',
			),
			false,
			true
		);

		wp_localize_script(
			'btc_tip_jar',
			'btc_tip_jar',
			array(
				'decimals' => $this->tip_jar->menu->settings['decimals'],
			)
		);

		wp_localize_script(
			'btc_tip_jar_fx',
			'btc_tip_jar_fx',
			array(
				'url' => $this->tip_jar->settings['fx_rate_url'],
				'fx'  => $this->tip_jar->menu->settings['fx'],
			)
		);

	}
	public function print_script() {
		wp_enqueue_script( 'btc_tip_jar' );
		wp_enqueue_script( 'btc_tip_jar_fx' );
		wp_enqueue_script( 'btc-tip-jar_formatCurrency' );
	}
	public function print_style() {
		wp_enqueue_style( 'btc-tip-jar' );
	}
}

abstract class Btc_Tip_Jar_User_Page {
	protected $user;

	protected $title;

	public function __construct( $user, $title ) {
		$this->user  = $user;
		$this->title = $title;
	}

	public function do_page() {
		echo '<div id="wrap">';
		screen_icon();

		printf(
			__( "<h1>Bitcoin Tip Jar - %s</h1>\n", 'btc-tip-jar' ),
			esc_html( $this->title )
		);

		$this->do_page_body();

		echo '</div>';
	}
	protected function get_balance() {
		global $current_user;
		get_currentuserinfo();

		$balance = $this->user->tip_jar->btc->get_user_balance( $current_user->ID );

		echo '<span id="btc-tip-jar_balance">';
		printf( __( 'Balance: ' ) );
		echo '<span class="btc-tip-jar_fx-format" id="btc-tip-jar_balance-amount">';
		echo esc_html( $balance );
		echo '</span>';
		echo '</span>';

	}

	abstract public function do_page_body();
}

?>
