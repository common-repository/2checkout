<?php
$form_id 	= $args['id'];
$_post_meta = get_post_meta( $form_id );
$post_meta 	= [];
$hidden 	= $flex = $min_max = '';
$script		= '';
foreach ( $_post_meta as $key => $value ) {
	$post_meta[ $key ] = is_serialized( $value[0] ) ? unserialize( $value[0] ) : $value[0];
}
extract( $post_meta['twoco_payment_type'] );
extract( $post_meta['twoco_amount'] );
extract( $post_meta['twoco_others'] );

if ( $amount_type == 'fixed' ) {
	$hidden 	= "display:none;";
	$btn_label 	= str_replace( '%%amount%%', "{$amount}", esc_html( $btn_label ) );
}
else{
	$flex 		= "display: flex;gap:10px;";
	$min_max 	= "min='{$min_amount}' max='{$max_amount}'";
	$amount 	= '';
	$btn_label 	= str_replace( '%%amount%%', "", esc_html( $btn_label ) );
}

$_cart = [
		'id' 		=> $form_id,
		'name' 		=> get_the_title( $form_id ),
		'price' 	=> $amount,
		'quantity' 	=> 1
	];

if ( $payment_type == 'subscription' ) {
	
	$_cart['recurrence'] = [
		'unit'		=> strtoupper( $period_unit ),
		'length'	=> $period_time,
	];

	$_cart['duration'] = [
		'unit'		=> strtoupper( $period_unit ),
		'length'	=> $period_time,
	];

	$_cart['renewalPrice'] = (float)$amount;
}

$cart[] = $_cart;
$custom_fields = '';
if ( isset( $enable_custom_field ) && $enable_custom_field == 'on' ) {
	$fields = [
		[
			// 'id' => 'field_1',
			'label' => 'Field 1',
			'type'  => 'text',
		],
		[
			'id' => 'field_2',
			'label' => 'Field 2',
			'type'  => 'number',
		],
		[
			'id' => 'field_3',
			'label' => 'Field 3',
			'type'  => 'email',
		],
	];

	$custom_fields = twoco_generate_html( $fields );
}

$cart 			= json_encode( $cart );
$redirect_url 	= add_query_arg( 'form_id', $form_id, get_the_permalink() );
if ( isset( $_GET['form_id'] ) && isset( $_GET['refno'] ) && $success_page != '' && $_GET['form_id'] == $form_id ) {
	$script = "<script>window.location.href='{$success_page}'</script>";
}
echo "
<div class='twoco-pay-container'>
	{$custom_fields}
	<div class='twoco-pay-section' style='{$flex}'>
		<div class='twoco-input-group' style='border-color: {$btn_color};{$hidden}'>
			<div class='twoco-prepend' style='background: {$btn_color}'>{$currency}</div>
			<input class='twoco-paying-amount' type='number' name='paying_amount' {$min_max} value={$amount}>
			<input type='hidden' name='redirect_url' value='{$redirect_url}'>
			<input type='hidden' name='currency' value='{$currency}'>
			<input type='hidden' name='cart' value='{$cart}'>
		</div>
		<button class='twoco-pay-btn' style='background:{$btn_color};color:#fff;border-color:{$btn_color}'>{$btn_label}</button>
	</div>
</div>{$script}";

?>