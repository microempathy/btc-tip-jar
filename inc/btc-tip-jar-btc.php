<?php

class Btc_Tip_Jar_Btc {
	private $settings;

	private $settings_menu;

	private $database;

	private $connect_string;

	public function __construct( $settings, $settings_menu, $database ) {
		$this->settings = $settings;
		$this->settings_menu = $settings_menu;
		$this->database = $database;

		if ( $this->settings_menu['rpcssl'] ) {
			$schema = 'https';
		} else {
			$schema = 'http';
		}

		$this->connect_string  = "{$schema}://";
		$this->connect_string .= "{$this->settings_menu['rpcuser']}:{$this->settings_menu['rpcpassword']}@";
		$this->connect_string .= "{$this->settings_menu['rpcconnect']}:{$this->settings_menu['rpcport']}";

	}
	public function connect() {
		require_once( plugin_dir_path( __FILE__ ) . '../lib/json-rpc-php/jsonRPCClient.php' );

		if (
			empty( $this->settings_menu['rpcuser'] )
			||
			empty( $this->settings_menu['rpcpassword'] )
			||
			empty( $this->settings_menu['rpcwallet'] )
		) {
			return false;
		}

		try {
			$connection = new jsonRPCClient( $this->connect_string, false );
			$connection->walletpassphrase( $this->settings_menu['rpcwallet'], intval( $this->settings['rpctimeout'] ) );

			return $connection;
		} catch( Exception $e ) {
			error_log( $e->getMessage() );
			return false;
		}

	}
	public function get_tx_history() {

	}
	public function get_user_address( $user ) {

		$label  = home_url( '/' );
		$label .= get_class() . '/' . $user;

		$user_address = get_user_meta( $user, '_' . get_class() . '_account', true );
		if ( empty( $user_address ) ) {
			$btc = $this->connect();
			try {
				$getaccountaddress = $btc->getaccountaddress( $label );
				$user_address = array();
				$user_address['label']   = $label;
				$user_address['address'] = $getaccountaddress;
				update_user_meta( $user, '_' . get_class() . '_account', $user_address );
			} catch( Exception $e ) {
				error_log( $e->getMessage() );
			}
		} else {
			return $user_address;
		}
	}
	public function get_post_address_user( $post_id, $author_id, $user_id ) {

		$author_account = $this->get_user_address( $author_id );

		$address = $this->database->get_user_address_query(
			$post_id,
			$author_id,
			$user_id
		);

		if ( !empty( $address ) ) {
			return $address;
		} else {
			$btc = $this->connect();
			try {
				$getnewaddress = $btc->getnewaddress( $author_account['label'] );

				$this->database->insert_post_address_user(
					$author_id,
					$post_id,
					$user_id,
					$getnewaddress
				);

				return $getnewaddress;
			} catch( Exception $e ) {
				error_log( $e->getMessage() );
				return false;
			}
		}
	}
	public function get_post_address_anonymous( $post_id, $author ) {

		$author_account = $this->get_user_address( $author );

		$anonymous_address = get_post_meta( $post_id, '_' . get_class() . '_anonymous', true );

		if ( empty( $anonymous_address ) ) {
			$btc = $this->connect();
			try {
				$getnewaddress = $btc->getnewaddress( $author_account['label'] );
			} catch( Exception $e ) {
				error_log( $e->getMessage() );
			}
			$anonymous_address = $getnewaddress;
			update_post_meta( $post_id, '_' . get_class() . '_anonymous', $anonymous_address );
		}

		return $anonymous_address;
	}
}

?>
