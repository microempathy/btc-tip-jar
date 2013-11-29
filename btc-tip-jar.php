<?php
/*
 * Plugin Name: Bitcoin Tip Jar
 * Plugin URI:  https://bitbucket.org/wikitopian/btc-tip-jar
 * Description: Easily tip specific posts with bitcoins.
 * Version:     0.1
 * Author:      @wikitopian
 * Author URI:  http://www.swarmstrategies.com/matt
 * License:     LGPLv3
 * */

class Btc_Tip_Jar {
	private $menu;
	private $btc;

	public function __construct() {

		$defaults = array(
			'rpcconnect'  => 'rpc.blockchain.info',
			'rpcssl'      => true,
			'rpcport'     => 443,
			'rpcuser'     => null,
			'rpcpassword' => null,
			'rpcwallet'   => null,
			'rpctimeout'  => 5.0,
		);

		// admin menu functionality
		require_once( 'inc/btc-tip-jar-menu.php' );
		$this->menu = new Btc_Tip_Jar_Menu( $defaults );

		// bitcoin functionality
		require_once( 'inc/btc-tip-jar-btc.php' );
		$this->btc = new Btc_Tip_Jar_Btc(
			$this->menu->settings['rpcconnect'],
			$this->menu->settings['rpcssl'],
			$this->menu->settings['rpcport'],
			$this->menu->settings['rpcuser'],
			$this->menu->settings['rpcpassword'],
			$this->menu->settings['rpcwallet'],
			$this->menu->settings['rpctimeout']
		);

		add_filter( 'the_content', array( &$this, 'add_qr_code' ) );
	}
	public function add_qr_code( $content = '' ) {

		if ( !is_single() ) {
			return $content;
		}

		global $post;

		$anonymous_address = $this->btc->get_post_address_anonymous( $post->post_author, $post->ID );

		$content .= 'QR Code: ' . $anonymous_address;

		return $content;
	}
}
$btc_tip_jar = new Btc_Tip_Jar();
