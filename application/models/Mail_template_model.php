<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mail_template_model extends CI_Model {
	public function __construct()
	{
			// Call the CI_Model constructor
			parent::__construct();
			$this->userData = $this->session->userdata('userinfo');
	}
	
	public function getTemplate($name){
		$this->db->select('Description,Subject,Createddate');
		$this->db->from('Mailtemplate');
		$this->db->where('Name',$name);
		$query = $this->db->get();
		return ( $query->num_rows() > 0 ) ? $query->row(): NULL;
	}
}