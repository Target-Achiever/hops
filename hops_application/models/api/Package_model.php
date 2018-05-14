<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Package_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /* ======     Package cost    ====== */
	public function package_cost($pack_id)
	{

		$model_data = $this->db->select('package_size_cost')->get_where('hs_package_sizes',array('package_sizes_id'=>$pack_id))->row_array();
		$model_data_cost = $model_data['package_size_cost'];

		return $model_data_cost;
	}

	/* ======     Insert order    ====== */
	public function insert_order($data)
	{

		$model_data_insert = $this->db->insert('hs_orders',$data);

		if($model_data_insert) {
			$model_data['insert_id'] = $this->db->insert_id();
			$model_data['status'] = 'true';
		}
		else {
			$model_data['status'] = 'false';
		}
	
		return $model_data;
	}

	/* ======     Check the shipper eligibility   ====== */
	public function check_user_cards($user_id)
	{

		$model_data = $this->db->get_where('hs_card_details',array('user_id'=>$user_id,'card_status'=>1))->num_rows();

		return $model_data;
	}

	/* ======     Search hopper by location    ====== */
	public function search_user_list($data)
	{

		$latitude = $data['pickup_latitude'];
		$longitude = $data['pickup_longitude'];
		
		$pickup_time = $data['pickup_time'];
		$drop_time = $data['drop_time'];

		// logics

		// $where_cond = '(((schedule_start_time>="'.$pickup_time.'" AND schedule_start_time<"'.$drop_time.'") OR (schedule_start_time<="'.$pickup_time.'" AND schedule_end_time>"'.$pickup_time.'")) AND (schedule_status=1 OR schedule_status=2))';
		// $this->db->select('hopper_schedules_id');
		// $this->db->from('hs_hopper_schedules');
		// $this->db->where($where_cond);
		// $model_data = $this->db->get()->result_array();

		// print_r($model_data);

		$distance = $data['distance'];

		$where_cond = '(u.user_mobile IS NOT NULL AND u.user_category=2 AND u.user_id!="'.$data['user_id'].'" AND u.user_status=1)';

		$this->db->select('u.user_id as user_id,3956 * 2 * ASIN(SQRT( POWER(SIN(('.$latitude.' - a.	user_latitude) * pi()/180 / 2), 2) + COS('.$latitude.' * pi()/180) * COS(a.user_latitude * pi()/180) *POWER(SIN(('.$longitude.' - a.user_longitude) * pi()/180 / 2), 2) )) as distance,dl.logs_device_type,dl.logs_device_token,dl.logs_login_status,us.push_alert');
		$this->db->from('hs_users u');
		$this->db->join('hs_user_addresses a','a.user_id=u.user_id','inner');
		$this->db->join('hs_doc_verify dv','dv.user_id=u.user_id AND dv.doc_verify_status=2','inner');
		$this->db->join('hs_user_device_logs dl','dl.user_id=u.user_id','inner');
		$this->db->join('hs_user_settings us','us.user_id=u.user_id','left');
		$this->db->having('distance <= "'.$distance.'" AND user_id NOT IN (select DISTINCT(hs.hopper_id) from hs_hopper_schedules hs where ((hs.schedule_start_time>="'.$pickup_time.'" AND hs.schedule_start_time<"'.$drop_time.'") OR (hs.schedule_start_time<="'.$pickup_time.'" AND hs.schedule_end_time>"'.$pickup_time.'")) AND hs.schedule_status=1 AND hs.hopper_id=u.user_id)', NULL, FALSE);
		$this->db->order_by('distance asc,u.user_profile_created_date desc');
		$model_data = $this->db->where($where_cond)->get()->result_array();
		
		return $model_data;
	}

	/* ======     To check whether the hopper is free on that time    ====== */
	public function check_hopper_time($data)
	{

		$pickup_time = $data['pickup_time'];
		$drop_time = $data['drop_time'];

		$where_cond = '(((schedule_start_time>="'.$pickup_time.'" AND schedule_start_time<"'.$drop_time.'") OR (schedule_start_time<="'.$pickup_time.'" AND schedule_end_time>"'.$pickup_time.'")) AND schedule_status=1 AND hopper_id="'.$data['user_id'].'")';
		$this->db->select('hopper_schedules_id');
		$this->db->from('hs_hopper_schedules');
		$this->db->where($where_cond);
		$model_data = $this->db->get()->row_array();

		return $model_data;
	}

	/* ======     Search hopper by location    ====== */
	// public function search_user_list($data)
	// {

	// 	$latitude = $data['pickup_latitude'];
	// 	$longitude = $data['pickup_longitude'];
		
	// 	$pickup_time = $data['pickup_time'];
	// 	$drop_time = $data['drop_time'];

	// 	// logics

	// 	// $where_cond = '(((schedule_start_time>="'.$pickup_time.'" AND schedule_start_time<"'.$drop_time.'") OR (schedule_start_time<="'.$pickup_time.'" AND schedule_end_time>"'.$pickup_time.'")) AND (schedule_status=1 OR schedule_status=2))';
	// 	// $this->db->select('hopper_schedules_id');
	// 	// $this->db->from('hs_hopper_schedules');
	// 	// $this->db->where($where_cond);
	// 	// $model_data = $this->db->get()->result_array();

	// 	// print_r($model_data);

	// 	$distance = $data['distance'];

	// 	$where_cond = '(u.user_mobile IS NOT NULL AND u.user_category=2 AND u.user_id!="'.$data['user_id'].'" AND u.user_status=1)';

	// 	$this->db->select('u.user_id as user_id,3956 * 2 * ASIN(SQRT( POWER(SIN(('.$latitude.' - a.	user_latitude) * pi()/180 / 2), 2) + COS('.$latitude.' * pi()/180) * COS(a.user_latitude * pi()/180) *POWER(SIN(('.$longitude.' - a.user_longitude) * pi()/180 / 2), 2) )) as distance,dl.logs_device_type,dl.logs_device_token,dl.logs_login_status,');
	// 	$this->db->from('hs_users u');
	// 	$this->db->join('hs_user_addresses a','a.user_id=u.user_id','inner');
	// 	$this->db->join('hs_doc_verify dv','dv.user_id=u.user_id AND dv.doc_verify_status=2','inner');
	// 	$this->db->join('hs_user_device_logs dl','dl.user_id=u.user_id','inner');
	// 	$this->db->having('distance <= "'.$distance.'" AND user_id NOT IN (select DISTINCT(hs.hopper_id) from hs_hopper_schedules hs where ((hs.schedule_start_time>="'.$pickup_time.'" AND hs.schedule_start_time<"'.$drop_time.'") OR (hs.schedule_start_time<="'.$pickup_time.'" AND hs.schedule_end_time>"'.$pickup_time.'")) AND (hs.schedule_status=1 OR hs.schedule_status=2) AND hs.hopper_id=u.user_id)', NULL, FALSE);
	// 	$this->db->order_by('distance asc,u.user_profile_created_date desc')->limit('1');
	// 	$model_data = $this->db->where($where_cond)->get()->result_array();
		
	// 	return $model_data;
	// }

	/* ===========         Save notifications batch   ======= */
	public function save_notifications_batch($data)
  	{

      	$insert_data = $this->db->insert_batch('hs_notifications',$data);

      	if($insert_data) {
          	$first_id = $this->db->insert_id();
          	$affected_rows = $this->db->affected_rows();
          	for($i=0;$i<$affected_rows;$i++) {
              	$insert_ids[] = $first_id+$i;
          	}
          	$model_data['insert_ids'] = $this->db->select('notification_id,notification_to_id')->where_in('notification_id',$insert_ids)->get('hs_notifications')->result_array();
      	}
      	else {
          	$model_data['insert_ids'] = '';
      	}

	    return $model_data;
  	}

  	public function save_notifications($data)
  	{

      	$insert_data = $this->db->insert('hs_notifications',$data);

      	if($insert_data) {

      		$model_data['status'] = 'true';
      		$model_data['insert_id'] = $this->db->insert_id();;
      	}
      	else {
          	$model_data['status'] = 'false';
      	}

	    return $model_data;
  	}

  	/* ===========         To get order list for user   ======= */
  	public function get_orders_list_user($user_id)
  	{

	  	$where_cond= '(o.order_status!=3 AND o.user_id="'.$user_id.'")';
	  	
	  	$this->db->select('o.order_id,o.pickup_address,o.drop_address,o.pickup_latitude,o.pickup_longitude,o.drop_latitude,o.drop_longitude,o.pickup_time,o.drop_time,o.package_size,o.package_name,o.package_image,o.total_cost,o.order_status,o.orders_created_date,IFNULL(o.hopper_id,"") as user_id,IFNULL(u.user_fullname,"") as user_fullname,IFNULL(u.user_email,"") as user_email,IFNULL(u.user_mobile,"") as user_mobile');
	  	$this->db->from('hs_orders o');
	  	$this->db->join('hs_users u','o.hopper_id=u.user_id','left');
	  	$this->db->order_by('o.order_id desc');
	  	$model_data = $this->db->where($where_cond)->get()->result_array();
		
		return $model_data;
	}

	/* ===========         To get order list for hopper   ======= */
  	public function get_orders_list_hopper($user_id)
  	{

	  	$where_cond= '(hs.hopper_id="'.$user_id.'")';
	  	
	  	$this->db->select('hs.hopper_schedules_id,hs.schedule_status,o.order_id,o.pickup_address,o.drop_address,o.pickup_latitude,o.pickup_longitude,o.drop_latitude,o.drop_longitude,o.pickup_time,o.drop_time,o.package_size,o.package_name,o.package_image,o.total_cost,o.order_status,o.orders_created_date,IFNULL(u.user_id,"") as user_id,IFNULL(u.user_fullname,"") as user_fullname,IFNULL(u.user_email,"") as user_email,IFNULL(u.user_mobile,"") as user_mobile');
	  	$this->db->from('hs_hopper_schedules hs');
	  	$this->db->join('hs_orders o','o.order_id=hs.order_id','inner');
	  	$this->db->join('hs_users u','o.user_id=u.user_id','inner');
	  	$this->db->order_by('hs.hopper_schedules_id desc');
	  	$model_data = $this->db->where($where_cond)->get()->result_array();

		return $model_data;
	}

	public function insert_test($data) {
		$this->db->insert('test',$data);
	}

	// /* ======     Search hopper by location    ====== */
	// public function search_user_list_schedule($data,$exclude_array)
	// {

	// 	$latitude = $data['pickup_latitude'];
	// 	$longitude = $data['pickup_longitude'];
		
	// 	$pickup_time = $data['pickup_time'];
	// 	$drop_time = $data['drop_time'];

	// 	$distance = $data['distance'];

	// 	$where_cond = '(u.user_mobile IS NOT NULL AND u.user_category=2 AND u.user_id!="'.$data['user_id'].'" AND u.user_status=1)';

	// 	$this->db->select('u.user_id as user_id,3956 * 2 * ASIN(SQRT( POWER(SIN(('.$latitude.' - a.	user_latitude) * pi()/180 / 2), 2) + COS('.$latitude.' * pi()/180) * COS(a.user_latitude * pi()/180) *POWER(SIN(('.$longitude.' - a.user_longitude) * pi()/180 / 2), 2) )) as distance,dl.logs_device_type,dl.logs_device_token,dl.logs_login_status,');
	// 	$this->db->from('hs_users u');
	// 	$this->db->join('hs_user_addresses a','a.user_id=u.user_id','inner');
	// 	$this->db->join('hs_doc_verify dv','dv.user_id=u.user_id AND dv.doc_verify_status=2','inner');
	// 	$this->db->join('hs_user_device_logs dl','dl.user_id=u.user_id','inner');
	// 	$this->db->having('distance <= "'.$distance.'" AND user_id NOT IN (select DISTINCT(hs.hopper_id) from hs_hopper_schedules hs where ((hs.schedule_start_time>="'.$pickup_time.'" AND hs.schedule_start_time<"'.$drop_time.'") OR (hs.schedule_start_time<="'.$pickup_time.'" AND hs.schedule_end_time>"'.$pickup_time.'")) AND (hs.schedule_status=1 OR hs.schedule_status=2) AND hs.hopper_id=u.user_id) AND user_id NOT IN ('.implode($exclude_array,',').')', NULL, FALSE);
	// 	$this->db->order_by('distance asc,u.user_profile_created_date desc')->limit('1');
	// 	$model_data = $this->db->where($where_cond)->get()->result_array();
		
	// 	return $model_data;
	// }

	/* ===========         Check the order is new order or not   ======= */
  	public function check_order($order_id)
  	{

  		$model_data = $this->db->select('package_name,pickup_time,drop_time,user_id')->get_where('hs_orders',array('order_id'=>$order_id,'order_status'=>1))->row_array();

		return $model_data;
	}

	/* ===========         Accept the order and create a schedule   ======= */
  	public function create_hopper_schedule($data)
  	{

  		// Update order
  		$update_order = $this->db->where('order_id',$data['order_id'])->update('hs_orders',array('hopper_id'=>$data['user_id'],'order_status'=>2));

  		// Create a schedule for hopper
  		$create_schedule = $this->db->insert('hs_hopper_schedules',array('hopper_id'=>$data['user_id'],'order_id'=>$data['order_id'],'schedule_start_time'=>$data['pickup_time'],'schedule_end_time'=>$data['drop_time'],'schedule_status'=>1,'hopper_schedules_updated_date'=>$data['current_date']));
  		if($create_schedule) {
  			$model_data['status'] = "true";
  			$model_data['insert_id'] = $this->db->insert_id();
  		}
  		else {
  			$model_data['status'] = "false";
  		}

		return $model_data;
	}

	/* ===========         Get user device details   ======= */
  	public function get_users_device_details($user_id)
  	{

  		$model_data = $this->db->select('logs_device_type,logs_device_token')->get_where('hs_user_device_logs',array('user_id'=>$user_id,'logs_login_status'=>1))->row_array();

		return $model_data;
	}

	

	


	


	

}
// End of package model - api


?>
