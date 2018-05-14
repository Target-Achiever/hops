<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Profile_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /* ======     Get user full details    ====== */
	public function user_full_details($user_id)
	{

		$this->db->select('u.user_id,IFNULL(u.user_fullname,"") as user_fullname,IFNULL(u.user_email,"") as user_email,IFNULL(u.user_mobile,"") as user_mobile,IFNULL(u.user_profile_image,"") as user_profile_image,IFNULL(ad.user_address1,"") as user_address1,IFNULL(ad.user_address2,"") as user_address2,IFNULL(ad.user_city,"") as user_city,IFNULL(ad.user_zipcode,"") as user_zipcode,IFNULL(ad.user_country,"") as user_country,IFNULL(ac.user_account_name,"") as user_account_name,IFNULL(ac.account_iban,"") as account_iban,IFNULL(ac.account_swift,"") as account_swift,IFNULL(ac.account_details,"") as account_details');
		$this->db->from('hs_users u');
		$this->db->join('hs_user_addresses ad','u.user_id=ad.user_id','left');
		$this->db->join('hs_user_accounts ac','u.user_id=ac.user_id','left');
		$model_data = $this->db->where('u.user_id',$user_id)->get()->row_array();

		return $model_data;
	}

	/* ======     Update user full details    ====== */
	public function update_profile($user_id,$data)
	{	

		// Update user basic details
		$update_user_det = $this->db->where('user_id',$user_id)->update('hs_users',$data['user_details']);

		// Update user address
		$user_address = $this->db->get_where('hs_user_addresses',array('user_id'=>$user_id))->num_rows();
		if($user_address == 0) {
			$data['user_address']['user_id'] = $user_id;
			$insert_user_address = $this->db->insert('hs_user_addresses',$data['user_address']);
		}
		else {
			$update_user_address = $this->db->where('user_id',$user_id)->update('hs_user_addresses',$data['user_address']);
		}

		// Update user account
		if(!empty($data['user_account'])) {
			$user_account = $this->db->get_where('hs_user_accounts',array('user_id'=>$user_id))->num_rows();
			if($user_account == 0) {
				$data['user_account']['user_id'] = $user_id;
				$insert_user_account = $this->db->insert('hs_user_accounts',$data['user_account']);
			}
			else {
				$update_user_account = $this->db->where('user_id',$user_id)->update('hs_user_accounts',$data['user_account']);
			}
		}
			
		return TRUE;
	}

	/* ======     Update user category    ====== */
	public function update_user_category($user_id,$data)
	{	

		$model_data = $this->db->where('user_id',$user_id)->update('hs_users',array('user_category'=> $data['user_category']));

		return $model_data;
	}

	/* ======     Update user location    ====== */
	public function update_user_location($user_id,$data)
	{	

		$model_data_count = $this->db->get_where('hs_user_addresses',array('user_id'=>$user_id))->num_rows();
		if($model_data_count == 0) {
			$model_data = $this->db->insert('hs_user_addresses',array('user_id'=>$user_id,'user_latitude'=>$data['user_latitude'],'user_longitude'=> $data['user_longitude']));
		}
		else {
			$model_data = $this->db->where('user_id',$user_id)->update('hs_user_addresses',array('user_latitude'=>$data['user_latitude'],'user_longitude'=> $data['user_longitude']));
		}
		

		return $model_data;
	}

	/* ======     Insert card details    ====== */
	public function insert_card_details($data)
	{	

		$model_data_count = $this->db->get_where('hs_card_details',array('user_id'=>$data['user_id'],'card_number'=>$data['card_number'],'card_status'=>1))->num_rows();

		if($model_data_count == 0) {
			$data['card_status'] = 1;
			$insert_card = $this->db->insert('hs_card_details',$data);
			$model_data['status'] = "true";
		}
		else {
			$model_data['status'] = "false";
		}		

		return $model_data;
	}

	/* ======     Get saved cards list    ====== */
	public function saved_cards_list($user_id)
	{	

		$model_data = $this->db->select('card_details_id,card_holder_name,card_number,expiry_date,card_type')->get_where('hs_card_details',array('user_id'=>$user_id,'card_status'=>1))->result_array();
		
		return $model_data;
	}

	/* ======     Delete saved card details    ====== */
	public function delete_card_details($card_id)
	{	

		$model_data = $this->db->where('card_details_id',$card_id)->update('hs_card_details',array('card_status'=>2));
		
		return $model_data;
	}


}
// End of profile model - api


?>
