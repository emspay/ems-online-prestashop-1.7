<style>
a.emspayapplepay::after {
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
<<<<<<< HEAD
a.emspayapplepay{
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
=======
a.emspayapplepay {
      background: url({$base_dir}modules/emspayapplepay/logo.png) 15px 12px no-repeat
>>>>>>> b27fb4a2e2892dca304dbc13eb37c5758d5a69be
}
</style>
<div class="row">
      <div class="col-xs-12">
            <p class="payment_module">
                  <a class="emspayapplepay" href="{$link->getModuleLink('emspayapplepay', 'payment')|escape:'html'}" title="{l s='Pay by Apple Pay' mod='emspayapplepay'}">
                        <span class="applelogo"><img src={$base_dir}modules/emspayapplepay/logo_bestelling.png></span>
                        <span class="appletitle">{l s='Pay by Apple Pay' mod='emspayapplepay'}<span>
                  </a>
            </p>
      </div>
</div>