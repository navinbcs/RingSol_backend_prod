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
            $firstname = $data->firstname;     
            // $lastname = $data->lastname;       
            //$fullName = $firstname." ".$lastname;
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

        $BloodGroupId = $data->bloodgroup;        
        $mn = $data->mobile_number * 1;
        $mobile_number = $this->encryptDecrypt("en",$mn);
        $firstname = $data->firstname;     
            // $lastname = $data->lastname;       
            //$fullName = $firstname." ".$lastname;
        $lastname =$this->encryptDecrypt("en",$data->lastname);
        $fullName = $firstname;
        $full_name = $this->encryptDecrypt("en",$fullName);
        $email = $this->encryptDecrypt("en",$data->email);
        $gender = isset($data->gender)?$data->gender:NULL;
        $country = isset($data->country_id)?$data->country_id:NULL;
        $state = isset($data->state_id)?$data->state_id:NULL;
        $city = isset($data->city_id)?$data->city_id:NULL;
        $saveDataArray = array(                                 
                            "MobileNumber"=>$mobile_number,
                            "FullName"=>$full_name,
                            "LastName"=>$lastname,
                            "Email"=>$email,
                            "MobileCode"=>$data->mob_code,
                            "CountryMasterId"=>$country,
                            "StateMasterId"=>$state,
                            "CityMasterId"=>$city,
                            "Address"=>urlencode($data->address),
                            "BloodGroupId"=>$BloodGroupId,
                            "GenderId"=>$gender,
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

    public function getGenderMasterData(){ 
        $genderData =  $this->WebserviceModel->getGenderMasterData();                                         
        if($genderData)             
        {
            $response['response_code']=1;
            $response['response_message']='Success';
            $response['user_data']=$genderData;
        }
        else 
        {
            $response["response_code"] = 2;
            $response["response_message"] = "Failed";
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
            $firstname = $data->firstname;     
            // $lastname = $data->lastname;       
            //$fullName = $firstname." ".$lastname;
            $lastname =$this->encryptDecrypt("en",$data->lastname);
            $fullName = $firstname;
            $full_name = $this->encryptDecrypt("en",$fullName);
            $gender = isset($data->gender)?$data->gender:NULL;
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
                                    "FullName"=>$full_name,
                                    "LastName"=>$lastname,
                                    "Email"=>$email,
                                    "MobileCode"=>$mobCode,
                                    "BloodGroupId"=>$BloodGroupId,
                                    "Address"=>$address,
                                    "InsertDate"=>date("Y-m-d H:i:s"),
									"CountryMasterId"=>$country,
									"StateMasterId"=>$state,
									"CityMasterId"=>$city,
                                    "DateOfBirth"=>$dob1,
                                    "GenderId"=>$gender,
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
            $dob = isset($data->DOB)?$data->DOB:NULL;
            if(isset($dob) && !empty($dob)){
                $ts = strtotime($dob);
                $dob1 = date("Y-m-d H:i:s", $ts);
            }else{
                $dob1 = NULL;
            }    
            $firstname = $data->firstname;     
            // $lastname = $data->lastname;       
            //$fullName = $firstname." ".$lastname;
            $lastname =$this->encryptDecrypt("en",$data->lastname);
            $fullName = $firstname;
            $full_name = $this->encryptDecrypt("en",$fullName);
            $email = $this->encryptDecrypt("en",$data->email);
            $gender = isset($data->gender)?$data->gender:NULL;
            $updateArray = array( 
                                    "FullName"=>$full_name,
                                    "LastName"=>$lastname,
                                    "Email"=>$email,
                                    "BloodGroupId"=>$BloodGroupId,
                                    "Address"=>$address,
                                    "InsertDate"=>date("Y-m-d H:i:s"),
									"CountryMasterId"=>$country,
									"StateMasterId"=>$state,
									"CityMasterId"=>$city,
                                    "DateOfBirth"=>$dob1,
                                    "GenderId"=>$gender,
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

    public function getDependentProfile(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
			$dependentPatientId = $data->DependentProfilePatientId;
			$DependentProfile = $this->WebserviceModel->getDependentProfile($dependentPatientId);
			$DependentProfile->MobileNumber = $this->encryptDecrypt("dc",$DependentProfile->MobileNumber);   
            // $name = $this->encryptDecrypt("dc",$DependentProfile->FullName);
            // $NameArr = explode(" ",$name);
            // $DependentProfile->FirstName = isset($NameArr[0])?$NameArr[0]:"";
            // $DependentProfile->LastName = isset($NameArr[1])?$NameArr[1]:"";
            $DependentProfile->FirstName = $this->encryptDecrypt("dc",$DependentProfile->FullName);
            $DependentProfile->LastName = $this->encryptDecrypt("dc",$DependentProfile->LastName);
            // $DependentProfile->FullName = $this->encryptDecrypt("dc",$DependentProfile->FullName);
            $DependentProfile->FullName = $DependentProfile->FirstName." ".$DependentProfile->LastName;
            $DependentProfile->Email = $this->encryptDecrypt("dc",$DependentProfile->Email);			
			$DependentProfile->MobileCode = str_replace(" ", "", $DependentProfile->MobileCode);
			$DependentProfile->Address = urldecode($DependentProfile->Address);
            if(isset($DependentProfile->DateOfBirth) && !empty($DependentProfile->DateOfBirth)){
                $DependentProfile->DateOfBirth = $DependentProfile->DateOfBirth;
            }else{
                $DependentProfile->DateOfBirth = "2000-01-01 00:00:00.000";
            }
            $mobileCo = trim($DependentProfile->MobileCode);
            $getContCode = $this->WebserviceModel->getCountryFlagAndCode($mobileCo);
            if($getContCode){
                $DependentProfile->CountryCode = $getContCode->CountryCode;
            }else{
                 $DependentProfile->CountryCode = "";
            }
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

    public function check_commentary_notification()
    {   
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);     
        $runningCommentry = $this->WebserviceModel->getRunningCommentry();
        if($runningCommentry){
            $response['response_code'] = 7;
            $response['response_message'] = 'Success';
            $response['response_data'] = $runningCommentry;
        }
        else
        {
            $response['response_code'] = 2;
            $response['response_message'] = 'Failed';
        }    
		echo json_encode($response); exit;
    }

    public function updateEreferralFormInReportTable(){
        $data = json_decode(file_get_contents('php://input'));   
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);      
        if($data)
        {
		$patientId = $data->patientId;
            $reportTransitId = $data->reportTransitId;
            $updateArr = array("IsReferralFormProcessed"=> 1);
			$Update = $this->WebserviceModel->updateEreferralFormInReportTable($patientId,$reportTransitId,$updateArr);
			if($Update == true){
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
            // $userData->FirstName = isset($NameArr[0])?$NameArr[0]:"";
            // $userData->LastName = isset($NameArr[1])?$NameArr[1]:"";
            // $userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
            $userData->FirstName = $this->encryptDecrypt("dc",$userData->FullName);
            $userData->LastName = $this->encryptDecrypt("dc",$userData->LastName);
            $userData->FullName = $userData->FirstName." ". $userData->LastName;
            $userData->Email = $this->encryptDecrypt("dc",$userData->Email);
			$userData->Address = urldecode($userData->Address);
            if(isset($userData->DateOfBirth) && !empty($userData->DateOfBirth)){
                $userData->DateOfBirth = $userData->DateOfBirth;
            }else{
                $userData->DateOfBirth = "2000-01-01 00:00:00.000";
            }
            $mobileCo = trim($userData->MobileCode);
            $getContCode = $this->WebserviceModel->getCountryFlagAndCode($mobileCo);
            if($getContCode){
                $userData->CountryCode = $getContCode->CountryCode;
            }else{
                 $userData->CountryCode = "";
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
        }
        else
        {
            $response['response_code'] = 4;
            $response['response_message'] = 'JWT Token Error';
        }    
        echo json_encode($response); exit;
    }

    public function check_user_age()
    {   
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $patientId = $data->patientId;
            $getAge = $this->WebserviceModel->getDateOfBirthOfPatient($patientId);
            // print_r($getAge);exit;
            if($getAge){
                $dob = new DateTime($getAge->DateOfBirth);
                $today   = new DateTime('today');
                $year = $dob->diff($today)->y;
                $month = $dob->diff($today)->m;
                $day = $dob->diff($today)->d;
                if( strtotime($getAge->DateOfBirth) < (time() - (18 * 60 * 60 * 24 * 365))) {
                    $valid = 1;
                } else {
                    $valid = 2;
                }
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data'] = $year." year"." ".$month." months ".$day." days";
                $response['is_valid'] = $valid;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }    
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }

    public function chkAvailableFileForProfiles(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $patientId = $data->patientId;
            $resultArr = array();
            $chkAvailable = $this->WebserviceModel->chkAvailableFileForProfiles($patientId);
            // print_r($chkAvailable);exit;
            if(isset($chkAvailable)){
                $data = array("patientId"=>$patientId, "count"=>$chkAvailable->count);
                array_push($resultArr,$data);
            }
            
            $depList = $this->WebserviceModel->getDependentListByPatientId($patientId);
            if($depList){
                // print_r($depList);exit;
                foreach($depList as $dep){
                    $chk = $this->WebserviceModel->chkAvailableFileForProfiles($dep->DependentProfilePatientId);
                    if(isset($chk)){
                        $data1 = array("dependentPatientId"=>$dep->DependentProfilePatientId, "count"=>$chk->count);
                        array_push($resultArr,$data1);
                    }
                }
                
            }
            if($resultArr){
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data'] = $resultArr;
            }
            else
            {
                $response['response_code'] = 2;
                $response['response_message'] = 'Failed';
            }    
		}
		else
		{
			$response['response_code'] = '3';
			$response['response_message'] = 'Data is Null';
		}    
		echo json_encode($response); exit;
    }

    public function getUserDataByIdForAppointment(){   
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {    
            $user_id = $data->user_id;       
            $userData =  $this->WebserviceModel->getUserDataById($user_id);
            $userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);   
            $name = $this->encryptDecrypt("dc",$userData->FullName);
            $NameArr = explode(" ",$name);
            // $userData->FirstName = isset($NameArr[0])?$NameArr[0]:"";
            // $userData->LastName = isset($NameArr[1])?$NameArr[1]:"";
            // $userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
            $userData->FirstName = $this->encryptDecrypt("dc",$userData->FullName);
            $userData->LastName = $this->encryptDecrypt("dc",$userData->LastName);
            $userData->FullName = $userData->FirstName." ". $userData->LastName;
            $userData->Email = $this->encryptDecrypt("dc",$userData->Email);
			$userData->Address = urldecode($userData->Address);
            if(isset($userData->DateOfBirth) && !empty($userData->DateOfBirth)){
                $userData->DateOfBirth = $userData->DateOfBirth;
            }else{
                $userData->DateOfBirth = "2000-01-01 00:00:00.000";
            }
            $mobileCo = trim($userData->MobileCode);
            $getContCode = $this->WebserviceModel->getCountryFlagAndCode($mobileCo);
            if($getContCode){
                $userData->CountryCode = $getContCode->CountryCode;
            }else{
                 $userData->CountryCode = "";
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
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is Null';
        }    
        echo json_encode($response); exit;
    }
}
?>