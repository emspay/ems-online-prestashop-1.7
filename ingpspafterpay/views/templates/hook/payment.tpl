<div class="row">
    <div class="col-xs-12">
        <div class="payment_module">
            <div class='ingpspafterpay'>
                <form id="ingpspafterpay_form" name="ingpspafterpay_form" action="{$link->getModuleLink('ingpspafterpay', 'payment')|escape:'html'}" method="post"> 
                    <p>
                        <input type="checkbox" name="ingpspafterpay_terms_conditions" id="ingpspafterpay_terms_conditions" />  
                        {l s='I accept AfterPay' mod='ingpspafterpay'} 
                        <span>
                            <a href="{$terms_and_condition_url}" target="_blank"> 
                                {l s='Terms & Conditions' mod='ingpspafterpay'} 
                            </a> 
                        </span>
                    </p>
                </form> 
            </div> 
        </div>
    </div>
</div>   
<script type="text/javascript">
    var message_ingpspafterpay_error = "{l s='Please accept Afterpay Terms & Conditions' mod='ingpspafterpay' js=1}";
</script>
