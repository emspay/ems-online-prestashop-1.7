<div class="row">
      <div class="col-xs-12">
            <div class="payment_module">
                  <div class='emspayapplepay'>
                        <form id="emspayapplepay_form" name="emspayapplepay_form" action="{$link->getModuleLink('emspayapplepay', 'payment')|escape:'html'}" method="post">
                              <p id="ginger_notification"></p>
                        </form>
                  </div>
            </div>
      </div>
</div>
<script type="text/javascript">

      var applepay_isnt_available = "{l s='Apple Pay is not available for your device' mod='emspayapplepay' js=1}";

      if(!window.ApplePaySession)
      {
            document.getElementById('ginger_notification').textContent = applepay_isnt_available
      }

</script>
