<style>
a.emspaytikkiepaymentrequest::after {
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
a.emspaytikkiepaymentrequest {
      background: url({$base_dir}modules/emspaytikkiepaymentrequest/logo_bestelling.png) 15px 12px no-repeat
}
</style>
<div class="row">
      <div class="col-xs-12">
            <p class="payment_module">
                  <a class="emspaytikkiepaymentrequest" href="{$link->getModuleLink('emspaytikkiepaymentrequest', 'payment')|escape:'html'}" title="{l s='Pay by Tikkie Payment Request' mod='emspaytikkiepaymentrequest'}">
                        {l s='Pay by Tikkie Payment Request' mod='emspaytikkiepaymentrequest'}</span>
                  </a>
            </p>
      </div>
</div>