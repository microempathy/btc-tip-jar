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
			'connection' => array(
				'rpcconnect'  => 'rpc.blockchain.info',
				'rpcssl'      => false,
				'rpcport'     => 80,
				'rpcuser'     => null,
				'rpcpassword' => null,
				'rpcwallet'   => null,
			),
		);

		// admin menu functionality
		require_once( 'inc/btc-tip-jar-menu.php' );
		$this->menu = new Btc_Tip_Jar_Menu( $defaults );

		// bitcoin functionality
		require_once( 'inc/btc-tip-jar-btc.php' );
		$this->btc = new Btc_Tip_Jar_Btc(
			$this->menu->settings['connection']['rpcconnect'],
			$this->menu->settings['connection']['rpcssl'],
			$this->menu->settings['connection']['rpcport'],
			$this->menu->settings['connection']['rpcuser'],
			$this->menu->settings['connection']['rpcpassword'],
			$this->menu->settings['connection']['rpcwallet']
		);

	}
}
$btc_tip_jar = new Btc_Tip_Jar();
