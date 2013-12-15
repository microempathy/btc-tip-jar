<?php
/*
 * Plugin Name: Bitcoin Tip Jar
 * Plugin URI:  https://bitbucket.org/wikitopian/btc-tip-jar
 * Description: Easily tip specific posts with bitcoins.
 * Version:     0.1
 * Author:      @wikitopian
 * Author URI:  http://www.swarmstrategies.com/matt
 * License:     LGPLv3
 */

class Btc_Tip_Jar {
	public  $database;
	public  $menu;
	public  $btc;

	public $settings;
	public $settings_menu;

	public function __construct() {

		$settings = array(
			'debug'       => false,
			'rpctimeout'  => 2,
			'list_tx_max' => 999,
			'lastblock'   => false,
			'fx_rate_url' => 'https://blockchain.info/ticker?cors=true',
		);
		$this->settings = get_option( 'btc-tip-jar', $settings );
		update_option( 'btc-tip-jar', $this->settings );

		// admin menu functionality
		$settings_menu = array(
			'rpcconnect'  => 'rpc.blockchain.info',
			'rpcssl'      => true,
			'rpcport'     => 443,
			'rpcuser'     => null,
			'rpcpassword' => null,
			'rpcwallet'   => null,
			'fx'          => 'USD',
			'decimals'    => 5,
		);

		$this->settings_menu = get_option(
			'btc-tip-jar_options', $settings_menu
		);

		// user interface
		require_once( 'inc/btc-tip-jar-menu.php' );
		$this->menu = new Btc_Tip_Jar_Menu( $this->settings_menu );

		// database functionality
		require_once( 'inc/btc-tip-jar-database.php' );
		$this->database = new Btc_Tip_Jar_Database(
			$this->settings,
			$this->settings_menu
		);

		// bitcoin functionality
		require_once( 'inc/btc-tip-jar-btc.php' );
		$this->btc = new Btc_Tip_Jar_Btc(
			$this->settings,
			$this->settings_menu,
			$this->database
		);

		// user menus and functionality
		require_once( 'inc/btc-tip-jar-user.php' );
		$this->user = new Btc_Tip_Jar_User( $this );

		register_activation_hook(
			__FILE__,
			array( &$this->database, 'create_transactions_table' )
		);

		register_activation_hook(
			__FILE__,
			array( &$this->database, 'create_addresses_table' )
		);

		add_action(
			'wp_enqueue_scripts',
			array( &$this, 'do_scripts_and_styles' )
		);

		add_filter( 'the_content', array( &$this, 'add_post_tip_jar' ) );
	}
	public function do_scripts_and_styles() {

		global $wp_scripts;
		$jquery   = $wp_scripts->query( 'jquery-ui-core' );
		$protocol = is_ssl() ? 'https' : 'http';

		$url  = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/";
		$url .= "{$jquery->ver}/themes/smoothness/jquery-ui.min.css";

		wp_enqueue_style( 'jquery-ui-smoothness', $url, false, null );

		wp_enqueue_style(
			'btc-tip-jar',
			plugins_url( '/styles/btc-tip-jar.css', __FILE__ )
		);

		wp_enqueue_script(
			'btc-tip-jar',
			plugins_url( '/scripts/btc-tip-jar.js', __FILE__ ),
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-button',
				'jquery-ui-dialog',
			),
			false,
			true
		);

		wp_enqueue_script(
			'btc-tip-jar_Fx',
			plugins_url( '/scripts/btc-tip-jar-fx.js', __FILE__ ),
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-button',
				'jquery-ui-dialog',
			),
			false,
			true
		);

		wp_enqueue_script(
			'btc-tip-jar_formatCurrency',
			plugins_url( '/scripts/jquery-formatcurrency/jquery.formatCurrency.js', __FILE__ ),
			array(
				'jquery',
			),
			false,
			true
		);

		wp_localize_script(
			'btc-tip-jar',
			'btc-tip-jar',
			array(
				'decimals' => $this->menu->settings['decimals'],
			)
		);

		wp_localize_script(
			'btc-tip-jar_Fx',
			'btc-tip-jar_Fx',
			array(
				'url' => $this->settings['fx_rate_url'],
				'fx'  => $this->menu->settings['fx'],
			)
		);

	}
	public function add_post_tip_jar( $content = '' ) {

		if ( !is_single() ) {
			return $content;
		}

		global $post;
		global $current_user;
		get_currentuserinfo();

		if ( is_user_logged_in() ) {
			$qr_url = $this->get_tip_qr_url(
				$post->post_name,
				$post->ID,
				$post->post_author,
				$current_user->ID
			);
		} else {
			$qr_url = $this->get_tip_qr_url(
				$post->post_name,
				$post->ID,
				$post->post_author,
				false
			);
		}

		if ( is_user_logged_in() ) {
			$logout = wp_logout_url( get_permalink() );

			$before = "Donating as {$current_user->display_name}...";
			$after  = "<a href=\"{$logout}\">Log out</a> ";
			$after .= 'first to donate anonymously.';
		} else {
			$login = wp_login_url( get_permalink() );

			$before = 'Donating anonymously...';
			$after  = "<a href=\"{$login}\">Log in</a> first to take credit!";
		}

		$this->btc->refresh_tx_history();
		$donated = $this->database->get_donated_post( $post->ID );

		$tip_jar = <<<HTML
<div class="sharedaddy">
	<div class="sd-block">
		<input
			type     = "button"
			id       = "btc-tip-jar_tip-jar"
			class    = "btc-tip-jar_Fx-format"
			name     = "btc-tip-jar_tip-jar"
			title    = "Bitcoin Tip This Post"
			value    = "Tip This Post"
			data-btc = "{$donated}"
			/>
	</div>
</div>

<div id="btc-tip-jar_dialog" title="Bitcoin Tip Jar">
{$before}
<hr />
		<img src="{$qr_url}" />
<hr />
{$after}
</div>
HTML;

		$content .= "<br />\n" . $tip_jar;

		return $content;
	}
	public function get_tip_qr_url( $label, $post_id, $author_id, $user_id ) {

		if ( $user_id ) {
			$address = $this->btc->get_post_address_user(
				$post_id,
				$author_id,
				$user_id
			);
		} else {
			$address = $this->btc->get_post_address_anonymous(
				$post_id,
				$author_id
			);
		}

		return $this->get_qr_url( $address, $label );
	}
	public function get_qr_url( $address, $label ) {
		require_once( 'lib/phpqrcode/qrlib.php' );

		$filename = 'btc-tip-jar-' . $address . '.png';
		$path_url = plugins_url( '/lib/phpqrcode/cache/codes/', __FILE__ );
		$path     = plugin_dir_path( __FILE__ ) . 'lib/phpqrcode/cache/codes/';

		if ( !file_exists( $path . $filename ) ) {
			QRcode::png(
				"bitcoin:{$address}?label=donation-to-{$label}",
				$path . $filename,
				QR_ECLEVEL_H
			);
		}

		return $path_url . $filename;
	}
}
$btc_tip_jar = new Btc_Tip_Jar();
