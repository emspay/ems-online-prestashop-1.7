<div class="row">
    <div class="col-xs-12">
        <div class="payment_module">
            <div class='ingpspideal'>
                <form id="ingpspideal_form" name="ingpspideal_form" action="{$link->getModuleLink('ingpspideal', 'payment')|escape:'html'}" method="post"> 
                    {l s='Pay by iDEAL' mod='ingpspideal'}<br />
                    {l s='Choose your bank' mod='ingpspideal'}
                    &nbsp;&nbsp;
                    <select name="issuerid" id="issuerid">
                        <option value="">{l s='Choose your bank' mod='ingpspideal'}</option>

                        {foreach from=$issuers item=issuer}
                            <option value="{$issuer.id}">{$issuer.name}</option>
                        {/foreach}  
                    </select>            
                </form>
            </div> 
        </div>
    </div>
</div>
<script type="text/javascript">
    var mess_ingpsp__error = "{l s='Choose your bank' mod='ingpspideal' js=1}";
</script>