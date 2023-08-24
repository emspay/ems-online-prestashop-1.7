<style>
a.emspayviacash::after {
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
a.emspayviacash{
      padding-left: 0px !important;
}
span.applelogo{
      margin-left: 15px;
}
span.applelogo img{
      width: 64px;
      height: auto;
}
span.appletitle{
      padding-left: 20px;

a.emspayviacash {
      background: url({$base_dir}modules/emspayviacash/logo.png) 15px 12px no-repeat
}
</style>
<div class="row">
      <div class="col-xs-12">
            <p class="payment_module">
                  <a class="emspayviacash" href="{$link->getModuleLink('emspayviacash', 'payment')|escape:'html'}" title="{l s='Pay by American Express' mod='emspayviacash'}">
                        <span class="applelogo"><img src={$base_dir}modules/emspayviacash/logo_bestelling.png></span>
                        <span class="appletitle">{l s='Pay by Apple Pay' mod='emspayviacash'}<span>
                  </a>
            </p>
      </div>
</div>