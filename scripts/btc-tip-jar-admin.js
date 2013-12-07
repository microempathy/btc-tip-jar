jQuery( document ).ready(function( $ ) {

    $('span.Btc_Tip_Jar_history_table_amount')
    .formatCurrency({
        symbol: "\u0e3f",
        roundToDecimalPlace: Btc_Tip_Jar_User.decimals,
    });

});
