$(document).ready(function () {
    $("#payment-confirmation").find(".btn").click(function () {
        var parent = $('#ingpspideal_form').parent().parent().parent().parent().parent();
        if (parent.css('display') == 'block'
                && $('#issuerid').val() == '')
        {
            alert(mess_ingpsp__error);
            return false
        }
        return true;
    });
});