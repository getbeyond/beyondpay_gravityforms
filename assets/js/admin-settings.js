function onBeyondPayTestModeChanged(input) {
  var isTestMode = input.checked;
  if(isTestMode){
    document.querySelectorAll('.beyond_pay_setting_test_only').forEach(
      function (i) {i.parentNode.parentNode.classList.remove('hidden');}
    );
    document.querySelectorAll('.beyond_pay_setting_live_only').forEach(
      function (i) {i.parentNode.parentNode.classList.add('hidden');}
    );
  }else{
    document.querySelectorAll('.beyond_pay_setting_live_only').forEach(
      function (i) {i.parentNode.parentNode.classList.remove('hidden');}
    );
    document.querySelectorAll('.beyond_pay_setting_test_only').forEach(
      function (i) {i.parentNode.parentNode.classList.add('hidden');}
    );
  }
}
function onBeyondPayStylingChanged(input) {
  var isCustomStyling = input.checked;
  if(isCustomStyling){
    document.querySelector('.beyond_pay_styling').parentNode.parentNode.classList.remove('hidden');
  }else{
    document.querySelector('.beyond_pay_styling').parentNode.parentNode.classList.add('hidden');
  }
}

window.addEventListener('load', () => {
    onBeyondPayTestModeChanged(document.querySelector('.enable-test-mode'));
    onBeyondPayStylingChanged(document.querySelector('#use-custom-styling'));
});