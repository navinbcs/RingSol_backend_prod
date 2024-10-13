<?php
if (!function_exists('check_login_user')) {
	    function check_login_user() {
	        $ci = get_instance();
	        if ($ci->session->userdata('logged_in') != TRUE) {
	            $ci->session->sess_destroy();
	            redirect(base_url().'index.php/ringWeb/Login');
	        }
	    }
	}
?>