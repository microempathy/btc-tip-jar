jQuery( document ).ready(function( $ ) {

	$( "#Btc_Tip_Jar_dialog" )
	.dialog()
	.dialog( "close" );

	$( "#Btc_Tip_Jar_tip_jar" )
	.button()
	.click(function( event ) {
		event.preventDefault();
		$( "#Btc_Tip_Jar_dialog" ).dialog( "open" );
	});

	var fx_rate_url = 'https://' + Btc_Tip_Jar.fx_rate_url + '?cors=true';

	$.ajax({
		url: fx_rate_url,
		dataType: 'json',
		crossDomain: true,
		jsonpCallback: 'MyJSONPCallback',
		success: function(data){
			Btc_Tip_Jar.fx_rates = data;

			var btc = $( "#Btc_Tip_Jar_tip_jar" ).data( 'btc' );
			var fx  = Btc_Tip_Jar.fx_rates[Btc_Tip_Jar.fx]['15m'];
			var fx_donated = $.getFormattedCurrency(
				btc * fx,
				{
					symbol: Btc_Tip_Jar.fx_rates[Btc_Tip_Jar.fx].symbol,
				}
			);
			var btc_donated = $.getFormattedCurrency(
				btc,
				{
					symbol: "\u0e3f",
					roundToDecimalPlace: Btc_Tip_Jar.decimals,
				}
			);
			if( btc > 0 ) {
				$( "#Btc_Tip_Jar_tip_jar" ).val(
					'Donated: '
					+
					btc_donated
					+
					' (' + fx_donated + ')'
				);
			}
		}
	});
});
