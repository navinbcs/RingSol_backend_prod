<?php 
date_default_timezone_set('Asia/Kolkata');
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
class Backupservice extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
        // $this->load->model(array('WebserviceModel'));
        $config['allowed_types'] = 'pdf|csv';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->load->helper('url', 'form');
    }
/*********START: WEBSERVICE FOR BACKUP DIRECT FROM DEVICE*************/

    public function BakupDataInJsonDirectFromDevice_OLD(){
        // echo 123456;exit;
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
            $TenantPhoneNumber = $data->TenantPhoneNumber;
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
            $refTenantPhoneNumber = $data->refTenantPhoneNumber;
            $refUserPhoneNumber = $data->refUserPhoneNumber;
            $remarks = $data->remarks;
            $sync = $data->sync;
            $uniqueId = $data->uniqueId;
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
                        "TenantPhoneNumber" => $TenantPhoneNumber,
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
                        "refTenantPhoneNumber" => $refTenantPhoneNumber,
                        "refUserPhoneNumber" => $refUserPhoneNumber,
                        "remarks" => $remarks,
                        "sync" => $sync,
                        "UploadedFileRef" => $UploadedFileRef,
                    );
            /******Make User Folder*********/
            $folderName = "upload/Ring_".$UserId."_Backup";
            if(!is_dir($folderName))
            {
                mkdir($folderName, 0777);
            }        
            $jsonFileName = $folderName."/P_".$UserId."_".$uniqueId."_Backup.json";        
            if(is_file($jsonFileName)){
                $jsonFile = $jsonFileName;
                $inp = file_get_contents($jsonFile);
                $tempArray = json_decode($inp,true);
                // print_r($tempArray);exit;
                array_push($tempArray, $jsonArr);
                $jsonData = json_encode($tempArray);
                $result = file_put_contents($jsonFile, $jsonData);

            }else{
                array_push($blankArray, $jsonArr);
                $json = json_encode($blankArray);
                $jsonFileName1 = $jsonFileName;
                $result = file_put_contents($jsonFileName1, $json);                              
            }  
            
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['json_file'] = $jsonFileName;
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

    

    public function sendZip()
    {
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->userId;
            $uniqueId = $data->uniqueId;
            $UserEmail = $data->UserEmail;
            $mailSub = $data->mailSub;
            $mailBody = $data->mailBody;

        /**Find Report Json And Files Json */
            $jsonFileName = "upload/Ring_".$UserId."_Backup/P_".$UserId."_".$uniqueId."_Backup.json";
            if(is_file($jsonFileName))
            {
                $reportJson = $jsonFileName;
                $inp = file_get_contents($reportJson);
                $tempArray = json_decode($inp,true);
                $blankArr = array();
                
                foreach($tempArray as $field) {
                    if(isset($field['UploadedFileRef']) && !empty($field['UploadedFileRef'])){
                        foreach($field['UploadedFileRef'] as $fileVal) {
                            // print_r($fileVal); echo "----";
                            array_push($blankArr,"upload/Ring_".$UserId."_Backup/".$fileVal.".json");
                        }
                               
                    }
                }
                // exit;
                array_push($blankArr,$reportJson);
                if(!empty($blankArr))
                {
                    /**Create Folder******************************/
                    $date = date("d-m-Y");
                    // $folderName = "upload/Ring_".$UserId."_Backup";
                    $folderName = "upload/Ring_".$UserId."_Backup/Ring_".$UserId."_".$date."_Backup";
                    if(!is_dir($folderName))
                    {
                        mkdir($folderName, 0777);
                        foreach($blankArr as $value){
                            $fileN = explode('/',$value);
                            copy($value, $folderName.'/'.$fileN[2]);
                        }
                    }else{
                        array_map("unlink", glob("$folderName/*"));
                        array_map("rmdir", glob("$folderName/*")); 
                        $dltFol = rmdir($folderName);
                        if($dltFol){
                            mkdir($folderName, 0777);
                            foreach($blankArr as $value){
                                $fileN = explode('/',$value);
                                copy($value, $folderName.'/'.$fileN[2]);
                            }
                        }
                        
                    }                      
                    /**Create Zip Folder ***************/
                    $temp_unzip_path = $folderName.'/';
                    $zip = new ZipArchive();
                    $dirArray = array();
                    $new_zip_file = "upload/Ring_".$UserId."_Backup/Ring_".$UserId."_".$date."_Backup.zip";

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

    public function BakupDataInJsonDirectFromDevice(){
        // echo 123456;exit;
        $data = json_decode(file_get_contents('php://input'));       
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->UserId;
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
            $TenantPhoneNumber = $data->TenantPhoneNumber;
            $createTime = $data->createTime;
            if($ReportUploadType == "local"){
                /******check User Folder and create file text*********/
                $folderName = "upload/Ring_".$UserId."_Backup";
                if(!is_dir($folderName))
                {
                    mkdir($folderName, 0777);
                }
                // else{
                //     array_map("unlink", glob("$folderName/*"));
                //     array_map("rmdir", glob("$folderName/*")); 
                //     $deleteFol = rmdir($folderName);
                //     if($deleteFol){
                //         mkdir($folderName, 0777);
                //     }              
                // }        
                $TextFileName = $root.$folderName."/Local_".$UserId."_".time().".txt";    
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

            $uniqueId = $data->uniqueId;
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
                        "TenantPhoneNumber" => $TenantPhoneNumber,
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
                        "UploadedFileRef" => $UploadedFileRef,
                    );
            /******Make User Folder*********/
            $folderName = "upload/Ring_".$UserId."_Backup";
            if(!is_dir($folderName))
            {
                mkdir($folderName, 0777);
            }
            // else{
            //     array_map("unlink", glob("$folderName/*"));
            //     array_map("rmdir", glob("$folderName/*")); 
            //     $deleteFol = rmdir($folderName);
            //     if($deleteFol){
            //         mkdir($folderName, 0777);
            //     }              
            // }        
            // $jsonFileName = $folderName."/P_".$UserId."_".$uniqueId."_Backup.json";  
            $jsonFileName = $folderName."/P_".$UserId."_Backup.json";      
            if(is_file($jsonFileName)){
                $jsonFile = $jsonFileName;
                $inp = file_get_contents($jsonFile);
                $tempArray = json_decode($inp,true);
                // print_r($tempArray);exit;
                array_push($tempArray, $jsonArr);
                $jsonData = json_encode($tempArray);
                $result = file_put_contents($jsonFile, $jsonData);

            }else{
                array_push($blankArray, $jsonArr);
                $json = json_encode($blankArray);
                $jsonFileName1 = $jsonFileName;
                $result = file_put_contents($jsonFileName1, $json);                              
            }  
            
            if($result)             
            {
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['json_file'] = $jsonFileName;
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

    public function saveFileAttachmentDetails(){
        $data = json_decode(file_get_contents('php://input'));       
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {   
            $UserId = $data->UserId;
            $jsonPathFile = $data->jsonPathFile;
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
            $jsonFileName = "upload/Ring_".$UserId."_Backup/".$uniqueNumber.".json";

            $result = file_put_contents($jsonFileName, $json);
            if($result)             
            {
                $this->updateBackupJsonPathFile($UserId,$data->UserType,$data->ReportTransitId,$uniqueNumber,$jsonPathFile);
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['file_json'] = $root.$jsonFileName;
                $response['file_json_ref'] = $uniqueNumber;
                $response['jsonPathFile'] = $jsonPathFile;
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

    function updateBackupJsonPathFile($UserId,$UserType,$ReportTransitId,$fileRef,$jsonPathFile){
        
        if(is_file($jsonPathFile)){
            $jsonFile = $jsonPathFile;
            $inp = file_get_contents($jsonFile);
            $tempArray = json_decode($inp,true);
            $blankArr = array();
            foreach($tempArray as $field) {
                if($field['ReportTransitId'] == $ReportTransitId){
                    array_push($field['UploadedFileRef'],$fileRef);
                    array_push($blankArr,$field);        
                }else{
                    array_push($blankArr,$field); 
                }
            }
            $jsonData = json_encode($blankArr);
            $result = file_put_contents($jsonFile, $jsonData);
        }
    }

    public function sendReportJsonAndZip()
    {
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->userId;
            $uniqueId = $data->uniqueId;
            $UserEmail = $data->UserEmail;
            $mailSub = $data->mailSub;
            $mailBody = $data->mailBody;

        /**Find Report Json And Files Json */
            $jsonFileName = "upload/Ring_".$UserId."_Backup/P_".$UserId."_".$uniqueId."_Backup.json";
            if(is_file($jsonFileName))
            {
                $reportJson = $jsonFileName;
                $inp = file_get_contents($reportJson);
                $tempArray = json_decode($inp,true);
                $blankArr = array();
                
                foreach($tempArray as $field) {
                    if(isset($field['UploadedFileRef']) && !empty($field['UploadedFileRef'])){
                        foreach($field['UploadedFileRef'] as $fileVal) {
                            // print_r($fileVal); echo "----";
                            array_push($blankArr,"upload/Ring_".$UserId."_Backup/".$fileVal.".json");
                        }
                               
                    }
                }

                print_r($blankArr); exit ;
                // exit;
                array_push($blankArr,$reportJson);
                if(!empty($blankArr))
                {
                    /**Create Folder******************************/
                    $date = date("d-m-Y");
                    // $folderName = "upload/Ring_".$UserId."_Backup";
                    $folderName = "upload/Ring_".$UserId."_Backup/Ring_".$UserId."_".$date."_Backup";
                    if(!is_dir($folderName))
                    {
                        mkdir($folderName, 0777);
                        foreach($blankArr as $value){
                            $fileN = explode('/',$value);
                            copy($value, $folderName.'/'.$fileN[2]);
                        }
                    }else{
                        array_map("unlink", glob("$folderName/*"));
                        array_map("rmdir", glob("$folderName/*")); 
                        $dltFol = rmdir($folderName);
                        if($dltFol){
                            mkdir($folderName, 0777);
                            foreach($blankArr as $value){
                                $fileN = explode('/',$value);
                                copy($value, $folderName.'/'.$fileN[2]);
                            }
                        }
                        
                    }                      
                    /**Create Zip Folder ***************/
                    $temp_unzip_path = $folderName.'/';
                    $zip = new ZipArchive();
                    $dirArray = array();
                    $new_zip_file = "upload/Ring_".$UserId."_Backup/Ring_".$UserId."_".$date."_Backup.zip";

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

    public function downloadZip()
    {
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->userId;
            $uniqueId = $data->uniqueId;
        /**Find Report Json And Files Json */
            // $jsonFileName = "upload/Ring_".$UserId."_Backup/P_".$UserId."_".$uniqueId."_Backup.json";
             $jsonFileName = "upload/Ring_".$UserId."_Backup/P_".$UserId."_Backup.json"; 
            if(is_file($jsonFileName))
            {
                $reportJson = $jsonFileName;
                $inp = file_get_contents($reportJson);
                $tempArray = json_decode($inp,true);
                $blankArr = array();
                
                foreach($tempArray as $field) {
                    if(isset($field['UploadedFileRef']) && !empty($field['UploadedFileRef'])){
                        foreach($field['UploadedFileRef'] as $fileVal) {
                            // print_r($fileVal); echo "----";
                            array_push($blankArr,"upload/Ring_".$UserId."_Backup/".$fileVal.".json");
                        }
                               
                    }
                }

                // print_r($blankArr); exit ;
                // exit;
                array_push($blankArr,$reportJson);
                if(!empty($blankArr))
                {
                    /**Create Folder******************************/
                    $date = date("d-m-Y");
                    // $folderName = "upload/Ring_".$UserId."_Backup";
                    $folderName = "upload/Ring_".$UserId."_".$date."_Backup";
                    if(!is_dir($folderName))
                    {
                        mkdir($folderName, 0777);
                        foreach($blankArr as $value){
                            $fileN = explode('/',$value);
                            copy($value, $folderName.'/'.$fileN[2]);
                        }
                    }else{
                        array_map("unlink", glob("$folderName/*"));
                        array_map("rmdir", glob("$folderName/*")); 
                        $dltFol = rmdir($folderName);
                        if($dltFol){
                            mkdir($folderName, 0777);
                            foreach($blankArr as $value){
                                $fileN = explode('/',$value);
                                copy($value, $folderName.'/'.$fileN[2]);
                            }
                        }
                        
                    }                      
                    /**Create Zip Folder ***************/
                    $temp_unzip_path = $folderName.'/';
                    $zip = new ZipArchive();
                    $dirArray = array();
                    $new_zip_file = "upload/Ring_".$UserId."_Backup/Ring_".$UserId."_".$uniqueId."_".$date."_Backup.zip";
                    // if(is_dir($new_zip_file))
                    // {
                    //     array_map("unlink", glob("$new_zip_file/*"));
                    //     array_map("rmdir", glob("$new_zip_file/*")); 
                    //     $dltZip = rmdir($new_zip_file);
                    // }
                    // $new_zip_file1 = "upload/Ring_".$UserId."_Backup/Ring_".$UserId."_".$date."_Backup.zip";
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
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                    $response['response_data'] = $root.$sqlFile;
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
            print_r($file_dir);exit;
            $files = scandir($file_dir);
            $blankArr1 = array();
            if(isset($files) && !empty($files)){
                foreach($files as $value){
                    if($value == "P_".$userId."_Backup.json"){
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
    
    public function DeleteBackupFolder(){
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->userId;
            $folderName = "upload/Ring_".$UserId."_Backup";
            array_map("unlink", glob("$folderName/*"));
            array_map("rmdir", glob("$folderName/*")); 
            $deleteFol = rmdir($folderName);
            if($deleteFol){
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
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

}