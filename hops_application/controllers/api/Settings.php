<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . 'libraries/REST_Controller.php';

class Settings extends REST_Controller {

	/* Constructor */
	public function __construct()
    {
        parent::__construct();
        $this->load->library('../controllers/api/common/');
        $this->load->model('api/settings_model');
    }

    // Settings view
	public function settings_view_get($arg=array())
	{
		
      	$data = $arg;

		if(!empty($data)) {

			$settings_data = $this->settings_model->user_settings_view($data['user_id']);
			$password_exist = $this->settings_model->check_password_exist($data['user_id']);

			if(!empty($password_exist)) {
				$settings_data['password_exist'] = TRUE;
			}
			else {
				$settings_data['password_exist'] = FALSE;
			}

			$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_list_success'),'server_data'=>$settings_data);
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Settings update
	public function settings_update_post($arg=array())
	{
		
      	$data = json_decode(file_get_contents('php://input'),true);

		if(!empty($data)) {

			$user_id = $arg['user_id'];
			$data['user_settings_updated_date'] = date('Y-m-d H:i:s');

			$settings_update = $this->settings_model->user_settings_update($user_id,$data);

			$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_settings_update'));
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Change password
	public function change_password_post($arg=array())
	{
		
		$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) {

			$user_id = $arg['user_id'];

			$password_exist = $this->settings_model->check_password_exist($user_id);

			if(!empty($password_exist)) {

				$old_password = (!empty($data['old_password'])) ? $this->common->custom_encrypt($data['old_password']) : '';

				if($old_password == $password_exist['user_password']) {

					$new_password = $this->common->custom_encrypt($data['new_password']);
					$password_update = $this->settings_model->update_password($user_id,$new_password);
					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_reset_password'));
				}
				else {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_password_invalid'));
				}
			}
			else {
				$new_password = $this->common->custom_encrypt($data['new_password']);
				$password_update = $this->settings_model->update_password($user_id,$new_password);
				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_reset_password'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

}
// End of profile controller - api