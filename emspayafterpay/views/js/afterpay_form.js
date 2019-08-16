$(document).ready(function () {
    $("#payment-confirmation").find(".btn").click(function () {
        var parent = $('#emspayafterpay_form').parent().parent().parent().parent().parent();
        if (parent.css('display') == 'block'
                && $('#emspayafterpay_terms_conditions').is(':checked') === false)
        {
            alert(message_emspayafterpay_error);
            return false
        }
        return true;
    });
});