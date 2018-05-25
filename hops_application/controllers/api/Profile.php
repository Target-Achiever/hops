<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . 'libraries/REST_Controller.php';

class Profile extends REST_Controller {

	/* Constructor */
	public function __construct()
    {
        parent::__construct();
        $this->load->library('../controllers/api/common/');
        $this->load->model('api/profile_model');
    }

    // Profile view
	public function profile_view_get($arg=array())
	{
		
      	$data = $arg;

		if(!empty($data)) {

			$user_det = $this->profile_model->user_full_details($data['user_id']);

			if(!empty($user_det)) {
				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_list_success'),'server_data'=>$user_det);
			}
			else {
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_no_records'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Profile update
	public function profile_update_post($arg=array())
	{
		
		$data = $this->input->post();

		if(!empty($data)) {

			$user_id = $arg['user_id'];
			$file_path = "";

			$user_cat = $arg['user_category'];

			if(!empty($_FILES['user_profile_image'])) {

				$file_ext = ".".strtolower(end((explode('.',$_FILES['user_profile_image']['name']))));
				$file_name   = time().mt_rand(000,999).$file_ext;
				$config['upload_path'] = "./".UPLOADS."profile/";
			    $config['allowed_types'] = '*';
				$config['file_name']   = $file_name;
				$this->load->library('upload', $config);

				if ($this->upload->do_upload('user_profile_image')) {
					$filepath = $config['upload_path'].$file_name;
					$create_thumb = $this->common->create_thumb($filepath);
					$file_path = str_replace("./", "", $filepath);
     			}
     			$update_data['user_details']['user_profile_image'] = $file_path;

     			// Delete original profile image
     			if(!empty($data['pre_profile_image']) && file_exists("./".$data['pre_profile_image']))
 				{
 					unlink("./".$data['pre_profile_image']);
		 			// Delete thumbnail of profile image
		 			// $file_ext = ".".end((explode('.',$data['pre_profile_image'])));
		 			// $unlink_thumb = str_replace($file_ext, "_thumb$file_ext", $data['pre_profile_image']);
		 			// if(file_exists($unlink_thumb)) unlink($unlink_thumb);
 				}
			}

			// Update data for user basic details
			$update_data['user_details']['user_fullname'] = $data['user_fullname'];
			
			// Update data for address
			$update_data['user_address']['user_address1'] = $data['user_address1'];
			$update_data['user_address']['user_address2'] = $data['user_address2'];
			$update_data['user_address']['user_city'] = $data['user_city'];
			$update_data['user_address']['user_zipcode'] = $data['user_zipcode'];
			$update_data['user_address']['user_country'] = $data['user_country'];

			if($user_cat == 2) {
				// Update data for account
				$update_data['user_account']['user_account_name'] = $data['user_account_name'];
				$update_data['user_account']['account_iban'] = $data['account_iban'];
				$update_data['user_account']['account_swift'] = $data['account_swift'];
				$update_data['user_account']['account_details'] = $data['account_details'];
			}

			if(!empty($data['mobile_status']) && $data['mobile_status'] == 1) {

				$update_otp['user_otp'] = mt_rand(111111,999999);
				$update_otp['user_otp_sent_date'] = date('Y-m-d H:i:s');
				$text = "To+confirm+your+mobile+number+using+OTP+".$update_otp['user_otp'];
				$tomobile = $data['user_mobile'];
				$update_data['user_details']['user_mobile_secondary'] = $data['user_mobile'];

				$verification = $this->common->send_sms($tomobile,$text);

				// Update profile
				$update_profile = $this->profile_model->update_profile($user_id,$update_data);

				// verification
				if($verification) {
						
					$otp_update = $this->profile_model->update_otp($user_id,$update_otp);
					$response = array('status' => TRUE,'status_code'=>HTTP_OK,'message'=>$this->lang->line('text_resend_otp'), 'server_data'=>array('otp'=>$update_otp['user_otp']));
				}
				else {
					$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER,'message'=> $this->lang->line('text_sms_error'));
				}
			}
			else {
				
				// Update profile
				$update_profile = $this->profile_model->update_profile($user_id,$update_data);

				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'server_data'=>array('user_profile_image'=>$file_path),'message' => $this->lang->line('text_profile_update'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// User category update
	public function assign_role_post($arg=array())
	{
		
		$data = json_decode(file_get_contents('php://input'),true);

		if(!empty($data)) {

			$user_id = $arg['user_id'];

			$update_role = $this->profile_model->update_user_category($user_id,$data);

			if($update_role) {
				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_role_assign'));
			}
			else {
				$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER,'message' => $this->lang->line('text_query_error'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Update user location
	public function location_update_post($arg=array())
	{
		
		$data = json_decode(file_get_contents('php://input'),true);

		if(!empty($data)) {

			$user_id = $arg['user_id'];

			$update_location = $this->profile_model->update_user_location($user_id,$data);

			$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_location_update'));
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Insert card details
	public function card_details_save_post($arg=array())
	{
		
		$data = json_decode(file_get_contents('php://input'),true);

		if(!empty($data)) {

			$data['user_id'] = $arg['user_id'];

			$save_card = $this->profile_model->insert_card_details($data);

			if($save_card['status'] == "true") {
				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_save_card'));
			}
			else {
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message' => $this->lang->line('text_save_card_error'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Get saved cards list
	public function saved_cards_list_get($arg=array())
	{
		
		$data = $arg;

		if(!empty($data)) {

			$data['user_id'] = $arg['user_id'];

			$saved_cards = $this->profile_model->saved_cards_list($data['user_id']);

			if(!empty($saved_cards)) {
				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'server_data'=>$saved_cards,'message' => $this->lang->line('text_list_success'));
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

	// Delete saved card details
	public function card_details_delete_get($arg=array())
	{
		
		$data = $this->input->get();

		if(!empty($data)) {

			$data['user_id'] = $arg['user_id'];

			$delete_card = $this->profile_model->delete_card_details($data['card_details_id']);

			if($delete_card) {
				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_delete_success'));
			}
			else {
				$response = array('status' => FALSE,'status_code'=>HTTP_SERVER,'message' => $this->lang->line('text_query_error'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Otp verification
	public function profile_otp_verification_get($arg=array())
	{
		
		$data = $this->input->get();

		if(!empty($data)) {

			$user_id = $arg['user_id'];
			$current_mobile_number = $arg['user_mobile'];
			$otp = $data['otp'];

			$check_otp = $this->profile_model->check_otp($user_id,$otp);

			if(!empty($check_otp)) {

				// OTP validation for maximum one minute
				$current_timeF = date('Y-m-d H:i:s');
				$cTime  = strtotime($current_timeF);
				$otpTime = strtotime($check_otp['user_otp_sent_date']);
				$differenceInSeconds = $cTime - $otpTime;
				
				// Check the total minutes exceeds two minutes
				if($differenceInSeconds > 120) 
				{
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_otp_expired'));
					$this->response($response);
				}

				$update_mobile = $this->profile_model->update_user_mobile($user_id,$current_mobile_number);

				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_otp_success'));
			}
			else {
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_invalid_otp'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Resend otp
	public function profile_resend_otp_get($arg=array())
	{
		
		$data = $arg;

		if(!empty($data)) {

			$user_id = $arg['user_id'];
			$secondary_mobile = $arg['user_mobile_secondary'];

			$update_otp['user_otp'] = mt_rand(111111,999999);
			$update_otp['user_otp_sent_date'] = date('Y-m-d H:i:s');
			$text = "To+confirm+your+number+using+OTP+".$update_otp['user_otp'];
			$tomobile = $secondary_mobile;

			$verification = $this->common->send_sms($tomobile,$text);

			// verification
			if($verification) {
						
				$otp_update = $this->profile_model->update_otp($user_id,$update_otp);
				$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_resend_otp'), 'server_data'=>array('otp'=>$update_otp['user_otp']));
			}
			else {
				$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER,'message'=> $this->lang->line('text_sms_error'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}
	
}
// End of profile controller - api