<h1>{l s='Your order at %s' sprintf=[$shop.name] mod='emspayklarnadirectdebit'}</h1>

<h3>{l s='Klarna Direct Debit Payment Success' mod='emspayklarnadirectdebit'}</h3>

<p>
    {l s='Your order is complete.' mod='emspayklarnadirectdebit'}
    <br/><br/>
    <b>{l s='You have chosen the Klarna Direct Debit payment method.' mod='emspayklarnadirectdebit'}</b>
    <br/><br/>
    {l s='For any questions or for further information, please contact our' mod='emspayklarnadirectdebit'}
    <a href="{$link->getPageLink('contact-form', true)|escape:'html'}">{l s='customer support' mod='emspayklarnadirectdebit'}</a>.
</p>