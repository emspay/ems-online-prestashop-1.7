{extends file=$template}

{block name='content'}
    
<h1>{l s='Your order at %s' sprintf=[$shop.name] mod='emspayafterpay'}</h1>

<div class="error">
    <p><b>{l s='Unfortunately, we can not currently accept your purchase with AfterPay. Please choose another payment option to complete your order. We apologize for the inconvenience.' mod='emspay'}</b></p>
    {if isset($error_message)}
        <p><strong>{$error_message}</strong></p>
    {/if}
    <p><a href="{$checkout_url}">{l s='Please click here to try again.' mod='emspay'}</a></p>
</div>

{/block}