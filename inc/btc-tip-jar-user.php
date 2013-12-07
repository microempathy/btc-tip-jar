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
			'Bitcoin Tip Jar',
			'Tip Jar',
			'read',
			$this->tip_jar->prefix,
			array( &$this->overview, 'do_page' ),
			plugins_url( '../images/btc-tip-jar-16.png', __FILE__ ),
			71
		);

		$this->do_page(
			'Bitcoin Tip Jar - Overview',
			'Overview',
			'',
			$this->overview
		);

		$this->do_page(
			'Bitcoin Tip Jar - Deposit Bitcoins',
			'Deposit',
			'_deposit',
			$this->deposit
		);

		$this->do_page(
			'Bitcoin Tip Jar - Withdraw Bitcoins',
			'Withdraw',
			'_withdraw',
			$this->withdraw
		);

		$this->do_page(
			'Bitcoin Tip Jar - Transfer Bitcoins',
			'Transfer',
			'_transfer',
			$this->transfer
		);
	}
	private function do_page( $title, $menu_title, $page_slug, $print ) {
		$page = add_submenu_page(
			$this->tip_jar->prefix,
			$title,
			$menu_title,
			'read',
			$this->tip_jar->prefix . $page_slug,
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
			$this->tip_jar->prefix,
			plugins_url( '/../styles/btc-tip-jar-admin.css', __FILE__ )
		);

		wp_register_script(
			$this->tip_jar->prefix,
			plugins_url( '/../scripts/btc-tip-jar-admin.js', __FILE__ )
		);

		wp_register_script(
			$this->tip_jar->prefix . '_formatCurrency',
			plugins_url( '/../scripts/jquery-formatcurrency/jquery.formatCurrency.js', __FILE__ ),
			array(
				'jquery',
			),
			false,
			true
		);

		wp_localize_script(
			$this->tip_jar->prefix,
			$this->tip_jar->prefix,
			array(
				'decimals' => $this->tip_jar->menu->settings['decimals'],
			)
		);

	}
	public function print_script() {
		wp_enqueue_script( $this->tip_jar->prefix );
		wp_enqueue_script( $this->tip_jar->prefix . '_formatCurrency' );
	}
	public function print_style() {
		wp_enqueue_style( $this->tip_jar->prefix );
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
		echo '<h1>Bitcoin Tip Jar - ';
		echo esc_html( $this->title );
		echo '</h1>' . "\n";

		$this->do_page_body();

		echo '</div>';
	}

	abstract public function do_page_body();
}

?>
