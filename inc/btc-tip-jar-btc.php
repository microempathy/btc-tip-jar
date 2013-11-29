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
	public function get_author_account( $author ) {

		$label  = home_url( '/' );
		$label .= get_class() . '/' . $author;

		$author_account = get_user_meta( $author, '_' . get_class() . '_account', true );
		if ( empty( $author_account ) ) {
			$btc = $this->connect();
			try {
				$getaccountaddress = $btc->getaccountaddress( $label );
				$author_account = array();
				$author_account['label']   = $label;
				$author_account['address'] = $getaccountaddress;
				update_user_meta( $author, '_' . get_class() . '_account', $author_account );
			} catch( Exception $e ) {
				error_log( $e->getMessage() );
			}
		} else {
			return $author_account;
		}
	}
	public function get_post_address_user( $post_id, $author, $user ) {

		$author_account = $this->get_author_account( $author );

	}
	public function get_post_address_anonymous( $post_id, $author ) {

		$author_account = $this->get_author_account( $author );

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
