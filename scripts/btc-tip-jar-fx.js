jQuery( document ).ready(function( $ ) {

	get_fx_rates();

});

function get_fx_rates() {

	jQuery.ajax({
		url: Btc_Tip_Jar_Fx.url,
		dataType: 'json',
		crossDomain: true,
		jsonpCallback: 'MyJSONPCallback',
		success: function(data){
			Btc_Tip_Jar_Fx.fx_rates = data;

			jQuery('.Btc_Tip_Jar_Fx_format').each(function(i, o) {
				bitcoin_format(o);
			});

		}
	});

}

function bitcoin_format(o) {

	var o = jQuery(o);

	var btc = 0.0;
	if( o.data('btc') ) {
		btc = o.data('btc');
	} else {
		btc = o.text();
	}

	var fx  = Btc_Tip_Jar_Fx.fx_rates[Btc_Tip_Jar_Fx.fx]['15m'];

	var fx_amount = jQuery.getFormattedCurrency(
		btc * fx,
		{
			symbol: Btc_Tip_Jar_Fx.fx_rates[Btc_Tip_Jar_Fx.fx].symbol,
		}
	);

	var btc_amount = jQuery.getFormattedCurrency(
		btc,
		{
			symbol: "\u0e3f",
			roundToDecimalPlace: Btc_Tip_Jar.decimals,
		}
	);

	if( btc > 0 ) {
		var amount =
			btc_amount
			+
			' (' + fx_amount + ')';

		if( o.data('btc') ) {
			o.val(amount);
		} else {
			o.text(amount);
		}
	}

}
