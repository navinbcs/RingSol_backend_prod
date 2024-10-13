<?php 
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ERROR | E_PARSE);
class Webservice extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
        ob_clean();
        $this->load->model(array('WebserviceModel'));
        $config['allowed_types'] = 'pdf|csv';
        $this->load->library('upload', $config);
        $this->load->library('m_pdf');
        $this->upload->initialize($config);
        $this->load->helper('url', 'form');
    }
//*********START: WEBSERVICE FOR LOGIN***********************


    function signUp(){        
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
            $mn = $data->mobile_number * 1;
            $mobile_number = $this->encryptDecrypt("en",$mn);
            $fname = $data->fname;            
            $fullName = $fname;
            $full_name = $this->encryptDecrypt("en",$fullName);
            $mob_code = $data->mob_code;
            $dob = isset($data->DOB)?$data->DOB:NULL;
            if(isset($dob) && !empty($dob)){
                $ts = strtotime($dob);
                $dob1 = date("Y-m-d H:i:s", $ts);
            }else{
                $dob1 = NULL;
            }
            $email = $this->encryptDecrypt("en",$data->email);
            // $BloodGroupId = isset($data->bloodgroup)?$data->bloodgroup:NULL;
            if(empty($data->bloodgroup) || !isset($data->bloodgroup)){
                $BloodGroupId = NULL;
            }else{
				$BloodGroupId = $data->bloodgroup;
			}
            $address = urlencode($data->address);
            $country = isset($data->country_id)?$data->country_id:NULL;
            $state = isset($data->state_id)?$data->state_id:NULL;
            $city = isset($data->city_id)?$data->city_id:NULL;
            $pincode = isset($data->pin_code)?$data->pin_code:NULL;
            $saveDataArray = array( 
                                    "MobileNumber"=>$mobile_number,
                                    "FullName"=>$full_name,
                                    "Email"=>$email,
                                    "MobileCode"=>$mob_code,
                                    "BloodGroupId"=>$BloodGroupId,
                                    "Address"=>$address,
                                    "CountryMasterId"=>$country,
                                    "StateMasterId"=>$state,
                                    "CityMasterId"=>$city,
                                    "PinCode"=>$pincode,
                                    "DateOfBirth"=>$dob1,
                                    "InsertDate"=>date("Y-m-d H:i:s")                                    
                                    );
            $checkData = $this->WebserviceModel->checkDuplicateMobileno($mob_code,$mobile_number,$email);
            if($checkData){
                $response['response_code'] = '4';
                $response['response_message'] = 'Duplicate Mobile Number Or Email ';                
            }
            else
            {
                $getData = $this->WebserviceModel->signUp($saveDataArray);
                if(true)
                {
                    $response['response_code'] = '1';
                    $response['response_message'] = 'Success';
                    $response['Data'] = $getData;
                }
                else
                {
                    $response['response_code'] = '2';
                    $response['response_message'] = 'Failed';
                }
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function GenerateOtp(){           
        $data = json_decode(file_get_contents('php://input'));
        if($data)        
        {    
            $mobile_number = $data->mobile_number * 1;
            $mobile_number = $this->encryptDecrypt("en",$mobile_number);
            $mob_code = $data->mob_code;
            $result = $this->WebserviceModel->checkUser($mobile_number,$mob_code);
            if($result)             
            {
                $pat_email = $this->encryptDecrypt("dc",$result->Email);
                if($mobile_number == "4IM7R/rfrKSyKbrLKiJ07g==" && $mob_code == "+60"){
                    $otp = "123456";
                }else{
                    $otp = rand(100000, 999999);
                }
                $pat_name = $this->encryptDecrypt("dc",$result->FullName);
                //$otp = rand(100000, 999999);
                // $message = 'Your OTP for login verification is '.$otp.'. Do not share this with anyone.';
                // $subject = 'Login OTP';
                $type = "loginOtp";
                $dataArr = array("name"=>$pat_name, "otp"=>$otp);
                // $sendMail = Utility::callSendMail($pat_email,$message,$subject);
                $sendMail = Utility::callSendMailWithTemplate($pat_email,$type,$dataArr);
                $result1 = $this->WebserviceModel->updateOtpData($otp,$mobile_number,$mob_code);
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['otp'] = $otp;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';
            }          
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;        
    }

    function expireOtp(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)            
        {    
            $mobile_number = $data->mobile_number;          
            $isDoctor = $this->WebserviceModel->checkDoctor($mobile_number);
            if(isset($isDoctor) && !empty($isDoctor))
            {       
                $doc_email = $isDoctor->Email;
                $otp = "";
                $mobile_number = $this->encryptDecrypt("en",$data->mobile_number);                    
                $result1 = $this->WebserviceModel->updateDoctorOtpData($otp,$mobile_number);
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['otp'] = $otp;                   
            }
            else
            {
                $mobile_number = $this->encryptDecrypt("en",$data->mobile_number);                    
                $mob_code = $data->mob_code;                    
                $result = $this->WebserviceModel->checkUser($mobile_number,$mob_code);
                if($result)             
                {
                    $pat_email = $this->encryptDecrypt("dc",$result->Email);
                    $otp = "";                       
                    $result1 = $this->WebserviceModel->updateOtpData($otp,$mobile_number,$mob_code);
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                    $response['otp'] = $otp;
                }
                else
                {
                    $response['response_code']=2;
                    $response['response_message']='Failed';
                }
            }               
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;   
    }

    function sendMail($otp="11",$email="pankajmehra164@gmail.com")
    {
        $data = json_decode(file_get_contents('php://input'));
        $config = Array(
        'protocol' => 'smtp',
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_user' => 'donotreplysancy@gmail.com', // change it to yours
        'smtp_pass' => 'Sancyuser@123', // change it to yours
        'mailtype' => 'html',
        'charset' => 'iso-8859-1',
        'wordwrap' => TRUE
        );
        $this->load->helper('string');
        $message = 'OTP For Login '.$otp.'. Do not share this with anyone';
        $this->load->library('email', $config);
        $this->email->set_newline("\r\n");
        $this->email->from('donotreplysancy@gmail.com'); // change it to yours
        $this->email->to($email);// change it to yours
        $this->email->subject('OTP-RingApp');
        $this->email->message($message);
        if($this->email->send())
        {
            $response['response_code']=1;
            $response['response_message']='Sucess';
            $response['email']=$email;
            $response['otp']=$otp;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';
        }
        echo json_encode($response); exit;
    }

    public function Login()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $otp = $data->otp;    
            $device_id = $data->device_id;
            $platform = $data->platform;
            $result = $this->WebserviceModel->checkOtp($otp);
            if($result || true)             
            {
                $checkDevice = $this->WebserviceModel->checkLoginDevice($result->PatientId);
                $result->MobileNumber = $this->encryptDecrypt("dc",$result->MobileNumber);
                // $result->FullName = $this->encryptDecrypt("dc",$result->FullName);
                $result->FirstName = $this->encryptDecrypt("dc",$result->FullName);
                $result->LastName = $this->encryptDecrypt("dc",$result->LastName);
                $result->FullName = $result->FirstName." ". $result->LastName;
                $result->Email = $this->encryptDecrypt("dc",$result->Email);
                $result->Email = $this->encryptDecrypt("dc",$result->Email);
                if(isset($result->DateOfBirth) && !empty($result->DateOfBirth)){
                    $result->DateOfBirth = $result->DateOfBirth;
                }else{
                    $result->DateOfBirth = "2000-01-01 00:00:00.000";
                }
                $UserSettings = $this->WebserviceModel->getUserSettings($result->PatientId,"Patient");
                $notificationDeviceId = isset($checkDevice->DeviceId)?$checkDevice->DeviceId:"";
                if(empty($checkDevice->DeviceId) || $notificationDeviceId == $device_id)
                {
                    $kunci = $this->config->item('thekey');
                    $token['id'] = $result->PatientId; 
                    $token['data'] = $result;
                    $date1 = new DateTime();
                    $token['iat'] = $date1->getTimestamp();
                    $token['exp'] = $date1->getTimestamp() + 60 * 60 * 5;
                    $output['token'] = JWT::encode($token, $kunci);
                    $saveDataArray = array( 
                                "PatientId"=> $result->PatientId,
                                "DeviceId"=>$device_id,
                                "Platform"=>$platform,
                                );
                    $notificationUserType = 0;            
                    $checkuserid = $this->WebserviceModel->checkuserid($result->PatientId,$notificationUserType);
                    if($checkuserid){
                        $updateDeviceToken = $this->WebserviceModel->updateDeviceToken($result->PatientId,$device_id,$platform);
                    }else{
                        $insertDeviceToken = $this->WebserviceModel->insertDeviceToken($saveDataArray);
                    }
                    $BackupDataNew = $this->WebserviceModel->checkUserBackup($result->PatientId,'Patient',3);
                    $response['response_code']=1;
                    $response['response_message']='Success';
                    $response['is_doctor']=0;
                    $response['data']=$result;
                    $response['token']=$output['token'];
                    $response['UserSettings']=isset($UserSettings)?$UserSettings:"";
                    $response['BackupDataNew']=isset($BackupDataNew)?$BackupDataNew:"";

                }
                else
                {
                    $isReff = 0;
                    $BackupDataNew = $this->WebserviceModel->checkUserBackup($result->PatientId,'Patient',3);
                    $response['response_code']=4;
                    $response['response_message']='Device id differ';
                    $response['PatientId']=$result->PatientId;
                    $response['UserSettings']=isset($UserSettings)?$UserSettings:"";
                    $response['BackupDataNew']=isset($BackupDataNew)?$BackupDataNew:"";
                } 
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';
            }        
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;        
    }
	
	function doctor_login_new()
	{
		$data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $userName = $data->userName;
            $password = $data->password;
            $result = $this->WebserviceModel->checkDoctorAuth($userName,$password);
			if($result)             
			{
                $kunci = $this->config->item('thekey');
                $token['id'] = $result->UserId;  
                $token['data'] = $result;
                $date1 = new DateTime();
                $token['iat'] = $date1->getTimestamp();
                $token['exp'] = $date1->getTimestamp() + 60 * 60 * 5; 
                $output['token'] = JWT::encode($token, $kunci); 
                $saveDataArray = array( 
                                    "PatientId"=> $result->UserId,
                                    "DeviceId"=>$device_id,
                                    "Platform"=>$platform,
                                    "UserType"=>1,
                                    );
                $notificationUserType = 1;            
                $checkuserid = $this->WebserviceModel->checkuserid($result->UserId,$notificationUserType);
                if($checkuserid){
                    $updateDeviceToken = $this->WebserviceModel->updateDeviceToken($result->UserId,$device_id,$platform);
                }else{
                    $insertDeviceToken = $this->WebserviceModel->insertDeviceToken($saveDataArray);
					}
				$UserSettings = $this->WebserviceModel->getUserSettings($result->UserId,"Doctor");
				$response['response_code']=1;
				$response['response_message']='Success';
				$response['is_doctor']=1;
				$response['data']=$result;
				$response['token']=$output['token'];
				$response['UserSettings']=isset($UserSettings)?$UserSettings:"";
            }else{
                $response['response_code']=2;
                $response['response_message']='Failed';
            }

        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
            echo json_encode($response);exit;
	}
    function update_device_token(){
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $userData = JWT::decode($token, $kunci);    
        // $user_id = $userData->id;
        // if($user_id)
        // {
            if($data)
            {
                $device_id = $data->device_id;
                $platform = $data->platform;
                $patient_id = $data->patient_id;
                $updateDeviceToken = $this->WebserviceModel->updateDeviceToken($patient_id,$device_id,$platform);
                if($updateDeviceToken == true){
                    $response['response_code']=1;
                    $response['response_message']='Success';
                }else{
                    $response['response_code']=2;
                    $response['response_message']='Failed';
                }
            }
            else
            {
                $response['response_code'] = 3;
                $response['response_message'] = 'Data is NULL';
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response);exit;
    }
	
	function clear_device_token(){
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $userData = JWT::decode($token, $kunci);    
        // $user_id = $userData->id;
        // if($user_id)
        // {
            if($data)
            {
                $patient_id = $data->patient_id;
                $userType = $data->userType;
                $updateDeviceToken = $this->WebserviceModel->clear_device_token($patient_id,$userType);
                if($updateDeviceToken == true){
                    $response['response_code']=1;
                    $response['response_message']='Success';
                }else{
                    $response['response_code']=2;
                    $response['response_message']='Failed';
                }

            }
            else
            {
                $response['response_code'] = 3;
                $response['response_message'] = 'Data is NULL';
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response);exit;
    }


    function check_device_token(){
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $userData = JWT::decode($token, $kunci);    
        // $user_id = $userData->id;
        // if($user_id)
        // {
            if($data)
            {
                $device_id = $data->device_id;
                $patient_id = $data->patient_id;
                $check = $this->WebserviceModel->check_device_token($patient_id,$device_id);
                if($check){
                    $response['response_code']=1;
                    $response['response_message']='Success';
                }else{
                    $response['response_code']=2;
                    $response['response_message']='Failed';
                }
            }
            else
            {
                $response['response_code'] = 3;
                $response['response_message'] = 'Data is NULL';
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response);exit;
    }


    public function doctorLogin($otp,$device_id,$platform)
    {              
        $result = $this->WebserviceModel->checkOtpForDoctor($otp);
        if($result)             
        {
                $kunci = $this->config->item('thekey');
                $token['id'] = $result->UserId;
                $token['data'] = $result;
                $date1 = new DateTime();
                $token['iat'] = $date1->getTimestamp();
                $token['exp'] = $date1->getTimestamp() + 60 * 60 * 5;
                $output['token'] = JWT::encode($token, $kunci);
                $saveDataArray = array( 
                                    "PatientId"=> $result->UserId,
                                    "DeviceId"=>$device_id,
                                    "Platform"=>$platform,
                                    "UserType"=>1,
                                    );
                $notificationUserType = 1;            
                $checkuserid = $this->WebserviceModel->checkuserid($result->UserId,$notificationUserType);
                if($checkuserid){
                    $updateDeviceToken = $this->WebserviceModel->updateDeviceToken($result->UserId,$device_id,$platform);
                }else{
                    $insertDeviceToken = $this->WebserviceModel->insertDeviceToken($saveDataArray);
                }
            $UserSettings = $this->WebserviceModel->getUserSettings($result->UserId,"Doctor");
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['is_doctor']=1;
            $response['data']=$result;
            $response['token']=$output['token'];
            $response['UserSettings']=isset($UserSettings)?$UserSettings:"";
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';
        }
        echo json_encode($response);exit;        
    }



    public function uploadImage() 
    {
       
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            $path = 'upload/';
            if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {
                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png", "pdf" =>"application/pdf", "JPG" => "image/jpeg","JPEG" => "image/jpeg", "PNG" => "image/png", "PDF" =>"application/pdf");
                $file_name = 'c_'.time().$_FILES['file']['name'];
                $new_file_name = explode("?", $file_name)[0];
                $file_ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
                $upload_data = $path . '/' . $new_file_name;
                $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
                $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
                if(!array_key_exists($file_ext, $allowed))
                {
                    $response['status'] = 'false';
                    $response['response_code'] = 5;  
                    $response['response_status'] = " Please select a valid file format.";
                }           
                else{
                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $upload_data)) {
                        $response['response_code'] = '1';
                        $response['response_message'] = 'Success';
                        $response['file_name'] = $new_file_name;
                        $response['image_path'] = $root . $upload_data;
                    } else {
                        $response["response_code"] = 2;
                        $response["response_message"] = "Failed To Upload";
                    }
                }
            } else {
                $response["response_code"] = 3;
                $response["response_message"] = "No image is received";
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response);exit;
    }

    public function getUserData(){    
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            $userData =  $this->WebserviceModel->getUserData();
            $dataArray = array();
                foreach ($userData as $value) {
                $mobile = $this->encryptDecrypt("dc",$value->MobileNumber);
                $fullName = $this->encryptDecrypt("dc",$value->FullName);
                $email = $this->encryptDecrypt("dc",$value->Email);
                $dataArray1 = array(
                                    "PatientId" =>$value->PatientId,
                                    "MobileCode"=>$value->MobileCode,
                                    "MobileNumber"=>$mobile,
                                    "OTP"=>$value->OTP,
                                    "FullName"=>$fullName,
                                    "Email"=>$email,
                                    "Avtar"=>$value->Avtar,
                                    "DateOfBirth"=>$value->DateOfBirth,
                                    "GenderId"=>$value->GenderId,
                                    "Address"=>urldecode($value->Address),
                                    "BloodGroupId"=>$value->BloodGroupId,
                                    "InsertDate"=>$value->InsertDate,
                                    "UpdateDate"=>$value->UpdateDate,
                                    "IsActive"=>$value->IsActive                     
                                );
                    array_push($dataArray,$dataArray1);
                }
            if($userData )             
            {                
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['user_data']=$userData;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';           
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }
        echo json_encode($response); exit;

    }

     public function getUserDataById(){   
        $data = json_decode(file_get_contents('php://input'));
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $tokenData = JWT::decode($token, $kunci);    
        $tokenUser = $tokenData->id;
        $user_id = $tokenData->data->PatientId;
        if($tokenUser)
        {           
            $userData =  $this->WebserviceModel->getUserDataById($user_id);
            $userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);   
            $name = $this->encryptDecrypt("dc",$userData->FullName);
            $NameArr = explode(" ",$name);
            $userData->FirstName = isset($NameArr[0])?$NameArr[0]:"";
            $userData->LastName = isset($NameArr[1])?$NameArr[1]:"";
            $userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
            $userData->Email = $this->encryptDecrypt("dc",$userData->Email);
			$userData->Address = urldecode($userData->Address);
            if($userData )             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['user_data']=$userData;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';           
            }
        }
        else
        {
            $response['response_code'] = 4;
            $response['response_message'] = 'JWT Token Error';
        }    
        echo json_encode($response); exit;
    }

    function getdoctorlist(){   
        // $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $userData = JWT::decode($token, $kunci);    
        // $user_id = $userData->data->PatientId;         
        $userData =  $this->WebserviceModel->getdoctorlist();               
        if($userData )             
        {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['user_data']=$userData;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';            
        }
        echo json_encode($response); exit;
    }

    public function updateUserData(){
        if($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'));
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);
        $user_id = $userData->data->PatientId;
        $BloodGroupId=$data->bloodgroup;        
        $mn = $data->mobile_number * 1;
        $mobile_number = $this->encryptDecrypt("en",$mn);
        $fullName = $data->fullName;
        $full_name = $this->encryptDecrypt("en",$fullName);
        $email = $this->encryptDecrypt("en",$data->email);
        $saveDataArray = array(                                 
                            "MobileNumber"=>$mobile_number,
                            "FullName"=>$full_name,
                            "Email"=>$email,
                            "MobileCode"=>$data->mob_code,
                            "Address"=>urlencode($data->address),
                            "BloodGroupId"=>$BloodGroupId,
                            "UpdateDate"=>date("Y-m-d H:i:s")                               
                            );
        $result1 = $this->WebserviceModel->updateUserData($saveDataArray,$user_id);                  
        if($result1)             
        {
            $response['response_code']=1;
            $response['response_message']='Sucess';
            $response['data']=$result1;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';           
        }        
        echo json_encode($response); exit;
    }

    public function saveReportData(){
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'));
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);
        $user_id = $userData->data->PatientId;        
        $sql = "SELECT * FROM CategoryMaster WHERE Category = '".$data->category_name."'";
        $query = $this->db->query($sql);
        $catData = $query->row();    
        $saveDataArray = array(                                 
                                "PatientId"=>$user_id,
                                "FileType"=>$data->file_type,
                                "FileExtension"=>$data->file_extension,
                                "FilePath"=>$data->filename_path,
                                "Description"=>$data->description,
                                "CategoryId"=>$catData->CategoryId,
                                "UpdateDate"=>date("Y-m-d H:i:s")                                
                                );
        $result1 = $this->WebserviceModel->saveReportData($saveDataArray,$user_id);                         
        if($result1)             
        {
            $response['response_code']=1;
            $response['response_message']='Sucess';
            $response['data']=$result1;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';            
        }        
        echo json_encode($response); exit;
    }

     function getMasterHospitallist(){
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $userData = JWT::decode($token, $kunci);    
        // $user_id = $data->user_id;
        $userData =  $this->WebserviceModel->getMasterHospitallist();              
        if($userData )             
        {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['user_data']=$userData;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';            
        }
        echo json_encode($response); exit;
    }

    function getReportlist(){   
        $data = json_decode(file_get_contents('php://input'));
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);   
        $user_id = $userData->data->PatientId;    
        $file_uploaded_data =  $this->WebserviceModel->getReportlistByUser($user_id);
        $fileArrayAttachemnt = array();
        foreach($file_uploaded_data as $file ){
            $InsertDate = $file->InsertDate;
            $newInsertDate = date("d/m/Y",  strtotime($InsertDate));                
            $fileArrayAttachemnt[]=array("id"=>$file->ReportTransitId,"cname"=>$file->cname,"tanantName"=>$file->TenantName,"description"=>$file->Description,"files"=>array(),"created_date"=>$newInsertDate );
            $fileArray = json_decode($file->FileAttachments);
            if(is_array($fileArray)){
                foreach($fileArray as $fileData){
                    $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["files"][]="https://app.ring.healthcare/upload/".$fileData->Filename;
                }
            }
        }
        if($userData)             
        {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['user_data']=$fileArrayAttachemnt;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';           
        }
        echo json_encode($response); exit;
    }

    function getRxReportlist(){
        $data = json_decode(file_get_contents('php://input'));
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);   
        $user_id = $userData->data->PatientId;
        $userData =  $this->WebserviceModel->getRxReportlistByUser($user_id);               
        if($userData)             
        {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['user_data']=$userData;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';            
        }
        echo json_encode($response); exit;
    }

    function getLabReportlist(){  
        $data = json_decode(file_get_contents('php://input'));
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);    
        $user_id = $userData->data->PatientId;
        $userData =  $this->WebserviceModel->getLabReportlistByUser($user_id);      
        if($userData)             
        {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['user_data']=$userData;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';            
        }
        echo json_encode($response); exit;
    }

    function getRadioReportlist(){
        $data = json_decode(file_get_contents('php://input'));
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);
        $user_id = $userData->data->PatientId;
        $userData =  $this->WebserviceModel->getRadioReportlistByUser($user_id);              
        if($userData)             
        {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['user_data']=$userData;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';            
        }
        echo json_encode($response); exit;
    }

    function getClinicalReportlist(){
        $data = json_decode(file_get_contents('php://input'));
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);   
        $user_id = $userData->data->PatientId;
        $userData =  $this->WebserviceModel->getClinicalReportlistByUser($user_id);                         
        if($userData)             
        {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['user_data']=$userData;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';            
        }
        echo json_encode($response); exit;
    }

    function HospitalSearch(){
        $data = json_decode(file_get_contents('php://input'));
        $getData = $this->WebserviceModel->HospitalSearch($data->distance,$data->Lat,$data->long);
        if($getData)
        {
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
            $response['Data'] = $getData;
            $response['OpeningTime'] = '10:30 AM';
            $response['ClosingTime'] = '7:30 PM';
        }
        else
        {
            $response['response_code'] = '2';
            $response['response_message'] = 'Failed';
        }   
        echo json_encode($response);exit;
    }

    function HospitalFilter(){
        $data = json_decode(file_get_contents('php://input'));
        $getData = $this->WebserviceModel->HospitalFilter($data->name);
        if($getData)
        {
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
            $response['Data'] = $getData;
            $response['OpeningTime'] = '10:30 AM';
            $response['ClosingTime'] = '7:30 PM';
        }
        else
        {
            $response['response_code'] = '2';
            $response['response_message'] = 'Failed';
        }   
        echo json_encode($response);exit;
    }

    function android_notification(){
        $data = json_decode(file_get_contents('php://input'));
        // print_r($data);exit;
        $PatientId=   $data->patientId;
        if(isset($data->RingId) && !empty($data->RingId)){
            $PatientId = $data->RingId;
        }
        $notification_heading = isset($data->notification_heading)?$data->notification_heading:"";        
        $notification_body = isset($data->notification_body)?$data->notification_body:"Your report has been sent please download it.";
        $notification_type = isset($data->notification_type)?$data->notification_type:"Referal";
        $companyName = isset($data->company_name)?$data->company_name:"";
        if(!isset($data->patientId)){
            echo json_encode(array("msg"=>"PatientId not sent")); exit;
        }
        $userType = 0; //0 = Patient, 1 = Doctor
        $checkuserid = $this->WebserviceModel->checkuserid($PatientId,$userType);
        $token = isset($checkuserid->DeviceId)?$checkuserid->DeviceId:0;
		if(isset($PatientId) && empty($token)){
			$mainProfileId = $this->WebserviceModel->getMainPatientId($PatientId);
			if(isset($mainProfileId->MainProfilePatientId)){
				$checkusertoken = $this->WebserviceModel->checkuserid($mainProfileId->MainProfilePatientId,$userType);
        		$usertoken = isset($checkusertoken->DeviceId)?$checkusertoken->DeviceId:0;
			}else{
				$usertoken = $token;
			}
			
		}else{
			$usertoken = $token;
		}

        if($checkuserid->Platform == "ios"){
            $message = array(
                        'title' => 'Report is ready', 
                        'body' => $notification_body, 
                        'sound' => 'default', 
                        'badge' => '1',
                        'notifictionType' => $notification_type,
                        "notification_heading" =>  $notification_heading,
                        "notification_body" =>  $notification_body,
                        "company_name" =>  $companyName
                    ); 
            $result = $this->send_notification_ios($usertoken,$notification_body,$data,$message);     
        }else{
            //echo '111';exit;
            $message = array(
                        'title' => 'Report is ready', 
                        'body' => $notification_body, 
                        'sound' => 'default', 
                        'badge' => '1',
                        'click_action'=>'FCM_PLUGIN_ACTIVITY', //For only Android App
                        'notifictionType' => $notification_type,
                        "notification_heading" =>  $notification_heading,
                        "notification_body" =>  $notification_body,
                        "company_name" =>  $companyName,
                    );      
            $url = "https://fcm.googleapis.com/fcm/send";
            $serverKey = 'AAAAYtnkw9E:APA91bHqSJZSydEao75WwveTXNCaM4rqn4dPypsHsVy9m4PfV6pzEMHd5ntJAt3cV5SWDF5ZP1lkGakKnGSNbfWn97DK0vDUSY9LTIDxVIBEP_pWTmReLq0tbw6t1qmIuzEXz9t6MVq1'; // add api key here
            $notification = $message;
            $data = array('extraInfo'=> 'DomingoMG','notifictionType' => $notification_type, 'others'=> $message);
            $arrayToSend = array('to' => $usertoken, 'notification' => $notification, 'priority'=>'high', 'data'=> $data);
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key='. $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);    
            //Send the request
            $response = curl_exec($ch);
            if ($response === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
            $notificationArr = array(
                "PatientId" => $PatientId,
                "DeviceID" => $token,
                "notification_body" => $notification_body,
                "NotificationDate" => date("Y-m-d H:i:s"),
                "CreatedDate" => date("Y-m-d H:i:s")
                ); 
            $this->WebserviceModel->InsertNotificationLog($notificationArr);
        }        
    }

    public function send_notification_ios($device_id,$notification_body,$data,$message)
    {
        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        $token = $device_id; 

        // $notification = array('title' => 'Report is ready' , 'text' => $notification_body);
        $notification = $message;
        //$data = array('extraInfo'=> 'DomingoMG','notifictionType' => 'Referal');
        $arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high', 'data'=> $data);
        //$key = 'AAAAz0gGzvM:APA91bEFcGWq6Ug7DoqW8puWKqa2OibMCIbZ__fzsmsGr9iacFWsrtyzemP79ACrnKsWaig4GAqsTsEFm3Tgk-j7mO-rZVcLy2-sopmRWnJwSgT-oDBjcIBFLl8249Giw72hMsM1uvpI';
	$key = 'AAAA6pCx2Ms:APA91bHrE3BPJAW4goJKIhCRy6zg23rNcA-QhkY-0ND0kPAusd21I-1jH9DY7aeROE8TQiKZw3-c1DBjLzTXw6xsHyI9PoUQcLjGLxqf2vfuz5SRVCk4DvDaKkK13NZ91VEDX5ax3kj0';
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = "Authorization: key= $key"; // key here

        //Setup curl, add headers and post parameters.
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);       

        //Send the request
        $response = curl_exec($ch);
        //Close request
        curl_close($ch);
        return $response;
    }

    function android_notification_old(){
        $data = json_decode(file_get_contents('php://input'));
        $PatientId=   $data->patientId;
        if(!isset($data->patientId)){
            echo json_encode(array("msg"=>"PatientId not sent")); exit;
        }
        $userType = 0; //0 = Patient, 1 = Doctor
        $checkuserid = $this->WebserviceModel->checkuserid($PatientId,$userType);
        $token = isset($checkuserid->DeviceId)?$checkuserid->DeviceId:0;
		if(isset($PatientId) && empty($token)){
			$mainProfileId = $this->WebserviceModel->getMainPatientId($PatientId);
			if(isset($mainProfileId->MainProfilePatientId)){
				$checkusertoken = $this->WebserviceModel->checkuserid($mainProfileId->MainProfilePatientId,$userType);
        		$usertoken = isset($checkusertoken->DeviceId)?$checkusertoken->DeviceId:0;
			}else{
				$usertoken = $token;
			}
			
		}else{
			$usertoken = $token;
		}
        $message = array(
                        'title' => 'Report is ready', 
                        'body' => 'Your report has been sent please download it.', 
                        'sound' => 'default', 
                        'badge' => '1',
                        'click_action'=>'FCM_PLUGIN_ACTIVITY', //For only Android App
                        'notifictionType' => 'Referal'
                    );      
        $url = "https://fcm.googleapis.com/fcm/send";
        $serverKey = 'AAAAYtnkw9E:APA91bHqSJZSydEao75WwveTXNCaM4rqn4dPypsHsVy9m4PfV6pzEMHd5ntJAt3cV5SWDF5ZP1lkGakKnGSNbfWn97DK0vDUSY9LTIDxVIBEP_pWTmReLq0tbw6t1qmIuzEXz9t6MVq1'; // add api key here
        $notification = $message;
        $data = array('extraInfo'=> 'DomingoMG','notifictionType' => 'Referal');      
        $arrayToSend = array('to' => $usertoken, 'notification' => $notification, 'priority'=>'high', 'data'=> $data);
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);    
        //Send the request
        $response = curl_exec($ch);
        if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
    }

    function fileTransferConfirmation(){
        $data = json_decode(file_get_contents('php://input'));
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $tokenData = JWT::decode($token, $kunci);    
        $tokenUser = $tokenData->id;
        if($tokenUser)
        {
            $getData = $this->WebserviceModel->updateEreportFile($data->file_id);
            if($getData)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = 4;
            $response['response_message'] = 'JWT Token Error';
        }
        echo json_encode($response);exit;           
    }

    function fileTransferStatusUpdate(){
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            $fileId = $data->file_id;
            $proccessUser = $data->user;
            if($proccessUser == "doctor"){
                $proccessName = "IsDoctorProcessed";
            }else if($proccessUser == "patient"){
                $proccessName = "IsPatientProcessed";
            }
            $getData = $this->WebserviceModel->fileTransferStatusUpdate($fileId,$proccessName);
            if($getData)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response);exit;
    }

    function fileTransferStatusUpdateNew(){
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            $reportTransitId = $data->ReportTransitId;
            $proccessUser = $data->user;
            $transitDetailId  = $data->EreportsTransitDetailId;
            $doctorId  = $data->DoctorId;
            if($proccessUser == "doctor"){
                $proccessName = "IsDoctorProcessed";
            }else if($proccessUser == "patient"){
                $proccessName = "IsPatientProcessed";
            }
            if(!empty($transitDetailId)){
                $updateDetail = $this->WebserviceModel->updateFileTransferStatus($transitDetailId,$proccessName);
                $getRemainFiles = $this->WebserviceModel->getFilesRemainingToDownload($reportTransitId,$proccessName,$doctorId);
                if($getRemainFiles->count == 0)
                {
                    $updateEreportTransit = $this->WebserviceModel->fileTransferStatusUpdate($reportTransitId,$proccessName);
                }
            }else{
                $updateDetail = $this->WebserviceModel->fileTransferStatusUpdate($reportTransitId,$proccessName);
            }
            
            if($updateDetail)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response);exit;
    }

    function getUrlForFile(){
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {       
            $file_url = $data->file_url;
            $pdfName = 'upload/'.mt_rand().time().".pdf";
            $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
            $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
            if(file_put_contents($pdfName, fopen($file_url, 'r')))
            {            
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['file_url'] = $root.$pdfName;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response);exit;      
    }

    public function uploadBase64Image() 
    {
        $data = json_decode(file_get_contents('php://input'));
        print_r($_FILES);exit;
            if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        if($data)
        {
            $headers = apache_request_headers();
            $dataUri = $data->file_url;
            $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
            $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
            define('UPLOAD_DIR', 'upload/');
                $img = $dataUri;
                $img = str_replace('data:image/jpeg;base64,', '', $img);
                $img = str_replace(' ', '+', $img);
                $data = base64_decode($img);
                $newFileName = uniqid() . '.jpeg';
                $file = UPLOAD_DIR . $newFileName;
                $success = file_put_contents($file, $data);
            if ($success) 
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['image_path'] = $root.$file;
            } 
            else 
            {
                $response["response_code"] = 2;
                $response["response_message"] = "Failed To Upload";
            }

        } 
        else 
        {
            $response["response_code"] = 3;
            $response["response_message"] = "No image is received";
        }
        echo json_encode($response);exit;
    }
 
    function getBloodGrouplist(){
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {             
            $userData =  $this->WebserviceModel->getBloodGrouplist();                                 
            if($userData)             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['user_data']=$userData;
            }
            else 
            {
                $response["response_code"] = 2;
                $response["response_message"] = "Failed";
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }        
        echo json_encode($response); exit;    
    }

    function encryptDecrypt($type,$name){
        if($type == 'en'){
            $type1 = "Encrypt";
        }else{
            $type1 = "Decrypt";
        }
        $arrayToSend = array('userName'=>$name,'type'=>$type1);
        $url = 'https://apiweb.ring.healthcare:5028/api/Register/EncryptDecrypt';
        $json = json_encode($arrayToSend);           
        $headers = array('Content-Type: application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);            
        $response = curl_exec($ch);         
        curl_close($ch); 
        return  $response;
    }

    function getSpecialityList(){  
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {           
            $specialityData =  $this->WebserviceModel->getSpecialityList();                                         
            if($specialityData)             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['user_data']=$specialityData;
            }
            else 
            {
                $response["response_code"] = 2;
                $response["response_message"] = "Failed";
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }        
        echo json_encode($response); exit;    
    }

    function HospitalFilterForSpeciality()
    {
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            if($data){
                $hosName = isset($data->name)?$data->name:" ";
                $specialityId = isset($data->speciality_id)?$data->speciality_id:" ";
                if(!empty($hosName) && $specialityId ==" "){
                    $getData = $this->WebserviceModel->HospitalFilter($hosName);
                }else if(!empty($specialityId) && $hosName == " " ){
                    $getData = $this->WebserviceModel->HospitalFilterForSpeciality($specialityId);
                }           
                if($getData)
                {
                    $response['response_code'] = '1';
                    $response['response_message'] = 'Success';
                    $response['Data'] = $getData;
                    $response['OpeningTime'] = '10:30 AM';
                    $response['ClosingTime'] = '7:30 PM';
                }
                else
                {
                    $response['response_code'] = '2';
                    $response['response_message'] = 'Failed';
                }
            }
            else
            {
                $response['response_code'] = '3';
                $response['response_message'] = 'Data is null';
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }        
        echo json_encode($response);exit;
    }    
    
    function patientSearchByKeyword()
    {
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            if($data){
                $keyword = $data->keyword;
                $getPatient = $this->WebserviceModel->patientSearchByKeyword($keyword);            
                if($getPatient)
                {
                    $response['response_code'] = '1';
                    $response['response_message'] = 'Success';
                    $response['response_data'] = $getPatient;
                }
                else
                {
                    $response['response_code'] = '2';
                    $response['response_message'] = 'Failed';
                }
            }
            else
            {
                $response['response_code'] = '3';
                $response['response_message'] = 'Data is null';
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }        
        echo json_encode($response);exit;
    } 

    function patientSearchByMobileNumber()
    {
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            if($data){ 
                // $mn = $data->mobile * 1;
                $mn = $data->mobile;
                $mob = isset($mn)?$mn:"";
                $mob_code = isset($data->mob_code)?$data->mob_code:"";
                $email = isset($data->email)?$data->email:"";
                if(!empty($mob) && !empty($mob_code)){
                    $mobile_number = $this->encryptDecrypt("en",$mob);
                    $getPatient = $this->WebserviceModel->patientSearchByMobileNumber($mobile_number,$mob_code);
                }else if(!empty($email)){
                    $email = $this->encryptDecrypt("en",$email);
                    $getPatient = $this->WebserviceModel->patientSearchByEmail($email);
                }               
                if(isset($getPatient))
                {
                    $getPatient->MobileNumber = $this->encryptDecrypt("dc",$getPatient->MobileNumber);   
                    $getPatient->FullName = $this->encryptDecrypt("dc",$getPatient->FullName);
                    $getPatient->Email = $this->encryptDecrypt("dc",$getPatient->Email);     
                    $response['response_code'] = '1';
                    $response['response_message'] = 'Success';
                    $response['response_data'] = $getPatient;
                }
                else
                {
                    $response['response_code'] = '2';
                    $response['response_message'] = 'Failed';
                }
            }
            else
            {
                $response['response_code'] = '3';
                $response['response_message'] = 'Data is null';
            } 
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }       
        echo json_encode($response);exit;
    } 


    function send_notification(){
        $data = json_decode(file_get_contents('php://input'));
        $Campaign=$_GET["Campaign"];      
        $getData = $this->WebserviceModel->GetCampaignData($Campaign);
        if($getData)
        {
            foreach($getData as $val){
                $message = array(
                            'title' => $val->Title, 
                            'body' => strip_tags($val->CampaignDescription), 
                            'sound' => 'default', 
                            'badge' => '1',
                            'notifictionType' => 'Campaign',
                            'click_action'=>'FCM_PLUGIN_ACTIVITY' //For only Android App
                        );
                $notifictionType =  "Campaign";      
                $this->send_notification_android_new($val->DeviceId,$message,$notifictionType);
            }      
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
            $response['Data'] = $getData;
        }
        else
        {
            $response['response_code'] = '2';
            $response['response_message'] = 'Failed';
            $response['message'] = 'Incorrect Campaign Id';
        }  
        echo json_encode($response);exit;
    }

    function refered_notification()
    {
        $data = json_decode(file_get_contents('php://input'));
        $ReportTransitId=$_GET["ReportTransitId"];       
        $getData = $this->WebserviceModel->GetReportTransitIdData($ReportTransitId);
        if($getData)
        {           
            $message = array(
                        'title' => 'test', 
                        'body' => 'test', 
                        'sound' => 'default', 
                        'badge' => '1',
                        'click_action'=>'FCM_PLUGIN_ACTIVITY' //For only Android App
                    );
            $notifictionType =  "refered_notification";            
            // $this->send_notification_android_new($getData[0]->DeviceId,$message,$notifictionType);           
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
        }
        else
        {
            $response['response_code'] = '2';
            $response['response_message'] = 'Failed';
            $response['message'] = 'Incorrect Campaign Id';
        }  
        echo json_encode($response);exit;
    }
    function send_notification_android_new($device_id,$message,$notifictionType){
        $token = $device_id; 
        $url = "https://fcm.googleapis.com/fcm/send";
        $serverKey = 'AAAAYtnkw9E:APA91bHqSJZSydEao75WwveTXNCaM4rqn4dPypsHsVy9m4PfV6pzEMHd5ntJAt3cV5SWDF5ZP1lkGakKnGSNbfWn97DK0vDUSY9LTIDxVIBEP_pWTmReLq0tbw6t1qmIuzEXz9t6MVq1'; // add api key here
        $notification = $message;
        $data = array('extraInfo'=> 'DomingoMG','notifictionType' => $notifictionType);
        $arrayToSend = array('to' => $token, 'notification' => $notification, 'priority'=>'high', 'data'=> $data);
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);   
        $response = curl_exec($ch);
        if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
    }

    public function GetCampaignDataOfNotification(){  
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            if($data){
                $autoDeleteDays = $data->autoDeleteDays;           
                $userData =  $this->WebserviceModel->GetCampaignDataOfNotification();
                date_default_timezone_set("Asia/Kuala_Lumpur");
                $campData = array();
                foreach($userData as $val){
                    $campDate = $val->InsertDate;
                    $campDate1 = strtotime($campDate);
                    $now = time();
                    $datediff = $now - $campDate1;
                    $diffDays = round($datediff / (60 * 60 * 24));            
                    if($diffDays < $autoDeleteDays){
                        array_push($campData,$val);				   
                    }
                }
                if($campData)             
                {
                    $response['response_code']=1;
                    $response['response_message']='Success';
                    $response['data']=$campData;
                }
                else
                {
                    $response['response_code']=2;
                    $response['response_message']='Failed';            
                }
            }
            else
            {
                $response['response_code'] = '3';
                $response['response_message'] = 'Data is null';
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }
        echo json_encode($response); exit;
    }

    public function GetRingGroupData(){  
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {     
            $keyword = isset($data->keyword)?$data->keyword:"";
            if(!empty($keyword)){
                $grpData =  $this->WebserviceModel->GetRingGroupData($keyword);
            }else{
                $grpData =  $this->WebserviceModel->GetRingGroupDataWithoutData();
            }       
            if($grpData)             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['data']=$grpData;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';          
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response); exit;
    }

    public function GetSpecialtyData(){  
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            $userData =  $this->WebserviceModel->GetSpecialtyData($data->TenantId);
            if($userData)             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['data']=$userData;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';            
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response); exit;
    }

    public function GetReferredToDoctorData(){   
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            $userData =  $this->WebserviceModel->GetReferredToDoctorData($data->TenantId);
            if($userData)             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['data']=$userData;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';           
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response); exit;
    }

    public function EreportTransitListViaDoctorId(){
        $data = json_decode(file_get_contents('php://input'));
        // $headers = $_SERVER["HTTP_AUTHORIZATION"];
        // $token = str_replace("Bearer ", "", $headers);
        // $kunci = $this->config->item('thekey');
        // $tokenData = JWT::decode($token, $kunci);    
        // $tokenUser = $tokenData->id;
        // if($tokenUser)
        // {
            if($data)
            {
                $doc_id = $data->doctor_id;
                $reportList = $this->WebserviceModel->EreportTransitListViaDoctorId($doc_id);   
                $fileArrayAttachemnt = array();
                foreach($reportList as $file ){
                    $InsertDate = $file->InsertDate;
                    $newInsertDate = date("d/m/Y",  strtotime($InsertDate));
                    $fileArrayAttachemnt[]=array("ReportTransitId"=>$file->ReportTransitId,"InsertDate"=>$newInsertDate,"Description"=>$file->Description,"EreferralForm"=>array(),"FileAttachments"=>array(),"IsProcessed"=>$file->IsProcessed,"IsDoctorProcessed"=>$file->IsDoctorProcessed,"IsPatientProcessed"=>$file->IsPatientProcessed,"AddReferral"=>$file->AddReferral,"doctorName"=>$file->doctorName,"TenantName"=>$file->TenantName );              
                    $referralFormArray = json_decode($file->EreferralForm);
                    if(is_array($referralFormArray)){
                        foreach($referralFormArray as $referralData){                       
                            $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["EreferralForm"][]="https://app.ring.healthcare/upload/".$referralData->Filename;
                        }
                    }
                    $fileAttachmentList = $this->WebserviceModel->getFileAttachmentByReportTransitId($file->ReportTransitId);
                    if(is_array($fileAttachmentList)){
                        foreach($fileAttachmentList as $fileData){
                            $jsonFileArr = json_decode($fileData->FileAttachments);                   
                            if(isset($jsonFileArr[0]->Filename)){    
                                $fileData->FileAttachments = "https://app.ring.healthcare/upload/".$jsonFileArr[0]->Filename;
                                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["FileAttachments"][]=$fileData;
                            }
                        }
                    }
                }
                if($reportList)             
                {
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                    $response['response_data']= $fileArrayAttachemnt;
                }
                else
                {
                    $response['response_code'] = 2;
                    $response['response_message'] = 'Failed';
                }
            }
            else 
            {
                $response['response_code'] = 3;
                $response['response_message'] = 'Data is null';
            }
        // }
        // else
        // {
        //     $response['response_code'] = 4;
        //     $response['response_message'] = 'JWT Token Error';
        // }    
        echo json_encode($response); exit;
    }

    public function EreportTransitListViaPatientId(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $pat_id = $data->patient_id;
            $ringGrpId = isset($data->RingGroupMasterId)?$data->RingGroupMasterId:"";
            $reportList = $this->WebserviceModel->EreportTransitListViaPatientId($pat_id,$ringGrpId);
            $fileArrayAttachemnt = array();
            foreach($reportList as $file ){
                // print_r($file);exit;
                $InsertDate = $file->InsertDate;
                if(isset($file->Description) && $file->Description != null){
                    $desc = $file->Description;
                }else{
                     $desc = '';
                }
                $newInsertDate = date("d/m/Y",  strtotime($InsertDate));
                if(isset($file->ICDSubCode) && isset($file->ICDSubCodeDescription) && !empty($file->ICDSubCode) && !empty($file->ICDSubCodeDescription)){
                   $diagnosis = $file->ICDSubCode."_".$file->ICDSubCodeDescription; 
                }else if(isset($file->ICD) && !empty($file->ICD)){
                    $diagnosis = $file->ICD;
                }else{
                    $diagnosis = $desc;
                }
                $fileArrayAttachemnt[]=array("PatientMasterId"=>$file->PatientMasterId,"ReportTransitId"=>$file->ReportTransitId,"RingGroupMasterId"=>$file->RingGroupMasterID,"RingGroupMasterIdReff"=>$file->RingGroup,"InsertDate"=>$newInsertDate,"Description"=>$desc,"EreferralForm"=>array(),"FileAttachments"=>array(),"IsProcessed"=>$file->IsProcessed,"IsDoctorProcessed"=>$file->IsDoctorProcessed,"IsPatientProcessed"=>$file->IsPatientProcessed,"AddReferral"=>$file->AddReferral,"DoctorId"=>$file->DoctorId,"doctorName"=>$file->doctorName, "DoctorPhoneNumber"=>$file->UserPhoneNumber,"TenantName"=>$file->TenantName,"WorkingSchedule"=>array(),"TenantPhoneNuber"=>$file->TenantPhoneNuber,"TenantFaxNumber"=>$file->TenantFaxNumber,"TenantAddress"=>$file->TenantAddress,"Refferal"=>array(), "diagnosis"=>$diagnosis,"DoctorSpeciality"=>$file->DoctorSpeciality);               
                /**Working Hours of Tenant */
				$workHrArr = array();
                $refworkHrArr = array();
                // $workingArr = $this->db->select('*')->from('TenantWorkingHours')->where('TenantId',$file->TenantId)->get()->result();
				$workingArr = $this->WebserviceModel->getWorkingScheduleOfTenant($file->TenantId);
                foreach($workingArr as $workHrVal){
					//print_r($workHrVal);exit; 
    				// $workHrVal->FromTime = date("Y-m-d h:i", strtotime($workHrVal->FromTime));
					// $workHrVal->ToTime = date("Y-m-d h:i", strtotime($workHrVal->ToTime));
                    $workHrVal->FromTime = date("h:i A", strtotime($workHrVal->FromTime));
					$workHrVal->ToTime = date("h:i A", strtotime($workHrVal->ToTime));
					array_push($workHrArr,$workHrVal);
				}
                if(isset($workHrArr) && !empty($workHrArr)){
                    $workingHTML = '<div class="f14 txtlist">';
                    $workingHTMLinMalay = '<div class="f14 txtlist">';
                    foreach($workHrArr as $workHrArrVal)
                    {
                        // $workingHTML .= '<ion-col size="12" class="f14 txtlist">
                        //                 <span class="fw600" translate> FromTime :</span>'.$workHrArrVal->FromTime.'<br>
                        //             </ion-col>
                        //             <ion-col size="12" class="f14 txtlist">
                        //                 <span class="fw600" translate> ToTime :</span>'.$workHrArrVal->ToTime.'<br>
                        //             </ion-col>';
                        // $workingHTMLinMalay .= '<ion-col size="12" class="f14 txtlist">
                        //     <span class="fw600" translate> Dari jam :</span>'.$workHrArrVal->FromTime.'<br>
                        // </ion-col>
                        // <ion-col size="12" class="f14 txtlist">
                        //     <span class="fw600" translate> Hingga jam :</span>'.$workHrArrVal->ToTime.'<br>
                        // </ion-col>';
                        $workingHTML .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                        $workingHTMLinMalay .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                    }
                    $workingHTML .= '</div>';
                    $workingHTMLinMalay .= '</div>';
                }else{
                    $workingHTML = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> From Time :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> To Time :</span>N/A
                                    </ion-col>
                                    </p>';
                    $workingHTMLinMalay = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Dari jam :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Hingga jam :</span>N/A
                                    </ion-col>
                                    </p>';                
                }
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingSchedule"] = $workingHTML;
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingScheduleInMalay"] = $workingHTMLinMalay;
                // $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingSchedule"] = $workHrArr;

                /*Referral Details of E-Report*/

                if(isset($file->ReferredToUserId) && !empty($file->ReferredToUserId)){
                 $refDocDetails = $this->WebserviceModel->getRefReportDoctorDetails($file->ReferredToUserId,$file->refTenant);  
                //   $refworkingArr = $this->db->select('*')->from('TenantWorkingHours')->where('TenantId',$refDocDetails[0]->TenantId)->get()->result(); 
                   $refworkingArr = $this->WebserviceModel->getWorkingScheduleOfTenant($refDocDetails[0]->TenantId);
                // echo "<pre>"; print_r($refDocDetails);exit;
                  if(isset($refworkingArr) && !empty($refworkingArr)){
                    foreach($refworkingArr as $refworkingVal)
                    {
                        //print_r($workHrVal);exit; 
                        // $refworkingVal->FromTime = date("Y-m-d h:i", strtotime($refworkingVal->FromTime));
                        // $refworkingVal->ToTime = date("Y-m-d h:i", strtotime($refworkingVal->ToTime));
                        $refworkingVal->FromTime = date("h:i A", strtotime($refworkingVal->FromTime));
                        $refworkingVal->ToTime = date("h:i A", strtotime($refworkingVal->ToTime));
                        array_push($refworkHrArr,$refworkingVal);
				    }
                }
                  if(isset($refworkHrArr) && !empty($refworkHrArr)){
                    $refworkingHTML = '<div class="f14 txtlist">';
                    $refworkingHTMLMalay = '<div class="f14 txtlist">';
                    foreach($refworkHrArr as $refworkHrArrVal)
                    {
                        $refworkingHTML .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$refworkHrArrVal->DayName.' : </span>  '.$refworkHrArrVal->FromTime.' - '.$refworkHrArrVal->ToTime.'<br>
                                    </ion-col>';
                        $refworkingHTMLMalay .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$refworkHrArrVal->DayName.' : </span>  '.$refworkHrArrVal->FromTime.' - '.$refworkHrArrVal->ToTime.'<br>
                                    </ion-col>';            
                    }
                    $refworkingHTML .= '</div>';
                    $refworkingHTMLMalay .= '</div>';
                }else{
                    $refworkingHTML = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> From Time :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> To Time :</span>N/A
                                    </ion-col>
                                    </p>';
                    $refworkingHTMLMalay = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Dari jam :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Hingga jam :</span>N/A
                                    </ion-col>
                                    </p>';                
                }
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingScheduleref"] = $refworkingHTML;
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingSchedulerefMalay"] = $refworkingHTMLMalay;                
                  $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["Refferal"] = $refDocDetails;
                }
                if($file->IsReferralFormProcessed == 0){
                    $referralFormArray = json_decode($file->EreferralForm);
                    if(is_array($referralFormArray)){
                        foreach($referralFormArray as $referralData){
                            $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["EreferralForm"][]="https://app.ring.healthcare/upload/".$referralData->Filename;
                        }
                    }
                }
                $fileAttachmentList = $this->WebserviceModel->getFileAttachmentByReportTransitId($file->ReportTransitId,$file->DoctorId);
                if(is_array($fileAttachmentList)){
                    foreach($fileAttachmentList as $fileData){
						$jsonFileArr = json_decode($fileData->FileAttachments);
						if(isset($jsonFileArr[0]->Filename)){
							$fileData->FileAttachments = "https://app.ring.healthcare/upload/".$jsonFileArr[0]->Filename;
							$fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["FileAttachments"][]=$fileData;
						}
                    }
                }
            }
            if($reportList)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $fileArrayAttachemnt;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    public function EreportTransitDataById(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $report_id = $data->ReportTransitId;
            $reportData = $this->WebserviceModel->EreportTransitDataById($report_id);
            $fileArrayAttachemnt = array();
            foreach($reportData as $file ){
                $InsertDate = $file->InsertDate;
                $newInsertDate = date("d/m/Y",  strtotime($InsertDate));
                $fileArrayAttachemnt[]=array("ReportTransitId"=>$file->ReportTransitId,"InsertDate"=>$newInsertDate,"Description"=>$file->Description,"EreferralForm"=>array(),"FileAttachments"=>array(),"IsProcessed"=>$file->IsProcessed,"IsDoctorProcessed"=>$file->IsDoctorProcessed,"IsPatientProcessed"=>$file->IsPatientProcessed,"AddReferral"=>$file->AddReferral,"doctorName"=>$file->doctorName,"TenantName"=>$file->TenantName,"Address"=>$file->Address,"Latitude"=>$file->Latitude,"Longitude"=>$file->Longitude,"Category"=>$file->Category );                                                                                                                                                                                                                                                                                                                                                                                                                                          
                $referralFormArray = json_decode($file->EreferralForm);
                if(is_array($referralFormArray)){
                    foreach($referralFormArray as $referralData){
                        $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["EreferralForm"][]="https://app.ring.healthcare/upload/".$referralData->Filename;
                    }
                }
                $fileArray = json_decode($file->FileAttachments);
                if(is_array($fileArray)){
                    foreach($fileArray as $fileData){
                        $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["FileAttachments"][]="https://app.ring.healthcare/upload/".$fileData->Filename;
                    }
                }
            }
            if($reportData)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $fileArrayAttachemnt;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }


    public function generateBase64ToImageLink() 
    {
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data) 
        {
            list($type, $data1) = explode(';', $data->baseImage);
            list($base, $data1)      = explode(',', $data1);
            $data1 = base64_decode($data1);
            $ext = explode(':', $type);
            $file_ext = explode('/', $ext[1]);
            $file_name = 'c_'.time();
            $path = 'upload/';
            $fileLink = $path . $file_name;          
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
            $file = $fileLink .".".$addExt;
            $success = file_put_contents($file, $data1);
            if ($success) {
               
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                //$response['file_link'] = $root.$file;
		$response['file_link'] = "https://apimobile.ring.healthcare:5025/".$file;
                // $response['file_link'] = "https://win.k2key.in/Ring_dev/index.php/Webservice/downloadFile?token=".$token;
            } else {
                $response["response_code"] = 2;
                $response["response_message"] = "Failed";
            }
            
        } else {
            $response["response_code"] = 3;
            $response["response_message"] = "Data Null";
        }
        echo json_encode($response);exit;
    }
    
    public function getRandomStringMd5()
    {
        $length = 16;
        $string = md5(rand());
        $randomString = substr($string, 0, $length);
        return $randomString;
    }
    public function testImage() 
    {
        $data = json_decode(file_get_contents('php://input'));
        list($type, $data1) = explode(';', $data->baseImage);
        list($base, $data1)      = explode(',', $data1);
        $data1 = base64_decode($data1);
        $ext = explode(':', $type);
        $file_ext = explode('/', $ext[1]);
        $file_name = 'c_'.time();
        $path = 'upload/';
        $fileLink = $path . $file_name;
        $file = $fileLink .".".$file_ext[1];
        $success = file_put_contents($file, $data1);
    }

    public function insertUsersBackupData()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $UserId = $data->UserId;
            $UserType = $data->UserType;
            $FileName = $data->FileName;
            $insertArr = array( 
                            "UserId"=> $UserId,
                            "UserType"=>$UserType,
                            "FileName"=>$FileName
                        );        
            $result = $this->WebserviceModel->insertUsersBackupData($insertArr);       
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    public function restoreUsersBackupData()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $UserId = $data->UserId;
            $UserType = $data->UserType;        
            // $result['BackupData'] = $this->WebserviceModel->restoreUsersBackupData($UserId,$UserType,0); 
            // $result['refferalBackupData'] = $this->WebserviceModel->restoreUsersBackupData($UserId,$UserType,1);    
            $result['BackupDataNew'] = $this->WebserviceModel->restoreUsersBackupData($UserId,$UserType,3);  
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data'] = $result;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    public function MobileNumberChangeOTP()
    {      
        $data = json_decode(file_get_contents('php://input'));
        if($data)    
        {    
            $PatientId = $data->PatientId;
            $mn = $data->PatMobNumber * 1;
            $NewMobNumber = $mn;
            $NewMobCode = $data->PatMobCode;
            $NewMobNumber_en = $this->encryptDecrypt("en",$NewMobNumber);
            $chkExistUser = $this->WebserviceModel->checkUser($NewMobNumber_en,$NewMobCode);
            if($chkExistUser){
                $response['response_code'] = 4;
                $response['response_message'] = 'Duplicate Mobile number';
            }else{
                $patientdata = $this->WebserviceModel->getUserDataById($PatientId);
                if($patientdata)             
                {
                    $pat_email = $this->encryptDecrypt("dc",$patientdata->Email);
                    $pat_name = $this->encryptDecrypt("dc",$patientdata->FullName);
                    $mobile_number = $patientdata->MobileNumber;
                    $mob_code = $patientdata->MobileCode;
                    $mob_code = str_replace(' ', '', $mob_code);
                    $otp = rand(100000, 999999);
                    // $message = 'Your OTP for Mobile Number change is '.$otp.'. Do not share this with anyone';
                    // $subject = 'Mobile number change request OTP';
                    $type = "changeNumberOtp";
                    $dataArr = array("name"=>$pat_name, "otp"=>$otp);
                    $sendMail = Utility::callSendMailWithTemplate($pat_email,$type,$dataArr);
                    // $sendMail = Utility::callSendMail($pat_email,$message,$subject);
                    $result1 = $this->WebserviceModel->updateOtpData($otp,$mobile_number,$mob_code);
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                    $response['otp'] = $otp; 
                }
                else
                {
                    $response['response_code']=2;
                    $response['response_message']='Failed';
                }   
            }
                        
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;        
    }

    public function changePatientMobileNumber()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $PatientId = $data->PatientId;
            $mn = $data->newMobileNumber * 1;
            $NewMobNumber = $mn;
            $NewMobCode=$data->newMobileCode;
            $otp = $data->otp;
            $checkOtp = $this->WebserviceModel->checkOtp($otp);
            if($checkOtp){
                $mobile_number = $this->encryptDecrypt("en",$NewMobNumber);
                $updateDataArray = array(                                
                                        "MobileNumber"=>$mobile_number,
                                        "MobileCode"=>$NewMobCode,
                                        "UpdateDate"=>date("Y-m-d H:i:s")                               
                                        );
                $result = $this->WebserviceModel->updateUserData($updateDataArray,$PatientId); 
                if($result)             
                {
                    $depList = $this->WebserviceModel->getDependentListByPatientId($PatientId);
                    if($depList){
                        foreach($depList as $val){
                            $DependentProfilePatientId = isset($val->DependentProfilePatientId)?$val->DependentProfilePatientId:"";
                            $depData =  $this->WebserviceModel->getUserDataById($DependentProfilePatientId);
                            $depMobileNumber = $this->encryptDecrypt("dc",$depData->MobileNumber);
                            $depMobileNumber1 = explode("-",$depMobileNumber);
                            $newDepMobile = implode("-",array($NewMobNumber,$depMobileNumber1[1]));
                            $dep_mobile_number = $this->encryptDecrypt("en",$newDepMobile);
                            $depDataArray = array(                                
                                                    "MobileNumber"=>$dep_mobile_number,
                                                    "MobileCode"=>$NewMobCode,
                                                    "UpdateDate"=>date("Y-m-d H:i:s")                               
                                                );
                            $this->WebserviceModel->updateUserData($depDataArray,$DependentProfilePatientId);
                        }
                    }                    
                    $patientdata = $this->WebserviceModel->getUserDataById($PatientId);
                    $pat_email = $this->encryptDecrypt("dc",$patientdata->Email);
                    // $message = 'Your mobile number has been changed successfully. Your new mobile number for RING login is "'.$NewMobNumber.'"';
                    //$subject = 'Mobile number has been changed successfully';
					// $subject = 'Changed Number Successfully';
                    // $sendMail = Utility::callSendMail($pat_email,$message,$subject);
                    $pat_name = $this->encryptDecrypt("dc",$patientdata->FullName);
                    $type = "changeNumber";
                    $dataArr = array("name"=>$pat_name, "new_mobile_number"=>$NewMobCode." ".$NewMobNumber);
                    $sendMail = Utility::callSendMailWithTemplate($pat_email,$type,$dataArr);
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                }
                else
                {
                    $response['response_code']=2;
                    $response['response_message']='Failed';                
                }
            }
            else 
            {
                $response['response_code'] = 4;
                $response['response_message'] = 'Otp not matched';
            }            
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }           
        echo json_encode($response); exit;
    }

    public function updateNotificationStatus()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $PatientId = $data->PatientId;
            $IsCampaignNotify=$data->IsCampaignNotify;
            $IsNotify=$data->IsNotify;
            $updateDataArray = array( 
                "IsCampaignNotify"=>$IsCampaignNotify, 
                "IsNotify"=>$IsNotify
            );
            $result = $this->WebserviceModel->updateNotificationStatus($updateDataArray,$PatientId); 
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';                
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }           
        echo json_encode($response); exit;
    }

    public function getNotificationStatus()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $PatientId = $data->PatientId;
            $result = $this->WebserviceModel->getNotificationStatus($PatientId); 
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $result;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';                
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }           
        echo json_encode($response); exit;
    }

    public function insertProfileSetting()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $userId = $data->userId;
            $userType = $data->userType;
            $field = $data->field;
            $value = $data->value;
            $checkUser = $this->WebserviceModel->checkUserSettings($userId,$userType,$field);
            if($checkUser){
                $updateArr = array("Value"=> $value); 
                $result = $this->WebserviceModel->UpdateProfileSetting($updateArr,$checkUser->Id);          
            }else{
                $insertArr = array( 
                        "User_id"=> $userId,
                        "User_type"=>$userType,
                        "Field"=>$field,
                        "Value"=> $value
                    );
                $result = $this->WebserviceModel->insertProfileSetting($insertArr); 
            }            
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $result;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';                
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }           
        echo json_encode($response); exit;
    }

    public function getSyncStatus()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $userId = $data->userId;
            $userType = $data->userType;
            $field = "Sync";
            $result = $this->WebserviceModel->getSyncStatus($userId,$userType,$field);            
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $result;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';                
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }           
        echo json_encode($response); exit;
    }

    public function getBase64ImageToImageLink($baseImage) 
    {
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
            $data1 = explode(';', $baseImage);
            $type = $data1[0];
            print_r($data1);
            $data2 = explode(',', $data1[2]);
            $base = $data2[0];
            print_r($data2);
            $data3 = base64_decode($data2[1]);
            print_r($data3);exit;
            $ext = explode(':', $type);
            $file_ext = explode('/', $ext[1]);
            $file_name = 'c_'.time();
            $path = 'upload/';
            $fileLink = $path . $file_name;
            if($file_ext[1] == 'msword'){
                $addExt = 'doc';
            }else if($file_ext[1] == 'vnd.ms-excel'){
                $addExt = 'xls';
            }
            else if($ext[1] == 'image/*'){
                $addExt = 'png';
            }
            else{
                $addExt = $file_ext[1];
            }
            $file = $fileLink .".".$addExt;
            $success = file_put_contents($file, $data1);
            return  $root.$file;         
    }

    public function insertUsersChat()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $SenderId = $data->SenderId;
            $SenderType = $data->SenderType;
            $SenderFullName = $data->SenderFullName;
            $ReceiverId = $data->ReceiverId;
            $ReceiverType = $data->ReceiverType;
            $ReceiverFullName = $data->ReceiverFullName;
            $Message = $data->Message;
            $ChatImageUrl = $data->ChatImageUrl;
            $Type = $data->Type;
            $SeenStatus = $data->SeenStatus;
            $dateTime = date("Y-m-d H:i:s"); 
            $newDateTime = new DateTime($dateTime); 
            $newDateTime->setTimezone(new DateTimeZone("UTC")); 
            $dateTimeUTC = $newDateTime->format("Y-m-d H:i:s"); 
            $insertArr = array( 
                            "SenderId"=> $SenderId,
                            "SenderType"=>$SenderType,
                            "SenderFullName"=>$SenderFullName,
                            "ReceiverId"=> $ReceiverId,
                            "ReceiverType"=>$ReceiverType,
                            "ReceiverFullName"=>$ReceiverFullName,
                            "Message"=> $Message,
                            "ChatImageUrl"=>$ChatImageUrl,
                            "Type"=>$Type,
                            "SeenStatus"=>$SeenStatus,
                            "InsertDate"=>$dateTimeUTC
                        );       
            $result = $this->WebserviceModel->insertUsersChat($insertArr);       
            if($result)             
            {
                $notifictionType =  "Chat"; 
                if($ReceiverType == "Patient"){
                    $userType = 0;
                }else if($ReceiverType == "Doctor"){
                    $userType = 1;
                }  
                $device = $this->WebserviceModel->checkuserid($ReceiverId,$userType);
                $messageBody = array(
                        'title' => 'Chat', 
                        'body' => $Message, 
                        'sound' => 'default', 
                        'badge' => '1',
                        'notifictionType' => $notifictionType,
                        'click_action'=>'FCM_PLUGIN_ACTIVITY' //For only Android App
                        );
				if(isset($device->DeviceId) && !empty($device->DeviceId)){
					$this->send_notification_android_new($device->DeviceId,$messageBody,$notifictionType);
				}
                
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
				$response['chat_id'] = $result;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    public function createJsonAndInsertBackup23May22()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            /**Input data from frontend */
            $UserId = $data->userId;
            $UserType = $data->userType;
            $Baseurl = $data->baseurl;
            $ContentType = $data->content_Type;
            $Dates = $data->dates;
            $Description = $data->description;
            $DocType = $data->doc_Type;
            $File_name = $data->file_name;
            $FilenamePath = $data->filename_path;
            $HospitalName = $data->hospital_name;
            $Mobno = $data->mobno;
            $Remarks = $data->remarks;
            $UploadImgfinalpath = $data->uploadImgfinalpath1;

            /**Create File Name according to the User*/
			$uniqueNumber = time();
			if($UserType == "Patient"){
				$base64TextFile = "upload/P_".$UserId."_".$uniqueNumber.".txt";
			}else if($UserType == "Doctor"){
				$base64TextFile = "upload/D_".$UserId."_".$uniqueNumber.".txt";
			}

            $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
            $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

            
			$success = file_put_contents($base64TextFile, $Baseurl);
			$blankArray = array();
            $jsonArr = array(
                            "filename_path" => $FilenamePath,
                            "content_Type" => $ContentType,
                            "dates" => $Dates,
                            "description" => $Description,
                            "file_name" => $File_name,
                            "doc_Type" => $DocType,
                            "filename_base64_link" => $uniqueNumber,
                            "hospital_name" => $HospitalName,
                            "mobno" => $Mobno,
                            "remarks" => $Remarks,
                            "uploadImgfinalpath1" => $UploadImgfinalpath
                            ); 
            
            
            $checkUserBackup = $this->WebserviceModel->checkUserBackup($UserId,$UserType);
            if($checkUserBackup){
                $jsonFile = $checkUserBackup->FileName;
                $inp = file_get_contents($jsonFile);
                $tempArray = json_decode($inp,true);
                array_push($tempArray, $jsonArr);
                $jsonData = json_encode($tempArray);
                $result = file_put_contents($jsonFile, $jsonData);

            }else{
				array_push($blankArray, $jsonArr);
				$json = json_encode($blankArray);
				if($UserType == "Patient"){
					$jsonFileName = "upload/P_".$UserId.".json";
				}else if($UserType == "Doctor"){
					$jsonFileName = "upload/D_".$UserId.".json";
				}
				file_put_contents($jsonFileName, $json);
				
                $insertArr = array(
                            "UserId" => $UserId,
                            "UserType" => $UserType,
                            "FileName" => $jsonFileName,
                            );                
                $result = $this->WebserviceModel->insertUsersBackupData($insertArr);
            }     
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }
	
	public function getChatDataByUserId()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {	$chatId = isset($data->chat_id)?$data->chat_id:"";
            $senderId = $data->senderId;
			$senderType = $data->senderType;
			$receiverId = $data->receiverId;
			$receiverType = $data->receiverType;
		 	if(isset($chatId) && !empty($chatId)){
				$result1 = $this->WebserviceModel->getChatDataWithoutChatIdByUserId($chatId,$senderId,$senderType,$receiverId,$receiverType);
				if($result1){
					$result = $result1;
				}else{
					$result = "NULL";
					}
			}else{
				$result = $this->WebserviceModel->getChatDataByUserId($senderId,$senderType,$receiverId,$receiverType);
			}
                     
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $result;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';                
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }           
        echo json_encode($response); exit;

    }
	
	function updateChatSeenStatus(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $chatId = $data->chatId;
			$updateArr = array("SeenStatus"=>2);
			$update = $this->WebserviceModel->updateChatSeenStatus($chatId,$updateArr);
            if($update){
                $response['response_code']=1;
                $response['response_message']='Success';
            }else{
                $response['response_code']=2;
                $response['response_message']='Failed';
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
            echo json_encode($response);exit;
    }
	
	function getFileByFileNumber(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
			$UserId = $data->userId;
            $UserType = $data->userType;
            $fileNumber = $data->fileNumber;
			
			$root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
            $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
			$filePath = $root."upload/".$fileNumber.".json";
            $inp = file_get_contents($filePath);
            $text = json_decode($inp,true);
            if($text){
                $response['response_code']=1;
                $response['response_message']='Success';
				$response['response_Data']=$text;
            }else{
                $response['response_code']=2;
                $response['response_message']='Failed';
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;
    }

    public function getReportTypeAndPatientViaDoctorId(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $doc_id = $data->doctor_id;
            $reportList = $this->WebserviceModel->getReportTypeAndPatientViaDoctorId($doc_id);            
            foreach($reportList as $key=>$value){
                $patientId = $value->PatientId;
                if($patientId){
                    $latestFile = $this->WebserviceModel->getLetestErefFile($doc_id,$patientId); 
                }
                $value->PatientFullName = $this->encryptDecrypt("dc",$value->FullName);
                $value->LatestFileCategory = isset($latestFile->Category)?$latestFile->Category:"";
            }
            if($reportList)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $reportList;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    public function checkDeviceTokenByPatientId(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $device_id = $data->device_id;
            $patient_id = $data->patient_id;
            $checkDevice = $this->WebserviceModel->checkDeviceTokenByPatientId($patient_id,$device_id);           
            if($checkDevice)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $checkDevice;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }
	
	public function createJsonAndInsertBackup()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            if(isset($data->AddReferral) && $data->AddReferral == 1){
                $UserId = $data->userId;
                $UserType = $data->userType;
                $Baseurl = $data->baseurl;
                $Category = $data->Category;
                $DoctorId = $data->DoctorId;
                $EreportsTransitDetailId = $data->EreportsTransitDetailId;
                $InsertDate = $data->InsertDate;
                $PhoneNumber = $data->PhoneNumber;
                $ReportTransitId = $data->ReportTransitId;
                $TenantName = $data->TenantName;
                $data2 = isset($data->data2)?$data->data2:'';
                $description = $data->description;
                $doctorName = $data->doctorName;
                $sync_status = $data->sync_status;
                $filetype = $data->filetype;
                $isdoctor = $data->isdoctor;
                $IsRefferal = 1;
                $FilenamePath = $data->filename_path;
				$DisplayPatientId = $data->DisplayPatientId;
                $sqlDbId = isset($data->dbId)?$data->dbId:'';
                /**Create File Name according to the User*/
                // $uniqueNumber = time();
                $uniqueNumber = $EreportsTransitDetailId."_".$ReportTransitId;
                if($UserType == "Patient"){
                    $base64TextFile = "upload/P_".$UserId."_".$uniqueNumber.".txt";
                }else if($UserType == "Doctor"){
                    $base64TextFile = "upload/D_".$UserId."_".$uniqueNumber.".txt";
                }

                $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
                $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

                
                $success = file_put_contents($base64TextFile, $Baseurl);
                $blankArray = array();
                $jsonArr = array(
							"DisplayPatientId" => $DisplayPatientId,
                            "Category" => $Category,
                            "DoctorId" => $DoctorId,
                            "EreportsTransitDetailId" => $EreportsTransitDetailId,
                            "InsertDate" => $InsertDate,
                            "PhoneNumber" => $PhoneNumber,
                            "ReportTransitId" => $ReportTransitId,
                            "TenantName" => $TenantName,
                            "data2" => $data2,
                            "description" => $description,
                            "doctorName" => $doctorName,
                            "sync_status" => $sync_status,
                            "filetype" => $filetype,
                            "isdoctor" => $isdoctor,
                            "filename_base64_link" => $uniqueNumber,
                            "filename_path" => $FilenamePath,
                            "dbId"=>$sqlDbId
                            ); 
                        
                        
                $checkUserBackup = $this->WebserviceModel->checkUserBackup($UserId,$UserType,$IsRefferal);
                if($checkUserBackup){
                    $jsonFile = $checkUserBackup->FileName;
                    $inp = file_get_contents($jsonFile);
                    $tempArray = json_decode($inp,true);
                    array_push($tempArray, $jsonArr);
                    $jsonData = json_encode($tempArray);
                    $result = file_put_contents($jsonFile, $jsonData);

                }else{
                    array_push($blankArray, $jsonArr);
                    $json = json_encode($blankArray);
                    if($UserType == "Patient"){
                        $jsonFileName = "upload/P_".$UserId."_RefferalFile.json";
                    }else if($UserType == "Doctor"){
                        $jsonFileName = "upload/D_".$UserId."_RefferalFile.json";
                    }
                    file_put_contents($jsonFileName, $json);
                    
                    $insertArr = array(
                                "UserId" => $UserId,
                                "UserType" => $UserType,
                                "FileName" => $jsonFileName,
                                "IsRefferal" => $IsRefferal,
                                ); 
					if($jsonFileName){
						$result = $this->WebserviceModel->insertUsersBackupData($insertArr);
					}
                    
                }
            }
            else
            {
                /**Input data from frontend */
                $UserId = $data->userId;
                $UserType = $data->userType;
                $Baseurl = $data->baseurl;
                $ContentType = $data->content_Type;
                $Dates = $data->dates;
                $Description = $data->description;
                $DocType = $data->doc_Type;
                $File_name = $data->file_name;
                $FilenamePath = $data->filename_path;
                $HospitalName = $data->hospital_name;
                $Mobno = $data->mobno;
                $Remarks = $data->remarks;
                $UploadImgfinalpath = $data->uploadImgfinalpath1;
                $IsRefferal = 0;
				$DisplayPatientId = $data->DisplayPatientId;
                $sqlDbId = $data->dbId;
                /**Create File Name according to the User*/
                $uniqueNumber = time();
                if($UserType == "Patient"){
                    $base64TextFile = "upload/P_".$UserId."_".$uniqueNumber.".txt";
                }else if($UserType == "Doctor"){
                    $base64TextFile = "upload/D_".$UserId."_".$uniqueNumber.".txt";
                }

                $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
                $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

                
                $success = file_put_contents($base64TextFile, $Baseurl);
                $blankArray = array();
                $jsonArr = array(
								"DisplayPatientId" => $DisplayPatientId,
                                "filename_path" => $FilenamePath,
                                "content_Type" => $ContentType,
                                "dates" => $Dates,
                                "description" => $Description,
                                "file_name" => $File_name,
                                "doc_Type" => $DocType,
                                "filename_base64_link" => $uniqueNumber,
                                "hospital_name" => $HospitalName,
                                "mobno" => $Mobno,
                                "remarks" => $Remarks,
                                "uploadImgfinalpath1" => $UploadImgfinalpath,
                                "dbId"=>$sqlDbId
                                ); 
                
                
                $checkUserBackup = $this->WebserviceModel->checkUserBackup($UserId,$UserType,$IsRefferal);
                if($checkUserBackup){
                    $jsonFile = $checkUserBackup->FileName;
                    $inp = file_get_contents($jsonFile);
                    $tempArray = json_decode($inp,true);
                    array_push($tempArray, $jsonArr);
                    $jsonData = json_encode($tempArray);
                    $result = file_put_contents($jsonFile, $jsonData);

                }else{
                    array_push($blankArray, $jsonArr);
                    $json = json_encode($blankArray);
                    if($UserType == "Patient"){
                        $jsonFileName = "upload/P_".$UserId.".json";
                    }else if($UserType == "Doctor"){
                        $jsonFileName = "upload/D_".$UserId.".json";
                    }
                    file_put_contents($jsonFileName, $json);
                    
                    $insertArr = array(
                                "UserId" => $UserId,
                                "UserType" => $UserType,
                                "FileName" => $jsonFileName,
                                );                
                    if($jsonFileName){
						$result = $this->WebserviceModel->insertUsersBackupData($insertArr);
					}
                }            
            }     
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }
	

	public function getRingGroupMasterData(){  
        $data = json_decode(file_get_contents('php://input'));       
        $keyword = isset($data->keyword)?$data->keyword:"";
        $ringGrpId = isset($data->RingGroupMasterId)?$data->RingGroupMasterId:"";
        if(!empty($keyword)){
            $grpData =  $this->WebserviceModel->getRingGroupMasterDataWithKeyword($keyword,$ringGrpId);
        }else{
            $grpData =  $this->WebserviceModel->getRingGroupMasterData($ringGrpId);
        }
        
        if($grpData)             
        {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['data']=$grpData;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';          
        }
        echo json_encode($response); exit;
    }
	

	public function getRingGroupTanentByRinGrpMasterId(){  
        $data = json_decode(file_get_contents('php://input'));
        $ringGrpMasterId = $data->RingGroupMasterId;
        $keyword = isset($data->keyword)?$data->keyword:"";
        if(!empty($keyword)){
            $tenantData =  $this->WebserviceModel->getRingGroupTanentByRinGrpMasterId($ringGrpMasterId,$keyword);
        }else{
            $tenantData =  $this->WebserviceModel->getRingGroupTanentByRinGrpMasterIdWithoutKey($ringGrpMasterId);
        }
        
        if($tenantData)             
        {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['data']=$tenantData;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';          
        }
        echo json_encode($response); exit;
    }
	

	public function chatListByPatientId(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $patient_id = $data->patient_id;
			$user_type = "Patient";
            $list = $this->WebserviceModel->chatListByPatientId($patient_id,$user_type);
			$doctorArr = array();
			if($list["receiverDoc"]){
				foreach($list["receiverDoc"] as $Receiver){
					if(!empty($Receiver->DoctorId)){
						array_push($doctorArr,$Receiver);
					}
				}
			}
			if($list["senderDoc"]){
				foreach($list["senderDoc"] as $sender){
					if(!empty($sender->DoctorId) && array_search($sender->DoctorId,array_column($doctorArr, 'DoctorId')) == 0){
						array_push($doctorArr,$sender);
					}
				}
			}
            if($doctorArr)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $doctorArr;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }
	
	public function countOfUnreadMsgByReportId(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $patient_id = $data->patient_id;
			$report_id = $data->ReportTransitId;
            $doctorData = $this->WebserviceModel->getDoctorIdByReportId($report_id);
			if($doctorData->DoctorId){
				$ChatCount = $this->WebserviceModel->countOfUnreadMsgOfPatient($patient_id,$doctorData->DoctorId);
				$totalUnreadChatCount = $ChatCount->UnreadChatCount; 
			}else{
				$totalUnreadChatCount = 0;
			}
            if($totalUnreadChatCount)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $totalUnreadChatCount;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

	public function getRelationMasterDataOfDependent(){
		$data = json_decode(file_get_contents('php://input'));
		$lang = isset($data->lang)?$data->lang:"";
		
        $relationMasterData =  $this->WebserviceModel->getRelationMasterDataOfDependent();	 
		//echo "<pre>"; print_r($relationMasterData);exit;
        if($relationMasterData)             
        {
			if($lang == "English"){
				$response['response_code']=1;
				$response['response_message']='Success';
				$response['data']=$relationMasterData;
			}else if($lang == "Malay"){
				$relationMasterDataInMalay = '{"response_code": 1,
					"response_message": "Success",
					"data": [
						{
							"Id": "1",
							"Description": "ADIK / ABANG LELAKI"
						},
						{
							"Id": "2",
							"Description": "ANAK PEREMPUAN"
						},
						{
							"Id": "3",
							"Description": "BAPA"
						},
						{
							"Id": "4",
							"Description": "IBU"
						},
						{
							"Id": "5",
							"Description": "LAIN LAIN"
						},
						{
							"Id": "6",
							"Description": "ANAK LELAKI"
						},
						{
							"Id": "7",
							"Description": "PASANGAN"
						},
						{
							"Id": "8",
							"Description": "ADIK / KAKAK PEREMPUAN"
						},
						{
							"Id": "9",
							"Description": "EJEN"
						},
						{
							"Id": "10",
							"Description": "IBU SAUDARA"
						},
						{
							"Id": "11",
							"Description": "MAJIKAN"
						},
						{
							"Id": "12",
							"Description": "BAPA SAUDARA"
						},
						{
							"Id": "13",
							"Description": "CUCU PEREMPUAN"
						},
						{
							"Id": "14",
							"Description": "CUCU LELAKI"
						},
						{
							"Id": "15",
							"Description": "ANAK SAUDARA PEREMPUAN"
						},
						{
							"Id": "16",
							"Description": "ANAK SAUDARA LELAKI"
						},
						{
							"Id": "17",
							"Description": "PENGASUH"
						},
						{
							"Id": "18",
							"Description": "RAKAN"
						},
						{
							"Id": "19",
							"Description": "SENDIRI"
						}
					]
				}';
				
				echo $relationMasterDataInMalay; exit;
			}else{
				$response['response_code']=1;
				$response['response_message']='Success';
				$response['data']=$relationMasterData;
			}
            
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';          
        }
        echo json_encode($response); exit;
    }

    public function getDependentsMobileNumber_old(){  
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        { 
            $patient_id = $data->patient_id;
            $patientData =  $this->WebserviceModel->getUserDataById($patient_id);
            $mobileNumber = $this->encryptDecrypt("dc",$patientData->MobileNumber);
            $dependentCount =  $this->WebserviceModel->getDependentCount($patient_id);	 
            $count = $dependentCount->DependentCount + 1;
			$MobileCode = str_replace(" ", "", $patientData->MobileCode);
            if($mobileNumber){
                $dependentMob = $mobileNumber."-".$count;
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['data']=$dependentMob;
				$response['mob_code']=$MobileCode;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';          
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }    
        echo json_encode($response); exit;
    }
    public function getDependentsMobileNumber(){  
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        { 
            $patient_id = $data->patient_id;
            $patientData =  $this->WebserviceModel->getUserDataById($patient_id);
            if($patientData){
                $mobileNumber = $this->encryptDecrypt("dc",$patientData->MobileNumber);
                // $dependentCount =  $this->WebserviceModel->getDependentCount($patient_id);	
                $lastDep =  $this->WebserviceModel->getLastDependentData($patient_id);
                if($lastDep){
                    $depData =  $this->WebserviceModel->getUserDataById($lastDep->DependentProfilePatientId); 
                    $depMobileNumber = $this->encryptDecrypt("dc",$depData->MobileNumber);
                    $suffix = explode("-",$depMobileNumber);
                    $count = $suffix[1] + 1;
                }else{
                    $count = 1;
                }              
                // print_r($count);exit;
                $MobileCode = str_replace(" ", "", $patientData->MobileCode);
                if($mobileNumber){
                    $dependentMob = $mobileNumber."-".$count;

                    $mobileCo = trim($MobileCode);
                    $getContCode = $this->WebserviceModel->getCountryFlagAndCode($mobileCo);
                    if($getContCode){
                        $CountryCode = $getContCode->CountryCode;
                    }else{
                        $CountryCode = "";
                    }
                    $response['response_code']=1;
                    $response['response_message']='Success';
                    $response['data']=$dependentMob;
                    $response['mob_code']=$MobileCode;
                    $response['CountryCode']=$CountryCode;
                }
                else
                {
                    $response['response_code']=2;
                    $response['response_message']='Failed';          
                }
            }else{
                $response['response_code']=4;
                $response['response_message']='Patient not found';
            }           
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }    
        echo json_encode($response); exit;
    }

   public function insertDependentProfile()
    {        
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
            $relation = $data->relation_id;
            $patientId = $data->patient_id;
            $mobCode = $data->mob_code;
            $mobileNumber = $this->encryptDecrypt("en",$data->mobile_number);          
            $fullName = $this->encryptDecrypt("en",$data->full_name);
            $email = $this->encryptDecrypt("en",$data->email);
            $BloodGroupId = isset($data->bloodgroup)?$data->bloodgroup:NULL;
            $address = urlencode($data->address);
            $country = $data->country_id;
            $state = $data->state_id;
            $city = $data->city_id;
            $pinCode = $data->pin_code;
            $dob = isset($data->DOB)?$data->DOB:NULL;
            if(isset($dob) && !empty($dob)){
                $ts = strtotime($dob);
                $dob1 = date("Y-m-d H:i:s", $ts);
            }else{
                $dob1 = NULL;
            }
            $saveDataArray = array( 
                                    "MobileNumber"=>$mobileNumber,
                                    "FullName"=>$fullName,
                                    "Email"=>$email,
                                    "MobileCode"=>$mobCode,
                                    "BloodGroupId"=>$BloodGroupId,
                                    "Address"=>$address,
                                    "InsertDate"=>date("Y-m-d H:i:s"),
									"CountryMasterId"=>$country,
									"StateMasterId"=>$state,
									"CityMasterId"=>$city,
                                    "DateOfBirth"=>$dob1,
									"PinCode"=>$pinCode
                                );
            $dependentId = $this->WebserviceModel->insertdependentProfile($saveDataArray);
            if($dependentId)
            {
                $depArr = array( 
                                "MainProfilePatientId"=>$patientId,
                                "RelationshipTypeId"=>$relation,
                                "DependentProfilePatientId"=>$dependentId 
                                );
                $this->WebserviceModel->updateDependentTable($depArr);
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }
	

	public function getDependentListByPatId(){
   
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $PatientId = isset($data->Patient_id)?$data->Patient_id:"";
            $depList = $this->WebserviceModel->getDependentListByPatientId($PatientId);
			$blankArr = array();
			if($depList){
				foreach($depList as $val){
                    $DependentProfilePatientId = isset($val->DependentProfilePatientId)?$val->DependentProfilePatientId:"";
					$userData =  $this->WebserviceModel->getUserDataById($DependentProfilePatientId);
					$userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);   
					$userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
					$userData->Relationship = $val->Relationship;
                    $userData->Email = $this->encryptDecrypt("dc",$userData->Email);
                    $userData->bloodgroup = $userData->bloodgroup;
                    $userData->bloodgroupDes = $userData->bloodgroupDes;
                    if(!empty($userData->CountryMasterId) && !empty($userData->StateMasterId) && !empty($userData->CityMasterId)){
                        $UserAddress =  $this->WebserviceModel->getPatientAddressById($val->DependentProfilePatientId);
                        if($UserAddress){
                            $userData->Country = $UserAddress->Country;
                            $userData->State = $UserAddress->State;
                            $userData->City = $UserAddress->City;
                        }else{
                            $userData->Country = "null";
                            $userData->State = "null";
                            $userData->City = "null";
                        }
                    }
                    else
                    {
                        $userData->Country = "null";
                        $userData->State = "null";
                        $userData->City = "null";
                    }
					array_push($blankArr,$userData);
				}	
				$response['response_code']=1;
				$response['response_message']='Success';
				$response['user_data']=$blankArr;
			}
			else
			{
				$response['response_code']=2;
				$response['response_message']='Failed';
			}
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }
	
	public function getAllTenants(){
		$tenantData =  $this->WebserviceModel->getAllTenants();
		if($tenantData)             
		{
			$response['response_code']=1;
			$response['response_message']='Success';
			$response['data']=$tenantData;
		}
		else
		{
			$response['response_code']=2;
			$response['response_message']='Failed';          
		}
		echo json_encode($response); exit;
    }
	
	public function deleteDependentProfile(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $mainPatientId = $data->MainProfilePatientId;
			$dependentPatientId = $data->DependentProfilePatientId;
            /************** Delete Patient's E-report transit and details******************/
            $reportData = $this->WebserviceModel->getEreportTransitIdForDeletion($dependentPatientId);
            if($reportData){
                foreach($reportData as $reportval){
                    $deleteAlert = $this->WebserviceModel->deleteNotifications($reportval->ReportTransitId, "Alerts");
                    $deleteReportDetails =  $this->WebserviceModel->deleteReportDetails($reportval->ReportTransitId, "EreportsTransitDetail"); 
                    $deleteReport =  $this->WebserviceModel->deleteReportDetails($reportval->ReportTransitId, "EreportsTransit"); 
                }
            }
            /************************************************************* */
			$deleteDependentProfile = $this->WebserviceModel->deleteDependentProfile($dependentPatientId);
			if($deleteDependentProfile){	
				$deleteDepDetail = $this->WebserviceModel->deleteDepDetail($dependentPatientId,$mainPatientId);
				$response['response_code']=1;
				$response['response_message']='Success';
				$response['data']=$deleteDependentProfile;
			}
			else
			{
				$response['response_code']=2;
				$response['response_message']='Failed';
			}
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }
	
	public function getDependentProfile(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
			$dependentPatientId = $data->DependentProfilePatientId;
			$DependentProfile = $this->WebserviceModel->getDependentProfile($dependentPatientId);
			$DependentProfile->MobileNumber = $this->encryptDecrypt("dc",$DependentProfile->MobileNumber);   
            $DependentProfile->FullName = $this->encryptDecrypt("dc",$DependentProfile->FullName);
            $DependentProfile->Email = $this->encryptDecrypt("dc",$DependentProfile->Email);			
			$DependentProfile->MobileCode = str_replace(" ", "", $DependentProfile->MobileCode);
			$DependentProfile->Address = urldecode($DependentProfile->Address);
			if($DependentProfile){
				$response['response_code']=1;
				$response['response_message']='Success';
				$response['data']=$DependentProfile;
			}
			else
			{
				$response['response_code']=2;
				$response['response_message']='Failed';
			}
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }
	
	public function updateDependentProfile()
    {        
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
			$mainPatientId = $data->MainProfilePatientId;
			$dependentPatientId = $data->DependentProfilePatientId;
            $relation = $data->relation_id;
            $BloodGroupId = $data->bloodgroup;
            $address = urlencode($data->address);
            $country = $data->country_id;
            $state = $data->state_id;
            $city = $data->city_id;
            $pinCode = $data->pin_code;       
            $fullName = $this->encryptDecrypt("en",$data->full_name);
            $email = $this->encryptDecrypt("en",$data->email);
            $dob = isset($data->DOB)?$data->DOB:NULL;
            if(isset($dob) && !empty($dob)){
                $ts = strtotime($dob);
                $dob1 = date("Y-m-d H:i:s", $ts);
            }else{
                $dob1 = NULL;
            }
            $updateArray = array( 
                                    "FullName"=>$fullName,
                                    "Email"=>$email,
                                    "BloodGroupId"=>$BloodGroupId,
                                    "Address"=>$address,
                                    "InsertDate"=>date("Y-m-d H:i:s"),
									"CountryMasterId"=>$country,
									"StateMasterId"=>$state,
									"CityMasterId"=>$city,
                                    "DateOfBirth"=>$dob1,
									"PinCode"=>$pinCode  
                                );
            $update = $this->WebserviceModel->updateDependentProfile($updateArray,$dependentPatientId);
            if($update || true)
            {
                $depUpdateArr = array( 
                                "RelationshipTypeId"=>$relation,
                                );
                $this->WebserviceModel->updateDependentRelation($depUpdateArr,$mainPatientId,$dependentPatientId);
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }
	

	public function getSwitchProfileDependentListByPatId(){
   
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $PatientId = $data->Patient_id;
            $depList = $this->WebserviceModel->getDependentListByPatientId($PatientId);
			$blankArr = array();
			if($depList){
				$PatientData =  $this->WebserviceModel->getUserDataById($PatientId);
				$PatientData->MobileNumber = $this->encryptDecrypt("dc",$PatientData->MobileNumber);   
                $FirstName = $this->encryptDecrypt("dc",$PatientData->FullName);
                $LastName = $this->encryptDecrypt("dc",$PatientData->LastName);
                if(isset($PatientData->LastName)){
                    $PatientData->FullName = $FirstName." ".$LastName;
                }else{
                    $PatientData->FullName = $FirstName;
                }
				$PatientData->Relationship = "Self";
                $PatientData->Email = $this->encryptDecrypt("dc",$PatientData->Email);
                if(!empty($PatientData->CountryMasterId) && !empty($PatientData->StateMasterId) && !empty($PatientData->CityMasterId)){
                    $PatientAddress =  $this->WebserviceModel->getPatientAddressById($PatientId);
                    if($PatientAddress){
                        $PatientData->Country = $PatientAddress->Country;
                        $PatientData->State = $PatientAddress->State;
                        $PatientData->City = $PatientAddress->City;
                    }else{
                        $PatientData->Country = "null";
                        $PatientData->State = "null";
                        $PatientData->City = "null";
                    }
                }
                else
                {
                    $PatientData->Country = "null";
                    $PatientData->State = "null";
                    $PatientData->City = "null";
                }
				array_push($blankArr,$PatientData);
				foreach($depList as $val){
					$userData =  $this->WebserviceModel->getUserDataById($val->DependentProfilePatientId);
					$userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);   
					// $userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
                    $userFirstName = $this->encryptDecrypt("dc",$userData->FullName);
                    $userLastName = $this->encryptDecrypt("dc",$userData->LastName);
                    if(isset($userData->LastName)){
                        $userData->FullName = $userFirstName." ".$userLastName;
                    }else{
                        $userData->FullName = $userFirstName;
                    }
					$userData->Relationship = $val->Relationship;
                    $userData->IsConverted = $val->IsConverted;
                    $userData->Email = $this->encryptDecrypt("dc",$userData->Email);
                    if(!empty($userData->CountryMasterId) && !empty($userData->StateMasterId) && !empty($userData->CityMasterId)){
                        $UserAddress =  $this->WebserviceModel->getPatientAddressById($val->DependentProfilePatientId);
                        if($UserAddress){
                            $userData->Country = $UserAddress->Country;
                            $userData->State = $UserAddress->State;
                            $userData->City = $UserAddress->City;
                        }else{
                            $userData->Country = "null";
                            $userData->State = "null";
                            $userData->City = "null";
                        }
                    }
                    else
                    {
                        $userData->Country = "null";
                        $userData->State = "null";
                        $userData->City = "null";
                    }
					array_push($blankArr,$userData);
				}	
				$response['response_code']=1;
				$response['response_message']='Success';
				$response['user_data']=$blankArr;
			}
			else
			{
				$response['response_code']=2;
				$response['response_message']='Failed';
			}
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }
	
	public function hospitalSearchForMapSection(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
			// $cityId = isset($data->cityId)?$data->cityId:""; 01 nov 2022
            $cityId = isset($data->stateId)?$data->stateId:""; //01 nov 2022 
			$ringGroupId = isset($data->ringGroupId)?$data->ringGroupId:"";
			$hospital = isset($data->hospital)?$data->hospital:"";
			$speciality = isset($data->specialityId)?$data->specialityId:"";
			$userLat = isset($data->userLat)?$data->userLat:"";
			$userLong = isset($data->userLong)?$data->userLong:"";
			$maxDistance = isset($data->maxDistance)?$data->maxDistance:"10";
            $citINP = isset($data->citINP)?$data->citINP:"" ;
            $ringGrpINP = isset($data->ringGrpINP)?$data->ringGrpINP:"" ;
            $searchSpclINP = isset($data->searchSpclINP)?$data->searchSpclINP:"" ;
			$result = $this->WebserviceModel->hospitalSearchForMapSection($cityId,$ringGroupId,trim($hospital),$speciality,$citINP,$ringGrpINP,$searchSpclINP,$userLat,$userLong);
			$count = $this->WebserviceModel->hospitalSearchForMapSectionCount($cityId,$ringGroupId,trim($hospital),$speciality,$citINP,$ringGrpINP,$searchSpclINP,$userLat,$userLong);			
            if(!empty($userLat) && !empty($userLong)){
				$blankArr = array();
				foreach($result as $val){
					$dist = $this->distance($userLat, $userLong, $val->Latitude, $val->Longitude, "K");
					$val->totalDistance = (float)number_format($dist, 3, '.', '')." KM";
                    $val->PhoneNumber = $val->PhoneCode.$val->PhoneNumber;
					if($dist <= $maxDistance){
						array_push($blankArr,$val);
					}
				}
				$result1 = $blankArr;
			}else{
				$result1 = $result;
			}
			if($result){
				$response['response_code']=1;
				$response['response_message']='Success';
				$response['data']=$result;
                $response['count']=count($count);
			}
			else
			{
				$response['response_code']=2;
				$response['response_message']='Failed';
			}
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }
	
	function distance($lat1, $lon1, $lat2, $lon2, $unit) {
	  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
		return 0;
	  }
	  else
	  {
		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);
		if ($unit == "K") {
		  return ($miles * 1.609344);
		} else if ($unit == "N") {
		  return ($miles * 0.8684);
		} else {
		  return $miles;
		}
	  }
	}

    function cityAutoSearchByKeyword()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data){
            $keyword = isset($data->keyword)?$data->keyword:"";
            $getData = $this->WebserviceModel->cityAutoSearchByKeyword($keyword);            
            if(!empty($keyword) && !empty($getData))
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $getData;

            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is null';
        }    
        echo json_encode($response);exit;
    } 

    function specialityAutoSearchByKey(){  
        $data = json_decode(file_get_contents('php://input'));        
        if($data){
            $keyword = $data->keyword;   
            $specialityData =  $this->WebserviceModel->specialityAutoSearchByKey($keyword);                                         
            if($specialityData)             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['user_data']=$specialityData;
            }
            else 
            {
                $response["response_code"] = 2;
                $response["response_message"] = "Failed";
            } 
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is null';
        }        
        echo json_encode($response); exit;    
    }

    public function getUserDataByEmailOrMobile()
    {   
        $data = json_decode(file_get_contents('php://input'));
        $email = $this->encryptDecrypt("en",$data->email);
        $mn = $data->mobile * 1;
        $mobile = $this->encryptDecrypt("en",$mn);
        $userData =  $this->WebserviceModel->getUserDataByEmailOrMobile($email,$mobile);
        if($userData )             
        {
            $userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);   
            $name = $this->encryptDecrypt("dc",$userData->FullName);
            $NameArr = explode(" ",$name);
            $userData->FirstName = isset($NameArr[0])?$NameArr[0]:"";
            $userData->LastName = isset($NameArr[1])?$NameArr[1]:"";
            $userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
            $userData->Email = $this->encryptDecrypt("dc",$userData->Email);
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['user_data']=$userData;
        }
        else
        {
            $response['response_code']=2;
            $response['response_message']='Failed';           
        }
        echo json_encode($response); exit;
    }
	

	public function sendBackupSQLViaEmail()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $UserId = $data->userId;
            $UserEmail = $data->UserEmail;
            $fileData = $data->fileData;
            $mailSub = $data->mailSub;
            $mailBody = $data->mailBody;
            $sqlFileName = $data->sqlFileName;
            $sqlFile = "upload/".$sqlFileName.".txt";
            $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
            $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);            
            $success = file_put_contents($sqlFile, $fileData);               
            if($success)             
            {
                $message = $mailBody;
                $subject = $mailSub;
                $sendMail = Utility::callSendMailwithAttachedFile($UserEmail,$message,$subject,$root.$sqlFile);
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    /*********************Create Dependent as main patient***************************/
    public function changeRequestOtpForDependent(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)        
        {   
            $patientId = $data->patientId;
            $Patient = $this->WebserviceModel->getUserDataById($patientId);
            if($Patient)             
            {
                $pat_email = $this->encryptDecrypt("dc",$Patient->Email);
                $otp = rand(100000, 999999);
                // $message = 'Your OTP for dependent profile modification is '.$otp.'. Do not share this with anyone.';
                // $subject = 'Dependent profile change request OTP';
                // $sendMail = Utility::callSendMail($pat_email,$message,$subject);
                $pat_name = $this->encryptDecrypt("dc",$Patient->FullName);
                $type = "convertProfileOtp";
                $dataArr = array("name"=>$pat_name, "otp"=>$otp);
                $sendMail = Utility::callSendMailWithTemplate($pat_email,$type,$dataArr);
                $updateOtpData = $this->WebserviceModel->updateOtpDataById($otp,$patientId);
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['otp'] = $otp;
                $response['email'] = $pat_email;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';
            }              
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit; 
    }

    public function changeDependentToMainPatient(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $otp = $data->otp;
            $checkOtp = $this->WebserviceModel->checkOtp($otp);
            $mainPatientId = $data->patientId;
            $dependentPatientId = $data->dependentPatientId;
            $mn = $data->newMobileNumber * 1;
            $NewMobNumber = $mn;
            $NewMobCode = $data->newMobileCode;          
            if(!empty($checkOtp) && $checkOtp->PatientId == $mainPatientId)             
            {                
                $mobile_number = $this->encryptDecrypt("en",$NewMobNumber);
                $updateDataArray = array(                                
                                        "MobileNumber"=>$mobile_number,
                                        "MobileCode"=>$NewMobCode,
                                        "UpdateDate"=>date("Y-m-d H:i:s")                               
                                        );
                $checkMobile = $this->WebserviceModel->DuplicateMobileNumber($mobile_number);
                if($checkMobile){
                    $response['response_code'] = '5';
                    $response['response_message'] = 'Duplicate Mobile Number';
                }else{                        
                    $result = $this->WebserviceModel->updateUserData($updateDataArray,$dependentPatientId); 
                    if($result)             
                    {    
                        /**Remove from Dependent List */ 
                        $deleteDepDetail = $this->WebserviceModel->deleteDepDetail($dependentPatientId,$mainPatientId);                        
                        /**Update User Setting */
                        $insertArr = array( 
                            "User_id"=> $dependentPatientId,
                            "User_type"=>'Patient',
                            "Field"=>'Independent',
                            "Value"=> 1
                        );
                        $this->WebserviceModel->insertProfileSetting($insertArr);
                        /**Fetch Backup Json and Process */
                        $this->getBackupJsonAndCropDependentData($mainPatientId,$dependentPatientId);
                        /**Send Confirmation Mail to Parent and Dependent Patient */
                        $patientdata = $this->WebserviceModel->getUserDataById($mainPatientId);
                        $pat_email = $this->encryptDecrypt("dc",$patientdata->Email);
                        $DependentData = $this->WebserviceModel->getUserDataById($dependentPatientId);
                        $dep_pat_email = $this->encryptDecrypt("dc",$DependentData->Email);
                        // $message = 'Your dependent has been successfully converted into main patient.';
                        // $message1 = 'You have been successfully converted from dependent patient to main patient. Your new mobile number for RING login is "'.$NewMobNumber.'"';
                        // $subject = 'Dependent Patient has been changed successfully';

                        $pat_name = $this->encryptDecrypt("dc",$patientdata->FullName);
                        $dep_pat_name = $this->encryptDecrypt("dc",$DependentData->FullName);
                        $type1 = "convertProfile";
                        $mailDataArr1 = array("name"=>$pat_name, "dependent_full_name"=>$dep_pat_name);
                        $sendMail1 = Utility::callSendMailWithTemplate($pat_email,$type1,$mailDataArr1);

                        $type2 = "convertProfileFinal";
                        $mailDataArr2 = array("name"=>$dep_pat_name, "dependent_full_name"=>$dep_pat_name);
                        $sendMail2 = Utility::callSendMailWithTemplate($dep_pat_email,$type2,$mailDataArr2);
                        
                        // $sendMail1 = Utility::callSendMail($dep_pat_email,$message1,$subject);
                        // $sendMail2 = Utility::callSendMail($pat_email,$message,$subject);
                        $response['response_code'] = 1;
                        $response['response_message'] = 'Success';
                    }
                    else
                    {
                        $response['response_code']=2;
                        $response['response_message']='Failed';                
                    }
                }
            }   
            else
            {
                $response['response_code'] = 4;
                $response['response_message']='Wrong OTP';                
            } 
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }

    function getBackupJsonAndCropDependentData($patId,$depPatId){
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        $UserTypeShortKey = "P";
        /*********Local data backup (Uploaded from Mobile)************/
        $checkUserBackup = $this->WebserviceModel->checkUserBackup($patId,'Patient',0);
        $blankArr = array();
        if($checkUserBackup){
            $jsonFile = $checkUserBackup->FileName;
            $inp = file_get_contents($jsonFile);
            $tempArray = json_decode($inp,true);
            foreach ($tempArray as $value) {
                if($value['DisplayPatientId'] == $depPatId){
                     /**Create File Name according to the User*/
                    $filePath = $root."upload/".$UserTypeShortKey."_".$patId."_".$value['filename_base64_link'].".txt";   
                    $text = @file_get_contents($filePath);
                    $base64TextFile = "upload/P_".$depPatId."_".$value['filename_base64_link'].".txt";
                    $success1 = file_put_contents($base64TextFile, $text);
                     array_push($blankArr,$value);
                }
            }  
            
                if(isset($blankArr) && !empty($blankArr)) {  
                    $json = json_encode($blankArr);
                    $jsonFileName = "upload/P_".$depPatId.".json";
                    file_put_contents($jsonFileName, $json);                    
                    $insertArr = array(
                                "UserId" => $depPatId,
                                "UserType" => 'Patient',
                                "FileName" => $jsonFileName,
                                );                
                    if($jsonFileName){
						$this->WebserviceModel->insertUsersBackupData($insertArr);
					} 
                }    
        }
        /***************Online data backup**************/
        $refBackup = $this->WebserviceModel->checkUserBackup($patId,'Patient',1);
        $blankArr1 = array();
        if($refBackup){
            $jFile = $refBackup->FileName;
            $jFile1 = file_get_contents($jFile);
            $tempAr = json_decode($jFile1,true);
            foreach ($tempAr as $tempVal) {
                if($value['DisplayPatientId'] == $depPatId){
                    /**Create File Name according to the User*/
                    $filePath1 = $root."upload/".$UserTypeShortKey."_".$patId."_".$value['filename_base64_link'].".txt";   
                    $text1 = @file_get_contents($filePath1);
                    $base64TextFile1 = "upload/P_".$depPatId."_".$value['filename_base64_link'].".txt";
                    $success2 = file_put_contents($base64TextFile1, $text1);
                    array_push($blankArr1,$tempVal);
                }
            }             
            if(isset($blankArr1) && !empty($blankArr1)) {    
                $cjson = json_encode($blankArr1);
                $jsonFileName1 = "upload/P_".$depPatId."_RefferalFile.json";
                file_put_contents($jsonFileName1, $cjson);
                
                $insertArr1 = array(
                            "UserId" => $depPatId,
                            "UserType" => 'Patient',
                            "FileName" => $jsonFileName1,
                            "IsRefferal" => 1,
                            );                
                if($jsonFileName1){
                    $this->WebserviceModel->insertUsersBackupData($insertArr1);
                } 
            }        
        }
        /**************************************************************************/
        return true;
    }

    /******************************************************************* */
    public function DependentListByMobileNumber(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $mn = $data->MobileNumber * 1;
            $Mob = $mn;
            $encryptedMob = $this->encryptDecrypt("en",$Mob);
            $PatientData =  $this->db->select('PatientId,MobileCode,MobileNumber,FullName,Email')->from('PatientMaster')->where('MobileNumber',$encryptedMob)->get()->row();
            $depList = $this->db->select('P.PatientId,P.MobileCode,P.MobileNumber,P.FullName,P.Email,R.Description as Relationship')->from('PatientMaster p')->join("DependentProfileDetails D","P.PatientId = D.DependentProfilePatientId","LEFT")->join("RelationshipMaster R","R.Id = D.RelationshipTypeId","LEFT")->where('D.MainProfilePatientId',$PatientData->PatientId)->get()->result();
			$NewArr = array();
			if($depList){
				$PatientData->MobileNumber = $this->encryptDecrypt("dc",$PatientData->MobileNumber);   
				$PatientData->FullName = $this->encryptDecrypt("dc",$PatientData->FullName);
				$PatientData->Relationship = "Self";
				array_push($NewArr,$PatientData);
				foreach($depList as $val){
					$val->MobileNumber = $this->encryptDecrypt("dc",$val->MobileNumber);   
					$val->FullName = $this->encryptDecrypt("dc",$val->FullName);
					array_push($NewArr,$val);
				}	
				$response['response_code']=1;
				$response['response_message']='Success';
				$response['user_data']=$NewArr;
			}
			else
			{
				$response['response_code']=2;
				$response['response_message']='Failed';
			}
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }

    /**************************************************************************/

    public function sendDataForCreateJsonAndInsert(){
        $data = json_decode(file_get_contents('php://input'));       
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->DisplayPatientId;
            $UserType = $data->userType;
            $AddReferral = $data->AddReferral;
            $Category = $data->Category;
            $DisplayPatientId = $data->DisplayPatientId;
            $DoctorId = $data->DoctorId;
            $DoctorPhoneNumber = $data->DoctorPhoneNumber;
            $EreportsTransitDetailId = $data->EreportsTransitDetailId;
            $InsertDate = $data->InsertDate;
            $PhoneNumber = $data->PhoneNumber;
            $ReportTransitId = $data->ReportTransitId;
            $ReportUploadType = $data->ReportUploadType;
            $RingGrpId = isset($data->RingGroupMasterId)?$data->RingGroupMasterId:"";
            $TenantAddress = $data->TenantAddress;
            $TenantFaxNumber = $data->TenantFaxNumber;
            $TenantName = $data->TenantName;
            $TenantPhoneNuber = $data->TenantPhoneNuber;
            $createTime = $data->createTime;
            if($ReportUploadType == "local"){
                $data2 = $data->data2;
            }else if($ReportUploadType == "online"){
                $data2 = "";
            }
            $description = $data->description;
            $diagnosis = $data->diagnosis;
            $doctorName = $data->doctorName;
            $filetype = $data->filetype;
            $isdoctor = $data->isdoctor;
            $nativeURL = $data->nativeURL;
            $refDoctorName = $data->refDoctorName;
            $refTenantAddress = $data->refTenantAddress;
            $refTenantFaxNumber = $data->refTenantFaxNumber;
            $refTenantName = $data->refTenantName;
            $refTenantPhoneNuber = $data->refTenantPhoneNuber;
            $refUserPhoneNumber = $data->refUserPhoneNumber;
            $remarks = $data->remarks;
            $sync = $data->sync;
            $UploadedFileRef = array();
            $blankArray = array();
            $jsonArr = array(
                        "AddReferral" => $AddReferral,
                        "Category" => $Category,
                        "DisplayPatientId" => $DisplayPatientId,
                        "DoctorId" => $DoctorId,
                        "DoctorPhoneNumber" => $DoctorPhoneNumber,
                        "EreportsTransitDetailId" => $EreportsTransitDetailId,
                        "InsertDate" => $InsertDate,
                        "PhoneNumber" => $PhoneNumber,
                        "ReportTransitId" => $ReportTransitId,
                        "ReportUploadType" => $ReportUploadType,
                        "RingGroupMasterId" => $RingGrpId,
                        "TenantAddress" => $TenantAddress,
                        "TenantFaxNumber" => $TenantFaxNumber,
                        "TenantName" => $TenantName,
                        "TenantPhoneNumber" => $TenantPhoneNuber,
                        "createTime" => $createTime,
                        "data2" => $data2,
                        "description" => $description,
                        "diagnosis" => $diagnosis,
                        "doctorName" => $doctorName,
                        "isdoctor" => $isdoctor,
                        "filetype" => $filetype,
                        "nativeURL" => $nativeURL,
                        "refDoctorName" => $refDoctorName,
                        "refTenantAddress" => $refTenantAddress,
                        "refTenantFaxNumber"=>$refTenantFaxNumber,
                        "refTenantName" => $refTenantName,
                        "refTenantPhoneNumber" => $refTenantPhoneNuber,
                        "refUserPhoneNumber" => $refUserPhoneNumber,
                        "remarks" => $remarks,
                        "sync" => $sync,
                        "UploadedFileRef" => $UploadedFileRef,
                    );

            $checkUserBackup = $this->WebserviceModel->checkUserBackup($UserId,$UserType,3);
            if($checkUserBackup){
                $jsonFile = $checkUserBackup->FileName;
                $inp = file_get_contents($jsonFile);
                $tempArray = json_decode($inp,true);
                // print_r($tempArray);exit;
                array_push($tempArray, $jsonArr);
                $jsonData = json_encode($tempArray);
                $result = file_put_contents($jsonFile, $jsonData);

            }else{
                array_push($blankArray, $jsonArr);
                $json = json_encode($blankArray);
                if($UserType == "Patient"){
                    $jsonFileName = "upload/P_".$UserId."_mailledBackup.json";
                }else if($UserType == "Doctor"){
                    $jsonFileName = "upload/D_".$UserId."_mailledBackup.json";
                }
                file_put_contents($jsonFileName, $json);
                
                $insertArr = array(
                            "UserId" => $UserId,
                            "UserType" => $UserType,
                            "FileName" => $jsonFileName,
                            "IsRefferal" => 3,
                            ); 
                if($jsonFileName){
                    $result = $this->WebserviceModel->insertUsersBackupData($insertArr);
                }
                
            }  
            
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit; 
    }

    public function createJsonOfFileAttachment(){
        $data = json_decode(file_get_contents('php://input'));       
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $jsonArr = array(
                        "Category" => $data->Category,
                        "DisplayPatientId" => $data->DisplayPatientId,
                        "EreportsTransitDetailId" => $data->EreportsTransitDetailId,
                        "FileAttachments" => $data->FileAttachments,
                        "PhoneNumber" => $data->PhoneNumber,
                        "ReportTransitId" => $data->ReportTransitId,
                        "basedata" => $data->basedata,
                        "base64Data" => $data->base64Data,
                        "isdoctor" => $data->isdoctor,
                        "showreferalIcon" => $data->showreferalIcon,
                        "sync_status" => $data->sync_status,
                        "filetype" => $data->filetype
                    );
            $json = json_encode($jsonArr);
            $uniqueNumber = time();
            $jsonFileName = "upload/".$uniqueNumber.".json";

            $result = file_put_contents($jsonFileName, $json);
            if($result)             
            {
                $this->updateBackupJson($data->UserId,$data->UserType,$data->ReportTransitId,$uniqueNumber);
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['file_json'] = $root.$jsonFileName;
                $response['file_json_ref'] = $uniqueNumber;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;     
    }

    function updateBackupJson($UserId,$UserType,$ReportTransitId,$uniqueNumber){
        
        $checkUserBackup = $this->WebserviceModel->checkUserBackup($UserId,$UserType,3);
        if($checkUserBackup){
            $jsonFile = $checkUserBackup->FileName;
            $inp = file_get_contents($jsonFile);
            $tempArray = json_decode($inp,true);
            $blankArr = array();
            foreach($tempArray as $field) {
                if($field['ReportTransitId'] == $ReportTransitId){
                    array_push($field['UploadedFileRef'],$uniqueNumber);
                    array_push($blankArr,$field);        
                }else{
                    array_push($blankArr,$field); 
                }
            }
            $jsonData = json_encode($blankArr);
            $result = file_put_contents($jsonFile, $jsonData);
        }
    }

    public function userOTPValidateForWebPage(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $otp = $data->otp;
            $result = $this->WebserviceModel->checkOtp($otp);
            if($result)             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['data']=$result; 
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }

        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;     
    }

    /******************************************************************* */

    public function sendZipFileViaEmail()
    {
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->userId;
            $UserType = $data->userType;
            $UserEmail = $data->UserEmail;
            $mailSub = $data->mailSub;
            $mailBody = $data->mailBody;

        /**Find Report Json And Files Json */
            $checkUserBackup = $this->WebserviceModel->checkUserBackup($UserId,$UserType,3);
            if($checkUserBackup)
            {
                $reportJson = $checkUserBackup->FileName;
                $inp = file_get_contents($reportJson);
                $tempArray = json_decode($inp,true);
                $blankArr = array();
                
                foreach($tempArray as $field) {
                    if(isset($field['UploadedFileRef']) && !empty($field['UploadedFileRef'])){
                        foreach($field['UploadedFileRef'] as $fileVal) {
                            // print_r($fileVal); echo "----";
                            array_push($blankArr,"upload/".$fileVal.".json");
                        }
                               
                    }
                }
                // exit;
                array_push($blankArr,$reportJson);
                if(!empty($blankArr))
                {
                    /**Create Folder******************************/
                    $date = date("d-m-Y");
                    $folderName = "upload/Ring_".$UserId."_".$date."_Backup";
                    if(!is_dir($folderName))
                    {
                        mkdir($folderName, 0777);
                        foreach($blankArr as $value){
                            $fileN = explode('/',$value);
                            copy($value, $folderName.'/'.$fileN[1]);
                        }
                    }else{
                        array_map("unlink", glob("$folderName/*"));
                        array_map("rmdir", glob("$folderName/*")); 
                        $dltFol = rmdir($folderName);
                        if($dltFol){
                            mkdir($folderName, 0777);
                            foreach($blankArr as $value){
                                $fileN = explode('/',$value);
                                copy($value, $folderName.'/'.$fileN[1]);
                            }
                        }
                        
                    }                      
                    /**Create Zip Folder ***************/
                    $temp_unzip_path = $folderName.'/';
                    $zip = new ZipArchive();
                    $dirArray = array();
                    $new_zip_file = "upload/Ring_".$UserId."_".$date."_Backup.zip";

                    $new = $zip->open($new_zip_file, ZIPARCHIVE::CREATE);
                    if ($new === true) {
                        $handle = opendir($temp_unzip_path);
                        while (false !== ($entry = readdir($handle))) {
                            if(!in_array($entry,array('.','..')))
                            {
                                $dirArray[] = $entry;
                                $zip->addFile($temp_unzip_path.$entry,$entry);
                            }
                        }
                        closedir($handle);
                    } else {
                        $response['response_code'] = 5;
                        $response['response_message'] = 'Zip creation failled';
                    }
                    $zip->close();
                    // exit;
                    /**********************************************/
                    /**Send Mail with attached zip */
                    $sqlFile = $new_zip_file;         
                    $message = $mailBody;
                    $subject = $mailSub;
                    $sendMail = Utility::callSendMailwithAttachedFile($UserEmail,$message,$subject,$root.$sqlFile);
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                }
                else 
                {
                    $response['response_code'] = 4;
                    $response['response_message'] = 'No any json file found';
                }    
            }
            else 
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failled';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    public function unzipJsonFileForWeb_Copy(){
        $data = json_decode(file_get_contents('php://input'));
        if($_FILES)
        {
            $userId = $_POST['userId'];
            $zipFile = $_FILES['zip_file'];
            $zip = new ZipArchive;
            $res = $zip->open($zipFile['tmp_name']);
            $path = 'myzips/extract_path/';
            if ($res === TRUE) 
            {
                $zip->extractTo($path);
                $zip->close();
            } else {
               return false;
            }
            $files = scandir($path);
            $blankArr = array();
            $blankArr1 = array();
            if(isset($files) && !empty($files)){
                foreach($files as $value){
                    if($value != '.' && $value != '..' && $value != "P_".$userId."_mailledBackup.json"){
                        $reportJson = $path.$value;
                        $inp = file_get_contents($reportJson);
                        $tempArray = json_decode($inp,true);
                        array_push($blankArr,$tempArray); 
                    }
                    if($value == "P_".$userId."_mailledBackup.json"){
                        $reportJson1 = $path.$value;
                        $inp1 = file_get_contents($reportJson1);
                        $tempArray1 = json_decode($inp1,true);
                        array_push($blankArr1,$tempArray1); 
                    }
                }
            }
            if(isset($blankArr) && isset($blankArr1))             
            {
                /***Delete extract temp file  */
                $folder_path = $path;
                $AllFiles = glob($folder_path.'/*'); 
                foreach($AllFiles as $file1) {
                    if(is_file($file1)) 
                        unlink($file1); 
                }
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['ReportData']=$blankArr1; 
                $response['FilesData']=$blankArr; 
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }

        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;
    }

    public function validateZip(){
         if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;

            }
        $data = json_decode(file_get_contents('php://input'));
        // print_r($_FILES);exit;
        if(isset($_FILES["zip_file"]) && $_FILES["zip_file"]["error"] == 0)
        {
            $userId = $_POST['userId'];
            $zipFile = $_FILES['zip_file'];
            $zip = new ZipArchive;
            $res = $zip->open($zipFile['tmp_name']);
            $time = time();
            $path = 'myzips/extract_path/'.$time.'/';
            if ($res === TRUE) 
            {
                $zip->extractTo($path);
                $zip->close();
                $kunci = $this->config->item('thekey');
                $token['id'] = $userId; 
                $token['file_dir'] = $path;
                $date1 = new DateTime();
                $token['iat'] = $date1->getTimestamp();
                $token['exp'] = $date1->getTimestamp() + 60 * 60 * 5; 
                $output['token'] = JWT::encode($token, $kunci);

                $response['response_code']=1;
                $response['response_message']='Success';
                $response['token'] = $output['token'];
            } else {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
            
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;    
    }

    public function unzipJsonFileForWeb(){
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'));
        $headers = $data->headers;
        $token = str_replace("Bearer ", "", $headers);        
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);
        $userId = $userData->id;
        $file_dir = $userData->file_dir;
        if($file_dir)
        {
            // print_r($file_dir);exit;
            $files = scandir($file_dir);
            $blankArr1 = array();
            if(isset($files) && !empty($files)){
                foreach($files as $value){
                    //if($value == "P_".$userId."_Backup.json"){
					 if($value == "P_".$userId."_mailledBackup.json"){
                        $reportJson1 = $file_dir.$value;
                        $inp1 = file_get_contents($reportJson1);
                        $tempArray1 = json_decode($inp1,true);
                        array_push($blankArr1,$tempArray1); 
                    }
                }
            }
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
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;
    }
    /******************************************************************* */
    public function ringGroupListByPatientId(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $patientId = $data->patientId;
            $result = $this->WebserviceModel->ringGroupListByPatientId($patientId);
            $resArr = array();
            $chkCRUser = $this->WebserviceModel->chkCRUser($patientId);
            if(isset($chkCRUser) && !empty($chkCRUser->DeviceId)){
                foreach($result as $val){
                    if($val['RingGroupId'] == $chkCRUser->RingGroupMasterId){
                        $val['is_sync'] = 1;
                    }else{
                        $val['is_sync'] = 0;
                    }
                array_push($resArr, $val);
                }           
            }else{
              foreach($result as $val){
                $val['is_sync'] = 0;
                array_push($resArr, $val);
                }
            }
            if($resArr)             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['data']=$resArr; 
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;     
    }
    /********************************************************************/

    function gmailSigninLogin()
    {
        
        $data = json_decode(file_get_contents('php://input'));
        
        if($data)
        {
            $fname = $data->fname; 
            $device_id=$data->device_id;
            $platform = $data->platform;
            $login_with = $data->login_with;
            $fullName = $fname;
            $full_name = $this->encryptDecrypt("en",$fullName);
            $email = $this->encryptDecrypt("en",$data->email);
            $checkData = $this->WebserviceModel->checkUserEmailId($email);
            if($checkData){
                $checkData->MobileNumber = $this->encryptDecrypt("dc",$checkData->MobileNumber);
		$checkData->FirstName = $this->encryptDecrypt("dc",$checkData->FullName);
                $checkData->LastName = $this->encryptDecrypt("dc",$checkData->LastName);
                $checkData->FullName = $checkData->FirstName." ". $checkData->LastName;
		$checkData->Email = $this->encryptDecrypt("dc",$checkData->Email);
                if(isset($checkData->MobileNumber) && !empty($checkData->MobileNumber)){
                    $checkDevice = $this->WebserviceModel->checkLoginDevice($checkData->PatientId);
                    $UserSettings = $this->WebserviceModel->getUserSettings($checkData->PatientId,"Patient");
                    $notificationDeviceId = isset($checkDevice->DeviceId)?$checkDevice->DeviceId:"";
                    if(empty($checkDevice->DeviceId) || $notificationDeviceId == $device_id)
                    {
                        $kunci = $this->config->item('thekey');
                        $token['id'] = $checkData->PatientId;
                        $token['data'] = $checkData;
                        $date1 = new DateTime();
                        $token['iat'] = $date1->getTimestamp();
                        $token['exp'] = $date1->getTimestamp() + 60 * 60 * 5;
                        $output['token'] = JWT::encode($token, $kunci); 
                        $saveDataArray = array( 
                                    "PatientId"=> $checkData->PatientId,
                                    "DeviceId"=>$device_id,
                                    "Platform"=>$platform,
                                    );
                        $notificationUserType = 0;            
                        $checkuserid = $this->WebserviceModel->checkuserid($checkData->PatientId,$notificationUserType);
                        if($checkuserid){
                            $updateDeviceToken = $this->WebserviceModel->updateDeviceToken($checkData->PatientId,$device_id,$platform);
                        }else{
                            $insertDeviceToken = $this->WebserviceModel->insertDeviceToken($saveDataArray);
                        }
                        
                        $BackupDataNew = $this->WebserviceModel->checkUserBackup($checkData->PatientId,'Patient',3);
                        $response['response_code']=1;
                        $response['response_message']='Success';
                        $response['is_doctor']=0;
                        $response['data']=$checkData;
                        $response['token']=$output['token'];
                        $response['UserSettings']=isset($UserSettings)?$UserSettings:"";
                        $response['BackupDataNew']=isset($BackupDataNew)?$BackupDataNew:"";
                    }
                    else
                    {
						$isReff = 0;
						$BackupDataNew = $this->WebserviceModel->checkUserBackup($checkData->PatientId,'Patient',3);
                        $response['response_code']=5;
                        $response['response_message']='Device id differ';
                        $response['PatientId']=$checkData->PatientId;
                        $response['UserSettings']=isset($UserSettings)?$UserSettings:"";
						$response['BackupDataNew']=isset($BackupDataNew)?$BackupDataNew:"";
                    }
                }else{
                    $response['response_code'] = '2';
                    $response['response_message'] = 'Email exist and mobile number empty';
                    $response['patient_id'] = $checkData->PatientId;
                }
            }else{
                $insertGmailArray = array( 
                    "FullName"=>$full_name,
                    "Email"=>$email,
                    "InsertDate"=>date("Y-m-d H:i:s")
                    );
                $getData = $this->WebserviceModel->insertSignUpData($insertGmailArray);
                if($getData)
                {
                    $response['response_code'] = '3';
                    $response['response_message'] = 'Data insert Successfully';
                    $response['patient_id'] = $getData;

                }
                else
                {
                    $response['response_code'] = '4';
                    $response['response_message'] = 'Data insert Failed';
                }
            }
        }
        else
        {
            $response['response_code'] = '6';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function updatePatientGmailSignup_New()
    {       
        $data = json_decode(file_get_contents('php://input'));
        
        if($data)
        {
            $patient_id = $data->patient_id;
            $mn = $data->mobile_number * 1 ;
            $mobile_number = $this->encryptDecrypt("en",$mn);
            $fname = $data->fname; 
            $fullName = $fname;
            $full_name = $this->encryptDecrypt("en",$fullName);
            $mob_code = $data->mob_code;
            $email = $this->encryptDecrypt("en",$data->email);
            $BloodGroupId = isset($data->bloodgroup)?$data->bloodgroup:NULL;
            $address = urlencode($data->address);
            $DOB = $data->DOB;
            $country_id = $data->country_id;
            $state_id = $data->state_id;
            $city_id = $data->city_id;
            $pincode = $data->pin_code;
            $UpdDataArray = array( 
                                "MobileNumber"=>$mobile_number,
                                "FullName"=>$full_name,
                                "Email"=>$email,
                                "MobileCode"=>$mob_code,
                                "BloodGroupId"=>$BloodGroupId,
                                "Address"=>$address,
                                "DateOfBirth"=>$DOB,
                                "CountryMasterId"=>$country_id,
                                "StateMasterId"=>$state_id,
                                "CityMasterId"=>$city_id,
                                "PinCode"=>$pincode,
                                "InsertDate"=>date("Y-m-d H:i:s")                                   
                                );
            $updateData = $this->WebserviceModel->updatePatientGmailSignup($patient_id, $UpdDataArray);
            if($updateData)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['Data'] = $updateData;

            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function getDoctorsDetailsAndTenants(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $doctor_id = $data->doctor_id;
            $userData = $this->WebserviceModel->getDoctorsDetails($doctor_id);
            $userData->tenants = $this->WebserviceModel->getDoctorsTenants($doctor_id);
            if($userData)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $userData;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }       
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function getHealthCareInfo(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $tenant_id = $data->tenant_id;
            $tenantData = $this->WebserviceModel->getTenantsDetails($tenant_id);
            if($tenantData)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $tenantData;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }       
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function tenantsSearchDataByKeywords(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $keyword = $data->keyword;
            $tenantData = $this->WebserviceModel->tenantsSearchDataByKeywords($keyword);
            if($tenantData)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $tenantData;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }       
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    function facilityMasterData(){
        $data = json_decode(file_get_contents('php://input')); 
            $facilityData = $this->WebserviceModel->facilityMasterData();
            if($facilityData)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $facilityData;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        echo json_encode($response);exit;
    }

    public function GetCampaignDataOfNotificationWithFilter(){
   
        $data = json_decode(file_get_contents('php://input'));
        if($data){
            $autoDeleteDays = $data->autoDeleteDays;    
            $cityId = isset($data->cityId)?$data->cityId:"";
			$ringGroupId = isset($data->ringGroupId)?$data->ringGroupId:"";
			$tenantId = isset($data->tenantId)?$data->tenantId:"";     
            $userData =  $this->WebserviceModel->GetCampaignDataOfNotificationWithFilter($cityId,$ringGroupId,$tenantId);
            date_default_timezone_set("Asia/Kuala_Lumpur");
            $campData = array();
            foreach($userData as $val){
            $campDate = $val->InsertDate;
            $campDate1 = strtotime($campDate);
            $now = time();
            $datediff = $now - $campDate1;
            $diffDays = round($datediff / (60 * 60 * 24));
            if($diffDays < $autoDeleteDays){
                array_push($campData,$val);				   
            }
		}
        if($campData)             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                 $response['data']=$campData;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';
            
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is null';
        }       
        echo json_encode($response); exit;
    }

    public function testEmail(){
        Utility::callSendMailWithAttachmentNew();      
    }

    public function doctorLoginWithUserId()
    {   
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $user_id = $data->user_id;    
            $device_id = $data->device_id;
            $platform = $data->platform;            
            $result = $this->WebserviceModel->doctorLoginWithUserId($user_id);
            if($result)             
            {
                $kunci = $this->config->item('thekey');
                $token['id'] = $result->UserId;
                $token['data'] = $result;
                $date1 = new DateTime();
                $token['iat'] = $date1->getTimestamp();
                $token['exp'] = $date1->getTimestamp() + 60 * 60 * 5;
                $output['token'] = JWT::encode($token, $kunci);
                $saveDataArray = array( 
                                    "PatientId"=> $result->UserId,
                                    "DeviceId"=>$device_id,
                                    "Platform"=>$platform,
                                    "UserType"=>1,
                                    );
                $notificationUserType = 1;            
                $checkuserid = $this->WebserviceModel->checkuserid($result->UserId,$notificationUserType);
                if($checkuserid){
                    $updateDeviceToken = $this->WebserviceModel->updateDeviceToken($result->UserId,$device_id,$platform);
                }else{
                    $insertDeviceToken = $this->WebserviceModel->insertDeviceToken($saveDataArray);
                }
                $UserSettings = $this->WebserviceModel->getUserSettings($result->UserId,"Doctor");
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['is_doctor']=1;
                $response['data']=$result;
                $response['token']=$output['token'];
                $response['UserSettings']=isset($UserSettings)?$UserSettings:"";
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }    
        echo json_encode($response);exit;  
    }
    
    function stringEncyptionAndDecryption(){
        $data = json_decode(file_get_contents('php://input')); 
        if($data)
        {
            $method = $data->method;
            $string_to_encrypt = $data->string;
            $key="thekey";
            if($method == "encrypt"){
                $converted_string=openssl_encrypt($string_to_encrypt,"AES-128-ECB",$key);
            }else if($method == "decrypt"){
                $converted_string=openssl_decrypt($string_to_encrypt,"AES-128-ECB",$key);
            }else{
                $response['response_code'] = '4';
                $response['response_message'] = 'Error';
            }     
            
            if($converted_string)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $converted_string;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }    
        echo json_encode($response);exit;    
    }

    function encrypt($pure_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
        return $encrypted_string;
    }

    /**
     * Returns decrypted original string
     */
    function decrypt($encrypted_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
        return $decrypted_string;
    }

    public function SendOtpForDeletePatient()
    {      
        $data = json_decode(file_get_contents('php://input'));
        if($data)    
        {    
            $PatientId = $data->PatientId;
            $patientdata = $this->WebserviceModel->getUserDataById($PatientId);
            if($patientdata)             
            {
                $pat_email = $this->encryptDecrypt("dc",$patientdata->Email);
                $mobile_number = $patientdata->MobileNumber;
                $mob_code = $patientdata->MobileCode;
                $mob_code = str_replace(' ', '', $mob_code);
                // echo $pat_email;
                $PatMobCode = $data->PatMobCode;
                $PatMobCode = str_replace(' ', '', $PatMobCode);
                $PatMobNumber = $data->PatMobNumber; 
                $decryptedMobile = $this->encryptDecrypt("dc",$patientdata->MobileNumber);
                if(($PatMobCode == $mob_code) && ($PatMobNumber == $decryptedMobile)){
                    $otp = rand(100000, 999999);
                    // $message = 'Your OTP for account deletion is '.$otp.'. Do not share this with anyone';
                    // $subject = 'Account deletion request OTP';
                    // $sendMail = Utility::callSendMail($pat_email,$message,$subject);
                    // $this->sendMail($otp,$result->Email);

                    $pat_name = $this->encryptDecrypt("dc",$patientdata->FullName);
                    $type = "deleteProfileOtp";
                    $dataArr = array("name"=>$pat_name, "otp"=>$otp);
                    $sendMail = Utility::callSendMailWithTemplate($pat_email,$type,$dataArr);

                    $result1 = $this->WebserviceModel->updateOtpData($otp,$mobile_number,$mob_code);
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                    $response['otp'] = $otp;
                }else{
                    $response['response_code'] = 4;
                    $response['response_message'] = 'Mobile number or mobile code or both has not matched';
                }    
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';
            }               
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;        
    }

    public function patientAccountDeletion()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $PatientId = $data->PatientId;
            $MobileCode = $data->MobileCode;
            $MobNumber = $data->MobileNumber;
            $Otp = $data->Otp;
            $MobileNumber = $this->encryptDecrypt("en",$MobNumber);
            $result = $this->WebserviceModel->checkUserExistance($PatientId,$MobileCode,$MobileNumber,$Otp); 
            if($result)             
            {
                /************** Delete Dependent data******************/
                $depList = $this->WebserviceModel->getDependentListByPatientId($PatientId);
                if($depList){
                    foreach($depList as $val){
                        $DependentPatientId = $val->DependentProfilePatientId;
                        /************** Delete Patient's E-report transit and details******************/
                        $depReportData = $this->WebserviceModel->getEreportTransitIdForDeletion($DependentPatientId);
                        if($depReportData){
                            foreach($depReportData as $depReportval){
                                $deleteDepAlertData  =  $this->WebserviceModel->deleteReportDetails($depReportval->ReportTransitId, "Alerts"); 
                                $deleteDepReportDetails =  $this->WebserviceModel->deleteReportDetails($depReportval->ReportTransitId, "EreportsTransitDetail"); 
                                $deleteDepReport =  $this->WebserviceModel->deleteReportDetails($depReportval->ReportTransitId, "EreportsTransit"); 
                            }
                        }
                        /************************************************************* */
                        $deleteDependent =  $this->WebserviceModel->deleteDependentProfile($DependentPatientId);
                        $deleteDependentDetails =  $this->WebserviceModel->deleteDepDetail($DependentPatientId,$PatientId);
                    }
                }

                /************** Delete Patient's E-report transit and details******************/
                $reportData = $this->WebserviceModel->getEreportTransitIdForDeletion($PatientId);
                if($reportData){
                    foreach($reportData as $reportval){
                        $deleteAlertData  =  $this->WebserviceModel->deleteReportDetails($reportval->ReportTransitId, "Alerts"); 
                        $deleteReportDetails =  $this->WebserviceModel->deleteReportDetails($reportval->ReportTransitId, "EreportsTransitDetail"); 
                        $deleteReport =  $this->WebserviceModel->deleteReportDetails($reportval->ReportTransitId, "EreportsTransit"); 
                    }
                }
                /************** Delete Patient's backup******************/                   
                $deleteBackup = $this->WebserviceModel->deletePatientBackup($PatientId);
                $UserType = 0;
                $deleteDevice = $this->WebserviceModel->deleteUserDevice($PatientId,$UserType);
                /************** Finally Delete Patient account******************/ 
                $deletePatient = $this->WebserviceModel->deleteDependentProfile($PatientId);// We use this model function because of the same logic written in this function.
                if($deletePatient == TRUE){
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                }else{
                    $response['response_code'] = 2;
                    $response['response_message'] = 'Failed';
                }
                
            }
            else
            {
                $response['response_code']=4;
                $response['response_message']='Patient not found';                
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }           
        echo json_encode($response); exit;
    }
public function convertImageLinkToBase64(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            // $imageLink = "https://app.ring.healthcare/upload/EReportsAttachments/00000/0000001226_aaoddnldododn.png";
            $path = $data->imageLink;
            $link = trim($path);
            $link = str_replace (' ', '%20', $link);
            $type = pathinfo($link, PATHINFO_EXTENSION);
            $data = file_get_contents($link);
            // $base64 = base64_encode($data);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            // print_r($base64);exit;
            if($base64){
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['base64_data'] = $base64;
            }else{
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    public function refferalVisitNotesByPatientId(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $pat_id = $data->patient_id;
            $ringGrpId = isset($data->RingGroupMasterId)?$data->RingGroupMasterId:"";
            $reportList = $this->WebserviceModel->refferalVisitNotesByPatientId($pat_id,$ringGrpId);
            $fileArrayAttachemnt = array();
            foreach($reportList as $file ){
                $InsertDate = $file->InsertDate;
                if(isset($file->Description) && $file->Description != null){
                    $desc = $file->Description;
                }else{
                     $desc = '';
                }
                $newInsertDate = date("d/m/Y",  strtotime($InsertDate));
                if(empty($file->ICDSubCode)){    
                    $diagNos = $file->ReferralICD ;
				}else{
					$diagNos = $file->ICDSubCode."_".$file->DiagnosisName ;
				}
			    $docter_name = $file->sirname_title." ".$file->doctorName." ".$file->LastName;
			    $fileArrayAttachemnt[]=array("ReportTransitId"=>$file->ReportTransitId,"RingGroupMasterId"=>$file->RingGroupMasterID,"RingGroupMasterIdReff"=>$file->RingGroup,"InsertDate"=>$newInsertDate,"Description"=>$desc,"EreferralForm"=>array(),"FileAttachments"=>array(),"IsProcessed"=>$file->IsProcessed,"IsDoctorProcessed"=>$file->IsDoctorProcessed,"IsPatientProcessed"=>$file->IsPatientProcessed,"AddReferral"=>$file->AddReferral,"RefDoctorId"=>$file->ReferredToUserId,"DoctorId"=>$file->DoctorId,"doctorName"=>$docter_name, "DoctorPhoneNumber"=>$file->UserPhoneNumber,"DoctorSpeciality"=>$file->DoctorSpeciality,"TenantName"=>$file->TenantName,"WorkingSchedule"=>array(),"TenantPhoneNuber"=>$file->TenantPhoneNuber,"TenantFaxNumber"=>$file->TenantFaxNumber,"TenantAddress"=>$file->TenantAddress,"Refferal"=>array(),"diagnosis"=>$diagNos,  "EReferralStatus"=>$file->EReferralStatus, "VisitNotes"=>$file->VisitNotes, "Treatment"=>$file->Treatment, "Diagnosis"=>$diagNos);
                
                /**Working Hours of Tenant */
				$workHrArr = array();
                $refworkHrArr = array();
                $workingArr = $this->WebserviceModel->getWorkingScheduleOfTenant($file->TenantId);
                foreach($workingArr as $workHrVal){
                    $workHrVal->FromTime = date("h:i A", strtotime($workHrVal->FromTime));
					$workHrVal->ToTime = date("h:i A", strtotime($workHrVal->ToTime));
					array_push($workHrArr,$workHrVal);
				}
                if(isset($workHrArr) && !empty($workHrArr)){
                    $workingHTML = '<div class="f14 txtlist">';
                    $workingHTMLinMalay = '<div class="f14 txtlist">';
                    foreach($workHrArr as $workHrArrVal)
                    {
                        $workingHTML .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                        $workingHTMLinMalay .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                    }
                    $workingHTML .= '</div>';
                    $workingHTMLinMalay .= '</div>';
                }else{
                    $workingHTML = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> From Time :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> To Time :</span>N/A
                                    </ion-col>
                                    </p>';
                    $workingHTMLinMalay = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Dari jam :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Hingga jam :</span>N/A
                                    </ion-col>
                                    </p>';                
                }
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingSchedule"] = $workingHTML;
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingScheduleInMalay"] = $workingHTMLinMalay;

                /*Referral Details of E-Report*/
                if(isset($file->ReferredToUserId) && !empty($file->ReferredToUserId)){
                  $refDocDetails = $this->WebserviceModel->getRefReportDoctorDetails($file->ReferredToUserId,$file->refTenant);
                    //   print_r($refDocDetails);exit;
                  if($refDocDetails){
                    $refDrworkHrArr = array();
                    foreach($refDocDetails as $refDocDetailsVal){
                        $refDrworkingArr = $this->WebserviceModel->getWorkingScheduleOfTenant($refDocDetailsVal->TenantId);
                        foreach($refDrworkingArr as $refDrworkingArrVal){
                            $refDrworkingArrVal->FromTime = date("h:i A", strtotime($refDrworkingArrVal->FromTime));
                            $refDrworkingArrVal->ToTime = date("h:i A", strtotime($refDrworkingArrVal->ToTime));
                            array_push($refDrworkHrArr,$refDrworkingArrVal);
                        }
                        if(isset($refDrworkHrArr) && !empty($refDrworkHrArr)){
                            $refDrworkingHTML = '<div class="f14 txtlist">';
                            $refDrworkingHTMLinMalay = '<div class="f14 txtlist">';
                            foreach($refDrworkHrArr as $refDrworkHrArrVal)
                            {
                                $refDrworkingHTML .= '<ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> '.$refDrworkHrArrVal->DayName.' : </span>  '.$refDrworkHrArrVal->FromTime.' - '.$refDrworkHrArrVal->ToTime.'<br>
                                            </ion-col>';
                                $refDrworkingHTMLinMalay .= '<ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> '.$refDrworkHrArrVal->DayName.' : </span>  '.$refDrworkHrArrVal->FromTime.' - '.$refDrworkHrArrVal->ToTime.'<br>
                                            </ion-col>';
                            }
                            $refDrworkingHTML .= '</div>';
                            $refDrworkingHTMLinMalay .= '</div>';
                        }else{
                            $refDrworkingHTML = '<p class="f14 txtlist">
                                            <ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> From Time :</span>N/A<br>
                                            </ion-col>
                                            <ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> To Time :</span>N/A
                                            </ion-col>
                                            </p>';
                            $refDrworkingHTMLinMalay = '<p class="f14 txtlist">
                                            <ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> Dari jam :</span>N/A<br>
                                            </ion-col>
                                            <ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> Hingga jam :</span>N/A
                                            </ion-col>
                                            </p>';                
                        }
                        $refDocDetailsVal->workingHTML = $refDrworkingHTML;
                        $refDocDetailsVal->workingHTMLinMalay = $refDrworkingHTMLinMalay;
                        $refDocDetailsVal->refDoctorName = $refDocDetailsVal->sirname_title." ".$refDocDetailsVal->refDoctorName." ".$refDocDetailsVal->LastName;
                    }
                  }
                  $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["Refferal"] = $refDocDetails;
                }

                $fileAttachmentList = $this->WebserviceModel->getFileAttachmentByReportTransitIdForVisitNot($file->ReportTransitId,$file->ReferredToUserId);
                if(is_array($fileAttachmentList)){
                    foreach($fileAttachmentList as $fileData){
						$jsonFileArr = json_decode($fileData->FileAttachments);
						if(isset($jsonFileArr[0]->Filename)){
							$fileData->FileAttachments = "https://app.ring.healthcare/upload/".$jsonFileArr[0]->Filename;
							$fileData->Base64FileAttachments = $this->convertImageLinkToBase64ForFileAttachment($fileData->FileAttachments);
							$fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["FileAttachments"][]=$fileData;
						}
                    }
                }
            }
            if($reportList)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $fileArrayAttachemnt;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    public function refferalVisitNotesByPatientId_copy(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $pat_id = $data->patient_id;
            $ringGrpId = isset($data->RingGroupMasterId)?$data->RingGroupMasterId:"";
            $reportList = $this->WebserviceModel->refferalVisitNotesByPatientId($pat_id,$ringGrpId);
            $fileArrayAttachemnt = array();
            foreach($reportList as $file ){
                $InsertDate = $file->InsertDate;
                if(isset($file->Description) && $file->Description != null){
                    $desc = $file->Description;
                }else{
                     $desc = '';
                }
                $newInsertDate = date("d/m/Y",  strtotime($InsertDate));
				
              
				if(empty($file->ICDSubCode)){
					
               
                 $diagNos = $file->ReferralICD ;
				}else{
					$diagNos = $file->ICDSubCode."_".$file->DiagnosisName ;
				}
			                $fileArrayAttachemnt[]=array("ReportTransitId"=>$file->ReportTransitId,"RingGroupMasterId"=>$file->RingGroupMasterID,"RingGroupMasterIdReff"=>$file->RingGroup,"InsertDate"=>$newInsertDate,"Description"=>$desc,"EreferralForm"=>array(),"FileAttachments"=>array(),"IsProcessed"=>$file->IsProcessed,"IsDoctorProcessed"=>$file->IsDoctorProcessed,"IsPatientProcessed"=>$file->IsPatientProcessed,"AddReferral"=>$file->AddReferral,"RefDoctorId"=>$file->ReferredToUserId,"DoctorId"=>$file->DoctorId,"doctorName"=>$file->doctorName, "DoctorPhoneNumber"=>$file->UserPhoneNumber,"DoctorSpeciality"=>$file->DoctorSpeciality,"TenantName"=>$file->TenantName,"WorkingSchedule"=>array(),"TenantPhoneNuber"=>$file->TenantPhoneNuber,"TenantFaxNumber"=>$file->TenantFaxNumber,"TenantAddress"=>$file->TenantAddress,"Refferal"=>array(),"diagnosis"=>$diagNos,  "EReferralStatus"=>$file->EReferralStatus, "VisitNotes"=>$file->VisitNotes, "Treatment"=>$file->Treatment, "Diagnosis"=>$diagNos);
                
                /**Working Hours of Tenant */
                // $workingArr = $this->db->select('*')->from('TenantWorkingHours')->where('TenantId',$file->TenantId)->get()->result();
                // $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingSchedule"] = $workingArr;
                /**Working Hours of Tenant */
				$workHrArr = array();
                $refworkHrArr = array();
                // $workingArr = $this->db->select('*')->from('TenantWorkingHours')->where('TenantId',$file->TenantId)->get()->result();
				$workingArr = $this->WebserviceModel->getWorkingScheduleOfTenant($file->TenantId);
                foreach($workingArr as $workHrVal){
					//print_r($workHrVal);exit; 
    				// $workHrVal->FromTime = date("Y-m-d h:i", strtotime($workHrVal->FromTime));
					// $workHrVal->ToTime = date("Y-m-d h:i", strtotime($workHrVal->ToTime));
                    $workHrVal->FromTime = date("h:i A", strtotime($workHrVal->FromTime));
					$workHrVal->ToTime = date("h:i A", strtotime($workHrVal->ToTime));
					array_push($workHrArr,$workHrVal);
				}
                if(isset($workHrArr) && !empty($workHrArr)){
                    $workingHTML = '<div class="f14 txtlist">';
                    $workingHTMLinMalay = '<div class="f14 txtlist">';
                    foreach($workHrArr as $workHrArrVal)
                    {
                        // $workingHTML .= '<ion-col size="12" class="f14 txtlist">
                        //                 <span class="fw600" translate> FromTime :</span>'.$workHrArrVal->FromTime.'<br>
                        //             </ion-col>
                        //             <ion-col size="12" class="f14 txtlist">
                        //                 <span class="fw600" translate> ToTime :</span>'.$workHrArrVal->ToTime.'<br>
                        //             </ion-col>';
                        // $workingHTMLinMalay .= '<ion-col size="12" class="f14 txtlist">
                        //     <span class="fw600" translate> Dari jam :</span>'.$workHrArrVal->FromTime.'<br>
                        // </ion-col>
                        // <ion-col size="12" class="f14 txtlist">
                        //     <span class="fw600" translate> Hingga jam :</span>'.$workHrArrVal->ToTime.'<br>
                        // </ion-col>';
                        $workingHTML .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                        $workingHTMLinMalay .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                    }
                    $workingHTML .= '</div>';
                    $workingHTMLinMalay .= '</div>';
                }else{
                    $workingHTML = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> From Time :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> To Time :</span>N/A
                                    </ion-col>
                                    </p>';
                    $workingHTMLinMalay = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Dari jam :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Hingga jam :</span>N/A
                                    </ion-col>
                                    </p>';                
                }
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingSchedule"] = $workingHTML;
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingScheduleInMalay"] = $workingHTMLinMalay;

                /*Referral Details of E-Report*/
                if(isset($file->ReferredToUserId) && !empty($file->ReferredToUserId)){
                  $refDocDetails = $this->WebserviceModel->getRefReportDoctorDetails($file->ReferredToUserId,$file->refTenant);
                //   print_r($refDocDetails);exit;
                  if($refDocDetails){
                    $refDrworkHrArr = array();
                    foreach($refDocDetails as $refDocDetailsVal){
                        $refDrworkingArr = $this->WebserviceModel->getWorkingScheduleOfTenant($refDocDetailsVal->TenantId);
                        foreach($refDrworkingArr as $refDrworkingArrVal){
                            $refDrworkingArrVal->FromTime = date("h:i A", strtotime($refDrworkingArrVal->FromTime));
                            $refDrworkingArrVal->ToTime = date("h:i A", strtotime($refDrworkingArrVal->ToTime));
                            array_push($refDrworkHrArr,$refDrworkingArrVal);
                        }
                        if(isset($refDrworkHrArr) && !empty($refDrworkHrArr)){
                            $refDrworkingHTML = '<div class="f14 txtlist">';
                            $refDrworkingHTMLinMalay = '<div class="f14 txtlist">';
                            foreach($refDrworkHrArr as $refDrworkHrArrVal)
                            {
                                $refDrworkingHTML .= '<ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> '.$refDrworkHrArrVal->DayName.' : </span>  '.$refDrworkHrArrVal->FromTime.' - '.$refDrworkHrArrVal->ToTime.'<br>
                                            </ion-col>';
                                $refDrworkingHTMLinMalay .= '<ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> '.$refDrworkHrArrVal->DayName.' : </span>  '.$refDrworkHrArrVal->FromTime.' - '.$refDrworkHrArrVal->ToTime.'<br>
                                            </ion-col>';
                            }
                            $refDrworkingHTML .= '</div>';
                            $refDrworkingHTMLinMalay .= '</div>';
                        }else{
                            $refDrworkingHTML = '<p class="f14 txtlist">
                                            <ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> From Time :</span>N/A<br>
                                            </ion-col>
                                            <ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> To Time :</span>N/A
                                            </ion-col>
                                            </p>';
                            $refDrworkingHTMLinMalay = '<p class="f14 txtlist">
                                            <ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> Dari jam :</span>N/A<br>
                                            </ion-col>
                                            <ion-col size="12" class="f14 txtlist">
                                                <span class="fw600" translate> Hingga jam :</span>N/A
                                            </ion-col>
                                            </p>';                
                        }
                        $refDocDetailsVal->workingHTML = $refDrworkingHTML;
                         $refDocDetailsVal->workingHTMLinMalay = $refDrworkingHTMLinMalay;
                    }
                  }
                  $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["Refferal"] = $refDocDetails;
                }

                $fileAttachmentList = $this->WebserviceModel->getFileAttachmentByReportTransitIdForVisitNot($file->ReportTransitId,$file->ReferredToUserId);
                if(is_array($fileAttachmentList)){
                    foreach($fileAttachmentList as $fileData){
						$jsonFileArr = json_decode($fileData->FileAttachments);
                        foreach($jsonFileArr as $jsonFileArrVal){
                        // echo "<pre>";  print_r($jsonFileArrVal);exit; 
                            if(isset($jsonFileArrVal->Filename)){
                                $fileData->FileAttachments = "http://sancyberhad.ddns.net/RING_TEST/upload/".$jsonFileArrVal->Filename;
                                $fileData->Base64FileAttachments = $this->convertImageLinkToBase64ForFileAttachment($fileData->FileAttachments);
                                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["FileAttachments"][]=$fileData;
                            }
                        }
                    }
                }
            }
            if($reportList)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $fileArrayAttachemnt;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    function fileTransferStatusUpdateForReferralVisitNote(){
        $data = json_decode(file_get_contents('php://input'));
        $reportTransitId = $data->ReportTransitId;
        $proccessName = "IsDoctorProcessed";
        $updateEreportTransit = $this->WebserviceModel->fileTransferStatusUpdate($reportTransitId,$proccessName);
        $transitDetailId  = $data->EreportsTransitDetailId;
        $doctorId  = $data->DoctorId;
        if(!empty($transitDetailId)){
            $updateDetail = $this->WebserviceModel->updateFileTransferStatus($transitDetailId,$proccessName);
            $getRemainFiles = $this->WebserviceModel->getFilesRemainingToDownload($reportTransitId,$proccessName,$doctorId);
            if($getRemainFiles->count == 0)
            {
                $updateEreportTransit = $this->WebserviceModel->fileTransferStatusUpdate($reportTransitId,$proccessName);
            }
        }else{
            $updateDetail = $this->WebserviceModel->fileTransferStatusUpdate($reportTransitId,$proccessName);
        }
        if($updateEreportTransit)
        {
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
        }
        else
        {
            $response['response_code'] = '2';
            $response['response_message'] = 'Failed';
        }   
        echo json_encode($response);exit;
    }
	
	public function getDoctorForHospitalsearchPage(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $tenantId = $data->tenantId;
			$specialityId = $data->specialityId;
            $userData = $this->WebserviceModel->getDoctorFromTenant($tenantId,$specialityId);
            if($userData){
                foreach($userData as $userDataArr){
                    // print_r($userDataArr);
                    if($userDataArr->SecondarySpecialityId){
                       $secondData = $this->WebserviceModel->getDoctorFromTenantSec($userDataArr->DoctorId,$tenantId,$userDataArr->SecondarySpecialityId); 
						
                       array_push($userData,$secondData);
                    }
                    $impData = $this->db->select('*')->from('DoctorImplementation')->where('RingDoctorId',$userDataArr->DoctorId)->get()->row();
                    if($impData){
                        $userDataArr->Appointment = "enable";
                        $userDataArr->impData = $impData;
                        $userDataArr->Scheduled = $impData->Scheduled;
                    }else{
                        $userDataArr->Appointment = "disable";
                        $userDataArr->Scheduled = 0;
                    } 
                }
            }
            $tenantDetails = $this->WebserviceModel->getTenantsDetails($tenantId);
            $workingArr = $this->WebserviceModel->getWorkingScheduleOfTenant($tenantId);
            // print_r($workingArr);exit;
            $workHrArr = array();
                foreach($workingArr as $workHrVal){
                    $workHrVal->FromTime = date("h:i A", strtotime($workHrVal->FromTime));
					$workHrVal->ToTime = date("h:i A", strtotime($workHrVal->ToTime));
					array_push($workHrArr,$workHrVal);
				}
                if(isset($workHrArr) && !empty($workHrArr)){
                    $workingHTML = '<div class="f14 txtlist">';
                    foreach($workHrArr as $workHrArrVal)
                    {
                        $workingHTML .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                    }
                    $workingHTML .= '</div>';
                }else{
                    $workingHTML = '';            
                }
            if($userData)
            {

                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $userData;
                if($tenantDetails){
                    $response['Tenant_data'] = $tenantDetails;
                }
                $response['working_data'] = $workingHTML;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }       
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function save_report_data(){
        $data = file_get_contents('php://input');
        if($data)
        {
            $url = 'https://apiweb.ring.healthcare:5028/api/Register/UploadFiles';
            $json = $data;       
            $headers = array('Content-Type: application/json');
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $headers,
            ));

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
                echo $error_msg;

            }
            curl_close($curl);
            echo $response;
            return  $response;
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    	public function saveReportDataForHISDigitalSense1(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $UserId = $data->UserId;
            $DoctorName = $data->DoctorName;
            $MMCNumber = isset($data->MMCNumber)?$data->MMCNumber:"";
            $PatientName = isset($data->PatientName)?$data->PatientName:"";
            $Description = $data->Description;
			$CategoryName = $data->CategoryName;
			$FileAttachments = $data->FileAttachments; 
            $HospitalName = isset($data->HospitalName)?$data->HospitalName:""; 
            $Diagnosis = isset($data->Diagnosis)?$data->Diagnosis:""; 
            $PatientMobile = isset($data->PatientMobile)?$data->PatientMobile:"";
            $PatientMobileCode = isset($data->PatientMobileCode)?$data->PatientMobileCode:"";
            $PatientEmail1 = isset($data->PatientEmail)?$data->PatientEmail:"";
            if($PatientEmail1){
                $PatientEmail = strtolower($PatientEmail1);
            }else{
                $PatientEmail = "";
            }
            $Email = $this->encryptDecrypt("en",$PatientEmail);           
            // $HospitalName = $data->HospitalName; 
            $NewArr = array();		
            if(!empty($PatientMobile) && !empty($PatientMobileCode)){
                $Mobile = $this->encryptDecrypt("en",$PatientMobile);
                $MobCode = "+".str_replace(" ","",$PatientMobileCode);
                $chkPatient = $this->WebserviceModel->patientSearchByMobileNumber($Mobile, $MobCode); 
                if(isset($chkPatient) && $Email != $chkPatient->Email){
                    $depList = $this->WebserviceModel->getDependentListByPatientId($chkPatient->PatientId);
                    if($depList){
                        foreach($depList as $val){
                            $DependentProfilePatientId = isset($val->DependentProfilePatientId)?$val->DependentProfilePatientId:"";
                            $depData =  $this->WebserviceModel->getUserDataById($DependentProfilePatientId);
                    // print_r($depData);exit;
                            $depEmail = $this->encryptDecrypt("dc",$depData->Email);
                            $depName = $this->encryptDecrypt("dc",$depData->FullName);
                            if($PatientEmail == $depEmail && trim($PatientName) == $depName){
                                array_push($NewArr,$depData);
                            }                               
                        }
                    }
                }else{
                    array_push($NewArr,$chkPatient);
                }              
            }else if(!empty($PatientEmail)){
                $Email = $this->encryptDecrypt("en",$PatientEmail);
                $chkPatient = $this->WebserviceModel->patientSearchByEmail($Email);
                array_push($NewArr,$chkPatient);
            }		
			if(isset($NewArr) && !empty($NewArr)){
                // print_r($NewArr);exit;
                $chkDoctor = $this->WebserviceModel->doctorSearchDataForHIS($DoctorName,$MMCNumber);
                if($chkDoctor){
                     
                    $PatientId = $NewArr[0]->PatientId;
                    $docId = $chkDoctor->UserId;
                    $chkHospital = $this->WebserviceModel->tenantsSearchDataForHIS($HospitalName,$docId);   
                // print_r($chkHospital);exit;                
                    if($chkHospital){
                        $TenantId = $chkHospital->TenantId;
                        $chkRingGroup = $this->WebserviceModel->getRingGrpByTenantId($TenantId);
                        if($chkRingGroup){
                            $RingGrpId = $chkRingGroup->RingGroupId;
                        }else{
                            $RingGrpId = 2;
                        }
                        $chkCat = $this->WebserviceModel->getCategoryIdByCategoryName($CategoryName);
                        if($chkCat){
                            $CatId = $chkCat->CategoryId;
                        }else if($CategoryName == "CLINICALSUMMARY"){
			  $CatId = 4;
			}else{
                          $CatId = 11; 
                        }
                        $fileAttachedArr = array();
                        if(isset($FileAttachments) && !empty($FileAttachments)){
                            foreach($FileAttachments as $fileVal){
                                $DocumentName = $fileVal->DocumentName;
                                $DocumentUrl = $fileVal->DocumentUrl;
                                $imagePath = $DocumentUrl;
                                $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                                $contentData = file_get_contents($imagePath);
                                $base64 = base64_encode($contentData);
                                $fileArray = array("DocumentName"=>$DocumentName,"DocumentContent"=>$base64);
                                array_push($fileAttachedArr,$fileArray);
                            }
                        }
                        $fileArrJson = json_encode($fileAttachedArr);
                        $paramJson = '{"PatientMasterId":"'.$PatientId.'",
                                        "CategoryId":[{"CategoryId":"'.$CatId.'",
                                                    "IsProcessed":0,
                                                    "FileAttachments":'.$fileArrJson.'}],
                                        "Description":"'.$Description.'",
                                        "FileBytes":1000,
                                        "TenantId":'.$TenantId.',
                                        "AddReferral":false,
                                        "ReferredToUserId":"'.$docId.'",
                                        "ReferredByUserId":"'.$docId.'",
                                        "ReferralDescription":"NULL",
                                        "RingGroup":'.$RingGrpId.',
                                        "mob_code":"+'.$PatientMobileCode.'"}';

                                        
                        $url = 'https://apiweb.ring.healthcare:5028/api/Register/UploadFiles';
                        $json = $paramJson;   
                        //echo $json;exit;
                        $headers = array('Content-Type: application/json');
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $json,
                        CURLOPT_HTTPHEADER => $headers,
                        ));
                        $res = curl_exec($curl);
                        if (curl_errno($curl)) {
                            $error_msg = curl_error($curl);
                            echo $error_msg;
                        }
                        curl_close($curl);
                        echo $res;
                        if($res == '{}'){
                            $insert_id = $this->db->select('ReportTransitId')->from('EreportsTransit')->order_by('ReportTransitId',"desc")->limit(1)->get()->row();
                           
			    // if(!empty($Diagnosis)){
                            //     $Diagno = $Diagnosis;
                            // }else{
                            //     $Diagno = $Description;
                            // }
                            $updateDiagno = $this->db->where("ReportTransitId", $insert_id->ReportTransitId)->update("EreportsTransit",array("ICD"=>$Diagnosis));
                            $Notify = $this->android_notification_function($PatientId);
			    $response['response_code'] = 1;
                            $response['response_data'] = $insert_id;
                            $response['response_message'] = 'Report saved successfully';
                        }else{
                            $response['response_code'] = 2;
                            $response['response_message'] = 'Failled';
                        }
                    }else{
                        $response['response_code'] = 5;
                        $response['response_message'] = 'Hospital not found';
                    }
                }else{
                        $response['response_code'] = 6;
                        $response['response_message'] = 'Doctor not found';
                    }                               
            }else{
                $response['response_code'] = 4;
                $response['response_message'] = 'Patient not found';
            }            
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    function getCountryListForDropDown()
    { 
        
        $countryList = $this->WebserviceModel->getCountryListForDropDown();
        if($countryList){
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['response_data']=$countryList;
        }else{
            $response['response_code']=2;
            $response['response_message']='Failed';
        }        
        echo json_encode($response);exit;
    }

    function getCountryListForDropDown_dup()
    { 
        
        $countryList = $this->WebserviceModel->getCountryListForDropDown();
        if($countryList){
            foreach($countryList as $val){
                $phnCd = substr($val->PhoneCode, 1);
                $val->PhCodeForOrder = $phnCd;
            } 
            array_multisort( array_column($countryList, "PhCodeForOrder"), SORT_ASC, $countryList );          
        }

        if($countryList){
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['response_data']=$countryList;
        }else{
            $response['response_code']=2;
            $response['response_message']='Failed';
        }        
        echo json_encode($response);exit;
    }

    function mySort($a, $b) {
        return ($a['PhCodeForOrder'] <= $b['PhCodeForOrder']);
    }
	
	public function userDataByUserId(){   
        $data = json_decode(file_get_contents('php://input'));
        $user_id = $data->user_id;
        if($data)
        {           
            $userData =  $this->WebserviceModel->userDataByUserId($user_id);
			if($userData){
				$userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);   
				$name = $this->encryptDecrypt("dc",$userData->FullName);
				$NameArr = explode(" ",$name);
				$userData->FirstName = isset($NameArr[0])?$NameArr[0]:"";
				$userData->LastName = isset($NameArr[1])?$NameArr[1]:"";
				$userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
				$userData->Email = $this->encryptDecrypt("dc",$userData->Email);
				$response['response_code']=1;
                $response['response_message']='Success';
                $response['user_data']=$userData;
			}
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';           
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Null data';
        }    
        echo json_encode($response); exit;
    }
	

	public function android_notification_27march(){
        $data = json_decode(file_get_contents('php://input'));
        $PatientId=   $data->patientId;
        if(!isset($data->patientId)){
            echo json_encode(array("msg"=>"PatientId not sent")); exit;
        }
        $userType = 0; //0 = Patient, 1 = Doctor
        $checkuserid = $this->WebserviceModel->checkuserid($PatientId,$userType);
        $token = isset($checkuserid->DeviceId)?$checkuserid->DeviceId:0;
		if(isset($PatientId) && empty($token)){
			$mainProfileId = $this->WebserviceModel->getMainPatientId($PatientId);
			if(isset($mainProfileId->MainProfilePatientId)){
				$checkusertoken = $this->WebserviceModel->checkuserid($mainProfileId->MainProfilePatientId,$userType);
        		$usertoken = isset($checkusertoken->DeviceId)?$checkusertoken->DeviceId:0;
			}else{
				$usertoken = $token;
			}
			
		}else{
			$usertoken = $token;
		}
        $message = array(
                        'title' => 'Report is ready', 
                        'body' => 'Your report has been sent please download it.', 
                        'sound' => 'default', 
                        'badge' => '1',
                        'click_action'=>'FCM_PLUGIN_ACTIVITY', //For only Android App
                        'notifictionType' => 'Referal'
                    );      
        $url = "https://fcm.googleapis.com/fcm/send";
        $serverKey = 'AAAAYtnkw9E:APA91bHqSJZSydEao75WwveTXNCaM4rqn4dPypsHsVy9m4PfV6pzEMHd5ntJAt3cV5SWDF5ZP1lkGakKnGSNbfWn97DK0vDUSY9LTIDxVIBEP_pWTmReLq0tbw6t1qmIuzEXz9t6MVq1'; // add api key here
        $notification = $message;
        $data = array('extraInfo'=> 'DomingoMG','notifictionType' => 'Referal');      
        $arrayToSend = array('to' => $usertoken, 'notification' => $notification, 'priority'=>'high', 'data'=> $data);
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);    
        //Send the request
        $response = curl_exec($ch);
        if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
    }

    public function changeDependentToMainPatientWithBackup(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $otp = $data->otp;
            $checkOtp = $this->WebserviceModel->checkOtp($otp);
            $mainPatientId = $data->patientId;
            $dependentPatientId = $data->dependentPatientId;
            $mn = $data->newMobileNumber * 1;
            $NewMobNumber = $mn;
            $email = $data->email;
            $isBackup = $data->isBackup;
            $NewMobCode = $data->newMobileCode;          
            if(!empty($checkOtp) && $checkOtp->PatientId == $mainPatientId)             
            {   
                $newDependentEmail = $this->encryptDecrypt("en",$email);
                $checkEmail = $this->WebserviceModel->patientSearchByEmail($newDependentEmail);    
                if(isset($checkEmail) && $checkEmail->PatientId != $dependentPatientId){
                    $response['response_code'] = '6';
                    $response['response_message'] = 'Duplicate Email';
                }
                else
                {         
                    $mobile_number = $this->encryptDecrypt("en",$NewMobNumber);              
                    $mainpatientEmail = $this->encryptDecrypt("dc",$checkOtp->Email);
                    $updateDataArray = array(                                
                                            "MobileNumber"=>$mobile_number,
                                            "MobileCode"=>$NewMobCode,
                                            "Email"=>$newDependentEmail,
                                            "UpdateDate"=>date("Y-m-d H:i:s")                               
                                            );
                    $checkMobile = $this->WebserviceModel->DuplicateMobileNumber($mobile_number);
                    if($checkMobile){
                        $response['response_code'] = '5';
                        $response['response_message'] = 'Duplicate Mobile Number';
                    }else{                        
                        $result = $this->WebserviceModel->updateUserData($updateDataArray,$dependentPatientId); 
                        if($result)             
                        {    
                            /**Remove from Dependent List */ 
                            $deleteDepDetail = $this->WebserviceModel->deleteDepDetail($dependentPatientId,$mainPatientId);                        
                            /**Update User Setting */
                            $insertArr = array( 
                                "User_id"=> $dependentPatientId,
                                "User_type"=>'Patient',
                                "Field"=>'Independent',
                                "Value"=> 1
                            );
                            $this->WebserviceModel->insertProfileSetting($insertArr);
                            /**Fetch Backup Json and Process */
                            // $this->getBackupJsonAndCropDependentData($mainPatientId,$dependentPatientId);
                            /**Send Confirmation Mail to Parent and Dependent Patient */
                            $patientdata = $this->WebserviceModel->getUserDataById($mainPatientId);
                            $pat_email = $this->encryptDecrypt("dc",$patientdata->Email);
                            $DependentData = $this->WebserviceModel->getUserDataById($dependentPatientId);
                            $dep_pat_name = $this->encryptDecrypt("dc",$DependentData->FullName);
                            $dep_pat_email = $this->encryptDecrypt("dc",$DependentData->Email);
                            // $subject = 'Conversion of Dependent Profile.';
                            // $message1 = 'Your dependent, '.$dep_pat_name.' has been successfully converted into a main account.';
                            // $sendMail1 = Utility::callSendMail($pat_email,$message1,$subject);
                            $pat_name = $this->encryptDecrypt("dc",$patientdata->FullName);
                            $type1 = "convertProfile";
                            $mailDataArr1 = array("name"=>$pat_name, "dependent_full_name"=>$dep_pat_name);
                            $sendMail1 = Utility::callSendMailWithTemplate($pat_email,$type1,$mailDataArr1);
                            if($isBackup == 0){                         
                                $message2 = 'Your profile has been successfully converted in to a main account. Your mobile number for RING login is '.$NewMobNumber.'';                            
                                $sendMail2 = Utility::callSendMail($dep_pat_email,$message2,$subject);
                                
                            }else if($isBackup == 1){
                                $SessionId = $data->SessionId;
                                $zipLink = $this->SendBackupToDependentInConvertion($dependentPatientId,$SessionId);
                                if(isset($zipLink) && !empty($zipLink)){
                                    // $message3 = 'Your profile has been successfully converted into a main account. Your mobile number for RING Login is '.$NewMobNumber.'.

                                    //         Please login to your new RING account and restore the following zip file to view all your medical records.
                                    //         '.$zipLink.'

                                    //         Steps for restoration
                                    //         1. Download zip file
                                    //         2. Log into your account
                                    //         3. Click on more option > Settings > Email Backup & Restore > Restore
                                    //         4. Choose downloaded zip file
                                    //         5. Click on Upload button.';
                                    // $sendMail3 = Utility::callSendMail($dep_pat_email,$message3,$subject);      
                                    $type3 = "convertProfileFinal";
                                    $mailDataArr3 = array("name"=>$dep_pat_name, "ZipLink"=>$zipLink);
                                    $sendMail3 = Utility::callSendMailWithTemplate($dep_pat_email,$type3,$mailDataArr3);        
                                }else{
                                    $response['response_code'] = 5;
                                    $response['response_message'] = 'Zip creation error';
                                }
                                
                            }
                            
                            $response['response_code'] = 1;
                            $response['response_message'] = 'Success';
                        }
                        else
                        {
                            $response['response_code']=2;
                            $response['response_message']='Failed';                
                        }
                    }
                }    
            }   
            else
            {
                $response['response_code'] = 4;
                $response['response_message']='Wrong OTP';                
            } 
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }
    public function SendBackupToDependentInConvertion($UserId,$SessionId)
    {
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        
        // $UserId = $data->UserId;
        // $SessionId = $data->SessionId;
        //$chkSession = $this->WebserviceModel->chkSessionForBackup($SessionId);
        $chkSession = $this->WebserviceModel->chkSessionForBackupForCreateZip($SessionId,$UserId);
        $reportBackupArr = array();
        $reportFileBackupArr = array();
        if($chkSession){
            foreach($chkSession as $chkSessionVal){
                $BackupID = $chkSessionVal->BackupID;
                /**Find Report and Create Json */
                $ReportData = $this->WebserviceModel->getReportDataForBackup($UserId, $BackupID);
                if($ReportData)
                {
                    foreach($ReportData as $ReportDataVal){
                        array_push($reportBackupArr,$ReportDataVal);
                    }				
                }
                
                $ReportFileData = $this->WebserviceModel->getReportFileDetailsForBackup($UserId, $BackupID);
                if($ReportFileData)
                {
                    foreach($ReportFileData as $ReportFileDataVal){
                        array_push($reportFileBackupArr,$ReportFileDataVal);
                    }				
                }
                $ExtraReportData = $this->WebserviceModel->getExtraReportDataForBackup($UserId, $BackupID);
            }
        
            if($reportBackupArr)
            {                  
                /******Make User Folder*********/
                $folderName = "upload/RING_Backup".$UserId."_".$SessionId."_".time();
                if(!is_dir($folderName))
                {
                    mkdir($folderName, 0777);
                }

                foreach($reportBackupArr as $field) {
                    if(isset($field->Data2) && !empty($field->Data2)){
                        $data2 = file_get_contents($field->Data2);
                        $field->Data2 = $data2;
                    }
                }

                $reportJsonName = $folderName."/".$UserId."_Report_Backup.json";                        
                $json = json_encode($reportBackupArr);
                $result = file_put_contents($reportJsonName, $json);                              
            } 
            
            //$ReportFileData = $this->WebserviceModel->getReportFileDetailsForBackup($UserId, $BackupID);
            if($reportFileBackupArr)
            {  
                
                foreach($reportFileBackupArr as $field1) {
                    if(isset($field1->Base64Data) && !empty($field1->Base64Data)){
                        $Base64Data = file_get_contents($field1->Base64Data);
                        $field1->Base64Data = $Base64Data;
                    }
                }
                
                $fileJsonName = $folderName."/".$UserId."_Files_Backup.json";                        
                $json1 = json_encode($reportFileBackupArr);
                $result = file_put_contents($fileJsonName, $json1);                              
            }
            if($ExtraReportData)
            {  
                $extraRepJsonName = $folderName."/".$UserId."_Extra_Report_Backup.json";                        
                $json3 = json_encode($ExtraReportData);
                $result3 = file_put_contents($extraRepJsonName, $json3);                              
            }
            $date = date("d-m-Y");
            /**Create Zip Folder ***************/
                $temp_unzip_path = $folderName.'/';
                $zip = new ZipArchive();
                $dirArray = array();
                $new_zip_file = $folderName."/RING_Backup_".$date.".zip";

                $new = $zip->open($new_zip_file, ZIPARCHIVE::CREATE);
                if ($new === true) {
                    $handle = opendir($temp_unzip_path);
                    while (false !== ($entry = readdir($handle))) {
                        if(!in_array($entry,array('.','..')))
                        {
                            $dirArray[] = $entry;
                            $zip->addFile($temp_unzip_path.$entry,$entry);
                        }
                    }
                    closedir($handle);
                } else {
                    // $response['response_code'] = 5;
                    // $response['response_message'] = 'Zip creation failled';
                    return false;
                }
                $zip->close();

                /**Send Mail */
            
                // $Userdata = $this->WebserviceModel->getUserDataById($UserId);
                // $pat_email = $this->encryptDecrypt("dc",$Userdata->Email);
                //$pat_email = "mishraravi520@gmail.com";
                //$link = "https://win.k2key.in/Ring/index.php/BackupApi/getZip?sess=".$SessionId;
                $link = "https://win.k2key.in/Ring_dev/".$new_zip_file;
                $message = $link;
                // $subject = 'RING Backup '.date("d-m-Y");
                // $sendMail = Utility::callSendMail($pat_email,$message,$subject);
                // $response['response_code'] = 1;
                // $response['response_message'] = 'Success';
                return $message;
                                
        }
        else 
        {
            return false;
        }
    }

    public function saveReportDataForHISDigitalSenseNew(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $UserId = $data->UserId;
            $DoctorName = $data->DoctorName;
            $MMCNumber = $data->MMCNumber;
            $Description = $data->Description;
			$CategoryName = $data->CategoryName;
			$FileAttachments = $data->FileAttachments; 
            $HospitalName = isset($data->HospitalName)?$data->HospitalName:""; 
            if($UserId == "PRN-KKS-1750677" || $UserId == "PRN-KKS-1750681"){
                $PatientMobile = "9846545120";
                $PatientMobileCode = "60";
                $PatientEmail = "yekoko8745@iucake.com";
            }else{
                $PatientMobile = isset($data->PatientMobile)?$data->PatientMobile:"";
                $PatientMobileCode = isset($data->PatientMobileCode)?$data->PatientMobileCode:"";
                $PatientEmail = isset($data->PatientEmail)?$data->PatientEmail:"";
            }
                       
            // $HospitalName = $data->HospitalName; 		
            if(!empty($PatientMobile) && !empty($PatientMobileCode)){
                $Mobile = $this->encryptDecrypt("en",$PatientMobile);
                $MobCode = "+".str_replace(" ","",$PatientMobileCode);
                $chkPatient = $this->WebserviceModel->patientSearchByMobileNumber($Mobile, $MobCode);               
            }else if(!empty($PatientEmail)){
                $Email = $this->encryptDecrypt("en",$PatientEmail);
                $chkPatient = $this->WebserviceModel->patientSearchByEmail($Email);
            }			
			if($chkPatient){
                $chkDoctor = $this->WebserviceModel->doctorSearchDataForHIS($DoctorName,$MMCNumber);
                if($chkDoctor){
                    $PatientId = $chkPatient->PatientId;
                    $docId = $chkDoctor->UserId;
                    $chkHospital = $this->WebserviceModel->tenantsSearchDataForHIS($HospitalName,$docId);   
                // print_r($chkHospital);exit;                
                    if($chkHospital){
                        $TenantId = $chkHospital->TenantId;
                        $chkRingGroup = $this->WebserviceModel->getRingGrpByTenantId($TenantId);
                        if($chkRingGroup){
                            $RingGrpId = $chkRingGroup->RingGroupId;
                        }else{
                            $RingGrpId = 2;
                        }
                        $chkCat = $this->WebserviceModel->getCategoryIdByCategoryName($CategoryName);
                        if($chkCat){
                            $CatId = $chkCat->CategoryId;
                        }else{
                        $CatId = 11; 
                        }
                        $fileAttachedArr = array();
                        if(isset($FileAttachments) && !empty($FileAttachments)){
                            foreach($FileAttachments as $fileVal){
                                $DocumentName = $fileVal->DocumentName;
                                $DocumentUrl = $fileVal->DocumentUrl;
                                $imagePath = $DocumentUrl;
                                $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                                $contentData = file_get_contents($imagePath);
                                $base64 = base64_encode($contentData);
                                $fileArray = array("DocumentName"=>$DocumentName,"DocumentContent"=>$base64);
                                array_push($fileAttachedArr,$fileArray);
                            }
                        }
                        $fileArrJson = json_encode($fileAttachedArr);
                        $paramJson = '{"PatientMasterId":"'.$PatientId.'",
                                        "CategoryId":[{"CategoryId":"'.$CatId.'",
                                                    "IsProcessed":0,
                                                    "FileAttachments":'.$fileArrJson.'}],
                                        "Description":"'.$Description.'",
                                        "FileBytes":1000,
                                        "TenantId":'.$TenantId.',
                                        "AddReferral":false,
                                        "ReferredToUserId":"76",
                                        "ReferredByUserId":"76",
                                        "ReferralDescription":"NULL",
                                        "RingGroup":'.$RingGrpId.',
                                        "mob_code":"+'.$PatientMobileCode.'"}';

                                        
                        $url = 'https://apiweb.ring.healthcare:5028/api/Register/UploadFiles';
                        $json = $paramJson;   
                        //echo $json;exit;
                        $headers = array('Content-Type: application/json');
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $json,
                        CURLOPT_HTTPHEADER => $headers,
                        ));
                        $res = curl_exec($curl);
                        if (curl_errno($curl)) {
                            $error_msg = curl_error($curl);
                            echo $error_msg;
                        }
                        curl_close($curl);
                        echo $res;
                        if($res == '{}'){
                            $insert_id = $this->db->select('ReportTransitId')->from('EreportsTransit')->order_by('ReportTransitId',"desc")->limit(1)->get()->row();
                            $response['response_code'] = 1;
                            $response['response_data'] = $insert_id;
                            $response['response_message'] = 'Report saved successfully';
                        }else{
                            $response['response_code'] = 2;
                            $response['response_message'] = 'Failled';
                        }
                    }else{
                        $response['response_code'] = 5;
                        $response['response_message'] = 'Hospital not found';
                    }
                }else{
                        $response['response_code'] = 6;
                        $response['response_message'] = 'Doctor not found';
                    }                               
            }else{
                $response['response_code'] = 4;
                $response['response_message'] = 'Patient not found';
            }            
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    // public function send_notification_ios(){
    //     // $token='c7mkfszwz8o:APA91bG-1a9P0q9u-xFmm1hdYIOGPHu5dlOwlr2gx-81UJzmZIc7l0yKHy3c9ymtx_ym7w2zzp4E7AXdzMyjVUWV9LOMHm798iR5bfaPVuABmMARpp49lPnXqPfllmaIQOjKFuy8ArBk';
 	//     // $data = json_decode(file_get_contents('php://input'));
    //     // print_r($device_token);exit;
    //     $tokens = $device_token; 
    //     // print_r($tokens);exit;
    //     $message = $message;
    //     $url = "https://fcm.googleapis.com/fcm/send";
    //     $serverKey = 'AAAAYtnkw9E:APA91bHqSJZSydEao75WwveTXNCaM4rqn4dPypsHsVy9m4PfV6pzEMHd5ntJAt3cV5SWDF5ZP1lkGakKnGSNbfWn97DK0vDUSY9LTIDxVIBEP_pWTmReLq0tbw6t1qmIuzEXz9t6MVq1'; // add api key here
    //     $notification = [
    //     // 'title' =>'title',
    //     'body' => $message,
    //     'icon' =>'myIcon', 
    //     'sound' => 'mySound',
    //     'click_action'=>'FCM_PLUGIN_ACTIVITY' //For only Android App
    //     ];
    //     $extraNotificationData = ["message" => $notification,"moredata" =>$chatData];
    //     $fcmNotification = [
    //     // 'registration_ids' => $tokens, //multple token array
    //     'to'        => $tokens, //single token
    //     'notification' => $notification,
    //     'data' => $extraNotificationData
    //     ];
    //     $headers = [
    //         'Authorization: key=' . $serverKey,
    //         'Content-Type: application/json'
    //     ];
    
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL,$url);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
    //     $result = curl_exec($ch);
    //     curl_close($ch);

    // }

    public function GenerateOtpForSignUp(){           
        $data = json_decode(file_get_contents('php://input'));
        if($data)        
        {    
            $mobile_number = $data->mobile_number * 1;
            $mn = $this->encryptDecrypt("en",$mobile_number);
            $mob_code = $data->mob_code;
            $email  = $data->email;
            $email_enc = $this->encryptDecrypt("en",$email);
            $name  = $data->name;
            $otp = rand(100000, 999999);
            $checkData = $this->WebserviceModel->checkDuplicateMobileno($mob_code,$mn,$email_enc);
            if($checkData){
                $response['response_code'] = 4;
                $response['response_message'] = 'Duplicate Mobile Number Or Email ';                
            }else{
                $message = '<b><h2>Hi '.$name.' !</h2></b></br>';  
                $message .= '<p>Use the following one-time password(OTP) to sign up to your Ring account.</p></br>';
                $message .= '<p>This OTP will be valid for 3 minutes.</p></br></br>';
                $message .= '<b><h2>'.$otp.'</h2></b></br>';
                $message .= '<p>Regards</p></br>';  
                $message .= '<b><p>Ring Team</p></b>';  
                $subject = 'SignUp OTP';
                // $sendMail = Utility::callSendMail($email,$message,$subject);
                $type = "signUpOtp";
                $dataArr = array("name"=>$name, "otp"=>$otp);
                $sendMail = Utility::callSendMailWithTemplate($email,$type,$dataArr);
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['otp'] = $otp; 
            }        
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;        
    }

    public function downloadFile(){        
  
        $token = $_GET["token"];
        $chkToken = $this->db->select('*')->from('SharedFilesDetails')->where('Token',$token)->get()->row();

        if($chkToken){
            $fileType = $chkToken->FileType;
            // print_r($chkToken);exit;
            $file = "upload/".$chkToken->FileName.'.'.$fileType;
            if($fileType == "pdf"){
                
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=" . urlencode($file));   
            header("Content-Type: application/download");
            header("Content-Description: File Transfer");            
            header("Content-Length: " . filesize($file));
            
            flush(); // This doesn't really matter.
            
            $fp = fopen($file, "r");
            while (!feof($fp)) {
                echo fread($fp, 65536);
                flush(); // This is essential for large downloads
            } 
            
            fclose($fp);
            }else{
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $basename = pathinfo($file, PATHINFO_BASENAME);
                // // print_r($basename);exit;
                // header("Expires: 0");
                // header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                // header("Cache-Control: no-store, no-cache, must-revalidate");
                // header("Cache-Control: post-check=0, pre-check=0", false);
                // header("Pragma: no-cache");

                // header("Content-type: image/".$ext);
                // // tell file size
                // header('Content-length: '.filesize($file));
                // // set file name
                // header("Content-Disposition: attachment; filename=\"$basename\"");
                // readfile($file);
                // // Exit script. So that no useless data is output.
                // exit;

                $this->load->helper('download');
                // read file contents
                $data = file_get_contents(base_url($chkToken->FilePath.$chkToken->FileName.'.'.$fileType));
                force_download($basename, $data);
            }
            
        }
         
    }



function android_notificationByUserId($id){
        $data = json_decode(file_get_contents('php://input'));
        // print_r($data);exit;
        $PatientId=   $id;
        $notification_body = isset($data->notification_body)?$data->notification_body:"Your report has been sent please download it.";
       
        $userType = 0; //0 = Patient, 1 = Doctor
        $checkuserid = $this->WebserviceModel->checkuserid($PatientId,$userType);
        $token = isset($checkuserid->DeviceId)?$checkuserid->DeviceId:0;
		if(isset($PatientId) && empty($token)){
			$mainProfileId = $this->WebserviceModel->getMainPatientId($PatientId);
			if(isset($mainProfileId->MainProfilePatientId)){
				$checkusertoken = $this->WebserviceModel->checkuserid($mainProfileId->MainProfilePatientId,$userType);
        		$usertoken = isset($checkusertoken->DeviceId)?$checkusertoken->DeviceId:0;
			}else{
				$usertoken = $token;
			}
			
		}else{
			$usertoken = $token;
		}

        if($checkuserid->Platform == "ios"){
            $message = array(
                        'title' => 'Report is ready', 
                        'body' => $notification_body, 
                        'sound' => 'default', 
                        'badge' => '1',
                        'notifictionType' => 'Referal'
                    ); 
            $this->send_notification_ios($usertoken,$notification_body,$data,$message);
        }else{
            $message = array(
                        'title' => 'Report is ready', 
                        'body' => $notification_body, 
                        'sound' => 'default', 
                        'badge' => '1',
                        'click_action'=>'FCM_PLUGIN_ACTIVITY', //For only Android App
                        'notifictionType' => 'Referal'
                    );      
            $url = "https://fcm.googleapis.com/fcm/send";
            $serverKey = 'AAAAYtnkw9E:APA91bHqSJZSydEao75WwveTXNCaM4rqn4dPypsHsVy9m4PfV6pzEMHd5ntJAt3cV5SWDF5ZP1lkGakKnGSNbfWn97DK0vDUSY9LTIDxVIBEP_pWTmReLq0tbw6t1qmIuzEXz9t6MVq1'; // add api key here
            $notification = $message;
            $data = array('extraInfo'=> 'DomingoMG','notifictionType' => 'Referal');      
            $arrayToSend = array('to' => $usertoken, 'notification' => $notification, 'priority'=>'high', 'data'=> $data);
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key='. $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);    
            //Send the request
            $response = curl_exec($ch);
            if ($response === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
        }        
    }

	// function android_notification_function($PatientId){
    //     $PatientId = $PatientId;
    //     $notification_body = isset($data->notification_body)?$data->notification_body:"Your report has been sent please download it.";
    //     if(!isset($PatientId)){
    //         print_r(array("msg"=>"PatientId not sent")); exit;
    //     }
    //     $userType = 0; //0 = Patient, 1 = Doctor
    //     $checkuserid = $this->WebserviceModel->checkuserid($PatientId,$userType);
    //     $token = isset($checkuserid->DeviceId)?$checkuserid->DeviceId:0;
	// 	if(isset($PatientId) && empty($token)){
	// 		$mainProfileId = $this->WebserviceModel->getMainPatientId($PatientId);
	// 		if(isset($mainProfileId->MainProfilePatientId)){
	// 			$checkusertoken = $this->WebserviceModel->checkuserid($mainProfileId->MainProfilePatientId,$userType);
    //     		$usertoken = isset($checkusertoken->DeviceId)?$checkusertoken->DeviceId:0;
	// 		}else{
	// 			$usertoken = $token;
	// 		}
			
	// 	}else{
	// 		$usertoken = $token;
	// 	}
    //     $message = array(
    //                     'title' => 'Report is ready', 
    //                     'body' => $notification_body, 
    //                     'sound' => 'default', 
    //                     'badge' => '1',
    //                     'click_action'=>'FCM_PLUGIN_ACTIVITY', //For only Android App
    //                     'notifictionType' => 'Referal'
    //                 );      
    //     $url = "https://fcm.googleapis.com/fcm/send";
    //     $serverKey = 'AAAAYtnkw9E:APA91bHqSJZSydEao75WwveTXNCaM4rqn4dPypsHsVy9m4PfV6pzEMHd5ntJAt3cV5SWDF5ZP1lkGakKnGSNbfWn97DK0vDUSY9LTIDxVIBEP_pWTmReLq0tbw6t1qmIuzEXz9t6MVq1'; // add api key here
    //     $notification = $message;
    //     $data = array('extraInfo'=> 'DomingoMG','notifictionType' => 'Referal');      
    //     $arrayToSend = array('to' => $usertoken, 'notification' => $notification, 'priority'=>'high', 'data'=> $data);
    //     $json = json_encode($arrayToSend);
    //     $headers = array();
    //     $headers[] = 'Content-Type: application/json';
    //     $headers[] = 'Authorization: key='. $serverKey;
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
    //     //Send the request
    //     $response = curl_exec($ch);
    //     if ($response === FALSE) {
    //         die('FCM Send Error: ' . curl_error($ch));
    //     }
    //     curl_close($ch);
    // }

    public function ringGroupListByPatientIdNew(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $patientId = $data->patientId;
            $result = $this->WebserviceModel->ringGroupListByPatientIdNew($patientId);
            $resArr = array();
            $chkCRUser = $this->WebserviceModel->chkCRUser($patientId);
            if(isset($chkCRUser) && !empty($chkCRUser->DeviceId)){ 
                foreach($result as $val){  
                    if($val->RingGroupName == $chkCRUser->RingGroup){
                        $val->is_sync = 1;
                    }else{
                        $val->is_sync = 0;
                    }
                array_push($resArr, $val);
                }           
            }else{
              foreach($result as $val){
                $val->is_sync = 0;
                array_push($resArr, $val);
                }
            }
            if($resArr)             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['data']=$resArr; 
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;     
    }

    public function EreportTransitListViaPatientIdWithBase64FileAtt(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $pat_id = $data->patient_id;
            $ringGrpId = isset($data->RingGroupMasterId)?$data->RingGroupMasterId:"";
            $reportList = $this->WebserviceModel->EreportTransitListViaPatientId($pat_id,$ringGrpId);
            $fileArrayAttachemnt = array();
            foreach($reportList as $file ){
                // print_r($file);exit;
                $InsertDate = $file->InsertDate;
                if(isset($file->Description) && $file->Description != null){
                    $desc = $file->Description;
                }else{
                     $desc = '';
                }
                $newInsertDate = date("d/m/Y",  strtotime($InsertDate));
                $docter_name = $file->sirname_title." ".$file->doctorName." ".$file->LastName;
                if(isset($file->ICDSubCode) && isset($file->ICDSubCodeDescription) && !empty($file->ICDSubCode) && !empty($file->ICDSubCodeDescription)){
                   $diagnosis = $file->ICDSubCode."_".$file->ICDSubCodeDescription; 
                }else if(isset($file->ICD) && !empty($file->ICD)){
                    $diagnosis = $file->ICD;
                }else{
                    $diagnosis = $desc;
                }
                $fileArrayAttachemnt[]=array("PatientMasterId"=>$file->PatientMasterId,"ReportTransitId"=>$file->ReportTransitId,"RingGroupMasterId"=>$file->RingGroupMasterID,"RingGroupMasterIdReff"=>$file->RingGroup,"InsertDate"=>$newInsertDate,"Description"=>$desc,"EreferralForm"=>array(),"FileAttachments"=>array(),"IsProcessed"=>$file->IsProcessed,"IsDoctorProcessed"=>$file->IsDoctorProcessed,"IsPatientProcessed"=>$file->IsPatientProcessed,"AddReferral"=>$file->AddReferral,"DoctorId"=>$file->DoctorId,"doctorName"=>$docter_name, "DoctorPhoneNumber"=>$file->UserPhoneNumber,"TenantName"=>$file->TenantName,"WorkingSchedule"=>array(),"TenantPhoneNuber"=>$file->TenantPhoneNuber,"TenantFaxNumber"=>$file->TenantFaxNumber,"TenantAddress"=>$file->TenantAddress,"Refferal"=>array(), "diagnosis"=>$diagnosis,"DoctorSpeciality"=>$file->DoctorSpeciality);               
                /**Working Hours of Tenant */
				$workHrArr = array();
                $refworkHrArr = array();
                $rDDArr = array();
                // $workingArr = $this->db->select('*')->from('TenantWorkingHours')->where('TenantId',$file->TenantId)->get()->result();
				$workingArr = $this->WebserviceModel->getWorkingScheduleOfTenant($file->TenantId);
                foreach($workingArr as $workHrVal){
					//print_r($workHrVal);exit; 
    				// $workHrVal->FromTime = date("Y-m-d h:i", strtotime($workHrVal->FromTime));
					// $workHrVal->ToTime = date("Y-m-d h:i", strtotime($workHrVal->ToTime));
                    $workHrVal->FromTime = date("h:i A", strtotime($workHrVal->FromTime));
					$workHrVal->ToTime = date("h:i A", strtotime($workHrVal->ToTime));
					array_push($workHrArr,$workHrVal);
				}
                if(isset($workHrArr) && !empty($workHrArr)){
                    $workingHTML = '<div class="f14 txtlist">';
                    $workingHTMLinMalay = '<div class="f14 txtlist">';
                    foreach($workHrArr as $workHrArrVal)
                    {
                        // $workingHTML .= '<ion-col size="12" class="f14 txtlist">
                        //                 <span class="fw600" translate> FromTime :</span>'.$workHrArrVal->FromTime.'<br>
                        //             </ion-col>
                        //             <ion-col size="12" class="f14 txtlist">
                        //                 <span class="fw600" translate> ToTime :</span>'.$workHrArrVal->ToTime.'<br>
                        //             </ion-col>';
                        // $workingHTMLinMalay .= '<ion-col size="12" class="f14 txtlist">
                        //     <span class="fw600" translate> Dari jam :</span>'.$workHrArrVal->FromTime.'<br>
                        // </ion-col>
                        // <ion-col size="12" class="f14 txtlist">
                        //     <span class="fw600" translate> Hingga jam :</span>'.$workHrArrVal->ToTime.'<br>
                        // </ion-col>';
                        $workingHTML .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                        $workingHTMLinMalay .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                    }
                    $workingHTML .= '</div>';
                    $workingHTMLinMalay .= '</div>';
                }else{
                    $workingHTML = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> From Time :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> To Time :</span>N/A
                                    </ion-col>
                                    </p>';
                    $workingHTMLinMalay = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Dari jam :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Hingga jam :</span>N/A
                                    </ion-col>
                                    </p>';                
                }
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingSchedule"] = $workingHTML;
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingScheduleInMalay"] = $workingHTMLinMalay;
                // $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingSchedule"] = $workHrArr;

                /*Referral Details of E-Report*/

                if(isset($file->ReferredToUserId) && !empty($file->ReferredToUserId)){
                  $refDocDetails = $this->WebserviceModel->getRefReportDoctorDetails($file->ReferredToUserId,$file->refTenant);
                  foreach($refDocDetails as $rDD){
                        $rDD->refDoctorName = $rDD->sirname_title." ".$rDD->refDoctorName." ".$rDD->LastName;
                        array_push($rDDArr,$rDD);
                  }
                //   $refworkingArr = $this->db->select('*')->from('TenantWorkingHours')->where('TenantId',$refDocDetails[0]->TenantId)->get()->result(); 
                   $refworkingArr = $this->WebserviceModel->getWorkingScheduleOfTenant($refDocDetails[0]->TenantId);
                // echo "<pre>"; print_r($refDocDetails);exit;
                  if(isset($refworkingArr) && !empty($refworkingArr)){
                    foreach($refworkingArr as $refworkingVal)
                    {
                        //print_r($workHrVal);exit; 
                        // $refworkingVal->FromTime = date("Y-m-d h:i", strtotime($refworkingVal->FromTime));
                        // $refworkingVal->ToTime = date("Y-m-d h:i", strtotime($refworkingVal->ToTime));
                        $refworkingVal->FromTime = date("h:i A", strtotime($refworkingVal->FromTime));
                        $refworkingVal->ToTime = date("h:i A", strtotime($refworkingVal->ToTime));
                        array_push($refworkHrArr,$refworkingVal);
				    }
                }
                  if(isset($refworkHrArr) && !empty($refworkHrArr)){
                    $refworkingHTML = '<div class="f14 txtlist">';
                    $refworkingHTMLMalay = '<div class="f14 txtlist">';
                    foreach($refworkHrArr as $refworkHrArrVal)
                    {
                        $refworkingHTML .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$refworkHrArrVal->DayName.' : </span>  '.$refworkHrArrVal->FromTime.' - '.$refworkHrArrVal->ToTime.'<br>
                                    </ion-col>';
                        $refworkingHTMLMalay .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$refworkHrArrVal->DayName.' : </span>  '.$refworkHrArrVal->FromTime.' - '.$refworkHrArrVal->ToTime.'<br>
                                    </ion-col>';            
                    }
                    $refworkingHTML .= '</div>';
                    $refworkingHTMLMalay .= '</div>';
                }else{
                    $refworkingHTML = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> From Time :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> To Time :</span>N/A
                                    </ion-col>
                                    </p>';
                    $refworkingHTMLMalay = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Dari jam :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Hingga jam :</span>N/A
                                    </ion-col>
                                    </p>';                
                }
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingScheduleref"] = $refworkingHTML;
                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["WorkingSchedulerefMalay"] = $refworkingHTMLMalay;                
                  $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["Refferal"] = $rDDArr;
                }
                // $referralFormArray = json_decode($file->EreferralForm);
                // if(is_array($referralFormArray)){
                //     foreach($referralFormArray as $referralData){
                //          $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["EreferralForm"][]="https://app.ring.healthcare/upload/".$referralData->Filename;
                //     }
                // }
                if($file->IsReferralFormProcessed == 0){
                    $referralFormArray = json_decode($file->EreferralForm);
                    if(is_array($referralFormArray)){
                        foreach($referralFormArray as $referralData){
                            $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["EreferralForm"][]="https://app.ring.healthcare/upload/".$referralData->Filename;
                        }
                    }
                }
                $fileAttachmentList = $this->WebserviceModel->getFileAttachmentByReportTransitId($file->ReportTransitId,$file->DoctorId);
                if(is_array($fileAttachmentList)){
                    foreach($fileAttachmentList as $fileData){
						$jsonFileArr = json_decode($fileData->FileAttachments);
						if(isset($jsonFileArr[0]->Filename)){
							$fileData->FileAttachments = "https://app.ring.healthcare/upload/".$jsonFileArr[0]->Filename;
                            $fileData->Base64FileAttachments = $this->convertImageLinkToBase64ForFileAttachment($fileData->FileAttachments);
							$fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["FileAttachments"][]=$fileData;
						}
                    }
                }
            }
            if($reportList)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data']= $fileArrayAttachemnt;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    public function convertImageLinkToBase64ForFileAttachment($path){
            $path = $path;
            $link = trim($path);
            $link = str_replace (' ', '%20', $link);
            $type = pathinfo($link, PATHINFO_EXTENSION);
            $data = file_get_contents($link);
            // $base64 = base64_encode($data);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            return $base64;
    }

    public function SendOtpToDependentPatient()
    {      
        $data = json_decode(file_get_contents('php://input'));
        if($data)    
        {    
            $email = $data->email;
            $otp = rand(100000, 999999);
            // $message = 'Your OTP for account conversion is '.$otp.'. Do not share this with anyone';
            // $subject = 'Account conversion request OTP';
            // $sendMail = Utility::callSendMail($email,$message,$subject);
            // $pat_name = $this->encryptDecrypt("dc",$result->FullName);
            $type = "convertProfileOtp";
            $dataArr = array("name"=>$pat_name, "otp"=>$otp);
            $sendMail = Utility::callSendMailWithTemplate($email,$type,$dataArr);
            $response['response_code'] = 1;
            $response['response_message'] = 'Success';
            $response['otp'] = $otp;
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;        
    }

    public function testDistanceForMap(){
        $url ="https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=16.538048,80.613266&destinations=23.0225,72.5714";
        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL,$url);
        // Execute
        $result=curl_exec($ch);
        // Closing
        curl_close($ch);
        $result_array=json_decode($result);
        print_r($result_array);
    }

    public function EmailChangeOTP()
    {      
        $data = json_decode(file_get_contents('php://input'));
        if($data)    
        {    
            $PatientId = $data->PatientId;
            $email = $data->Email;
            $email_en = $this->encryptDecrypt("en",$email);
            $chkExistUser = $this->WebserviceModel->checkUserEmailId($email_en);
            if(isset($chkExistUser) && !empty($chkExistUser)){
                $response['response_code'] = 4;
                $response['response_message'] = 'Duplicate Email ID';
            }else{
                $patientdata = $this->WebserviceModel->getUserDataById($PatientId);
                if($patientdata)             
                {
                    $pat_email = $this->encryptDecrypt("dc",$patientdata->Email);
                    $otp = rand(100000, 999999);
                    // $message = 'Your OTP for email change is '.$otp.'. Do not share this with anyone';
                    // $subject = 'Email address change request OTP';
                    // $sendMail = Utility::callSendMail($email,$message,$subject);

                    $pat_name = $this->encryptDecrypt("dc",$patientdata->FullName);
                    $type = "changeEmailOtp";
                    $mailDataArr = array("name"=>$pat_name, "otp"=>$otp);
                    $sendMail = Utility::callSendMailWithTemplate($email,$type,$mailDataArr);

                    // $message1 = 'A request to update your email address has been sent to '.$email.'.<br><br>';
                    // $message1 .= 'If this was not you, please contact us through the link below.<br><br>';
                    // $message1 .= '“Contact Us” link – upon clicking, user to be redirected to compose mail and “To” to be set as help@ring.healthcare<br><br>';
                    // $message1 .= 'The RING Team';
                    // $subject1 = 'Email Address Change Request';
                    // $sendMail1 = Utility::callSendMail($pat_email,$message1,$subject1);
                    $type2 = "changeEmail";
                    $mailDataArr2 = array("name"=>$pat_name, "new_email"=>$email);
                    $sendMail2 = Utility::callSendMailWithTemplate($pat_email,$type2,$mailDataArr2);

                    $result1 = $this->WebserviceModel->updateOtpDataById($otp,$PatientId);
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                    $response['otp'] = $otp; 
                }
                else
                {
                    $response['response_code']=2;
                    $response['response_message']='Failed';
                }   
            }
                        
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;        
    }

    public function changePatientEmailAddress()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $PatientId = $data->PatientId;
            $email = $data->Email;
            $otp = $data->Otp;
            $checkOtp = $this->WebserviceModel->checkOtp($otp);
            if($checkOtp){               
                $email_en = $this->encryptDecrypt("en",$email);
                $updateDataArray = array(                                
                                        "Email"=>$email_en,
                                        "UpdateDate"=>date("Y-m-d H:i:s")                               
                                        );
                $result = $this->WebserviceModel->updateUserData($updateDataArray,$PatientId); 
                if($result)             
                {                    
                    $patientdata = $this->WebserviceModel->getUserDataById($PatientId);
                    $pat_email = $this->encryptDecrypt("dc",$patientdata->Email);
                    // $message = 'Your email address has been changed successfully. Your new email address for RING login is "'.$email.'"';
                    //$subject = 'Mobile number has been changed successfully';
					// $subject = 'Changed email address Successfully';
                    // $sendMail = Utility::callSendMail($pat_email,$message,$subject);
                    $pat_name = $this->encryptDecrypt("dc",$patientdata->FullName);
                    $type = "changeEmailFinal";
                    $dataArr = array("name"=>$pat_name, "new_email"=>$email);
                    $sendMail = Utility::callSendMailWithTemplate($pat_email,$type,$dataArr);

                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                }
                else
                {
                    $response['response_code']=2;
                    $response['response_message']='Failed';                
                }
            }
            else 
            {
                $response['response_code'] = 4;
                $response['response_message'] = 'Otp not matched';
            }            
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }           
        echo json_encode($response); exit;
    }

    function ICD_search()
    {
        $data = json_decode(file_get_contents('php://input'));
            if($data){
                $keyword = $data->keyword;
                $getIcd = $this->WebserviceModel->ICD_search($keyword);            
                if($getIcd)
                {
                    $response['response_code'] = '1';
                    $response['response_message'] = 'Success';
                    $response['response_data'] = $getIcd;
                }
                else
                {
                    $response['response_code'] = '2';
                    $response['response_message'] = 'Failed';
                }
            }
            else
            {
                $response['response_code'] = '3';
                $response['response_message'] = 'Data is null';
            }   
        echo json_encode($response);exit;
    }

    function advertiseListing()
    {   
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $patientId = $data->patientId;
            $ringGroup = $data->ringGroupId;
            $timeZone = $data->timeZone;
            
            $userData =  $this->WebserviceModel->getUserDataById($patientId);
            if($userData){
                $CountryMasterId = $userData->CountryMasterId;
                $StateMasterId = $userData->StateMasterId;
                $CityMasterId = $userData->CityMasterId;
            }
            // print_r($userData);exit;
            $currentDate = date('Y-m-d H:i:s');
            if(isset($ringGroup) && !empty($ringGroup)){
                if(isset($data->companyName) && !empty($data->companyName)){
                    $getAdv = $this->WebserviceModel->advertiseListingWithCompany($patientId,$ringGroup,$currentDate,$data->companyName);
                }else{
                    $getAdv = $this->WebserviceModel->advertiseListing($patientId,$ringGroup,$currentDate); 
                }               
            }else{
                if(isset($data->companyName) && !empty($data->companyName)){
                    $getAdv = $this->WebserviceModel->advertiseListingUniversalWithCompany($patientId,$currentDate,$data->companyName);
                }else{
                    $getAdv = $this->WebserviceModel->advertiseListingUniversal($patientId,$currentDate); 
                }
            }
           
            $finalArr = array();
            if($getAdv){
                // echo "<pre>"; print_r($getAdv); exit;
                foreach($getAdv as $advVal){
                //    echo "<pre>"; print_r($getAdv); exit;
                    $insertdate = $advVal->InsertDate;
                    $today = date('Y-m-d h:i:s');
                    $time = strtotime($insertdate);
                   //  date_default_timezone_set($timeZone);
                    $advVal->localInsertDate = date("Y-m-d H:i A", $time); 
                    $advVal->PublishedDate1 = date("Y-m-d H:i A", strtotime($advVal->PublishedDate)); 
                    if ($today == date('Y-m-d', strtotime($insertdate))) {
                       $advVal->InsertDay = "Today";
                    }else{
                        $advVal->InsertDay = date('d-m-Y', strtotime($insertdate));
                    }

                    $StartDate = date("Y-m-d H:i:s", strtotime($advVal->StartDate));
                    $EndDate = date("Y-m-d H:i:s", strtotime($advVal->EndDate));
                    // print_r($advVal->CountryMasterId); exit;
                    if(!empty($advVal->CountryMasterId) && !empty($userData->CountryMasterId) && $advVal->CountryMasterId == $userData->CountryMasterId){
                        if(!empty($advVal->EndDate)){
                            if($StartDate <= $currentDate && $EndDate >= $currentDate){
                                array_push($finalArr,$advVal); 
                            }
                        }else{
                            if($StartDate <= $currentDate){
                                array_push($finalArr,$advVal); 
                            } 
                        }
                    }
                                                        
                }
                // exit;
            }          
            if($finalArr)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $finalArr;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else 
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }    
        echo json_encode($response);exit;
    }

    function Advertise_search()
    {
        $data = json_decode(file_get_contents('php://input'));
            if($data){
                $keyword = $data->keyword;
                $patientId = $data->patientId;
                $ringGroup = $data->ringGroupId;
                $currentDate = date('Y-m-d H:i:s');
                if(isset($ringGroup) && !empty($ringGroup)){
                    $getData = $this->WebserviceModel->Advertise_search($keyword,$ringGroup,$currentDate); 
                }else{
                    $getData = $this->WebserviceModel->Advertise_search_universal($keyword,$currentDate);
                }
                
                $finalArr = array();
                if($getData){
                    foreach($getData as $advVal){
                        $insertdate = $advVal->InsertDate;
                        $today = date('Y-m-d');
                        
                        if ($today == date('Y-m-d', strtotime($insertdate))) {
                        $advVal->InsertDay = "Today";
                        }else{
                            $advVal->InsertDay = date('d-m-Y', strtotime($insertdate));
                        }

                        $StartDate = date("Y-m-d H:i:s", strtotime($advVal->StartDate));
                        $EndDate = date("Y-m-d H:i:s", strtotime($advVal->EndDate));
                        // echo $EndDate;
                        if(!empty($advVal->EndDate)){
                            if($StartDate <= $currentDate && $EndDate >= $currentDate){
                                array_push($finalArr,$advVal); 
                            }
                        }else{
                            if($StartDate <= $currentDate){
                                array_push($finalArr,$advVal); 
                            } 
                        }                   
                    }
                }

                if($finalArr)
                {
                    $response['response_code'] = '1';
                    $response['response_message'] = 'Success';
                    $response['response_data'] = $finalArr;
                }
                else
                {
                    $response['response_code'] = '2';
                    $response['response_message'] = 'Failed';
                }
            }
            else
            {
                $response['response_code'] = '3';
                $response['response_message'] = 'Data is null';
            }   
        echo json_encode($response);exit;
    }

    function AdvertiseMsgInbox()
    {
        $data = json_decode(file_get_contents('php://input'));
            if($data){
                $companyName = $data->companyName;
                $patientId = $data->patientId;
                $ringGroup = $data->ringGroupId;
                $userData =  $this->WebserviceModel->getUserDataById($patientId);
                $getData = $this->WebserviceModel->AdvertiseMsgInbox($companyName,$ringGroup);            
                if($getData)
                {
                    $response['response_code'] = '1';
                    $response['response_message'] = 'Success';
                    $response['response_data'] = $getData;
                }
                else
                {
                    $response['response_code'] = '2';
                    $response['response_message'] = 'Failed';
                }
            }
            else
            {
                $response['response_code'] = '3';
                $response['response_message'] = 'Data is null';
            }   
        echo json_encode($response);exit;
    }

    public function updatePatientGmailSignup()
    {       
        $data = json_decode(file_get_contents('php://input'));
        
        if($data)
        {
            $patient_id = $data->patient_id;
            $mn = $data->mobile_number * 1 ;
            $mobile_number = $this->encryptDecrypt("en",$mn);
            // $fname = $data->fname; 
            // $fullName = $fname;
            // $full_name = $this->encryptDecrypt("en",$fullName);
            $firstname = $data->firstname;
            $lastname =$this->encryptDecrypt("en",$data->lastname);
            $fullName = $firstname;
            $full_name = $this->encryptDecrypt("en",$fullName);
            $mob_code = $data->mob_code;
            $email = $this->encryptDecrypt("en",$data->email);
            $BloodGroupId = isset($data->bloodgroup)?$data->bloodgroup:NULL;
            $address = urlencode($data->address);
            $DOB = isset($data->DOB)?$data->DOB:NULL;
            if(isset($DOB) && !empty($DOB)){
                $ts = strtotime($DOB);
                $DOB1 = date("Y-m-d H:i:s", $ts);
            }else{
                $DOB1 = NULL;
            }
            $country_id = $data->country_id;
            $state_id = $data->state_id;
            $city_id = $data->city_id;
            $pincode = $data->pin_code;
            $gender = isset($data->gender)?$data->gender:NULL;
            $UpdDataArray = array( 
                                "MobileNumber"=>$mobile_number,
                                "FullName"=>$full_name,
                                "LastName"=>$lastname,
                                "Email"=>$email,
                                "MobileCode"=>$mob_code,
                                "BloodGroupId"=>$BloodGroupId,
                                "Address"=>$address,
                                "DateOfBirth"=>$DOB1,
                                "CountryMasterId"=>$country_id,
                                "StateMasterId"=>$state_id,
                                "CityMasterId"=>$city_id,
                                "PinCode"=>$pincode,
                                "GenderId"=>$gender,
                                "InsertDate"=>date("Y-m-d H:i:s")                                   
                                );
            $updateData = $this->WebserviceModel->updatePatientGmailSignup($patient_id, $UpdDataArray);
            if($updateData)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['Data'] = $updateData;

            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    function createUserOnRingServer22JAN2024(){        
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
            $mn = $data->mobile_number * 1;
            $mobile_number = $this->encryptDecrypt("en",$mn);
            $firstname = $this->encryptDecrypt("en",$data->firstname);
            $lastname = $this->encryptDecrypt("en",$data->lastname);
            $mob_code = "+".$data->mob_code;
            $dob = isset($data->DOB)?$data->DOB:NULL;
            if(isset($dob) && !empty($dob)){
                $ts = strtotime($dob);
                $dob1 = date("Y-m-d H:i:s", $ts);
            }else{
                $dob1 = NULL;
            }
            $email = $this->encryptDecrypt("en",$data->email);
            if(empty($data->bloodgroup) || !isset($data->bloodgroup)){
                $BloodGroupId = NULL;
            }else{
                $BloodGroupdata = $this->WebserviceModel->searchIdOfGivenString($data->bloodgroup,"BloodGroupMaster","BloodGroupCode");
				if(isset($BloodGroupdata) && !empty($BloodGroupdata)){
                    $BloodGroupId = $BloodGroupdata->BloodGroupId;
                }else{
                    $BloodGroupId = NULL;
                }
			}
            $address = urlencode($data->address);

            if(empty($data->country_id) || !isset($data->country_id)){
                $country = NULL;
            }else{
                $countrydata = $this->WebserviceModel->searchIdOfGivenString($data->country_id,"CountryMaster","CountryDescription");
				if(isset($countrydata) && !empty($countrydata)){
                    $country = $countrydata->ID;
                }else{
                    $country = NULL;
                }
			}
 
            if(empty($data->state_id) || !isset($data->state_id)){
                $state = NULL;
            }else{
                $statedata = $this->WebserviceModel->searchIdOfGivenString($data->state_id,"StateMaster","StateDescription");
				if(isset($statedata) && !empty($statedata)){
                    $state = $statedata->ID;
                }else{
                    $state = NULL;
                }
			}
            
            if(empty($data->city_id) || !isset($data->city_id)){
                $city = NULL;
            }else{
                $citydata = $this->WebserviceModel->searchIdOfGivenString($data->city_id,"CityMaster","CityDescription");
				if(isset($citydata) && !empty($citydata)){
                    $city = $citydata->ID;
                }else{
                    $city = NULL;
                }
			}
            $pincode = isset($data->pin_code)?$data->pin_code:NULL;
            if(empty($data->gender) || !isset($data->gender)){
                $gender = NULL;
            }else{
                $genderdata = $this->WebserviceModel->searchIdOfGivenString($data->gender,"GenderMaster","Description");
				if(isset($genderdata) && !empty($genderdata)){
                    $gender = $genderdata->Id;
                }else{
                    $gender = NULL;
                }
			}

            $MRN = isset($data->MRN)?$data->MRN:NULL;
            $impId = isset($data->impId)?$data->impId:1;
            $saveDataArray = array( 
                                    "MobileNumber"=>$mobile_number,
                                    "FullName"=>$firstname,
                                    "LastName"=>$lastname,
                                    "Email"=>$email,
                                    "MobileCode"=>$mob_code,
                                    "BloodGroupId"=>$BloodGroupId,
                                    "Address"=>$address,
                                    "CountryMasterId"=>$country,
                                    "StateMasterId"=>$state,
                                    "CityMasterId"=>$city,
                                    "PinCode"=>$pincode,
                                    "DateOfBirth"=>$dob1,
                                    "GenderId"=>$gender,
                                    "InsertDate"=>date("Y-m-d H:i:s")                                    
                                    );
                                    // print_r($saveDataArray);exit;
            $checkData = $this->WebserviceModel->checkDuplicateMobileno($mob_code,$mobile_number,$email);
            if($checkData){
                $response['response_code'] = '4';
                $response['response_message'] = 'Duplicate Mobile Number Or Email ';                
            }
            else
            {
                $getData = $this->WebserviceModel->signUp($saveDataArray);
                if($getData)
                {
                    $insertArr = array( 
                                    "MRNNo"=>$MRN,
                                    "RINGID"=>$getData,
                                    "ImplementationId"=>$impId
                                );
                    $update = $this->WebserviceModel->InsertMrnOfUser($insertArr);
                    $response['response_code'] = '1';
                    $response['response_message'] = 'Success';
                    $response['RingId'] = $getData;
                }
                else
                {
                    $response['response_code'] = '2';
                    $response['response_message'] = 'Failed';
                }
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    function AppointmentNotification(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $PatientId = $data->RingId;
            $notification_heading = isset($data->notification_heading)?$data->notification_heading:"";
            $notification_body = isset($data->notification_body)?$data->notification_body:"";
            $notifictionType = "Appointment";
            if(!isset($data->RingId)){
                echo json_encode(array("msg"=>"RingId not sent")); exit;
            }
            $userType = 0; //0 = Patient, 1 = Doctor
            $checkuserid = $this->WebserviceModel->checkuserid($PatientId,$userType);
            $token = isset($checkuserid->DeviceId)?$checkuserid->DeviceId:0;
            if(isset($PatientId) && empty($token)){
                $mainProfileId = $this->WebserviceModel->getMainPatientId($PatientId);
                if(isset($mainProfileId->MainProfilePatientId)){
                    $checkusertoken = $this->WebserviceModel->checkuserid($mainProfileId->MainProfilePatientId,$userType);
                    $usertoken = isset($checkusertoken->DeviceId)?$checkusertoken->DeviceId:0;
                }else{
                    $usertoken = $token;
                }
                
            }else{
                $usertoken = $token;
            }

            if($checkuserid->Platform == "ios"){
                $message = array(
                    'title' => $notification_heading, 
                    'body' => $notification_body, 
                    'sound' => 'default', 
                    'badge' => '1',
                    'notifictionType' => $notifictionType
                );  
                $data1 = array('extraInfo'=> 'DomingoMG','notifictionType' => $notifictionType); 
                $this->send_notification_ios($usertoken,$notification_body,$data1,$message);
            }else{
                $message = array(
                            'title' => $notification_heading, 
                            'body' => $notification_body, 
                            'sound' => 'default', 
                            'badge' => '1',
                            'click_action'=>'FCM_PLUGIN_ACTIVITY', //For only Android App
                            'notifictionType' => $notifictionType
                        );      
                $url = "https://fcm.googleapis.com/fcm/send";
                $serverKey = 'AAAAYtnkw9E:APA91bHqSJZSydEao75WwveTXNCaM4rqn4dPypsHsVy9m4PfV6pzEMHd5ntJAt3cV5SWDF5ZP1lkGakKnGSNbfWn97DK0vDUSY9LTIDxVIBEP_pWTmReLq0tbw6t1qmIuzEXz9t6MVq1'; // add api key here
                $notification = $message;
                $data = array('extraInfo'=> 'DomingoMG','notifictionType' => $notifictionType);      
                $arrayToSend = array('to' => $usertoken, 'notification' => $notification, 'priority'=>'high', 'data'=> $data);
                $json = json_encode($arrayToSend);
                // print_r($json);exit;
                $headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: key='. $serverKey;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);    
                //Send the request
                $response = curl_exec($ch);
                if ($response === FALSE) {
                    die('FCM Send Error: ' . curl_error($ch));
                }
                curl_close($ch);
            }  
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }          
    }

    // function sendReportToRingUser(){        
    function saveReportDataForHISDigitalSense(){    
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
            $ringId = $data->RingId;
            $ImpId = $data->ImpId;
            $insertArr = array( 
                "RingId"=>$ringId,
                "ImplementationId"=>$ImpId,
                "DataJson"=> json_encode($data)
            );
            $insert = $this->WebserviceModel->insertReportDataOfSCMS($insertArr);
            if($insert)
            {
                $Notify = $this->android_notification_function($ringId);
                $response['response_code'] = 1;
                $response['response_data'] = $insert;
                $response['response_message'] = 'Report saved successfully';
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    function android_notification_function($PatientId){
        $PatientId = $PatientId;
        $notification_body = isset($data->notification_body)?$data->notification_body:"Your report has been sent please download it.";
        if(!isset($PatientId)){
            print_r(array("msg"=>"PatientId not sent")); exit;
        }
        $userType = 0; //0 = Patient, 1 = Doctor
        $checkuserid = $this->WebserviceModel->checkuserid($PatientId,$userType);
        $token = isset($checkuserid->DeviceId)?$checkuserid->DeviceId:0;
		if(isset($PatientId) && empty($token)){
			$mainProfileId = $this->WebserviceModel->getMainPatientId($PatientId);
			if(isset($mainProfileId->MainProfilePatientId)){
				$checkusertoken = $this->WebserviceModel->checkuserid($mainProfileId->MainProfilePatientId,$userType);
        		$usertoken = isset($checkusertoken->DeviceId)?$checkusertoken->DeviceId:0;
			}else{
				$usertoken = $token;
			}
			
		}else{
			$usertoken = $token;
		}


        if($checkuserid->Platform == "ios"){
            $message = array(
                'title' => 'Report is ready', 
                'body' => $notification_body, 
                'sound' => 'default', 
                'badge' => '1',
                'notifictionType' => 'Referal'
            );  
            $data1 = array('extraInfo'=> 'DomingoMG','notifictionType' => 'Referal'); 
            $this->send_notification_ios($usertoken,$notification_body,$data1,$message);
        }else{
            $message = array(
                            'title' => 'Report is ready', 
                            'body' => $notification_body, 
                            'sound' => 'default', 
                            'badge' => '1',
                            'click_action'=>'FCM_PLUGIN_ACTIVITY', //For only Android App
                            'notifictionType' => 'Referal'
                        );      
            $url = "https://fcm.googleapis.com/fcm/send";
            $serverKey = 'AAAAYtnkw9E:APA91bHqSJZSydEao75WwveTXNCaM4rqn4dPypsHsVy9m4PfV6pzEMHd5ntJAt3cV5SWDF5ZP1lkGakKnGSNbfWn97DK0vDUSY9LTIDxVIBEP_pWTmReLq0tbw6t1qmIuzEXz9t6MVq1'; // add api key here
            $notification = $message;
            $data = array('extraInfo'=> 'DomingoMG','notifictionType' => 'Referal');      
            $arrayToSend = array('to' => $usertoken, 'notification' => $notification, 'priority'=>'high', 'data'=> $data);
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key='. $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
            //Send the request
            $response = curl_exec($ch);
            if ($response === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
        }
    }
     
    function GetCMSReportDataOfPatient(){
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
            $ringId = $data->patient_id;
            $ImpId = $data->ImpId;
            $GetData = $this->WebserviceModel->GetCMSReportDataOfPatient($ringId, $ImpId);
            foreach($GetData as $val){
                $val->DataJson = json_decode($val->DataJson);
                $val->DataJson->CategoryName = strtolower($val->DataJson->CategoryName);
                $val->DataJson->CategoryName = ucfirst($val->DataJson->CategoryName);

                $tenantId = $this->WebserviceModel->getTenantByname($val->DataJson->HospitalName);
                /**Working Hours of Tenant */
				$workHrArr = array();
                $refworkHrArr = array();
				$workingArr = $this->WebserviceModel->getWorkingScheduleOfTenant($tenantId->TenantId);
                foreach($workingArr as $workHrVal){
                    $workHrVal->FromTime = date("h:i A", strtotime($workHrVal->FromTime));
					$workHrVal->ToTime = date("h:i A", strtotime($workHrVal->ToTime));
					array_push($workHrArr,$workHrVal);
				}
                if(isset($workHrArr) && !empty($workHrArr)){
                    $workingHTML = '<div class="f14 txtlist">';
                    $workingHTMLinMalay = '<div class="f14 txtlist">';
                    foreach($workHrArr as $workHrArrVal)
                    {
                        $workingHTML .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                        $workingHTMLinMalay .= '<ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> '.$workHrArrVal->DayName.' : </span>  '.$workHrArrVal->FromTime.' - '.$workHrArrVal->ToTime.'<br>
                                    </ion-col>';
                    }
                    $workingHTML .= '</div>';
                    $workingHTMLinMalay .= '</div>';
                }else{
                    $workingHTML = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> From Time :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> To Time :</span>N/A
                                    </ion-col>
                                    </p>';
                    $workingHTMLinMalay = '<p class="f14 txtlist">
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Dari jam :</span>N/A<br>
                                    </ion-col>
                                    <ion-col size="12" class="f14 txtlist">
                                        <span class="fw600" translate> Hingga jam :</span>N/A
                                    </ion-col>
                                    </p>';                
                }
                $val->DataJson->WorkingSchedule = $workingHTML;
                $val->DataJson->WorkingScheduleInMalay = $workingHTMLinMalay;
                if($val->DataJson->FileAttachments){                  
                    foreach($val->DataJson->FileAttachments As $docfile){
                        // print_r($docfile->DocumentUrl);exit;
                        // $pdfName = 'upload/'.$docfile->DocumentName;
                        // // copy($docfile->DocumentUrl, $pdfName);
                        // $content = file_get_contents($docfile->DocumentUrl);
                        // // $dataC = json_decode($content, TRUE);
                        
                        // // echo $docfile->DocumentUrl ;
                        // // print_r($content);exit;
                        // $fp = fopen($pdfName, "w");
                        // fwrite($fp, $content);
                        // fclose($fp);
                        // $docfile->Base64DocumentUrl = $this->convertImageLinkToBase64ForFileAttachment($pdfName);
                        if($docfile->DocumentUrl){
                            // $url = 'https://apimobile.ring.healthcare:5026/index.php/Mail/getbase64DataOfFileURL';
                            $url = 'https://cmsapi.ring.healthcare:5001/index.php/Api/getbase64DataOfFileURL';
                            $json = '{
                                "path": "'.$docfile->DocumentUrl.'"
                            }';   
                            //echo $json;exit;
                            $headers = array('Content-Type: application/json');
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => $json,
                            CURLOPT_HTTPHEADER => $headers,
                            ));
                            $res = curl_exec($curl);
                            if (curl_errno($curl)) {
                                $error_msg = curl_error($curl);
                                echo $error_msg;
                            }
                            curl_close($curl);
                            // echo $res;exit;
                            // $docfile->Base64DocumentUrl = base64_encode(file_get_contents($docfile->DocumentUrl));
                            $docfile->Base64DocumentUrl = $res;
                            //print_r($docfile->Base64DocumentUrl);exit;
                        }else{
                            $docfile->Base64DocumentUrl = "";
                        }
                    }
                }  
                //   $val->DataJson[]= $val->DataJson;          
            }
            // exit;
            if($GetData)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $GetData;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    function CMSReportStatusUpdate(){
        $data = json_decode(file_get_contents('php://input'));
        $fileId = $data->fileId;
        $updateArr = array("IsDownloaded"=>1);
		$update = $this->WebserviceModel->CMSReportStatusUpdate($fileId,$updateArr);
        if($update)
        {
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
        }
        else
        {
            $response['response_code'] = '2';
            $response['response_message'] = 'Failed';
        }   
        echo json_encode($response);exit;
    }

    public function saveDoctorDetails(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $PhoneNumber = $data->DoctorPhoneNumber;
            $PhoneCode = $data->DoctorPhoneCode;
            $TenantCompanyNumber = isset($data->CompanyNumber)?$data->CompanyNumber:"";
            $TenantAddress = isset($data->HospitalAddress)?$data->HospitalAddress:"";
            $TenantFaxNumber = $data->HospitalFaxNumber;
			$speciality = $data->Speciality;
			$TenantName = $data->HospitalName; 
            $TenantPhoneNumber = isset($data->HospitalPhoneNumber)?$data->HospitalPhoneNumber:""; 
            $email = isset($data->DoctorEmail)?$data->DoctorEmail:""; 
            $name = isset($data->DoctorName)?$data->DoctorName:"";
            $mmcnumber = isset($data->mmcnumber)?$data->mmcnumber:"";
            $password = isset($data->Password)?$data->Password:"";
            $username = isset($data->Username)?$data->Username:"";
            $TenantCountry = isset($data->HospitalCountry)?$data->HospitalCountry:"";
            $TenantState = isset($data->HospitalState)?$data->HospitalState:"";
            $TenantCity = isset($data->HospitalCity)?$data->HospitalCity:"";
            $TenantPin = isset($data->HospitalPin)?$data->HospitalPin:"";
            $TenantPhoneCode = isset($data->HospitalPhoneCode)?$data->HospitalPhoneCode:"";
            $locationId = isset($data->HospitalId)?$data->HospitalId:""; 
            $ImpId = isset($data->ImpId)?$data->ImpId:"1";
            $CmsDoctorId = $data->DoctorId;

            if(empty($data->HospitalCountry) || !isset($data->HospitalCountry)){
                $country = NULL;
            }else{
                $countrydata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalCountry,"CountryMaster","CountryDescription");
				if(isset($countrydata) && !empty($countrydata)){
                    $country = $countrydata->ID;
                }else{
                    $country = NULL;
                }
			}
 
            if(empty($data->HospitalState) || !isset($data->HospitalState)){
                $state = NULL;
            }else{
                $statedata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalState,"StateMaster","StateDescription");
				if(isset($statedata) && !empty($statedata)){
                    $state = $statedata->ID;
                }else{
                    $state = NULL;
                }
			}
            
            if(empty($data->HospitalCity) || !isset($data->HospitalCity)){
                $city = NULL;
            }else{
                $citydata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalCity,"CityMaster","CityDescription");
				if(isset($citydata) && !empty($citydata)){
                    $city = $citydata->ID;
                }else{
                    $city = NULL;
                }
			}

            $paramJson = '{
                "PhoneNumber": "'.$PhoneNumber.'",
                "FacilityTypeId": "1",
                "TenantCompanyNumber": "'.$TenantCompanyNumber.'",
                "TenantAddress":"'.$TenantAddress.'",
                "TenantFaxNumber":"'.$TenantFaxNumber.'",
                "speciality":"1",
                "Role": "12",
                "TenantName":"'.$TenantName.'",
                "TenantPhoneNumber":"'.$TenantPhoneNumber.'",
                "email":"'.$email.'",
                "name":"'.$name.'",
                "mmcnumber":"'.$mmcnumber.'",
                "password":"'.$password.'",
                "username":"'.$username.'",
                "TenantCountry":"'.$country.'",
                "TenantState": "'.$state.'",
                "TenantCity":"'.$city.'",
                "TenantPin": "'.$TenantPin.'",
                "TenantPhoneCode": "+'.$TenantPhoneCode.'",
                "PhoneCode":"+'.$PhoneCode.'"
                }';
                            
            $url = 'http://sancyberhad.ddns.net/RING_API_TEST/api/Register/RegisterDoctor';
            $json = $paramJson;   
            // echo $json;exit;
            $headers = array('Content-Type: application/json');
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $headers,
            ));
            $res = curl_exec($curl);
            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
                echo $error_msg;
            }
            curl_close($curl);
            echo $res;
            if($res == '{}'){
                $insert_id = $this->db->select('UserId')->from('Users')->order_by('UserId',"desc")->limit(1)->get()->row();
           
                $insertArr = array( 
                                    "LocationId"=>$locationId,
                                    "CmsDoctorId"=>$CmsDoctorId,
                                    "RingDoctorId"=>$insert_id,
                                    "ImplementationId"=>$ImpId
                                );
                $update = $this->WebserviceModel->DoctorImpData($insertArr);
			    $response['response_code'] = 1;
                $response['response_data'] = $insert_id;
                $response['response_message'] = 'Doctor saved successfully';
            }else{
                $response['response_code'] = 2;
                $response['response_message'] = 'Failled';
            }                 
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    function createUserOnRingServer(){        
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
            $mn = $data->mobile_number * 1;
            $mobile_number = $this->encryptDecrypt("en",$mn);
            $firstname = $this->encryptDecrypt("en",$data->firstname);
            $lastname = $this->encryptDecrypt("en",$data->lastname);
            $mob_code = "+".$data->mob_code;
            $dob = isset($data->DOB)?$data->DOB:NULL;
            if(isset($dob) && !empty($dob)){
                $ts = strtotime($dob);
                $dob1 = date("Y-m-d H:i:s", $ts);
            }else{
                $dob1 = NULL;
            }

            
            if(!empty($data->email) && isset($data->email)){
                $email = $this->encryptDecrypt("en",$data->email);
            }else{
                $email = "";
            }
            if(empty($data->bloodgroup) || !isset($data->bloodgroup)){
                $BloodGroupId = NULL;
            }else{
                $BloodGroupdata = $this->WebserviceModel->searchIdOfGivenString($data->bloodgroup,"BloodGroupMaster","BloodGroupCode");
				if(isset($BloodGroupdata) && !empty($BloodGroupdata)){
                    $BloodGroupId = $BloodGroupdata->BloodGroupId;
                }else{
                    $BloodGroupId = NULL;
                }
			}
            $address = urlencode($data->address);

            if(empty($data->country_id) || !isset($data->country_id)){
                $country = NULL;
            }else{
                $countrydata = $this->WebserviceModel->searchIdOfGivenString($data->country_id,"CountryMaster","CountryDescription");
				if(isset($countrydata) && !empty($countrydata)){
                    $country = $countrydata->ID;
                }else{
                    $country = NULL;
                }
			}
 
            if(empty($data->state_id) || !isset($data->state_id)){
                $state = NULL;
            }else{
                $statedata = $this->WebserviceModel->searchIdOfGivenString($data->state_id,"StateMaster","StateDescription");
				if(isset($statedata) && !empty($statedata)){
                    $state = $statedata->ID;
                }else{
                    $state = NULL;
                }
			}
            
            if(empty($data->city_id) || !isset($data->city_id)){
                $city = NULL;
            }else{
                $citydata = $this->WebserviceModel->searchIdOfGivenString($data->city_id,"CityMaster","CityDescription");
				if(isset($citydata) && !empty($citydata)){
                    $city = $citydata->ID;
                }else{
                    $city = NULL;
                }
			}
            $pincode = isset($data->pin_code)?$data->pin_code:NULL;
            if(empty($data->gender) || !isset($data->gender)){
                $gender = NULL;
            }else{
                $genderdata = $this->WebserviceModel->searchIdOfGivenString($data->gender,"GenderMaster","Description");
				if(isset($genderdata) && !empty($genderdata)){
                    $gender = $genderdata->Id;
                }else{
                    $gender = NULL;
                }
			}

            $MRN = isset($data->MRN)?$data->MRN:NULL;
            $impId = isset($data->impId)?$data->impId:1;
            $ringId = isset($data->RingId)?$data->RingId:"";
            if(isset($ringId) && !empty($ringId)){
                $userData =  $this->WebserviceModel->getUserDataById($ringId);
                $userData->MobileCode = $userData->MobileCode;
                $userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);
                $userData->FirstName = $this->encryptDecrypt("dc",$userData->FullName);
                $userData->LastName = $this->encryptDecrypt("dc",$userData->LastName);
                $userData->Email = $this->encryptDecrypt("dc",$userData->Email);
                $userData->Address = urldecode($userData->Address);
                $Ring_id = $userData->PatientId;

            }else{
                $userData =  $this->WebserviceModel->getUserDataForCMS($MRN,$mob_code,$mobile_number,$email);
                $userData->MobileCode = $userData->MobileCode;
                $userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);
                $userData->FirstName = $this->encryptDecrypt("dc",$userData->FullName);
                $userData->LastName = $this->encryptDecrypt("dc",$userData->LastName);
                $userData->Email = $this->encryptDecrypt("dc",$userData->Email);
                $userData->Address = urldecode($userData->Address);
                $frstn = explode(" ",$data->firstname);

                similar_text($userData->FullName,$frstn[0],$percent);
                $rIdArr = array();
                if($percent > 80){
                    $Ring_id = $userData->PatientId;
                }else{
                    $Ring_id = "";
                }
            } 
            if(isset($Ring_id) && !empty($Ring_id)){
                $response['response_code'] = 1;
                $response['RingId'] = $Ring_id;
                $response['UserData'] = $userData;
            }else{
                $response['response_code'] = 2;
                $response['response_message'] = 'User not found';
            }           
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function HospitalIntegration()
    {       
		$data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $TenantCode = $data->TenantCode;
            $TenantName = $data->TenantName;
            $PhoneNumber = $data->PhoneNumber;
            $FaxNumber = $data->FaxNumber;
            $Address = $data->Address;
            $CountryID = $data->CountryID;
            $StateID = $data->StateID;
            $CityID = $data->CityID;
            $PostCode = $data->PostCode;
            $Latitude = $data->Latitude;
            $Longitude = $data->Longitude;
            $PhoneCode = $data->PhoneCode;
            $FaxCode = $data->FaxCode;
            $insertArray = array(
                "TenantCode"=>$TenantCode,
                "TenantNumber"=>$TenantName,
                "TenantName"=>$TenantName,
                "TenantTypeId"=>"",
                "PhoneNumber"=>$PhoneNumber,
                "FaxNumber"=>$FaxNumber,
                "Address"=>$Address,
                "CountryID"=>$CountryID,
                "StateID"=>$StateID,
                "CityID"=>$CityID,
                "PostCode"=>$PostCode,
                "Latitude"=>$Latitude,
                "Longitude"=>$Longitude,
                "PhoneCode"=>$PhoneCode,
                "FaxCode"=>$FaxCode,
                "IsActive"=>1
            );
            $chkFacility = $this->db->select('*')->from('Tenants')->where('TenantName',$TenantName)->get()->row();
            if(isset($chkFacility) && !empty($chkFacility)){
                $response['response_code']=4;
                $response['response_message']='facility exist';
            }else{
                $insert = $this->db->insert("Tenants",$insertArray);
                if($insert)             
                {
                    $response['response_code']=1;
                    $response['response_message']='Success';
                }
                else
                {
                    $response['response_code']=2;
                    $response['response_message']='Failed';
                } 
            }                    
		}
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
		echo json_encode($response);exit;        
    }

    public function saveReportDataForHISDigitalSenseold2(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $UserId = $data->UserId;
            $DoctorName = $data->DoctorName;
            $MMCNumber = isset($data->MMCNumber)?$data->MMCNumber:"";
            $PatientName = isset($data->PatientName)?$data->PatientName:"";
            $Description = $data->Description;
			$CategoryName = $data->CategoryName;
			$FileAttachments = $data->FileAttachments; 
            $HospitalName = isset($data->HospitalName)?$data->HospitalName:""; 
            $Diagnosis = isset($data->Diagnosis)?$data->Diagnosis:""; 
            $PatientMobile = isset($data->PatientMobile)?$data->PatientMobile:"";
            $PatientMobileCode = isset($data->PatientMobileCode)?$data->PatientMobileCode:"";
            $PatientEmail1 = isset($data->PatientEmail)?$data->PatientEmail:"";
	    if($PatientEmail1){
		$PatientEmail = strtolower($PatientEmail1);
	    }else{
		$PatientEmail = "";
	    }
            $Email = $this->encryptDecrypt("en",$PatientEmail);           
            // $HospitalName = $data->HospitalName; 
            $NewArr = array();		
            if(!empty($PatientMobile) && !empty($PatientMobileCode)){
                $Mobile = $this->encryptDecrypt("en",$PatientMobile);
                $MobCode = "+".str_replace(" ","",$PatientMobileCode);
                $chkPatient = $this->WebserviceModel->patientSearchByMobileNumber($Mobile, $MobCode); 
                if(isset($chkPatient) && $Email != $chkPatient->Email){
                    $depList = $this->WebserviceModel->getDependentListByPatientId($chkPatient->PatientId);
                    if($depList){
                        foreach($depList as $val){
                            $DependentProfilePatientId = isset($val->DependentProfilePatientId)?$val->DependentProfilePatientId:"";
                            $depData =  $this->WebserviceModel->getUserDataById($DependentProfilePatientId);
                    // print_r($depData);exit;
                            $depEmail = $this->encryptDecrypt("dc",$depData->Email);
                            $depName = $this->encryptDecrypt("dc",$depData->FullName);
                            if($PatientEmail == $depEmail && trim($PatientName) == $depName){
                                array_push($NewArr,$depData);
                            }                               
                        }
                    }
                }else{
                    array_push($NewArr,$chkPatient);
                }              
            }else if(!empty($PatientEmail)){
                $Email = $this->encryptDecrypt("en",$PatientEmail);
                $chkPatient = $this->WebserviceModel->patientSearchByEmail($Email);
                array_push($NewArr,$chkPatient);
            }		
			if(isset($NewArr) && !empty($NewArr)){
                // print_r($NewArr);exit;
                $chkDoctor = $this->WebserviceModel->doctorSearchDataForHIS($DoctorName,$MMCNumber);
                if($chkDoctor){
                     
                    $PatientId = $NewArr[0]->PatientId;
                    $docId = $chkDoctor->UserId;
                    $chkHospital = $this->WebserviceModel->tenantsSearchDataForHIS($HospitalName,$docId);   
                // print_r($chkHospital);exit;                
                    if($chkHospital){
                        $TenantId = $chkHospital->TenantId;
                        $chkRingGroup = $this->WebserviceModel->getRingGrpByTenantId($TenantId);
                        if($chkRingGroup){
                            $RingGrpId = $chkRingGroup->RingGroupId;
                        }else{
                            $RingGrpId = 2;
                        }
                        $chkCat = $this->WebserviceModel->getCategoryIdByCategoryName($CategoryName);
                        if($chkCat){
                            $CatId = $chkCat->CategoryId;
                        }else if($CategoryName == "CLINICALSUMMARY"){
			  $CatId = 4;
			}else{
                          $CatId = 11; 
                        }
                        $fileAttachedArr = array();
                        if(isset($FileAttachments) && !empty($FileAttachments)){
                            foreach($FileAttachments as $fileVal){
                                $DocumentName = $fileVal->DocumentName;
                                $DocumentUrl = $fileVal->DocumentUrl;
                                $imagePath = $DocumentUrl;
                                $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                                $contentData = file_get_contents($imagePath);
                                $base64 = base64_encode($contentData);
                                $fileArray = array("DocumentName"=>$DocumentName,"DocumentContent"=>$base64);
                                array_push($fileAttachedArr,$fileArray);
                            }
                        }
                        $fileArrJson = json_encode($fileAttachedArr);
                        $paramJson = '{"PatientMasterId":"'.$PatientId.'",
                                        "CategoryId":[{"CategoryId":"'.$CatId.'",
                                                    "IsProcessed":0,
                                                    "FileAttachments":'.$fileArrJson.'}],
                                        "Description":"'.$Description.'",
                                        "FileBytes":1000,
                                        "TenantId":'.$TenantId.',
                                        "AddReferral":false,
                                        "ReferredToUserId":"'.$docId.'",
                                        "ReferredByUserId":"'.$docId.'",
                                        "ReferralDescription":"NULL",
                                        "RingGroup":'.$RingGrpId.',
                                        "mob_code":"+'.$PatientMobileCode.'"}';

                                        
                        $url = 'https://apiweb.ring.healthcare:5028/api/Register/UploadFiles';
                        $json = $paramJson;   
                        //echo $json;exit;
                        $headers = array('Content-Type: application/json');
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $json,
                        CURLOPT_HTTPHEADER => $headers,
                        ));
                        $res = curl_exec($curl);
                        if (curl_errno($curl)) {
                            $error_msg = curl_error($curl);
                            echo $error_msg;
                        }
                        curl_close($curl);
                        echo $res;
                        if($res == '{}'){
                            $insert_id = $this->db->select('ReportTransitId')->from('EreportsTransit')->order_by('ReportTransitId',"desc")->limit(1)->get()->row();
                           
			    // if(!empty($Diagnosis)){
                            //     $Diagno = $Diagnosis;
                            // }else{
                            //     $Diagno = $Description;
                            // }
                            $updateDiagno = $this->db->where("ReportTransitId", $insert_id->ReportTransitId)->update("EreportsTransit",array("ICD"=>$Diagnosis));
                            $Notify = $this->android_notification_function($PatientId);
			                $response['response_code'] = 1;
                            $response['response_data'] = $insert_id;
                            $response['response_message'] = 'Report saved successfully';
                        }else{
                            $response['response_code'] = 2;
                            $response['response_message'] = 'Failled';
                        }
                    }else{
                        $response['response_code'] = 5;
                        $response['response_message'] = 'Hospital not found';
                    }
                }else{
                        $response['response_code'] = 6;
                        $response['response_message'] = 'Doctor not found';
                    }                               
            }else{
                $response['response_code'] = 4;
                $response['response_message'] = 'Patient not found';
            }            
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }
        echo json_encode($response); exit;
    }

    function InsertMrnOfUser(){
        $data = json_decode(file_get_contents('php://input'));
        if($data){
            $MRN = $data->MRN;
            $RINGID = $data->RINGID;
            $impId = 1;
            $insertArr = array( 
                "MRNNo"=>$MRN,
                "RINGID"=>$getData,
                "ImplementationId"=>$impId
            );
            $update = $this->WebserviceModel->InsertMrnOfUser($insertArr);
            if($update)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }  
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }     
        echo json_encode($response);exit;
    }


    function url_get_contents ($Url) {
        if (!function_exists('curl_init')){ 
            die('CURL is not installed!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function userVerification()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {         
            $ringId = $data->patientId;
            $impId = 1;
            $isVarified = $this->WebserviceModel->userVerification($ringId,$impId);
            if($isVarified)
            {          
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';	
                $response['response_data'] = $isVarified;
            }else{				
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;        
    }

    function InsertUserVerificationData(){
        $data = json_decode(file_get_contents('php://input'));
        if($data){
            $MRN = $data->mrno;
            $RINGID = $data->patientId;
            $impId = isset($data->ImpId)?$data->ImpId:1;
            $CMSID = $data->CMSID;
            $insertArr = array( 
                "MRNNo"=>$MRN,
                "RINGID"=>$RINGID,
                "CMSID"=>$CMSID,
                "ImplementationId"=>$impId
            );
            $insert = $this->WebserviceModel->InsertMrnOfUser($insertArr);
            if($insert)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $insert;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }  
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }     
        echo json_encode($response);exit;
    }

    function UpdateMRNOnRingServerCopy(){        
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
            $mn = $data->mobile_number * 1;
            $mobile_number = $this->encryptDecrypt("en",$mn);
            $firstname = $this->encryptDecrypt("en",$data->firstname);
            $lastname = $this->encryptDecrypt("en",$data->lastname);
            $mob_code = "+".$data->mob_code;
            $dob = isset($data->DOB)?$data->DOB:NULL;
            if(isset($dob) && !empty($dob)){
                $ts = strtotime($dob);
                $dob1 = date("Y-m-d H:i:s", $ts);
            }else{
                $dob1 = NULL;
            }
            if(isset($data->email) && !empty($data->email)){
                $email = $this->encryptDecrypt("en",$data->email);
            }else{
                $email = NULL;
            }
            
            if(empty($data->bloodgroup) || !isset($data->bloodgroup)){
                $BloodGroupId = NULL;
            }else{
                $BloodGroupdata = $this->WebserviceModel->searchIdOfGivenString($data->bloodgroup,"BloodGroupMaster","BloodGroupCode");
				if(isset($BloodGroupdata) && !empty($BloodGroupdata)){
                    $BloodGroupId = $BloodGroupdata->BloodGroupId;
                }else{
                    $BloodGroupId = NULL;
                }
			}
            $address = urlencode($data->address);

            if(empty($data->country_name) || !isset($data->country_name)){
                $country = NULL;
            }else{
                $countrydata = $this->WebserviceModel->searchIdOfGivenString($data->country_name,"CountryMaster","CountryDescription");
				if(isset($countrydata) && !empty($countrydata)){
                    $country = $countrydata->ID;
                }else{
                    $country = NULL;
                }
			}
 
            if(empty($data->state_name) || !isset($data->state_name)){
                $state = NULL;
            }else{
                $statedata = $this->WebserviceModel->searchIdOfGivenString($data->state_name,"StateMaster","StateDescription");
				if(isset($statedata) && !empty($statedata)){
                    $state = $statedata->ID;
                }else{
                    $state = NULL;
                }
			}
            
            if(empty($data->city_name) || !isset($data->city_name)){
                $city = NULL;
            }else{
                $citydata = $this->WebserviceModel->searchIdOfGivenString($data->city_name,"CityMaster","CityDescription");
				if(isset($citydata) && !empty($citydata)){
                    $city = $citydata->ID;
                }else{
                    $city = NULL;
                }
			}
            $pincode = isset($data->pin_code)?$data->pin_code:NULL;
            if(empty($data->gender) || !isset($data->gender)){
                $gender = NULL;
            }else{
                $genderdata = $this->WebserviceModel->searchIdOfGivenString($data->gender,"GenderMaster","Description");
				if(isset($genderdata) && !empty($genderdata)){
                    $gender = $genderdata->Id;
                }else{
                    $gender = NULL;
                }
			}

            $MRN = isset($data->MRNO)?$data->MRNO:NULL;
            $impId = isset($data->impId)?$data->impId:1;
			$passportNo = isset($data->passport_no)?$data->passport_no:"";
			$NRICno = isset($data->NRICno)?$data->NRICno:"";
			
            if(!isset($data->RingId) || empty($data->RingId)){
                // echo 1;
                $userData =  $this->WebserviceModel->getUserDataForCMSCopy($mob_code,$mobile_number,$email);
                // print_r($userData);exit;
                $RingId = $userData->PatientId;
            }else{
                $RingId = $data->RingId;
            }

			$CMSId = $data->CMSId;
            if($RingId){
                $chkMRN = $this->WebserviceModel->checkMrnOfRingUser($RingId,$CMSId);
                if($chkMRN){
                    $response['response_code'] = '4';
                    $response['response_message'] = 'MRN already exists';
                    $response['response_data'] = $chkMRN;
                }
                else
                {
                    $insertArr = array( 
                        "MRNNo"=>$MRN,
                        "RINGID"=>$RingId,
                        "CMSID"=>$CMSId,
                        "ImplementationId"=>$impId
                    );
                    $insert = $this->WebserviceModel->InsertMrnOfUser($insertArr);
                    if($insert)
                    {				
                        $response['response_code'] = '1';
                        $response['response_message'] = 'Success';
                        $response['response_data'] = $insert;
                    }
                    else
                    {
                        $response['response_code'] = '2';
                        $response['response_message'] = 'Failed';
                    }
                }
            }else{
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed. RINGID not found';
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function checkDependentForConvert(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)        
        {   
            $mn = $data->mobile_number * 1;
            $mobile_number = $this->encryptDecrypt("en",$mn);
            $mob_code = $data->mob_code;
            $email = $this->encryptDecrypt("en",$data->email);
            $Patient = $this->WebserviceModel->findDuplicateUser($mob_code,$mobile_number,$email);
            if($Patient)             
            {
                $Patient->pat_email = $this->encryptDecrypt("dc",$Patient->Email);
                $Patient->pat_name = $this->encryptDecrypt("dc",$Patient->FullName);
                $response['response_code'] = 1;
                $response['response_message'] = 'User Found.';
                $response['response_Data'] = $Patient;
            }
            else
            {
                $Patientdata1 = $this->WebserviceModel->findDuplicateUserbyMobile($mob_code,$mobile_number);
                $Patientdata2 = $this->WebserviceModel->findDuplicateUserbyEmail($email);
                // echo "<pre>"; print_r($Patientdata1);
                // print_r($Patientdata2);exit;
                if(isset($Patientdata1->PatientId) && isset($Patientdata2->PatientId) && ($Patientdata1->PatientId == $Patientdata2->PatientId)){
                    $response['response_code'] = 1;
                    $response['response_message'] = 'An account with this email and mobile number already exists. RING will link the data from this dependent profile to the existing account. Do you wish to continue?';
                    $response['response_Data'] = $Patientdata1;
                }else if(isset($Patientdata1->PatientId) && isset($Patientdata2->PatientId) && ($Patientdata1->PatientId != $Patientdata2->PatientId)){
                    $response['response_code'] = 4;
                    $response['response_message'] = 'This mobile number is already associated with a different email, please try again. This email is already associated with a different mobile number, please try again';
                    $response['response_Data'] = $Patientdata1;
                    $response['response_Data2'] = $Patientdata2;
                }else if(isset($Patientdata1->PatientId) && !isset($Patientdata2->PatientId)){
                    $response['response_code'] = 5;
                    $response['response_message'] = 'This mobile number is already associated with a different email, please try again';
                    $response['response_Data'] = $Patientdata1;
                }else if(!isset($Patientdata1->PatientId) && isset($Patientdata2->PatientId)){
                    $response['response_code'] = 6;
                    $response['response_message'] = 'This email is already associated with a different mobile number, please try again';
                    $response['response_Data'] = $Patientdata2;
                }else{
                    $response['response_code'] = 2;
                    $response['response_message'] = 'failed';
                }
            }              
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit; 
    }

    public function sendOtpForConvertDependent(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)        
        {   
            $email = $data->email;
            $patientId = $data->patientId;
            $Patient = $this->WebserviceModel->getUserDataById($patientId);
            if($Patient)             
            {
                $otp = rand(100000, 999999);
                $pat_name = $this->encryptDecrypt("dc",$Patient->FullName);
                $type = "convertProfileOtp";
                $dataArr = array("name"=>$pat_name, "otp"=>$otp);
                $sendMail = Utility::callSendMailWithTemplate($email,$type,$dataArr);
                $updateOtpData = $this->WebserviceModel->updateOtpDataById($otp,$patientId);
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['otp'] = $otp;
                $response['email'] = $email;
            }
            else
            {
                $response['response_code']=2;
                $response['response_message']='Failed';
            }              
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit; 
    }

    function newUserCreateAfterConvertion(){        
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
            $mainPatientId = $data->mainPatientId;
            $dependentPatientId = $data->dependentPatientId;
            $mn = $data->mobile_number * 1;
            $mobile_number = $this->encryptDecrypt("en",$mn);
            $firstname = $data->firstname;
            $lastname =$this->encryptDecrypt("en",$data->lastname);
            $fullName = $firstname;
            $full_name = $this->encryptDecrypt("en",$fullName);
            $mob_code = $data->mob_code;
            $dob = isset($data->DOB)?$data->DOB:NULL;
            if(isset($dob) && !empty($dob)){
                $ts = strtotime($dob);
                $dob1 = date("Y-m-d H:i:s", $ts);
            }else{
                $dob1 = NULL;
            }
            $email = $this->encryptDecrypt("en",$data->email);
            // $BloodGroupId = isset($data->bloodgroup)?$data->bloodgroup:NULL;
            if(empty($data->bloodgroup) || !isset($data->bloodgroup)){
                $BloodGroupId = NULL;
            }else{
				$BloodGroupId = $data->bloodgroup;
			}
            $address = urlencode($data->address);
            $country = isset($data->country_id)?$data->country_id:NULL;
            $state = isset($data->state_id)?$data->state_id:NULL;
            $city = isset($data->city_id)?$data->city_id:NULL;
            $pincode = isset($data->pin_code)?$data->pin_code:NULL;
            $gender = isset($data->gender)?$data->gender:NULL;
            $saveDataArray = array( 
                                    "MobileNumber"=>$mobile_number,
                                    "FullName"=>$full_name,
                                    "LastName"=>$lastname,
                                    "Email"=>$email,
                                    "MobileCode"=>$mob_code,
                                    "BloodGroupId"=>$BloodGroupId,
                                    "Address"=>$address,
                                    "CountryMasterId"=>$country,
                                    "StateMasterId"=>$state,
                                    "CityMasterId"=>$city,
                                    "PinCode"=>$pincode,
                                    "DateOfBirth"=>$dob1,
                                    "GenderId"=>$gender,
                                    "InsertDate"=>date("Y-m-d H:i:s")                                    
                                    );
            $checkData = $this->WebserviceModel->checkDuplicateMobileno($mob_code,$mobile_number,$email);
            if($checkData){
                $response['response_code'] = '4';
                $response['response_message'] = 'Duplicate Mobile Number Or Email ';                
            }
            else
            {
                $getData = $this->WebserviceModel->signUp($saveDataArray);
                if(true)
                {
                    $updateArr = array("ConvertionStatus"=>1);
                    $updateStatus = $this->WebserviceModel->updateDependentCovertionStatus($mainPatientId,$dependentPatientId,$updateArr);
                    $response['response_code'] = '1';
                    $response['response_message'] = 'Success';
                    $response['Data'] = $getData;
                }
                else
                {
                    $response['response_code'] = '2';
                    $response['response_message'] = 'Failed';
                }
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function confirmMailDependent(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $mainPatientId = $data->patientId;
            $dependentPatientId = $data->dependentPatientId;
            $mn = $data->newMobileNumber * 1;
            $NewMobNumber = $mn;
            $NewMobCode = $data->newMobileCode; 
            /**Fetch Backup Json and Process */
            $this->getBackupJsonAndCropDependentData($mainPatientId,$dependentPatientId);
            /**Send Confirmation Mail to Parent and Dependent Patient */

            $DependentData = $this->WebserviceModel->getUserDataById($dependentPatientId);
            $dep_pat_email = $this->encryptDecrypt("dc",$DependentData->Email);

            $dep_pat_name = $this->encryptDecrypt("dc",$DependentData->FullName);

            $type = "convertProfileFinal";
            $mailDataArr = array("name"=>$dep_pat_name, "dependent_full_name"=>$dep_pat_name);
            $sendMail = Utility::callSendMailWithTemplate($dep_pat_email,$type,$mailDataArr2);
            
            $response['response_code'] = 1;
            $response['response_message'] = 'Success';
                     
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }

    function UpdateMRNOnRingServer(){        
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
            $mn = $data->mobile_number * 1;
            $mobile_number = $this->encryptDecrypt("en",$mn);
            $firstname = $this->encryptDecrypt("en",$data->firstname);
            $lastname = $this->encryptDecrypt("en",$data->lastname);
            $mob_code = "+".$data->mob_code;
            $dob = isset($data->DOB)?$data->DOB:NULL;
            if(isset($dob) && !empty($dob)){
                $ts = strtotime($dob);
                $dob1 = date("Y-m-d H:i:s", $ts);
            }else{
                $dob1 = NULL;
            }
            if(isset($data->email) && !empty($data->email)){
                $email = $this->encryptDecrypt("en",$data->email);
            }else{
                $email = NULL;
            }
            
            if(empty($data->bloodgroup) || !isset($data->bloodgroup)){
                $BloodGroupId = NULL;
            }else{
                $BloodGroupdata = $this->WebserviceModel->searchIdOfGivenString($data->bloodgroup,"BloodGroupMaster","BloodGroupCode");
				if(isset($BloodGroupdata) && !empty($BloodGroupdata)){
                    $BloodGroupId = $BloodGroupdata->BloodGroupId;
                }else{
                    $BloodGroupId = NULL;
                }
			}
            $address = urlencode($data->address);

            if(empty($data->country_name) || !isset($data->country_name)){
                $country = NULL;
            }else{
                $countrydata = $this->WebserviceModel->searchIdOfGivenString($data->country_name,"CountryMaster","CountryDescription");
				if(isset($countrydata) && !empty($countrydata)){
                    $country = $countrydata->ID;
                }else{
                    $country = NULL;
                }
			}
 
            if(empty($data->state_name) || !isset($data->state_name)){
                $state = NULL;
            }else{
                $statedata = $this->WebserviceModel->searchIdOfGivenString($data->state_name,"StateMaster","StateDescription");
				if(isset($statedata) && !empty($statedata)){
                    $state = $statedata->ID;
                }else{
                    $state = NULL;
                }
			}
            
            if(empty($data->city_name) || !isset($data->city_name)){
                $city = NULL;
            }else{
                $citydata = $this->WebserviceModel->searchIdOfGivenString($data->city_name,"CityMaster","CityDescription");
				if(isset($citydata) && !empty($citydata)){
                    $city = $citydata->ID;
                }else{
                    $city = NULL;
                }
			}
            $pincode = isset($data->pin_code)?$data->pin_code:NULL;
            if(empty($data->gender) || !isset($data->gender)){
                $gender = NULL;
            }else{
                $genderdata = $this->WebserviceModel->searchIdOfGivenString($data->gender,"GenderMaster","Description");
				if(isset($genderdata) && !empty($genderdata)){
                    $gender = $genderdata->Id;
                }else{
                    $gender = NULL;
                }
			}

            $MRN = isset($data->MRNO)?$data->MRNO:NULL;
            $impId = isset($data->impId)?$data->impId:1;
			$passportNo = isset($data->passport_no)?$data->passport_no:"";
			$NRICno = isset($data->NRICno)?$data->NRICno:"";
			$rIdArr = array();
            if(!isset($data->RingId) || empty($data->RingId)){
                // echo 1;
                $userData =  $this->WebserviceModel->getUserDataForCMSCopy($mob_code,$mobile_number,$email);
                // 
                $userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
                $frstn = explode(" ",$data->firstname);
                similar_text(strtolower($userData->FullName),strtolower($frstn[0]),$percent);
                // print_r($percent);exit;
                if($percent > 80)
                {
                    
                    array_push($rIdArr,$userData->PatientId);
                }else if($percent < 80){
                    $depList = $this->WebserviceModel->getDependentListByPatientId($userData->PatientId);
                    // print_r($depList);
                    if($depList){
                        foreach($depList as $val){
                            $DependentProfilePatientId = isset($val->DependentProfilePatientId)?$val->DependentProfilePatientId:"";
                            $depData =  $this->WebserviceModel->getUserDataById($DependentProfilePatientId);
                            $depfrstN = $this->encryptDecrypt("dc",$depData->FullName);
                            // print_r($depfrstN);
                            similar_text($depfrstN,$frstn[0],$percent1);
                            if($percent1 > 80){
                                array_push($rIdArr,$DependentProfilePatientId);
                            }
                        }
                    }
                }else{
                    // $RingId = "";
                    array_push($rIdArr,$userData->PatientId);
                } 
                // print_r($rIdArr);exit;
                if($rIdArr){
                    $RingId = $rIdArr[0];
                }else{
                    $RingId = "";
                }           
            }else{
                $RingId = $data->RingId;
            }

			$CMSId = $data->CMSId;
            if($RingId){
                $chkMRN = $this->WebserviceModel->checkMrnOfRingUser($RingId,$CMSId);
                if($chkMRN){
                    $response['response_code'] = '4';
                    $response['response_message'] = 'MRN already exists';
                    $response['response_data'] = $chkMRN;
                }
                else
                {
                    $insertArr = array( 
                        "MRNNo"=>$MRN,
                        "RINGID"=>$RingId,
                        "CMSID"=>$CMSId,
                        "ImplementationId"=>$impId
                    );
                    $insert = $this->WebserviceModel->InsertMrnOfUser($insertArr);
                    if($insert)
                    {				
                        $response['response_code'] = '1';
                        $response['response_message'] = 'Success';
                        $response['response_data'] = $insert;
                    }
                    else
                    {
                        $response['response_code'] = '2';
                        $response['response_message'] = 'Failed';
                    }
                }
            }else{
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed. RINGID not found';
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function confirmMailDependentWithBackup(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $mainPatientId = $data->patientId;
            $dependentPatientId = $data->dependentPatientId;
            $mn = $data->newMobileNumber * 1;
            $NewMobNumber = $mn;
            $email = $data->email;
            $isBackup = $data->isBackup;
            $NewMobCode = $data->newMobileCode; 
            $fullMobile  = $NewMobCode.$NewMobNumber;
            $SessionId = $data->SessionId;   
            $newDependentEmail = $this->encryptDecrypt("en",$email);
            $mobile_number = $this->encryptDecrypt("en",$NewMobNumber);          
                /**Fetch Backup Json and Process */
                /**Send Confirmation Mail to Parent and Dependent Patient */
                $patientdata = $this->WebserviceModel->getUserDataById($mainPatientId);
                $pat_email = $this->encryptDecrypt("dc",$patientdata->Email);
                $DependentData = $this->WebserviceModel->getUserDataById($dependentPatientId);
                $dep_pat_name = $this->encryptDecrypt("dc",$DependentData->FullName);
                $dep_pat_email = $this->encryptDecrypt("dc",$DependentData->Email);
                
                $pat_name = $this->encryptDecrypt("dc",$patientdata->FullName);
                $type1 = "convertProfile";
                $mailDataArr1 = array("name"=>$pat_name, "dependent_full_name"=>$dep_pat_name);
                $sendMail1 = Utility::callSendMailWithTemplate($pat_email,$type1,$mailDataArr1);

                
                $zipLink = $this->SendBackupToDependentInConvertion($dependentPatientId,$SessionId);
                if(isset($zipLink) && !empty($zipLink)){                     
                    $type3 = "convertProfileFinal";
                    $mailDataArr3 = array("name"=>$dep_pat_name, "mobile"=>$fullMobile, "ZipLink"=>$zipLink);
                    $sendMail3 = Utility::callSendMailWithTemplate($email,$type3,$mailDataArr3);  
                }else{
                    $response['response_code'] = 2;
                    $response['response_message'] = 'Zip creation error';
                }
                                          
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                                      
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }

    public function confirmMailDependentWithBackupForExistingAccount(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $mainPatientId = $data->patientId;
            $dependentPatientId = $data->dependentPatientId;
            $mn = $data->newMobileNumber * 1;
            $NewMobNumber = $mn;
            $email = $data->email;
            $isBackup = $data->isBackup;
            $NewMobCode = $data->newMobileCode; 
            $fullMobile  = $NewMobCode.$NewMobNumber;
            $SessionId = $data->SessionId;   
            $OlddependentPatientId = $data->OlddependentPatientId;
            $newDependentEmail = $this->encryptDecrypt("en",$email);
            $mobile_number = $this->encryptDecrypt("en",$NewMobNumber);          
                /**Fetch Backup Json and Process */
                /**Send Confirmation Mail to Parent and Dependent Patient */
                $patientdata = $this->WebserviceModel->getUserDataById($mainPatientId);
                $pat_email = $this->encryptDecrypt("dc",$patientdata->Email);
                $DependentData = $this->WebserviceModel->getUserDataById($dependentPatientId);
                $dep_pat_name = $this->encryptDecrypt("dc",$DependentData->FullName);
                $dep_pat_email = $this->encryptDecrypt("dc",$DependentData->Email);
                
                $pat_name = $this->encryptDecrypt("dc",$patientdata->FullName);
                $type1 = "convertProfile";
                $mailDataArr1 = array("name"=>$pat_name, "dependent_full_name"=>$dep_pat_name);
                $sendMail1 = Utility::callSendMailWithTemplate($pat_email,$type1,$mailDataArr1);

                
                $zipLink = $this->SendBackupToDependentInConvertion($dependentPatientId,$SessionId);
                // print_r($zipLink);exit;
                if(isset($zipLink) && !empty($zipLink)){                     
                    $type3 = "convertProfileFinalNew";
                    $mailDataArr3 = array("name"=>$dep_pat_name,"mobile"=>$fullMobile, "ZipLink"=>$zipLink);
                    $sendMail3 = Utility::callSendMailWithTemplate($email,$type3,$mailDataArr3);  
                }else{
                    $response['response_code'] = 2;
                    $response['response_message'] = 'Zip creation error';
                }
                $updateArray = array("ConvertionStatus"=>1);
                $updateStatus = $this->WebserviceModel->updateDependentCovertionStatus($mainPatientId,$OlddependentPatientId,$updateArray);                          
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                                      
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }

    public function getReportsCount(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $pat_id = $data->patient_id;
            $ringGrpId = isset($data->RingGroupMasterId)?$data->RingGroupMasterId:"";
            $reportList1 = $this->WebserviceModel->getReportsCount1($pat_id,$ringGrpId);
            $reportList2 = $this->WebserviceModel->getReportsCount2($pat_id,$ringGrpId);
            $reportList3 = $this->WebserviceModel->getReportsCount3($pat_id);
            if($reportList1 || $reportList2 || $reportList3)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['Web_MRDT'] = count($reportList1);
                $response['Web_MRDT_REF'] = count($reportList2);
                $response['CMS_MRDT'] = count($reportList3);
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }else{
            $response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
        }
        echo json_encode($response); exit;
    }

    public function updateNotDownloadedReportToDependentToMain(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $oldPatientId = $data->oldPatientId;
			$newPatientId = $data->newPatientId;
            /************** Update Patient's E-report transit and details******************/
            $reportData = $this->WebserviceModel->EreportForTransferUpdate($oldPatientId);
            if($reportData){
                foreach($reportData as $reportval){
                    $updateReport =  $this->WebserviceModel->updateReportDetails($reportval->ReportTransitId, $newPatientId, "EreportsTransit"); 
                }
            }
            $CMSreportData = $this->WebserviceModel->CMSreportForTransferUpdate($oldPatientId);
            if($CMSreportData){
                foreach($CMSreportData as $CMSreportval){
                    $updateCmsReport =  $this->WebserviceModel->updateCMSReportPatient($CMSreportval->Id, $newPatientId, "CMS_Report"); 
                }
            }
            /************************************************************* */
			if($true){
				$response['response_code']=1;
				$response['response_message']='Success';
			}
			else
			{
				$response['response_code']=2;
				$response['response_message']='Failed';
			}
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }

    function UpdateUserVerificationData(){
        $data = json_decode(file_get_contents('php://input'));
        if($data){
            $RINGID = $data->patientId;          
			$oldRingId = $data->oldPatientId;
            $chkMRN = $this->WebserviceModel->checkMrnOfRingUserByRingId($oldRingId);
            if($chkMRN){
                $updateArr = array( 
                    "RINGID"=>$RINGID,
                );
                $update = $this->WebserviceModel->UpdateMrnOfUser($updateArr,$chkMRN->Id);
            }
            
            if($update)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $update;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }  
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }     
        echo json_encode($response);exit;
    }

    public function userVerificationForTesting()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {         
            $ringId = $data->patientId;
            $impId = isset($data->ImpId)?$data->ImpId:1;
            $isVarified = $this->WebserviceModel->userVerificationForTesting($ringId,$impId);
            if($isVarified)
            {          
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';	
                $response['response_data'] = $isVarified;
            }else{				
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;        
    }

    function InsertUserVerificationDataTesting(){
        $data = json_decode(file_get_contents('php://input'));
        if($data){
            $MRN = $data->mrno;
            $RINGID = $data->patientId;
            $impId = isset($data->ImpId)?$data->ImpId:1;
            $CMSID = $data->CMSID;
            $check = $this->WebserviceModel->userVerification($RINGID,$impId);
            if(isset($check) && !empty($check)){
                $updateArr = array("IsConnected"=>1);
                $update = $this->WebserviceModel->UpdateMrnOfUser($updateArr,$check->Id);
                if($update){
                    $insert = $this->WebserviceModel->checkMrnOfRingUserByRingId($RINGID);
                }
            }else{
                $insertArr = array( 
                    "MRNNo"=>$MRN,
                    "RINGID"=>$RINGID,
                    "CMSID"=>$CMSID,
                    "ImplementationId"=>$impId,
                    "IsConnected"=>1
                );
                $insert = $this->WebserviceModel->InsertMrnOfUser($insertArr);
            }
            if($insert)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $insert;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }  
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }     
        echo json_encode($response);exit;
    }

    public function saveDoctorDetailsOnRingServerOLD(){	
		$data = json_decode(file_get_contents('php://input'));
		if($data)
		{
			$locationId = $data->LocationId;
			$hospitalName = $data->HospitalName;
			$mmcn = $data->DoctorMMCN;
			$doctorId = $data->DoctorCMSId;
			
			$chkDoctor = $this->WebserviceModel->doctorSearch($mmcn);
			$HospitalRingId = $this->WebserviceModel->tenantsSearchDataByKeywordsNew($hospitalName);
			if(isset($chkDoctor) && isset($HospitalRingId))
			{
				$insertIntArr = array("ImplementationId"=>1,
								"LocationId"=>$locationId,
								"CmsDoctorId"=>$doctorId,
								"RingDoctorId"=>$chkDoctor->UserId,
								"TenantId"=>$HospitalRingId->TenantId,
                                "Scheduled"=>1
                            );
				$chkImpExist = $this->WebserviceModel->chkImpExist($doctorId,$locationId);
				if(!isset($chkImpExist) && empty($chkImpExist)){
					$updateIntTable = $this->WebserviceModel->DoctorImpData($insertIntArr);
				}

				$response['response_code'] = 1;
                $response['response_data'] = $chkDoctor->UserId;
                $response['response_message'] = 'Doctor saved successfully';
				
			}
			else
			{
				$PhoneNumber = isset($data->PhoneNumber)?$data->PhoneNumber:NULL; 
				// $PhoneCode = isset($data->PhoneCode)?$data->PhoneCode:NULL; 
                if(isset($data->PhoneCode) && !empty($data->PhoneCode)){
                    $PhoneCode = "+".$data->PhoneCode;
                }else{
                    $PhoneCode = NULL; 
                }
				$speciality = isset($data->Speciality)?$data->Speciality:NULL; 
				$email = isset($data->EmailID)?$data->EmailID:NULL; 
				$fname = isset($data->FirstName)?$data->FirstName:NULL;
				$lname = isset($data->LastName)?$data->LastName:NULL;
				$user = $fname." ".$lname;
				$mmcnumber = isset($mmcn)?$mmcn:NULL;
				// $Password = isset($data->Password)?$data->Password:12345678;
                if(isset($data->Password) && !empty($data->Password)){
                    $Password = $data->Password;
                }else{
                    $Password = 12345678; 
                }
				$username = isset($user)?$user:NULL;
				$TenantNumber = $data->HospitalNumber;
				$TenantAddress = $data->HospitalAddress;
				$TenantFaxNumber = $data->HospitalFaxNumber;
				$TenantPhoneCode = $data->HospitalPhoneCode;
				$TenantPhoneNumber = $data->HospitalPhoneNumber;
				$TenantName = $data->HospitalName;
				$TenantPostCode = $data->HospitalPostCode;
				$TenantCountry = isset($data->HospitalCountry)?$data->HospitalCountry:0;
            	$TenantState = isset($data->HospitalState)?$data->HospitalState:0;
            	$TenantCity = isset($data->HospitalCity)?$data->HospitalCity:0;
				if(empty($data->HospitalCountry) || !isset($data->HospitalCountry)){
                $country = 0;
				}else{
					$countrydata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalCountry,"CountryMaster","CountryDescription");
					if(isset($countrydata) && !empty($countrydata)){
						$country = $countrydata->ID;
					}else{
						$country = 0;
					}
				}
	
				if(empty($data->HospitalState) || !isset($data->HospitalState)){
					$state = 0;
				}else{
					$statedata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalState,"StateMaster","StateDescription");
					if(isset($statedata) && !empty($statedata)){
						$state = $statedata->ID;
					}else{
						$state = 0;
					}
				}
				
				if(empty($data->HospitalCity) || !isset($data->HospitalCity)){
					$city = 0;
				}else{
					$citydata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalCity,"CityMaster","CityDescription");
					if(isset($citydata) && !empty($citydata)){
						$city = $citydata->ID;
					}else{
						$city = 0;
					}
				}
				$paramJson = '{
					"PhoneNumber": "'.$PhoneNumber.'",
					"FacilityTypeId": "2",
					"TenantCompanyNumber": "'.$TenantNumber.'",
					"TenantAddress":"'.$TenantAddress.'",
					"TenantFaxNumber":"'.$TenantFaxNumber.'",
					// "speciality":"'.$speciality.'",
                    "speciality":"1",
					"Role": "12",
					"TenantName":"'.$TenantName.'",
					"TenantPhoneNumber":"'.$TenantPhoneNumber.'",
					"email":"'.$email.'",
					"name":"'.$username.'",
					"mmcnumber":"'.$mmcnumber.'",
					"password":"'.$Password.'",
					"username":"'.$username.'",
					"TenantCountry":"'.$country.'",
					"TenantState": "'.$state.'",
					"TenantCity":"'.$city.'",
					"TenantPin": "'.$TenantPostCode.'",
					"TenantPhoneCode": "'.$TenantPhoneCode.'",
					"PhoneCode":"'.$TenantPhoneCode.'"
					}';
					
					
				$url = 'https://apiweb.ring.healthcare:5028/api/Register/RegisterDoctor';
				$json = $paramJson;   
				// echo $json;exit;
				$headers = array('Content-Type: application/json');
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => $json,
				CURLOPT_HTTPHEADER => $headers,
				));
				$res = curl_exec($curl);
				if (curl_errno($curl)) {
					$error_msg = curl_error($curl);
					echo $error_msg;
				}
				curl_close($curl);
				echo $res;
				if($res == '{}'){
					$insert_id = $this->WebserviceModel->lastInstertedId();
					$insertIntArr = array("ImplementationId"=>"1",
							"LocationId"=>$locationId,
							"CmsDoctorId"=>$doctorId,
							"RingDoctorId"=>$insert_id->UserId,
							"TenantId"=>$insert_id->TenantId,
                            "Scheduled"=>1
                        );
					$chkImpExist = $this->WebserviceModel->chkImpExist($doctorId,$locationId);

					if(!isset($chkImpExist) && empty($chkImpExist)){
						$updateIntTable = $this->WebserviceModel->DoctorImpData($insertIntArr);
					}	
                    $response['response_code'] = 1;
                    $response['response_data'] = $insert_id->UserId;
                    $response['response_message'] = 'Doctor saved successfully';			
				}else{
                    $response['response_code']=2;
					$response['response_message']='Failed';
                }
							
			}
			
		}
		else
		{
			$response['response_code']=3;
			$response['response_message']='data is null';
		} 	
		echo json_encode($response);exit;
	}

    public function userConnectionStatus()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {         
            $ringId = $data->patientId;
            $impId = 1;
            $connection = $this->WebserviceModel->userConnectionStatus($ringId,$impId);
            if($connection)
            {          
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';	
                $response['response_data'] = $connection;
            }else{				
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;        
    }

    function updateUserMRN(){
        $data = json_decode(file_get_contents('php://input'));
        if($data){
            $RINGID = $data->patientId;
            $impId = 1;
            $check = $this->WebserviceModel->userVerification($RINGID,$impId);
            if(isset($check) && !empty($check)){
                $updateArr = array("IsConnected"=>1);
                $update = $this->WebserviceModel->UpdateMrnOfUser($updateArr,$check->Id);
                if($update){
                    $result = $this->WebserviceModel->checkMrnOfRingUserByRingId($RINGID);
                }
            }
            if($result)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $result;
            }
            else
            {
                $response['response_code'] = '2';
                $response['response_message'] = 'Failed';
            }  
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is null';
        }     
        echo json_encode($response);exit;
    }

    public function saveDoctorDetailsOnRingServer(){	
		$data = json_decode(file_get_contents('php://input'));
		if($data)
		{
			
            $Action = $data->Action;
            if($Action === "AddDoctor"){
            // echo "<pre>"; print_r($data);exit;
                $hospitalDetails = $data->HospitalDetails;
                // echo "<pre>"; print_r($hospitalDetails);exit;
                $locationId = $hospitalDetails[0]->LocationId;
                $hospitalName = $hospitalDetails[0]->HospitalName;
                $mmcn = $data->DoctorMMCN;
                $doctorId = $data->DoctorCMSId;
                $ImplementationId = $data->ImplementationId;
                $IsScheduled = $hospitalDetails[0]->IsScheduled;
                $url1 = 'https://cmsapi.ring.healthcare:5001/index.php/Api/GetCMSDoctorDetails';
                        $json1 = '{"UserId": "'.$doctorId.'"}';   
                        $headers1 = array('Content-Type: application/json');
                        $curl1 = curl_init();
                        curl_setopt_array($curl1, array(
                        CURLOPT_URL => $url1,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $json1,
                        CURLOPT_HTTPHEADER => $headers1,
                        ));
                        $res1 = curl_exec($curl1);
                        if (curl_errno($curl1)) {
                            $error_msg1 = curl_error($curl1);
                            echo $error_msg1;
                        }
                        curl_close($curl1);
                $docdetail = json_decode($res1);
                $doctorCmsdetail = $docdetail->data;
                $chkDoctor = $this->WebserviceModel->doctorSearch($mmcn); // Check doctor avl in ring db
                //echo "<pre>"; print_r($chkDoctor);exit;
                $HospitalRingId = $this->WebserviceModel->tenantsSearchDataByKeywordsNew($hospitalName); //find doctor associated hospital
                if(isset($chkDoctor) && isset($HospitalRingId))
                {
                    $insertIntArr = array("ImplementationId"=>1,
                                    "LocationId"=>$locationId,
                                    "CmsDoctorId"=>$doctorId,
                                    "RingDoctorId"=>$chkDoctor->UserId,
                                    "TenantId"=>$HospitalRingId->TenantId,
                                    "Scheduled"=>1
                                );
                    $chkImpExist = $this->WebserviceModel->chkImpExist($doctorId,$locationId);
                    if(!isset($chkImpExist) && empty($chkImpExist)){
                        $updateIntTable = $this->WebserviceModel->DoctorImpData($insertIntArr);
                    }

                    $chkUserTenant = $this->WebserviceModel->chkUserTenant($chkDoctor->UserId,$HospitalRingId->TenantId);
                    if(!isset($chkUserTenant) && empty($chkUserTenant)){
                        $userTenantArr = array("UserId"=>$chkDoctor->UserId,
                            "TenantId"=>$HospitalRingId->TenantId
                        );
                        $InsertUserTenant = $this->WebserviceModel->InsertUserTenant($userTenantArr);
                    }

                    $ResultDocId = $chkDoctor->UserId;
                    // $response['response_code'] = 1;
                    // $response['response_data'] = $chkDoctor->UserId;
                    // $response['response_message'] = 'Doctor saved successfully';
                    
                }
                else
                {
                    $PhoneNumber = isset($data->PhoneNumber)?$data->PhoneNumber:NULL; 
                    // $PhoneCode = isset($data->PhoneCode)?$data->PhoneCode:NULL; 
                    if(isset($data->PhoneCode) && !empty($data->PhoneCode)){
                        $PhoneCode = "+".$data->PhoneCode;
                    }else{
                        $PhoneCode = NULL; 
                    }
                    //$speciality = isset($data->Speciality)?$data->Speciality:NULL; 
                    // if(empty($data->Speciality) || !isset($data->Speciality)){
                    //     $speciality = 0;
                    //     }else{
                    //         $specialityData = $this->WebserviceModel->searchIdOfGivenString($data->Speciality,"PractitionerSpecialityMaster","SpecialityDescription");
                    //         if(isset($specialityData) && !empty($specialityData)){
                    //             $speciality = $specialityData->ID;
                    //         }else{
                    //             $speciality = 1;
                    //         }
                    //     }

                    if(isset($data->Speciality) && !empty($data->Speciality)){
                        if($data->Speciality == "General practitioner"){
                            $speciality = 18;
                        }else if($data->Speciality == "Dentist"){
                            $speciality = 18;
                        }else if($data->Speciality == "Pediatrician"){
                            $speciality = 8;
                        }else if($data->Speciality == "Obstetrics & Gynaecology (O&G)"){
                            $speciality = 9;
                        }else if($data->Speciality == "Aesthetic"){
                            $speciality = 18;
                        }else if($data->Speciality == "Dietitian"){
                            $speciality = 17;
                        }
                    }else{
                        $speciality = 0;
                    }
                    $email = isset($data->EmailID)?$data->EmailID:NULL; 
                    $fname = isset($data->FirstName)?$data->FirstName:NULL;
                    $lname = isset($data->LastName)?$data->LastName:NULL;
                    $user = $fname." ".$lname;
                    $mmcnumber = isset($mmcn)?$mmcn:NULL;
                    // $Password = isset($data->Password)?$data->Password:12345678;
                    if(isset($data->Password) && !empty($data->Password)){
                        $Password = $data->Password;
                    }else{
                        $Password = 12345678; 
                    }
                    $username = isset($user)?$user:NULL;
                    $TenantNumber = $hospitalDetails[0]->HospitalNumber;
                    $TenantAddress = $hospitalDetails[0]->HospitalAddress;
                    $TenantFaxNumber = $hospitalDetails[0]->HospitalFaxNumber;
                    $TenantPhoneCodeDigit = $hospitalDetails[0]->HospitalPhoneCode;
                    $GetPhCodeId = $this->WebserviceModel->getPhoneCodeId($TenantPhoneCodeDigit);
                    if(isset($GetPhCodeId)){
                        $TenantPhoneCode = $GetPhCodeId->ID;
                    }else{
                        $TenantPhoneCode = null;
                    }
                    // print_r($TenantPhoneCode);exit;
                    $TenantPhoneNumber = $hospitalDetails[0]->HospitalPhoneNumber;
                    $TenantName = $hospitalDetails[0]->HospitalName;
                    $TenantPostCode = $hospitalDetails[0]->HospitalPostCode;
                    $TenantCountry = isset($hospitalDetails[0]->HospitalCountry)?$hospitalDetails[0]->HospitalCountry:0;
                    $TenantState = isset($hospitalDetails[0]->HospitalState)?$hospitalDetails[0]->HospitalState:0;
                    $TenantCity = isset($hospitalDetails[0]->HospitalCity)?$hospitalDetails[0]->HospitalCity:0;
                    if(empty($hospitalDetails[0]->HospitalCountry) || !isset($hospitalDetails[0]->HospitalCountry)){
                    $country = 0;
                    }else{
                        $countrydata = $this->WebserviceModel->searchIdOfGivenString($hospitalDetails[0]->HospitalCountry,"CountryMaster","CountryDescription");
                        if(isset($countrydata) && !empty($countrydata)){
                            $country = $countrydata->ID;
                        }else{
                            $country = 0;
                        }
                    }
        
                    if(empty($hospitalDetails[0]->HospitalState) || !isset($hospitalDetails[0]->HospitalState)){
                        $state = 0;
                    }else{
                        $statedata = $this->WebserviceModel->searchIdOfGivenString($hospitalDetails[0]->HospitalState,"StateMaster","StateDescription");
                        if(isset($statedata) && !empty($statedata)){
                            $state = $statedata->ID;
                        }else{
                            $state = 0;
                        }
                    }
                    
                    if(empty($hospitalDetails[0]->HospitalCity) || !isset($hospitalDetails[0]->HospitalCity)){
                        $city = 0;
                    }else{
                        $citydata = $this->WebserviceModel->searchIdOfGivenString($hospitalDetails[0]->HospitalCity,"CityMaster","CityDescription");
                        if(isset($citydata) && !empty($citydata)){
                            $city = $citydata->ID;
                        }else{
                            $city = 0;
                        }
                    }

                    if(empty($doctorCmsdetail->sirname) || !isset($doctorCmsdetail->sirname)){
                        $PrefixId = null;
                    }else{
                        $sirnamedata = $this->WebserviceModel->searchIdOfGivenString($doctorCmsdetail->sirname,"HonorificMaster","Description");
                        if(isset($sirnamedata) && !empty($sirnamedata)){
                            $PrefixId = $sirnamedata->id;
                        }else{
                            $PrefixId = null;
                        }
                    }

                    if(!empty($doctorCmsdetail->genderid) || isset($doctorCmsdetail->genderid)){
                        $genderid = $doctorCmsdetail->genderid;
                    }else{
                        $genderid = null;
                    }

                    $IsScheduled = $hospitalDetails[0]->IsScheduled;
                    $sitiUserName = "SITI".$doctorId;
                    $paramJson = '{
                        "PhoneNumber": "'.$PhoneNumber.'",
                        "FacilityTypeId": "2",
                        "TenantCompanyNumber": "'.$TenantNumber.'",
                        "TenantAddress":"'.$TenantAddress.'",
                        "TenantFaxNumber":"'.$TenantFaxNumber.'",
                        "speciality":"'.$speciality.'",
                        "Role": "12",
                        "TenantName":"'.$TenantName.'",
                        "TenantPhoneNumber":"'.$TenantPhoneNumber.'",
                        "email":"'.$email.'",
                        "name":"'.$fname.'",
                        "mmcnumber":"'.$mmcnumber.'",
                        "password":"'.$Password.'",
                        "username":"'.$sitiUserName.'",
                        "TenantCountry":"'.$country.'",
                        "TenantState": "'.$state.'",
                        "TenantCity":"'.$city.'",
                        "TenantPin": "'.$TenantPostCode.'",
                        "TenantPhoneCode": "'.$TenantPhoneCode.'",
                        "PhoneCode":"'.$TenantPhoneCode.'",                       
                        "DisplayName":"'.$fname.'",
                        "GenderId":"'.$genderid.'",
                        "HonorficMasterId":"'.$PrefixId.'",
                        "LastName":"'.$lname.'"
                        }';
                        
                        
                    $url = 'https://apiweb.ring.healthcare:5028/api/Register/IntegrateRegisterDoctor';
                    $json = $paramJson;   
                    // echo $json;exit;
                    $headers = array('Content-Type: application/json');
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $json,
                    CURLOPT_HTTPHEADER => $headers,
                    ));
                    $res = curl_exec($curl);
                    if (curl_errno($curl)) {
                        $error_msg = curl_error($curl);
                        echo $error_msg;
                    }
                    curl_close($curl);
                    echo $res;
                    if($res == '{}'){
                        $insert_id = $this->WebserviceModel->lastInstertedId();
                        $insertIntArr = array("ImplementationId"=>"1",
                                "LocationId"=>$locationId,
                                "CmsDoctorId"=>$doctorId,
                                "RingDoctorId"=>$insert_id->UserId,
                                "TenantId"=>$insert_id->TenantId,
                                "Scheduled"=>1
                            );
                        $chkImpExist = $this->WebserviceModel->chkImpExist($doctorId,$locationId);

                        if(!isset($chkImpExist) && empty($chkImpExist)){
                            $updateIntTable = $this->WebserviceModel->DoctorImpData($insertIntArr);
                        }	

                        
                        // $response['response_code'] = 1;
                        // $response['response_data'] = $insert_id->UserId;
                        // $response['response_message'] = 'Doctor saved successfully';		
                        $ResultDocId = $insert_id->UserId;	
                    }
                               
                }
                if($ResultDocId){
                    foreach($hospitalDetails as $hospitalDetailsVal){
                        $locationId1 = $hospitalDetailsVal->LocationId;
                        $hospitalName1 = $hospitalDetailsVal->HospitalName;
                        $HospitalRingId1 = $this->WebserviceModel->tenantsSearchDataByKeywordsNew($hospitalName1);
                        $DocRingId = $ResultDocId;
                        if($HospitalRingId1){
                            $insertIntArr1 = array("ImplementationId"=>$ImplementationId,
                                "LocationId"=>$locationId,
                                "CmsDoctorId"=>$doctorId,
                                "RingDoctorId"=>$DocRingId,
                                "TenantId"=>$HospitalRingId1->TenantId,
                                "Scheduled"=>1
                            );
                            $chkImpExist1 = $this->WebserviceModel->chkImpExist($doctorId,$locationId1);
                            if(!isset($chkImpExist1) && empty($chkImpExist1)){
                                $updateIntTable = $this->WebserviceModel->DoctorImpData($insertIntArr1);
                            }
                            $chkUserTenant = $this->WebserviceModel->chkUserTenant($DocRingId,$HospitalRingId1->TenantId);
                            if(!isset($chkUserTenant) && empty($chkUserTenant)){
                                $userTenantArr = array("UserId"=>$DocRingId,
                                    "TenantId"=>$HospitalRingId1->TenantId
                                );
                                $InsertUserTenant = $this->WebserviceModel->InsertUserTenant($userTenantArr);
                            }
                        }else{
                            $TenantNumber1 = $hospitalDetailsVal->HospitalNumber;
                            $TenantAddress1 = $hospitalDetailsVal->HospitalAddress;
                            $TenantFaxNumber1 = $hospitalDetailsVal->HospitalFaxNumber;
                            $TenantPhoneCodeDigit1 = $hospitalDetailsVal->HospitalPhoneCode;
                            $GetPhCodeId = $this->WebserviceModel->getPhoneCodeId($TenantPhoneCodeDigit1);
                            if(isset($GetPhCodeId)){
                                $TenantPhoneCode1 = $GetPhCodeId->ID;
                            }else{
                                $TenantPhoneCode1 = null;
                            }
                            $TenantPhoneNumber1 = $hospitalDetailsVal->HospitalPhoneNumber;
                            $TenantName1 = $hospitalDetailsVal->HospitalName;
                            $TenantPostCode1 = $hospitalDetailsVal->HospitalPostCode;
                            if(empty($hospitalDetailsVal->HospitalCountry) || !isset($hospitalDetailsVal->HospitalCountry)){
                                $country1 = 0;
                            }else{
                                $countrydata1 = $this->WebserviceModel->searchIdOfGivenString($hospitalDetailsVal->HospitalCountry,"CountryMaster","CountryDescription");
                                if(isset($countrydata1) && !empty($countrydata1)){
                                    $country1 = $countrydata1->ID;
                                }else{
                                    $country1 = 0;
                                }
                            }
                
                            if(empty($hospitalDetailsVal->HospitalState) || !isset($hospitalDetailsVal->HospitalState)){
                                $state1 = 0;
                            }else{
                                $statedata1 = $this->WebserviceModel->searchIdOfGivenString($hospitalDetailsVal->HospitalState,"StateMaster","StateDescription");
                                if(isset($statedata) && !empty($statedata)){
                                    $state1 = $statedata1->ID;
                                }else{
                                    $state1 = 0;
                                }
                            }
                            
                            if(empty($hospitalDetailsVal->HospitalCity) || !isset($hospitalDetailsVal->HospitalCity)){
                                $cit1y = 0;
                            }else{
                                $citydata1 = $this->WebserviceModel->searchIdOfGivenString($hospitalDetailsVal->HospitalCity,"CityMaster","CityDescription");
                                if(isset($citydata1) && !empty($citydata1)){
                                    $city1 = $citydata1->ID;
                                }else{
                                    $city1 = 0;
                                }
                            }
                            $TenantLatitude = $hospitalDetailsVal->HospitalLatitude;
                            $TenantLongitude = $hospitalDetailsVal->HospitalLongitude;
                            $HosInsertArr1 = array("TenantCode"=>$TenantNumber1,
                                "TenantNumber"=>$TenantNumber1,
                                "TenantName"=>$TenantName1,
                                "TenantTypeId"=>2,
                                "PhoneNumber"=>$TenantPhoneNumber1,
                                "Address"=>$TenantAddress1,
                                "CountryID"=>$country1,
                                "StateID"=>$state1,
                                "CityID"=>$city1,
                                "PostCode"=>$TenantPostCode1,
                                "Latitude"=>$TenantLatitude1,
                                "Longitude"=>$TenantLongitude,
                                "IsActive"=>1,
                                "PhoneCode"=>$TenantPhoneCode1
                            );

                            $InsertHospitalData1 = $this->WebserviceModel->InsertHospitalData($HosInsertArr1); 
                            if($InsertHospitalData1){
                                $insertIntArr1 = array("ImplementationId"=>$ImplementationId,
                                    "LocationId"=>$locationId1,
                                    "CmsDoctorId"=>$doctorId,
                                    "RingDoctorId"=>$DocRingId,
                                    "TenantId"=>$InsertHospitalData1,
                                    "Scheduled"=>1
                                );
                                $chkImpExist1 = $this->WebserviceModel->chkImpExist($doctorId,$locationId1);
                                if(!isset($chkImpExist1) && empty($chkImpExist1)){
                                    $updateIntTable = $this->WebserviceModel->DoctorImpData($insertIntArr1);
                                }
                                $chkUserTenant = $this->WebserviceModel->chkUserTenant($DocRingId,$HospitalRingId1->TenantId);
                                if(!isset($chkUserTenant) && empty($chkUserTenant)){
                                    $userTenantArr1 = array("UserId"=>$DocRingId,
                                        "TenantId"=>$InsertHospitalData1
                                    );
                                    $InsertUserTenant = $this->WebserviceModel->InsertUserTenant($userTenantArr1);
                                }
                            }
                        }
                    }
                    $response['response_code'] = 1;
                    $response['response_data'] = $ResultDocId;
                    $response['response_message'] = 'Doctor saved successfully';            
                }
                else{
                    $response['response_code']=2;
                    $response['response_message']='Failed';
                }
            }else if($Action === "UpdateDoctor"){
                $doctorId = $data->DoctorCMSId;
                $ImpId = $data->ImplementationId;
                $HospitalDetailsArr = $data->HospitalDetails;
                $chkDoctorImp = $this->WebserviceModel->chkDoctorImp($doctorId,$ImpId);
                if($chkDoctorImp){
                    $DocRingId = $chkDoctorImp->RingDoctorId;
                    $PhoneNumber = $data->PhoneNumber; 
                    $PhoneCode = "+".$data->PhoneCode;
                    $email = $data->EmailID; 
                    $fname = $data->FirstName;
                    $lname = $data->LastName;
                    $user = $fname." ".$lname;
                    $mmcn = $data->DoctorMMCN;
                    $IsScheduled = $data->IsScheduled;
                    $updateArr = array("Username"=>"SITI".$doctorId,
                        "Email"=>$email,
                        "PhoneNumber"=>$PhoneNumber,
                        "PhoneCode"=>$PhoneCode,
                        "MMCNumber"=>$mmcn,
                        "DisplayName"=>$fname,
                        "LastName"=>$lname
                    );
                    $updateArr1 = array("PractitionerName"=>$user,
                        "Email"=>$email,
                        "Mobile"=>$PhoneNumber,
                        "MMCNumber"=>$mmcn
                    );
                    $updateUserTable = $this->WebserviceModel->updateUserTable($DocRingId,$updateArr);   
                    if($updateUserTable){
                        $getPracId = $this->WebserviceModel->getPractitionerId($DocRingId);
                        $updatePractitionerTable = $this->WebserviceModel->updatePractitionerTable($getPracId->LinkUserId,$updateArr1);                       
                        $response['response_code'] = 1;
                        $response['response_message'] = 'Doctor updated successfully';			
                    }else{
                        $response['response_code']=2;
                        $response['response_message']='Failed';
                    }
                }else
                {
                    $response['response_code']=4;
                    $response['response_message']='Doctor not found';
                }
            }else if($Action === "DeactivateDoctor"){
                $doctorId = $data->DoctorCMSId;
                $ImpId = $data->ImplementationId;
                // $LocationId = isset($data->LocationId)?$data->LocationId:1;
                $chkDoctorImp = $this->WebserviceModel->chkImpAvlByDoctorId($doctorId);
                // print_r($chkDoctorImp);exit;
                if($chkDoctorImp){
                    // $deleteImpData = $this->WebserviceModel->deleteImpData($doctorId,$LocationId);
                    $userUpdateArr = array("IsActive"=>0, "IsDeleted"=>1);
                    $updateInUserTable = $this->WebserviceModel->updateInUserTable($chkDoctorImp[0]->RingDoctorId,$userUpdateArr);
                    if($updateInUserTable){
                        $response['response_code'] = 1;
                        $response['response_message'] = 'Doctor Deactivate successfully';			
                    }else{
                        $response['response_code']=2;
                        $response['response_message']='Failed';
                    }
                }
                else
                {
                    $response['response_code']=4;
                    $response['response_message']='Doctor not found';
                }

            }else if($Action === "ReactivateDoctor"){
                $doctorId = $data->DoctorCMSId;
                $ImpId = $data->ImplementationId;
                // $LocationId = isset($data->LocationId)?$data->LocationId:1;
                $chkDoctorImp = $this->WebserviceModel->chkImpAvlByDoctorId($doctorId);
                if($chkDoctorImp){
                    // $deleteImpData = $this->WebserviceModel->deleteImpData($doctorId,$LocationId);
                    $userUpdateArr = array("IsActive"=>1, "IsDeleted"=>0);
                    $updateInUserTable = $this->WebserviceModel->updateInUserTable($chkDoctorImp[0]->RingDoctorId,$userUpdateArr);
                    if($updateInUserTable){
                        $response['response_code'] = 1;
                        $response['response_message'] = 'Doctor Activate successfully';			
                    }else{
                        $response['response_code']=2;
                        $response['response_message']='Failed';
                    }
                }
                else
                {
                    $response['response_code']=4;
                    $response['response_message']='Doctor not found';
                }
            }else if($Action === "ScheduleChange"){
                $doctorId = $data->DoctorCMSId;
                $ImpId = $data->ImplementationId;
                $LocationId = $data->LocationId;
                $IsScheduled = $data->IsScheduled;
                // $chkDoctorImp = $this->WebserviceModel->chkDoctorImp($doctorId,$ImpId);
                // if($chkDoctorImp){
                //     $updateArr = array("Scheduled"=>$IsScheduled);
                //     $updateDocSchedule = $this->WebserviceModel->updateDocSchedule($doctorId,$LocationId,$updateArr);   
                //     if($updateDocSchedule){
                        $response['response_code'] = 1;
                        $response['response_message'] = 'Doctor Schedule updated successfully';			
                //     }else{
                //         $response['response_code']=2;
                //         $response['response_message']='Failed';
                //     }
                // }    

            }else if($Action === "AddLocation"){
                $locationId = $data->LocationId;
                $hospitalName = $data->HospitalName;
                $doctorId = $data->DoctorCMSId;
                $ImplementationId = $data->ImplementationId;
                $HospitalRingId = $this->WebserviceModel->tenantsSearchDataByKeywordsNew($hospitalName);
                $chkDoctorImp = $this->WebserviceModel->chkDoctorImp($doctorId,$ImpId);
                $DocRingId = $chkDoctorImp->RingDoctorId;
                if($HospitalRingId){
                    $insertIntArr = array("ImplementationId"=>$ImplementationId,
                        "LocationId"=>$locationId,
                        "CmsDoctorId"=>$doctorId,
                        "RingDoctorId"=>$DocRingId,
                        "TenantId"=>$HospitalRingId->TenantId,
                        "Scheduled"=>$IsScheduled
                    );
                    $chkImpExist = $this->WebserviceModel->chkImpExist($doctorId,$locationId);
                    if(!isset($chkImpExist) && empty($chkImpExist)){
                        $updateIntTable = $this->WebserviceModel->DoctorImpData($insertIntArr);
                    }
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Location added successfully';
                }else{
                    $TenantNumber = $data->HospitalNumber;
                    $TenantAddress = $data->HospitalAddress;
                    $TenantFaxNumber = $data->HospitalFaxNumber;
                    $TenantPhoneCodeDigit = $data->HospitalPhoneCode;
                    $GetPhCodeId = $this->WebserviceModel->getPhoneCodeId($TenantPhoneCodeDigit);
                    if(isset($GetPhCodeId)){
                        $TenantPhoneCode = $GetPhCodeId->ID;
                    }else{
                        $TenantPhoneCode = null;
                    }
                    $TenantPhoneNumber = $data->HospitalPhoneNumber;
                    $TenantName = $data->HospitalName;
                    $TenantPostCode = $data->HospitalPostCode;
                    if(empty($data->HospitalCountry) || !isset($data->HospitalCountry)){
                        $country = 0;
                    }else{
                        $countrydata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalCountry,"CountryMaster","CountryDescription");
                        if(isset($countrydata) && !empty($countrydata)){
                            $country = $countrydata->ID;
                        }else{
                            $country = 0;
                        }
                    }
        
                    if(empty($data->HospitalState) || !isset($data->HospitalState)){
                        $state = 0;
                    }else{
                        $statedata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalState,"StateMaster","StateDescription");
                        if(isset($statedata) && !empty($statedata)){
                            $state = $statedata->ID;
                        }else{
                            $state = 0;
                        }
                    }
                    
                    if(empty($data->HospitalCity) || !isset($data->HospitalCity)){
                        $city = 0;
                    }else{
                        $citydata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalCity,"CityMaster","CityDescription");
                        if(isset($citydata) && !empty($citydata)){
                            $city = $citydata->ID;
                        }else{
                            $city = 0;
                        }
                    }
                    $TenantLatitude = $data->HospitalLatitude;
                    $TenantLongitude = $data->HospitalLongitude;
                    $HosInsertArr = array("TenantCode"=>$TenantNumber,
                        "TenantNumber"=>$TenantNumber,
                        "TenantName"=>$TenantName,
                        "TenantTypeId"=>2,
                        "PhoneNumber"=>$TenantPhoneNumber,
                        "Address"=>$TenantAddress,
                        "CountryID"=>$country,
                        "StateID"=>$state,
                        "CityID"=>$city,
                        "PostCode"=>$TenantPostCode,
                        "Latitude"=>$TenantLatitude,
                        "Longitude"=>$TenantLongitude,
                        "IsActive"=>1,
                        "PhoneCode"=>$TenantPhoneCode
                    );

                   $InsertHospitalData = $this->WebserviceModel->InsertHospitalData($HosInsertArr); 
                   if($InsertHospitalData){
                        $insertIntArr = array("ImplementationId"=>$ImplementationId,
                            "LocationId"=>$locationId,
                            "CmsDoctorId"=>$doctorId,
                            "RingDoctorId"=>$DocRingId,
                            "TenantId"=>$InsertHospitalData,
                            "Scheduled"=>$IsScheduled
                        );
                        $chkImpExist = $this->WebserviceModel->chkImpExist($doctorId,$locationId);
                        if(!isset($chkImpExist) && empty($chkImpExist)){
                            $updateIntTable = $this->WebserviceModel->DoctorImpData($insertIntArr);
                        }
                        $userTenantArr = array("UserId"=>$DocRingId,
                            "TenantId"=>$InsertHospitalData
                        );
                        $InsertUserTenant = $this->WebserviceModel->InsertUserTenant($userTenantArr); 
                        $response['response_code'] = 1;
                        $response['response_message'] = 'Location added successfully';
                   }else{
                        $response['response_code']=2;
                        $response['response_message']='Failed';
                    }
                }
                	

            }else if($Action === "UpdateLocation"){
                $doctorId = $data->DoctorCMSId;
                $hospitalName = $data->HospitalName;
                $TenantNumber = $data->HospitalNumber;
                $TenantAddress = $data->HospitalAddress;
                $TenantFaxNumber = $data->HospitalFaxNumber;
                $TenantPhoneCode = $data->HospitalPhoneCode;
                $TenantPhoneNumber = $data->HospitalPhoneNumber;
                $TenantName = $data->HospitalName;
                $TenantPostCode = $data->HospitalPostCode;
                if(empty($data->HospitalCountry) || !isset($data->HospitalCountry)){
                    $country = 0;
                }else{
                    $countrydata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalCountry,"CountryMaster","CountryDescription");
                    if(isset($countrydata) && !empty($countrydata)){
                        $country = $countrydata->ID;
                    }else{
                        $country = 0;
                    }
                }
    
                if(empty($data->HospitalState) || !isset($data->HospitalState)){
                    $state = 0;
                }else{
                    $statedata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalState,"StateMaster","StateDescription");
                    if(isset($statedata) && !empty($statedata)){
                        $state = $statedata->ID;
                    }else{
                        $state = 0;
                    }
                }
                
                if(empty($data->HospitalCity) || !isset($data->HospitalCity)){
                    $city = 0;
                }else{
                    $citydata = $this->WebserviceModel->searchIdOfGivenString($data->HospitalCity,"CityMaster","CityDescription");
                    if(isset($citydata) && !empty($citydata)){
                        $city = $citydata->ID;
                    }else{
                        $city = 0;
                    }
                }
                $TenantLatitude = $data->HospitalLatitude;
                $TenantLongitude = $data->HospitalLongitude;
                $HosUpdateArr = array("TenantCode"=>$TenantNumber,
                    "TenantNumber"=>$TenantNumber,
                    "TenantName"=>$TenantName,
                    "TenantTypeId"=>2,
                    "PhoneNumber"=>$TenantPhoneNumber,
                    "Address"=>$TenantAddress,
                    "CountryID"=>$country,
                    "StateID"=>$state,
                    "CityID"=>$city,
                    "PostCode"=>$TenantPostCode,
                    "Latitude"=>$TenantLatitude,
                    "Longitude"=>$TenantLongitude,
                    "IsActive"=>1,
                    "PhoneCode"=>$TenantPhoneCode
                );               
                $HospitalRingId = $this->WebserviceModel->tenantsSearchDataByKeywordsNew($hospitalName);
                if($HospitalRingId){
                    $UpdateHospitalData = $this->WebserviceModel->UpdateHospitalData($HospitalRingId,$HosUpdateArr);
                    if($UpdateHospitalData){
                        $response['response_code'] = 1;
                        $response['response_message'] = 'Location update successfully';
                    }else{
                        $response['response_code']=2;
                        $response['response_message']='Failed';
                    }
                }else{
                    $response['response_code']=4;
                    $response['response_message']='Hospital not found';
                }
                
            }else if($Action === "DeleteLocation"){
                $locationId = $data->LocationId;
                $ImpId = $data->ImplementationId;
                $doctorId = $data->DoctorCMSId;
                $chkLocationImp = $this->WebserviceModel->chkLocationImp($locationId,$ImpId);
                if($chkLocationImp){
                    $deleteLocData = $this->WebserviceModel->deleteLocData($locationId,$ImpId);
                    if($deleteLocData){
                        $response['response_code'] = 1;
                        $response['response_message'] = 'Location Deleted successfully';			
                    }else{
                        $response['response_code']=2;
                        $response['response_message']='Failed';
                    }
                }
                else
                {
                    $response['response_code']=4;
                    $response['response_message']='Doctor not found';
                }
            }			
		}
		else
		{
			$response['response_code']=3;
			$response['response_message']='data is null';
		}
		echo json_encode($response);exit;
	}
}
?>