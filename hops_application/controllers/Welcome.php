<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function page_not_found()
	{
		$response = array('status' => FALSE,'status_code'=>HTTP_NOT_FOUND,'message' => 'API not found');
		echo json_encode($response);
	}

}
// End of welcome controller
