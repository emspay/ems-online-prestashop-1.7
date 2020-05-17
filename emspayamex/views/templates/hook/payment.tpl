<style>
a.emspayamex::after {
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
a.emspayamex{
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

a.emspayamex {
      background: url({$base_dir}modules/emspayamex/logo.png) 15px 12px no-repeat
}
</style>
<div class="row">
      <div class="col-xs-12">
            <p class="payment_module">
                  <a class="emspayamex" href="{$link->getModuleLink('emspayamex', 'payment')|escape:'html'}" title="{l s='Pay by American Express' mod='emspayamex'}">
                        <span class="applelogo"><img src={$base_dir}modules/emspayamex/logo_bestelling.png></span>
                        <span class="appletitle">{l s='Pay by Apple Pay' mod='emspayamex'}<span>
                  </a>
            </p>
      </div>
</div>