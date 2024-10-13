<?php 
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ERROR | E_PARSE);
ini_set("memory_limit","-1");
ini_set("post_max_size","-1");
ini_set('upload_max_filesize','100M'); 
ini_set('sqlsrv.ClientBufferMaxKBSize','524288'); 
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','524288');
set_time_limit(0);
class BackupApi extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
        ob_clean();
        $this->load->model(array('WebserviceModel'));
        $config['allowed_types'] = 'pdf|csv';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->load->helper('url', 'form');
    }
/*********START: WEBSERVICE FOR BACKUP DIRECT FROM DEVICE*************/

    public function saveBackupReportInTable(){
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
            $folderName = "upload";
            $randnum = rand(11111111,99999999);
            if($ReportUploadType == "local"){
                /******create file text*********/                     
                $TextFileName = $folderName."/Local_".$randnum."_".$UserId."_".time().".txt";    
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

            if(isset($data->EreferralFormBase64)){
                /******create file text*********/                     
                $EreferralFormBase64Text = $folderName."/EreferralFormBase64_".$randnum."_".$UserId."_".time().".txt";    
                file_put_contents($EreferralFormBase64Text, $data->EreferralFormBase64);
                $EreferralFormBase64 = $EreferralFormBase64Text;
            }else{
                $EreferralFormBase64 = "";
            }
            $SessionId = $data->SessionId;
            
			 if($SessionId){
			 	$chkSession = $this->WebserviceModel->chkSessionForBackup($SessionId);
				if($chkSession){
					$jsonArr = array(
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
                        "WorkingScheduleref" => $WorkingScheduleref,
                        "EreferralFormBase64" => $EreferralFormBase64,
						"BackupID" => $chkSession->BackupID
                    );
					$insertSession = $chkSession->BackupID;
				//$checkDuplicate = $this->WebserviceModel->checkDuplicateEntryForBackup('PatientBackupReport','createTime',$createTime,'BackupID',$chkSession->BackupID);	
				}else{
					$sessionArr = array(
						"UserId" => $UserId,
                        "SessionTimestamp" => $SessionId,
                        "Status" => 0
						);
					//$chkSession = $this->WebserviceModel->chkSessionForBackup($SessionId);
					//if(isset($chkSession)){
					//	$insertSession = $chkSession->BackupID;
					//}else{
						$insertSession = $this->WebserviceModel->insertSession($sessionArr);
						$jsonArr = array(
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
                            "WorkingScheduleref" => $WorkingScheduleref,
                            "EreferralFormBase64" => $EreferralFormBase64,
							"BackupID" => $insertSession
						);
					//}					
				}
				$checkDuplicate = $this->WebserviceModel->checkDuplicateEntryForBackup('PatientBackupReport','createTime',$createTime,'BackupID',$insertSession);	
				if(isset($checkDuplicate) && !empty($checkDuplicate)){
					$response['response_code'] = 1;
					$response['response_message'] = 'Success';
					$response['BackupReportID'] = $checkDuplicate->BackupReportId;
				}else{
				 	$result = $this->WebserviceModel->insertBackupDataInReportTable($jsonArr); 
					if($result)             
					{
						$response['response_code'] = 1;
						$response['response_message'] = 'Success';
						$response['BackupReportID'] = $result;
					}
					else
					{
						$response['response_code'] = 2;
						$response['response_message'] = 'Failed';
					} 
				}	
			}
			else
            {
                $response['response_code'] = 4;
                $response['response_message'] = 'Session not found';
            }
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit; 
    }
	
	
	public function saveFileDetailsInBackupFileTable(){
        $data = json_decode(file_get_contents('php://input'));       
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
       // print_r($data ); exit ;
        if($data)
        {   
			$SessionId = $data->SessionId;
			/******create file text*********/
			$folderName = "upload"; 
            		$randnum = rand(11111111,99999999);     
			$TextFileName = $folderName."/".$randnum."_".$data->UserId."_".time().".txt";    
			file_put_contents($TextFileName, $data->base64Data);
			$base64Data = $TextFileName;
            $basedata = str_replace(' ', '%20', $data->basedata);
            // $wrkHrMalay = trim($data->isdoctor);
			/******create file text*********/
            if($SessionId){
                $chkSession = $this->WebserviceModel->chkSessionForBackup($SessionId);
                if(isset($chkSession) && !empty($chkSession)){
                    $jsonArr = array(
                        "Category" => $data->Category,
                        "UserId" => $data->UserId,
                        "DisplayPatientId" => $data->DisplayPatientId,
                        "EreportsTransitDetailId" => $data->EreportsTransitDetailId,
                        "FileAttachments" => $data->FileAttachments,
                        "PhoneNumber" => $data->PhoneNumber,
                        "ReportTransitId" => $data->ReportTransitId,
                        "Basedata" => $basedata,
                        "Base64Data" => $base64Data,
                        // "Isdoctor" => $wrkHrMalay,
                        "ShowreferalIcon" => $data->showreferalIcon,
                        "Sync_status" => $data->sync_status,
                        "FileType" => isset($data->fileType)?$data->fileType:'',
                        "BackupID" => $chkSession->BackupID,
						"BackupReportID" => $data->BackupReportID
                    );
			$checkDuplicate = $this->WebserviceModel->checkDuplicateEntryForBackup('PatientBackupFiles','EreportsTransitDetailId',$data->EreportsTransitDetailId,'BackupReportID',$data->BackupReportID);					
					if(isset($checkDuplicate) && !empty($checkDuplicate)){
					$response['response_code'] = 1;
					$response['response_message'] = 'Success';
				}else{
                    $result = $this->WebserviceModel->insertDetailsInFileTable($jsonArr);
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
                }else{
                   	$response['response_code'] = 5;
                	$response['response_message'] = 'Wrong Session'; 
                }              
            }
			else
            {
                $response['response_code'] = 4;
                $response['response_message'] = 'Session not found';
            }               
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;     
    }
	
	/****************************************************************/
	
	public function createReportJsonAndZip()
    {
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->UserId;
            $SessionId = $data->SessionId;
            $chkSession = $this->WebserviceModel->chkSessionForBackup($SessionId);
            if($chkSession){
                $BackupID = $chkSession->BackupID;
                /**Find Report and Create Json */
                $ReportData = $this->WebserviceModel->getReportDataForBackup($UserId, $BackupID);
                if($ReportData)
                {                  
                    /******Make User Folder*********/
                    $folderName = "upload/RING_Backup".$UserId."_".$SessionId;
                    if(!is_dir($folderName))
                    {
                        mkdir($folderName, 0777);
                    }        
                    $reportJsonName = $folderName."/".$UserId."_Report_Backup.json";                        
                    $json = json_encode($ReportData);
                    $result = file_put_contents($reportJsonName, $json);                              
                } 
                
                $ReportFileData = $this->WebserviceModel->getReportFileDetailsForBackup($UserId, $BackupID);
                if($ReportFileData)
                {         
                    $fileJsonName = $folderName."/".$UserId."_Files_Backup.json";                        
                    $json1 = json_encode($ReportFileData);
                    $result = file_put_contents($fileJsonName, $json1);                              
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
                        $response['response_code'] = 5;
                        $response['response_message'] = 'Zip creation failled';
                    }
                    $zip->close();

                    /**Send Mail */
				
					$Userdata = $this->WebserviceModel->getUserDataById($UserId);
					$pat_email = $this->encryptDecrypt("dc",$Userdata->Email);
					//$pat_email = "mishraravi520@gmail.com";
					//$link = "https://win.k2key.in/Ring/index.php/BackupApi/getZip?sess=".$SessionId;
                    $link = "https://apimobile.ring.healthcare:5025/".$new_zip_file;
					$message = $link;
					$subject = 'RING Backup '.date("d-m-Y");
					$sendMail = Utility::callSendMail($pat_email,$message,$subject);
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                                  
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


    function encryptDecrypt($type,$name){
        if($type == 'en'){
            $type1 = "Encrypt";
        }else{
            $type1 = "Decrypt";
        }
        $arrayToSend = array('userName'=>$name,'type'=>$type1);
        $url = 'https://apiweb.ring.healthcare:5028/Register/EncryptDecrypt';
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

    public function unzipJsonFile(){
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data["status"] = "ok";
            echo json_encode($data);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'));
		$root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        $headers = $data->headers;
        $token = str_replace("Bearer ", "", $headers);        
        $kunci = $this->config->item('thekey');
        $userData = JWT::decode($token, $kunci);
        $userId = $userData->id;
        $file_dir = $userData->file_dir;
        if($file_dir)
        {
            $files = scandir($file_dir);
            $reportArr = array();
			$fileArr = array();
            $extraRepArr = array();
            if(isset($files) && !empty($files)){
                foreach($files as $value){
                    if($value == $userId."_Report_Backup.json"){
                        $reportJson = $file_dir.$value;
                        $inp = file_get_contents($reportJson);
                        $tempArray = json_decode($inp,true);
						
                        array_push($reportArr,$tempArray); 
                    }else if($value == $userId."_Files_Backup.json"){
						$fileJson = $file_dir.$value;
                        $inp1 = file_get_contents($fileJson);
                        $tempArray1 = json_decode($inp1,true);
						
                        array_push($fileArr,$tempArray1); 
					}else if($value == $userId."_Extra_Report_Backup.json"){
						$extraRepJson = $file_dir.$value;
                        $inp2 = file_get_contents($extraRepJson);
                        $tempArray2 = json_decode($inp2,true);
						$tempArray2['Medication'] = json_decode($tempArray2['Medication'],true);
                        $tempArray2['Vaccination'] = json_decode($tempArray2['Vaccination'],true);
                        $tempArray2['Alergy'] = json_decode($tempArray2['Alergy'],true);
                        array_push($extraRepArr,$tempArray2); 
					}
                }
            }
            if(isset($reportArr) && !empty($reportArr))             
            {
                $response['response_code']=1;
                $response['response_message']='Success';
                $response['ReportData']=$reportArr;
				$response['FileData']=$fileArr;
                $response['ExtraReportData']=$extraRepArr;
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
	
	public function createReportJsonAndZipNew()
    {
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->UserId;
            $SessionId = $data->SessionId;
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
                        if(isset($field->EreferralFormBase64) && !empty($field->EreferralFormBase64)){
							$EreferralFormBase64 = file_get_contents($field->EreferralFormBase64);
							$field->EreferralFormBase64 = $EreferralFormBase64;
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
                        $response['response_code'] = 5;
                        $response['response_message'] = 'Zip creation failled';
                    }
                    $zip->close();

                    /**Send Mail */
				
					$Userdata = $this->WebserviceModel->getUserDataById($UserId);
					$pat_email = $this->encryptDecrypt("dc",$Userdata->Email);
					//$pat_email = "mishraravi520@gmail.com";
					//$link = "https://win.k2key.in/Ring/index.php/BackupApi/getZip?sess=".$SessionId;
                    $link = "https://apimobile.ring.healthcare:5025/".$new_zip_file;
					// $message = $link;
                    $message = '<p>Your backup file is ready to be downloaded and will expire in <span style="color:red">24 hours</span>. Please download your backup file now.</p><br><br>';
		    //$message = 'Your backup file is ready to be downloaded and will expire in 24 hours. Please download your backup file now.';		
                    $message .= '<a href="'.$link.'"><button class="btn" style="background-color: #a8c2db;border: none;color: #101215;padding: 12px 30px;cursor: pointer;font-size: 15px;"><i class="fa fa-download"></i> Download Zip</button></a>';
			$subject = 'RING Backup '.date("d-m-Y");
		    //$sendMail = Utility::callSendMailwithAttachedFile($pat_email,$message,$subject,$link);
                    $sendMail = Utility::callSendMail($pat_email,$message,$subject);
			$response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                                  
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

    public function saveExtraDetailsBackupOfUser(){
        $data = json_decode(file_get_contents('php://input'));       
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
       // print_r($data ); exit ;
        if($data)
        {   
			$SessionId = $data->SessionId;
            $Medicine = isset($data->Medication)?$data->Medication:"";
            if(!empty($Medicine)){
                $Medication = json_encode($Medicine);
            }else{
                $Medication = "NULL";
            }
            $Vaccine = isset($data->Vaccination)?$data->Vaccination:"";
            if(!empty($Vaccine)){
                $Vaccination = json_encode($Vaccine);
            }else{
                $Vaccination = "NULL";
            }
            $Alrg = isset($data->Allergy)?$data->Allergy:"";
            if(!empty($Alrg)){
                $Allergy = json_encode($Alrg);
            }else{
                $Allergy = "NULL";
            }
                       
            if($SessionId){
                $chkSession = $this->WebserviceModel->chkSessionForBackup($SessionId);
                if(isset($chkSession) && !empty($chkSession)){
                    $jsonArr = array(
                        "UserId" => $data->UserId,
                        "BackupID" => $chkSession->BackupID,
                        "Medication"=> $Medication,
                        "Vaccination"=> $Vaccination,
                        "Alergy"=> $Allergy,
                    );
                    $result = $this->WebserviceModel->insertDetailsInExtraBackupTable($jsonArr);
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
                }else{
                   	$response['response_code'] = 5;
                	$response['response_message'] = 'Wrong Session'; 
                }              
            }
			else
            {
                $response['response_code'] = 4;
                $response['response_message'] = 'Session not found';
            }               
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;     
    }

    public function createReportJsonAndZipForTestingPerpose()
    {
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->UserId;
            $SessionId = $data->SessionId;
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
                        $response['response_code'] = 5;
                        $response['response_message'] = 'Zip creation failled';
                    }
                    $zip->close();

                    /**Send Mail */
				
					$Userdata = $this->WebserviceModel->getUserDataById($UserId);
					$pat_email = $this->encryptDecrypt("dc",$Userdata->Email);
					//$pat_email = "mishraravi520@gmail.com";
					//$link = "https://win.k2key.in/Ring/index.php/BackupApi/getZip?sess=".$SessionId;
                    $link = "https://apimobile.ring.healthcare:5025/".$new_zip_file;
					// $message = $link;
                    $message = '<p>Your backup file is ready to be downloaded and will expire in <span style="color:red">24 hours</span>. Please download your backup file now.</p>';
			        $share_message = '<p>Your backup file is ready to be share and will expire in <span style="color:red">24 hours</span>. Please share your backup file now.</p>';
                    $subject = 'RING Backup '.date("d-m-Y");
                    $returnData = array("link" => $link, "message" => $message, "share_message" => $share_message);
                    // $insertArr = array("FileName" => "RING_Backup_".$date.".zip", "FileLocation" => $folderName, "FileType" => "zip", "TimeDuration" => "24", "Link" => $link);
					// $this->WebserviceModel->insertTimelineForDeleteFile($insertArr);
                    // $sendMail = Utility::callSendMailwithAttachedFile($pat_email,$message,$subject,$link);
                    $response['response_code'] = 1;
                    $response['response_message'] = 'Success';
                    $response['response_data'] = $returnData;
                                  
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

    public function saveExtraDetailsBackupOfUserWithoutReport(){
        $data = json_decode(file_get_contents('php://input'));       
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
       // print_r($data ); exit ;
        if($data)
        {   
			$SessionId = $data->SessionId;
            $Medicine = isset($data->Medication)?$data->Medication:"";
            if(!empty($Medicine)){
                $Medication = json_encode($Medicine);
            }else{
                $Medication = "NULL";
            }
            $Vaccine = isset($data->Vaccination)?$data->Vaccination:"";
            if(!empty($Vaccine)){
                $Vaccination = json_encode($Vaccine);
            }else{
                $Vaccination = "NULL";
            }
            $Alrg = isset($data->Allergy)?$data->Allergy:"";
            if(!empty($Alrg)){
                $Allergy = json_encode($Alrg);
            }else{
                $Allergy = "NULL";
            }
                       
            if($SessionId){
                $chkSession = $this->WebserviceModel->chkSessionForBackup($SessionId);
                if(isset($chkSession) && !empty($chkSession)){
                    $jsonArr = array(
                        "UserId" => $data->UserId,
                        "BackupID" => $chkSession->BackupID,
                        "Medication"=> $Medication,
                        "Vaccination"=> $Vaccination,
                        "Alergy"=> $Allergy
                    );
                    
                }else{
                   	$sessionArr = array(
						"UserId" => $data->UserId,
                        "SessionTimestamp" => $SessionId,
                        "Status" => 0
                    );
                    $insertSession = $this->WebserviceModel->insertSession($sessionArr); 
                    $jsonArr = array(
                        "UserId" => $data->UserId,
                        "BackupID" => $insertSession,
                        "Medication"=> $Medication,
                        "Vaccination"=> $Vaccination,
                        "Alergy"=> $Allergy
                    );
                } 
                $result = $this->WebserviceModel->insertDetailsInExtraBackupTable($jsonArr);
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
                $response['response_code'] = 4;
                $response['response_message'] = 'Session not found';
            }               
        }
        else
        {
            $response['response_code'] = 3;
            $response['response_message'] = 'Data is NULL';
        }
        echo json_encode($response);exit;     
    }

    public function createReportJsonAndZipForWithoutReport()
    {
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->UserId;
            $SessionId = $data->SessionId;
			$chkSession = $this->WebserviceModel->chkSessionForBackupForCreateZip($SessionId,$UserId);
            if($chkSession){
				foreach($chkSession as $chkSessionVal){
					$BackupID = $chkSessionVal->BackupID;
                    $ExtraReportData = $this->WebserviceModel->getExtraReportDataForBackup($UserId, $BackupID);
				}

                /******Make User Folder*********/
                $folderName = "upload/RING_Backup".$UserId."_".$SessionId."_".time();
                if(!is_dir($folderName))
                {
                    mkdir($folderName, 0777);
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
                    $response['response_code'] = 5;
                    $response['response_message'] = 'Zip creation failled';
                }
                $zip->close();

                /**Send Mail */
            
                $Userdata = $this->WebserviceModel->getUserDataById($UserId);
                $pat_email = $this->encryptDecrypt("dc",$Userdata->Email);
                $link = "https://apimobile.ring.healthcare:5025/".$new_zip_file;
                $message = '<p>Your backup file is ready to be downloaded and will expire in <span style="color:red">24 hours</span>. Please download your backup file now.</p>';
                $share_message = '<p>Your backup file is ready to be share and will expire in <span style="color:red">24 hours</span>. Please share your backup file now.</p>';
                $subject = 'RING Backup '.date("d-m-Y");
                $returnData = array("link" => $link, "message" => $message, "share_message" => $share_message);
                $insertArr = array("FileName" => "RING_Backup_".$date.".zip", "FileLocation" => $folderName, "FileType" => "zip", "TimeDuration" => "24", "Link" => $link);
                $this->WebserviceModel->insertTimelineForDeleteFile($insertArr);
                $response['response_code'] = 1;
                $response['response_message'] = 'Success';
                $response['response_data'] = $returnData;
                                  
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
}