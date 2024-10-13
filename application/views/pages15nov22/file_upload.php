<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href=<?= base_url("assets/css/ring.css?v=2") ?>>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src=<?= base_url("assets/js/jquery.redirect.js") ?>></script>
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
          <form id="fileUploadForm"  method="post" enctype="multipart/form-data">
            <div class="row p-5">
                <div class="col-md-12 mt-3">
                    <div>
                        <label for="formFileLg" class="col-form-label">Upload File</label>
                        <input class="form-control form-control-lg" id="zip_file" name="zip_file" type="file" accept=".zip,.rar,.7zip">
                      </div>
                </div>
            </div>
            <input type="hidden" id="userId" name="userId" value="<?= $userId ?>"/>
           <div class="card-footer p-3 px-5 text-end">
            <button type="button" class="btn btn-outline-secondary btn-lg px-5 rounded-pill ">Cancel</button>
            <input type="submit" class="btn btn-success btn-lg px-5 rounded-pill" value="Send">
           </div>
        </form>
       </div>
    </div>
    <div id="overlay" style="display: none;">
        <div id="load"><img src="https://i.gifer.com/origin/b4/b4d657e7ef262b88eb5f7ac021edda87.gif" style="width: 100px;"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <script>
    $(function () {
        $('#fileUploadForm').on('submit', function (e) {
          e.preventDefault();
          var formData = new FormData($(this)[0]);
          var BaseUrl = "<?php echo base_url() ?>";
          $.ajax({
            type: 'post',
            url: BaseUrl+'index.php/ringWeb/home/validateZipFile',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                            $("#overlay").show();
                        },
            success: function(response){
            const obj = JSON.parse(response);
            if(obj.response_code == 1){
              $("#overlay").hide();
              Swal.fire({
                text: "Zip file extracted successfully",
                icon: 'success',
                // showCancelButton: true,
                confirmButtonColor: '#006056',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ok'
              }).then((result) => {
                if (result.isConfirmed) {
                  $.redirect(BaseUrl+'index.php/ringWeb/home/dashboard', {'reportData': obj.ReportData});
                }
              });
            }else{
              Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong!',
              });
              $("#overlay").hide();
            }
              
            }
          });

        });

      });
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </body>
</html>