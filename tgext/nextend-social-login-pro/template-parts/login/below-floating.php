<script type="text/javascript">
  window._nsl.push(function ($) {
      $(document).ready(function () {
          var $form = $('<form class="nsl-floating-form"></form>').insertAfter('#loginform,#registerform,#front-login-form,#setupform'),
              $main = $('#nsl-custom-login-form-main');

          if ($form.parent().hasClass('tml')) {
              $form = $form.parent();
          }

          $main.find('.nsl-container')
              .addClass('nsl-container-login-layout-below-floating')
              .css('display', 'block');

          $main.appendTo($form);
      });
  });
</script>
<style type="text/css">
    #nsl-custom-login-form-main .nsl-container {
        display: none;
    }

    form.nsl-floating-form {
        padding: 26px 24px;
    }
</style>
<noscript>
    <style>
        #nsl-custom-login-form-main .nsl-container {
            display: block;
        }
    </style>
</noscript>