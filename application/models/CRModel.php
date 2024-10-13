<?php
class CRModel extends CI_Model
{
	public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }


    function userRegistration($data)
    { 
        $result = $this->db->insert("PatientMaster",$data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function checkExistUser($mob_code,$mobileno,$email){
        $this->db->select("*");
        $this->db->from('PatientMaster');
        $this->db->where('MobileCode', $mob_code);
        $this->db->where('MobileNumber', $mobileno);
        $this->db->or_where('Email', $email);
        $result = $this->db->get()->row();
        return $result;
    }

    function insertData($data,$tableName)
    { 
        $result = $this->db->insert($tableName,$data);
        $insert_id = $this->db->insert_id();       
        return $insert_id;
    }

    function selectCRUserData($id)
    { 
        $this->db->select("*");
        $this->db->from('CloseRingUser');
        $this->db->where('UniversalID', $id);
        $result = $this->db->get()->row();
        return $result;
    }

    function chkSameDevice($deviceId)
    { 
        $this->db->select("*");
        $this->db->from('CloseRingUser');
        $this->db->where('DeviceId', $deviceId);
        $result = $this->db->get()->row();
        return $result;
    }

    function clearDeviceId($UniversalID){
        $this->db->set("DeviceId", NULL);
        $this->db->set("DeviceToken", NULL);
        $this->db->set('LoginPlatform', NULL);
        $this->db->set('UpdateDate', date("Y-m-d H:i:s"));
        $this->db->where('UniversalID', $UniversalID);
        return $this->db->update("CloseRingUser");
    }

    function chkCRUser($userId,$ringGroup){
        $this->db->select("*");
        $this->db->from('CloseRingUser');
        $this->db->where('UserId', $userId);
        $this->db->where('RingGroup', $ringGroup);
        $result = $this->db->get()->row();
        return $result;
    }
    
    public function updateCRtoken($universalId,$deviceId,$platform,$deviceToken){
        if($deviceId == null)
        {
            return false;
        }
        else
        {
            $this->db->set('DeviceId', $deviceId);
            $this->db->set('DeviceToken', $deviceToken);
            $this->db->set('LoginPlatform', $platform);
            $this->db->set('UpdateDate', date("Y-m-d H:i:s"));
            $this->db->where('UniversalID', $universalId);
            return $this->db->update("CloseRingUser");
        }
    }

    function checkUniLoginDevice($deviceId,$source){
        $this->db->select("PatientId,DeviceId")
        ->from("Notification")
        ->where('DeviceId', $deviceId)
        ->where('Platform', $source);
        $result = $this->db->get()->row();
        return $result;
    }
    
    function checkCRLoginDevice($deviceId,$source){
        $this->db->select("*")
        ->from("CloseRingUser")
        // ->where('UniversalID', $uniId)
        ->where('DeviceId', $deviceId)
        ->where('LoginPlatform', $source);
        $result = $this->db->get()->row();
        // echo $this->db->last_query();exit;
        return $result;
    }

    public function getUserData($UserId){
        $this->db->select('*');
        $this->db->from('PatientMaster');
        $this->db->where('PatientId',$UserId);
        $result = $this->db->get()->row();
        // echo $this->db->last_query();exit;
        return $result;
    }

    public function fetchReportDataForSync($UniversalID){
        $this->db->select('*');
        $this->db->from('CloseRingReports');
        $this->db->where('UniversalID',$UniversalID);
        $result = $this->db->get()->result();
        return $result;
    }

    public function getUseridByUniversal($UniversalID){
        $this->db->select('UserId');
        $this->db->from('CloseRingUser');
        $this->db->where('UniversalID',$UniversalID);
        $result = $this->db->get()->row();
        return $result;
    }

    public function fetchFileDataForSyncByReportId($ReportId){
        $this->db->select('*');
        $this->db->from('CloseRingFiles');
        $this->db->where('CRReportId',$ReportId);
        $result = $this->db->get()->result();
        return $result;
    }

    public function getRingGroupID($ringGrp)
    { 
		$this->db->select('RingGroupMasterId,RingGroupName');
		$this->db ->from('RingGroupMaster');
		$this->db->where('RingGroupName', $ringGrp);
		$result = $this->db ->get()->row();
		return $result;
    }

    public function ringGrpUpdate($patient,$ringGroupID){
        $this->db->set('EreferralForm', "NULL");
        $this->db->where('ReportTransitId',$reportTransitId);
        $this->db->where('PatientMasterId',$patientId);
        // $this->db->where('IsPatientProcessed',1);
        $result =  $this->db->update('EreportsTransit');
        return $result;
    }
}