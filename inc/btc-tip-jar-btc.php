<?php

class Btc_Tip_Jar_Btc {
	private $rpcconnect;
	private $rpcssl;
	private $rpcport;
	private $rpcuser;
	private $rpcpassword;
	private $rpcwallet;

	private $connect_string;

	public function __construct(
		$rpcconnect,
		$rpcssl,
		$rpcport,
		$rpcuser,
		$rpcpassword,
		$rpcwallet,
		$rpctimeout
	) {
		$this->rpcconnect  = $rpcconnect;
		$this->rpcssl      = $rpcssl;
		$this->rpcport     = $rpcport;
		$this->rpcuser     = $rpcuser;
		$this->rpcpassword = $rpcpassword;
		$this->rpcwallet   = $rpcwallet;
		$this->rpctimeout  = $rpctimeout;

		if ( $this->rpcssl ) {
			$schema = 'https';
		} else {
			$schema = 'http';
		}

		$this->connect_string  = "{$schema}://";
		$this->connect_string .= "{$this->rpcuser}:{$this->rpcpassword}@";
		$this->connect_string .= "{$this->rpcconnect}:{$this->rpcport}";

	}
	public function connect() {
		require_once( plugin_dir_path( __FILE__ ) . '../lib/json-rpc-php/jsonRPCClient.php' );

		if (
			empty( $this->rpcuser )
			||
			empty( $this->rpcpassword )
			||
			empty( $this->rpcwallet )
		) {
			return false;
		}

		try {
			$connection = new jsonRPCClient( $this->connect_string, false );
			$connection->walletpassphrase( $this->rpcwallet, intval( $this->rpctimeout ) );

			return $connection;
		} catch( Exception $e ) {
			error_log( $e->getMessage() );
			return false;
		}

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
		global $wpdb;
		$settings = get_option( 'Btc_Tip_Jar' );

		$sql = <<<SQL
SELECT
	address
	FROM {$settings['addresses_table']}
	WHERE post_id   = {$post_id}
	  AND author_id = {$author_id}
	  AND user_id   = {$user_id}
	LIMIT 1;
SQL;

		$address = $wpdb->get_results( $sql );

		if ( !empty( $address[0]->address ) ) {
			return $address->address;
		} else {
			$btc = $this->connect();
			try {
				$getnewaddress = $btc->getnewaddress( $author_account['label'] );
				$wpdb->insert(
					$settings['addresses_table'],
					array(
						'author_id' => $author_id,
						'post_id'   => $post_id,
						'user_id'   => $user_id,
						'address'   => $getnewaddress,
					)
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
