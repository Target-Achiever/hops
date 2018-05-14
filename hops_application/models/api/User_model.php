<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /* ======     Check the record already exist or not by mobile or email    ====== */
	public function check_already_exist($val,$type)
	{
		if($type == "email") {
			$where_cond = array("user_email"=>$val);
		}
		else {
			$where_cond = array("user_mobile"=>$val);
		}

		$model_data = $this->db->get_where('hs_users',$where_cond)->num_rows();

		return $model_data;
	}

	/* =============         Insert user details        ======== */
	public function insert_users($data)
	{
		$model_action = $this->db->insert('hs_users',$data);
		
		if($model_action) {
			$model_data['insert_id'] = $this->db->insert_id();
			$model_data['status'] = "true";
		}
		else {
			$model_data['status'] = "false";
		}

		return $model_data;
	}

	/* =============         Insert user related actions        ======== */
	public function insert_user_actions($data)
	{
		$model_action = $this->db->insert('hs_user_actions',$data);
		
		return TRUE;
	}

	/* =============         Check otp by mobile number and get user data        ======== */
	public function check_otp($data)
	{
		
		if(!empty($data['user_mobile'])) {
			$where_cond = array('user_mobile'=>$data['user_mobile']);
		}
		else {
			$where_cond = array('user_email'=>$data['user_email']);
		}
		
		$this->db->select('u.user_id,u.user_fullname,u.user_email,u.user_mobile,u.user_status,a.user_otp_sent_date,IFNULL(user_profile_image,"") as user_profile_image,IFNULL(user_category,"") as user_category');
		$this->db->from('hs_users u');
		$this->db->join('hs_user_actions a','u.user_id=a.user_id AND a.user_otp="'.$data['user_otp'].'"','inner');
		$model_data = $this->db->where($where_cond)->get()->row_array();

		return $model_data;
	}

	/* =============         Update user data        ======== */
	public function update_users($user_id,$data)
	{
		
		$model_data = $this->db->where('user_id',$user_id)->update('hs_users',$data);
		
		return TRUE;
	}

	/* =============         Update user data by email id or mobile number      ======== */
	public function update_users_by_em($data)
	{
		if(!empty($data['user_email'])) {

			$user_email = $data['user_email'];
			unset($data['user_email']);
			$model_data = $this->db->where('user_email',$user_email)->update('hs_users',$data);
		}
		else {

			$user_mobile = $data['user_mobile'];
			unset($data['user_mobile']);
			$model_data = $this->db->where('user_mobile',$user_mobile)->update('hs_users',$data);
		}
		
		return TRUE;
	}

	/* ========    If device token already exists means, update that as empty  ======== */
	public function device_token_update($device_token)
	{
		$model_data = $this->db->where('logs_device_token',$device_token)->update('hs_user_device_logs',array('logs_device_token'=>''));

		return TRUE;
	}

	/*    =============         Insert or Update user logs        ========     */
	public function update_userlogs($user_id,$data)
	{
		$model_data_count = $this->db->get_where('hs_user_device_logs',array('user_id'=>$user_id))->num_rows();

		if($model_data_count == 0) {
			$data['user_id'] = $user_id;
			$data['logs_login_count'] = 1;
			$model_data = $this->insert_userlogs($data);
		}
		else {
			$this->db->where('user_id',$user_id);
			$this->db->set('logs_login_count','logs_login_count+1',FALSE);
			$this->db->set($data);
			$model_data = $this->db->update('hs_user_device_logs');
		}

		return TRUE;
	}

	/* =============         Insert user logs        ======== */
	public function insert_userlogs($data)
	{
		$this->db->insert('hs_user_device_logs',$data);

		$this->user_default_settings($data['user_id']);
		
		return TRUE;
	}

	/* =============         Get user details        ======== */
	public function get_user_details($val,$type)
	{
		$model_data = array();

		if($type == "email") {
			$where_cond = array("user_email"=>$val);
		}
		else if($type == "mobile") {
			$where_cond = array("user_mobile"=>$val);
		}
		else {
			$where_cond = array("users_id"=>$val);	
		}

		$model_data = $this->db->select('user_id,user_fullname,user_email,user_mobile,user_status,IFNULL(user_profile_image,"") as user_profile_image,IFNULL(user_category,"") as user_category')->get_where('hs_users',$where_cond)->row_array();

		return $model_data;
	}

	/* =============         Update otp   ======== */
	public function update_otp($user_id,$data)
	{
		
		$model_data = $this->db->where('user_id',$user_id)->update('hs_user_actions',$data);
		
		return TRUE;
	}

	/* =============         Signin verification        ======== */
	public function signin_verify($data)
	{

		if(!empty($data['user_email'])) {
			$where_cond = '(user_email="'.$data['user_email'].'" AND user_password="'.$data['user_password'].'")';
			$result_data = $this->db->select('user_id,user_fullname,user_email,user_mobile,user_status,IFNULL(user_profile_image,"") as user_profile_image,IFNULL(user_category,"") as user_category')->get_where('hs_users',$where_cond)->row_array();
		}
		else {
			$where_cond = '(user_mobile="'.$data['user_mobile'].'" AND user_password="'.$data['user_password'].'")';
			$result_data = $this->db->select('user_id,user_fullname,user_email,user_mobile,user_status,IFNULL(user_profile_image,"") as user_profile_image,IFNULL(user_category,"") as user_category')->get_where('hs_users',$where_cond)->row_array();
		}

		if(!empty($result_data)) {
			$model_data['status'] = "true";
			$model_data['data'] = $result_data;
		}
		else {
			$model_data['status'] = "false";
		}

		return $model_data;
	}

	/* ========                  Logout          ======== */
	public function user_logout($data)
	{

		$update_data = $this->db->where('user_id',$data['user_id'])->update('hs_user_device_logs',array('logs_login_status'=>2));
		
		return TRUE;
	}

	/* =============         User default settings   ======== */
	public function user_default_settings($user_id)
	{
		// insert settings
		$insert_settings_data = array(
									'user_id' => $user_id,
									'user_settings_updated_date' => date('Y-m-d H:i:s')
								);
		$model_settings_data = $this->db->insert('hs_user_settings',$insert_settings_data);

		return TRUE;
	}
	
    

}
// End of user model - api

?>
