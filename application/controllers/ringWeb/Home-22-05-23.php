<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct()
  {
    parent::__construct();
    $this->load->library('session');
    // check_login_user();
    // $this->load->model('Dashboard_model'); 
  }
	
	public function index()
	{
    check_login_user();
      redirect(base_url().'index.php/ringWeb/home/fileUploadPage');
	}

  public function fileUploadPage()
	{
    
    if(isset($_POST['userdata']['PatientId']) && !empty($_POST['userdata']['PatientId'])){
      $userdata = $_POST['userdata'];
      $data['userId'] = $userdata['PatientId'];

      $this->session->set_userdata(array(
                'PatientId' => $userdata['PatientId'],
                'logged_in' => true
              ));
      $this->load->view('pages/file_upload',$data);
    }else{
      check_login_user();
      // $this->load->view('pages/home');
      // redirect(base_url().'index.php/ringWeb/Login');
      // echo "<pre>"; print_r($_SESSION);exit;
      $data['userId'] = $_SESSION['PatientId'];
      $this->load->view('pages/file_upload',$data);
    }
       
	}

  public function dashboard()
	{
    check_login_user();
    $reportData = $_POST['reportData'][0];
    $blankArr = array();
    if(isset($reportData) && !empty($reportData)){
      $i = 0;
      foreach ($reportData as $reportVal) {
        $i++;
        $reportVal['fileDataArray'] = array();
        $reportVal['reportNo'] = $i;
        if(isset($reportVal['UploadedFileRef'])){
          foreach ($reportVal['UploadedFileRef'] as $val) {
            $jsonFile = "upload/".$val.".json";
            $inp = file_get_contents($jsonFile);
            $tempArray = json_decode($inp,true);
            array_push($reportVal['fileDataArray'],$tempArray);
          }
        }
          array_push($blankArr,$reportVal);
      }
    }
    $data['reportData'] = $blankArr;
    // echo "<pre>"; print_r($data['reportData']);exit;
    $this->load->view('pages/dashboardView',$data);
	}

  public function sendOtpMail()
	{
      $url = "https://win.k2key.in/Ring/index.php/Webservice/GenerateOtp";
      $headers = array(
      "Content-Type:application/json",
    );
      /*******************************************************/
          $code = $_POST['code'];
          $mobile = $_POST['mobile'];
          $data_json = '{
      "mobile_number": "'.$mobile.'",
      "mob_code": "'.$code.'"
    }';
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
          curl_setopt($ch, CURLOPT_TIMEOUT, 0);

          $result = curl_exec($ch);
          curl_close($ch);
          echo $result;
	}

    public function validateOtp(){
        $otp = $_POST['otp'];
        $url = "https://win.k2key.in/Ring/index.php/Webservice/userOTPValidateForWebPage";
        $headers = array(
				"Content-Type:application/json",
			);
        /*******************************************************/
        $data_json = '{
            "otp": "'.$otp.'"
        }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);

        $result = curl_exec($ch);
        curl_close($ch);
        echo $result;
    }

    public function validateZipFile(){
      if($_FILES && $_POST['userId'])
      { 
        $userId = $_POST['userId'];
        $zipFile = $_FILES['zip_file'];
        $zip = new ZipArchive;
        $res = $zip->open($zipFile['tmp_name']);
        $time = time();
        // print_r($zipFile);exit;
        $path = 'myzips/extract_path/'.$time.'/';
        if($res)
        {
          $zip->extractTo($path);
          $zip->close();
          $files = scandir($path);
          $blankArr1 = array();
          // print_r($files);exit;
          if(isset($files) && !empty($files)){
            foreach($files as $value){
              if($value == "P_".$userId."_mailledBackup.json"){
                  $reportJson1 = $path.$value;
                  // print_r($reportJson1);exit;
                  $inp1 = file_get_contents($reportJson1);
                  $tempArray1 = json_decode($inp1,true);
                  array_push($blankArr1,$tempArray1); 
              }
            }
          }
          // print_r($blankArr1);exit;
          // exit;
          if(isset($blankArr1) && !empty($blankArr1))             
          {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['ReportData']=$blankArr1;
          }
          else
          {
            $response['response_code'] = 2;
            $response['response_message'] = 'Failed';
          }
        }else{
          $response['response_code'] = 4;
          $response['response_message'] = 'res not found';
        }    
      }else{
        $response['response_code'] = 3;
        $response['response_message'] = 'Null';
      }
      echo json_encode($response);exit;
    }

    function getImgUrlFromBase64(){
      $baseImage = $_POST['baseImage'];
      // print_r($baseImage);exit;
      $url = "https://win.k2key.in/Ring/index.php/Webservice/generateBase64ToImageLink";
      $headers = array(
				"Content-Type:application/json",
			);
        /*******************************************************/
        $data_json = '{
            "baseImage": "'.$baseImage.'"
        }';
      // print_r($data_json);exit;
        $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
          curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        echo $result;
    }

    public function getImgUrlFromBase64Copy(){
      $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
      $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
      $baseImage = $_POST['baseImage'];
      // echo $baseImage;exit;
      if(isset($baseImage) && !empty($baseImage))
        {
            $data = explode(';', $baseImage);
            $type = $data[0];
            $data1 = explode(',', $data[2]);
            $base = $data1[0];
            $data2 = base64_decode($data1[1]);
            $ext = explode(':', $type);
            $file_ext = explode('/', $ext[1]);
            $file_name = 'c_'.time();
            $path = 'upload/';
            $fileLink = $path . $file_name; 
            // print_r($data1[1]);exit;         
            if($file_ext[1] == 'msword'){
                $addExt = 'doc';
            }else if($file_ext[1] == 'vnd.ms-excel'){
                $addExt = 'xls';
            }else if($file_ext[0] == 'image' && $file_ext[1] == '*'){
                $addExt = 'jpeg';
            }
            else{
                $addExt = $file_ext[1];
            }
            // $addExt = $_POST['type'];
            $file = $fileLink .".".$addExt;
            $success = file_put_contents($file, $data2);

            if($success) 
            {
              $response['response_code'] = '1';
              $response['response_message'] = 'Success';
              $response['file_link'] = $root.$file;
            }
            else 
            {
              $response["response_code"] = 2;
              $response["response_message"] = "Failed";
            }            
        }
        else
        {
          $response["response_code"] = 3;
          $response["response_message"] = "Data Null";
        }
        echo json_encode($response);exit;
    }

    public function getImgUrlFromBase64ForLocal(){
      $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
      $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
      $baseImage = $_POST['baseImage'];
      // echo $baseImage;exit;
      if(isset($baseImage) && !empty($baseImage))
        {
            $data = explode(';', $baseImage);
            $type = $data[0];
            $data1 = explode(',', $data[1]);
            $base = $data1[0];
            $data2 = base64_decode($data1[1]);
            $ext = explode(':', $type);
            $file_ext = explode('/', $ext[1]);
            $file_name = 'c_'.time();
            $path = 'upload/';
            $fileLink = $path . $file_name; 
            // print_r($data1[1]);exit;         
            if($file_ext[1] == 'msword'){
                $addExt = 'doc';
            }else if($file_ext[1] == 'vnd.ms-excel'){
                $addExt = 'xls';
            }else if($file_ext[0] == 'image' && $file_ext[1] == '*'){
                $addExt = 'jpeg';
            }
            else{
                $addExt = $file_ext[1];
            }
            // $addExt = $_POST['type'];
            $file = $fileLink .".".$addExt;
            $success = file_put_contents($file, $data2);

            if($success) 
            {
              $response['response_code'] = '1';
              $response['response_message'] = 'Success';
              $response['file_link'] = $root.$file;
            }
            else 
            {
              $response["response_code"] = 2;
              $response["response_message"] = "Failed";
            }            
        }
        else
        {
          $response["response_code"] = 3;
          $response["response_message"] = "Data Null";
        }
        echo json_encode($response);exit;
    }

    function logout() {
	        $ci = get_instance();
          $ci->session->sess_destroy();
          redirect(base_url().'index.php/ringWeb/Login');	        
	    }
}

?>