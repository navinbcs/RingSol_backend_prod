<?php 
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ERROR | E_PARSE);
require_once APPPATH . "libraries/tcpdf/PDFMerger.php";
 
use PDFMerger\PDFMerger;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('max_execution_time', '0');
set_time_limit(0);
class File_transfer_api extends CI_Controller
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
/*******************START: APIs***********************/
    
    public function FetchMixPDFOfMRDTDocument1(){
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $patId = $data->patient_id;
            $ringGrpId = isset($data->RingGroup_Id)?$data->RingGroup_Id:"";
            $startDate = $data->start_date;
            $endDate = $data->end_date;
            $pdfFileArr = array();
            $imageFileArr = array();
            $reportFileList = $this->WebserviceModel->FetchMixPDFOfMRDTDocument($patId,$ringGrpId,$startDate,$endDate);
            if(is_array($reportFileList)){
                foreach($reportFileList as $fileData){
                    $jsonFileArr = json_decode($fileData->FileAttachments);
                    if(isset($jsonFileArr[0]->Filename)){
                        $filename = explode(".",$jsonFileArr[0]->Filename);
                        $ext = $filename[1];
                        if($ext == "pdf"){
                            $fileAttachments = "http://sancyberhad.ddns.net/RING_TEST/upload/".$jsonFileArr[0]->Filename;
                            array_push($pdfFileArr,$fileAttachments);
                        }else{
                            $fileAttachments = "http://sancyberhad.ddns.net/RING_TEST/upload/".$jsonFileArr[0]->Filename;
                            array_push($imageFileArr,$fileAttachments); 
                        }							
                    }
                }
            }
            $template_name = 'template/file_merge_template.html';
            $content = file_get_contents($template_name);
            $content = $this->mergePdfTemplate($content,$imageFileArr);
            $path =  dirname(dirname(__DIR__)).'/pdfDoc/';
            $pdfName = time().'_mergedFile_'.$patId.'.pdf';
            $this->m_pdf->pdf->WriteHTML($content);
            $this->m_pdf->pdf->Output($path.$pdfName, "F");
            $pdfPath =$root.'pdfDoc/'.$pdfName;
            // print_r($pdfPath);exit;
            array_push($pdfFileArr,$pdfPath);
            // $pdf = new FPDI();
            $outputPdfNm = time().'_AllMRDTFiles_'.$patId.'.pdf';
            $output= $path.$outputPdfNm;//where to save the combined file
            $mergePdf = $this->mergePDFFiles($pdfFileArr, $output);
            // foreach ($pdfFileArr as $file) {
            //     $pageCount = $pdf->setSourceFile($file);
            //     for ($i = 0; $i < $pageCount; $i++) {
            //         $tpl = $pdf->importPage($i + 1, '/MediaBox');
            //         $pdf->addPage();
            //         $pdf->useTemplate($tpl);
            //     }
            // }
            // $pdf->Output($output);//save the file

            $finalPdfPath =$root.'pdfDoc/'.$outputPdfNm;
            print_r($finalPdfPath);exit;

            if($imageFileArr)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $imageFileArr;
                $response['response_data1'] = $pdfFileArr;
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
        echo json_encode($response); exit;
    }

    public function mergePdfTemplate($content,$imageFileArr){
        $str ='';
        foreach($imageFileArr as $imgVal){
            // print_r($imgVal);exit;
            $str .= '<div class="wrapper-table scroll-x margintopcss centerImage">';
            $str .= '<img src="'.$imgVal.'" alt="Report" width="400" height="400">';
            $str .= '</div>';
        }
        $content = str_replace(
         array('VAR_ITEM_DATA'),
         array($str),
         $content
      );
      return $content;
    }

    function mergePDFFiles($filenames, $outFile)
    {
        if ($filenames) {

            $filesTotal = sizeof($filenames);
            $fileNumber = 1;

            $this->m_pdf->pdf->SetImportUse(); // this line comment out if method doesnt exist

            if (!file_exists($outFile)) {
                $handle = fopen($outFile, 'w');
                fclose($handle);
            }

            foreach ($filenames as $fileName) {
                if (file_exists($fileName)) {
                    $pagesInFile = $this->m_pdf->pdf->SetSourceFile($fileName);
                    for ($i = 1; $i <= $pagesInFile; $i++) {
                        $tplId = $this->m_pdf->pdf->ImportPage($i); // in mPdf v8 should be 'importPage($i)'
                        $this->m_pdf->pdf->UseTemplate($tplId);
                        if (($fileNumber < $filesTotal) || ($i != $pagesInFile)) {
                            $this->m_pdf->pdf->WriteHTML('<pagebreak />');
                        }
                    }
                }
                $fileNumber++;
            }

            $this->m_pdf->pdf->Output($outFile);

        }

    }

    public function FetchMixPDFOfMRDTDocument2(){
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $patId = $data->patient_id;
            $ringGrpId = isset($data->RingGroup_Id)?$data->RingGroup_Id:"";
            $startDate = $data->start_date;
            $endDate = $data->end_date;
            $pdfFileArr = array();
            $imageFileArr = array();
            $reportFileList = $this->WebserviceModel->FetchMixPDFOfMRDTDocument($patId,$ringGrpId,$startDate,$endDate);
            if(is_array($reportFileList)){
                foreach($reportFileList as $fileData){
                    $jsonFileArr = json_decode($fileData->FileAttachments);
                    if(isset($jsonFileArr[0]->Filename)){
                        $filename = explode(".",$jsonFileArr[0]->Filename);
                        $ext = $filename[1];
                        if($ext == "pdf"){
                            $fileAttachments = "http://sancyberhad.ddns.net/RING_TEST/upload/".$jsonFileArr[0]->Filename;
                            $url  = $fileAttachments;
                            $pdffilename1 = time().'_mergedFile_'.$patId.'.pdf';
                            $path1 = dirname(dirname(__DIR__)).'/pdfDoc/';

                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_REFERER, $url);
                            $curl_data = curl_exec($ch);
                            curl_close($ch);
                            $pdfResult = file_put_contents($path1.$pdffilename1, $curl_data);
                            array_push($pdfFileArr,$pdffilename1);
                        }else{
                            $fileAttachments = "http://sancyberhad.ddns.net/RING_TEST/upload/".$jsonFileArr[0]->Filename;
                            array_push($imageFileArr,$fileAttachments); 
                        }							
                    }
                }
            }
            // print_r($imageFileArr);
            // print_r($pdfFileArr);exit;
            $template_name = 'template/file_merge_template.html';
            $content = file_get_contents($template_name);
            $content = $this->mergePdfTemplate($content,$imageFileArr);
            $path =  dirname(dirname(__DIR__)).'/pdfDoc/';
            $pdfName = time().'_mergedFile_'.$patId.'.pdf';
            $this->m_pdf->pdf->WriteHTML($content);
            $this->m_pdf->pdf->Output($path.$pdfName, "F");
            $pdfPath =$root.'pdfDoc/'.$pdfName;
            unset($this->m_pdf);
            // print_r($pdfPath);exit;
            array_push($pdfFileArr,$pdfName);
            // print_r($pdfFileArr);exit;
            // MERGER FILES
	        $pdf = new PDFMerger;

	        if($pdfFileArr){
	            foreach($pdfFileArr as $file){
                    // print_r($file);exit;
                    $pdf->addPDF($path.$file, 'all');
	                // $pdf->addPDF($file, 'all');
	            }

	            $new_file = md5(time().rand(1,10)) .'.pdf';
	            $pdf->merge('file', $path.$new_file);

	        } else {
	            $new_file = '';
	        }

	        // REMOVE TEMPORARY FILES
	        if($pdfFileArr){
	            foreach($pdfFileArr as $file){
	                @unlink($path.$file);
	            }
	        }
            // print_r($new_file);exit;

            if($new_file)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $imageFileArr;
                $response['response_data1'] = $pdfFileArr;
                $response['response_data2'] = $root.'pdfDoc/'.$new_file;
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
        echo json_encode($response); exit;
    }

    public function FetchMixPDFOfMRDTDocument(){
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $patId = $data->patient_id;
            $ringGrpId = isset($data->RingGroup_Id)?$data->RingGroup_Id:"";
            $startDate = $data->start_date;
            $endDate = $data->end_date;
            $pdfFileArr = array();
            $imageFileArr = array();
            $fileArrayAttachemnt = array();
            $reportFileList = $this->WebserviceModel->FetchMixPDFOfMRDTDocument_new($patId,$ringGrpId,$startDate,$endDate);
            // print_r($reportFileList);exit;
            if(is_array($reportFileList)){
                foreach($reportFileList as $fileData){
                    $InsertDate = $fileData->InsertDate;
                    if(isset($fileData->Description) && $fileData->Description != null){
                        $desc = $fileData->Description;
                    }else{
                        $desc = '';
                    }
                    $newInsertDate = date("d/m/Y",  strtotime($InsertDate));
                    if(isset($fileData->ICDSubCode) && isset($fileData->ICDSubCodeDescription) && !empty($fileData->ICDSubCode) && !empty($fileData->ICDSubCodeDescription)){
                        $diagnosis = $fileData->ICDSubCode."_".$fileData->ICDSubCodeDescription; 
                    }else if(isset($fileData->ICD) && !empty($fileData->ICD)){
                        $diagnosis = $fileData->ICD;
                    }else{
                        $diagnosis = $desc;
                    }
                    $fileArrayAttachemnt[]=array("PatientMasterId"=>$fileData->PatientMasterId,"ReportTransitId"=>$fileData->ReportTransitId,"RingGroupMasterId"=>$fileData->RingGroupMasterID,"RingGroupMasterIdReff"=>$fileData->RingGroup,"InsertDate"=>$newInsertDate,"Description"=>$desc,"EreferralForm"=>array(),"FileAttachments"=>array(),"IsProcessed"=>$fileData->IsProcessed,"IsDoctorProcessed"=>$fileData->IsDoctorProcessed,"IsPatientProcessed"=>$fileData->IsPatientProcessed,"AddReferral"=>$fileData->AddReferral,"DoctorId"=>$fileData->DoctorId,"doctorName"=>$fileData->doctorName, "DoctorPhoneNumber"=>$fileData->UserPhoneNumber,"TenantName"=>$fileData->TenantName,"WorkingSchedule"=>array(),"TenantPhoneNuber"=>$fileData->TenantPhoneNuber,"TenantFaxNumber"=>$fileData->TenantFaxNumber,"TenantAddress"=>$fileData->TenantAddress,"Refferal"=>array(), "diagnosis"=>$diagnosis,"DoctorSpeciality"=>$fileData->DoctorSpeciality);               
                    $fileAttachmentList = $this->WebserviceModel->getFileAttachmentByReportTransitIdForPdf($fileData->ReportTransitId);
                    //  print_r($fileDataVal->fileAttachmentList);exit;
                    if(is_array($fileAttachmentList)){
                        foreach($fileAttachmentList as $fileDataVal){
                            // print_r($fileDataVal->FileAttachments);exit;
                            $jsonFileArr = json_decode($fileDataVal->FileAttachments);
                            if(isset($jsonFileArr[0]->Filename)){
                                $fileDataVal->FileAttachments = $jsonFileArr[0]->Filename;
                                $fileArrayAttachemnt[count( $fileArrayAttachemnt)-1]["FileAttachments"][]=$fileDataVal;
                            }
                        }
                    }
                                // "http://sancyberhad.ddns.net/RING_TEST/upload/"

                    // $jsonFileArr = json_decode($fileData->FileAttachments);
                    // if(isset($jsonFileArr[0]->Filename)){
                    //     $filename = explode(".",$jsonFileArr[0]->Filename);
                    //     $ext = $filename[1];
                    //     if($ext == "pdf"){
                    //         $fileAttachments = "http://sancyberhad.ddns.net/RING_TEST/upload/".$jsonFileArr[0]->Filename;
                    //         $url  = $fileAttachments;
                    //         $pdffilename1 = time().'_mergedFile_'.$patId.'.pdf';
                    //         $path1 = dirname(dirname(__DIR__)).'/pdfDoc/';

                    //         $ch = curl_init($url);
                    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    //         curl_setopt($ch, CURLOPT_REFERER, $url);
                    //         $curl_data = curl_exec($ch);
                    //         curl_close($ch);
                    //         $pdfResult = file_put_contents($path1.$pdffilename1, $curl_data);
                    //         array_push($pdfFileArr,$pdffilename1);
                    //     }else{
                    //         $fileAttachments = "http://sancyberhad.ddns.net/RING_TEST/upload/".$jsonFileArr[0]->Filename;
                    //         array_push($imageFileArr,$fileAttachments); 
                    //     }							
                    // }
                //   print_r($fileArrayAttachemnt);exit;            
                }
                $template_name = 'template/file_merge_template_new.html';
                $content = file_get_contents($template_name);
                $content = $this->mergePdfTemplate_new($content,$fileArrayAttachemnt);
                $path =  dirname(dirname(__DIR__)).'/pdfDoc/';
                $pdfName = time().'_mergedFile_'.$patId.'.pdf';
                $this->m_pdf->pdf->WriteHTML($content);
                $this->m_pdf->pdf->Output($path.$pdfName, "F");
                $pdfPath =$root.'pdfDoc/'.$pdfName;
                    
                // array_push($pdfFileArr,$pdfName);
            }
            // echo $pdfPath; exit;
            unset($this->m_pdf); 
            // print_r($imageFileArr);

            // $template_name = 'template/file_merge_template.html';
            // $content = file_get_contents($template_name);
            // $content = $this->mergePdfTemplate($content,$imageFileArr);
            // $path =  dirname(dirname(__DIR__)).'/pdfDoc/';
            // $pdfName = time().'_mergedFile_'.$patId.'.pdf';
            // $this->m_pdf->pdf->WriteHTML($content);
            // $this->m_pdf->pdf->Output($path.$pdfName, "F");
            // $pdfPath =$root.'pdfDoc/'.$pdfName;
            // unset($this->m_pdf);
            // print_r($pdfPath);exit;
            // array_push($pdfFileArr,$pdfName);
            // print_r($pdfFileArr);exit;
            // MERGER FILES
	        // $pdf = new PDFMerger;

	        // if($pdfFileArr){
	        //     foreach($pdfFileArr as $file){
            //         // print_r($file);exit;
            //         $pdf->addPDF($path.$file, 'all');
	        //         // $pdf->addPDF($file, 'all');
	        //     }

	        //     $new_file = md5(time().rand(1,10)) .'.pdf';
	        //     $pdf->merge('file', $path.$new_file);

	        // } else {
	        //     $new_file = '';
	        // }

	        // // REMOVE TEMPORARY FILES
	        // if($pdfFileArr){
	        //     foreach($pdfFileArr as $file){
	        //         @unlink($path.$file);
	        //     }
	        // }
            // print_r($new_file);exit;

            if($pdfPath)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $pdfPath;
                // $response['response_data1'] = $pdfFileArr;
                // $response['response_data2'] = $root.'pdfDoc/'.$new_file;
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
        echo json_encode($response); exit;
    }

    public function mergePdfTemplate_new($content,$fileArrayAttachemnt){
        $filepath = "http://sancyberhad.ddns.net/RING_TEST/upload/";
        $str ='';
        // print_r($fileArrayAttachemnt);exit;
        foreach($fileArrayAttachemnt as $fileVal){
            // print_r($fileVal);exit;       
            $str .= '<div><table>';
            $str .= '<tr><th>Diagnosis</th><td>'.$fileVal["diagnosis"].'</td></tr>';
            $str .= '<tr><th>Date</th><td>'.$fileVal["InsertDate"].'</td></tr>';
            $str .= '<tr><th>Description</th><td>'.$fileVal["Description"].'</td></tr>';
            $str .= '<tr><th>Doctor Name</th><td>'.$fileVal["doctorName"].'</td></tr>';
            $str .= '<tr><th>Facility Name</th><td>'.$fileVal["TenantName"].'</td></tr>';
            $str .= '<tr><th>Doctor Speciality</th><td>'.$fileVal["DoctorSpeciality"].'</td></tr>';
            $str .= '</table></div><div>';
            foreach($fileVal["FileAttachments"] as $fileAttchVal){
                // print_r($fileAttchVal);exit;
                $fileWithPath = $filepath.$fileAttchVal->FileAttachments;
                $filename = explode(".",$fileAttchVal->FileAttachments);
                $ext = $filename[1];
                $str .= '<h3 style="text-align:center"">'.$fileAttchVal->Category.'</h3>';
                if($ext == "pdf"){   
                            $url = $fileWithPath;
                            $pdffilename1 = md5(time().rand(1,10)) .'.pdf';
                            $path1 = dirname(dirname(__DIR__)).'/pdfDoc/';

                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_REFERER, $url);
                            $curl_data = curl_exec($ch);
                            curl_close($ch);
                            $pdfResult = file_put_contents($path1.$pdffilename1, $curl_data);  
                    // $str .= '<div class="wrapper-table scroll-x margintopcss centerImage">';
                    $str .= '<embed src="pdfDoc/'.$pdffilename1.'" type="application/pdf" width="100%" height="100%" />';
                      
                }else{   
                    $str .= '<div class="wrapper-table scroll-x margintopcss centerImage">';
                    $str .= '<img src="'.$fileWithPath.'" alt="Report" width="600" height="800">';
                    $str .= '</div>';
                }
            }
            $str .= '</div><p style="page-break-before: always">';
        }
        $content = str_replace(
         array('VAR_ITEM_DATA'),
         array($str),
         $content
      );
      return $content;
    }

    public function createOnePdfOfAllMRDTRecords(){


        // $pdf = new PDFMerger;
        // echo 1 ;
        // $pdf->addPDF('upload/bp.pdf');
        // $pdf->addPDF('upload/20309738231639142349.pdf');
        // $pdf->merge('file', 'upload/TEST2.pdf'); // generate the file

        // exit ;
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->UserId;
            $SessionId = $data->SessionId;
            $allergy = $data->SessionId;
            // $UserId = "333";
            // $SessionId = "1688522401974";
			$chkSession = $this->WebserviceModel->chkSession($SessionId,$UserId);
			$reportArr = array();
			$reportFileArr = array();    
            $resultArr = array();
            if($chkSession){
				foreach($chkSession as $chkSessionVal){
					$ID = $chkSessionVal->BackupID;
					/**Find Report and Create Json */
					$ReportData = $this->WebserviceModel->getReportDataForPDFCreation($UserId, $ID);
					if($ReportData)
					{
						foreach($ReportData as $ReportDataVal){
							array_push($reportArr,$ReportDataVal);
						}				
					}		
					$ReportFileData = $this->WebserviceModel->getReportFileDetailsForPDFCreation($UserId, $ID);
					if($ReportFileData)
					{
						foreach($ReportFileData as $ReportFileDataVal){
							array_push($reportFileArr,$ReportFileDataVal);
						}				
					}
				}
                if($reportFileArr)
                {  				
					foreach($reportFileArr as $field1) {
						if(isset($field1->Base64Data) && !empty($field1->Base64Data)){
							$Base64Data = file_get_contents($field1->Base64Data);
                            $Base64Data1 = $this->getImgUrlFromBase64($Base64Data);
							$field1->Base64Data = $Base64Data1;
						}
					}					                            
                }
                // echo "<pre>"; print_r($reportFileArr);
				if($reportArr)
				{  
                    $i = 0;
					foreach($reportArr as $field) {
						if(isset($field->Data2) && !empty($field->Data2)){
							$data2 = file_get_contents($field->Data2);
							$field->Data2 = $data2;
						}
                        $ReportId = $field->BackupReportId;
                        $fileArr = $this->getdataById($reportFileArr, $ReportId);
                        $field->FileArray = $fileArr;
						array_push($resultArr,$field);
                        $i++;
					}                           
				}

            // echo "<pre>"; print_r($resultArr);
            // exit;
                $pdfresultArr = array();
                if($resultArr)
				{ 
                    // foreach($resultArr as $index=>$resultVal){

                    //     // echo "<pre>";

                    //     // print_r($resultVal);exit;
                    //     if($resultVal ){

                    //      //   print_r($resultVal);
                    //         $createPdf = $this->createPdf($UserId,$resultVal);
                    //     //    print_r($createPdf);exit;
                    //     }
                        
                    //     array_push($pdfresultArr,$createPdf);
            
                    // }
                    // print_r($pdfresultArr);exit;
                    $createPdf = $this->createPdf($UserId,$resultArr);
                    // $return = $this->mergePdf($pdfresultArr);
                    // $pdf = new PDFMerger;
                    // $path1 =  dirname(dirname(__DIR__)).'/';
                    // $path2 =  dirname(dirname(__DIR__)).'/pdfDoc/';
                    // if($pdfresultArr){
                    //     foreach($pdfresultArr as $file){
                    //         $pdf->addPDF($path1.$file, 'all');
                    //         // $pdf->addPDF($file, 'all');
                    //     }
                    //     // exit;
                    //     $new_file = md5(time().rand(1,10)) .'_final.pdf';
                    //     $pdf->merge('file', $path2.$new_file);
                    //     print_r($new_file);exit;
                    // } else {
                    //     $new_file = '';
                    // }

                    // // REMOVE TEMPORARY FILES
                    // if($pdfresultArr){
                    //     foreach($pdfresultArr as $file){
                    //         @unlink($path1.$file);
                    //     }
                    // }
                    // print_r($return);exit;
                }

            }
            // $template_name = 'template/file_merge_template.html';
            // $content = file_get_contents($template_name);
            // $content = $this->mergePdfTemplate($content,$imageFileArr);
            // $path =  dirname(dirname(__DIR__)).'/pdfDoc/';
            // $pdfName = time().'_mergedFile_'.$patId.'.pdf';
            // $this->m_pdf->pdf->WriteHTML($content);
            // $this->m_pdf->pdf->Output($path.$pdfName, "F");
            // $pdfPath =$root.'pdfDoc/'.$pdfName;
            // unset($this->m_pdf);
            // print_r($pdfPath);exit;
            // array_push($pdfFileArr,$pdfName);
            
            if($createPdf)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $createPdf;
                // $response['response_data1'] = $pdfFileArr;
                $response['fileWithPath'] = $root.$createPdf;
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
        echo json_encode($response); exit;
    }

    function getdataById($arrays, $id){
        $returnArr = array();
		foreach($arrays as $array){
			//print_r($array['BackupReportID']);exit;
			if ($array->BackupReportID == $id)
                array_push($returnArr,$array);
				// return $array;
		}
		return $returnArr;
	}

    public function getImgUrlFromBase64($baseImage){
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        $data = explode(';', $baseImage);
        $type = $data[0];
        $data1 = explode(',', $data[1]);
        $base = $data1[0];
        $data2 = base64_decode($data1[1]);
        $ext = explode(':', $type);
        $file_ext = explode('/', $ext[1]);
        $randnum = rand(11111111,99999999);
        $file_name = $randnum.'_IMG_'.time();
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
        return $file;
    }

    function createPdf($UserId,$dataArray){
        // echo "<pre>"; print_r($dataArray);exit;
        $fileArr = array();
        $i = 0;
        foreach($dataArray as $key=>$dataValue){
            // print_r($key);
            $filepath = "https://apimobile.ring.healthcare:5025/upload/";
            $template_name = 'template/file_merge_template_new.html';
            // $template_name = 'template/ring.html';
            $content = file_get_contents($template_name);

            $this->m_pdf->pdf->SetHTMLHeader('<table style="width:100%; margin-top:0; border-collapse: collapse;">
            <tr style="background: #03625c; border-collapse: collapse;">
                <td style="text-align: center; border-collapse: collapse;">
                    <div>
                        <img src="https://apimobile.ring.healthcare:5025/assets/logo/ring_logo.png" width="100" />
                    </div>
                </td>
                <td style="text-align: right; color:#FFFFFF; border-collapse: collapse;"><div>Created Date'.date("d M Y").'</div></td>
            </tr>
            </table>');
            $this->m_pdf->pdf->SetHTMLFooter('<table style="width:100%; margin-top:2rem;">
            <tr>
                <td style="background: #03625c; text-align: center;">
                    <div>
                        <img src="https://apimobile.ring.healthcare:5025/assets/logo/ring_logo.png" width="100" />
                    </div>
                </td>
            </tr>
            </table>');
            $this->m_pdf->pdf->AddPage('', '', '', '', '',
            20, // margin_left
            20, // margin right
            25, // margin top
            35, // margin bottom
            5, // margin header
            5); // margin footer
            if($key == 0){
                $userData =  $this->WebserviceModel->getUserDataById($UserId);
            }else{
                $userData = "";
            }
            
            $content = $this->createPdfContentHtml($content,$dataValue,$userData);
            // $content = $this->createPdfContentHtml_newDesign($content,$dataValue);
            $path =  dirname(dirname(__DIR__)).'/pdfDoc/';
            $pdfName = time().rand(1,10).'_mergedFile_'.$UserId.'.pdf';
            if($content){

                // echo $content ;
                // echo "-----------------------------------";
                $this->m_pdf->pdf->WriteHTML($content);
                $this->m_pdf->pdf->Output($path.$pdfName, "F");
                $pdfPath =$root.'pdfDoc/'.$pdfName;
                unset($this->m_pdf); 
                $this->load->library('m_pdf');
                
                array_push($fileArr,$pdfPath);
                foreach($dataValue->FileArray as $files){ 
                    $filename = explode("/",$files->Base64Data);
                    $filename1 = explode(".",$filename[1]);
                    $ext = $filename1[1];
                    if($ext == "pdf"){ 
                        array_push($fileArr,$files->Base64Data);
                    }
                }
                
            }else{
                return null;
            }   
            $i++;   
        }
         $return = $this->mergePdf($fileArr);
        //  echo "<pre>"; print_r($return);exit;
         return $return;
        
    }

    public function createPdfContentHtml($content,$dataArray,$userData){
        // echo "<pre>"; print_r($userData);exit;
        $filepath = "https://apimobile.ring.healthcare:5025/upload/";    
        $str ='';
        $str .= '<div><table style="margin-top:3rem;">';
        if(isset($userData) && !empty($userData)){
            $userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);   
            $userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
            $userData->Email = $this->encryptDecrypt("dc",$userData->Email);                       
            $str .= '<tr><th>Patient Info</th><td>Name : '.$userData->FullName.'<br>';
            $str .= 'Mobile Number : '.$userData->MobileNumber.'<br>';
            $str .= 'Email ID : '.$userData->Email.'<br>';
            $str .= 'Blood Group : '.$userData->bloodgroup.'<br>';
            $str .= 'Address : '.$userData->Address.', '.$userData->PinCode.'</td></tr>';
        }
        
        $str .= '<tr><th>Diagnosis</th><td>'.$dataArray->Diagnosis.'</td></tr>';
        $date = date_create($dataArray->InsertDate);
        // $InsertDate = date_format($date,"d M Y h:i A");
        $timestamp = $dataArray->CreateTime;
        $InsertDate = date('d M Y h:i A', $timestamp/1000);
        $str .= '<tr><th>Date</th><td>'.$InsertDate.'</td></tr>';
        $str .= '<tr><th>Description</th><td>'.$dataArray->Description.'</td></tr>';
        $str .= '<tr><th>Doctor Name</th><td>'.$dataArray->DoctorName.'</td></tr>';
        $str .= '<tr><th>Facility Name</th><td>'.$dataArray->TenantName.'</td></tr>';
        $str .= '<tr><th>Doctor Speciality</th><td>'.$dataArray->DoctorSpeciality.'</td></tr>';
        $str .= '</table></div><div>';
        if($dataArray->FileArray){
            foreach($dataArray->FileArray as $fileAttchVal){
                $filename = explode("/",$fileAttchVal->Base64Data);
                $filename1 = explode(".",$filename[1]);
                $ext = $filename1[1];
                $fileWithPath = $filepath.$filename[1];
                // $str .= '<h3 style="text-align:center"">'.$fileAttchVal->Category.'</h3>';
                // 
                if($ext != "pdf"){ 
                    $str .= '<div class="wrapper-table scroll-x margintopcss centerImage">';
                    $str .= '<img src="'.$fileWithPath.'" alt="Report" width="600" height="800">';
                    $str .= '</div>'; 
                }
            }
            
        }
        // $str .= '</div><p style="page-break-before: always">';
        $content = str_replace(
         array('VAR_ITEM_DATA'),
         array($str),
         $content
      );
      return $content;
    }

    public function mergePdf($pdfFileArr){
        // print_r($pdfFileArr);
        $pdf = new PDFMerger;
        $path1 =  dirname(dirname(__DIR__)).'/';
        $path2 =  dirname(dirname(__DIR__)).'/pdfDoc/';
        if($pdfFileArr){
            foreach($pdfFileArr as $file){
                // print_r($file);exit;
                $pdf->addPDF($path1.$file, 'all');
                // $pdf->addPDF($file, 'all');
            }

            $new_file = md5(time().rand(1,10)) .'.pdf';
            $pdf->merge('file', $path2.$new_file);

        } else {
            $new_file = '';
        }

        // REMOVE TEMPORARY FILES
        if($pdfFileArr){
            foreach($pdfFileArr as $file){
                @unlink($path1.$file);
            }
        }
        unset($pdf); 
        // print_r($new_file);exit;
        return 'pdfDoc/'.$new_file;
    }

    public function createPdfContentHtml_newDesign($content,$dataArray){
        // echo "<pre>"; print_r($dataArray);exit;
        $filepath = "https://apimobile.ring.healthcare:5025/upload/";
        $str ='';     
        // $str .= '<div><table>';
        $str .= '<tr><th class="hleft" width="40%">Diagnosis</th><td >'.$dataArray->Diagnosis.'</td></tr>';
        $str .= '<tr><th class="hleft" width="40%">Date</th><td>'.$dataArray->InsertDate.'</td></tr>';
        $str .= '<tr><th class="hleft" width="40%">Description</th><td>'.$dataArray->Description.'</td></tr>';
        $str .= '<tr><th class="hleft" width="40%">Doctor Name</th><td>'.$dataArray->DoctorName.'</td></tr>';
        $str .= '<tr><th class="hleft" width="40%">Facility Name</th><td>'.$dataArray->TenantName.'</td></tr>';
        $str .= '<tr><th class="hleft" width="40%">Doctor Speciality</th><td>'.$dataArray->DoctorSpeciality.'</td></tr>';
        // $str .= '</table></div><div>';
        $str .= '<tr>';
        if($dataArray->FileArray){
            foreach($dataArray->FileArray as $fileAttchVal){
                $filename = explode("/",$fileAttchVal->Base64Data);
                $filename1 = explode(".",$filename[1]);
                $ext = $filename1[1];
                $fileWithPath = $filepath.$filename[1];
                // $str .= '<h3 style="text-align:center"">'.$fileAttchVal->Category.'</h3>';
                if($ext != "pdf"){   
                    $str .= '<div class="wrapper-table scroll-x margintopcss centerImage">';
                    $str .= '<img src="'.$fileWithPath.'" alt="Report" width="600" height="800">';
                    $str .= '</div>'; 
                }
            }
        }
        $str .= '</tr>';
        $content = str_replace(
         array('VAR_ITEM_DATA'),
         array($str),
         $content
      );
      return $content;
    }

    function encryptDecrypt($type,$name){
        if($type == 'en'){
            $type1 = "Encrypt";
        }else{
            $type1 = "Decrypt";
        }
        $arrayToSend = array('userName'=>$name,'type'=>$type1);
        $url = 'http://sancyberhad.ddns.net/RING_API_TEST/api/Register/EncryptDecrypt';
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

    public function createOnePdfOfAllMRDTRecordsWithAllergy(){
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->UserId;
            $SessionId = $data->SessionId;
            $allergy = isset($data->allergy)?$data->allergy:"";
            $sDate = isset($data->startDate)?$data->startDate:"";
            $eDate = isset($data->endDate)?$data->endDate:"";
            $cat = isset($data->category)?$data->category:"";
			$chkSession = $this->WebserviceModel->chkSession($SessionId,$UserId);
			$reportArr = array();
			$reportFileArr = array();    
            $resultArr = array();
            if($chkSession){
				foreach($chkSession as $chkSessionVal){
					$ID = $chkSessionVal->BackupID;
					/**Find Report and Create Json */
					$ReportData = $this->WebserviceModel->getReportDataForPDFCreation($UserId, $ID);
					if($ReportData)
					{
						foreach($ReportData as $ReportDataVal){
							array_push($reportArr,$ReportDataVal);
						}				
					}		
					$ReportFileData = $this->WebserviceModel->getReportFileDetailsForPDFCreation($UserId, $ID);
					if($ReportFileData)
					{
						foreach($ReportFileData as $ReportFileDataVal){
							array_push($reportFileArr,$ReportFileDataVal);
						}				
					}
				}
                if($reportFileArr)
                {  				
					foreach($reportFileArr as $field1) {
						if(isset($field1->Base64Data) && !empty($field1->Base64Data)){
							$Base64Data = file_get_contents($field1->Base64Data);
                            $Base64Data1 = $this->getImgUrlFromBase64($Base64Data);
							$field1->Base64Data = $Base64Data1;
						}
					}					                            
                }
				if($reportArr)
				{  
                    $i = 0;
					foreach($reportArr as $field) {
						if(isset($field->Data2) && !empty($field->Data2)){
							$data2 = file_get_contents($field->Data2);
							
							$data2_1 = $this->getImgUrlFromBase64($data2);
							$field->Data2 = $data2_1;
						}
                        $ReportId = $field->BackupReportId;
                        $fileArr = $this->getdataById($reportFileArr, $ReportId);
                        $field->FileArray = $fileArr;
						array_push($resultArr,$field);
                        $i++;
					}                           
				}
                $pdfresultArr = array();
                if($resultArr)
				{ 
                    $createPdf = $this->createPdfWithAllergy($UserId,$resultArr,$allergy,$sDate,$eDate,$cat);
                }

            }
            if($createPdf)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $createPdf;
                $response['fileWithPath'] = "https://apimobile.ring.healthcare:5025/".$createPdf;
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
        echo json_encode($response); exit;
    }

    function compareByTimeStamp($a, $b)
    {
        // print_r($a);exit;
        $time1 = strtotime($a->InsertDate);
        
        $time2 = strtotime($b->InsertDate);
        if ($time1 < $time2){
            return 1; 
        }else if ($time1 > $time2){
            return -1; 
        }else{
            return 0;
        }
    }

    function createPdfWithAllergy($UserId,$dataArray,$allergy,$sDate,$eDate,$cat){
        usort($dataArray, 'compareByTimeStamp');
        // echo "<pre>"; print_r($dataArray);exit;
        $fileArr = array();
        $i = 0;
        foreach($dataArray as $key=>$dataValue){
            // print_r($key);
            $filepath = "https://apimobile.ring.healthcare:5025/upload/";
            $template_name = 'template/file_merge_template_new.html';
            // $template_name = 'template/ring.html';
            $content = file_get_contents($template_name);

            $this->m_pdf->pdf->SetHTMLHeader('<table style="width:100%; margin-top:0; border-collapse: collapse;">
            <tr style="background: #03625c; border-collapse: collapse;">
                <td style="text-align: center; border-collapse: collapse;">
                    <div>
                        <img src="https://apimobile.ring.healthcare:5025/assets/logo/ring_logo.png" width="100" />
                    </div>
                </td>
                
            </tr>
            </table>');
            $this->m_pdf->pdf->SetHTMLFooter('<table style="width:100%; margin-top:2rem;">
            <tr>
                <td style="background: #03625c; text-align: center;">
                    <div>
                        <img src="https://apimobile.ring.healthcare:5025/assets/logo/ring_logo.png" width="100" />
                    </div>
                </td>
            </tr>
            </table>');
            $this->m_pdf->pdf->AddPage('', '', '', '', '',
            20, // margin_left
            20, // margin right
            25, // margin top
            35, // margin bottom
            5, // margin header
            5); // margin footer
            if($key == 0){
                $userData =  $this->WebserviceModel->getUserDataById($UserId);
                $userAdd =  $this->WebserviceModel->getPatientAddressById($UserId);
                $allergyData =  $allergy;
                $pdfInfo = '<p><b>Report Date</b> -  '.$sDate.' To '.$eDate.'   <b>Created Date</b> - '.date("d M Y").'  <b>Category</b> - '.$cat.' </p>';
            }else{
                $userData = "";
                $allergyData = "";
                $pdfInfo = "";
                $userAdd = "";
            }
            
            $content = $this->createPdfContentHtmlWithAllergy($content,$dataValue,$userData,$allergyData,$pdfInfo,$userAdd);
            // $content = $this->createPdfContentHtml_newDesign($content,$dataValue);
            $path =  dirname(dirname(__DIR__)).'/pdfDoc/';
            $pdfName = time().rand(1,10).'_mergedFile_'.$UserId.'.pdf';
            if($content){

                // echo $content ;
                // echo "-----------------------------------";
                $this->m_pdf->pdf->WriteHTML($content);
                $this->m_pdf->pdf->Output($path.$pdfName, "F");
                $pdfPath =$root.'pdfDoc/'.$pdfName;
                unset($this->m_pdf); 
                $this->load->library('m_pdf');
                
                array_push($fileArr,$pdfPath);
                foreach($dataValue->FileArray as $files){ 
                    $filename = explode("/",$files->Base64Data);
                    $filename1 = explode(".",$filename[1]);
                    $ext = $filename1[1];
                    if($ext == "pdf"){ 
                        array_push($fileArr,$files->Base64Data);
                    }
                }
                
            }else{
                return null;
            }   
            $i++;   
        }
         $return = $this->mergePdf($fileArr);
        //  echo "<pre>"; print_r($return);exit;
         return $return;
        
    }

    public function createPdfContentHtmlWithAllergy($content,$dataArray,$userData,$allergy,$pdfInfo,$userAdd){
        // echo "<pre>"; print_r($userData);exit;
        $filepath = "https://apimobile.ring.healthcare:5025/upload/";    
        $str ='';
        if(isset($pdfInfo) && !empty($pdfInfo)){                     
            // $str .= '<tr><th> Report Details</th>';
            $str .= '<div style="color:black; margin-top:3rem;">'.$pdfInfo.'</div>';
            // $str .= '</tr>';
        }
        $str .= '<div><table style="">';
        
        if(isset($userData) && !empty($userData)){
            $userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);   
            $userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
            $userData->Email = $this->encryptDecrypt("dc",$userData->Email); 
            if($userData->Address){
                $add1 = rtrim($userData->Address,"+");
                $add = str_replace("+",", ",$add1);
            }else{
                $add = "";
            }
            if($userData->PinCode){ $PinCode = ', '.$userData->PinCode; }else{ $PinCode = ""; }                      
            $str .= '<tr><th>Patient Info</th><td>Name - '.$userData->FullName.'<br>';
            $str .= 'Mobile Number - '.$userData->MobileCode.' '.$userData->MobileNumber.'<br>';
            $str .= 'Email ID - '.$userData->Email.'<br>';
            $str .= 'Blood Group - '.$userData->bloodgroup.'<br>';
            if(isset($userAdd) && !empty($userAdd)){
                if($userAdd->City){ $City = ', '.$userAdd->City; }else{ $City = ""; }
                if($userAdd->State){ $State = ', '.$userAdd->State; }else{ $State = ""; }
                if($userAdd->Country){ $Country = ', '.$userAdd->Country; }else{ $Country = ""; }
                
                $str .= 'Address - '.$add.$City.$State.$Country.$PinCode.'</td></tr>';
            }else{
                $str .= 'Address - '.$add.$PinCode.'</td></tr>';
            }
        }
        if(isset($allergy) && !empty($allergy)){                     
            $str .= '<tr><th> Allergy</th><td>';
            foreach($allergy as $allergyVal){
                $str .= $allergyVal->medicine_name.' - '.$allergyVal->allergy_effect.'<br>';
            }
            $str .= '</td></tr>';
        }
        
        if($dataArray->ReportUploadType == "online"){
            $str .= '<tr><th>Diagnosis</th><td>'.$dataArray->Diagnosis.'</td></tr>';
            $date = date_create($dataArray->InsertDate);
            // $InsertDate = date_format($date,"d M Y h:i A");
            $timestamp = $dataArray->CreateTime;
            $InsertDate = date('d M Y h:i A', $timestamp/1000);
            $str .= '<tr><th>Date</th><td>'.$InsertDate.'</td></tr>';
            $str .= '<tr><th>Description</th><td>'.$dataArray->Description.'</td></tr>';
            $str .= '<tr><th>Doctor Name</th><td>'.$dataArray->DoctorName.'</td></tr>';
            $str .= '<tr><th>Facility Name</th><td>'.$dataArray->TenantName.'</td></tr>';
            $str .= '<tr><th>Doctor Speciality</th><td>'.$dataArray->DoctorSpeciality.'</td></tr>';
            $str .= '</table></div><div>';
            if($dataArray->FileArray){
                foreach($dataArray->FileArray as $fileAttchVal){
                    $filename = explode("/",$fileAttchVal->Base64Data);
                    $filename1 = explode(".",$filename[1]);
                    $ext = $filename1[1];
                    $fileWithPath = $filepath.$filename[1];
                    if($ext != "pdf"){ 
                        $str .= '<div class="wrapper-table scroll-x margintopcss centerImage">';
                        $str .= '<img src="'.$fileWithPath.'" alt="Report" width="600" height="800">';
                        $str .= '</div>'; 
                    }
                }
            }
        }else if($dataArray->ReportUploadType == "local"){
            $date = date_create($dataArray->InsertDate);
            // $InsertDate = date_format($date,"d M Y h:i A");
            $timestamp = $dataArray->CreateTime;
            $InsertDate = date('d M Y h:i A', $timestamp/1000);
            $str .= '<tr><th>Date</th><td>'.$InsertDate.'</td></tr>';
            $str .= '<tr><th>Description</th><td>'.$dataArray->Description.'</td></tr>';
            $str .= '<tr><th>Upload Type</th><td>Self</td></tr>';
            $str .= '</table></div><div>';
            if($dataArray->Data2){
                    $filename = explode("/",$dataArray->Data2);
                    $filename1 = explode(".",$filename[1]);
                    $ext = $filename1[1];
                    $fileWithPath = $filepath.$filename[1];
                    if($ext != "pdf"){ 
                        $str .= '<div class="wrapper-table scroll-x margintopcss centerImage">';
                        $str .= '<img src="'.$fileWithPath.'" alt="Report" width="600" height="800">';
                        $str .= '</div>'; 
                    }
            }
        }

        $content = str_replace(
         array('VAR_ITEM_DATA'),
         array($str),
         $content
      );
      return $content;
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
            $randnum = rand(11111111,99999999);
            $file_name = $randnum.'_IMG_'.time();
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
              $response['file_ext'] = $addExt;
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


    public function pdfCreationForMedicineAndVaccination($content,$userData,$allergy,$pdfInfo,$userAdd,$medicine,$vaccination){
        // echo "<pre>"; print_r($pdfInfo);exit;
        $filepath = "https://apimobile.ring.healthcare:5025/upload/";    
        $str ='';
        if(isset($pdfInfo) && !empty($pdfInfo)){                     
            // $str .= '<tr><th> Report Details</th>';
            $str .= '<div style="color:black; margin-top:3rem;">'.$pdfInfo.'</div>';
            // $str .= '</tr>';
        }
        $str .= '<div><table style="">';
        
        if(isset($userData) && !empty($userData)){
            $userData->MobileNumber = $this->encryptDecrypt("dc",$userData->MobileNumber);   
            $userData->FullName = $this->encryptDecrypt("dc",$userData->FullName);
            $userData->Email = $this->encryptDecrypt("dc",$userData->Email); 
            if($userData->Address){
                $add1 = rtrim($userData->Address,"+");
                $add = str_replace("+",", ",$add1);
            }else{
                $add = "";
            }
            if($userData->PinCode){ $PinCode = ', '.$userData->PinCode; }else{ $PinCode = ""; }                      
            $str .= '<tr><th>Patient Info</th><td>Name - '.$userData->FullName.'<br>';
            $str .= 'Mobile Number - '.$userData->MobileCode.' '.$userData->MobileNumber.'<br>';
            $str .= 'Email ID - '.$userData->Email.'<br>';
            $str .= 'Blood Group - '.$userData->bloodgroup.'<br>';
            if(isset($userAdd) && !empty($userAdd)){
                if($userAdd->City){ $City = ', '.$userAdd->City; }else{ $City = ""; }
                if($userAdd->State){ $State = ', '.$userAdd->State; }else{ $State = ""; }
                if($userAdd->Country){ $Country = ', '.$userAdd->Country; }else{ $Country = ""; }
                
                $str .= 'Address - '.$add.$City.$State.$Country.$PinCode.'</td></tr>';
            }else{
                $str .= 'Address - '.$add.$PinCode.'</td></tr>';
            }
        }
        if(isset($allergy) && !empty($allergy)){                     
            $str .= '<tr><th> Allergy</th><td>';
            foreach($allergy as $allergyVal){
                $str .= $allergyVal->medicine_name.' - '.$allergyVal->allergy_effect.'<br>';
            }
            $str .= '</td></tr>';
        }
        if(isset($medicine) && !empty($medicine)){                     
            $str .= '<tr><th> Medicine</th><td>';
            foreach($medicine as $medicineVal){
                $med_startdate = date_create($medicineVal->sDate);
                $medStartdate = date_format($med_startdate,"d M Y");
                $med_enddate = date_create($medicineVal->eDate);
                $medEnddate = date_format($med_enddate,"d M Y");
                $str .= $medicineVal->medicine.'<br>';
                $str .= $medStartdate.' To '.$medEnddate.' - '.$medicineVal->dosage.'<br><br>';
            }
            $str .= '</td></tr>';
        }    
        if(isset($vaccination) && !empty($vaccination)){                     
            $str .= '<tr><th> Vaccination</th><td>';
            foreach($vaccination as $vaccinVal){                
		$vacDate = explode('/', $vaccinVal->Vaccination_date);
		$month = $vacDate[1];
		$day   = $vacDate[0];
		$year  = $vacDate[2];
		$vaccsDate = $year."-".$month."-".$day;
                $Vaccination_date = date_create($vaccsDate);
                $VaccinationDate = date_format($Vaccination_date,"d M Y");
                $str .= $vaccinVal->vaccination_name.'<br>';
                $str .= $VaccinationDate.'<br><br>';
            }
            $str .= '</td></tr>';
        }   
        $str .= '</table></div><div>';
        $content = str_replace(
         array('VAR_ITEM_DATA'),
         array($str),
         $content
      );
      return $content;
    }

    function createPdfForMedicineVaccinAndAllergy($UserId,$allergy,$medicine,$vaccination){
            $filepath = "https://apimobile.ring.healthcare:5025/upload/";
            $template_name = 'template/file_merge_template_new.html';
            // $template_name = 'template/ring.html';
            $content = file_get_contents($template_name);

            $this->m_pdf->pdf->SetHTMLHeader('<table style="width:100%; margin-top:0; border-collapse: collapse;">
            <tr style="background: #03625c; border-collapse: collapse;">
                <td style="text-align: center; border-collapse: collapse;">
                    <div>
                        <img src="https://apimobile.ring.healthcare:5025/assets/logo/ring_logo.png" width="100" />
                    </div>
                </td>
                
            </tr>
            </table>');
            $this->m_pdf->pdf->SetHTMLFooter('<table style="width:100%; margin-top:2rem;">
            <tr>
                <td style="background: #03625c; text-align: center;">
                    <div>
                        <img src="https://apimobile.ring.healthcare:5025/assets/logo/ring_logo.png" width="100" />
                    </div>
                </td>
            </tr>
            </table>');
            $this->m_pdf->pdf->AddPage('', '', '', '', '',
            20, // margin_left
            20, // margin right
            25, // margin top
            35, // margin bottom
            5, // margin header
            5); // margin footer
            $userData =  $this->WebserviceModel->getUserDataById($UserId);
            $userAdd =  $this->WebserviceModel->getPatientAddressById($UserId);
            $allergyData =  $allergy;
            $pdfInfo = 'Created Date - '.date("d M Y").'  ';
                       
            $content = $this->pdfCreationForMedicineAndVaccination($content,$userData,$allergy,$pdfInfo,$userAdd,$medicine,$vaccination);
            // $content = $this->createPdfContentHtml_newDesign($content,$dataValue);
            $path =  dirname(dirname(__DIR__)).'/pdfDoc/';
            $pdfName = time().rand(1,10).'_mergedFile_'.$UserId.'.pdf';
            if($content){

                // echo $content ;
                // echo "-----------------------------------";
                $this->m_pdf->pdf->WriteHTML($content);
                $this->m_pdf->pdf->Output($path.$pdfName, "F");
                $pdfPath =$root.'pdfDoc/'.$pdfName;
                unset($this->m_pdf); 
                $this->load->library('m_pdf');
            }else{
                return null;
            }   
         return $pdfPath;       
    }

    public function createOnePdfOfAllMRDTRecordsWithAllergyAndMore(){
        $data = json_decode(file_get_contents('php://input'));
        $root = (isset($_SERVER['HTTPS']) ? "http://" : "http://") . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        if($data)
        {
            $UserId = $data->UserId;
            $allergy = isset($data->allergy)?$data->allergy:"";
            $medicine = isset($data->medicine)?$data->medicine:"";
            $vaccination = isset($data->vaccination)?$data->vaccination:"";
            $createPdf = $this->createPdfForMedicineVaccinAndAllergy($UserId,$allergy,$medicine,$vaccination);
            if($createPdf)
            {
                $response['response_code'] = '1';
                $response['response_message'] = 'Success';
                $response['response_data'] = $createPdf;
                $response['fileWithPath'] = "https://apimobile.ring.healthcare:5025/".$createPdf;
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
        echo json_encode($response); exit;
    }
}