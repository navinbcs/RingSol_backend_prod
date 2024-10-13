<?php 
// ini_set('memory_limit','128M');
error_reporting(E_ERROR | E_PARSE);
ini_set('memory_limit', '-1');
date_default_timezone_set('Asia/Kolkata');
class HIS_Webservice extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
        $this->load->model(array('HISModel'));
        $config['allowed_types'] = 'pdf|csv';

        $this->load->library('upload', $config);

        $this->upload->initialize($config);
        $this->load->helper('url', 'form');
    }
//*********START: WEBSERVICE FOR LOGIN***********************

    public function Login()
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {
            $userName = $data->username;    
            $password = $data->password;
			$result = $this->HISModel->checkUser($userName,$password);
			 //echo '<pre>';print_r($result);exit;
			if($result)             
			{
				$kunci = $this->config->item('thekey');
				$token['id'] = $result->ID;  
				$token['data'] = $result;
				$date1 = new DateTime();
				$token['iat'] = $date1->getTimestamp();
				$token['exp'] = $date1->getTimestamp() + 60 * 60 * 5; 
				$output['token'] = JWT::encode($token, $kunci); 
				
				$response['response_code']=1;
				$response['response_message']='Success';
				$response['data']=$result;
				$response['token']=$output['token'];
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

	public function facilitySearchByKeywords(){
        $data = json_decode(file_get_contents('php://input'));       
        if($data)
        {
            $keyword = $data->keyword;
            $facilityData = $this->HISModel->facilitySearchByKeywords($keyword);
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
        }
        else
        {
            $response['response_code'] = '3';
            $response['response_message'] = 'Data is Null';
        }
        echo json_encode($response);exit;
    }

    public function getFacilityTypeData(){       
        $facilityTypeData = $this->HISModel->facilityTypeData();
        if($facilityTypeData)
        {
           $response['response_code'] = '1';
           $response['response_message'] = 'Success';
           $response['response_data'] = $facilityTypeData;
        }
        else
        {
           $response['response_code'] = '2';
           $response['response_message'] = 'Failed';
        }
        echo json_encode($response);exit;
    }

    function facilityIntegration(){ 
        $data = json_decode(file_get_contents('php://input'));  
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $tokenData = JWT::decode($token, $kunci);    
        $tokenUser = $tokenData->id;  
        if($data)
        {
            $userName = $data->username;
            $password = $data->Password;
            $facilityName = $data->facilityName;
            $facilityTypeId = $data->facilityTypeId;
            $companyNumber = $data->companyNumber;
            $facilityAddress = $data->facilityAddress;
            $phoneCountryCode = $data->phoneCountryCode;
            $faxCountryCode = $data->faxCountryCode;
            $phoneNumber = $data->phoneNumber;
            $faxNumber = $data->faxNumber;
            $postCode = $data->postCode;
            $countryId = $data->countryId;
            $stateId = $data->stateId;
            $cityId = $data->cityId;

            $facilityId = $this->HISModel->checkHospitalByName($facilityName);
            if($facilityId){
                $insertData = array( 
                    "UserName" => $userName,
                    "Password" => $password,
                    "FacilityId" => $facilityId->TenantId,
                    "CreatedBy" => $tokenUser
                );
                $insertInt = $this->HISModel->insertIntegrationData($insertData);
            }else{
                $facilityInsertArray = array(        
                    "TenantName" => $facilityName,                               
                    "TenantNumber" => $companyNumber,
                    "TenantTypeId" => $facilityTypeId,
                    "PhoneNumber" => $phoneNumber,
                    "FaxNumber" => $faxNumber,
                    "Address" => $facilityAddress,
                    "CountryID" => $countryId,
                    "StateID" => $stateId,
                    "CityID" => $cityId,
                    "PostCode" => $postCode,
                    "IsActive" => 1,
                    "PhoneCode" => $phoneCountryCode,
                    "FaxCode" => $faxCountryCode
                );
                $saveFacility = $this->HISModel->saveFacilityData($facilityInsertArray);
                if($saveFacility){
                    $insertData = array( 
                        "UserName" => $userName,
                        "Password" => $password,
                        "FacilityId" => $saveFacility,
                        "CreatedBy" => $tokenUser
                    );
                    $insertInt = $this->HISModel->insertIntegrationData($insertData);
                }else{
                    $insertInt = "";
                }
                
            }
            if($insertInt)
            {
		$otp = rand(100000, 999999);
                $message = 'The user and the hospital have been successfully integrated. Your OTP for verification is '.$otp.'.';
                $subject = 'HIS To Ring Hospital Integration';
                $sendMail = Utility::callSendMail($userName,$message,$subject);
                $updateOTP = $this->HISModel->updateOtpData($otp,$insertInt);
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $insertData;
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

    public function GetStateMaster(){  
        $data = json_decode(file_get_contents('php://input')); 
        if($data)
        {   
            $countryId = $data->countryId;
            $stateData = $this->HISModel->GetStateMaster($countryId);
            if($stateData)
            {
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
            $response['response_data'] = $stateData;
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

    public function GetCityMaster(){  
        $data = json_decode(file_get_contents('php://input')); 
        if($data)
        {   
            $stateId = $data->stateId;
            $cityData = $this->HISModel->GetCityMaster($stateId);
            if($cityData)
            {
            $response['response_code'] = '1';
            $response['response_message'] = 'Success';
            $response['response_data'] = $cityData;
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

    public function getIntegratedUserList(){    
        $headers = $_SERVER["HTTP_AUTHORIZATION"];
        $token = str_replace("Bearer ", "", $headers);
        $kunci = $this->config->item('thekey');
        $tokenData = JWT::decode($token, $kunci);    
        $tokenUser = $tokenData->id;    
        $userData = $this->HISModel->getIntegratedUserList();
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
        echo json_encode($response);exit;
    }

}