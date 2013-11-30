<?php

class Btc_Tip_Jar_Database {
	private $wpdb;

	private $settings;
	private $settings_menu;
	private $settings_database;

	public function __construct( $settings, $settings_menu ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		global $wpdb;
		$this->wpdb = $wpdb;

		$this->settings = $settings;
		$this->settings_menu = $settings_menu;

		$db_prefix = 'Btc_Tip_Jar';
		$transactions_table = "{$this->wpdb->base_prefix}{$db_prefix}_transactions";
		$addresses_table    = "{$this->wpdb->base_prefix}{$db_prefix}_addresses";

		$settings_database = array(
			'db_prefix'          => $db_prefix,
			'transactions_table' => $transactions_table,
			'addresses_table'    => $addresses_table,
		);

		delete_option( get_class() );

		$this->settings_database = get_option( get_class(), $settings_database );
		update_option( get_class(), $this->settings_database );
	}
	public function create_transactions_table() {

		$transactions_sql = <<<SQL
CREATE TABLE {$this->settings_database['transactions_table']} (
	fee       DECIMAL(16,8) DEFAULT 0.0,
	amount    DECIMAL(16,8) DEFAULT 0.0,
	blockindex VARCHAR(64) NOT NULL,
	category  VARCHAR(64)  NOT NULL,
	confirmations mediumint(9) DEFAULT 0,
	address   VARCHAR(64)  NOT NULL,
	txid      VARCHAR(64)  NOT NULL,
	block     mediumint(9) NOT NULL,
	blockhash VARCHAR(64)  NOT NULL,
	account   varchar(64)  NULL,
	UNIQUE KEY (txid)
);
SQL;

		DbDelta( $transactions_sql );

	}
	public function create_addresses_table() {

		$addresses_table_sql = <<<SQL
CREATE TABLE {$this->settings_database['addresses_table']} (
	id        mediumint(9) NOT NULL AUTO_INCREMENT,
	time      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
	author_id mediumint(9) NOT NULL,
	post_id   mediumint(9) NOT NULL,
	user_id   mediumint(9) NOT NULL,
	address   VARCHAR(64)  NOT NULL,
	UNIQUE KEY (id)
);
SQL;

		dbDelta( $addresses_table_sql );
	}
	public function get_user_address_query( $post_id, $author_id, $user_id ) {

		$sql = <<<SQL
SELECT
	address
	FROM {$this->settings_database['addresses_table']}
	WHERE post_id   = {$post_id}
	  AND author_id = {$author_id}
	  AND user_id   = {$user_id}
	LIMIT 1;
SQL;

		$results = $this->wpdb->get_results( $sql );

		if ( !empty( $results[0] ) ) {
			return $results[0]->address;
		} else {
			return false;
		}
	}
	public function insert_post_address_user(
		$author_id,
		$post_id,
		$user_id,
		$getnewaddress
	) {
		$this->wpdb->insert(
			$this->settings_database['addresses_table'],
			array(
				'author_id' => $author_id,
				'post_id'   => $post_id,
				'user_id'   => $user_id,
				'address'   => $getnewaddress,
			)
		);
	}
	public function insert_transactions( $transactions ) {
		foreach ( $transactions as $transaction ) {
			$this->wpdb->insert(
				$this->settings_database['transactions_table'],
				array(
				'fee'           => $transaction['fee'],
				'amount'        => $transaction['amount'],
				'blockindex'    => $transaction['blockindex'],
				'category'      => $transaction['category'],
				'confirmations' => $transaction['confirmations'],
				'address'       => $transaction['address'],
				'txid'          => $transaction['txid'],
				'block'         => $transaction['block'],
				'blockhash'     => $transaction['blockhash'],
				'account'       => $transaction['account'],
				)
			);
		}
	}
}

?>
