<style>
    a.emspayklarnadirectdebit::after {
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
    a.emspayklarnadirectdebit {
        background: url('https://cdn.klarna.com/1.0/shared/image/generic/logo/nl_nl/basic/blue-black.png?width=85') 10px 30px no-repeat;
    }
</style>

<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="emspayklarnadirectdebit" href="{$link->getModuleLink('emsklarnadirectdebit', 'payment')|escape:'html'}"
               title="{l s='Pay by Klarna Direct Debit' mod='emspayklarnadirectdebit'}">
                {l s='Pay by Klarna Direct Debit' mod='emspayklarnadirectdebit'}
            </a>
        </p>
    </div>
</div>
