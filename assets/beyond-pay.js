/* global beyond_pay_strings, tokenpay */

/**
 * Initializes and attaches the TokenPay iframe.
 * @returns {undefined}
 */
function attachBeyondPay(){
  if(!document.getElementById('card')){
    return;
  }
  if (typeof(tokenpay) === 'undefined') {
    tokenpay = TokenPay(beyond_pay_strings.public_key, beyond_pay_strings.is_test_mode === 'yes');
  }
  tokenpay.initialize({
    dataElement: '#card', 
    errorElement: '#errorMessage', 
    useStyles: false
  });
  jQuery(function() {
    var token_input = document.getElementById('beyond_pay_token');
    if(!token_input.form){ // admin
      return;
    }
    var isSubmitting = false;
    var isTokenUsed = false;
    var formWrapper = jQuery(token_input.form).closest( '.gform_wrapper' );
	var formID = formWrapper.attr( 'id' ).split( '_' )[ 2 ];
    token_input.form.addEventListener(
      'submit',
      function(e){
        if(isSubmitting){
          e.preventDefault();
          return;
        }
        if(isTokenUsed){
          token_input.value = "";
        }
        if(token_input.value){
          isTokenUsed = true;
        } else {
            isSubmitting = true;
            tokenpay.createToken(
              function(res){
                isSubmitting = false;
                isTokenUsed = false;
                token_input.value = res.token;
                token_input.form.submit();
              },
              function(err){
                window["gf_submitting_"+formID] = false;
                formWrapper.find( '.gform_ajax_spinner' ).remove();
                isSubmitting = false;
                isTokenUsed = false;
                token_input.value = "";
              }
            );
            e.preventDefault();
        }
      }
    );
  });
}

