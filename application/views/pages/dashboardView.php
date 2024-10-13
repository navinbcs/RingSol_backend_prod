<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href=<?= base_url("assets/css/ring.css") ?>>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src=<?= base_url("assets/js/jquery.redirect.js") ?>></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Ring</title>
  </head>
<style>
     tbody {
        display:block;
        max-height:70vh;
        overflow-x: hidden;
        overflow-y: auto;
    }
    thead, tbody tr {
        display:table;
        width:100%;
        table-layout:fixed;
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

  .header {
  overflow: hidden;
  background-color: #006056;
  padding: 10px 5px;
}

.header a {
  float: left;
  color: white;
  text-align: center;
  padding: 12px;
  text-decoration: none;
  font-size: 18px; 
  line-height: 25px;
  border-radius: 4px;
}

.header a.logo {
  font-size: 25px;
  font-weight: bold;
}

.header a:hover {
  background-color: #ddd;
  color: black;
}

.header a.active {
  background-color: #24a0ed;
  color: white;
}

.header-right {
  float: right;
}

@media screen and (max-width: 500px) {
  .header a {
    float: none;
    display: block;
    text-align: left;
  }
  
  .header-right {
    float: none;
  }
}
    </style>
  <body >
  <div class="header">
    <img src="<?= base_url("assets/logo/ring_logo.png") ?>" alt="Ring" width="150" height="50">
    <div class="header-right">
      <!-- <a class="active" href="#home">Home</a> -->
      <a class="" href="<?= base_url("index.php/ringWeb/home/fileUploadPage") ?>">Back</a>
      <a class="" href="<?= base_url("index.php/ringWeb/home/logout") ?>">Logout</a>
    </div>
  </div>
    <div style="height:200px;position: fixed;"></div>
          <div class="card" style="width:800px;margin: auto;">
            <table class="table m-0">
                <thead class="table-black">
                 <tr>
                  <!-- <form method="GET">   -->
                    <th><h4>Upload Details</h4></th>
                    <th>
                      
                    <div class="form-group">
                      <select class="form-select ms-auto auto_submit_item" aria-label="Default select example"  name="cat" id="cat" style="width: 300px!important;">
                        <option value="Clinical Report">Clinical Report</option>
                        <option value="Consent and Authorization Forms">Consent and Authorization Forms</option>
                        <option value="Financial Information">Financial Information</option>
                        <option value="Lab Report">Lab Report</option>
                        <option value="Orders">Orders</option>
                        <option value="Patient Demographics">Patient Demographics</option>
                        <option value="Prescription">Prescription</option>
                        <option value="Progrsss Notes">Progrsss Notes</option>
                        <option value="Radio Report">Radio Report</option>
                        <option value="Treatment History">Treatment History</option>
                        <option value="Others">Others</option>
                      </select>
                    </div>
                    </th><th>
                    <!-- <?php if(isset($_GET['type'])){ ?> -->
                        <!-- <div class="form-group">
                        <select class="form-select ms-auto auto_submit_item" aria-label="Default select example"  name="type" id="type" style="width: 130px!important;">
                          <option value="1" <?php if ($_GET['type'] == 1) { echo ' selected="selected"'; } ?>>All</option>
                          <option value="2" <?php if ($_GET['type'] == 2) { echo ' selected="selected"'; } ?>>Online</option>
                          <option value="3" <?php if ($_GET['type'] == 3) { echo ' selected="selected"'; } ?>>Self Uploaded</option>
                        </select>
                      </div> -->
                    <!-- <?php }else{ ?> -->
                    <div class="form-group">
                      <select class="form-select ms-auto auto_submit_item" aria-label="Default select example"  name="type" id="type" style="width: 130px!important;">
                        <option value="1">All</option>
                        <option value="2">Online</option>
                        <option value="3">Self Uploaded</option>
                      </select>
                    </div>
                    <!-- <?php } ?> -->
                    </th>
                    <!-- </form> -->
                  </tr>
                </thead>
                <tbody>
                    <?php if(isset($reportData) && !empty($reportData)){
                        foreach ($reportData as $reportVal) {
                            // print_r($reportVal);exit;
                            // if(isset($_GET["type"]) && !empty($_GET["type"])){
                            //   if($_GET["type"] != $val->type)  continue;
                            // }
                            
                             ?>
                            <tr>
                                <td class="w-200p middle"><div class="fileicon"><svg  xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
                                  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                                  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                                </svg></div></td>
                                <td>
                                  <!-- date("Y-m-d", strtotime($reportVal['InsertDate'])) -->
                                  <!-- <div class = 'reportDiv_<?=$fileData['Category'];?>'> -->
                                    <h5><?= isset($reportVal['TenantName'])?$reportVal['TenantName']:""; ?></h5>
                                    <p><?= isset($reportVal['DoctorName'])?$reportVal['DoctorName']:""; ?></p>
                                    <p><?= isset($reportVal['Diagnosis'])?$reportVal['Diagnosis']:""; ?></p>
                                    <!-- <p><?= isset($reportVal['description'])?$reportVal['description']:""; ?></p> -->
                                    <?php if(isset($reportVal['InsertDate'])){
                                        $InsertDate = date("Y-m-d", strtotime($reportVal['InsertDate']));
                                      }else{
                                        $InsertDate = "";
                                      } 
                                    ?>
                                    <p><?= $InsertDate; ?></p>
                            <?php if(isset($reportVal['FileArray']) && !empty($reportVal['FileArray'])){
                              // echo "<pre>"; print_r($reportVal['FileArray']);exit;
                                foreach ($reportVal['FileArray'] as $fileData) {
                                  ?>                                    
                                    <button type="button" class="btn-sm btn-success reportDiv_<?=$fileData['Category'];?>"  btnId = "<?php echo $fileData['Base64Data']; ?>" onclick="show(<?= $reportVal['reportNo']; ?>)" id="btnID_<?= $reportVal['reportNo']; ?>"><?= isset($fileData['Category'])?$fileData['Category']:""; ?></button>
                            <?php  } }else{ ?>                    
                              <button type="button" class="btn-sm btn-success reportDiv_<?=$reportVal['Category'];?>" btnId = "<?php echo $reportVal['Data2']; ?>" onclick="showLocal(<?= $reportVal['reportNo']; ?>)" id="btnID_<?= $reportVal['reportNo']; ?>"><?= isset($reportVal['Category'])?$reportVal['Category']:""; ?></button>
                            <?php } ?>
                                <!-- </div> -->
                                </td>
                            </tr>
                    <?php  }  } ?>
                </tbody>
              </table>         
       </div>
    </div>

<div id="overlay" style="display: none;">
        <div id="load"><img src="https://i.gifer.com/origin/b4/b4d657e7ef262b88eb5f7ac021edda87.gif" style="width: 100px;"></div>
    </div>
   
<iframe id="upload_target"><html><body></body></html></iframe>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    
    <script>
        function show(no){
            var d = $("#btnID_"+no).attr('btnId'); 
            // alert(d)
            var BaseUrl = "<?php echo base_url() ?>";
            $.ajax({
              type: "POST",
              url: BaseUrl+"index.php/ringWeb/home/getImgUrlFromBase64Copy",
              data: {"baseImage": d},
              beforeSend: function() {
                  $("#overlay").show();
              },
              success: function(response){
                  const obj = JSON.parse(response);
                  // console.log(obj);
                  if(obj.response_code == 1){
                    if(obj.file_ext == 'pdf'){
                      $("#overlay").hide();
                      window.open(obj.file_link,'_blank');
                    }else{
                        $("#overlay").hide();
                        console.log(obj);
                        img = '<img src="';
                        img += obj.file_link
                        img += '" style="left: 34%;position: absolute;">';
                        popup = window.open();
                        popup.document.write(img); 
                    }                   
                  }else{
                      Swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'Something Wrong',
                  });
                  $("#overlay").hide();
                  }

              },
              error: function(e){
                  Swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'Image not found.',
                  });
                  $("#overlay").hide();
              }
            });
            
            
        }

        function showLocal(no){
            var d = $("#btnID_"+no).attr('btnId'); 
            // alert(d)
            var BaseUrl = "<?php echo base_url() ?>";
            $.ajax({
              type: "POST",
              url: BaseUrl+"index.php/ringWeb/home/getImgUrlFromBase64ForLocal",
              data: {"baseImage": d},
              beforeSend: function() {
                  $("#overlay").show();
              },
              success: function(response){
                  const obj = JSON.parse(response);
                  // console.log(obj);
                  if(obj.response_code == 1){
                    if(obj.file_ext == 'pdf'){
                      $("#overlay").hide();
                      window.open(obj.file_link,'_blank');
                    }else{
                      $("#overlay").hide();
                      console.log(obj);
                      img = '<img src="';
                      img += obj.file_link
                      img += '" style="left: 34%;position: absolute;">';
                      popup = window.open();
                      popup.document.write(img); 
                    }
                  }else{
                      Swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'Something Wrong',
                  });
                  $("#overlay").hide();
                  }

              },
              error: function(e){
                  Swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'Image not found.',
                  });
                  $("#overlay").hide();
              }
            });
            
            
        }
        $(document).ready( function () {
          $(".auto_submit_item").change(function() {
            var Cat = this.value;
            if($(".reportDiv_"+Cat).html() != Cat){
              $(".reportDiv_"+Cat).hide();
            // }else{
            //   $(".reportDiv").hide();
            }
          });
        });

        // $(window).on('beforeunload', function(){
        //    window.location.href = "https://win.k2key.in/Ring_dev/index.php/ringWeb/home/logoutindex.php/ringWeb/home/logout";

        // });
    </script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </body>
</html>