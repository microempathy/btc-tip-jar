jQuery( document ).ready(function( $ ) {

    $('span.Btc_Tip_Jar_history_table_amount')
    .formatCurrency({
        symbol: "\u0e3f",
        roundToDecimalPlace: Btc_Tip_Jar.decimals,
    });

    $('span#Btc_Tip_Jar_balance_amount')
    .formatCurrency({
        symbol: "\u0e3f",
        roundToDecimalPlace: Btc_Tip_Jar.decimals,
    });

});
