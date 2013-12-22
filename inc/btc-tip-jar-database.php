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

		$transactions_table = "{$this->wpdb->base_prefix}btc_tip_jar_transactions";
		$addresses_table    = "{$this->wpdb->base_prefix}btc_tip_jar_addresses";

		$settings_database = array(
			'transactions_table' => $transactions_table,
			'addresses_table'    => $addresses_table,
		);

		$this->settings_database = get_option(
			'btc-tip-jar_database',
			$settings_database
		);

		update_option( 'btc-tip-jar_database', $this->settings_database );
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
	blockhash VARCHAR(64)  NOT NULL,
	account   varchar(64)  NULL,
	time      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY (txid)
);
SQL;

		DbDelta( $transactions_sql );

	}
	public function create_addresses_table() {

		$addresses_table_sql = <<<SQL
CREATE TABLE {$this->settings_database['addresses_table']} (
	id        mediumint(9) NOT NULL AUTO_INCREMENT,
	type      VARCHAR(16)  NOT NULL,
	tx_id     mediumint(9) NOT NULL,
	rx_id     mediumint(9) NOT NULL,
	post_id   mediumint(9) NOT NULL,
	address   VARCHAR(64)  NOT NULL,
	UNIQUE KEY (id)
);
SQL;

		dbDelta( $addresses_table_sql );
	}
	public function get_user_address_query(
		$post_id,
		$rx_id,
		$tx_id
	) {

		$sql = <<<SQL
SELECT
	address
	FROM {$this->settings_database['addresses_table']}
	WHERE post_id   = {$post_id}
	  AND rx_id     = {$rx_id}
	  AND tx_id     = {$tx_id}
	LIMIT 1;
SQL;

		$results = $this->wpdb->get_results( $sql );

		if ( !empty( $results[0] ) ) {
			return $results[0]->address;
		} else {
			return false;
		}
	}
	public function insert_post_address_user( $post_id, $rx_id, $tx_id, $address ) {
		$this->wpdb->insert(
			$this->settings_database['addresses_table'],
			array(
				'type'      => 'tip',
				'post_id'   => $post_id,
				'rx_id'     => $rx_id,
				'tx_id'     => $tx_id,
				'address'   => $address,
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
				'blockhash'     => $transaction['blockhash'],
				'account'       => $transaction['account'],
				)
			);
		}
	}
	public function get_donated_post( $post_id ) {

		$donations_query = <<<SQL
SELECT
	SUM( trx.amount ) AS btc
	FROM {$this->settings_database['addresses_table']} AS adr
	INNER JOIN {$this->settings_database['transactions_table']} AS trx
	ON  trx.address  = adr.address
	AND adr.type     = 'tip'
	AND trx.category = 'receive'
	WHERE adr.post_id = {$post_id};
SQL;

		$donations = $this->wpdb->get_results( $donations_query );
		if ( !empty( $donations[0] ) ) {
			$donations = $donations[0]->btc;
		} else {
			$donations = 0.0;
		}

		$anon_address = get_post_meta( $post_id, 'btc-tip-jar_anonymous', true );

		$anon_donations_query = <<<SQL
SELECT
	SUM( trx.amount ) AS btc
	FROM {$this->settings_database['transactions_table']} AS trx
	WHERE trx.address  = '{$anon_address}'
	  AND trx.category = 'receive';
SQL;

		$anon_donations = $this->wpdb->get_results( $anon_donations_query );

		if ( !empty( $anon_donations[0] ) ) {
			return $donations + $anon_donations[0]->btc;
		} else {
			return $donations;
		}
	}
	public function get_transactions( $user, $type, $first, $final ) {

		$user_anonymous = get_user_meta(
			$user, 'btc-tip-jar_account', true
		);
		$user_anonymous = $user_anonymous['address'];

		if ( $type == 'all' ) {
			$type_snippet = '';
		} else {
			$type_snippet = "AND adr.type = '{$type}'";
		}

		$transactions_query = <<<TRANSACTIONS
SELECT
	trx.time,
	CASE
		WHEN trx.category = 'send' THEN 'withdrawal'
		WHEN adr.type IS NOT NULL THEN adr.type
		WHEN anm.address IS NOT NULL THEN 'tip'
		ELSE 'deposit'
	END as `type`,
	adr.post_id,
	CASE
		WHEN adr.tx_id IS NOT NULL THEN adr.tx_id
		WHEN anm.address IS NOT NULL THEN 0
		ELSE {$user}
	END AS `tx_id`,
	COALESCE(adr.rx_id, {$user}) AS `rx_id`,
	CASE
		WHEN trx.category = 'send' THEN trx.amount - trx.fee
		ELSE trx.amount
	END AS amount
	FROM {$this->settings_database['transactions_table']} AS trx
	LEFT JOIN {$this->settings_database['addresses_table']} AS adr
	ON  adr.address  = trx.address
	LEFT JOIN (
		SELECT
			anmpst_mta.meta_value AS `address`
			FROM {$this->wpdb->posts} AS pst
			LEFT JOIN {$this->wpdb->postmeta} anmpst_mta
			ON  anmpst_mta.post_id = pst.ID
			WHERE pst.post_author = {$user}
			  AND anmpst_mta.meta_key = 'btc-tip-jar_anonymous'
	) AS anm
	ON  anm.address = trx.address
	WHERE
	(
		adr.tx_id = {$user}
		OR
		adr.rx_id = {$user}
		OR
		trx.address = '{$user_anonymous}'
		OR
		anm.address IS NOT NULL
	)
	  {$type_snippet}
	  AND
	(
		trx.time >= '{$first}'
		AND
		trx.time <= '{$final}'
	)
	ORDER BY trx.time ASC;
TRANSACTIONS;

		return $this->wpdb->get_results( $transactions_query, ARRAY_A );
	}
}

?>
