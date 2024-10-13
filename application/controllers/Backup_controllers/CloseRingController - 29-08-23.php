<?php 
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ERROR | E_PARSE);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
class CloseRingController extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
        $this->load->model(array('CRModel','WebserviceModel'));
        $config['allowed_types'] = 'pdf|csv';
        $this->load->library('upload', $config);
        $this->load->library('m_pdf');
        $this->upload->initialize($config);
        $this->load->helper('url', 'form');
    }
//*********START: WEBSERVICE FOR LOGIN***********************


    function userRegistration(){        
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
            $ringGroup = isset($data->ringGroup)?$data->ringGroup:NULL;
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
            $checkData = $this->CRModel->checkExistUser($mob_code,$mobile_number,$email);
            if($checkData){
                $insertArr = array( 
                    "UserId" => $checkData->PatientId,
                    "RingGroup" => $ringGroup,
                    "UpdateDate" => date("Y-m-d H:i:s")                                    
                );  
                $insert = $this->CRModel->insertData($insertArr,"CloseRingUser");     
            }
            else
            {
                $getData = $this->CRModel->userRegistration($saveDataArray);
                $insertArr = array( 
                    "UserId" => $getData,
                    "RingGroup" => $ringGroup,
                    "UpdateDate" => date("Y-m-d H:i:s")                                    
                );  
                $insert = $this->CRModel->insertData($insertArr,"CloseRingUser");
            }
            $returnData = $this->CRModel->selectCRUserData($insert);
            if($returnData)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $returnData;
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

    function encryptDecrypt($type,$name){
        if($type == 'en'){
            $type1 = "Encrypt";
        }else{
            $type1 = "Decrypt";
        }
        $arrayToSend = array('userName'=>$name,'type'=>$type1);
        $url = 'http://sancyberhad.ddns.net/RINGCR_API_TEST/api/Register/EncryptDecrypt';
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

    public function updateCRtoken(){
        // echo 1;exit;
        $data = json_decode(file_get_contents('php://input'));        
        if($data)
        {
            // print_r($data);exit;
            $deviceId = $data->deviceId;
            $platform = $data->platform;
            // $universalId = $data->universalId;
            $mn = $data->mobile_number * 1;
            $mobile_number = $this->encryptDecrypt("en",$mn);
            $mob_code = $data->mob_code;
            $email = $this->encryptDecrypt("en",$data->email);
            $ringGroup = isset($data->ringGroup)?$data->ringGroup:NULL;
            $chkUser = $this->CRModel->checkExistUser($mob_code,$mobile_number,$email); 
            // print_r($chkUser);exit;          
            if($chkUser){
                $chkCRUser = $this->CRModel->chkCRUser($chkUser->PatientId,$ringGroup);
                if($chkCRUser){
                    $updateDeviceToken = $this->CRModel->updateCRtoken($chkCRUser->UniversalID,$deviceId,$platform);
                }
            }           
            if($updateDeviceToken == true){
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['response_data'] = $chkCRUser;
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

    public function saveReportDataOfCR_User(){
        // echo 123456;exit;
        $data = json_decode(file_get_contents('php://input'));       
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UniversalID = $data->UniversalID;
            $UserId = $data->UserId;
            $UserType = $data->userType;
            $AddReferral = $data->AddReferral;
            $Category = $data->Category;
            $DisplayPatientId = $data->DisplayPatientId;
            $DoctorId = $data->DoctorId;
            $DoctorPhoneNumber = $data->DoctorPhoneNumber;
            $EreportsTransitDetailId = $data->EreportsTransitDetailId;
			$InsertDate = str_replace('/', '-', $data->InsertDate);
			$InsertDate1 = date('Y-m-d h:i:s', strtotime($InsertDate));
            $PhoneNumber = $data->PhoneNumber;
            $ReportTransitId = $data->ReportTransitId;
            $ReportUploadType = $data->ReportUploadType;
            if(isset($data->RingGroupMasterId)){
                $RingGrpId = (int)$data->RingGroupMasterId;
            }else{
                $RingGrpId = "";
            }
            $TenantAddress = $data->TenantAddress;
            $TenantFaxNumber = $data->TenantFaxNumber;
            $TenantName = $data->TenantName;
            $TenantPhoneNumber = $data->TenantPhoneNumber;
            $createTime = $data->createTime;
            if($ReportUploadType == "local"){
                /******create file text*********/
                $folderName = "upload/CR_User_Report_Files";      
                $TextFileName = $folderName."/Local_".$UniversalID."_".time().".txt";    
                file_put_contents($TextFileName, $data->data2);
                $data2 = $TextFileName;
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
            $refTenantPhoneNumber = $data->refTenantPhoneNumber;
            $refUserPhoneNumber = $data->refUserPhoneNumber;
            $remarks = $data->remarks;
            $sync = $data->sync;

            $DiagnosisRef = isset($data->DiagnosisRef)?$data->DiagnosisRef:"";
            $DoctorSpeciality = isset($data->DoctorSpeciality)?$data->DoctorSpeciality:"";
            $EReferralStatus = isset($data->EReferralStatus)?$data->EReferralStatus:"";
            $EreferralForm = isset($data->EreferralForm)?$data->EreferralForm:"";
            $RingGroupMasterIdReff = isset($data->RingGroupMasterIdReff)?$data->RingGroupMasterIdReff:"";
            $Treatment = isset($data->Treatment)?$data->Treatment:"";
            $VisitNotes = isset($data->VisitNotes)?$data->VisitNotes:"";
            $WorkingSchedule = isset($data->WorkingSchedule)?$data->WorkingSchedule:"";
            $WorkingScheduleref = isset($data->WorkingScheduleref)?$data->WorkingScheduleref:"";
            $jsonArr = array(
                "UniversalID" => $UniversalID,
                "UserId" => $UserId,
                "AddReferral" => $AddReferral,
                "Category" => $Category,
                "DisplayPatientId" => $DisplayPatientId,
                "DoctorId" => $DoctorId,
                "DoctorPhoneNumber" => $DoctorPhoneNumber,
                "EreportsTransitDetailId" => $EreportsTransitDetailId,
                "InsertDate" => $InsertDate1,
                "PhoneNumber" => $PhoneNumber,
                "ReportTransitId" => $ReportTransitId,
                "ReportUploadType" => $ReportUploadType,
                "RingGroupMasterId" => $RingGrpId,
                "TenantAddress" => $TenantAddress,
                "TenantFaxNumber" => $TenantFaxNumber,
                "TenantName" => $TenantName,
                "TenantPhoneNumber" => $TenantPhoneNumber,
                "createTime" => $createTime,
                "data2" => $data2,
                "description" => $description,
                "diagnosis" => $diagnosis,
                "doctorName" => $doctorName,
                "Isdoctor" => $isdoctor,
                "filetype" => $filetype,
                "NativeURL" => $nativeURL,
                "refDoctorName" => $refDoctorName,
                "refTenantAddress" => $refTenantAddress,
                "refTenantFaxNumber"=>$refTenantFaxNumber,
                "refTenantName" => $refTenantName,
                "refTenantPhoneNumber" => $refTenantPhoneNumber,
                "refUserPhoneNumber" => $refUserPhoneNumber,
                "remarks" => $remarks,
                "sync" => $sync,
                "DiagnosisRef" => $DiagnosisRef,
                "DoctorSpeciality" => $DoctorSpeciality,
                "EReferralStatus" => $EReferralStatus,
                "EreferralForm" => $EreferralForm,
                "RingGroupMasterIdReff" => $RingGroupMasterIdReff,
                "Treatment" => $Treatment,
                "VisitNotes" => $VisitNotes,
                "WorkingSchedule" => $WorkingSchedule,
                "WorkingScheduleref" => $WorkingScheduleref
            );
            $folderName = "upload/CR_User_Report_Files/";
            $filename = "CR_".$UniversalID."_".time()."_report.json";
            $jsonFileName = $folderName.$filename;        
            $blankArray = array();
            array_push($blankArray, $jsonArr);
            $json = json_encode($blankArray);
            $result = file_put_contents($jsonFileName, $json);  
            if($result){
                $insertArr = array(
                    "UniversalID" => $UniversalID,
                    "ReportRef" => $jsonFileName
                );
                $insert = $this->CRModel->insertData($insertArr,"CloseRingReports");
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['response_data'] = $jsonFileName;
                $response['CR_Report_ID'] = $insert;
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

    public function saveFilesDetailsOfCR_User(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {   
            $UniversalID = $data->UniversalID;
			/******create file text*********/
			$folderName = "upload/CR_User_Report_Files/";      
			$TextFileName = $folderName."/".$UniversalID."_".time().".txt";    
			file_put_contents($TextFileName, $data->base64Data);
			$base64Data = $TextFileName;
            $basedata = str_replace(' ', '%20', $data->basedata);

            $jsonArr = array(
                "CR_Report_ID" => $data->CR_Report_ID,
                "Category" => $data->Category,
                "UserId" => $data->UserId,
                "DisplayPatientId" => $data->DisplayPatientId,
                "EreportsTransitDetailId" => $data->EreportsTransitDetailId,
                "FileAttachments" => $data->FileAttachments,
                "PhoneNumber" => $data->PhoneNumber,
                "ReportTransitId" => $data->ReportTransitId,
                "Basedata" => $basedata,
                "Base64Data" => $base64Data,
                "ShowreferalIcon" => $data->showreferalIcon,
                "Sync_status" => $data->sync_status,
                "FileType" => isset($data->fileType)?$data->fileType:'',
            );
            
            $filename = "CR_".$UniversalID."_".time()."_File.json";
            $jsonFileName = $folderName.$filename;        
            $blankArray = array();
            array_push($blankArray, $jsonArr);
            $json = json_encode($blankArray);
            $result = file_put_contents($jsonFileName, $json);
            if($result)             
            {
                $insertArr = array(
                    "UniversalID" => $UniversalID,
                    "CRReportId" => $data->CR_Report_ID,
                    "FileRef" => $jsonFileName
                );
                $insert = $this->CRModel->insertData($insertArr,"CloseRingFiles");
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['CR_File_ID'] = $insert;
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
        
}