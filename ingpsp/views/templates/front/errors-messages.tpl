<h1>{l s='Your order at %s' sprintf=[$shop.name] mod='ingpsp'}</h1>

<h1>{l s='Unexpected payment error' mod='ingpsp'}</h1>

<div class="error">
    <p><b>{l s='Unfortunately there was a problem processing your payment.' mod='ingpsp'}</b></p>
    <p><a href="{$checkout_url}">{l s='Please click here to try again.' mod='ingpsp'}</a></p>
</div>
