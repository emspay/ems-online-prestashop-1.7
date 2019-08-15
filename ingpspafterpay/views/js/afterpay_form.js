$(document).ready(function () {
    $("#payment-confirmation").find(".btn").click(function () {
        var parent = $('#ingpspafterpay_form').parent().parent().parent().parent().parent();
        if (parent.css('display') == 'block'
                && $('#ingpspafterpay_terms_conditions').is(':checked') === false)
        {
            alert(message_ingpspafterpay_error);
            return false
        }
        return true;
    });
});