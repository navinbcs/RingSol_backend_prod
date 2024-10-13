<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct()
  {
    parent::__construct();
    $this->load->library('session'); 
  }
	
	public function index()
	{
    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
      redirect(base_url().'index.php/ringWeb/home/fileUploadPage');
    }else{
      $this->load->view('pages/home');
    }       
	}
}
?>