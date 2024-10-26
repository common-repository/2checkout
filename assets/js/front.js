(function(document, src, libName, config) {
	var script = document.createElement("script");
	script.src = src;
	script.async = true;
	var firstScriptElement = document.getElementsByTagName("script")[0];
	script.onload = function() {
		for (var namespace in config) {
			if (config.hasOwnProperty(namespace)) {
				window[libName].setup.setConfig(namespace, config[namespace]);
			}
		}
		window[libName].register();
	};

	firstScriptElement.parentNode.insertBefore(script, firstScriptElement);
} )( document, "https://secure.2checkout.com/checkout/client/twoCoInlineCart.js", "TwoCoInlineCart", { cart: { host: "https://secure.2checkout.com" } } );

jQuery(function($){
	$(document).on('click', '.twoco-pay-btn', function(e){
		e.preventDefault()
		var parent 		= $(this).closest('.twoco-pay-container');
		var amount 		= $( 'input[name="paying_amount"]', parent).val();
		var currency 	= $( 'input[name="currency"]', parent).val();
		var redirect_url 	= $( 'input[name="redirect_url"]', parent).val();
		var custom_fields 	= $( '.twoco-custom-fields-form', parent).serializeArray();
		var cart = $( 'input[name="cart"]', parent).val();
			cart = JSON.parse(cart);
			cart[0]['price'] = amount;

			if ( 'renewalPrice' in cart[0] ) {
				cart[0]['renewalPrice'] = parseFloat(amount);
			}

		if ( custom_fields.length > 0 ) {
			redirect_url = redirect_url + '&custom_fields='+JSON.stringify(custom_fields)
			console.log( redirect_url );
		}

		if ( amount == '' ) {
			alert( "Amount Can not be empty" );
			return;
		}

		TwoCoInlineCart.setup.setMerchant(TWOCO.config.merchant_id); // your Merchant code
		TwoCoInlineCart.setup.setMode('DYNAMIC'); // product type
		TwoCoInlineCart.register();

		// set products
		TwoCoInlineCart.products.addMany(cart);

		// set cart
		TwoCoInlineCart.cart.setTest(TWOCO.config.sandbox);
		TwoCoInlineCart.cart.setReturnMethod({type: 'redirect', url	: redirect_url});
		TwoCoInlineCart.cart.setCurrency(currency);
		// TwoCoInlineCart.cart.setOrderExternalRef(resp.tco.order_id);

		// kick off
		TwoCoInlineCart.cart.checkout();
	});
})