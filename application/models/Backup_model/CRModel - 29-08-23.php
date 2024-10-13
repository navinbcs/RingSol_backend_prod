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

    function chkCRUser($userId,$ringGroup){
        $this->db->select("*");
        $this->db->from('CloseRingUser');
        $this->db->where('UserId', $userId);
        $this->db->where('RingGroup', $ringGroup);
        $result = $this->db->get()->row();
        return $result;
    }
    
    public function updateCRtoken($universalId,$deviceId,$platform){
        if($deviceId == null)
        {
            return false;
        }
        else
        {
            $this->db->set('DeviceId', $deviceId);
            $this->db->set('LoginPlatform', $platform);
            $this->db->set('UpdateDate', date("Y-m-d H:i:s"));
            $this->db->where('UniversalID', $universalId);
            return $this->db->update("CloseRingUser");
        }
    }

}