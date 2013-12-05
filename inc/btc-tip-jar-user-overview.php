<?php

class Btc_Tip_Jar_User_Overview extends Btc_Tip_Jar_User_Page {
	private $table;

	public function do_page_body() {
		$this->table = new Btc_Tip_Jar_User_History_Table();

		$this->table->get_transactions( $this->get_transactions() );

		$this->table->prepare_items();
		$this->table->display();
	}
	public function get_transactions() {
		global $current_user;
		get_currentuserinfo();

		$transactions = $this->user->tip_jar->database->get_transactions(
			$current_user->ID,
			'all',
			'2000-01-01',
			'2020-01-01'
		);

		foreach ( $transactions as &$transaction ) {
			switch ( $transaction['type'] ) {
			case 'tip':
				$tip_post = get_post( $transaction['post_id'] );
				if ( $transaction['tx_id'] == 0 ) {
					$transaction['title'] = 'Anonymous Tip';
				} else {
					$link = get_permalink( $transaction['post_id'] );
					$title = "<a href=\"{$link}\">{$tip_post->post_title}</a>";
					$transaction['title'] = $title;
				}
				break;
			case 'deposit':
				$link = menu_page_url( 'Btc_Tip_Jar_User_deposit', false );
				$title = "<a href=\"{$link}\">Deposit</a>";
				$transaction['title'] = $title;
				break;
			case 'withdrawal':
				$link = menu_page_url( 'Btc_Tip_Jar_User_withdraw', false );
				$title = "<a href=\"{$link}\">Withdrawal</a>";
				$transaction['title'] = $title;
				break;
			}

			if ( $transaction['tx_id'] == 0 ) {
				$tx_user = 'Anonymous';
			} else {
				$tx_user = get_user_meta( $transaction['tx_id'], 'nickname', true );
			}

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
			case 'amount':
				$class = get_class() . '_amount';
				return "<span class=\"{$class}\">{$item[$column_name]}</span>";
			default:
				return $item[$column_name];
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
