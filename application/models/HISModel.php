<?php
class HISModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

/****************************************************************************/
    public function checkUser($userName,$password){
	$query = $this->db->query("SELECT * FROM HISIntegration WHERE UserName='".$userName."' AND Password='".$password."' ");
        $result = $query->row();
        return $result;
    }

    function facilitySearchByKeywords($keyword){
        $this->db->select("T.TenantId, T.TenantName, T.TenantCode, T.TenantNumber, T.PhoneCode, T.PhoneNumber, T.FaxCode, T.FaxNumber, T.Address, T.PostCode, T.TenantTypeId, TT.TenantType,T.CountryID, C.CountryDescription Country,T.StateID, S.StateDescription State,T.CityID, CT.CityDescription City");
        $this->db->from("Tenants T");
        $this->db->join("CountryMaster C","C.ID = T.CountryID","LEFT");
        $this->db->join("StateMaster S","S.ID = T.StateID","LEFT");
        $this->db->join("CityMaster CT","CT.ID = T.CityID","LEFT");
        $this->db->join("TenantType TT","TT.TenantTypeId = T.TenantTypeId","LEFT");
        $this->db->like('T.TenantName', $keyword);
        $this->db->where('T.IsActive',1);
        $this->db->limit(5);
        $result = $this->db ->get()->result();
        return $result;
    }

    function facilityTypeData(){
        $this->db->select("TenantTypeId FacilityTypeId, TenantType FacilityType");
        $this->db->from("TenantType");
        $result = $this->db ->get()->result();
        return $result;
    }

    function insertIntegrationData($insertData)
    { 
        $result = $this->db->insert("HISIntegration",$insertData);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function saveFacilityData($facilityInsertArray)
    { 
        $result = $this->db->insert("Tenants",$facilityInsertArray);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function checkHospitalByName($facilityName)
    {
        $this->db->select('TenantId');
        $this->db->from('Tenants');
        $this->db->like('TenantName', $facilityName);
        $result = $this->db ->get()->row();
        return $result; 
    }

    function GetStateMaster($countryId)
    {
        $this->db->select('Id,CountryId,StateCode,StateDescription');
        $this->db->from('StateMaster');
        $this->db->where('CountryId', $countryId);
        $result = $this->db ->get()->result();
        return $result; 
    }

    function GetCityMaster($stateId)
    {
        $this->db->select('Id,StateId,CityDescription');
        $this->db->from('CityMaster');
        $this->db->where('StateId', $stateId);
        $result = $this->db ->get()->result();
        return $result; 
    }

    public function updateOtpData($otp,$id){
        $this->db->set('Otp',$otp);
        $this->db->where('ID',$id);
        $result = $this->db->update('HISIntegration');
        return $result;
    }

    function getIntegratedUserList(){        
        $this->db->select('H.*, TT.TenantType, C.CountryDescription Country, S.StateDescription State, CT.CityDescription City');
        $this->db ->from('HISIntegration H');
        $this->db->join("Tenants T","T.TenantId = H.FacilityId","LEFT");
        $this->db->join("CountryMaster C","C.ID = T.CountryID","LEFT");
        $this->db->join("StateMaster S","S.ID = T.StateID","LEFT");
        $this->db->join("CityMaster CT","CT.ID = T.CityID","LEFT");
        $this->db->join("TenantType TT","TT.TenantTypeId = T.TenantTypeId","LEFT");
        $this->db->where('H.IsAdmin',0);
	$result = $this->db ->get()->result();
	return $result;
    }
}