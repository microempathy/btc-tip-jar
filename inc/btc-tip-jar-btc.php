<?php

class Btc_Tip_Jar_Btc {
	private $rpcconnect;
	private $rpcssl;
	private $rpcport;
	private $rpcuser;
	private $rpcpassword;
	private $rpcwallet;

	private $connection;
	private $connected = false;

	public function __construct(
		$rpcconnect  = 'rpc.blockchain.info',
		$rpcssl      = false,
		$rpcport     = 80,
		$rpcuser     = null,
		$rpcpassword = null,
		$rpcwallet   = null
	) {
		$this->rpcconnect  = $rpcconnect;
		$this->rpcssl      = $rpcssl;
		$this->rpcport     = $rpcport;
		$this->rpcuser     = $rpcuser;
		$this->rpcpassword = $rpcpassword;
		$this->rpcwallet   = $rpcwallet;

		if ( $this->connect() ) {
			$this->connected = true;
		}

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

		if ( $this->rpcssl ) {
			$schema = 'https';
		} else {
			$schema = 'http';
		}

		$connect_string  = "{$schema}://";
		$connect_string .= "{$this->rpcuser}:{$this->rpcpassword}@";
		$connect_string .= "{$this->rpcconnect}:{$this->rpcport}";

		error_log( $connect_string );

		try {
		$this->connection = new jsonRPCClient( $connect_string, true );
		$this->connection->getInfo();

		return true;
		} catch( Exception $e ) {
			error_log( $e->getMessage() );
			return false;
		}

	}
}

?>
