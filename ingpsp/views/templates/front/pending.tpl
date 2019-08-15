{extends file=$template}

{block name='content'}
    <h1>
        {l s='Your order at %s' sprintf=[$shop.name] mod='ingpsp'}
    </h1>
    <h3>
        {l s='We did not receive a confirmation from your bank or card issuer.' mod='ingpsp'}
    </h3>
    <p>
        {l s='You will receive a message as soon as we have received this.' mod='ingpsp'}
    </p>
    <a href="{$checkout_url}" title="{l s='Please click here to try again.' mod='ingpsp'}" class="button-exclusive btn btn-default">
        <i class="icon-chevron-left"></i>
        {l s='Please click here if you wish to try again' mod='ingpsp'}
    </a>
{/block}