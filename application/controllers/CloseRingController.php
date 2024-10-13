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
            $firstname = $data->firstname;
            $lastname = $data->lastname;       
            $fullName = $firstname." ".$lastname;
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
                                    "GenderId"=>$gender,
                                    "InsertDate"=>date("Y-m-d H:i:s")                                    
                                    );
            $checkData = $this->CRModel->checkExistUser($mob_code,$mobile_number,$email);
            $getRingGroupID = $this->CRModel->getRingGroupID($ringGrp);
            if($getRingGroupID){
                $ringGroupID = $getRingGroupID->RingGroupMasterId;
            }else{
                $ringGroupID = 3;
            }
            if($checkData){
                $insertArr = array( 
                    "UserId" => $checkData->PatientId,
                    "RingGroup" => $ringGroup,
                    "RingGroupId" => $ringGroupID,
                    "UpdateDate" => date("Y-m-d H:i:s")                                    
                );  
                $insert = $this->CRModel->insertData($insertArr,"CloseRingUser");  
                $rngArr = array( 
                    "DoctorID" => 1,
                    "PatientID" => $checkData->PatientId,
                    "RingGroupID" => $ringGroupID,
                    "TenantID" => 9                                    
                );
                $ringGrpUpdateM_M_Table = $this->CRModel->insertData($rngArr,"MRDT_Mappings");   
            }
            else
            {
                $getData = $this->CRModel->userRegistration($saveDataArray);
                $insertArr = array( 
                    "UserId" => $getData,
                    "RingGroup" => $ringGroup,
                    "RingGroupId" => $ringGroupID,
                    "UpdateDate" => date("Y-m-d H:i:s")                                    
                );  
                $insert = $this->CRModel->insertData($insertArr,"CloseRingUser");
                $rngArr = array( 
                    "DoctorID" => 1,
                    "PatientID" => $getData,
                    "RingGroupID" => $ringGroupID,
                    "TenantID" => 9                                    
                );
                $ringGrpUpdateM_M_Table = $this->CRModel->insertData($rngArr,"MRDT_Mappings");
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
        $url = 'https://apiuat.ring.healthcare/api/Register/EncryptDecrypt';
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
            $deviceToken = $data->deviceToken;
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
                    /**clear same device id data */
                    $chkSameDevice = $this->CRModel->chkSameDevice($deviceId);
                    if($chkSameDevice){
                        $clearDevice = $this->CRModel->clearDeviceId($chkSameDevice->UniversalID);
                    }
                    /**************************** */
                    $updateDeviceToken = $this->CRModel->updateCRtoken($chkCRUser->UniversalID,$deviceId,$platform,$deviceToken);
                }
            }           
            if($updateDeviceToken == true){
                $result = $this->CRModel->selectCRUserData($chkCRUser->UniversalID);
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
            $getUseridByUniversal = $this->CRModel->getUseridByUniversal($UniversalID);
            $UniVersalPatId = $getUseridByUniversal->UserId;
            // $UserId = $data->UserId;
            $UserId = $UniVersalPatId;
            $UserType = $data->userType;
            $AddReferral = $data->AddReferral;
            $Category = $data->Category;
            // $DisplayPatientId = $data->DisplayPatientId;
            $DisplayPatientId = $UniVersalPatId;
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
            $getUseridByUniversal = $this->CRModel->getUseridByUniversal($UniversalID);
            $UniVersalPatId = $getUseridByUniversal->UserId;
            
            $jsonArr = array(
                "CR_Report_ID" => $data->CR_Report_ID,
                "Category" => $data->Category,
                "UserId" => $data->UserId,
                // "DisplayPatientId" => $data->DisplayPatientId,
                "DisplayPatientId" => $UniVersalPatId,
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
     
    public function autoLoginForCRInSameDevice(){
        $data = json_decode(file_get_contents('php://input'));
        if($data)
        {   
            $deviceId = $data->deviceId;	
            $source = $data->source;
            $deviceTok = $data->deviceTok;
            $notificationUserType = 0;   
            $checkCRDevice = $this->CRModel->checkCRLoginDevice($deviceId,$source);
            $checkuserid = $this->WebserviceModel->checkuserid($checkCRDevice->UserId,$notificationUserType);

            if($checkuserid){
                $updateDeviceToken = $this->WebserviceModel->updateDeviceToken($checkCRDevice->UserId,$deviceTok,$source);
            }else{

                $saveDataArray = array( 
                    "PatientId"=> $checkCRDevice->UserId,
                    "DeviceId"=>$deviceTok,
                    "Platform"=>$source,
                    );

               //     print_r( $saveDataArray);
                $insertDeviceToken = $this->WebserviceModel->insertDeviceToken($saveDataArray);
            }
        //   print_r($checkCRDevice);exit;
            if($checkCRDevice){
                $UserData = $this->CRModel->getUserData($checkCRDevice->UserId);
                $UserData->MobileNumber = $this->encryptDecrypt("dc",$UserData->MobileNumber);
                $UserData->FullName = $this->encryptDecrypt("dc",$UserData->FullName);
                $UserData->Email = $this->encryptDecrypt("dc",$UserData->Email);
                $UserSettings = $this->WebserviceModel->getUserSettings($checkCRDevice->UserId,"Patient");
                $kunci = $this->config->item('thekey');
                $token['id'] = $checkCRDevice->UserId; 
                $token['data'] = $UserData;
                $date1 = new DateTime();
                $token['iat'] = $date1->getTimestamp();
                $token['exp'] = $date1->getTimestamp() + 60 * 60 * 5;
                $output['token'] = JWT::encode($token, $kunci);
                $UserData->UniversalID = $checkCRDevice->UniversalID;
                $UserData->CRDeviceToken = $checkCRDevice->DeviceToken;

                $response['response_code']=1;
                $response['response_message']='Success';
                $response['is_doctor']=0;
                $response['data']=$UserData;
                $response['token']=$output['token'];
                $response['UserSettings']=isset($UserSettings)?$UserSettings:"";
                $response['BackupDataNew']=isset($BackupDataNew)?$BackupDataNew:"";
                
            }else{
                $response['response_code']=2;
                $response['response_message']='Failled.Close Ring User Not Found';
            }          
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;     
    }

    public function fetchReportAndFileDataForSync(){
        // echo 123456;exit;
        $data = json_decode(file_get_contents('php://input'));       
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UniversalID = $data->UniversalID;
            $resultArr = array();
            $getReportData = $this->CRModel->fetchReportDataForSync($UniversalID);
            // print_r($result);exit;
            if(isset($getReportData) && !empty($getReportData)){
                foreach($getReportData as $val){
                    $report = file_get_contents($root.$val->ReportRef);
                    $repArray = json_decode($report,true);
                    if(isset($repArray[0]->Data2) && !empty($repArray[0]->Data2)){
                        $data2 = file_get_contents($repArray[0]->Data2);
                        $repArray[0]->Data2 = $data2;
                    }
                    
                    $Files = array();
                    $getFileData = $this->CRModel->fetchFileDataForSyncByReportId($val->ID);
                    if($getFileData){
                        foreach($getFileData as $fileVal){
                            $file = file_get_contents($root.$fileVal->FileRef);
                            $fileArray = json_decode($file,true);
                            // print_r($fileArray);exit;  
                            if(isset($fileArray[0]["Base64Data"]) && !empty($fileArray[0]["Base64Data"])){
                                $Base64Data = file_get_contents($fileArray[0]["Base64Data"]);
                                $fileArray[0]["Base64Data"] = $Base64Data;
                            }
                            array_push($Files,$fileArray[0]);
                        }
                        // print_r($Files);exit;
                    }
                    // print_r($repArray);exit;
                    $repArray[0]["filesData"] = $Files;
                    array_push($resultArr,$repArray[0]);

                }
            }

            if($resultArr)             
            {
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
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;     
    }

    function android_notification_for_data_sync(){
        $data = json_decode(file_get_contents('php://input'));
        // print_r($data);exit;
        $UniversalID = $data->UniversalID;
        $patientData = $this->CRModel->selectCRUserData($UniversalID);
        if($patientData){
            $PatientId = $patientData->UserId;
            $notification_body = "Request for sync data.";
            $token = isset($patientData->DeviceToken)?$patientData->DeviceToken:0;
            $usertoken = $token;
            $message = array(
                        'title' => 'Request for sync data', 
                        'body' => $notification_body, 
                        'sound' => 'default', 
                        'badge' => '1',
                        'click_action'=>'FCM_PLUGIN_ACTIVITY', //For only Android App
                        'notifictionType' => 'Sync'
                    );      
            $url = "https://fcm.googleapis.com/fcm/send";
            $serverKey = 'AAAAmljYfCA:APA91bEKpAPt9sImLJOFBDDr-56nMtF9uY-PXu9MVXCpJy3mU5eN2AxBBnLtRS94n62CMq9dGB3jMt_Ar9a-RaHxZVQKBeDOfhiNZsLcuMDDyE_XUoFnMs2DZU1-piT0PvSIkVLSx-Vg'; // For KPJ Ring
            $notification = $message;
            $data = array('extraInfo'=> 'DomingoMG','notifictionType' => 'Sync');      
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
        }else{
            echo json_encode(array("msg"=>"Patient not found")); exit;
        }
         
    }
}