<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Settings_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /* ======     Get user settings details    ====== */
	public function user_settings_view($user_id)
	{

		$model_data = $this->db->select('user_id,email_notify,newsletter_notify,display_ratings_option,push_alert')->get_where('hs_user_settings',array('user_id'=>$user_id))->row_array();
		
		return $model_data;
	}

	/* ======     Check the password exist or not    ====== */
	// public function check_password_exist($user_id)
	// {	

	// 	$where_cond = '(user_id="'.$user_id.'" AND user_password IS NOT NULL)';

	// 	$model_data = $this->db->select('user_id')->where($where_cond)->get('hs_users')->row_array();

	// 	return $model_data;
	// }

	/* ======     Get user settings update    ====== */
	public function user_settings_update($user_id,$data)
	{

		$model_data = $this->db->where('user_id',$user_id)->update('hs_user_settings',$data);
		
		return TRUE;
	}

	/* ======     Check the password exist or not    ====== */
	public function check_password_exist($user_id)
	{	

		$where_cond = '(user_id="'.$user_id.'" AND user_password IS NOT NULL)';

		$model_data = $this->db->select('user_id,user_password')->where($where_cond)->get('hs_users')->row_array();

		return $model_data;
	}

	/* ======     Update user password    ====== */
	public function update_password($user_id,$password)
	{	

		$model_data = $this->db->where('user_id',$user_id)->update('hs_users',array('user_password'=>$password));

		return TRUE;
	}
	
}
// End of profile model - api

?>
