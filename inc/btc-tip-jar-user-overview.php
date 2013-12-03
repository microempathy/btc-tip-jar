<?php

class Btc_Tip_Jar_User_Overview {
	private $user;

	public function __construct( $user ) {
		$this->user = $user;

		$this->table = new Btc_Tip_Jar_User_History_Table();
	}
	public function do_page() {

		echo '<div id="wrap">';
		screen_icon();
		echo '<h1>Bitcoin Tip Jar - Overview</h1>';

		$this->table->get_transactions( $this->get_transactions() );

		$this->table->prepare_items();
		$this->table->display();

		echo '</div>';
	}
	public function get_transactions() {
		$transactions = $this->user->tip_jar->database->get_transactions(
			1,
			'tip',
			'2013-01-01',
			'2014-01-01'
		);

		foreach ( $transactions as &$transaction ) {
			if ( $transaction['type'] == 'tip' ) {
				$tip_post = get_post( $transaction['post_id'] );
				$transaction['title'] = $tip_post->post_title;
				$transaction['link']  = get_permalink( $transaction['post_id'] );
			} else {
				$transaction['title'] = 'Non-Tip';
			}

			$tx_user = get_user_meta( $transaction['tx_id'], 'nickname', true );
			$rx_user = get_user_meta( $transaction['rx_id'], 'nickname', true );

			$transaction['tx_user'] = $tx_user;
			$transaction['rx_user'] = $rx_user;

			$transaction['balance'] = 'N/A';
		}

		return $transactions;
	}
}

class Btc_Tip_Jar_User_History_Table extends WP_List_Table {
	function __construct() {
		parent::__construct(
			array(
				'singular' => 'Transaction',
				'plural'   => 'Transactions',
				'ajax'     => true,
			)
		);
	}
	public function get_transactions( $transactions ) {
		$this->items = $transactions;
	}
	public function get_columns() {
		return array(
			'time'    => 'Date',
			'type'    => 'Type',
			'title'   => 'Title',
			'tx_user' => 'From',
			'rx_user' => 'To',
			'amount'  => 'Amount',
			'balance' => 'Balance',
		);
	}
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'time':
			$date = new DateTime( $item[$column_name] );
			return $date->format( 'Y-m-d H:i:s' );
		case 'type':
			switch ( $item[$column_name] ) {
			case 'tip':
				return 'Tip';
			case 'withdrawal':
				return 'Withdrawal';
			case 'deposit':
				return 'Deposit';
			case 'transfer':
				return 'Transfer';
			default:
				return $item[$column_name];
			}
			case 'title':
				return $this->get_title( $item[$column_name], $item['link'] );
			case 'amount':
				$class = get_class() . '_amount';
				return "<span class=\"{$class}\">{$item[$column_name]}</span>";
			default:
				return $item[$column_name];
		}
	}
	private function get_title( $title, $link ) {
		if ( empty( $link ) ) {
			return $title;
		} else {
			return "<a href=\"{$link}\">{$title}</a>";
		}
	}
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );
	}
}



?>
