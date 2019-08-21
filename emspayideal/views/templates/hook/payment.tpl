<div class="row">
    <div class="col-xs-12">
        <div class="payment_module">
            <div class='emspayideal'>
                <form id="emspayideal_form" name="emspayideal_form" action="{$link->getModuleLink('emspayideal', 'payment')|escape:'html'}" method="post">
                    {l s='Pay by iDEAL' mod='emspayideal'}<br />
                    {l s='Choose your bank' mod='emspayideal'}
                    &nbsp;&nbsp;
                    <select name="issuerid" id="issuerid">
                        <option value="">{l s='Choose your bank' mod='emspayideal'}</option>

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
    var mess_emspay__error = "{l s='Choose your bank' mod='emspayideal' js=1}";
</script>