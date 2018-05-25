<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller {

	/* Constructor */
	public function __construct()
    {
    	parent::__construct();
    	$this->load->model('ajax_model');
    	$this->load->library('form_validation');
    	$this->load->helper('common');
    	//Load email library 
    	$this->load->library('email');
        
  //       if (!$this->input->is_ajax_request()) {
  //   		exit('No direct script access allowed');
		// }
	}

	
	// Contact us
	public function contact_us_test()
	{
		$this->load->view('contact');
		
	}

    // Contact us
	public function contact_us()
	{
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');

		$validation_rules = array(
								array(
									'field' => 'name',
									'label' => 'name',
									'rules' => 'required|max_length[100]|alpha'
								),
								array(
									'field' => 'email',
									'label' => 'Email',
									'rules' => 'required|valid_email'
								)
							);

		$this->form_validation->set_rules($validation_rules);

		if ($this->form_validation->run() == FALSE) {
			
			$message = '';
			foreach ($validation_rules as $field) {
					
				$error_msg = form_error($field['field']);

				if(!empty($error_msg)) {
					$message = $error_msg;
					break;
				}
			}

			$ajax_response = array('status'=>false,'message'=>$message);
		}
		else {

			$mail_data['subject'] = 'GET IN TOUCH';
			$mail_data['message'] = 'testtt';

			$mail_send = send_mail_admin($mail_data);










			$ajax_response = array('status'=>true,'message'=>'success');
		}

		echo json_encode($ajax_response);
	}

	// Package cost
	public function get_package_cost()
	{
		$data = $this->input->post();

		if(!empty($data['pickup_lat']) && !empty($data['pickup_long']) && !empty($data['drop_lat']) && !empty($data['drop_long']) && !empty($data['package_size'])) {


        	$distance = 3956 * 2 * asin(sqrt(pow(sin(($data['pickup_lat'] - $data['drop_lat']) * pi()/180 / 2), 2) + cos($data['pickup_lat'] * pi()/180) * cos($data['drop_lat'] * pi()/180) *pow(sin(($data['pickup_long'] - $data['drop_long']) * pi()/180 / 2), 2) ));

        	$package_cost = $distance * 10;

        	$ajax_response = array('status'=>true,'cost'=>$package_cost);
		}
		else {
			$ajax_response = array('status'=>false,'message'=>'unable to get package cost');
		}

		echo json_encode($ajax_response);
	}

	


}

// End of ajax controller
