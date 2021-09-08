{extends file=$template}

{block name='content'}
    <h1>
        {l s='Your order at %s' sprintf=[$shop.name] mod='emspay'}
    </h1>
    <h3>
        {l s='Please wait while your order status is being checked...' mod='emspay'}
    </h3>

    <div><img src="{$modules_dir}ginger/assets/ajax-loader.gif"/></div>

    <script language="JavaScript">
        {literal}
            var fallback_url = '{/literal}{$fallback_url}{literal}';
            var validation_url = '{/literal}{$validation_url}{literal}';
        {/literal}
    </script>
    <script type="text/javascript" src="{$modules_dir}ginger/processing.js"></script>
{/block}