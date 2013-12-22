jQuery( document ).ready(function( $ ) {

	get_fx_rates();

});

function get_fx_rates() {

	jQuery.ajax({
		url: btc_tip_jar_fx.url,
		dataType: 'json',
		crossDomain: true,
		jsonpCallback: 'MyJSONPCallback',
		success: function(data){
			btc_tip_jar_fx.fx_rates = data;

			jQuery('.btc-tip-jar_fx-format').each(function(i, o) {
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

	var fx  = btc_tip_jar_fx.fx_rates[btc_tip_jar_fx.fx]['15m'];

	var fx_amount = jQuery.getFormattedCurrency(
		btc * fx,
		{
			symbol: btc_tip_jar_fx.fx_rates[btc_tip_jar_fx.fx].symbol,
		}
	);

	var btc_amount = jQuery.getFormattedCurrency(
		btc,
		{
			symbol: "\u0e3f",
			roundToDecimalPlace: btc_tip_jar.decimals,
		}
	);

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
