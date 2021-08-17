var sendingBeyondPayCapturePayment = false;
function beyondPayCapturePayment(form_id, entry_id) {
  if(sendingBeyondPayCapturePayment){
    return;
  }
  sendingBeyondPayCapturePayment = true;
  jQuery.post('admin-ajax.php',{
      entry_id,
      form_id,
      action: 'beyond_pay_capture'
    },(respRaw) => {
      sendingBeyondPayCapturePayment = false;
      var resp = JSON.parse(respRaw);
      if(resp.success){
        document.location.reload();
      }else{
        alert('Error capturing transaction: ' + resp.error);
      }
    });
}