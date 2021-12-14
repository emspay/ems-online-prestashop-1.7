$(document).ready(function () {
    $("#payment-confirmation").find(".btn").click(function () {
        if(!window.ApplePaySession)
        {
            alert(applepay_isnt_available);
            return false;
        }
        return true;
    });
});