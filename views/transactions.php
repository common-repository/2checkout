<?php
$time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

$config = [
	'per_page'		=> 20,
	'columns'		=> [
		'id'				=> __( 'Transaction #', '2checkout' ),
		'form_id'			=> __( 'Payment Form', '2checkout' ),
		'refno'				=> __( 'Reference #', '2checkout' ),
		'payer_name'		=> __( 'Payer Name', '2checkout' ),
		'payer_email'		=> __( 'Payer Email', '2checkout' ),
		'amount'			=> __( 'Amount', '2checkout' ),
		'custom_fields'		=> __( 'Custom Fields', '2checkout' ),
		'status'			=> __( 'Payment Status', '2checkout' ),
		'time'				=> __( 'Time', '2checkout' ),
	],
	'sortable'		=> [],
	'orderby'		=> 'id',
	'order'			=> 'desc',
	'data'			=> [],
	'bulk_actions'	=> [
		'delete'	=> __( 'Delete', '2checkout' ),
	],
];

global $wpdb;
$transactions = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}2co_transactions`" );
foreach ( $transactions as $transaction ) {

	$custom_fields = get_transaction_meta( $transaction->id, 'custom_fields', true );
	$custom_fields_html = '<div class="twoco-custom-fields-data" >';
	if ( $custom_fields ) {
		$custom_fields = unserialize( $custom_fields );
		foreach ( $custom_fields as $field ) {
			$name = ucfirst( $field['name'] );
			$custom_fields_html .= "<p>{$name} : {$field['value']}</p>";
		}
	}
	$custom_fields_html .= '</div>';


	$config['data'][] = [
		'id'			=> "#{$transaction->id}",
		'form_id'		=> get_the_title( $transaction->form_id ),
		'refno'			=> $transaction->refno,
		'payer_name'	=> $transaction->payer_name,
		'payer_email'	=> $transaction->payer_email,
		'amount'		=> strtoupper( $transaction->currency ) . ' ' . $transaction->amount,
		'custom_fields'	=> $custom_fields_html,
		'status'		=> $transaction->status,
		'time'			=> date_i18n( $time_format, $transaction->time ),
	];
}

$table = new \codexpert\plugin\Table( $config );

echo '
<div class="wrap">
	<h2>' . __( 'Transactions', '2checkout' ) . '</h2>
	<form method="post">';

$table->prepare_items();
$table->search_box( 'Search', 'search' );
$table->display();

echo '</form>
</div>';