jQuery( document ).ready(function( $ ) {

	$( "#btc-tip-jar_dialog" )
	.dialog()
	.dialog( "close" );

	$( "#btc-tip-jar_tip-jar" )
	.button()
	.click(function( event ) {
		event.preventDefault();
		$( "#btc-tip-jar_dialog" ).dialog( "open" );
	});

});
