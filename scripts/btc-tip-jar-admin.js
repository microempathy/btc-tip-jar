jQuery( document ).ready(function( $ ) {

    $('span.Btc_Tip_Jar_User_History_Table_amount')
    .formatCurrency({
        symbol: "\u0e3f",
        roundToDecimalPlace: Btc_Tip_Jar_User.decimals,
    });

});
