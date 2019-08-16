<div class="row">
    <div class="col-xs-12">
        <div class="payment_module">
            <div class='emspayafterpay'>
                <form id="emspayafterpay_form" name="emspayafterpay_form" action="{$link->getModuleLink('emspayafterpay', 'payment')|escape:'html'}" method="post">
                    <p>
                        <input type="checkbox" name="emspayafterpay_terms_conditions" id="emspayafterpay_terms_conditions" />
                        {l s='I accept AfterPay' mod='emspayafterpay'}
                        <span>
                            <a href="{$terms_and_condition_url}" target="_blank"> 
                                {l s='Terms & Conditions' mod='emspayafterpay'}
                            </a> 
                        </span>
                    </p>
                </form> 
            </div> 
        </div>
    </div>
</div>   
<script type="text/javascript">
    var message_emspayafterpay_error = "{l s='Please accept Afterpay Terms & Conditions' mod='emspayafterpay' js=1}";
</script>
