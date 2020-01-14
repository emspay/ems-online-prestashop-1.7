<style>
    a.emspayklarnapaylater::after {
        display: block;
        content: "\f054";
        position: absolute;
        right: 15px;
        margin-top: -11px;
        top: 50%;
        font-family: "FontAwesome";
        font-size: 25px;
        height: 22px;
        width: 14px;
        color: #777;
    }
    a.emspayklarnapaylater {
        background: url('https://cdn.klarna.com/1.0/shared/image/generic/logo/nl_nl/basic/blue-black.png?width=85') 10px 30px no-repeat;
    }
</style>

<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="emspayklarnapaylater" href="{$link->getModuleLink('emspayklarnapaylaterpaylater', 'payment')|escape:'html'}"
               title="{l s='Pay by Klarna Pay Later' mod='emspayklarnapaylaterpaylater'}">
                {l s='Pay by Klarna Pay Later' mod='emspayklarnapaylaterpaylater'}
            </a>
        </p>
    </div>
</div>
