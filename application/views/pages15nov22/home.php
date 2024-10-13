<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href=<?= base_url("assets/css/ring.css?n=123") ?>>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src=<?= base_url("assets/js/jquery.redirect.js") ?>></script>
    <!-- <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script> -->
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Ring</title>
  </head>
  <style>
    .swal2-confirm{
        background-color: #006056!important;
    }

       #overlay {
  position: fixed;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0,0,0,0.3);
  z-index: 2;
  cursor: pointer;

}

#load{
  position: absolute;
  top: 50%;
  left: 50%;
  font-size: 50px;
  color: white;
  transform: translate(-50%,-50%);
  -ms-transform: translate(-50%,-50%);
  z-index:222;
  box-shadow: rgba(149, 157, 165, 0.6) 0px 8px 24px;
 border-radius: 50%;
 width: 150px;
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
}
  </style>
  <body >

<div style="height:200px;position: fixed;"></div>
    <div class="centerDiv">
          <div class="card" style="width:800px;">
          <form id="formone">
            <div class="row p-5">
                <div class="col-md-3">
                    <label class="col-form-label">Mobile Code</label>
                    <select name="mobCode" id="mobCode" class="form-select form-select-lg" aria-label="Default select example">
                        <option value="+60">+60</option>
                        <option value="+61">+61</option>
                        <option value="+91">+91</option>
                      </select>
                </div>
                <div class="col-md-6">
                    <label class="col-form-label">Mobile No</label>
                    <input id="mobileNumber" name="mobileNumber" class="form-control form-control-lg" type="text" placeholder="Please enter mobile no." required>
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="button" id="getotp"  onclick="getotpfunc()" class="btn btn-success btn-lg w-100 rounded-pill" >Get OTP</button>
                </div>
            </form>
            <form id="formtwo">
                <div id="otpText" class="col-md-12 mt-3" style="display:none">
                    <label class="col-form-label">OTP</label>
                    <input name="otp" id="otp" class="form-control form-control-lg" type="text" placeholder="Please enter OTP">
                </div>
            </div>
            
           <div id="otpbtn" class="card-footer p-3 px-5 text-end"  style="display:none">
            <button type="button"  id="validateOtp"  onclick="validateOtpfunc()" class="btn btn-success btn-lg px-5 rounded-pill">Validate</button>
           </div>
         </form>
       </div>
    </div>
    <div id="overlay" style="display: none;">
        <div id="load"><img src="https://i.gifer.com/origin/b4/b4d657e7ef262b88eb5f7ac021edda87.gif" style="width: 100px;"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script>
       $(document).ready(function(){
        
            var formone = $("#formone");  //form id
            formone.validate({
                rules:
                {
                    mobileNumber:{ required : true, number : true,}		
                },
                messages:
                {
                    mobileNumber :{ required : "This field is required",number : "Please enter numbers only"}						
                }		
            });
            var formtwo = $("#formtwo");  //form id
            formtwo.validate({
                rules:
                {
                    otp:{ required : true,}		
                },
                messages:
                {
                    otp :{ required : "This field is required",}						
                }		
            });
            
        }); 

        function getotpfunc(){
            var BaseUrl = "<?php echo base_url() ?>";
            var IsValid = $("#formone").valid();
            if(IsValid){
                var mobileNumber = $("#mobileNumber").val(); 
                var code = $("#mobCode").val();
                $.ajax({
                        type: "POST",
                        url: BaseUrl+"index.php/ringWeb/home/sendOtpMail",
                        data: {"code": code,"mobile": mobileNumber},
                        beforeSend: function() {
                            $("#overlay").show();
                        },
                        success: function(response){
                            const obj = JSON.parse(response);
                            if(obj.response_code == 1){
                                var otp = obj.otp;
                                $("#otpText").css("display", "block");
                                $("#otpbtn").css("display", "block");
                                $("#getotp").text("Re-send");
                                Swal.fire({
                                    icon: 'success',
                                    title: 'OTP has been sent successfully',
                                    confirmButtonColor: '#3085d6',
                                });
                                 $("#overlay").hide();
                            }else if(obj.response_code == 2){
                                Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'The mobile number given by you was not found.',
                            });
                            $("#overlay").hide();
                            }
                            

                        },
                        error: function(e){}
                });
            }
        }

        function validateOtpfunc(){
            var BaseUrl = "<?php echo base_url() ?>";
            var IsValid = $("#formtwo").valid();
            if(IsValid){
                var otp = $("#otp").val(); 
                $.ajax({
                        type: "POST",
                        url: BaseUrl+"index.php/ringWeb/home/validateOtp",
                        data: {"otp": otp},
                        beforeSend: function() {
                            $("#overlay").show();
                        },
                        success: function(response){
                            const obj = JSON.parse(response);
                            // console.log(obj);
                            if(obj.response_code == 1){
                                $("#overlay").hide();
                                $.redirect(BaseUrl+'index.php/ringWeb/home/fileUploadPage', {'userdata': obj.data}); 
                            }else{
                                Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Wrong OTP',
                            });
                            $("#overlay").hide();
                            }

                        },
                        error: function(e){
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Either mobile number is wrong or OTP.',
                            });
                            $("#overlay").hide();
                        }
                });
            }
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </body>
</html>