<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . 'libraries/REST_Controller.php';

class User extends REST_Controller {

	/* Constructor */
	public function __construct()
    {
        parent::__construct();
        $this->load->library('../controllers/api/common/');
        $this->load->model('api/user_model');
    }

    // Registration
	public function index_post($arg=array())
	{
		
      	$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) {

			$email_count = $this->user_model->check_already_exist($data['user_email'],"email");
			$mobile_count = $this->user_model->check_already_exist($data['user_mobile'],"mobile");

			if($email_count == 0 && $mobile_count == 0) {

				// User registration values
				$user_actions['user_otp'] = mt_rand(111111,999999);
				$user_actions['user_otp_sent_date'] = date('Y-m-d H:i:s');
				$otp_val = $user_actions['user_otp'];
				$data['user_password'] = $this->common->custom_encrypt($data['user_password']);
				$data['user_status'] = 2;
				$data['user_register_type'] = 1;
				$data['user_profile_updated_date'] = date('Y-m-d H:i:s');

				$result = $this->user_model->insert_users($data);

				if($result['status'] == "true") {

					$user_actions['user_id'] = $result['insert_id'];
					$result_actions = $this->user_model->insert_user_actions($user_actions);

					// otp
					$text = "Thanks+for+joining+with+us.+Your+OTP+is+".$user_actions['user_otp'];
					$tomobile = $data['user_mobile'];
					$verification = $this->common->send_sms($tomobile,$text);

					if($verification) {

						// server data
						$server_data['otp'] = $otp_val;
						$server_data['user_id'] = $result['insert_id'];

						$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message' => $this->lang->line('text_registration_success'),'server_data'=>$server_data);
					}
					else {
						$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER, 'message'=> $this->lang->line('text_sms_error'));
					}
				}
				else {
					$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER,'message' => $this->lang->line('text_query_error'));
				}
			}
			else {

				$message = ($email_count == 1) ? $this->lang->line('text_email_exists') : (($mobile_count == 1) ? $this->lang->line('text_mobile_exists') : '');
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$message);
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// OTP verification
	public function otp_verification_post($arg=array())
	{
		
      	$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) {

			// Check given field is email or mobile
			if(preg_match('/^[0-9]+$/', $data['user_email_mobile'])) {
				$data['user_mobile'] = $data['user_email_mobile'];
			}
			else {
				$data['user_email'] = $data['user_email_mobile'];
			}
			unset($data['user_email_mobile']);

			$user_data = $this->user_model->check_otp($data);

			if(!empty($user_data)) {

				// OTP validation for maximum one minute
				$current_timeF = date('Y-m-d H:i:s');
				$cTime  = strtotime($current_timeF);
				$otpTime = strtotime($user_data['user_otp_sent_date']);
				$differenceInSeconds = $cTime - $otpTime;
				
				// Check the total minutes exceeds two minutes
				if($differenceInSeconds > 120) 
				{
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_otp_expired'));
					$this->response($response);
				}
				// Check the user is blocked by admin or not
				// else if($user_data['user_status'] == 3) {
				// 	$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_admin_error'));
				// 	$this->response($response);
				// }

				$user_id = $user_data['user_id'];

				if($data['api_action'] == "registration") {

					$update_data['user_status'] = 1;
					$user_update = $this->user_model->update_users($user_id,$update_data);

					// Signin data
					$logs_data['logs_login_type'] = 1;
					$logs_data['unique_id'] = $this->common->random_unique_id();
					$logs_data['logs_login_status'] = 1;
					$logs_data['logs_updated_date'] = date('Y-m-d H:i:s');
					$logs_data['logs_device_type'] = $data['logs_device_type'];
					$logs_data['logs_device_token'] = $data['logs_device_token'];
					$device_token_update = $this->user_model->device_token_update($data['logs_device_token']);
					$logs_result = $this->user_model->update_userlogs($user_id,$logs_data);

					$user_det = array('user_id'=>$user_id,'user_fullname'=>$user_data['user_fullname'],'user_category'=>$user_data['user_category'],'user_profile_image'=>$user_data['user_profile_image'],'session_id'=>$logs_data['unique_id']);

					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_login_success'), 'server_data'=>$user_det);
				}
				else if($data['api_action'] == "forgot_password") {

					if($user_data['user_status'] == 2)
					{
						$update_data['user_status'] = 1;
						$user_update = $this->user_model->update_users($user_id,$update_data);
					}
					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_otp_success'));
				}
				else {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_keyword_mismatch'));
				}
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
	public function resend_otp_get($arg=array())
	{
		
      	$data = $this->input->get();

		if(!empty($data)) {

			// Check given field is email or mobile
			if(preg_match('/^[0-9]+$/', $data['user_email_mobile'])) {
				$data['user_mobile'] = $data['user_email_mobile'];
				$user_data = $this->user_model->get_user_details($data['user_mobile'],"mobile");
			}
			else {
				$data['user_email'] = $data['user_email_mobile'];
				$user_data = $this->user_model->get_user_details($data['user_email'],"email");
			}
			unset($data['user_email_mobile']);

			if(!empty($user_data) && !empty($data['user_email'])) {

				$user_id = $user_data['user_id'];
				$update_otp['user_otp'] = mt_rand(111111,999999);
				$update_otp['user_otp_sent_date'] = date('Y-m-d H:i:s');

				if($data['api_action'] == "forgot_password") {

					$mail_sub = "Resend OTP";
					$email_data['username'] = $user_data['user_fullname'];
					$email_data['otp'] = $update_otp['user_otp'];
					$email_data['type'] = "Forgot Password";
					$mail_msg = $this->load->view('templates/otp_template',$email_data,true);
					$verification = $this->common->send_mail($data['user_email'],$mail_sub,$mail_msg);

					// verification
					if($verification) {

						$otp_update = $this->user_model->update_otp($user_id,$update_otp);
						$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_resend_otp'), 'server_data'=>array('otp'=>$update_otp['user_otp']));
					}
					else {
						$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER,'message'=> $this->lang->line('text_email_error'));
					}
				}
				else if($data['api_action'] == "registration") {

					$mail_sub = "Resend OTP";
					$email_data['username'] = $user_data['user_fullname'];
					$email_data['otp'] = $update_otp['user_otp'];
					$email_data['type'] = "Account Confirmation";
					$mail_msg = $this->load->view('templates/otp_template',$email_data,true);
					$verification = $this->common->send_mail($data['user_email'],$mail_sub,$mail_msg);
					$otp_update = $this->user_model->update_otp($user_id,$update_otp);

					// verification
					if($verification) {
						
						$otp_update = $this->user_model->update_otp($user_id,$update_otp);
						$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS, 'message'=>$this->lang->line('text_resend_otp'), 'server_data'=>array('otp'=>$update_otp['user_otp']));
					}
					else {
						$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER, 'message'=> $this->lang->line('text_email_error'));
					}
				}
				else {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_keyword_mismatch'));
				}
			}
			else if(!empty($user_data) && !empty($data['user_mobile'])) {

				$user_id = $user_data['user_id'];
				$update_otp['user_otp'] = mt_rand(111111,999999);
				$update_otp['user_otp_sent_date'] = date('Y-m-d H:i:s');
				$text = '';

				if($data['api_action'] == "forgot_password") {
					$text = "To+reset+your+password+using+OTP+".$update_otp['user_otp'];
				}
				else if($data['api_action'] == "registration") {
					$text = "To+confirm+your+account+using+OTP+".$update_otp['user_otp'];
				}
				else {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_keyword_mismatch'));
					$this->response($response);
				}

				$tomobile = $data['user_mobile'];
				$verification = $this->common->send_sms($tomobile,$text);

				// verification
				if($verification) {
						
					$otp_update = $this->user_model->update_otp($user_id,$update_otp);
					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_resend_otp'), 'server_data'=>array('otp'=>$update_otp['user_otp']));
				}
				else {
					$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER,'message'=> $this->lang->line('text_sms_error'));
				}
			}
			else {
				$response = array('status'=>FALSE,'status_code'=>HTTP_CONFLICT,'message'=> $this->lang->line('text_user_not_exist'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Login
	public function login_post($arg=array())
	{
		
      	$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) {

			// Check given field is email or mobile
			if(preg_match('/^[0-9]+$/', $data['user_email_mobile'])) {
				$data['user_mobile'] = $data['user_email_mobile'];
			}
			else {
				$data['user_email'] = $data['user_email_mobile'];
			}
			unset($data['user_email_mobile']);

			$data['user_password'] = $this->common->custom_encrypt($data['user_password']);
			$signin_verify = $this->user_model->signin_verify($data);

			if($signin_verify['status'] == "true") {

				$user_data = $signin_verify['data'];
				$user_id = $user_data['user_id'];

				// Check the user is blocked by admin or not
				if($user_data['user_status'] == 3) {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_admin_error'));
					$this->response($response);
				}
				else if($user_data['user_status'] == 1) {

					$logs_data['logs_login_type'] = 1;
					$logs_data['unique_id'] = $this->common->random_unique_id();
					$logs_data['logs_login_status'] = 1;
					$logs_data['logs_device_type'] = $data['logs_device_type'];
					$logs_data['logs_device_token'] = $data['logs_device_token'];
					$logs_data['logs_updated_date'] = date('Y-m-d H:i:s');

					$device_token_update = $this->user_model->device_token_update($data['logs_device_token']);
					$logs_result = $this->user_model->update_userlogs($user_id,$logs_data);

					$user_det = array('user_id'=>$user_id,'user_fullname'=>$user_data['user_fullname'],'user_profile_image'=>$user_data['user_profile_image'],'user_category'=>$user_data['user_category'],'session_id'=>$logs_data['unique_id']);
					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_login_success'), 'server_data'=>$user_det);
				}
				else {

					$user_otp = mt_rand(111111,999999);

					if(!empty($data['user_email'])) {

						$update_otp['user_otp'] = $user_otp;
						$update_otp['user_otp_sent_date'] = date('Y-m-d H:i:s');
						$mail_sub = "Account Confirmation";
						$email_data['username'] = $user_data['user_fullname'];
						$email_data['otp'] = $update_otp['user_otp'];
						$email_data['type'] = "Account Confirmation";
						$mail_msg = $this->load->view('templates/otp_template',$email_data,true);
						$verification = $this->common->send_mail($data['user_email'],$mail_sub,$mail_msg);
						$gw = 'email';
					}
					else {
						$update_otp['user_otp'] = $user_otp;
						$update_otp['user_otp_sent_date'] = date('Y-m-d H:i:s');
						$text = "Thanks+for+joining+with+us.+Your+OTP+is+".$update_otp['user_otp'];
						$tomobile = $data['user_mobile'];
						$verification = $this->common->send_sms($tomobile,$text);
						$gw = 'sms';
					}

					// verification
					if($verification) {
							
						$otp_update = $this->user_model->update_otp($user_id,$update_otp);
						$response = array('status' => TRUE, 'status_code'=>HTTP_OK, 'message'=>$this->lang->line('text_resend_otp'), 'server_data'=>array('otp'=>$update_otp['user_otp']));
					}
					else {
						$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER,'message'=> $this->lang->line('text_'.$gw.'_error'));
					}			
				}
			}
			else {
				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT, 'message'=>$this->lang->line('text_login_error'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// social_media_login
	public function social_media_login_post($arg=array())
	{
		
      	$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) {

			// Check whether email/mobile will come or not
			if(empty($data['user_email_mobile'])) {

				$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_social_media'));
				$this->response($response);
			}

			// Check given field is email or mobile
			if(preg_match('/^[0-9]+$/', $data['user_email_mobile'])) {
				$user_rec['user_mobile'] = $data['user_email_mobile'];
				$get_user_details = $this->user_model->get_user_details($user_rec['user_mobile'],"mobile");
			}
			else {
				$user_rec['user_email'] = $data['user_email_mobile'];
				$get_user_details = $this->user_model->get_user_details($user_rec['user_email'],"email");
			}
			unset($data['user_email_mobile']);

			if(!empty($get_user_details)) {

				$user_result = $get_user_details;

				$user_id = $user_result['user_id'];

				// Check the user is blocked by admin or not
				if($user_result['user_status'] == 3) {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_admin_error'));
					$this->response($response);
				}
				else if($user_result['user_status'] == 2)
				{
					$update_user['user_status'] = 1;
					$user_det_update = $this->user_model->update_users($user_id,$update_user);
				}

				$logs_data['logs_login_type'] = 2;
				$logs_data['unique_id'] = $this->common->random_unique_id();
				$logs_data['logs_login_status'] = 1;
				$logs_data['logs_updated_date'] = date('Y-m-d H:i:s');
				$logs_data['logs_device_type'] = $data['logs_device_type'];
				$logs_data['logs_device_token'] = $data['logs_device_token'];

				$device_token_update = $this->user_model->device_token_update($logs_data['logs_device_token'],$user_id);
				$logs_result = $this->user_model->update_userlogs($user_id,$logs_data);

				// Login response
				$user_det = array('user_id'=>$user_result['user_id'],'user_fullname'=>$user_result['user_fullname'],'user_profile_image'=>$user_result['user_profile_image'],'user_category'=>$user_result['user_category'],'session_id'=>$logs_data['unique_id']);
				$response = array('status' => TRUE, 'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_login_success'), 'server_data'=>$user_det);
			}
			else {

				// User data
				$user_rec['user_fullname'] = $data['user_fullname'];
				$user_rec['user_register_type'] = 2;
				$user_rec['user_status'] = 1;
				$user_rec['user_profile_updated_date'] = date('Y-m-d H:i:s');

				// Create a new directory if not exists
				if(!is_dir('./'.UPLOADS.'profile/')) {
					mkdir('./'.UPLOADS.'profile/',0777,true);
					// 1- execute 2- write 4- read
					// second parameter - First val is always zero,second one for owner,third one for owner user group,fourth one for everybody
				}	

				// Upload image from cdn path
				$file_path = '';

				if(!empty($data['user_profile_image'])) {

					$file_ext = ".png";
					$file_name   = time().mt_rand(000,999).$file_ext;
					$upload_path = './'.UPLOADS.'profile/'.$file_name;
					$contents =  @file_get_contents($data['user_profile_image']);

					if($contents) {
						$image = @file_put_contents($upload_path,$contents);

						if($image) {
							$create_thumb = $this->common->create_thumb($upload_path);
							$file_path = str_replace("./", "", $upload_path);
						}
					}
				}

				$user_rec['user_profile_image'] = $file_path;

				$user_result = $this->user_model->insert_users($user_rec);

				if($user_result['status'] == "true") {

					$logs_data['user_id'] = $user_result['insert_id'];
					$logs_data['unique_id'] = $this->common->random_unique_id();
					$logs_data['logs_login_type'] = 2;
					$logs_data['logs_login_status'] = 1;
					$logs_data['logs_login_count'] = 1;
					$logs_data['logs_updated_date'] = date('Y-m-d H:i:s');
					$logs_data['logs_device_type'] = $data['logs_device_type'];
					$logs_data['logs_device_token'] = $data['logs_device_token'];

					$device_token_update = $this->user_model->device_token_update($logs_data['logs_device_token'],$logs_data['user_id']);
					$logs_result = $this->user_model->insert_userlogs($logs_data);

					$user_det = array('user_id'=>$user_result['insert_id'],'user_fullname'=>$data['user_fullname'],'user_profile_image'=>$user_rec['user_profile_image'],'user_category'=>"",'session_id'=>$logs_data['unique_id']);
					$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_login_success'), 'server_data'=>$user_det);
				}
				else {
					$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER,'message'=> $this->lang->line('text_query_error'));
				}
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Forgot password
	public function forgot_password_get($arg=array())
	{
		
      	$data = $this->input->get();

		if(!empty($data)) {

			// Check given field is email or mobile
			if(preg_match('/^[0-9]+$/', $data['user_email_mobile'])) {
				$data['user_mobile'] = $data['user_email_mobile'];
				$user_data = $this->user_model->get_user_details($data['user_mobile'],"mobile");
			}
			else {
				$data['user_email'] = $data['user_email_mobile'];
				$user_data = $this->user_model->get_user_details($data['user_email'],"email");
			}
			unset($data['user_email_mobile']);

			if(!empty($user_data) && !empty($data['user_email'])) {

				// Check whether the user is blocked by admin or not
				if($user_data['user_status'] == 3) {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_admin_error'));
					$this->response($response);
				}
				else {

					$user_id = $user_data['user_id'];
					$update_otp['user_otp'] = mt_rand(111111,999999);
					$update_otp['user_otp_sent_date'] = date('Y-m-d H:i:s');
					$mail_sub = "Reset Password";
					$email_data['username'] = $user_data['user_fullname'];
					$email_data['otp'] = $update_otp['user_otp'];
					$email_data['type'] = "Reset Password";
					$mail_msg = $this->load->view('templates/otp_template',$email_data,true);
					$verification = $this->common->send_mail($data['user_email'],$mail_sub,$mail_msg);

					// Verification
					if($verification) {
							
						$otp_update = $this->user_model->update_otp($user_id,$update_otp);
						$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_resend_otp'), 'server_data'=>array('otp'=>$update_otp['user_otp']));
					}
					else {
						$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER,'message'=> $this->lang->line('text_email_error'));
					}
				}
			}
			else if(!empty($user_data) && !empty($data['user_mobile'])) {

				if($user_data['user_status'] == 3) {
					$response = array('status' => FALSE,'status_code'=>HTTP_CONFLICT,'message'=>$this->lang->line('text_admin_error'));
					$this->response($response);
				}
				else {

					$user_id = $user_data['user_id'];
					$update_otp['user_otp'] = mt_rand(111111,999999);
					$update_otp['user_otp_sent_date'] = date('Y-m-d H:i:s');
					$text = "To+reset+your+password+using+OTP+".$update_otp['user_otp'];
					$tomobile = $data['user_mobile'];
					$verification = $this->common->send_sms($tomobile,$text);

					// Verification
					if($verification) {
							
						$otp_update = $this->user_model->update_otp($user_id,$update_otp);
						$response = array('status' => TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_resend_otp'), 'server_data'=>array('otp'=>$update_otp['user_otp']));
					}
					else {
						$response = array('status'=>FALSE,'status_code'=>HTTP_SERVER,'message'=> $this->lang->line('text_email_error'));
					}
				}
			}
			else {
				$response = array('status'=>FALSE,'status_code'=>HTTP_CONFLICT,'message'=> $this->lang->line('text_user_not_exist'));
			}
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Reset password
	public function reset_password_post($arg=array())
	{
		
      	$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) {

			// Check given field is email or mobile
			if(preg_match('/^[0-9]+$/', $data['user_email_mobile'])) {
				$update_data['user_mobile'] = $data['user_email_mobile'];
			}
			else {
				$update_data['user_email'] = $data['user_email_mobile'];
			}
			unset($data['user_email_mobile']);

			$update_data['user_password'] = $this->common->custom_encrypt($data['user_password']);
			$user_update = $this->user_model->update_users_by_em($update_data);

			$response = array('status'=> TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_reset_password'));
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}

	// Logout
	public function logout_get() {

		$data = $this->input->get();

		if(!empty($data)) {

			// $logout_data = $this->user_model->user_logout($data);
			$response = array('status'=> TRUE,'status_code'=>HTTP_SUCCESS,'message'=>$this->lang->line('text_logout'));
		}
		else {
			$response = array('status' => FALSE,'status_code'=>HTTP_BAD_REQUEST,'message'=>$this->lang->line('text_empty_error'));
		}

		$this->response($response);
	}
}
// End of user controller - api