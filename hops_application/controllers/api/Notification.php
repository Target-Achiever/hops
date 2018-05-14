<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . 'libraries/REST_Controller.php';

class Notification extends REST_Controller {

	/* Constructor */
	public function __construct()
    {
        parent::__construct();
        $this->load->library('../controllers/api/common/');
        $this->load->model('api/notification_model');
    }

    // Notification list
	public function notification_list_get($arg=array())
	{
		
      	$data = $arg;

		if(!empty($data)) {

			$notification_list = $this->notification_model->get_notification_list($data['user_id']);

			if(!empty($notification_list)) {
				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'server_data'=>$notification_list,'message'=>$this->lang->line('text_list_success'));
			}
			else {
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_no_records'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Notification view
	public function notification_view_get($arg=array())
	{
		
      	$data = $this->input->get();

		if(!empty($data)) {

			$data['user_id'] = $arg['user_id'];

			$notification_details = $this->notification_model->get_notification_view_details($data);

			if(!empty($notification_details)) {

				$update_notification_status = $this->notification_model->update_notification_status($data['notification_id']);

				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'server_data'=>$notification_details,'message'=>$this->lang->line('text_list_success'));
			}
			else {
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_no_records'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	

}
// End of notification controller - api