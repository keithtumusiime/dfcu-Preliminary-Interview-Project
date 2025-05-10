<?php 
  $site_url = 'http://localhost:8000/dfcu/api'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>DFCU Payment Gateway</title>
  <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
  <link id="Link1" rel="icon" href="favicon.ico" type="image/ico" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body, html { 
      background: #f5f7fa; 
      font-family: FilsonProBook, Lato-Regular, Arial, sans-serif;
      font-size: 15px;
      margin: 0;
      padding: 0;
    }
    .container { max-width: 900px; margin-top: 40px; }
    .card { box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .status { font-weight: bold; }

    .swal-left-align .swal2-html-container { text-align: left; }
    .ribbon-img {
      position: absolute;
      top: 25px;
      left: 0;
      width: 150px; /* Adjust size as needed */
      z-index: 9999;
      transform: rotate(-25deg); /* Optional: for a tilted ribbon look */
    }

    h2 {
        color: #053568;
        font-family: FilsonProMedium, Lato-Bold, Arial, sans-serif;
        font-size: 2.1rem;
        text-align: center;
        text-decoration: none;
        font-weight: 400;
        display: block;
        margin: 0;
        position: relative;
        padding: 20px 0 20px 0;
    }

    .btn-primary {
      background-color: #083563;
      position: relative;
      overflow: hidden;
      text-decoration: none;
  }

    .btn {
      border: none;
      display: inline-block;
      padding: 0 35px;
      color: #f5f5f5;
      font-size: 1rem;
      cursor: pointer;
      border-radius: 4px;
      text-transform: none;
      letter-spacing: 1px;
      font-weight: 400;
      margin: 0 auto;
      line-height: 33px;
      font-family: FilsonProBook, Lato-Bold, Arial, sans-serif;
  }

  </style>
</head>
<body>
<div class="container">
  <img src="logo.png" class="ribbon-img">
  <h2 class="mb-4">DFCU Payment Gateway</h2>

  <!-- Payment Form -->
  <div class="card p-4 mb-4">
    <h5>Initiate Payment</h5>
    <form id="initiate">
      <div class="row">
        <div class="col-md-6 mb-2">
          <label>Payer Account</label>
          <input type="text" name="payer" class="form-control" required maxlength="10">
        </div>
        <div class="col-md-6 mb-2">
          <label>Payee Account</label>
          <input type="text" name="payee" class="form-control" required maxlength="10">
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 mb-2">
          <label>Amount</label>
          <input type="number" name="amount" class="form-control" required min="1">
        </div>
        <div class="col-md-4 mb-2">
          <label>Currency</label>
          <select name="currency" class="form-select">
            <option value="UGX">UGX</option>
            <option value="USD">USD</option>
          </select>
        </div>
        <div class="col-md-4 mb-2">
          <label>Reference</label>
          <input type="text" name="payer_reference" class="form-control">
        </div>
      </div>
      <button class="btn btn-primary mt-2" type="submit">Send Payment</button>
    </form>
  </div>

  <!-- Transactions Status -->
  <div class="card p-4">
    <h5>Payment Status Check</h5>
      <div class="row">
        <div class="col-md-6 mb-2">
          <input type="text" name="tran_ref" class="form-control" placeholder="Transaction Reference">
        </div>
        <div class="col-md-6 mb-2">
          <button class="btn btn-info check text-white">Check</button>
        </div>
      </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
  $('#initiate').on('submit', function(e) {
    e.preventDefault();
    const form = this; 
    const formData = {
      payer: $('input[name="payer"]').val().trim(),
      payee: $('input[name="payee"]').val().trim(),
      amount: parseFloat($('input[name="amount"]').val()),
      currency: $('select[name="currency"]').val(),
      payer_reference: $('input[name="payer_reference"]').val().trim()
    };

    $.ajax({
      url: '<?=$site_url ?>/payment/initiate',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(formData),
      beforeSend:function(){
        Swal.fire({
          title: 'Processing Payment...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
      },
      success: function(res) {
        console.log('Success:', res);
        form.reset();
        Swal.fire({
          icon: 'success',
          html: res.message+'. <br><strong>Transaction Ref: </strong>'+res.transaction_reference,
          customClass: {
              popup: 'swal-left-align'
          }
        });


      },
      error: function(jqXHR, textStatus, errorThrown) {
        let message = 'An error occurred. Please try again.';
        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
          message = jqXHR.responseJSON.message;
        } else {
          try {
            const json = JSON.parse(jqXHR.responseText);
            if (json.message) message = json.message;
          } catch (e) {
            message = jqXHR.responseText || errorThrown || message;
          }
        }
        console.error("Error:", jqXHR);
        Swal.fire({
          icon: 'error',
          title: 'Transaction Failed',
          text: message
        });
      }
    });
  });

  $('.check').on('click',function(e){
    var tran_id = $('input[name="tran_ref"]').val().trim();
    if (tran_id) {
      var url = '<?=$site_url ?>/payment/verify/'+tran_id;
      checkStatus(url);
    }else{
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Transaction Reference Required',
        showConfirmButton: false,
        timer: 1500
      });
    }
  })

  //check transaction
  function checkStatus(url) {
    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (data.status_code == 200) {
          //console.log(data);
          var ref = data.data.transaction_reference;
          var message = `
            <strong>Txn ID:&nbsp;&nbsp;&nbsp;</strong> ${ref}<br>
            <strong>Status:&nbsp;&nbsp;&nbsp;</strong> ${data.status}<br>
            <strong>Amount:&nbsp;&nbsp;&nbsp;</strong> ${data.data.amount} ${data.data.currency}<br>
            <strong>Payer:&nbsp;&nbsp;&nbsp;</strong> ${data.data.payer_account}<br>
            <strong>Payee:&nbsp;&nbsp;&nbsp;</strong> ${data.data.payee_account}<br>
            <strong>Reference:&nbsp;&nbsp;</strong> ${data.data.payer_reference}<br>
            
          `;
          Swal.fire({
            icon: 'info',
            title: 'Transaction Status',
            html: message,
            customClass: {
              popup: 'swal-left-align'
            }
          });
        }else{
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message
          });
        }
      });
  }

</script>
</body>
</html>
