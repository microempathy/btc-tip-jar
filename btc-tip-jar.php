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
	private $settings;
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

		$this->settings = get_option( get_class(), $defaults );

		// bitcoin functionality
		require_once( 'inc/btc-tip-jar-btc.php' );
		$this->btc = new Btc_Tip_Jar_Btc(
			$this->settings['connection']['rpcconnect'],
			$this->settings['connection']['rpcssl'],
			$this->settings['connection']['rpcport'],
			$this->settings['connection']['rpcuser'],
			$this->settings['connection']['rpcpassword'],
			$this->settings['connection']['rpcwallet']
		);

	}
}
$btc_tip_jar = new Btc_Tip_Jar();
