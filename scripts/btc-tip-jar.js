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

});
