<style>
a.emspaygooglepay::after {
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
a.emspaygooglepay{
      padding-left: 0px !important;
}
span.googlelogo{
      margin-left: 15px;
}
span.googlelogo img{
      width: 64px;
      height: auto;
}
span.googletitle{
      padding-left: 20px;

a.emspaygooglepay {
      background: url({$base_dir}modules/emspaygooglepay/logo.png) 15px 12px no-repeat
}
</style>
<div class="row">
      <div class="col-xs-12">
            <p class="payment_module">
                  <a class="emspaygooglepay" href="{$link->getModuleLink('emspaygooglepay', 'payment')|escape:'html'}" title="{l s='Pay by google Pay' mod='emspaygooglepay'}">
                        <span class="googlelogo"><img src={$base_dir}modules/emspaygooglepay/logo_bestelling.png></span>
                        <span class="googletitle">{l s='Pay by google Pay' mod='emspaygooglepay'}<span>
                  </a>
            </p>
      </div>
</div>